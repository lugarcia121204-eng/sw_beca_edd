<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Limpia los archivos físicos generados por datos de prueba
        foreach (['announcements', 'templates', 'documents', 'catalog_documents'] as $folder) {
            if (Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->deleteDirectory($folder);
            }
        }

        $this->call([
            ModuleSeeder::class,
            RoleSeeder::class,
            PermissionSeeder::class,
            StateSeeder::class,
            InstitutionSeeder::class,
            PriorityAreaSeeder::class,
            SubAreaSeeder::class,
            RenapoSeeder::class,
            CatalogDocumentSeeder::class,
            //AnnouncementSeeder::class,
            PositionTypeSeeder::class,
            \Database\Seeders\TemplateSeeder::class,
            UserSeeder::class,
        ]);
    }
}
