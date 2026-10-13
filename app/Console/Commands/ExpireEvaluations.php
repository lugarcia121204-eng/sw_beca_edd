<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Evaluation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ExpireEvaluations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'evaluations:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Marca como expiradas las evaluaciones pendientes que han superado su fecha límite (7 días hábiles)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $this->info("Buscando evaluaciones pendientes vencidas antes de: {$now->toDateTimeString()}");
        Log::info("Cron Evaluaciones: Iniciando expiración de evaluaciones vencidas.");

        // Find pending evaluations where deadline_at has passed and the application is still pending
        $expiredEvaluations = Evaluation::where('status', 'pending')
            ->whereHas('application', function ($query) {
                $query->where('status', 'pending');
            })
            ->where('deadline_at', '<', $now)
            ->get();

        if ($expiredEvaluations->isEmpty()) {
            $this->info('No se encontraron evaluaciones vencidas.');
            Log::info('Cron Evaluaciones: No hay evaluaciones vencidas.');
            return Command::SUCCESS;
        }

        $count = $expiredEvaluations->count();
        $this->info("Se encontraron {$count} evaluaciones vencidas. Actualizando estado a 'expired'...");

        foreach ($expiredEvaluations as $evaluation) {
            Log::info("Cron Evaluaciones: Expirando evaluación ID {$evaluation->id} - Evaluador: {$evaluation->evaluator_id} - Solicitud: {$evaluation->application_id}");
            
            $evaluation->status = 'expired';
            $evaluation->save();
        }

        $this->info("✓ Se marcaron {$count} evaluaciones como expiradas correctamente.");
        Log::info("Cron Evaluaciones: Se marcaron {$count} evaluaciones como expiradas.");

        return Command::SUCCESS;
    }
}
