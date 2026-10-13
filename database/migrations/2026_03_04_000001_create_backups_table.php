<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable()->comment('Bytes');
            $table->enum('status', ['completed', 'failed', 'in_progress'])->default('in_progress');
            $table->enum('type', ['manual', 'scheduled'])->default('manual');
            $table->enum('backup_mode', ['full', 'incremental'])->default('full');
            $table->boolean('is_encrypted')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
