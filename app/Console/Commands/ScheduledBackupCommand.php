<?php

namespace App\Console\Commands;

use App\Mail\BackupCompleted;
use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ScheduledBackupCommand extends Command
{
    protected $signature = 'backup:run-scheduled';

    protected $description = 'Run the scheduled database backup based on configured frequency';

    public function handle(): int
    {
        $schedule = BackupSchedule::getConfig();

        if (!$schedule->is_active) {
            $this->info('Scheduled backup is inactive — skipping.');
            return self::SUCCESS;
        }

        // Check if it is time to run
        if ($schedule->next_run_at && Carbon::now()->lt($schedule->next_run_at)) {
            $this->info('Not yet time to run scheduled backup. Next run: ' . $schedule->next_run_at);
            return self::SUCCESS;
        }

        $this->info('Running scheduled backup...');

        $backupName = 'Scheduled Backup';
        $mode = $schedule->backup_mode ?? 'full';

        $backupRecord = Backup::create([
            'name'        => $backupName,
            'description' => 'Respaldo automático - ' . now()->format('d/m/Y H:i'),
            'status'      => 'in_progress',
            'type'        => 'scheduled',
            'backup_mode' => $mode,
            'created_by'  => null,
        ]);

        try {
            $dbConfig  = config('database.connections.' . config('database.default'));
            $host      = $dbConfig['host'];
            $port      = $dbConfig['port'] ?? 3306;
            $user      = $dbConfig['username'];
            $pass      = $dbConfig['password'];
            $dbName    = $dbConfig['database'];
            $dumpBin   = $this->findMysqldumpBinary();

            $dir         = 'backups';
            $modeSuffix  = $mode === 'incremental' ? '_incremental' : '_full';
            $filename    = date('Y_m_d_His') . $modeSuffix . '_scheduled.sql';
            $storagePath = $dir . '/' . $filename;
            $fullPath    = Storage::disk('local')->path($storagePath);

            if (!Storage::disk('local')->exists($dir)) {
                Storage::disk('local')->makeDirectory($dir);
            }

            if ($mode === 'incremental') {
                // ── Incremental: data-only rows changed since last full backup ──
                $lastFull = Backup::where('status', 'completed')
                    ->where('backup_mode', 'full')
                    ->latest()
                    ->first();

                if (!$lastFull) {
                    throw new \RuntimeException(
                        'No existe un respaldo completo previo. El incremental programado requiere un Full como línea base.'
                    );
                }

                $since  = $lastFull->created_at->format('Y-m-d H:i:s');
                $header = "-- RESPALDO INCREMENTAL PROGRAMADO\n-- Desde: {$since}\n-- Generado: " . date('Y-m-d H:i:s') . "\nSET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n";
                file_put_contents($fullPath, $header);

                $tables = array_column(
                    array_map(
                        fn($t) => (array)$t,
                        DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME", [$dbName])
                    ),
                    'TABLE_NAME'
                );

                foreach ($tables as $table) {
                    $timeCols = array_column(
                        array_map(
                            fn($c) => (array)$c,
                            DB::select("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME IN ('created_at','updated_at')", [$dbName, $table])
                        ),
                        'COLUMN_NAME'
                    );

                    $whereArg = !empty($timeCols)
                        ? '--where="' . implode(' OR ', array_map(fn($c) => "`{$c}` >= '{$since}'", $timeCols)) . '"'
                        : '';

                    $tblCmd = sprintf(
                        '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --no-create-info --insert-ignore --skip-add-drop-table --skip-triggers %s %s %s >> "%s" 2>&1',
                        $dumpBin, $host, $port, $user, escapeshellarg($pass), $whereArg, $dbName, $table, $fullPath
                    );
                    exec($tblCmd, $tblOut, $tblCode);
                    if ($tblCode !== 0) {
                        throw new \RuntimeException("Incremental falló en `{$table}`: " . implode(' | ', $tblOut));
                    }
                }

                file_put_contents($fullPath, "\nSET FOREIGN_KEY_CHECKS=1;\n", FILE_APPEND);

                if (!file_exists($fullPath) || filesize($fullPath) < 100) {
                    throw new \RuntimeException('El archivo de respaldo incremental está vacío o es inválido.');
                }
            } else {
                // ── Full backup ──────────────────────────────────────────────
                $command = sprintf(
                    '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --events --add-drop-table %s > "%s" 2>&1',
                    $dumpBin, $host, $port, $user, escapeshellarg($pass), $dbName, $fullPath
                );
                exec($command, $output, $returnCode);
                if ($returnCode !== 0) {
                    throw new \RuntimeException(implode(' | ', $output));
                }
            }

            $backupRecord->update([
                'file_path'  => $storagePath,
                'file_size'  => file_exists($fullPath) ? filesize($fullPath) : null,
                'status'     => 'completed',
            ]);

            // Update last/next run timestamps
            $schedule->last_run_at = now();
            $schedule->next_run_at = $schedule->calculateNextRun();
            $schedule->save();

            // Send notification email if enabled
            if ($schedule->email_notifications) {
                $backupRecord->refresh();
                User::role('Super Admin')->each(fn ($u) => Mail::to($u)->queue(new BackupCompleted($backupRecord)));
            }

            $this->info('Scheduled backup completed: ' . $storagePath);
            Log::info('Scheduled backup completed: ' . $storagePath);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $backupRecord->update(['status' => 'failed']);
            Log::error('Scheduled backup failed: ' . $e->getMessage());
            $this->error('Scheduled backup failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function findMysqldumpBinary(): string
    {
        $laragonBase = 'D:/Lalo104lucky/Descargas/Aplicaciones/laragon/bin/mysql';
        if (is_dir($laragonBase)) {
            foreach (glob($laragonBase . '/*/bin/mysqldump.exe') as $found) {
                if (file_exists($found)) return $found;
            }
        }

        $candidates = [
            'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe',
            'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysqldump.exe',
            'C:/xampp/mysql/bin/mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) return $path;
        }

        return 'mysqldump';
    }
}
