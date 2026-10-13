<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\BackupCompleted;
use App\Models\Backup;
use App\Models\BackupSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class BackupController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // INDEX – pantalla principal
    // ─────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $rows   = (int) $request->input('rows', 10);

        $query = Backup::with('creator')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('creator', fn($u) => $u->where('name', 'like', "%{$search}%"));
            })
            ->latest();

        $backups = $query->paginate($rows)->withQueryString();

        // Stats
        $lastBackup  = Backup::where('status', 'completed')->latest()->first();
        $totalCount  = Backup::where('status', 'completed')->count();
        $schedule    = BackupSchedule::getConfig();

        // Format audit rows
        $backups->getCollection()->transform(function ($b) {
            return [
                'id'          => $b->id,
                'action'      => $b->type === 'scheduled' ? 'Programación automática' : 'Respaldo manual',
                'user'        => $b->creator?->name ?? 'Administrador',
                'name'        => $b->name,
                'date'        => $b->created_at->format('d-m-Y H:i:s'),
                'status'      => $b->status,
                'result'      => $b->status === 'completed' ? 'Realizado' : ($b->status === 'failed' ? 'Error' : 'En progreso'),
                'size'        => $b->file_size_formatted,
                'backup_mode' => $b->backup_mode ?? 'full',
            ];
        });

        return Inertia::render('SuperAdmin/Backup/Index', [
            'backups'    => $backups,
            'filters'    => compact('search', 'rows'),
            'stats'      => [
                'last_backup'  => $lastBackup?->created_at->format('d-m-Y H:i:s') ?? '—',
                'next_backup'  => $schedule->next_run_at?->format('d-m-Y H:i') ?? '—',
                'total'        => $totalCount,
            ],
            'schedule'   => [
                'frequency'           => $schedule->frequency,
                'run_time'            => substr($schedule->run_time, 0, 5),
                'email_notifications' => $schedule->email_notifications,
                'is_active'           => $schedule->is_active,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE – ejecutar respaldo manual
    // ─────────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'encrypted'   => 'boolean',
            'backup_mode' => 'in:full,incremental',
        ]);

        $mode = $request->input('backup_mode', 'full');

        /** @var \App\Models\Backup $backupRecord */
        $backupRecord = Backup::create([
            'name'         => $request->name,
            'description'  => $request->description,
            'status'       => 'in_progress',
            'type'         => 'manual',
            'backup_mode'  => $mode,
            'is_encrypted' => $request->boolean('encrypted'),
            'created_by'   => Auth::id(),
        ]);

        try {
            $filePath = $this->runMysqldump($request->name, $request->boolean('encrypted'), $mode);

            $backupRecord->update([
                'file_path'  => $filePath,
                'file_size'  => Storage::disk('local')->size($filePath),
                'status'     => 'completed',
            ]);

            // Send notification email if enabled
            $schedule = BackupSchedule::getConfig();
            if ($schedule->email_notifications) {
                $backupRecord->refresh();
                User::role('Super Admin')->each(fn ($u) => Mail::to($u)->queue(new BackupCompleted($backupRecord)));
            }

            return redirect()->route('superadmin.backup.index')
                ->with('success', 'Respaldo creado correctamente.')
                ->with('download_backup_id', $backupRecord->id);

        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            $backupRecord->update(['status' => 'failed']);

            return redirect()->route('superadmin.backup.index')
                ->with('error', 'Error al crear el respaldo: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // SCHEDULE – guardar configuración de programación
    // ─────────────────────────────────────────────────────────────────────

    public function updateSchedule(Request $request)
    {
        $request->validate([
            'frequency'           => 'required|in:daily,weekly,monthly',
            'run_time'            => 'required|date_format:H:i',
            'email_notifications' => 'boolean',
            'is_active'           => 'boolean',
            'backup_mode'         => 'in:full,incremental',
        ]);

        $schedule = BackupSchedule::getConfig();
        $schedule->fill([
            'frequency'           => $request->frequency,
            'run_time'            => $request->run_time . ':00',
            'email_notifications' => $request->boolean('email_notifications'),
            'is_active'           => $request->boolean('is_active'),
            'backup_mode'         => $request->input('backup_mode', 'full'),
        ]);
        $schedule->next_run_at = $schedule->calculateNextRun();
        $schedule->save();

        return redirect()->route('superadmin.backup.index')
            ->with('success', 'Programación actualizada correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESTORE – restaurar desde un respaldo
    // ─────────────────────────────────────────────────────────────────────

    public function restore(Request $request)
    {
        $request->validate([
            'backup_id' => 'required|exists:backups,id',
        ]);

        $backup = Backup::findOrFail($request->backup_id);

        if (!$backup->file_path || !Storage::disk('local')->exists($backup->file_path)) {
            return redirect()->route('superadmin.backup.index')
                ->with('error', 'El archivo de respaldo no se encontró en el servidor.');
        }

        $tempDecryptedPath = null;

        try {
            $dbConfig  = config('database.connections.' . config('database.default'));
            $host      = $dbConfig['host'];
            $port      = $dbConfig['port'] ?? 3306;
            $user      = $dbConfig['username'];
            $pass      = $dbConfig['password'];
            $dbName    = $dbConfig['database'];
            $filePath  = Storage::disk('local')->path($backup->file_path);
            $mysqlPath = $this->findMysqlClientBinary();

            // ── Decrypt if the backup was encrypted ─────────────────────
            if ($backup->is_encrypted) {
                $rawKey = config('app.key');
                if (str_starts_with($rawKey, 'base64:')) {
                    $rawKey = base64_decode(substr($rawKey, 7));
                }
                $rawKey = substr(str_pad($rawKey, 32, "\0"), 0, 32);

                $encData   = file_get_contents($filePath);
                $iv        = substr($encData, 0, 16);
                $cipher    = substr($encData, 16);
                $plainData = openssl_decrypt($cipher, 'AES-256-CBC', $rawKey, OPENSSL_RAW_DATA, $iv);

                if ($plainData === false) {
                    throw new \RuntimeException('Error al descifrar el respaldo: ' . openssl_error_string());
                }

                $tempDecryptedPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('backup_restore_', true) . '.sql';
                file_put_contents($tempDecryptedPath, $plainData);
                $filePath = $tempDecryptedPath;
            }

            $command = sprintf(
                '"%s" --host=%s --port=%s --user=%s --password=%s %s < "%s" 2>&1',
                $mysqlPath, $host, $port, $user, escapeshellarg($pass), $dbName, $filePath
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                // Sanitize output: strip binary/non-UTF-8 bytes so JSON encoding won't fail
                $errorMsg = mb_scrub(implode("\n", $output), 'UTF-8');
                throw new \RuntimeException($errorMsg);
            }

            Log::info('Database restored from backup: ' . $backup->name . ' by user ' . Auth::id());

            if ($tempDecryptedPath && file_exists($tempDecryptedPath)) {
                @unlink($tempDecryptedPath);
            }

            return redirect()->route('superadmin.backup.index')
                ->with('success', 'Base de datos restaurada correctamente desde "' . $backup->name . '".');

        } catch (\Exception $e) {
            if ($tempDecryptedPath && file_exists($tempDecryptedPath)) {
                @unlink($tempDecryptedPath);
            }
            Log::error('Restore failed: ' . $e->getMessage());
            return redirect()->route('superadmin.backup.index')
                ->with('error', 'Error al restaurar la base de datos: ' . mb_scrub($e->getMessage(), 'UTF-8'));
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESTORE FROM UPLOAD – restaurar desde archivo subido por el usuario
    // ─────────────────────────────────────────────────────────────────────

    public function restoreFromUpload(Request $request)
    {
        $request->validate([
            'backup_file' => [
                'required',
                'file',
                'max:204800', // 200 MB
                function ($attribute, $value, $fail) {
                    $name = strtolower($value->getClientOriginalName());
                    if (!str_ends_with($name, '.sql') && !str_ends_with($name, '.sql.enc') && !str_ends_with($name, '.enc')) {
                        $fail('El archivo debe tener extensión .sql o .sql.enc.');
                    }
                },
            ],
        ]);

        $file          = $request->file('backup_file');
        $originalName  = $file->getClientOriginalName();
        $isEncrypted   = str_ends_with(strtolower($originalName), '.enc');

        // Store upload in a temp location inside local storage
        $tempStorePath = $file->store('temp_restores', 'local');
        $fullTempPath  = Storage::disk('local')->path($tempStorePath);
        $tempDecryptedPath = null;

        try {
            $dbConfig  = config('database.connections.' . config('database.default'));
            $host      = $dbConfig['host'];
            $port      = $dbConfig['port'] ?? 3306;
            $user      = $dbConfig['username'];
            $pass      = $dbConfig['password'];
            $dbName    = $dbConfig['database'];
            $mysqlPath = $this->findMysqlClientBinary();
            $filePath  = $fullTempPath;

            if ($isEncrypted) {
                $rawKey = config('app.key');
                if (str_starts_with($rawKey, 'base64:')) {
                    $rawKey = base64_decode(substr($rawKey, 7));
                }
                $rawKey = substr(str_pad($rawKey, 32, "\0"), 0, 32);

                $encData   = file_get_contents($filePath);
                $iv        = substr($encData, 0, 16);
                $cipher    = substr($encData, 16);
                $plainData = openssl_decrypt($cipher, 'AES-256-CBC', $rawKey, OPENSSL_RAW_DATA, $iv);

                if ($plainData === false) {
                    throw new \RuntimeException('Error al descifrar el archivo: ' . openssl_error_string());
                }

                $tempDecryptedPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('backup_upload_', true) . '.sql';
                file_put_contents($tempDecryptedPath, $plainData);
                $filePath = $tempDecryptedPath;
            }

            $command = sprintf(
                '"%s" --host=%s --port=%s --user=%s --password=%s %s < "%s" 2>&1',
                $mysqlPath, $host, $port, $user, escapeshellarg($pass), $dbName, $filePath
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $errorMsg = mb_scrub(implode("\n", $output), 'UTF-8');
                throw new \RuntimeException($errorMsg);
            }

            Log::info('Database restored from uploaded file: ' . $originalName . ' by user ' . Auth::id());

            return redirect()->route('superadmin.backup.index')
                ->with('success', 'Base de datos restaurada correctamente desde el archivo "' . $originalName . '".');

        } catch (\Exception $e) {
            Log::error('Restore from upload failed: ' . $e->getMessage());
            return redirect()->route('superadmin.backup.restore-view')
                ->with('error', 'Error al restaurar la base de datos: ' . mb_scrub($e->getMessage(), 'UTF-8'));
        } finally {
            if ($tempDecryptedPath && file_exists($tempDecryptedPath)) {
                @unlink($tempDecryptedPath);
            }
            Storage::disk('local')->delete($tempStorePath);
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // DOWNLOAD – descargar archivo .sql
    // ─────────────────────────────────────────────────────────────────────

    public function download(Backup $backup)
    {
        if (!$backup->file_path || !Storage::disk('local')->exists($backup->file_path)) {
            return redirect()->route('superadmin.backup.index')
                ->with('error', 'El archivo de respaldo no se encontró.');
        }

        $extension    = $backup->is_encrypted ? '.sql.enc' : '.sql';
        $downloadName = Str::slug($backup->name) . '_' . $backup->created_at->format('Y-m-d_H-i') . $extension;

        return Storage::disk('local')->download($backup->file_path, $downloadName);
    }

    // ─────────────────────────────────────────────────────────────────────
    // DESTROY – eliminar registro y archivo
    // ─────────────────────────────────────────────────────────────────────

    public function destroy(Backup $backup)
    {
        if ($backup->file_path && Storage::disk('local')->exists($backup->file_path)) {
            Storage::disk('local')->delete($backup->file_path);
        }
        $backup->delete();

        return redirect()->route('superadmin.backup.index')
            ->with('success', 'Respaldo eliminado correctamente.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // VIEW METHODS (GET pages)
    // ─────────────────────────────────────────────────────────────────────

    public function create()
    {
        $hasFullBackup = Backup::where('status', 'completed')->where('backup_mode', 'full')->exists();
        return Inertia::render('SuperAdmin/Backup/Create', [
            'hasFullBackup' => $hasFullBackup,
        ]);
    }

    public function editSchedule()
    {
        $schedule = BackupSchedule::getConfig();
        return Inertia::render('SuperAdmin/Backup/Edit', [
            'hasFullBackup' => Backup::where('status', 'completed')->where('backup_mode', 'full')->exists(),
            'schedule' => [
                'frequency'           => $schedule->frequency,
                'run_time'            => substr($schedule->run_time, 0, 5),
                'email_notifications' => $schedule->email_notifications,
                'is_active'           => $schedule->is_active,
                'backup_mode'         => $schedule->backup_mode ?? 'full',
                'next_run_at'         => $schedule->next_run_at?->format('d-m-Y H:i') ?? null,
                'frequency_label'     => $schedule->frequency_label,
            ],
        ]);
    }

    public function showRestore()
    {
        $backupList = Backup::where('status', 'completed')
            ->latest()
            ->get(['id', 'name', 'created_at', 'file_size', 'type', 'backup_mode'])
            ->map(fn($b) => [
                'id'    => $b->id,
                'name'  => $b->name,
                'label' => $b->name . ' — ' . $b->created_at->format('d/m/Y H:i'),
                'date'  => $b->created_at->format('d/m/Y H:i'),
                'size'  => $b->file_size_formatted,
                'type'  => $b->type === 'scheduled' ? 'Automático' : 'Manual',
                'mode'  => $b->backup_mode === 'incremental' ? 'Incremental' : 'Completa',
            ])
            ->all();

        return Inertia::render('SuperAdmin/Backup/Restore', compact('backupList'));
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Runs mysqldump and stores the .sql file.
     * Returns the storage-relative path.
     */
    private function runMysqldump(string $backupName, bool $encrypted = false, string $mode = 'full'): string
    {
        $dbConfig  = config('database.connections.' . config('database.default'));
        $host      = $dbConfig['host'];
        $port      = $dbConfig['port'] ?? 3306;
        $user      = $dbConfig['username'];
        $pass      = $dbConfig['password'];
        $dbName    = $dbConfig['database'];

        $dumpBin     = $this->findMysqldumpBinary();
        $dir         = 'backups';
        $modeSuffix  = $mode === 'incremental' ? '_incremental' : '_full';
        $filename    = date('Y_m_d_His') . $modeSuffix . '_' . Str::slug($backupName) . '.sql';
        $storagePath = $dir . '/' . $filename;
        $fullPath    = Storage::disk('local')->path($storagePath);

        // Ensure directory exists
        if (!Storage::disk('local')->exists($dir)) {
            Storage::disk('local')->makeDirectory($dir);
        }

        if ($mode === 'incremental') {
            $this->runIncrementalDump($dbConfig, $dumpBin, $dbName, $fullPath);
        } else {
            // Full backup: schema + data + stored routines + triggers + events
            // Redirect stderr to a temp file to keep the SQL output clean (password warnings must not appear in the .sql file)
            $dumpErrFile = str_replace('\\', '/', tempnam(sys_get_temp_dir(), 'mysqldump_err_'));
            $command = sprintf(
                '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --events --add-drop-table --ignore-table=%s.backups %s > "%s" 2>"%s"',
                $dumpBin, $host, $port, $user, escapeshellarg($pass), $dbName, $dbName, $fullPath, $dumpErrFile
            );

            exec($command, $output, $returnCode);
            $dumpStderr = is_file($dumpErrFile) ? trim(file_get_contents($dumpErrFile)) : '';
            @unlink($dumpErrFile);

            if ($returnCode !== 0) {
                throw new \RuntimeException('mysqldump falló (código ' . $returnCode . '): ' . mb_scrub($dumpStderr, 'UTF-8'));
            }

            if (!file_exists($fullPath) || filesize($fullPath) === 0) {
                throw new \RuntimeException('El archivo de respaldo está vacío o no se creó correctamente.');
            }
        }

        // ── Encrypt with PHP native openssl if requested ────────────────
        if ($encrypted) {
            $encryptedPath = $storagePath . '.enc';
            $encryptedFull = Storage::disk('local')->path($encryptedPath);

            $rawKey = config('app.key');
            if (str_starts_with($rawKey, 'base64:')) {
                $rawKey = base64_decode(substr($rawKey, 7));
            }
            // AES-256-CBC requires exactly 32 bytes
            $rawKey = substr(str_pad($rawKey, 32, "\0"), 0, 32);

            $plainData = file_get_contents($fullPath);
            $iv        = random_bytes(16);
            $cipher    = openssl_encrypt($plainData, 'AES-256-CBC', $rawKey, OPENSSL_RAW_DATA, $iv);

            if ($cipher === false) {
                throw new \RuntimeException('Cifrado fallido: ' . openssl_error_string());
            }

            // Prepend IV so it can be decrypted later: [16-byte IV][ciphertext]
            file_put_contents($encryptedFull, $iv . $cipher);

            // Remove plain SQL, keep only encrypted file
            @unlink($fullPath);
            $storagePath = $encryptedPath;
        }

        return $storagePath;
    }

    /**
     * Generate an incremental (data-only, changed-since-last-full) SQL dump.
     */
    private function runIncrementalDump(array $dbConfig, string $dumpBin, string $dbName, string $outputPath): void
    {
        $host = $dbConfig['host'];
        $port = $dbConfig['port'] ?? 3306;
        $user = $dbConfig['username'];
        $pass = $dbConfig['password'];

        // Require a prior full backup as baseline
        $lastFull = Backup::where('status', 'completed')
            ->where('backup_mode', 'full')
            ->latest()
            ->first();

        if (!$lastFull) {
            throw new \RuntimeException(
                'No existe un respaldo completo previo. Realiza primero un respaldo de tipo Completo antes de generar uno Incremental.'
            );
        }

        $since = $lastFull->created_at->format('Y-m-d H:i:s');

        // SQL file header
        $header  = "-- ============================================================\n";
        $header .= "-- RESPALDO DIFERENCIAL (tipo: incremental)\n";
        $header .= "-- Incluye: filas NUEVAS y filas MODIFICADAS desde el respaldo completo base.\n";
        $header .= "-- Cambios desde: {$since}\n";
        $header .= "-- Respaldo base:  {$lastFull->name}\n";
        $header .= "-- Generado:       " . date('Y-m-d H:i:s') . "\n";
        $header .= "-- NOTA: Las sentencias REPLACE INTO actualizan filas existentes e insertan nuevas.\n";
        $header .= "-- NOTA: Restaura primero el respaldo completo base y luego aplica este diferencial.\n";
        $header .= "-- ============================================================\n\n";
        $header .= "SET NAMES utf8mb4;\n";
        $header .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        file_put_contents($outputPath, $header);

        // Get all tables in the database
        $tablesResult = DB::select(
            "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME",
            [$dbName]
        );
        $tables = array_column(array_map(fn($t) => (array)$t, $tablesResult), 'TABLE_NAME');

        // Exclude backup metadata — restoring it causes "in_progress" confusion
        $tables = array_filter($tables, fn($t) => $t !== 'backups');

        foreach ($tables as $table) {
            // Check if this table has timestamp columns for filtering
            $colsResult = DB::select(
                "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME IN ('created_at', 'updated_at')",
                [$dbName, $table]
            );
            $timeCols = array_column(array_map(fn($c) => (array)$c, $colsResult), 'COLUMN_NAME');

            // Build WHERE clause: capture NEW rows (created_at >= since) AND MODIFIED rows (updated_at >= since)
            // If a table only has created_at but not updated_at, modifications to existing rows would be missed.
            // We handle this by checking both columns independently.
            $whereArg     = '';
            $tmpWherePath = null;
            if (!empty($timeCols)) {
                // Build conditions using the available timestamp columns
                $conditions = [];
                if (in_array('updated_at', $timeCols)) {
                    $conditions[] = "updated_at >= '{$since}'";
                }
                if (in_array('created_at', $timeCols)) {
                    $conditions[] = "created_at >= '{$since}'";
                }
                $whereClause = implode(' OR ', $conditions);

                // Write the WHERE value to a temp file to avoid Windows cmd.exe quoting issues
                // with double-quoted --where="..." arguments containing spaces and special chars.
                $tmpWherePath = str_replace('\\', '/', tempnam(sys_get_temp_dir(), 'mysqlinc_where_'));
                file_put_contents($tmpWherePath, $whereClause);
                $whereArg = '--where=' . escapeshellarg($whereClause);
            }
            // else: no timestamp columns → dump all rows (safest fallback)

            $cmdOutput = [];
            // Redirect stderr to a temp file so password warnings don't pollute the SQL output
            $incrErrFile = str_replace('\\', '/', tempnam(sys_get_temp_dir(), 'mysqlinc_err_'));
            $cmd = sprintf(
                '"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --no-create-info --replace --skip-add-drop-table --skip-triggers %s %s %s >> "%s" 2>"%s"',
                $dumpBin, $host, $port, $user, escapeshellarg($pass),
                $whereArg, $dbName, $table, $outputPath, $incrErrFile
            );

            exec($cmd, $cmdOutput, $returnCode);
            $incrStderr = is_file($incrErrFile) ? trim(file_get_contents($incrErrFile)) : '';
            @unlink($incrErrFile);
            if ($tmpWherePath && file_exists($tmpWherePath)) {
                @unlink($tmpWherePath);
            }

            if ($returnCode !== 0) {
                throw new \RuntimeException(
                    "Error en respaldo incremental para la tabla `{$table}`: " . mb_scrub($incrStderr, 'UTF-8')
                );
            }
        }

        // SQL file footer
        file_put_contents($outputPath, "\nSET FOREIGN_KEY_CHECKS=1;\n", FILE_APPEND);

        if (!file_exists($outputPath) || filesize($outputPath) < 100) {
            throw new \RuntimeException('El archivo de respaldo incremental está vacío o es inválido.');
        }
    }

    /**
     * Locate mysqldump binary in common Laragon / system paths.
     */
    private function findMysqldumpBinary(): string
    {
        $candidates = [
            // Laragon common paths
            'D:/Lalo104lucky/Descargas/Aplicaciones/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe',
            'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysqldump.exe',
            'C:/laragon/bin/mysql/mysql-8.0.29-winx64/bin/mysqldump.exe',
            // WAMP
            'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysqldump.exe',
            // XAMPP
            'C:/xampp/mysql/bin/mysqldump.exe',
            // System PATH (Linux/Mac)
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];

        // Try to find dynamically in Laragon's mysql dir
        $laragonBase = 'D:/Lalo104lucky/Descargas/Aplicaciones/laragon/bin/mysql';
        if (is_dir($laragonBase)) {
            foreach (glob($laragonBase . '/*/bin/mysqldump.exe') as $found) {
                array_unshift($candidates, $found);
            }
        }

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        // Last resort: assume it's in PATH
        return 'mysqldump';
    }

    /**
     * Locate mysql client binary for restore.
     */
    private function findMysqlClientBinary(): string
    {
        $laragonBase = 'D:/Lalo104lucky/Descargas/Aplicaciones/laragon/bin/mysql';
        if (is_dir($laragonBase)) {
            foreach (glob($laragonBase . '/*/bin/mysql.exe') as $found) {
                if (file_exists($found)) return $found;
            }
        }

        $candidates = [
            'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysql.exe',
            'C:/wamp64/bin/mysql/mysql8.0.31/bin/mysql.exe',
            'C:/xampp/mysql/bin/mysql.exe',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) return $path;
        }

        return 'mysql';
    }
}
