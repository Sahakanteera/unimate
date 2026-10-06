<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::table('activity_participants', function (Blueprint $table) {
        // เพิ่มคอลัมน์ attendance เก็บค่า present (มา) หรือ absent (ขาด) โดยให้ค่าเริ่มต้นว่างไว้ (ยังไม่เช็ก)
        $table->enum('attendance', ['present', 'absent'])->nullable()->after('status');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_participants', function (Blueprint $table) {
            //
        });
    }
};
