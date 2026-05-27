<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RewardMonthlyCrownRanking extends Command
{
    protected $signature =
    'reward:monthly-crown-ranking';

    protected $description =
    'Send gifts based on monthly crown ranking';

    public function handle()
    {
        // =====================
        // 前月
        // =====================

        $yearMonth = now()
            ->subMonth()
            ->format('Y-m');

        // =====================
        // 最新snapshot取得
        // =====================

        $latestSnapshot = DB::table(
            'monthly_crown_rankings'
        )
            ->where('year_month', $yearMonth)
            ->max('snapshot_date');

        if (!$latestSnapshot) {

            $this->info('snapshot not found');

            return;
        }

        // =====================
        // 最終ランキング取得
        // =====================

        $rankings = DB::table(
            'monthly_crown_rankings'
        )
            ->where('year_month', $yearMonth)
            ->where(
                'snapshot_date',
                $latestSnapshot
            )
            ->orderBy('rank')
            ->get();

        foreach ($rankings as $row) {

            $rewardAmount = 0;

            // =====================
            // 報酬決定
            // =====================

            if ($row->rank === 1) {

                $rewardAmount = 3000;
            } elseif ($row->rank <= 3) {

                $rewardAmount = 2000;
            } elseif ($row->rank <= 10) {

                $rewardAmount = 1000;
            } elseif ($row->rank <= 50) {

                $rewardAmount = 300;
            } else {

                continue;
            }

            // =====================
            // gift送信
            // =====================

            DB::table('gifts')
                ->insert([
                    'title' =>
                    '月間クラウンランキング報酬',

                    'body' =>
                    "{$yearMonth} 月間クラウンランキング {$row->rank}位 の報酬です。",

                    'case' => 3,

                    'user_id' => $row->user_id,

                    'reward_type' => 'currency',

                    // crown
                    'reward_code' => 'crown',

                    'reward_amount' => $rewardAmount,

                    'expires_at' =>
                    now()->addMonths(3),

                    'from_date' => now(),

                    'send_at' => now(),

                    'created_at' => now(),

                    'updated_at' => now(),
                ]);
        }

        $this->info(
            'monthly ranking rewards sent'
        );
    }
}
