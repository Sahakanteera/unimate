<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // รีวิวคน (แยกจากรีวิวกิจกรรมในตาราง reviews) ผู้ที่ไปกิจกรรมจริงให้คะแนนกันเองหลังกิจกรรมจบ
        Schema::create('user_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->string('reviewee_role', 20); // host หรือ participant บันทึกตอนสร้าง
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            // 1 รีวิวต่อ (กิจกรรม, ผู้รีวิว, ผู้ถูกรีวิว)
            $table->unique(['activity_id', 'reviewer_id', 'reviewee_id']);
            // คะแนนในโปรไฟล์แยกตามบทบาท
            $table->index(['reviewee_id', 'reviewee_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_reviews');
    }
};
