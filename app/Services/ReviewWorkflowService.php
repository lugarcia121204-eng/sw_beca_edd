<?php
namespace App\Services;
use App\Models\{Application, Evaluation, Recognition};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class ReviewWorkflowService
{
 public function submit(int $id, array $data, \App\Models\User $user): void
 {
  DB::transaction(function () use ($id,$data,$user) {
   $initial=Evaluation::findOrFail($id);
   $application=Application::lockForUpdate()->findOrFail($initial->application_id);
   $evaluation=Evaluation::visibleTo($user)->lockForUpdate()->findOrFail($id);
   if ($evaluation->status !== 'pending' || !$user->canEvaluateApplication($application) || $evaluation->review_level !== $application->review_stage) {
    throw ValidationException::withMessages(['status'=>'Esta evaluación no corresponde a la etapa actual o ya fue enviada.']);
   }
   if ($application->announcement->current_stage !== 'evaluacion') {
    throw ValidationException::withMessages(['status'=>'La convocatoria debe estar en Evaluación.']);
   }
   $evaluation->update(collect($data)->only(['status','score','answers','comment'])->all());
   $this->advance($application->id);
  });
 }
 public function advance(int $id): void
 {
  DB::transaction(function () use ($id) {
   $application=Application::lockForUpdate()->findOrFail($id);
   if ($application->status !== 'pending') return;
   $reviews=$application->evaluations()->where('review_level',$application->review_stage)->get();
   if ($reviews->isEmpty()) return;
   $rejected=$reviews->firstWhere('status','rejected');
   if ($rejected) {
    $application->update(['status'=>'rejected','admin_comment'=>$rejected->comment,'review_stage'=>'completed']);
   } elseif ($reviews->every(fn($review)=>$review->status === 'approved')) {
    $next=['local'=>'state','state'=>'national','national'=>'completed'][$application->review_stage];
    $application->update(['review_stage'=>$next,'status'=>$next === 'completed' ? 'approved' : 'pending']);
    if ($next === 'completed') {
     Recognition::updateOrCreate(['user_id'=>$application->user_id,'announcement_id'=>$application->announcement_id,'type'=>'postulante'],['active'=>true,'sent_at'=>now()]);
     // PDFs are prepared on download using the existing template and signature service.
    }
   } else return;
   $application->refresh();
   $user=$application->user;
   if ($user) {
    $labels=['local'=>'Local','state'=>'Estatal','national'=>'Nacional','completed'=>'Concluida'];
    \App\Models\Notification::create(['user_id'=>$user->id,'title'=>'Avance de evaluación','type'=>'review_stage_changed','data'=>['message'=>'Solicitud #'.$application->id.': '.$labels[$application->review_stage].'.','application_id'=>$application->id,'review_stage'=>$application->review_stage]]);
   }
   foreach (\App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'Admin'))->get() as $admin) {
    \App\Models\Notification::create(['user_id'=>$admin->id,'title'=>'Seguimiento de revisión','type'=>'review_stage_changed','data'=>['message'=>'Solicitud #'.$application->id.': '.$application->review_stage.'. Revisar asignación de la siguiente etapa.','application_id'=>$application->id]]);
   }
  });
 }
}

