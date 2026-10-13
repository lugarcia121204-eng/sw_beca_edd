<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Define permissions for each resource of the security module
        $permissions = [
            // Security (modules, permissions, roles, users)
            ['name' => 'modules.index', 'description' => 'View modules list', 'module_key' => 'security'],
            ['name' => 'modules.create', 'description' => 'Create modules', 'module_key' => 'security'],
            ['name' => 'modules.edit', 'description' => 'Edit modules', 'module_key' => 'security'],
            ['name' => 'modules.delete', 'description' => 'Delete modules', 'module_key' => 'security'],
            
            ['name' => 'permissions.index', 'description' => 'View permissions list', 'module_key' => 'security'],
            ['name' => 'permissions.create', 'description' => 'Create permissions', 'module_key' => 'security'],
            ['name' => 'permissions.edit', 'description' => 'Edit permissions', 'module_key' => 'security'],
            ['name' => 'permissions.delete', 'description' => 'Delete permissions', 'module_key' => 'security'],

            ['name' => 'roles.index', 'description' => 'View roles list', 'module_key' => 'security'],
            ['name' => 'roles.create', 'description' => 'Create roles', 'module_key' => 'security'],
            ['name' => 'roles.edit', 'description' => 'Edit roles', 'module_key' => 'security'],
            ['name' => 'roles.delete', 'description' => 'Delete roles', 'module_key' => 'security'],

            ['name' => 'users.index', 'description' => 'View users list', 'module_key' => 'security'],
            ['name' => 'users.create', 'description' => 'Create users', 'module_key' => 'security'],
            ['name' => 'users.edit', 'description' => 'Edit users', 'module_key' => 'security'],
            ['name' => 'users.delete', 'description' => 'Delete users', 'module_key' => 'security'],

            // Applications (Admin - Individual Management)
            ['name' => 'applications.index', 'description' => 'View applications list', 'module_key' => 'applications'],
            ['name' => 'applications.create', 'description' => 'Create applications', 'module_key' => 'applications'],
            ['name' => 'applications.edit', 'description' => 'Edit applications', 'module_key' => 'applications'],
            ['name' => 'applications.delete', 'description' => 'Delete applications', 'module_key' => 'applications'],
            ['name' => 'applications.own', 'description' => 'View own applications only', 'module_key' => 'applications'],
            ['name' => 'applications.assign', 'description' => 'Assign evaluators to applications', 'module_key' => 'applications'],
            ['name' => 'applications.verdict', 'description' => 'Issue final verdict on applications', 'module_key' => 'applications'],

            // Announcements
            ['name' => 'announcements.index', 'description' => 'View announcements list', 'module_key' => 'announcements'],
            ['name' => 'announcements.create', 'description' => 'Create announcements', 'module_key' => 'announcements'],
            ['name' => 'announcements.edit', 'description' => 'Edit announcements', 'module_key' => 'announcements'],
            ['name' => 'announcements.delete', 'description' => 'Delete announcements', 'module_key' => 'announcements'],

            // Evaluations (Associated with applications)
            ['name' => 'evaluations.index', 'description' => 'View evaluations list', 'module_key' => 'evaluations'],
            ['name' => 'evaluations.create', 'description' => 'Create evaluations', 'module_key' => 'evaluations'],
            ['name' => 'evaluations.edit', 'description' => 'Edit evaluations', 'module_key' => 'evaluations'],
            ['name' => 'evaluations.delete', 'description' => 'Delete evaluations', 'module_key' => 'evaluations'],

            // Recognitions
            ['name' => 'recognitions.index', 'description' => 'View recognitions', 'module_key' => 'recognitions'],
            ['name' => 'recognitions.create', 'description' => 'Create recognitions', 'module_key' => 'recognitions'],
            ['name' => 'recognitions.edit', 'description' => 'Edit recognitions', 'module_key' => 'recognitions'],
            ['name' => 'recognitions.toggle', 'description' => 'Toggle recognitions active status', 'module_key' => 'recognitions'],
            
            // Teacher Recognitions
            ['name' => 'teacher.recognitions.index', 'description' => 'View my teacher recognitions', 'module_key' => 'recognitions'],

            // Catalog
            ['name' => 'catalog.index', 'description' => 'View catalog', 'module_key' => 'catalog'],
            ['name' => 'catalog.create', 'description' => 'Create in catalog', 'module_key' => 'catalog'],
            ['name' => 'catalog.edit', 'description' => 'Edit catalog', 'module_key' => 'catalog'],
            ['name' => 'catalog.delete', 'description' => 'Delete catalog', 'module_key' => 'catalog'],

            // Institutions
            ['name' => 'institutions.index', 'description' => 'View institutions', 'module_key' => 'catalog'],
            ['name' => 'institutions.create', 'description' => 'Create institutions', 'module_key' => 'catalog'],
            ['name' => 'institutions.edit', 'description' => 'Edit institutions', 'module_key' => 'catalog'],
            ['name' => 'institutions.delete', 'description' => 'Delete institutions', 'module_key' => 'catalog'],

            // Priority Areas
            ['name' => 'priority_areas.index', 'description' => 'View priority areas', 'module_key' => 'catalog'],
            ['name' => 'priority_areas.create', 'description' => 'Create priority areas', 'module_key' => 'catalog'],
            ['name' => 'priority_areas.edit', 'description' => 'Edit priority areas', 'module_key' => 'catalog'],
            ['name' => 'priority_areas.delete', 'description' => 'Delete priority areas', 'module_key' => 'catalog'],

            // Sub Areas
            ['name' => 'sub_areas.index', 'description' => 'View sub areas', 'module_key' => 'catalog'],
            ['name' => 'sub_areas.create', 'description' => 'Create sub areas', 'module_key' => 'catalog'],
            ['name' => 'sub_areas.edit', 'description' => 'Edit sub areas', 'module_key' => 'catalog'],
            ['name' => 'sub_areas.delete', 'description' => 'Delete sub areas', 'module_key' => 'catalog'],
            // Rubrics
            ['name' => 'rubrics.index', 'description' => 'View rubrics', 'module_key' => 'catalog'],
            ['name' => 'rubrics.create', 'description' => 'Create rubrics', 'module_key' => 'catalog'],
            ['name' => 'rubrics.edit', 'description' => 'Edit rubrics', 'module_key' => 'catalog'],
            ['name' => 'rubrics.delete', 'description' => 'Delete rubrics', 'module_key' => 'catalog'],

            // Documents (Catalog)
            ['name' => 'documents.index', 'description' => 'View documents catalog', 'module_key' => 'catalog'],
            ['name' => 'documents.create', 'description' => 'Create documents', 'module_key' => 'catalog'],
            ['name' => 'documents.edit', 'description' => 'Edit documents', 'module_key' => 'catalog'],
            ['name' => 'documents.delete', 'description' => 'Delete documents', 'module_key' => 'catalog'],
            ['name' => 'documents.show', 'description' => 'View document details', 'module_key' => 'catalog'],
            ['name' => 'documents.show', 'description' => 'View document details', 'module_key' => 'catalog'],
            ['name' => 'documents.download', 'description' => 'Download documents', 'module_key' => 'catalog'],

            // Templates (Catalog)
            ['name' => 'templates.index', 'description' => 'View templates list', 'module_key' => 'catalog'],
            ['name' => 'templates.create', 'description' => 'Create templates', 'module_key' => 'catalog'],
            ['name' => 'templates.edit', 'description' => 'Edit templates (toggle)', 'module_key' => 'catalog'],
            ['name' => 'templates.delete', 'description' => 'Delete templates', 'module_key' => 'catalog'],

            // Applications Control (Summary by Institutions)
            ['name' => 'requests.index', 'description' => 'View request control (institution summary)', 'module_key' => 'request_control'],
            ['name' => 'requests.show', 'description' => 'View request control details', 'module_key' => 'request_control'],

            // Teacher Specific
            ['name' => 'teacher.dashboard', 'description' => 'View teacher dashboard', 'module_key' => 'dashboard'],
            ['name' => 'teacher.applications.show', 'description' => 'View teacher application details', 'module_key' => 'applications'],

            ['name' => 'evaluator.evaluations.index', 'description' => 'View my evaluations history', 'module_key' => 'evaluations'],
            ['name' => 'evaluator.evaluations.show', 'description' => 'View evaluation details in history', 'module_key' => 'evaluations'],
            
            // Evaluator Recognitions
            ['name' => 'evaluator.recognitions.index', 'description' => 'View my recognitions', 'module_key' => 'evaluations'],

            // Admin Dashboard
            ['name' => 'admin.dashboard', 'description' => 'View admin dashboard', 'module_key' => 'dashboard'],

            // Backup
            ['name' => 'backup.index',    'description' => 'Ver listado de respaldos',          'module_key' => 'backup'],
            ['name' => 'backup.create',   'description' => 'Crear respaldo manual',              'module_key' => 'backup'],
            ['name' => 'backup.schedule', 'description' => 'Configurar programación automática', 'module_key' => 'backup'],
            ['name' => 'backup.restore',  'description' => 'Restaurar base de datos',            'module_key' => 'backup'],
            ['name' => 'backup.download', 'description' => 'Descargar archivo de respaldo',      'module_key' => 'backup'],
            ['name' => 'backup.delete',   'description' => 'Eliminar respaldos',                 'module_key' => 'backup'],
        ];

        // Create each permission
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'description' => $permission['description'],
                    'module_key' => $permission['module_key']
                ]
            );
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        // Super Admin - ALL permissions
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $allPermissions = Permission::all();
            $superAdmin->syncPermissions($allPermissions);
            $this->command->info("✅ {$allPermissions->count()} permissions assigned to Super Admin.");
        }

        // Admin - Management permissions
        $admin = Role::where('name', 'Admin')->first();
        if ($admin) {
            $adminPermissions = Permission::whereIn('name', [
                // Dashboard
                'admin.dashboard',
                
                // Users (Evaluators Management)
                'users.index',
                'users.delete',
                
                // Applications (Individual Management)
                'applications.index',
                'applications.create',
                'applications.edit',
                'applications.delete',
                'applications.own',
                'applications.assign',
                'applications.verdict',
                
                // Evaluations
                'evaluations.index',
                'evaluations.create',
                'evaluations.edit',
                'evaluations.delete',
                
                // Recognitions
                'recognitions.index',
                'recognitions.create',
                'recognitions.edit',
                'recognitions.toggle',
                
                // Documents (for viewing application documents)
                'documents.index',
                'documents.show',
                'documents.download',
            ])->get();
            $admin->syncPermissions($adminPermissions);
            $this->command->info("✅ {$adminPermissions->count()} permissions assigned to Admin.");
        }

        // Docente - Read permissions and own applications
        $docente = Role::where('name', 'Docente')->first();
        if ($docente) {
            $docentePermissions = Permission::whereIn('name', [
                // Dashboard
                'teacher.dashboard',
                
                // Announcements (Teacher View)
                'announcements.index',
                
                // Applications (Own only)
                'applications.own',
                'applications.create',
                'applications.edit',
                
                // Documents
                'teacher.applications.show',

                // Teacher Recognitions
                'teacher.recognitions.index',
            ])->get();
            $docente->syncPermissions($docentePermissions);
            $this->command->info("✅ {$docentePermissions->count()} permissions assigned to Docente.");
        }

        // Evaluator - Evaluation and read permissions
        $evaluador = Role::where('name', 'Evaluador')->first();
        if ($evaluador) {
            $evaluadorPermissions = Permission::whereIn('name', [
                'evaluations.index', 'evaluations.create', 'evaluations.edit',
                'announcements.index',
                'recognitions.index',
                'applications.index',
                'evaluator.evaluations.index',
                'evaluator.evaluations.show',
                'evaluator.recognitions.index',
            ])->get();
            $evaluador->syncPermissions($evaluadorPermissions);
            $this->command->info("✅ {$evaluadorPermissions->count()} permissions assigned to Evaluador.");
        }
    }
}