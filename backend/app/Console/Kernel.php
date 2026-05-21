<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\GenerateGameQuestions;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('game:generate')
            ->everyMinute()->withoutOverlapping();

        $schedule->command('gift:create-login-bonus')
            ->everyMinute()->withoutOverlapping();

        $schedule->command('reward:daily-crowns')
            ->everyMinute()->withoutOverlapping();
    }
};
