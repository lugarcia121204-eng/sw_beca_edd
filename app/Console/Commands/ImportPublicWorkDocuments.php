<?php
namespace App\Console\Commands;
use App\Models\Announcement;
use App\Models\WorkDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
class ImportPublicWorkDocuments extends Command
{
    protected $signature = 'work-documents:import-public';
    protected $description = 'Incorporar al catalogo los archivos de la pagina publica sin duplicarlos';
    public function handle(): int {
        $items = json_decode(file_get_contents(database_path('data/public-work-documents.json')), true, 512, JSON_THROW_ON_ERROR);
        $failed = 0;
        foreach ($items as $item) {
            if (WorkDocument::withTrashed()->where('source_key', $item['source_key'])->exists()) { $this->line('Ya importado: '.$item['title']); continue; }
            $path = null;
            try {
                $name = basename(parse_url($item['url'], PHP_URL_PATH));
                $announcement = $item['source_key'] === 'doc6' ? (Announcement::where('status', 'activa')->latest()->first() ?? Announcement::where('status', 'cerrada')->latest()->first()) : null;
                if ($announcement && $announcement->file_path && Storage::disk('public')->exists($announcement->file_path)) {
                    $body = Storage::disk('public')->get($announcement->file_path);
                    $name = $announcement->file_name ?: basename($announcement->file_path);
                    $item['title'] = 'Convocatoria '.$announcement->created_at->year;
                    $item['description'] = 'Documento oficial de convocatoria del TecNM para el programa de estímulos '.$announcement->created_at->year.'.';
                } else {
                    $body = Http::connectTimeout(10)->timeout(30)->get($item['url'])->throw()->body();
                }
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!strlen($body) || strlen($body) > 20 * 1024 * 1024 || ($extension === 'pdf' && !str_starts_with(ltrim($body), '%PDF-')) || ($extension === 'docx' && !str_starts_with($body, 'PK'))) {
                    throw new \RuntimeException('El servidor no devolvio un documento valido.');
                }
                $path = 'work-documents/imported-'.$item['source_key'].'.'.$extension;
                if (!Storage::disk('local')->put($path, $body)) throw new \RuntimeException('No se pudo guardar el archivo.');
                WorkDocument::create(['source_key' => $item['source_key'], 'title' => $item['title'], 'description' => $item['description'], 'category' => $item['category'], 'file_path' => $path, 'file_name' => $name, 'file_size' => strlen($body), 'published' => true]);
                $this->info('Importado: '.$item['title']);
            } catch (\Throwable $e) {
                if ($path) Storage::disk('local')->delete($path);
                $failed++; $this->error('No importado: '.$item['title'].' - '.$e->getMessage());
            }
        }
        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
