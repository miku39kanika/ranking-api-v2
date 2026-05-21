<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Gift;

class CreateDailyLoginBonus extends Command
{
    protected $signature = 'gift:create-login-bonus';

    protected $description = 'Create daily login bonus gift';

    public function handle()
    {
        // =====================
        // 今日すでに存在するか
        // =====================

        $exists = Gift::where('title', 'Login Bonus')
            ->whereDate('created_at', today())
            ->exists();

        if ($exists) {

            $this->info('already exists');

            return;
        }

        Gift::create([
            'title' => 'Login Bonus',
            'body' => 'ログインボーナスです！',
            'case' => 1,

            // reward
            'reward_type' => 'currency',
            'reward_code' => 1,
            'reward_amount' => 10,

            // 受け取り期限
            'expires_at' => now()->addDay(),

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('login bonus created');
    }
}
