<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'student@unimate.ac.th'],
            [
                'name' => 'Narin K. (นรินทร์)',
                'student_id' => '653020001-1',
                'password' => Hash::make('password'),
                'role' => 'student',
                'status' => 'active',
                'bio' => 'ชอบเล่นบาสเกตบอลช่วงเย็นวันจันทร์-พุธ และติวหนังสือเตรียมสอบวิชาคอมพิวเตอร์',
            ]
        );

        User::updateOrCreate(
            ['email' => 'suspended@unimate.ac.th'],
            [
                'name' => 'Ploy S. (พลอย)',
                'student_id' => '653020002-2',
                'password' => Hash::make('password'),
                'role' => 'student',
                'status' => 'suspended',
                'bio' => 'นักศึกษาสาขาการตลาด ชอบร่วมกิจกรรมวิ่งตอนเช้า',
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@unimate.ac.th'],
            [
                'name' => 'Admin User (ผู้ดูแลระบบ)',
                'student_id' => 'ADMIN-001',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'bio' => 'ผู้ดูแลระบบส่วนที่ 1 บริหารจัดการบัญชีนักศึกษาและการระงับสิทธิ์',
            ]
        );
    }
}
