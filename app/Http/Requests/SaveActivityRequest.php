<?php

namespace App\Http\Requests;

use App\Rules\GoogleMapsUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class SaveActivityRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $image = $this->file('location_image');
        if ($image instanceof UploadedFile && ! $image->isValid()) {
            Log::warning('Activity image upload failed', ['error_code' => $image->getError()]);
        }
        foreach (['starts_at', 'ends_at'] as $field) {
            if ($this->has($field.'_date') || $this->has($field.'_time')) {
                $date = $this->input($field.'_date');
                $time = $this->input($field.'_time');
                $this->merge([$field => is_string($date) && is_string($time) ? $date.'T'.$time : '']);
            }
        }
    }

    public function authorize(): bool
    {
        $activity = $this->route('activity');

        // เส้นทางแก้ไขมี Activity จาก route model binding จึงต้องตรวจ Policy ของโพสต์นั้น
        // เส้นทางสร้างยังไม่มี Activity และมี middleware auth/active ตรวจบัญชีอีกชั้น
        return $activity ? $this->user()->can('update', $activity) : $this->user() !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        // ใช้กฎร่วมกันทั้งสร้างและแก้ไข เวลาอ้างอิง Asia/Bangkok ตาม config/app.php
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'location' => ['required', 'string', 'max:255'],
            'location_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_location_image' => ['sometimes', 'boolean'],
            'google_maps_url' => ['nullable', 'string', 'max:2048', 'url:https', new GoogleMapsUrl],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:now'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'starts_at_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'starts_at_time' => ['sometimes', 'required', 'date_format:H:i'],
            'ends_at_date' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'ends_at_time' => ['sometimes', 'required', 'date_format:H:i'],
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
            'location_image.image' => 'กรุณาเลือกไฟล์รูปภาพสถานที่',
            'location_image.mimes' => 'รูปสถานที่ต้องเป็น JPG, PNG หรือ WebP',
            'location_image.max' => 'รูปสถานที่ต้องมีขนาดไม่เกิน 5 MB',
            'location_image.uploaded' => 'อัปโหลดรูปสถานที่ไม่สำเร็จ กรุณาเลือกไฟล์อีกครั้งแล้วลองใหม่',
            'starts_at_time.date_format' => 'กรุณากรอกเวลาเริ่มแบบ 24 ชั่วโมง เช่น 14:30',
            'ends_at_time.date_format' => 'กรุณากรอกเวลาสิ้นสุดแบบ 24 ชั่วโมง เช่น 16:00',
            'google_maps_url.url' => 'กรุณาวางลิงก์สถานที่จาก Google Maps ที่ขึ้นต้นด้วย https://',
            'google_maps_url.max' => 'ลิงก์ Google Maps ต้องยาวไม่เกิน 2,048 ตัวอักษร',
        ];
    }

    public function attributes(): array
    {
        return ['title' => 'ชื่อกิจกรรม', 'description' => 'รายละเอียด', 'category_id' => 'หมวดหมู่', 'location' => 'สถานที่', 'starts_at' => 'เวลาเริ่ม', 'ends_at' => 'เวลาสิ้นสุด', 'starts_at_date' => 'วันที่เริ่ม', 'starts_at_time' => 'เวลาเริ่ม', 'ends_at_date' => 'วันที่สิ้นสุด', 'ends_at_time' => 'เวลาสิ้นสุด', 'capacity' => 'จำนวนคน'];
    }
}
