<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('weekly');
            $table->time('run_time')->default('12:00:00');
            $table->boolean('email_notifications')->default(false);
            $table->boolean('is_active')->default(true);
            $table->enum('backup_mode', ['full', 'incremental'])->default('full');
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_schedules');
    }
};
