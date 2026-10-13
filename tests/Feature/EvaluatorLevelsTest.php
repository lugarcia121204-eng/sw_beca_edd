<?php
namespace Tests\Feature;

use App\Models\{User, State, Institution, Application, Announcement, Evaluation};
use App\Services\AssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class EvaluatorLevelsTest extends TestCase
{
    use RefreshDatabase;

    private function account(string $role = 'Evaluador'): User
    {
        Role::findOrCreate($role, 'web');
        $user = User::factory()->create();
        $user->assignRole($role);
        return $user;
    }

    public function test_only_admin_can_configure_and_verified_evaluator_needs_a_scope(): void
    {
        $evaluator = $this->account();
        $url = route('admin.evaluators.configuration', $evaluator);
        $data = ['evaluator_status'=>'authorized', 'evaluator_level'=>'local'];
        $this->actingAs($evaluator)->put($url, $data)->assertForbidden();
        $admin = $this->account('Admin');
        $this->actingAs($admin)->put($url, $data)->assertSessionHasErrors('evaluator_institution_id');
        $evaluator->forceFill(['email_verified_at'=>null])->save();
        $this->put($url, ['evaluator_status'=>'authorized','evaluator_level'=>'national'])->assertSessionHasErrors('evaluator_status');
        $evaluator->forceFill(['email_verified_at'=>now()])->save();
        $this->put($url, ['evaluator_status'=>'authorized','evaluator_level'=>'national'])->assertSessionHasNoErrors();
        $this->assertTrue($evaluator->fresh()->isAuthorizedEvaluator());
        $this->assertEquals($admin->id, $evaluator->fresh()->evaluator_authorized_by);
        $this->put($url, ['evaluator_status'=>'suspended','evaluator_level'=>'national'])->assertSessionHasNoErrors();
        $this->assertFalse($evaluator->fresh()->isAuthorizedEvaluator());
    }

    public function test_pending_accounts_cannot_access_evaluation_routes_or_configure_other_accounts(): void
    {
        $user=$this->account();
        $this->actingAs($user)->get(route('evaluator.dashboard'))->assertRedirect(route('evaluator.pending'));
        $this->get(route('evaluator.pending'))->assertOk()->assertInertia(fn(Assert $page)=>$page->component('Evaluator/Pending',false));
        $this->put(route('evaluator.evaluation.update',123),[])->assertForbidden();
        $this->put('/evaluator/evaluators/'.$user->id.'/configuration', [])->assertNotFound();
    }

    public function test_local_state_and_national_scopes_and_self_review(): void
    {
        $a=State::create(['name'=>'Estado A','abbreviation'=>'AA']);
        $b=State::create(['name'=>'Estado B','abbreviation'=>'BB']);
        $institutions=collect([
            Institution::create(['name'=>'Plantel A','state_id'=>$a->id]),
            Institution::create(['name'=>'Plantel B','state_id'=>$a->id]),
            Institution::create(['name'=>'Plantel C','state_id'=>$b->id]),
        ]);
        $announcement=Announcement::create(['name'=>'Prueba']);
        $applications=$institutions->map(function($institution)use($announcement){
            $teacher=User::factory()->create(['institution_id'=>$institution->id]);
            return Application::create(['user_id'=>$teacher->id,'announcement_id'=>$announcement->id]);
        });
        $evaluator=$this->account();
        $evaluator->forceFill(['evaluator_status'=>'authorized','evaluator_level'=>'local','evaluator_institution_id'=>$institutions[0]->id])->save();
        foreach($applications as $application) Evaluation::create(['application_id'=>$application->id,'evaluator_id'=>$evaluator->id,'status'=>'pending']);
        $this->assertEquals([$applications[0]->id],Evaluation::visibleTo($evaluator)->pluck('application_id')->all());
        $this->assertFalse($evaluator->canEvaluateApplication($applications[1]));
        $this->actingAs($evaluator)->get(route('evaluator.evaluation.show',Evaluation::where('application_id',$applications[1]->id)->first()->id))->assertNotFound();
        $evaluator->forceFill(['evaluator_level'=>'state','evaluator_institution_id'=>null,'evaluator_state_id'=>$a->id])->save();
        $this->assertEquals(2,Evaluation::visibleTo($evaluator)->count());
        $this->assertFalse($evaluator->canEvaluateApplication($applications[2]));
        $evaluator->forceFill(['evaluator_level'=>'national','evaluator_state_id'=>null])->save();
        $this->assertEquals(3,Evaluation::visibleTo($evaluator)->count());
        $own=Application::create(['user_id'=>$evaluator->id,'announcement_id'=>$announcement->id]);
        $this->assertFalse($evaluator->canEvaluateApplication($own));
        $evaluator->forceFill(['evaluator_status'=>'suspended'])->save();
        $this->assertEquals(0,Evaluation::visibleTo($evaluator)->count());
    }

    public function test_assignment_rejects_pending_evaluator_before_creating_or_sending_anything(): void
    {
        Mail::fake();
        $teacher=User::factory()->create();
        $announcement=Announcement::create(['name'=>'Prueba']);
        $application=Application::create(['user_id'=>$teacher->id,'announcement_id'=>$announcement->id]);
        $evaluator=$this->account();
        try {
            app(AssignmentService::class)->assignEvaluators($application->id,[$evaluator->id]);
            $this->fail('Se permitió una asignación sin autorización.');
        } catch(ValidationException $e) {
            $this->assertArrayHasKey('evaluator_ids',$e->errors());
        }
        $this->assertDatabaseCount('evaluations',0);
        Mail::assertNothingOutgoing();
    }

    public function test_public_registration_cannot_self_authorize(): void
    {
        $this->post(route('register'),[
            'evaluator_status'=>'authorized','evaluator_level'=>'national','role_type'=>'evaluador',
        ])->assertSessionHasErrors(['evaluator_status','evaluator_level']);
    }

    public function test_same_registration_link_creates_pending_evaluator_and_queues_verification(): void
    {
        Mail::fake();
        Role::findOrCreate('Evaluador','web');
        $state=State::create(['name'=>'Registro','abbreviation'=>'RG']);
        $institution=Institution::create(['name'=>'Registro','state_id'=>$state->id]);
        $area=\App\Models\PriorityArea::create(['name'=>'Area']);
        $sub=\App\Models\SubArea::create(['name'=>'Subarea','priority_area_id'=>$area->id]);
        $this->get(route('register.evaluator'))->assertOk();
        $this->post(route('register'),[
            'curp'=>'TEST900101HDFABC01','name'=>'Nuevo evaluador','email'=>'nuevo@example.test',
            'password'=>'password','password_confirmation'=>'password','role_type'=>'evaluador',
            'institution_id'=>$institution->id,'priority_area_id'=>$area->id,'sub_area_id'=>$sub->id,
        ])->assertSessionHasNoErrors()->assertRedirect(route('verification.notice'));
        $user=User::where('email','nuevo@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('Evaluador'));
        $this->assertEquals('pending',$user->evaluator_status);
        $this->assertNull($user->evaluator_level);
        $this->assertFalse($user->isAuthorizedEvaluator());
        Mail::assertQueued(\App\Mail\VerificationCode::class,1);
    }

    public function test_management_lists_scope_and_saves_local_and_state_configuration(): void
    {
        $admin=$this->account('Admin');$evaluator=$this->account();
        $state=State::create(['name'=>'Estado','abbreviation'=>'EA']);
        $institution=Institution::create(['name'=>'Plantel','state_id'=>$state->id]);
        $url=route('admin.evaluators.configuration',$evaluator);
        $this->actingAs($admin)->put($url,['evaluator_status'=>'authorized','evaluator_level'=>'local','evaluator_institution_id'=>$institution->id])->assertSessionHasNoErrors();
        $this->get(route('admin.evaluators.index'))->assertOk()->assertInertia(fn(Assert $page)=>$page->component('Admin/Security/Evaluators/Index',false)->where('users.data.0.evaluator_scope','Plantel')->has('states',1)->has('institutions',1));
        $this->put($url,['evaluator_status'=>'authorized','evaluator_level'=>'state','evaluator_state_id'=>$state->id,'evaluator_institution_id'=>$institution->id])->assertSessionHasNoErrors();
        $this->assertNull($evaluator->fresh()->evaluator_institution_id);
        $this->assertEquals($state->id,$evaluator->fresh()->evaluator_state_id);
    }
}
