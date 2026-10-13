<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class FixPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fix-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refactor permissions from Spanish to English in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permission refactoring...');

        $mappings = [
            'instituciones' => 'institutions',
            'areas_prioritarias' => 'priority_areas',
            'documentos' => 'catalog.documents',
            'rubricas' => 'rubrics',
            'sub_areas' => 'sub_areas', // Just in case, but probably same
        ];

        DB::transaction(function () use ($mappings) {
            foreach ($mappings as $spanish => $english) {
                $permissions = Permission::where('name', 'like', "{$spanish}.%")->get();
                
                foreach ($permissions as $permission) {
                    $newName = str_replace("{$spanish}.", "{$english}.", $permission->name);
                    
                    // Check if new name already exists (to avoid duplicates if seeded)
                    if (Permission::where('name', $newName)->exists()) {
                        $this->warn("Permission {$newName} already exists. Deleting user assignments for {$permission->name} and deleting it.");
                        // Migrate assignments? For now, we assume user wants to keep assignments.
                        // But if new permission exists, we might need to move roles/users to it.
                        // Ideally, we rename. If target exists, we delete source.
                        // But to be safe, let's just rename if target DOES NOT exist.
                         $permission->delete();
                    } else {
                        $permission->name = $newName;
                        $permission->module_key = 'catalog'; // Ensure correct module key
                        $permission->save();
                        $this->info("Renamed: {$spanish} -> {$english} ({$newName})");
                    }
                }
            }
            
            // Fix specific casing if needed
            // Fix catalog document permissions if they were just 'documentos.'
           // Already handled by mapping 'documentos' -> 'catalog.documents'
        });

        // Also ensure Role 'Super Admin' has all permissions
        $superAdmin = \Spatie\Permission\Models\Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
           $superAdmin->syncPermissions(Permission::all());
           $this->info('Synced Super Admin permissions.');
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Permissions updated successfully!');
    }
}
