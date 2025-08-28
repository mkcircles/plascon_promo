<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        //$schedule->command('command:sendAirtime')->everyMinute();
        $schedule->command('command:checkStatus')->everyFiveMinutes();
        $schedule->command('command:processFailedTAAirtimeTransactions')->everyFiveMinutes();
        $schedule->command('command:RetryATAirtimeTransaction')->everyFiveMinutes();
        $schedule->command('command:sendAirtimeEtherOne')->everyMinute();
        //$schedule->command('command:manageEtherOneKey')->everyMinute();
        //$schedule->command('command:')->everyMinute();

        //$schedule->command('command:generateCodes')->everyMinute();

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
