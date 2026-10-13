<?php

namespace App\Services;

use App\Models\Evaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\NotificationService;
use Exception;

class AssignmentService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Asigna uno o más evaluadores a una solicitud específica en una transacción.
     *
     * @param int $applicationId El ID de la Application.
     * @param array $evaluatorIds Un arreglo de IDs de User (rol Evaluador).
     * @return void
     */
    public function assignEvaluators(int $applicationId, array $evaluatorIds): void
    {
        DB::transaction(function () use ($applicationId, $evaluatorIds) {
            $application = \App\Models\Application::lockForUpdate()->findOrFail($applicationId);
            foreach ($evaluatorIds as $userId) {
                $evaluator = \App\Models\User::findOrFail($userId);
                if (!$evaluator->canEvaluateApplication($application)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'evaluator_ids' => 'El evaluador no está autorizado para el ámbito de esta solicitud.',
                    ]);
                }
                // Verificamos que no exista una evaluación previa para ese dúo
                $exists = Evaluation::where('application_id', $applicationId)
                    ->where('evaluator_id', $userId)
                    ->exists();

                if ($exists) {
                    Evaluation::where('application_id', $applicationId)->where('evaluator_id', $userId)
                        ->where('status', 'pending')->whereNull('review_level')
                        ->update(['review_level' => $application->review_stage]);
                }
                if (!$exists) {
                    Evaluation::create([
                        'application_id' => $applicationId,
                        'evaluator_id'   => $userId,
                        'status'         => 'pending',
                        'review_level'   => $application->review_stage,
                        'deadline_at'    => Carbon::now()->addWeekdays(7),
                    ]);

                    // Contar cuántas evaluaciones tiene el evaluador
                    $evaluationsCount = Evaluation::where('evaluator_id', $userId)
                        ->where('status', 'pending')
                        ->count();

                    // Enviar notificación individual
                    $this->notificationService->notifyEvaluatorAssignment($userId, $evaluationsCount);
                }
            }
        });
    }

    /**
     * Remueve a un evaluador de una solicitud específica.
     *
     * @param int $applicationId
     * @param int $evaluatorId
     * @return void
     */
    public function removeEvaluator(int $applicationId, int $evaluatorId): void
    {
        DB::transaction(function () use ($applicationId, $evaluatorId) {
            \App\Models\Application::lockForUpdate()->findOrFail($applicationId);
            $evaluation = Evaluation::where('application_id', $applicationId)->where('evaluator_id', $evaluatorId)->firstOrFail();
            if (!in_array($evaluation->status, ['pending', 'expired'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['evaluator_id' => 'Una evaluación concluida forma parte del historial y no se puede retirar.']);
            }
            $evaluation->delete();
            app(ReviewWorkflowService::class)->advance($applicationId);
        });
    }
}
