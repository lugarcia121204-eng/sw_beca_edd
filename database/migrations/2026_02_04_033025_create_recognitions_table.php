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
        Schema::create('recognitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Evaluator
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->enum('type', ['postulante', 'evaluator'])->default('evaluator'); // Distinguir docente vs evaluador
            $table->foreignId('template_id')->nullable()->constrained('templates')->onDelete('set null');
            $table->boolean('active')->default(false); // Switch estado
            $table->timestamp('sent_at')->nullable(); // When it was sent/activated
            $table->string('identifier')->nullable()->unique();
            $table->text('digital_seal')->nullable();
            $table->json('snapshot_data')->nullable();
            $table->timestamps();
            
            // An evaluator or teacher can only have one recognition per type per announcement
            $table->unique(['user_id', 'announcement_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recognitions');
    }
};
