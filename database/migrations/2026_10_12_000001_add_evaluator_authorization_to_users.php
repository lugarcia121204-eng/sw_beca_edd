<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('evaluator_level', 20)->nullable();
            $table->string('evaluator_status', 20)->default('pending')->index();
            $table->foreignId('evaluator_institution_id')->nullable()->constrained('institutions')->restrictOnDelete();
            $table->foreignId('evaluator_state_id')->nullable()->constrained('states')->restrictOnDelete();
            $table->foreignId('evaluator_authorized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('evaluator_authorized_at')->nullable();
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evaluator_institution_id');
            $table->dropConstrainedForeignId('evaluator_state_id');
            $table->dropConstrainedForeignId('evaluator_authorized_by');
            $table->dropColumn(['evaluator_level', 'evaluator_status', 'evaluator_authorized_at']);
        });
    }
};
