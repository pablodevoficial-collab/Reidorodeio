<?php

namespace App\Console;

use App\Jobs\WarmFantasyRankingCache;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('backup:database')->dailyAt('03:00');

        $schedule->job(new WarmFantasyRankingCache())->everyFiveMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}
