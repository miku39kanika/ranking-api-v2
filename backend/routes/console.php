<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('game:generate')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('gift:create-login-bonus')
    ->everyMinute()
    ->withoutOverlapping();

Schedule::command('reward:daily-crowns')
    ->everyMinute()
    ->withoutOverlapping();
