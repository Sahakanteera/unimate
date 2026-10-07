<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ส่วนที่ 5: รีวิวกิจกรรมหลังจบ (1 คนรีวิวได้ 1 ครั้งต่อกิจกรรม)
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['activity_id', 'user_id']);
        });

        // รายงานผู้ใช้หรือกิจกรรม target_type เป็น activity หรือ user
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id');
            $table->text('reason');
            $table->string('status', 20)->default('pending'); // pending, actioned, dismissed
            $table->text('admin_note')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index('status');
        });

        // ประวัติการดำเนินการของ Admin พร้อมเหตุผล
        Schema::create('moderation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30); // hide_activity, unhide_activity, suspend_user, dismiss
            $table->string('target_type', 20);
            $table->unsignedBigInteger('target_id');
            $table->text('reason');
            $table->timestamps();
        });

        // ซ่อนกิจกรรมโดย Admin แยกจาก status เดิม (published/cancelled) ของส่วนที่ 2
        Schema::table('activities', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable();
            $table->text('hidden_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn(['hidden_at', 'hidden_reason']);
        });
        Schema::dropIfExists('moderation_logs');
        Schema::dropIfExists('reports');
        Schema::dropIfExists('reviews');
    }
};
