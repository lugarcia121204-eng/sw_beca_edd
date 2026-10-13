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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->json('snapshot_data')->nullable();
            $table->enum('status', ['pending', 'in_review', 'approved', 'rejected'])->default('pending');
            $table->foreignId('position_type_id')->nullable()->constrained('position_types')->onDelete('set null');
            $table->foreignId('template_id')->nullable()->constrained('templates')->onDelete('set null');
            $table->text('admin_comment')->nullable();
            $table->string('identifier')->nullable()->unique(); // Folio único del PDF
            $table->text('digital_seal')->nullable(); // Sello digital firmado
            $table->enum('via', ['larga', 'corta'])->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
