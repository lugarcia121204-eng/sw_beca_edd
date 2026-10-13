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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->onDelete('cascade');
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade');
            
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired'])->default('pending');
            $table->timestamp('deadline_at')->nullable();
            $table->decimal('score', 5, 2)->default(0);
            $table->json('answers')->nullable();
            $table->text('comment')->nullable();
            
            $table->timestamps();
            
            // An evaluator can only evaluate an application once
            $table->unique(['application_id', 'evaluator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
