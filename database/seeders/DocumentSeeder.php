<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Document;
use App\Models\Application;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Active Announcement
        $announcement = Announcement::create([
            'name' => 'Convocatoria Estímulos 2026',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);

        // 2. Get some existing users or create new ones
        $users = User::role('Docente')->take(5)->get();

        if ($users->isEmpty()) {
            return; // Or create dummy users if preferred, but existing setup implies users exist
        }

        // 3. Create Applications and Documents for each user
        foreach ($users as $user) {
            // Check if application already exists
            if (Application::where('user_id', $user->id)->where('announcement_id', $announcement->id)->exists()) {
                continue;
            }

            $application = Application::create([
                'user_id' => $user->id,
                'announcement_id' => $announcement->id,
                'status' => 'pending',
            ]);

            // Create some dummy documents
            $documents = [
                ['name' => 'INE', 'path' => 'documents/dummy_ine.pdf', 'type' => 'pdf'],
                ['name' => 'CURP', 'path' => 'documents/dummy_curp.pdf', 'type' => 'pdf'],
                ['name' => 'Comprobante de Domicilio', 'path' => 'documents/dummy_comprobante.jpg', 'type' => 'image'],
                ['name' => 'Constancia Laboral', 'path' => 'documents/dummy_constancia.pdf', 'type' => 'pdf'],
            ];

            foreach ($documents as $doc) {
                Document::create([
                    'application_id' => $application->id,
                    'name' => $doc['name'],
                    'file_path' => $doc['path'],
                    'file_type' => $doc['type'],
                ]);
            }
        }
    }
}
