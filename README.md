# UniMate - เว็บแอปพลิเคชันระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย

**UniMate** เป็นระบบจับกลุ่มหาเพื่อนทำกิจกรรมต่างๆ ภายในมหาวิทยาลัย (เช่น เล่นกีฬา ติวหนังสือ ท่องเที่ยว) พัฒนาด้วย **Laravel Framework 13** (ตาม `composer.json`) ร่วมกับฐานข้อมูล **SQLite / MySQL**

เอกสารนี้อธิบายงานที่ทำแล้วในส่วนที่ 1 (ระบบสมาชิก) และส่วนที่ 2 (ระบบประกาศและค้นหากิจกรรม) สำหรับสมาชิกในทีมที่นำโค้ดไปพัฒนาต่อ

---

## 📌 เอกสารรายละเอียด ส่วนที่ 1: ระบบสมาชิกและสิทธิ์ผู้ใช้ (Part 1)

ส่วนที่ 1 รับผิดชอบเกี่ยวกับ **ระบบสมัครสมาชิก, การเข้าสู่ระบบ, การจัดการโปรไฟล์ส่วนตัว, การแบ่งสิทธิ์ผู้ใช้ (Student/Admin) และหน้าจอผู้ดูแลระบบสำหรับระงับ/เปิดใช้งานบัญชี**

---

### 1. ฟังก์ชันหลักของส่วนที่ 1 (Implemented Features)

| ฟังก์ชัน | รายละเอียดการทำงาน |
|---|---|
| **สมัครสมาชิก (Register)** | ลงทะเบียนบัญชีนักศึกษา (ชื่อ, รหัสนักศึกษา, อีเมลผู้ใช้, รหัสผ่านขั้นต่ำ 6 ตัวอักษร, คำแนะนำตัว Bio) |
| **เข้าสู่ระบบ / ออกจากระบบ (Login / Logout)** | ตรวจสอบสิทธิ์ และมีปุ่มเปิด-ปิดดูรหัสผ่าน (Toggle Eye Icon) หากบัญชีถูกระงับ `suspended` ระบบจะบล็อกล็อกอิน |
| **จัดการโปรไฟล์ (Profile Management)** | แก้ไขชื่อ, รหัสนักศึกษา, คำแนะนำตัว (Bio), เปลี่ยนรหัสผ่านใหม่ และอัปโหลดรูปโปรไฟล์อัตโนมัติ (Canvas Resizer) |
| **ระบบสิทธิ์ (Roles & Security)** | แบ่งเป็น `student` (นักศึกษา) และ `admin` (ผู้ดูแลระบบ) มี Middleware ป้องกันผู้ใช้ทั่วไปเข้าหน้า Admin (403 Forbidden) |
| **หน้า Admin จัดการสมาชิก (`/admin/users`)** | แดชบอร์ดสรุปสถิติสมาชิก ช่องค้นหาตามชื่อ/อีเมล/รหัสนักศึกษา และปุ่มสลับกด **ระงับบัญชี (Suspend)** หรือ **เปิดใช้งาน (Activate)** |

---

### 2. โครงสร้างฐานข้อมูล (Database Schema)

ตาราง `users` ได้รับการออกแบบเพิ่มเติมเพื่อรองรับส่วนที่ 1 และเตรียมโครงสร้างให้เพื่อนร่วมทีมส่วนที่ 2-5 ใช้งานต่อได้อย่างสะดวก:

#### Table: `users`
| คอลัมน์ (Column) | ประเภทข้อมูล (Type) | คำอธิบาย (Description) |
|---|---|---|
| `id` | BigInt (Auto Increment) | รหัสไอดีหลัก (Primary Key) |
| `name` | String | ชื่อ-นามสกุล หรือชื่อแสดงผล |
| `student_id` | String (Unique, Nullable) | รหัสนักศึกษา (เช่น `653020001-1`) |
| `email` | String (Unique) | อีเมลผู้ใช้ |
| `password` | String | รหัสผ่าน (เข้ารหัสความปลอดภัยด้วย Bcrypt) |
| `role` | Enum (`student`, `admin`) | ระดับสิทธิ์ผู้ใช้งาน (Default: `student`) |
| `status` | Enum (`active`, `suspended`) | สถานะบัญชีผู้ใช้ (Default: `active`) |
| `bio` | Text (Nullable) | คำแนะนำตัว / ข้อมูลส่วนตัว |
| `avatar` | String (Nullable) | พาธเก็บรูปโปรไฟล์ (เช่น `avatars/avatar_1_xxxx.png`) |
| `created_at` / `updated_at` | Timestamps | วันเวลาที่สร้างและอัปเดตข้อมูล |

---

### 3. รายละเอียดไฟล์ที่สร้างและเขียนในระบบ (File Architecture & Code Explanations)

#### 📂 3.1 Database & Migrations
- `database/migrations/2026_09_13_000001_add_unimate_fields_to_users_table.php`
  - **อธิบาย**: ไฟล์ Migration สำหรับเพิ่มคอลัมน์ `student_id`, `role`, `status`, `bio`, `avatar` เข้าไปยังตาราง `users`
- `database/seeders/UserSeeder.php`
  - **อธิบาย**: ไฟล์ Seeder สร้างข้อมูลตัวอย่าง 3 บัญชีหลัก (Student Active, Student Suspended, Admin) สำหรับใช้กดทดสอบและนำเสนออาจารย์
- `database/seeders/DatabaseSeeder.php`
  - **อธิบาย**: เรียกใช้ `UserSeeder::class` และ `CategorySeeder::class` เมื่อสั่งรันคำสั่ง `php artisan db:seed`

#### 📂 3.2 Models & Middlewares
- `app/Models/User.php`
  - **อธิบาย**: Eloquent Model ของผู้ใช้ เพิ่ม `$fillable` คอลัมน์ใหม่ และสร้าง Helper Methods: `isAdmin()`, `isStudent()`, `isSuspended()`, `isActive()`, `initials()`
- `app/Http/Middleware/EnsureAdmin.php`
  - **อธิบาย**: Middleware ตรวจสอบสิทธิ์ Admin หากนักศึกษาทั่วไปพยายามแอบเข้าหน้า `/admin/*` จะบล็อกและแสดงหน้า 403 Access Denied
- `app/Http/Middleware/EnsureActiveAccount.php`
  - **อธิบาย**: Middleware ตรวจสอบสถานะบัญชี หากบัญชีถูกระงับ (`status = suspended`) จะทำการสั่ง Logout ให้อัตโนมัติพร้อมแจ้งเตือนสีแดง
- `bootstrap/app.php`
  - **อธิบาย**: ลงทะเบียน Middleware Alias `'admin'` และ `'active'` ให้ Laravel เรียกใช้งาน

#### 📂 3.3 Controllers & Routes
- `app/Http/Controllers/AuthController.php`
  - **อธิบาย**: จัดการการลงทะเบียนนักศึกษาใหม่ (`register`), การเข้าสู่ระบบ (`login`) และการออกจากระบบ (`logout`)
- `app/Http/Controllers/ProfileController.php`
  - **อธิบาย**: จัดการการบันทึกแก้ไขข้อมูลส่วนตัว, รหัสนักศึกษา, คำแนะนำตัว (Bio), เปลี่ยนรหัสผ่านใหม่ และบันทึกรูปโปรไฟล์ (Base64 Canvas / File Upload)
- `app/Http/Controllers/Admin/UserController.php`
  - **อธิบาย**: จัดการหน้าผู้ดูแลระบบ ดึงรายชื่อสมาชิก คำนวณยอดสถิติ ค้นหาข้อมูล และสลับสถานะบัญชี `active` ↔ `suspended`
- `routes/web.php`
  - **อธิบาย**: กำหนดเส้นทาง URL ทั้งหมดในระบบ แบ่งเป็น Guest Routes, Authenticated Routes และ Admin Routes

#### 📂 3.4 Blade Views (หน้าจอระบบ)
- `resources/views/layouts/app.blade.php`
  - **อธิบาย**: Master Layout หลักของเว็บไซต์ (ธีมประยุกต์จาก fastwork.com ดู `docs/ui-design-th.md`) พร้อมแถบเมนูแคปซูลด้านบน เมนูล่างบนมือถือ และ Flash Alert แบบ toast เตือนสำเร็จ/ผิดพลาด
- `resources/views/auth/login.blade.php`
  - **อธิบาย**: หน้าฟอร์มเข้าสู่ระบบ พร้อมปุ่มไอคอนรูปดวงตา (👁️) เปิด-ปิดดูรหัสผ่าน
- `resources/views/auth/register.blade.php`
  - **อธิบาย**: หน้าฟอร์มสมัครสมาชิกนักศึกษา พร้อมระบุเงื่อนไขรหัสผ่านขั้นต่ำ 6 ตัวอักษร
- `resources/views/profile/edit.blade.php`
  - **อธิบาย**: หน้าจัดการโปรไฟล์ส่วนตัว สามารถอัปโหลดรูปภาพพร้อมระบบย่อรูปภาพอัตโนมัติ (Client-Side Canvas Auto-Resize)
- `resources/views/admin/users/index.blade.php`
  - **อธิบาย**: หน้า Admin Dashboard แสดงการ์ดสรุปสถิติ ตารางสมาชิก และปุ่มกด **ระงับบัญชี (Suspend) / เปิดใช้งาน (Activate)**
- `resources/views/errors/403.blade.php`
  - **อธิบาย**: หน้าแจ้งเตือนการไม่มีสิทธิ์เข้าถึง เมื่อผู้ใช้ทั่วไปพยายามเปิดหน้า Admin

---

### 4. บัญชีทดสอบสำหรับนำเสนออาจารย์ (Test Demo Accounts)

| บทบาท (Role) | อีเมล (Email) | รหัสผ่าน (Password) | สถานะ (Status) |
|---|---|---|---|
| 🎓 **นักศึกษาปกติ** | `student@unimate.ac.th` | `password` | 🟢 ปกติ (Active) |
| 🚫 **นักศึกษาถูกระงับ** | `suspended@unimate.ac.th` | `password` | 🔴 ถูกระงับ (Suspended) |
| 👑 **ผู้ดูแลระบบ (Admin)** | `admin@unimate.ac.th` | `password` | 🟢 ปกติ (Active) |

---

### 5. วิธีการติดตั้งและรันใช้งาน (Installation & Setup)

1. **ติดตั้ง Dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **สร้างไฟล์ `.env` และคีย์ระบบ**:
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

3. **รัน Migration และ Seeder สร้างตารางและข้อมูลทดสอบสำหรับเครื่องที่ติดตั้งใหม่**:
   ```bash
   php artisan migrate --seed
   ```

   หากมีฐานข้อมูลเดิมและต้องการเพิ่มเฉพาะส่วนกิจกรรม ให้ใช้คำสั่งอัปเดตในส่วนที่ 2 ด้านล่าง

4. **เชื่อมโยง Storage ลิงก์รูปภาพโปรไฟล์**:
   ```bash
   php artisan storage:link
   ```

5. **รันเซิร์ฟเวอร์ทดสอบ**:
   ```bash
   php artisan serve
   ```
   เปิดเบราว์เซอร์ไปที่: `http://127.0.0.1:8000/login`

---

## 📌 เอกสารรายละเอียด ส่วนที่ 2: ระบบประกาศและค้นหากิจกรรม (Part 2)

ส่วนที่ 2 รับผิดชอบเกี่ยวกับ **การสร้างโพสต์หาคนร่วมกิจกรรม, การแก้ไข/ยกเลิกโพสต์, กำหนดวันเวลา สถานที่ จำนวนคน, หน้ารายละเอียด, ค้นหาและกรองกิจกรรม และการจัดการหมวดหมู่** โดยเชื่อมกับบัญชีและสิทธิ์ผู้ใช้จากส่วนที่ 1

ลำดับงานที่ทำได้แล้ว: **สร้างโพสต์ → ค้นหาเจอด้วยตัวกรอง → เปิดรายละเอียด → แก้รายละเอียด → ตรวจว่าคนอื่นแก้โพสต์ไม่ได้**

---

### 1. ฟังก์ชันหลักของส่วนที่ 2 (Implemented Features)

| ฟังก์ชัน | รายละเอียดการทำงาน |
|---|---|
| **สร้างโพสต์กิจกรรม (Create)** | สมาชิกที่เข้าสู่ระบบและไม่ถูกระงับ สร้างชื่อกิจกรรม รายละเอียด หมวดหมู่ สถานที่ เวลาเริ่ม–สิ้นสุด และจำนวนคนที่รับได้ |
| **ค้นหาและกรอง (Search & Filter)** | ค้นจากชื่อหรือรายละเอียด กรองหมวดหมู่ สถานที่ วันที่เริ่ม และสถานะพร้อมกันได้ แบ่งหน้า 12 รายการและเก็บตัวกรองไว้เมื่อเปลี่ยนหน้า |
| **หน้ารายละเอียด (Detail)** | แสดงข้อมูลกิจกรรม ผู้ประกาศ วันเวลา สถานที่ จำนวนคน หมวดหมู่ และเวลาที่แก้ไขล่าสุด |
| **แก้ไขกิจกรรม (Update)** | เจ้าของโพสต์แก้ไขข้อมูลได้ โดยต้องผ่านกฎตรวจข้อมูลและสิทธิ์ที่เซิร์ฟเวอร์ |
| **ยกเลิกกิจกรรม (Cancel)** | เปลี่ยนสถานะเป็น `cancelled` เก็บโพสต์ไว้แสดงประวัติ เมื่อยกเลิกแล้วแก้ไขต่อไม่ได้ |
| **สิทธิ์เจ้าของโพสต์ (Ownership)** | คนอื่นรวมถึง Admin ไม่สามารถแก้ไขหรือยกเลิกโพสต์ของเจ้าของได้ แม้พิมพ์ URL หรือส่งคำขอเองจะได้ 403 |
| **จัดการหมวดหมู่ (Admin)** | เพิ่ม เปลี่ยนชื่อ และลบหมวดหมู่ ตรวจชื่อไม่ซ้ำ และห้ามลบหมวดหมู่ที่มีกิจกรรมใช้งาน |
| **เมนูและข้อความแจ้งเตือน** | เชื่อมเมนูกิจกรรม/สร้างโพสต์ เพิ่มเมนูมือถือ และแสดงผลสำเร็จหรือข้อผิดพลาดจากฟอร์ม |

**กติกาที่ทีมต้องทราบ**: `capacity` คือจำนวนคนที่รับเพิ่ม **ไม่รวมผู้ประกาศ** ยังไม่ใช่จำนวนผู้สมัครจริง ส่วน `published` หมายถึงประกาศแล้ว ไม่ได้หมายความว่ายังมีที่ว่างหรือยังไม่หมดเวลา

---

### 2. โครงสร้างฐานข้อมูล (Database Schema)

เพิ่มตาราง `categories` และ `activities` โดยใช้ `users` ของส่วนที่ 1 เป็นเจ้าของโพสต์

#### Table: `categories`

| คอลัมน์ (Column) | ประเภทข้อมูล (Type) | คำอธิบาย (Description) |
|---|---|---|
| `id` | BigInt (Auto Increment) | รหัสหมวดหมู่ |
| `name` | String(100), Unique | ชื่อหมวดหมู่ ห้ามซ้ำ |
| `created_at` / `updated_at` | Timestamps | วันเวลาที่สร้างและแก้ไข |

#### Table: `activities`

| คอลัมน์ (Column) | ประเภทข้อมูล (Type) | คำอธิบาย (Description) |
|---|---|---|
| `id` | BigInt (Auto Increment) | รหัสกิจกรรม ใช้อ้างอิงเมื่อเชื่อมระบบอื่น |
| `user_id` | Foreign ID → `users.id` | เจ้าของโพสต์ กำหนดจากบัญชีที่ล็อกอิน |
| `category_id` | Foreign ID → `categories.id` | หมวดหมู่กิจกรรม |
| `title` | String(200) | ชื่อกิจกรรม |
| `description` | Text | รายละเอียดกิจกรรม ฟอร์มรับได้ไม่เกิน 10,000 ตัวอักษร |
| `location` | String(255) | สถานที่นัดหมาย |
| `starts_at` | DateTime, Index | เวลาเริ่มกิจกรรม |
| `ends_at` | DateTime | เวลาสิ้นสุดกิจกรรม |
| `capacity` | Unsigned Integer | จำนวนคนที่รับเพิ่ม ฟอร์มรับจำนวนเต็ม 1–10,000 คน |
| `status` | String, Index | `published` (ค่าเริ่มต้น) หรือ `cancelled` |
| `created_at` / `updated_at` | Timestamps | วันเวลาที่สร้างและแก้ไข |

**ความสัมพันธ์**: User มีหลายกิจกรรม, Category มีหลายกิจกรรม และ Activity สังกัด User/Category อย่างละหนึ่งรายการ ในโค้ดใช้ `$activity->user`, `$activity->category` และ `$category->activities()` ได้ โดยยังไม่ได้เพิ่มเมธอด `activities()` ใน User Model

**Foreign keys**: การลบ User จะลบกิจกรรมของผู้ใช้นั้นตามด้วย (`cascadeOnDelete`) ส่วนหมวดหมู่ที่มีกิจกรรมอ้างอิงจะลบไม่ได้ (`restrictOnDelete`) รวมกิจกรรมที่ยกเลิกแล้วด้วย

---

### 3. รายละเอียดไฟล์ที่สร้างและแก้ไข (File Architecture & Code Explanations)

#### 📂 3.1 Database & Migrations

| ไฟล์ | หน้าที่ |
|---|---|
| `database/migrations/2026_09_17_000001_create_activity_tables.php` | สร้างตาราง categories/activities พร้อม foreign keys และ indexes |
| `database/seeders/CategorySeeder.php` | สร้างหมวดกีฬา ติวหนังสือ ท่องเที่ยว จิตอาสา ดนตรี และอื่น ๆ ด้วย `firstOrCreate` เรียกซ้ำได้ |
| `database/seeders/DatabaseSeeder.php` | เพิ่มการเรียก CategorySeeder ร่วมกับ UserSeeder เดิม |

#### 📂 3.2 Models, Request & Policy

| ไฟล์ | หน้าที่ |
|---|---|
| `app/Models/Activity.php` | Model กิจกรรม กำหนด `$fillable`, casts วันเวลา/จำนวนคน และความสัมพันธ์กับ User/Category |
| `app/Models/Category.php` | Model หมวดหมู่ และความสัมพันธ์ `hasMany` กับ Activity |
| `app/Http/Requests/SaveActivityRequest.php` | ตรวจสิทธิ์และข้อมูลร่วมกันทั้งสร้าง/แก้ไข เวลาเริ่มต้องอยู่ในอนาคต เวลาสิ้นสุดต้องหลังเวลาเริ่ม หมวดหมู่ต้องมีจริง |
| `app/Policies/ActivityPolicy.php` | เปรียบเทียบ ID ผู้ล็อกอินกับ `user_id` เจ้าของโพสต์ สำหรับสิทธิ์ `update` และ `cancel` |

#### 📂 3.3 Controllers, Routes & Config

| ไฟล์ | หน้าที่ |
|---|---|
| `app/Http/Controllers/ActivityController.php` | `index` ค้นหา/กรอง, `create` เปิดฟอร์ม, `store` บันทึก, `show` ดูรายละเอียด, `edit` เปิดแก้ไข, `update` บันทึกแก้ไข, `cancel` ยกเลิก |
| `app/Http/Controllers/Admin/CategoryController.php` | แสดงรายการ เพิ่ม เปลี่ยนชื่อ และลบหมวดหมู่ที่ยังไม่ถูกใช้ |
| `routes/web.php` | เพิ่ม routes กิจกรรมภายใต้ `auth`/`active` และ routes หมวดหมู่ภายใต้ `auth`/`active`/`admin` |
| `config/app.php` | เปลี่ยน timezone จาก UTC เป็น `Asia/Bangkok` เพื่อให้ฟอร์ม การตรวจเวลา และการแสดงผลใช้เวลาไทยทั้งแอป โดยไม่ได้แปลงข้อมูลเวลาเก่าย้อนหลัง |

#### 📂 3.4 Blade Views (หน้าจอระบบ)

| ไฟล์ | หน้าที่ |
|---|---|
| `resources/views/activities/index.blade.php` | ตัวกรอง การ์ดกิจกรรม จำนวนผลลัพธ์ และ pagination |
| `resources/views/activities/form.blade.php` | ฟอร์มร่วมสำหรับสร้างและแก้ไข แสดงข้อมูลเดิมด้วย `old()` เมื่อ validation ไม่ผ่าน |
| `resources/views/activities/show.blade.php` | รายละเอียด สถานะยกเลิก และปุ่มแก้ไข/ยกเลิกตามสิทธิ์ |
| `resources/views/activities/errors.blade.php` | แสดงข้อความ validation errors |
| `resources/views/admin/categories/index.blade.php` | หน้าจัดการหมวดหมู่สำหรับ Admin |
| `resources/views/layouts/app.blade.php` | เชื่อมเมนูกิจกรรม/สร้างโพสต์ เพิ่มเมนูมือถือและเมนูหมวดหมู่ |
| `resources/views/errors/403.blade.php` | ปรับข้อความให้ครอบคลุมทั้งการเข้าหน้า Admin และการไม่มีสิทธิ์แก้ไขกิจกรรม |

#### 📂 3.5 Tests & Documents

| ไฟล์ | หน้าที่ |
|---|---|
| `tests/Feature/ActivitiesTest.php` | ทดสอบ workflow กิจกรรม ตัวกรอง validation การปลอมเจ้าของ/สถานะ สิทธิ์บัญชีอื่น และการจัดการหมวดหมู่ |
| `docs/activities-guide-th.md` | อธิบายโค้ดทีละส่วนและขั้นตอนสาธิต |
| `docs/activities-handoff-th.md` | สรุปงานที่ทำแล้ว วิธีติดตั้ง และจุดเชื่อมต่อสำหรับทีม |

มีคอมเมนต์ภาษาไทยในจุดสำคัญ เช่น การจัดกลุ่ม OR ของคำค้น การกำหนดเจ้าของที่เซิร์ฟเวอร์ การตรวจ Policy และการเก็บโพสต์ที่ยกเลิกไว้เป็นประวัติ

---

### 4. เส้นทางใช้งานและการตรวจสิทธิ์ (Routes & Security)

| Method | URL | Route name | หน้าที่ |
|---|---|---|---|
| GET | `/activities` | `activities.index` | รายการและตัวกรอง |
| GET | `/activities/create` | `activities.create` | ฟอร์มสร้างโพสต์ |
| POST | `/activities` | `activities.store` | บันทึกโพสต์ใหม่ |
| GET | `/activities/{activity}` | `activities.show` | รายละเอียด |
| GET | `/activities/{activity}/edit` | `activities.edit` | ฟอร์มแก้ไขของเจ้าของ |
| PUT/PATCH | `/activities/{activity}` | `activities.update` | บันทึกการแก้ไข |
| PATCH | `/activities/{activity}/cancel` | `activities.cancel` | ยกเลิกโดยเจ้าของ |
| GET | `/admin/categories` | `admin.categories.index` | รายการหมวดหมู่ |
| POST | `/admin/categories` | `admin.categories.store` | เพิ่มหมวดหมู่ |
| PUT/PATCH | `/admin/categories/{category}` | `admin.categories.update` | เปลี่ยนชื่อหมวดหมู่ |
| DELETE | `/admin/categories/{category}` | `admin.categories.destroy` | ลบหมวดหมู่ที่ยังไม่ถูกใช้ |

**จุดสำคัญที่ใช้ป้องกันคนอื่นแก้โพสต์**:

```php
// app/Policies/ActivityPolicy.php
public function update(User $user, Activity $activity): bool
{
    return $user->id === $activity->user_id;
}
```

หน้าแก้ไขเรียก `Gate::authorize('update', $activity)` ส่วนคำขอบันทึกตรวจใน `SaveActivityRequest::authorize()` และคำขอยกเลิกเรียก `Gate::authorize('cancel', $activity)` จึงตรวจสิทธิ์ที่เซิร์ฟเวอร์ทุกทาง ไม่ได้ป้องกันแค่การซ่อนปุ่มด้วย `@can`

`user_id` และ `status` ไม่อยู่ใน `$fillable` และไม่อยู่ในข้อมูลที่ validation รับไว้ การสร้างโพสต์กำหนดเจ้าของจากบัญชีที่ล็อกอินและสถานะจาก Controller คนส่งฟอร์มจึงเปลี่ยนเจ้าของหรือสถานะเองไม่ได้

ทุกฟอร์มที่เปลี่ยนข้อมูลใช้ `@csrf` และแสดงข้อมูลที่ผู้ใช้กรอกผ่าน `{{ ... }}` เพื่อ escape HTML

---

### 5. วิธีอัปเดตสำหรับเพื่อนในทีม (Update & Setup)

หลังดึงโค้ดลงเครื่องและตั้งค่า dependencies/`.env` ตามส่วนที่ 1 แล้ว ให้รัน:

```bash
php artisan migrate
php artisan db:seed --class=CategorySeeder
```

คำสั่งนี้เพิ่มตารางและหมวดหมู่โดยไม่ล้างฐานข้อมูลเดิม **ไม่ต้องใช้ `migrate:fresh`** แต่ละคนต้องรันบนเครื่องของตัวเอง เพราะ Git ส่งโค้ด migration ไม่ได้อัปเดตฐานข้อมูลของเพื่อนให้อัตโนมัติ

เข้าสู่ระบบแล้วเปิด `/activities` หรือกดเมนู **กิจกรรม** และใช้ `/admin/categories` สำหรับผู้ดูแลระบบ ไม่มีการสร้างโพสต์กิจกรรมตัวอย่างอัตโนมัติ ให้สร้างผ่านฟอร์ม

---

### 6. วิธีทดสอบและสาธิต (Testing & Demo)

ใช้บัญชี Student/Admin ตามตารางส่วนที่ 1 และสมัครบัญชีนักศึกษาอีกหนึ่งบัญชีสำหรับทดสอบว่าแก้โพสต์คนอื่นไม่ได้

1. **สร้างโพสต์**: ล็อกอินบัญชี A → สร้างโพสต์ “ชวนเล่นแบดมินตัน” → เลือกหมวดกีฬา สถานที่ เวลาอนาคต และจำนวนคน → บันทึก
2. **ค้นหา**: กลับหน้ากิจกรรม → ค้นคำว่า “แบด” พร้อมกรองหมวด สถานที่ และวันที่ → พบโพสต์และเปิดรายละเอียด
3. **แก้ไข**: บัญชี A กดแก้ไข → เปลี่ยนสถานที่ → บันทึก → ตรวจว่าหน้ารายละเอียดแสดงค่าใหม่
4. **ทดสอบสิทธิ์**: จด URL `/activities/{id}/edit` → ออกจากระบบ → ล็อกอินบัญชี B → เปิดโพสต์เดิม พบว่าไม่มีปุ่มแก้ไข/ยกเลิก → เปิด URL แก้ไขโดยตรง ต้องได้ 403
5. **ยกเลิก**: กลับบัญชี A → ยกเลิก → หน้ารายละเอียดแสดง “ยกเลิกแล้ว” และค้นพบเมื่อกรองสถานะยกเลิก
6. **หมวดหมู่**: ล็อกอิน Admin → เพิ่ม/เปลี่ยนชื่อหมวดหมู่ → ทดลองลบหมวดหมู่ที่มีโพสต์ใช้งาน ต้องลบไม่ได้

รันทดสอบอัตโนมัติ:

```bash
php artisan test tests/Feature/ActivitiesTest.php
```

**ผลการตรวจสอบล่าสุดของงานส่วนนี้**: ผ่าน 14 กรณี รวม 88 assertions โดยใช้ SQLite ในหน่วยความจำ Laravel Pint ผ่าน และ Blade templates compile ได้

**ข้อจำกัดที่ทราบ**: ชุดทดสอบทั้งโปรเจกต์ยังมีปัญหาในระบบเดิม เช่น routes `dashboard`/`security.edit`/`register.store`/`login.store`, two-factor และ Vite manifest ของหน้า starter kit ส่วน PHPStan ยังรายงานข้อผิดพลาดในไฟล์ระบบสมาชิกเดิม แต่ไม่พบในไฟล์ PHP ใหม่ของระบบกิจกรรมหลังเพิ่ม memory limit ยังไม่ได้ตรวจหน้าจอผ่านเบราว์เซอร์จริง

---

### 7. จุดเชื่อมต่อและงานที่ทีมทำต่อ (Team Handoff)

| ส่วนที่ทำต่อ | ข้อมูลหรือจุดเชื่อมต่อที่มีแล้ว | สิ่งที่ยังต้องพัฒนา |
|---|---|---|
| **เข้าร่วมกิจกรรม** | `activities.id`, `user_id`, `capacity` | ตารางผู้เข้าร่วม สมัคร/ออก รายชื่อ และยอดผู้เข้าร่วมจริง |
| **อนุมัติผู้เข้าร่วม** | เจ้าของกิจกรรมผ่าน `$activity->user` | อนุมัติ/ปฏิเสธคำขอ และตรวจไม่ให้จำนวนคนเกิน |
| **นัดของฉัน** | เวลาเริ่ม/สิ้นสุด สถานที่ และสถานะ | เชื่อมกิจกรรมที่ผู้ใช้เข้าร่วม เมนูเดิมยังเป็น placeholder |
| **แชต/แจ้งเตือน/รีวิว** | รหัสกิจกรรมและเจ้าของ | ระบบข้อความ เตือนนัดหมาย และรีวิว |
| **รูปภาพ/แผนที่** | รายละเอียดและชื่อสถานที่ | อัปโหลดรูปกิจกรรม และพิกัดแผนที่ |

เมื่อเพิ่มระบบเข้าร่วม ให้ตรวจ `status`, เวลาเริ่ม และความจุที่เซิร์ฟเวอร์ การนับและบันทึกสมาชิกควรทำใน transaction เพื่อรองรับการสมัครพร้อมกัน ข้อนี้เป็นแนวทางสำหรับงานถัดไป ยังไม่ได้ implement ในส่วนที่ 2

โปรดรักษากติกาปัจจุบัน: เฉพาะเจ้าของแก้ไข/ยกเลิกได้ กิจกรรมที่ยกเลิกอ่านรายละเอียดได้แต่แก้ไขไม่ได้ (409) และการแก้ไขต้องกำหนดเวลาเริ่มในอนาคตตาม validation

อ่านรายละเอียดเพิ่มเติม: [คู่มือโค้ดส่วนกิจกรรม](docs/activities-guide-th.md) และ [เอกสารส่งต่องานสำหรับทีม](docs/activities-handoff-th.md)
