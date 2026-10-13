<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};
return new class extends Migration {
 public function up(): void {
  Schema::table('applications', fn(Blueprint $t) => $t->string('review_stage',20)->default('local')->index());
  Schema::table('evaluations', fn(Blueprint $t) => $t->string('review_level',20)->nullable()->index());
  // Keep historical decisions; legacy assignments must be reassigned explicitly.
  DB::table('applications')->whereIn('status',['approved','rejected'])->update(['review_stage'=>'completed']);
 }
 public function down(): void {
  Schema::table('evaluations',fn(Blueprint $t)=>$t->dropColumn('review_level'));
  Schema::table('applications',fn(Blueprint $t)=>$t->dropColumn('review_stage'));
 }
};
