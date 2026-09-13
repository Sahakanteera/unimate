<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('student_id')->unique()->nullable()->after('name');
            $table->enum('role', ['student', 'admin'])->default('student')->after('password');
            $table->enum('status', ['active', 'suspended'])->default('active')->after('role');
            $table->text('bio')->nullable()->after('status');
            $table->string('avatar')->nullable()->after('bio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['student_id', 'role', 'status', 'bio', 'avatar']);
        });
    }
};
