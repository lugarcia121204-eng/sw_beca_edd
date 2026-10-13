<?php

namespace App\Console\Commands;

use App\Mail\WeeklyApplicationsSummary;
use App\Models\Announcement;
use App\Models\Application;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-weekly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly applications summary to administrators every Friday';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check if there's an active announcement
        $activeAnnouncement = Announcement::where('status', 'Activa')->first();

        if (!$activeAnnouncement) {
            $this->info('No active announcement found. Skipping weekly notification.');
            return;
        }

        // Calculate date range (last 7 days, Monday to Friday)
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(6); // Last 7 days including today

        // Get applications count per weekday
        $weeklyData = [
            'Lunes' => 0,
            'Martes' => 0,
            'Miércoles' => 0,
            'Jueves' => 0,
            'Viernes' => 0,
        ];

        // Map Carbon day number to Spanish day name
        $dayMapping = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
        ];

        // Get applications from the last week
        $applications = Application::whereBetween('created_at', [$startDate, $endDate])->get();

        foreach ($applications as $application) {
            $dayOfWeek = $application->created_at->dayOfWeek;
            
            // Only count Monday (1) through Friday (5)
            if (isset($dayMapping[$dayOfWeek])) {
                $dayName = $dayMapping[$dayOfWeek];
                $weeklyData[$dayName]++;
            }
        }

        $totalApplications = array_sum($weeklyData);

        // Get all administrators
        $admins = User::role('Admin')->get();

        // Create notification and send email to each administrator
        foreach ($admins as $admin) {
            // Create individual notification for each admin
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'Resumen Semanal de Solicitudes',
                'data' => ['total' => $totalApplications],
                'type' => 'weekly_summary',
            ]);

            // Send email
            try {
                Mail::to($admin->email)->queue(new WeeklyApplicationsSummary($weeklyData, $totalApplications));
                $this->info("Email sent to {$admin->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send email to {$admin->email}: {$e->getMessage()}");
            }
        }

        $this->info("Weekly notifications created and emails sent to {$admins->count()} administrators.");
        $this->info("Total applications this week: {$totalApplications}");
    }
}
