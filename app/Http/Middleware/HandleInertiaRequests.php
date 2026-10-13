<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        // Get unread notifications count for Admin, Evaluador, Docente
        $unreadNotifications = 0;
        if ($request->user() && $request->user()->hasAnyRole(['Admin', 'Evaluador', 'Docente'])) {
            // Check if user_id column exists in notifications table
            if (\Schema::hasColumn('notifications', 'user_id')) {
                // Only count notifications assigned to this specific user
                $unreadNotifications = \App\Models\Notification::unread()
                    ->where('user_id', $request->user()->id)
                    ->count();
            } else {
                // Fallback to old behavior (all notifications)
                $unreadNotifications = \App\Models\Notification::unread()->count();
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
                'root' => $request->user() ? $request->user()->isSuperAdmin() : null,
                'roles' => $request->user() ? $request->user()->getRolesArray() : [],
                'can' => $request->user() ? $request->user()->getPermissionArray() : [],
                'primaryRole' => $request->user() ? $request->user()->getPrimaryRole() : null,
                'layoutName' => $request->user() ? $request->user()->getLayoutName() : null,
                'roles_list' => $request->user() ? $request->user()->getRolesList() : [],
            ],
            'unreadNotifications' => $unreadNotifications,
            'sidebarOpen' => !$request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success'            => fn() => $request->session()->get('success'),
                'error'              => fn() => $request->session()->get('error'),
                'download_backup_id' => fn() => $request->session()->get('download_backup_id'),
            ],
        ];
    }
}
