<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{User, Application, Announcement, Calendar, Institution, State, Evaluation, Recognition};
use App\Services\{ReviewWorkflowService, AssignmentService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
class ReviewWorkflowTest extends TestCase {
 use RefreshDatabase;
 private function fixture(): array {
  Mail::fake();
  $state=State::create(['name'=>'Puebla','abbreviation'=>'PU']);
  $institution=Institution::create(['name'=>'Plantel','state_id'=>$state->id]);
  $teacher=User::factory()->create(['institution_id'=>$institution->id]);
  $announcement=Announcement::create(['name'=>'Prueba']);
  Calendar::create(['announcement_id'=>$announcement->id,'publication_start'=>now()->subDays(10),'registration_start'=>now()->subDays(9),'registration_end'=>now()->subDays(3),'evaluation_start'=>now()->subDays(2),'evaluation_end'=>now()->addDays(5),'results_start'=>now()->addDays(6),'results_end'=>now()->addDays(10)]);
  $application=Application::create(['user_id'=>$teacher->id,'announcement_id'=>$announcement->id]);
  Role::findOrCreate('Evaluador','web');
  $users=[];
  foreach(['local','state','national'] as $level) {
   $user=User::factory()->create(); $user->assignRole('Evaluador');
   $user->forceFill(['evaluator_status'=>'authorized','evaluator_level'=>$level,'evaluator_institution_id'=>$institution->id,'evaluator_state_id'=>$state->id])->save();
   $users[$level]=$user;
  }
  return [$application,$users];
 }
 private function submit($application,$user,$status='approved'): Evaluation {
  app(AssignmentService::class)->assignEvaluators($application->id,[$user->id]);
  $review=Evaluation::where('application_id',$application->id)->where('evaluator_id',$user->id)->firstOrFail();
  app(ReviewWorkflowService::class)->submit($review->id,['status'=>$status,'score'=>80,'answers'=>['1'=>'yes'],'comment'=>$status==='rejected'?'No cumple':null],$user);
  return $review->fresh();
 }
 public function test_three_levels_advance_and_only_national_creates_recognition(): void {
  [$application,$users]=$this->fixture();
  $local=$this->submit($application,$users['local']);
  $this->assertEquals('state',$application->fresh()->review_stage);
  $this->assertDatabaseCount('recognitions',0);
  $this->submit($application,$users['state']);
  $this->assertEquals('national',$application->fresh()->review_stage);
  $this->assertDatabaseCount('recognitions',0);
  $this->submit($application,$users['national']);
  $this->assertEquals('approved',$application->fresh()->status);
  $this->assertEquals('completed',$application->fresh()->review_stage);
  $this->assertDatabaseHas('recognitions',['user_id'=>$application->user_id,'active'=>true,'type'=>'postulante']);
  $this->assertEquals(3,Evaluation::where('application_id',$application->id)->count());
  $this->assertEquals('local',$local->review_level);
  $this->assertEquals(3, \App\Models\Notification::where('type','review_stage_changed')->count());
  $this->expectException(ValidationException::class);
  app(ReviewWorkflowService::class)->submit($local->id,['status'=>'rejected'],$users['local']);
 }
 public function test_rejection_stops_progress_and_future_level_cannot_be_assigned(): void {
  [$application,$users]=$this->fixture();
  $this->assertFalse($users['national']->canEvaluateApplication($application));
  $this->submit($application,$users['local'],'rejected');
  $this->assertEquals('rejected',$application->fresh()->status);
  $this->assertDatabaseCount('recognitions',0);
  $this->expectException(ValidationException::class);
  app(AssignmentService::class)->assignEvaluators($application->id,[$users['state']->id]);
 }
 public function test_all_assigned_reviewers_must_approve_and_completed_review_cannot_be_removed(): void {
  [$application,$users]=$this->fixture();
  $second=User::factory()->create(); $second->assignRole('Evaluador');
  $second->forceFill(['evaluator_level'=>'local','evaluator_status'=>'authorized','evaluator_institution_id'=>$users['local']->evaluator_institution_id])->save();
  app(AssignmentService::class)->assignEvaluators($application->id,[$second->id]);
  $this->submit($application,$users['local']);
  $this->assertEquals('local',$application->fresh()->review_stage);
  $this->submit($application,$second);
  $this->assertEquals('state',$application->fresh()->review_stage);
  $this->expectException(ValidationException::class);
  app(AssignmentService::class)->removeEvaluator($application->id,$users['local']->id);
 }
 public function test_admin_cannot_override_stage_workflow(): void {
  [$application,$users]=$this->fixture();
  Role::findOrCreate('Admin','web'); $admin=User::factory()->create(); $admin->assignRole('Admin');
  $this->actingAs($admin)->post(route('admin.applications.verdict',$application->id),['status'=>'approved'])->assertSessionHasErrors('status');
  $this->assertEquals('pending',$application->fresh()->status);
 }
}
