<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CurpController;
use App\Http\Controllers\Security\ModuleController;
use App\Http\Controllers\Security\PermissionController;
use App\Http\Controllers\Security\RoleController;
use App\Http\Controllers\Security\UserController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\RequestControlController as AdminRequestControlController;
use App\Http\Controllers\Admin\ApplicationController; // SolicitudController renamed
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Evaluator\EvaluatorController; // EvaluadorController renamed
use App\Http\Controllers\TeacherController; // DocenteController renamed
use App\Http\Controllers\Catalog\InstitutionController;
use App\Http\Controllers\Catalog\PriorityAreaController;
use App\Http\Controllers\Catalog\SubAreaController;
use App\Http\Controllers\Catalog\RubricController;
use App\Http\Controllers\Catalog\AnnouncementController; // ConvocatoriaController renamed
use App\Http\Controllers\Catalog\DocumentController; // DocumentoController renamed
use App\Http\Controllers\Catalog\TemplateController;

/** @var \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard $auth */
Route::get('/', [HomeController::class, 'index'])->name('inicio');

Route::get('/verify-recognition/{identifier}', [\App\Http\Controllers\Public\VerificationController::class, 'verify'])->name('recognition.verify');

// Public Recognition Search
Route::get('/recognitions/search', [\App\Http\Controllers\Public\RecognitionSearchController::class, 'index'])->name('recognitions.search');
Route::get('/recognitions/download/{identifier}', [\App\Http\Controllers\Public\RecognitionSearchController::class, 'download'])->name('recognitions.public-download');

Route::get('/announcement/{announcement}/document.pdf', [HomeController::class, 'previewAnnouncement'])->name('announcement.preview');
Route::get('/announcement', [HomeController::class, 'showAnnouncement'])->name('announcement.show');

Route::get('/work-documents/{workDocument}/download', [\App\Http\Controllers\SuperAdmin\WorkDocumentController::class, 'publicDownload'])->name('work-documents.download');
Route::get('/work-documents/{workDocument}/preview', [\App\Http\Controllers\SuperAdmin\WorkDocumentController::class, 'preview'])->name('work-documents.preview');

Route::get('/documents', function () { // /documentos -> /documents
    $activeAnnouncement = \App\Models\Announcement::with('calendar')
        ->where('status', 'activa')
        ->latest('created_at')
        ->first();

    if (!$activeAnnouncement) {
        $activeAnnouncement = \App\Models\Announcement::with('calendar')
            ->where('status', 'cerrada')
            ->latest('created_at')
            ->first();
    }

    return Inertia::render('Documents', [
        'managedDocumentIds' => \App\Models\WorkDocument::withTrashed()->whereNotNull('source_key')->pluck('source_key'),
        'workDocuments' => \App\Models\WorkDocument::where('published', true)->latest()->get()->map(fn ($document) => $document->publicData()),
        'activeAnnouncement' => $activeAnnouncement ? (new \App\Http\Resources\Catalog\AnnouncementResource($activeAnnouncement))->resolve() : null,
    ]);
})->name('documents.index'); // documents.index

Route::get('/contact', function () { // /contacto -> /contact
    return Inertia::render('Contact');
})->name('contact');

// API to get institutions (contact)
Route::get('/api/institutions', [App\Http\Controllers\ContactController::class, 'getInstitutions']);

// API to send contact form
Route::post('/api/contact', [App\Http\Controllers\ContactController::class, 'sendContact']);

// API to search CURP (used by Register.vue)
Route::post('/api/search-curp', [CurpController::class, 'search']);

// API to get sub-areas
Route::get('/api/sub-areas/{priority_area_id}', [SubAreaController::class, 'getByPriorityArea']);

// Custom Register Routes (override Fortify)
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::get('/register/evaluator', [RegisterController::class, 'createEvaluador'])->name('register.evaluator'); // evaluator
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

// Email Verification Routes
Route::get('/email/verify', [HomeController::class, 'verifyEmailNotice'])->name('verification.notice');

Route::post('/email/verify/code', [CurpController::class, 'verifyCode'])->name('verification.verify.code');
Route::post('/email/verify/resend', [CurpController::class, 'resendCode'])->name('verification.resend');


Route::middleware(['auth', 'verified'])->group(function () {
    // Generic Dashboard Route (redirects by role)
    Route::get(
        'dashboard',
        function () {
            /** @var \App\Models\User $user */
            $user = auth()->user();
            $role = $user->getPrimaryRole();

            return match ($role) {
                'Super Admin' => redirect()->route('superadmin.dashboard'),
                'Admin' => redirect()->route('admin.dashboard'),
                'Evaluador' => redirect()->route('evaluator.dashboard'),
                'Docente' => redirect()->route('teacher.dashboard'),
                default => Inertia::render('Dashboard'),
            };
        }
    )->name('dashboard.index'); // renamed from inicio.dashboard

    // ========================
    // SUPER ADMIN DASHBOARD ROUTES
    // ========================
    Route::middleware(['role:Super Admin'])->prefix('superadmin')->name('superadmin.')->group(
        function () {
            Route::get('contact-messages', [\App\Http\Controllers\SuperAdmin\ContactMessageController::class, 'index'])->name('contact-messages.index');
            Route::patch('contact-messages/{contactMessage}', [\App\Http\Controllers\SuperAdmin\ContactMessageController::class, 'update'])->name('contact-messages.update');
            Route::get('work-documents/{workDocument}/download', [\App\Http\Controllers\SuperAdmin\WorkDocumentController::class, 'download'])->name('work-documents.download');
            Route::resource('work-documents', \App\Http\Controllers\SuperAdmin\WorkDocumentController::class)->only(['index', 'store', 'update', 'destroy']);
            Route::get('dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
            Route::get('dashboard/export', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'export'])->name('dashboard.export');
            Route::get('control-applications', [\App\Http\Controllers\SuperAdmin\RequestControlController::class, 'index'])->name('control-applications'); // control-solicitudes
            Route::get('control-applications/export', [\App\Http\Controllers\SuperAdmin\RequestControlController::class, 'export'])->name('control-applications.export');

        }
    );

    // ========================
    // BACKUP MODULE (permission-based, granular per ruta)
    // ========================
    Route::middleware(['permission:backup.index'])->prefix('superadmin')->name('superadmin.')->group(
        function () {
            Route::get('backup', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'index'])->name('backup.index');
            Route::get('backup/create', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'create'])->name('backup.create')->middleware('permission:backup.create');
            Route::post('backup', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'store'])->name('backup.store')->middleware('permission:backup.create');
            Route::get('backup/schedule', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'editSchedule'])->name('backup.edit')->middleware('permission:backup.schedule');
            Route::post('backup/schedule', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'updateSchedule'])->name('backup.update-schedule')->middleware('permission:backup.schedule');
            Route::get('backup/restore', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'showRestore'])->name('backup.restore-view')->middleware('permission:backup.restore');
            Route::post('backup/restore', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'restore'])->name('backup.do-restore')->middleware('permission:backup.restore');
            Route::post('backup/restore-upload', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'restoreFromUpload'])->name('backup.restore-upload')->middleware('permission:backup.restore');
            Route::get('backup/{backup}/download', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'download'])->name('backup.download')->middleware('permission:backup.download');
            Route::delete('backup/{backup}', [\App\Http\Controllers\SuperAdmin\BackupController::class, 'destroy'])->name('backup.destroy')->middleware('permission:backup.delete');
        }
    );

    Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(
        function () {
            Route::get('dashboard', [AdminController::class, 'index'])->name('dashboard');
            Route::get('dashboard/export', [AdminController::class, 'export'])->name('dashboard.export');
            // Evaluators Management
            Route::get('evaluators', [\App\Http\Controllers\Admin\EvaluatorController::class, 'index'])->name('evaluators.index'); // evaluadores
            Route::delete('evaluators/{id}', [\App\Http\Controllers\Admin\EvaluatorController::class, 'destroy'])->name('evaluators.destroy');

            Route::put('evaluators/{user}/configuration', [\App\Http\Controllers\Admin\EvaluatorController::class, 'updateConfiguration'])->name('evaluators.configuration');

            // Recognitions
            Route::get('recognitions', [\App\Http\Controllers\Admin\RecognitionController::class, 'index'])->name('recognitions.index'); // reconocimientos
            Route::post('recognitions/toggle', [\App\Http\Controllers\Admin\RecognitionController::class, 'toggle'])->name('recognitions.toggle');

            // Notifications API
            Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
            Route::post('notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
            Route::post('notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
            Route::delete('notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
        }
    );

    Route::get('/evaluator/pending', fn () => \Inertia\Inertia::render('Evaluator/Pending', [
        'authorizationStatus' => auth()->user()->evaluator_status,
    ]))->middleware('role:Evaluador')->name('evaluator.pending');

    Route::middleware(['role:Evaluador', \App\Http\Middleware\EnsureEvaluatorAuthorized::class])->prefix('evaluator')->name('evaluator.')->group(
        function () { // prefix evaluador -> evaluator
            Route::get('dashboard', [EvaluatorController::class, 'inicio'])->name('dashboard'); // inicio -> dashboard
            Route::get('evaluation/{id}', [EvaluatorController::class, 'show'])->name('evaluation.show');
            Route::put('evaluation/{id}', [EvaluatorController::class, 'evaluar'])->name('evaluation.update');
            Route::get('documents/{id}/stream', [EvaluatorController::class, 'streamDocument'])->name('documents.stream');

            // Evaluation History
            Route::get('evaluations', [EvaluatorController::class, 'index'])->name('evaluations.index'); // evaluaciones
            Route::get('evaluations/{id}', [EvaluatorController::class, 'showHistory'])->name('evaluations.show');


            // Recognitions
            Route::get('recognitions', [\App\Http\Controllers\Evaluator\RecognitionController::class, 'index'])->name('recognitions.index');
            Route::get('recognitions/{id}/download', [\App\Http\Controllers\Evaluator\RecognitionController::class, 'download'])->name('recognitions.download');

            // Notifications API
            Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
            Route::post('notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
            Route::post('notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
            Route::delete('notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
        }
    );

    Route::middleware(['role:Docente'])->prefix('teacher')->name('teacher.')->group(
        function () { // prefix docente -> teacher
            Route::get('dashboard', [TeacherController::class, 'inicio'])->name('dashboard'); // inicio -> dashboard
            Route::get('applications/{id}', [TeacherController::class, 'show'])->name('applications.show');
            Route::get('documents/acceptance/{id}', [TeacherController::class, 'downloadAcceptance'])->name('documents.downloadAcceptance');
            Route::get('documents/{id}/download', [TeacherController::class, 'download'])->name('documents.download');
            Route::get('documents/{id}/stream', [TeacherController::class, 'stream'])->name('documents.stream');

            // Announcements Teacher
            Route::get('announcements', [TeacherController::class, 'convocatorias'])->name('announcements.index'); // convocatorias
            Route::get('announcements/{id}/preview', [TeacherController::class, 'previewAnnouncement'])->name('announcements.preview');
            Route::get('announcements/{id}/apply', [TeacherController::class, 'solicitar'])->name('announcements.apply'); // solicitar
            Route::post('announcements/apply', [TeacherController::class, 'storeApplication'])->name('applications.store');

            // Teacher Recognitions
            Route::get('recognitions', [\App\Http\Controllers\Teacher\RecognitionController::class, 'index'])->name('recognitions.index');
            Route::get('recognitions/{recognition}/download', [\App\Http\Controllers\Teacher\RecognitionController::class, 'download'])->name('recognitions.download');

            // Notifications API
            Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
            Route::post('notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
            Route::post('notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
            Route::delete('notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
        }
    );

    // ========================
    // SECURITY MODULE
    // ========================

    Route::prefix('security')->name('security.')->group(
        function () { // seguridad -> security
            Route::resource('modules', ModuleController::class);

            // Documents Module Routes
            Route::get('documents', [AdminDocumentController::class, 'index'])->name('documents.index')->middleware('can:documents.index');
            Route::get('documents/{id}', [AdminDocumentController::class, 'show'])->name('documents.show')->middleware('can:documents.show');
            Route::get('documents/download/{document}', [AdminDocumentController::class, 'download'])->name('documents.download')->middleware('can:documents.download');

            Route::resource('permissions', PermissionController::class);
            Route::resource('roles', RoleController::class);

            Route::get('users/export', [UserController::class, 'export'])->name('users.export');
            Route::get('users/template', [UserController::class, 'template'])->name('users.template');
            Route::post('users/import', [UserController::class, 'import'])->name('users.import');
            Route::resource('users', UserController::class);
        }
    );


    // ========================
    // SHARED MODULES
    // ========================

    Route::get('control-applications', [AdminRequestControlController::class, 'index'])->name('applications.control.index')->middleware('can:requests.index');
    Route::get('control-applications/{id}', [AdminRequestControlController::class, 'show'])->name('applications.control.show')->middleware('can:requests.show');

    // Admin Specific Applications Logic
    Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(
        function () {
            // Route::get('inicio', [AdminController::class, 'inicio'])->name('inicio'); // Already defined above
            // Admin Applications (List, Assign, Verdict)
            Route::get('applications', [ApplicationController::class, 'index'])->name('applications.index'); // solicitudes -> applications
            Route::get('applications/{id}/assign', [ApplicationController::class, 'assignView'])->name('applications.assign_view');
            Route::post('applications/assign', [ApplicationController::class, 'assignEvaluator'])->name('applications.assign');
            Route::delete('applications/evaluator', [ApplicationController::class, 'removeEvaluator'])->name('applications.remove-evaluator');
            Route::get('applications/{id}', [ApplicationController::class, 'show'])->name('applications.show');
            Route::post('applications/{id}/verdict', [ApplicationController::class, 'verdict'])->name('applications.verdict');
            Route::get('applications/{application}/evaluations/{evaluation}', [ApplicationController::class, 'showEvaluation'])->name('applications.evaluation.show');
        }
    );

    // Announcements
    Route::get('announcements/{announcement}/preview', [AnnouncementController::class, 'preview'])->name('announcements.preview')->middleware('permission:announcements.index');
    Route::get('announcements/{announcement}/download', [AnnouncementController::class, 'download'])->name('announcements.download')->middleware('permission:announcements.index');
    Route::put('announcements/{announcement}/documents', [AnnouncementController::class, 'updateDocumentos'])->name('announcements.updateDocuments')->middleware('permission:announcements.edit');
    Route::resource('announcements', AnnouncementController::class)->names([
        'index' => 'announcements.index',
        'create' => 'announcements.create',
        'store' => 'announcements.store',
        'edit' => 'announcements.edit',
        'update' => 'announcements.update',
        'destroy' => 'announcements.destroy',
    ]);

    // Note: Generic recognition and evaluation views are handled within their respective role dashboards
    // Admin -> Admin/Recognitions/Index
    // Evaluator -> Evaluator/Evaluations/Index

    // Catalog routes are handled by controllers in SuperAdmin/Catalog/*
    Route::prefix('catalog')->name('catalog.')->group(
        function () {

            // CATALOG MODULE
            // Route::prefix('catalog')->name('catalog.')->group(function () { // Removed nested group
            Route::get('documents/{id}/download', [DocumentController::class, 'download'])->name('documents.download');
            Route::post('documents/{id}/toggle-active', [DocumentController::class, 'toggleActive'])->name('documents.toggleActive');
            Route::post('documents/{id}/update-via', [DocumentController::class, 'updateVia'])->name('documents.updateVia');
            Route::get('documents/{document}/download-docente', [DocumentController::class, 'downloadDocente'])->name('documents.downloadDocente');
            Route::get('documents/{document}/stream-docente', [DocumentController::class, 'streamDocente'])->name('documents.streamDocente');
            Route::resource('documents', DocumentController::class);

            Route::get('institutions/export', [InstitutionController::class, 'export'])->name('institutions.export');
            Route::post('institutions/import', [InstitutionController::class, 'import'])->name('institutions.import');
            Route::get('institutions/template', [InstitutionController::class, 'downloadTemplate'])->name('institutions.template');
            Route::resource('institutions', InstitutionController::class);

            Route::get('priority-areas/export', [PriorityAreaController::class, 'export'])->name('priority-areas.export');
            Route::post('priority-areas/import', [PriorityAreaController::class, 'import'])->name('priority-areas.import');
            Route::get('priority-areas/template', [PriorityAreaController::class, 'downloadTemplate'])->name('priority-areas.template');
            Route::resource('priority-areas', PriorityAreaController::class);

            Route::get('sub-areas/export', [SubAreaController::class, 'export'])->name('sub-areas.export');
            Route::resource('sub-areas', SubAreaController::class);

            Route::resource('rubrics', RubricController::class);
            Route::post('rubrics/{rubric}/toggle-active', [RubricController::class, 'toggleActive'])->name('rubrics.toggle-active');

            Route::post('templates/{template}/toggle-active', [TemplateController::class, 'toggleActive'])->name('templates.toggle-active');
            Route::get('templates/{template}/stream', [TemplateController::class, 'stream'])->name('templates.stream');
            Route::put('templates/{template}/content', [TemplateController::class, 'updateContent'])->name('templates.update-content');
            Route::resource('templates', TemplateController::class);
            // }); // Removed nested group closing brace
    
            // Modules accessible by permission (Admin/SuperAdmin)
            Route::middleware(['role:Admin|Super Admin'])->prefix('admin')->name('admin.')->group(
                function () {
                Route::resource('documents', \App\Http\Controllers\Admin\DocumentController::class)->only(['index', 'show']);
                Route::get('documents/{document}/download', [\App\Http\Controllers\Admin\DocumentController::class, 'download'])->name('documents.download');
                Route::get('documents/{document}/stream', [\App\Http\Controllers\Admin\DocumentController::class, 'stream'])->name('documents.stream');
            }
            );
        }
    );
});

require __DIR__ . '/settings.php';
