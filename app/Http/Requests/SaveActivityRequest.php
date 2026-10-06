<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        $activity = $this->route('activity');

        // เส้นทางแก้ไขมี Activity จาก route model binding จึงต้องตรวจ Policy ของโพสต์นั้น
        // เส้นทางสร้างยังไม่มี Activity และมี middleware auth/active ตรวจบัญชีอีกชั้น
        return $activity ? $this->user()->can('update', $activity) : $this->user() !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        // ใช้กฎร่วมกันทั้งสร้างและแก้ไข เวลาอ้างอิง Asia/Bangkok ตาม config/app.php
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => 'กรุณากรอก :attribute',
            'starts_at.after' => 'เวลาเริ่มต้องเป็นเวลาในอนาคต',
            'ends_at.after' => 'เวลาสิ้นสุดต้องอยู่หลังเวลาเริ่ม',
            'capacity.min' => 'จำนวนคนต้องไม่น้อยกว่า 1 คน',
            'capacity.max' => 'จำนวนคนต้องไม่เกิน 10,000 คน',
            'category_id.exists' => 'กรุณาเลือกหมวดหมู่ที่มีอยู่ในระบบ',
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'ชื่อกิจกรรม', 'description' => 'รายละเอียด', 'category_id' => 'หมวดหมู่', 'location' => 'สถานที่', 'starts_at' => 'เวลาเริ่ม', 'ends_at' => 'เวลาสิ้นสุด', 'capacity' => 'จำนวนคน'];
    }
}
