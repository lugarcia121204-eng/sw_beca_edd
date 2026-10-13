<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class UpdateAnnouncementStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convocatoria:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza el estado de las convocatorias basado en las fechas del calendario';

    /**
     * Execute the console command.
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today(); // 00:00:00 of today

        $this->info("Ejecutando actualización de estados para fecha: {$today->toDateString()}");
        Log::info("Cron Convocatorias: Iniciando revisión para {$today->toDateString()}");

        // 1. Activar convocatorias pendientes cuya fecha de publicación ya llegó
        // Buscamos convocatorias pendientes que tengan calendario con publication_start <= hoy
        $pendientes = \App\Models\Announcement::where('status', 'pendiente')
            ->whereHas('calendar', function ($query) use ($today) {
                $query->where('publication_start', '<=', $today);
            })
            ->with('calendar')
            ->get();

        foreach ($pendientes as $announcement) {
            DB::transaction(function () use ($announcement) {
                // Desactivar cualquier otra activa para mantener regla de "Solo una activa"
                \App\Models\Announcement::where('status', 'activa')
                    ->where('id', '!=', $announcement->id)
                    ->update(['status' => 'cerrada']);

                $announcement->update(['status' => 'activa']);

                // Notificar nueva convocatoria
                $notificationService = app(NotificationService::class);
                $notificationService->notifyNewAnnouncement($announcement->id);

                $this->info("Convocatoria activada: {$announcement->name}");
                Log::info("Cron Convocatorias: Convocatoria ID {$announcement->id} activada.");
            });
        }

        // 1.1 Notificar inicio de etapa de REGISTRO
        $inicioRegistro = \App\Models\Announcement::where('status', 'activa')
            ->whereHas('calendar', function ($query) use ($today) {
                $query->whereDate('registration_start', $today);
            })
            ->with('calendar')
            ->get();

        foreach ($inicioRegistro as $announcement) {
            // Verificar que no se haya enviado ya esta notificación de etapa
            $yaNotifico = \App\Models\Notification::where('type', 'announcement_stage_change')
                ->where('data->stage', 'Registro de Solicitudes')
                ->where('data->announcement_id', $announcement->id)
                ->exists();

            if (!$yaNotifico) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyAnnouncementStageChange(
                    $announcement->id,
                    $announcement->name,
                    'Registro de Solicitudes',
                    $announcement->calendar->registration_end ?? null
                );
                $this->info("Notificación de etapa Registro enviada: {$announcement->name}");
                Log::info("Cron Convocatorias: Etapa Registro notificada para ID {$announcement->id}.");
            }
        }

        // 1.2 Notificar inicio de etapa de EVALUACIÓN
        $inicioEvaluacion = \App\Models\Announcement::where('status', 'activa')
            ->whereHas('calendar', function ($query) use ($today) {
                $query->whereDate('evaluation_start', $today);
            })
            ->with('calendar')
            ->get();

        foreach ($inicioEvaluacion as $announcement) {
            $yaNotifico = \App\Models\Notification::where('type', 'announcement_stage_change')
                ->where('data->stage', 'Evaluación de Solicitudes')
                ->where('data->announcement_id', $announcement->id)
                ->exists();

            if (!$yaNotifico) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyAnnouncementStageChange(
                    $announcement->id,
                    $announcement->name,
                    'Evaluación de Solicitudes',
                    $announcement->calendar->evaluation_end ?? null
                );
                $this->info("Notificación de etapa Evaluación enviada: {$announcement->name}");
                Log::info("Cron Convocatorias: Etapa Evaluación notificada para ID {$announcement->id}.");
            }
        }

        // 1.3 Notificar inicio de etapa de RESULTADOS
        $inicioResultados = \App\Models\Announcement::where('status', 'activa')
            ->whereHas('calendar', function ($query) use ($today) {
                $query->whereDate('results_start', $today);
            })
            ->with('calendar')
            ->get();

        foreach ($inicioResultados as $announcement) {
            $yaNotifico = \App\Models\Notification::where('type', 'announcement_stage_change')
                ->where('data->stage', 'Publicación de Resultados')
                ->where('data->announcement_id', $announcement->id)
                ->exists();

            if (!$yaNotifico) {
                $notificationService = app(NotificationService::class);
                $notificationService->notifyAnnouncementStageChange(
                    $announcement->id,
                    $announcement->name,
                    'Publicación de Resultados',
                    $announcement->calendar->results_end ?? null
                );
                $this->info("Notificación de etapa Resultados enviada: {$announcement->name}");
                Log::info("Cron Convocatorias: Etapa Resultados notificada para ID {$announcement->id}.");
            }
        }

        // 1.5 Enviar notificaciones de veredicto diferidas (ocultas a los docentes hasta etapa de resultados)
        $enResultados = \App\Models\Announcement::where('status', 'activa')
            ->whereHas('calendar', function ($query) use ($today) {
                // Etapa de resultados activa: hoy es mayor o igual a results_start
                $query->whereDate('results_start', '<=', $today)
                      ->whereDate('results_end', '>=', $today);
            })
            ->get();

        $notificationService = app(NotificationService::class);
        foreach ($enResultados as $announcement) {
            $solicitudesEvaluadas = \App\Models\Application::where('announcement_id', $announcement->id)
                ->whereIn('status', ['approved', 'rejected'])
                ->get();
            
            foreach ($solicitudesEvaluadas as $app) {
                // Revisar si ya tiene una push notification enviada por esa application_id
                $hasNotification = \App\Models\Notification::where('type', 'application_verdict')
                    ->where('user_id', $app->user_id)
                    ->where('data->application_id', $app->id)
                    ->exists();
                
                if (!$hasNotification) {
                    $notificationService->notifyApplicationVerdict(
                        $app->id,
                        $app->user_id,
                        $app->status,
                        $announcement->name
                    );
                    $this->info("Cron: Notificación diferida enviada para Solicitud {$app->id} ({$app->status}).");
                    Log::info("Cron Convocatorias: Notificación diferida enviada al Docente {$app->user_id} para Solicitud {$app->id}");
                }
            }

            // Send recognition available emails to teachers (Docentes) whose recognitions are active
            // but the notification hasn't been sent yet.
            // Evaluators (Evaluador role) are notified immediately when the Admin activates their recognition.
            $reconocimientos = \App\Models\Recognition::with('user')
                ->where('announcement_id', $announcement->id)
                ->where('active', true)
                ->get();

            foreach ($reconocimientos as $rec) {
                // Skip evaluators — they are notified at activation time by the Admin
                if ($rec->user && $rec->user->hasRole('Evaluador')) {
                    continue;
                }

                $yaNotificoReconocimiento = \App\Models\Notification::where('type', 'recognition_available')
                    ->where('user_id', $rec->user_id)
                    ->where('data->announcement_id', $announcement->id)
                    ->exists();

                if (!$yaNotificoReconocimiento) {
                    $notificationService->notifyRecognitionAvailable(
                        $rec->user_id,
                        $announcement->id,
                        $announcement->name
                    );
                    $this->info("Cron: Reconocimiento disponible notificado al Docente {$rec->user_id}.");
                    Log::info("Cron Convocatorias: Reconocimiento notificado al Docente {$rec->user_id} para Convocatoria {$announcement->id}");
                }
            }
        }

        // 2. Cerrar convocatorias activas cuya fecha de resultados ya pasó
        // Buscamos activas que tengan calendario con results_end < hoy
        // (Es decir, ayer fue el último día)
        $activas = \App\Models\Announcement::where('status', 'activa')
            ->whereHas('calendar', function ($query) use ($today) {
                $query->where('results_end', '<', $today);
            })
            ->get();

        foreach ($activas as $announcement) {
            $announcement->update(['status' => 'cerrada']);

            // Notificar cierre definitivo de la convocatoria
            $notificationService = app(NotificationService::class);
            $notificationService->notifyAnnouncementClosed(
                $announcement->id,
                $announcement->name,
                $announcement->calendar->results_end ?? null
            );

            $this->info("Convocatoria cerrada automáticamente: {$announcement->name}");
            Log::info("Cron Convocatorias: Convocatoria ID {$announcement->id} cerrada automáticamente (período de resultados finalizado).");
        }

        $this->info('Actualización completada.');
    }
}
