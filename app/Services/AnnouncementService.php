<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\CatalogDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\NotificationService;

class AnnouncementService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Store a new announcement with its files, calendar, and document relations.
     */
    public function createAnnouncement(array $data, $request): Announcement
    {
        $activatedOnCreate = false;

        $announcement = DB::transaction(function () use ($data, $request, &$activatedOnCreate) {
            $publicationStart = $request->publication_start
                ? \Carbon\Carbon::parse($request->publication_start)->startOfDay()
                : null;

            $data['status'] = ($publicationStart && $publicationStart->lte(\Carbon\Carbon::today()))
                ? 'activa'
                : 'pendiente';

            // Handle File Upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = Str::uuid() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('announcements', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientMimeType();
                $data['file_size'] = $file->getSize();
            }

            // Handle Image Upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = Str::uuid() . '_img_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('announcements/images', $imageName, 'public');
                $data['image_path'] = $imagePath;
            }

            // Enforce single active announcement
            if (isset($data['status']) && $data['status'] === 'activa') {
                Announcement::where('status', 'activa')->update(['status' => 'cerrada']);
                $activatedOnCreate = true;
            }

            $announcement = Announcement::create($data);

            // Create Calendar
            $announcement->calendar()->create([
                'publication_start' => $request->publication_start,
                'registration_start' => $request->registration_start,
                'registration_end' => $request->registration_end,
                'evaluation_start' => $request->evaluation_start,
                'evaluation_end' => $request->evaluation_end,
                'results_start' => $request->results_start,
                'results_end' => $request->results_end,
            ]);

            // Bind documents
            $this->syncDocuments($announcement, $request->documents);

            return $announcement;
        });

        // Si la convocatoria nació directamente como activa, notificar nueva convocatoria
        if ($activatedOnCreate) {
            $this->notificationService->notifyNewAnnouncement($announcement->id);
        }

        return $announcement;
    }

    /**
     * Update an announcement and its relations.
     */
    public function updateAnnouncement(Announcement $announcement, array $data, $request): Announcement
    {
        return DB::transaction(function () use ($announcement, $data, $request) {
            // Handle File Update
            if ($request->hasFile('file')) {
                if ($announcement->file_path) {
                    Storage::disk('public')->delete($announcement->file_path);
                }

                $file = $request->file('file');
                $fileName = Str::uuid() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('announcements', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientMimeType();
                $data['file_size'] = $file->getSize();
            }

            // Handle Image Update
            if ($request->hasFile('image')) {
                if ($announcement->image_path) {
                    Storage::disk('public')->delete($announcement->image_path);
                }

                $image = $request->file('image');
                $imageName = Str::uuid() . '_img_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('announcements/images', $imageName, 'public');
                $data['image_path'] = $imagePath;
            }

            $statusChangedToActiva = false;
            if (isset($data['status']) && $data['status'] === 'activa' && $announcement->status !== 'activa') {
                $statusChangedToActiva = true;
                Announcement::where('status', 'activa')
                    ->where('id', '!=', $announcement->id)
                    ->update(['status' => 'cerrada']);
            }

            $announcement->update($data);

            // Si se activó la convocatoria, notificar a todos
            if ($statusChangedToActiva) {
                $this->notificationService->notifyNewAnnouncement($announcement->id);
            }

            // Detectar cambios en las fechas para notificar
            $calendar = $announcement->calendar;
            $newDates = [
                'publication_start' => $request->publication_start,
                'registration_start' => $request->registration_start,
                'registration_end' => $request->registration_end,
                'evaluation_start' => $request->evaluation_start,
                'evaluation_end' => $request->evaluation_end,
                'results_start' => $request->results_start,
                'results_end' => $request->results_end,
            ];

            $dateLabels = [
                'publication_start' => 'Inicio de Publicación',
                'registration_start' => 'Inicio de Registro',
                'registration_end' => 'Fin de Registro',
                'evaluation_start' => 'Inicio de Evaluación',
                'evaluation_end' => 'Fin de Evaluación',
                'results_start' => 'Inicio de Resultados',
                'results_end' => 'Fin de Resultados',
            ];

            $changes = [];
            if ($calendar) {
                foreach ($newDates as $key => $value) {
                    $oldValue = $calendar->$key instanceof \Carbon\Carbon ? $calendar->$key->toDateString() : $calendar->$key;
                    if ($value && $oldValue != $value) {
                        $changes[] = [
                            'label' => $dateLabels[$key],
                            'old' => $oldValue,
                            'new' => $value
                        ];
                    }
                }
            }

            // Update or Create Calendar
            $announcement->calendar()->updateOrCreate(
                ['announcement_id' => $announcement->id],
                $newDates
            );

            // Si hubo cambios, notificar
            if (!empty($changes)) {
                $this->notificationService->notifyAnnouncementDateChange(
                    $announcement->id,
                    $announcement->name,
                    $changes
                );
            }

            return $announcement;
        });
    }

    /**
     * Delete an announcement and its physical files.
     */
    public function deleteAnnouncement(Announcement $announcement): void
    {
        DB::transaction(function () use ($announcement) {
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            if ($announcement->file_path) {
                Storage::disk('public')->delete($announcement->file_path);
            }

            $announcement->delete();
        });
    }

    /**
     * Sync documents to the announcement.
     * Use when submitting specific Document IDs array.
     */
    public function syncDocuments(Announcement $announcement, ?array $documentIds): void
    {
        if ($documentIds) {
            $syncData = [];
            $docs = CatalogDocument::whereIn('id', $documentIds)->get(['id']);
            foreach ($docs as $doc) {
                $syncData[$doc->id] = ['is_mandatory' => true, 'via' => 'ambas'];
            }
            $announcement->catalogDocuments()->sync($syncData);
        } else {
            // Default active documents
            $activeDocuments = CatalogDocument::where('active', true)->get(['id']);
            if ($activeDocuments->isNotEmpty()) {
                $syncData = [];
                foreach ($activeDocuments as $doc) {
                    $syncData[$doc->id] = ['is_mandatory' => true, 'via' => 'ambas'];
                }
                $announcement->catalogDocuments()->sync($syncData);
            }
        }
    }
}
