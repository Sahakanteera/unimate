# ส่วนที่ 2: ระบบประกาศและค้นหากิจกรรม

## ขอบเขตที่เพิ่ม

- สมาชิกที่เข้าสู่ระบบและไม่ถูกระงับสามารถสร้างโพสต์กิจกรรมได้
- ระบุชื่อ รายละเอียด หมวดหมู่ สถานที่ วันเวลาเริ่ม–สิ้นสุด และจำนวนคนที่รับเพิ่ม (ไม่รวมผู้ประกาศ)
- ค้นหาจากชื่อหรือรายละเอียด และกรองหมวดหมู่ สถานที่ วันที่เริ่ม และสถานะพร้อมกันได้
- หน้ารายละเอียดแสดงเจ้าของโพสต์ ข้อมูลนัดหมาย และสถานะยกเลิก
- เจ้าของโพสต์แก้ไขหรือยกเลิกได้ คนอื่นรวมถึง Admin แก้โพสต์นั้นไม่ได้
- ยกเลิกด้วยการเปลี่ยนสถานะเป็น `cancelled` ไม่ลบข้อมูล และไม่เปิดให้แก้ไขต่อ
- Admin เพิ่ม/เปลี่ยนชื่อ/ลบหมวดหมู่ได้ แต่ลบหมวดหมู่ที่ถูกใช้งานไม่ได้
- มีเมนูกิจกรรมและสร้างโพสต์ทั้งเดสก์ท็อปและมือถือ

ส่วนนี้ยังไม่มีระบบสมัครเข้าร่วม นับผู้เข้าร่วมจริง อนุมัติสมาชิก หรือแชต `capacity` คือจำนวนที่ประกาศรับ ไม่ใช่จำนวนที่เข้าร่วมแล้ว

## เริ่มใช้งาน

```sh
php artisan migrate
php artisan db:seed --class=CategorySeeder
```

คำสั่งนี้เพิ่มตารางและหมวดหมู่เริ่มต้น โดยไม่ต้องล้างฐานข้อมูลเดิม Seeder หมวดหมู่เรียกซ้ำได้

เข้าสู่ระบบแล้วเปิด `/activities` หรือกดเมนู “กิจกรรม” ส่วน Admin จัดการหมวดหมู่ที่ `/admin/categories`

## โครงสร้างข้อมูล

`categories`: `id`, `name` (ไม่ซ้ำ), timestamps

`activities`:

| คอลัมน์ | ความหมาย |
| --- | --- |
| `user_id` | เจ้าของโพสต์ อ้างอิง users |
| `category_id` | หมวดหมู่ อ้างอิง categories |
| `title`, `description` | ชื่อและรายละเอียด |
| `location` | สถานที่ |
| `starts_at`, `ends_at` | เวลาเริ่มและสิ้นสุด |
| `capacity` | จำนวนคนที่ต้องการรับเพิ่ม 1–10,000 คน |
| `status` | published / cancelled |
| timestamps | เวลาสร้างและแก้ไขล่าสุด |

`config/app.php` เปลี่ยน timezone จาก UTC เป็น Asia/Bangkok เพื่อให้ฟอร์ม datetime-local การตรวจเวลา และการแสดงผลใช้เวลาไทยเหมือนกันทั้งแอป ข้อมูลเวลาเดิมไม่ได้ถูกแปลงย้อนหลัง

## อ่านโค้ดตามลำดับนี้

### 1. `routes/web.php` — รับ URL แล้วเรียก Controller

`Route::resource('activities', ActivityController::class)->except('destroy')` สร้างเส้นทาง CRUD มาตรฐาน ยกเว้นลบทิ้ง ใช้ route PATCH แยกสำหรับยกเลิก

| Method | URL | เมธอด |
| --- | --- | --- |
| GET | /activities | index |
| GET | /activities/create | create |
| POST | /activities | store |
| GET | /activities/{activity} | show |
| GET | /activities/{activity}/edit | edit |
| PUT/PATCH | /activities/{activity} | update |
| PATCH | /activities/{activity}/cancel | cancel |

ทุกเส้นทางอยู่ใน middleware `auth` และ `active` ส่วน `/admin/categories` เพิ่ม middleware `admin`

### 2. `app/Http/Requests/SaveActivityRequest.php` — ตรวจสิทธิ์และข้อมูล

`authorize()` ตรวจสิทธิ์เจ้าของก่อนรับการแก้ไข ส่วน `rules()` กำหนดข้อมูลที่รับได้: เวลาเริ่มต้องอยู่ในอนาคต เวลาสิ้นสุดหลังเวลาเริ่ม จำนวนคนเป็นจำนวนเต็ม และหมวดหมู่ต้องมีจริง

ถ้าข้อมูลผิด Laravel ส่งกลับหน้าฟอร์มพร้อม errors และข้อมูลเก่า ซึ่งแสดงด้วย `old(...)` และ `activities/errors.blade.php`

### 3. `app/Http/Controllers/ActivityController.php` — การทำงานหลัก

- `index()`: ตรวจตัวกรอง สร้าง Eloquent query แล้วแบ่งหน้า 12 รายการ `withQueryString()` เก็บตัวกรองเมื่อเปลี่ยนหน้า
- คำค้นใช้ `where(function (...) {...})` รวมชื่อและรายละเอียดเป็นวงเล็บ SQL: `(title LIKE ... OR description LIKE ...) AND category_id = ...` ทำให้ผลค้นหารายละเอียดไม่หลุดตัวกรองหมวดหมู่
- `with(['user', 'category'])` ดึงเจ้าของและหมวดหมู่พร้อมกัน เพื่อลดการ query ซ้ำแต่ละการ์ด
- `store()`: รับเฉพาะ `$request->validated()` แล้วกำหนด `user_id` จากผู้ใช้ที่ล็อกอิน และกำหนด `published` ที่เซิร์ฟเวอร์
- `show()`: ส่งข้อมูลไปหน้ารายละเอียด
- `edit()` / `update()`: ตรวจสิทธิ์และไม่รับการแก้ไขกิจกรรมที่ยกเลิกแล้ว (409)
- `cancel()`: ตรวจสิทธิ์ก่อนตั้งสถานะ `cancelled`

### 4. `app/Policies/ActivityPolicy.php` — จุดสำคัญสำหรับอธิบายเรื่องสิทธิ์

```php
public function update(User $user, Activity $activity): bool
{
    return $user->id === $activity->user_id;
}
```

เปรียบเทียบ ID ผู้ล็อกอินกับเจ้าของโพสต์ ต้องเท่ากันเท่านั้น Laravel ค้นหา Policy ตามชื่อ Model/Policy โดยอัตโนมัติ

- เปิดหน้าแก้ไข: Controller เรียก `Gate::authorize('update', $activity)`
- ส่งคำขอแก้ไข: FormRequest เรียก `$this->user()->can('update', $activity)`
- ส่งคำขอยกเลิก: Controller เรียก `Gate::authorize('cancel', $activity)`
- หน้าเว็บ: `@can('update', $activity)` แสดงปุ่มเฉพาะเจ้าของ

ดังนั้นแม้คนอื่นพิมพ์ URL เองหรือส่ง PUT/PATCH เอง เซิร์ฟเวอร์ก็คืน 403 และข้อมูลไม่เปลี่ยน การซ่อนปุ่มเป็นเพียงส่วนหน้าจอ

Model ไม่ใส่ `user_id` และ `status` ใน `$fillable` จึงเปลี่ยนเจ้าของหรือสถานะผ่านข้อมูลฟอร์มไม่ได้

### 5. Models และ Migration

- `app/Models/Activity.php`: ความสัมพันธ์ `belongsTo` กับ User และ Category; casts แปลงวันเวลาเป็นวัตถุวันที่
- `app/Models/Category.php`: ความสัมพันธ์ `hasMany` กับ Activity
- `database/migrations/2026_09_17_000001_create_activity_tables.php`: สร้างตาราง foreign keys และ indexes; `restrictOnDelete()` ป้องกันลบหมวดหมู่ที่ถูกอ้างอิงในระดับฐานข้อมูลด้วย
- `database/seeders/CategorySeeder.php`: สร้างหมวดหมู่เริ่มต้นด้วย `firstOrCreate`
- `database/seeders/DatabaseSeeder.php`: ลงทะเบียน CategorySeeder เพิ่ม

### 6. หน้าจอ Blade

- `resources/views/activities/index.blade.php`: ตัวกรอง รายการกิจกรรม จำนวนผลค้นหา และ pagination
- `resources/views/activities/form.blade.php`: ฟอร์มร่วมสำหรับสร้าง/แก้ไข เลือก action ตาม `$activity->exists`
- `resources/views/activities/show.blade.php`: รายละเอียด สถานะ และปุ่มตามสิทธิ์
- `resources/views/activities/errors.blade.php`: กล่องข้อความ validation
- `resources/views/admin/categories/index.blade.php`: ฟอร์มเพิ่ม/แก้ไข/ลบหมวดหมู่
- `resources/views/layouts/app.blade.php`: เชื่อมเมนูและเพิ่มเมนูมือถือ
- `resources/views/errors/403.blade.php`: ปรับข้อความให้ครอบคลุมทั้งสิทธิ์เจ้าของโพสต์และ Admin

ฟอร์มที่เปลี่ยนข้อมูลใช้ `@csrf` และใช้ `@method` สำหรับ PUT/PATCH/DELETE ข้อมูลที่ผู้ใช้กรอกแสดงด้วย `{{ ... }}` ซึ่ง escape HTML

### 7. `app/Http/Controllers/Admin/CategoryController.php`

ตรวจชื่อหมวดหมู่ไม่ให้ซ้ำ ก่อนลบตรวจว่ามีกิจกรรมอ้างอิงหรือไม่ ถ้ามีจะกลับหน้าเดิมพร้อมข้อความแจ้งเตือน

## ลำดับสาธิตให้อาจารย์

1. ล็อกอินบัญชี A → กิจกรรม → สร้างโพสต์ เช่น “ชวนเล่นแบดมินตัน” หมวดกีฬา กำหนดเวลาอนาคต สถานที่และจำนวนคน
2. กลับรายการ → ค้น “แบด” พร้อมหมวดกีฬา/สถานที่/วันที่ → พบโพสต์ → เปิดรายละเอียด
3. กดแก้ไข เปลี่ยนสถานที่ → บันทึก → แสดงค่าที่เปลี่ยนแล้ว
4. จด URL `/activities/{id}/edit` → ออกจากระบบ → ล็อกอินบัญชี B ที่สมัครไว้
5. เปิดรายละเอียดโพสต์เดิม → ไม่มีปุ่มแก้ไข/ยกเลิก → เปิด URL แก้ไขที่จดไว้ → ได้หน้า 403
6. กลับบัญชี A → ยกเลิกกิจกรรม → รายละเอียดยังอยู่และแสดง “ยกเลิกแล้ว” → ค้นด้วยสถานะยกเลิกพบโพสต์
7. ล็อกอิน Admin → หมวดหมู่ → เพิ่มและแก้ชื่อหมวดหมู่ → หมวดที่ถูกใช้งานลบไม่ได้

## ทดสอบอัตโนมัติ

```sh
php artisan test tests/Feature/ActivitiesTest.php
```

ทดสอบ workflow หลัก ตัวกรอง การกรอกข้อมูลผิด บัญชีถูกระงับ การปลอม owner/status และการส่งคำขอแก้ไข/ยกเลิกโดยคนอื่นรวมถึง Admin โดยใช้ SQLite ในหน่วยความจำ ไม่ใช้ฐานข้อมูลจริง
