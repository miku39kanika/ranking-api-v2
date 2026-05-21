<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('game:generate')
    ->dailyAt('00:00')
    ->withoutOverlapping();

Schedule::command('gift:create-login-bonus')
    ->dailyAt('00:00')
    ->withoutOverlapping();

Schedule::command('reward:daily-crowns')
    ->dailyAt('00:00')
    ->withoutOverlapping();

Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();
