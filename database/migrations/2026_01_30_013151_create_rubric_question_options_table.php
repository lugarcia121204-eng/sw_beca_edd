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
        Schema::create('rubric_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_question_id')->constrained('rubric_questions')->onDelete('cascade');
            $table->string('text');
            $table->integer('score');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rubric_question_options');
    }
};
