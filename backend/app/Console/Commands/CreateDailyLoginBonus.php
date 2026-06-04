<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Gift;
use Carbon\Carbon;

class CreateDailyLoginBonus extends Command
{
    protected $signature = 'gift:create-login-bonus';

    protected $description = 'Create daily login bonus gift';

    public function handle()
    {
        // =====================
        // 今日のログインボーナス存在確認
        // =====================

        $exists = Gift::where('title', 'ログインボーナス！')
            ->whereDate('created_at', today())
            ->exists();

        if (!$exists) {

            Gift::create([
                'title' => 'ログインボーナス！',
                'body' => 'ログインボーナスです。ランキング作成チケット(使用期限:当日)を1枚プレゼント！',
                'case' => 1,

                // reward
                'reward_type' => 'item',
                'reward_code' => 8,
                'reward_amount' => 1,

                // 受け取り期限
                'expires_at' => now()->endOfDay(),

                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info('login bonus created');
        }

        // =====================
        // 広告視聴giftを作り直す
        // =====================

        Gift::where('title', '動画広告を見て作成チケットGET!')
            ->delete();

        Gift::create([
            'title' => '動画広告を見て作成チケットGET!',
            'body' => '動画広告を見てランキング作成チケットをGET！',
            'case' => 1,
            'user_id' => null,

            'reward_type' => 'rewarded_ad',
            'reward_code' => 8,
            'reward_amount' => 1,

            'from_date' => null,
            'expires_at' => Carbon::now()->endOfDay(),

            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('rewarded ad gift recreated');
    }
}
