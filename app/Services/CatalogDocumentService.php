<?php

namespace App\Services;

use App\Models\CatalogDocument;
use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class CatalogDocumentService
{
    /**
     * Store a document and link it to active announcements.
     */
    public function createDocument(array $data, $request): CatalogDocument
    {
        return DB::transaction(function () use ($data, $request) {
            if ($request->hasFile('archivo')) {
                $file = $request->file('archivo');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('catalog_documents', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientMimeType();
                $data['file_size'] = $file->getSize();
            }

            $document = CatalogDocument::create($data);

            if ($document->active) {
                $this->linkToActiveAnnouncements($document);
            }

            return $document;
        });
    }

    /**
     * Update an existing Catalog Document.
     */
    public function updateDocument(CatalogDocument $document, array $data, $request): CatalogDocument
    {
        return DB::transaction(function () use ($document, $data, $request) {
            if ($request->hasFile('archivo')) {
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $file = $request->file('archivo');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('catalog_documents', $fileName, 'public');

                $data['file_path'] = $filePath;
                $data['file_name'] = $file->getClientOriginalName();
                $data['file_type'] = $file->getClientMimeType();
                $data['file_size'] = $file->getSize();
            }

            $document->update($data);
            return $document;
        });
    }

    /**
     * Link document to active announcements
     */
    public function linkToActiveAnnouncements(CatalogDocument $document): void
    {
        $activeAnnouncements = Announcement::where('status', 'activa')->pluck('id');
        if ($activeAnnouncements->isNotEmpty()) {
            $syncData = [];
            foreach ($activeAnnouncements as $convId) {
                $syncData[$convId] = ['is_mandatory' => true];
            }
            $document->announcements()->syncWithoutDetaching($syncData);
        }
    }
}
