<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Transição unpaid/partially_paid → overdue + notificação in-app
        $schedule->command('finance:check-overdue')->dailyAt('06:00');

        // Lembretes por e-mail de faturas ainda em aberto (inclui overdue)
        $schedule->command('payments:send-reminders')->dailyAt('08:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
