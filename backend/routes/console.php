<?php

use Illuminate\Support\Facades\Schedule;

// デイリーゲーム生成
Schedule::command('game:generate')
    ->dailyAt('05:00')
    ->withoutOverlapping();

// ログインボーナスgift生成
Schedule::command('gift:create-login-bonus')
    ->dailyAt('00:00')
    ->withoutOverlapping();

// デイリーcrown配布
Schedule::command('reward:daily-crowns')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// 月間crownランキング更新
Schedule::command(
    'ranking:update-monthly-crowns'
)->dailyAt('05:00');

// 月初ランキング報酬
Schedule::command(
    'reward:monthly-crown-ranking'
)->monthlyOn(1, '00:10');

// queue
Schedule::command('queue:work --stop-when-empty')
    ->everyMinute()
    ->withoutOverlapping();

// plan期限切れ
Schedule::command('plan:expire')
    ->daily();

// ベータ版 Bot投票
Schedule::command('bot:vote-rankings')
    ->everyMinute()
    ->withoutOverlapping();
