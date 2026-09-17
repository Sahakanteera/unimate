# UniMate - เว็บแอปพลิเคชันระบบจับกลุ่มหาเพื่อนร่วมทำกิจกรรมในมหาวิทยาลัย

## ส่วนที่ 2: ระบบประกาศและค้นหากิจกรรม

สำหรับคนในทีม: อ่าน [สรุปงานที่ทำแล้ว วิธีติดตั้ง และจุดเชื่อมต่อที่ทำต่อได้](docs/activities-handoff-th.md) ก่อนเริ่มต่อระบบ

เพิ่มสร้าง/แก้ไข/ยกเลิกกิจกรรม หน้ารายละเอียด ตัวกรอง และจัดการหมวดหมู่ พร้อมตรวจสิทธิ์เจ้าของโพสต์ที่เซิร์ฟเวอร์ อ่านคำอธิบายโค้ด รายชื่อไฟล์ และขั้นตอนสาธิตได้ที่ [คู่มือส่วนกิจกรรม](docs/activities-guide-th.md)

อัปเดตฐานข้อมูลเดิมด้วย `php artisan migrate` และ `php artisan db:seed --class=CategorySeeder` แล้วเข้าสู่ระบบเพื่อเปิด `/activities`

**UniMate** เป็นระบบจับกลุ่มหาเพื่อนทำกิจกรรมต่างๆ ภายในมหาวิทยาลัย (เช่น เล่นกีฬา ติวหนังสือ ท่องเที่ยว) พัฒนาด้วย **Laravel Framework 11** ร่วมกับฐานข้อมูล **SQLite / MySQL**

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
  - **อธิบาย**: เรียกใช้ `UserSeeder::class` เมื่อสั่งรันคำสั่ง `php artisan db:seed`

#### 📂 3.2 Models & Middlewares
- `app/Models/User.php`
  - **อธิบาย**: Eloquent Model ของผู้ใช้ เพิ่ม `$fillable` คอลัมน์ใหม่ และสร้าง Helper Methods: `isAdmin()`, `isStudent()`, `isSuspended()`, `isActive()`, `initials()`
- `app/Http/Middleware/EnsureAdmin.php`
  - **อธิบาย**: Middleware ตรวจสอบสิทธิ์ Admin หากนักศึกษาทั่วไปพยายามแอบเข้าหน้า `/admin/*` จะบล็อกและแสดงหน้า 403 Access Denied
- `app/Http/Middleware/EnsureActiveAccount.php`
  - **อธิบาย**: Middleware ตรวจสอบสถานะบัญชี หากบัญชีถูกระงับ (`status = suspended`) จะทำการสั่ง Logout ให้อัตโนมัติพร้อมแจ้งเตือนสีแดง
- `bootstrap/app.php`
  - **อธิบาย**: ลงทะเบียน Middleware Alias `'admin'` และ `'active'` ให้ Laravel 11 เรียกใช้งาน

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
  - **อธิบาย**: Master Layout หลักของเว็บไซต์ พร้อม Top Navigation Bar สีเข้ม ธีมสีน้ำเงิน UniMate และ Flash Alert เตือนสำเร็จ/ผิดพลาด
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
   cp .env.example .env
   php artisan key:generate
   ```

3. **รัน Migration และ Seeder สร้างตารางและข้อมูลทดสอบ**:
   ```bash
   php artisan migrate:fresh --seed
   ```

4. **เชื่อมโยง Storage ลิงก์รูปภาพโปรไฟล์**:
   ```bash
   php artisan storage:link
   ```

5. **รันเซิร์ฟเวอร์ทดสอบ**:
   ```bash
   php artisan serve
   ```
   เปิดเบราว์เซอร์ไปที่: `http://127.0.0.1:8000/login`
