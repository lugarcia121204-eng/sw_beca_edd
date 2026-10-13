<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    /**
     * Entry point for /.
     */
    public function index()
    {
        // If user is authenticated, redirect to their dashboard
        if (auth()->check()) {
            /** @var User $user */
            $user = auth()->user();
            $role = $user->getPrimaryRole();
            
            return match ($role) {
                'Super Admin' => redirect()->route('superadmin.dashboard'),
                'Admin' => redirect()->route('admin.dashboard'),
                'Evaluador' => redirect()->route('evaluator.dashboard'),
                'Docente' => redirect()->route('teacher.dashboard'),
                default => redirect()->route('dashboard.index'),
            };
        }
        
        // If not authenticated, show public page
        $baseQuery = Announcement::query()->with('calendar');

        $announcements = (clone $baseQuery)
            ->whereIn('status', ['activa', 'cerrada']) // Keep whereIn for combined status for pagination
            ->latest('created_at')
            ->paginate(3)
            ->withQueryString();
        
        // Get announcement for timeline (Priority: Activa > Cerrada)
        $timelineAnnouncement = (clone $baseQuery)->activa()
            ->latest('created_at')
            ->first();

        if (!$timelineAnnouncement) {
            $timelineAnnouncement = (clone $baseQuery)->cerrada()
                ->latest('created_at')
                ->first();
        }
        
        return Inertia::render('Home', [
            'canLogin' => Route::has('login'),
            'canRegister' => Features::enabled(Features::registration()),
            'announcements' => \App\Http\Resources\Catalog\AnnouncementResource::collection($announcements),
            'timelineAnnouncement' => $timelineAnnouncement ? new \App\Http\Resources\Catalog\AnnouncementResource($timelineAnnouncement) : null,
        ]);
    }

    /**
     * Show active or most recent announcement.
     */
    public function previewAnnouncement(Announcement $announcement): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        abort_unless(in_array($announcement->status, ['activa', 'cerrada'], true), 404);
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $path = $announcement->file_path;
        abort_unless($path && $disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function showAnnouncement()
    {
        // Priority: Activa > Cerrada
        $announcement = Announcement::with('calendar')
            ->activa()
            ->latest('created_at')
            ->first();

        if (!$announcement) {
            $announcement = Announcement::with('calendar')
                ->cerrada()
                ->latest('created_at')
                ->first();
        }
        
        return Inertia::render('Announcement', [
            'announcement' => $announcement ? new \App\Http\Resources\Catalog\AnnouncementResource($announcement) : null,
        ]);
    }

    /**
     * Email verification notice redirection.
     */
    public function verifyEmailNotice()
    {
        // If user is authenticated and verified, redirect to dashboard
        if (auth()->check() && auth()->user()->hasVerifiedEmail()) {
            /** @var User $user */
            $user = auth()->user();
            $role = $user->getPrimaryRole();
            
            return match ($role) {
                'Super Admin' => redirect()->route('superadmin.dashboard'),
                'Admin' => redirect()->route('admin.dashboard'),
                'Evaluador' => redirect()->route('evaluator.dashboard'),
                'Docente' => redirect()->route('teacher.dashboard'),
                default => redirect()->route('inicio'),
            };
        }
        
        // Obtener email del usuario autenticado o de la sesión
        $email = auth()->user()?->email ?? session('email');

        return Inertia::render('Auth/VerifyEmail', [
            'email' => $email,
            'status' => session('status'),
        ]);
    }
}
