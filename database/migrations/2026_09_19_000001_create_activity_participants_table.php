<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('pending'); // pending, approved, rejected, cancelled
            $table->text('message')->nullable();
            $table->timestamps();

            $table->unique(['activity_id', 'user_id']);
            $table->index(['activity_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_participants');
    }
};
