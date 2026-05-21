<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\GenerateGameQuestions;

// class Kernel extends ConsoleKernel
// {
//     protected function schedule(Schedule $schedule)
//     {
//         $schedule->command('game:generate')
//             ->dailyAt('00:00')->withoutOverlapping();

//         $schedule->command('gift:create-login-bonus')
//             ->dailyAt('00:05')->withoutOverlapping();

//         $schedule->command('reward:daily-crowns')
//             ->dailyAt('00:10')->withoutOverlapping();

//             $schedule->command('queue:work --stop-when-empty')
//             ->everyMinute()->withoutOverlapping();
//     }
// };
