<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['กีฬา', 'ติวหนังสือ', 'ท่องเที่ยว', 'จิตอาสา', 'ดนตรี', 'อื่น ๆ'] as $name) {
            // เรียก seeder ซ้ำได้โดยไม่สร้างชื่อหมวดหมู่เดิมซ้ำ
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
