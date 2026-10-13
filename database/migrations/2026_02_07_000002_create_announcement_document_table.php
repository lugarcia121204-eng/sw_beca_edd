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
        Schema::create('announcement_document', function (Blueprint $table) {
            $table->id();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('catalog_document_id')->constrained('catalog_documents')->onDelete('cascade');
            $table->boolean('is_mandatory')->default(true);
            $table->enum('via', ['larga', 'corta', 'ambas'])->default('ambas');
            $table->timestamps();
            
            $table->unique(['announcement_id', 'catalog_document_id'], 'announcement_document_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_document');
    }
};
