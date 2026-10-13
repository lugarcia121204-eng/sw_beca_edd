<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileService
{
    /**
     * @var string The default disk to use
     */
    protected string $disk;

    public function __construct(string $disk = 'public')
    {
        $this->disk = $disk;
    }

    /**
     * Download a file if it exists, otherwise abort with 404.
     *
     * @param string|null $path
     * @param string|null $name
     * @return StreamedResponse
     */
    public function download(?string $path, ?string $name = null): StreamedResponse
    {
        $this->abortIfMissing($path);

        return Storage::disk($this->disk)->download($path, $name);
    }

    /**
     * Return a file response to render in browser (e.g., PDF) if it exists.
     *
     * @param string|null $path
     * @return BinaryFileResponse
     */
    public function responseFile(?string $path): BinaryFileResponse
    {
        $this->abortIfMissing($path);

        return response()->file(Storage::disk($this->disk)->path($path));
    }

    /**
     * Delete a file from storage if it exists.
     *
     * @param string|null $path
     * @return bool
     */
    public function delete(?string $path): bool
    {
        if ($path && Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }

        return false;
    }

    /**
     * Ensure a file exists, or throw a 404 completely abstracting it from
     * the controller logic.
     *
     * @param string|null $path
     * @return void
     */
    protected function abortIfMissing(?string $path): void
    {
        if (!$path || !Storage::disk($this->disk)->exists($path)) {
            abort(404, 'The requested file was not found.');
        }
    }
}
