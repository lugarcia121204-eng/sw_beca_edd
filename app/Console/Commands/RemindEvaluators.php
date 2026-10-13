<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Evaluation;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RemindEvaluators extends Command
{
    protected $signature = 'evaluations:remind';

    protected $description = 'Envía recordatorio por correo a evaluadores con evaluaciones pendientes a punto de vencer (2 días hábiles o menos)';

    public function handle(NotificationService $notificationService): int
    {
        $now   = Carbon::now();
        $limit = Carbon::now()->addWeekdays(2)->endOfDay();

        $this->info("Buscando evaluaciones pendientes con deadline entre ahora y {$limit->toDateTimeString()}");

        // Buscar evaluaciones pending cuyo deadline vence en <= 2 días hábiles
        $proximasAVencer = Evaluation::where('status', 'pending')
            ->whereBetween('deadline_at', [$now, $limit])
            ->with('evaluator')
            ->get();

        if ($proximasAVencer->isEmpty()) {
            $this->info('No hay evaluaciones próximas a vencer.');
            return Command::SUCCESS;
        }

        // Agrupar por evaluador para enviar un solo correo con el total pendiente
        $porEvaluador = $proximasAVencer->groupBy('evaluator_id');

        foreach ($porEvaluador as $evaluatorId => $evaluations) {
            $pendingCount = $evaluations->count();

            // Calcular los días hábiles que le quedan (el mínimo de su evaluación más próxima)
            $closestDeadline = $evaluations->sortBy('deadline_at')->first()->deadline_at;
            $daysLeft = (int) max(0, $now->diffInWeekdays($closestDeadline));

            $notificationService->notifyEvaluatorReminder($evaluatorId, $pendingCount, $daysLeft);

            $this->info("Recordatorio enviado al evaluador ID {$evaluatorId} ({$pendingCount} pendientes, {$daysLeft} días hábiles restantes).");
            Log::info("Cron Recordatorio: Correo enviado al evaluador ID {$evaluatorId}. Pendientes: {$pendingCount}. Días restantes: {$daysLeft}.");
        }

        $this->info('Recordatorios enviados correctamente.');
        return Command::SUCCESS;
    }
}
