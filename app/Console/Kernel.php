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
        $schedule->command('GetLines basketball_ncaab')->everyMinute();
        $schedule->command('GetLines basketball_nba')->everyMinute();
        $schedule->command('GetLines americanfootball_ncaaf')->everyMinute();
        $schedule->command('GetLines americanfootball_nfl')->everyMinute();
        $schedule->command('GetScores basketball_ncaab')->everyFourHours();
        $schedule->command('GetScores basketball_nba')->everyFourHours();
        $schedule->command('GetScores americanfootball_ncaaf')->everyFourHours();
        $schedule->command('GetScores americanfootball_nfl')->everyFourHours();
        $schedule->command('RetroMatchScores')->everySixHours();



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
