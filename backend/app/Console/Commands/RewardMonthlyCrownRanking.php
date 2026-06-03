<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RewardMonthlyCrownRanking extends Command
{
    protected $signature = 'reward:monthly-crown-ranking';

    protected $description = 'Send gifts based on monthly crown ranking';

    public function handle()
    {
        $yearMonth = now()->subMonth()->format('Y-m');

        $latestSnapshot = DB::table('monthly_crown_rankings')
            ->where('year_month', $yearMonth)
            ->max('snapshot_date');

        if (!$latestSnapshot) {
            $this->info('snapshot not found');
            return;
        }

        $rankings = DB::table('monthly_crown_rankings')
            ->where('year_month', $yearMonth)
            ->where('snapshot_date', $latestSnapshot)
            ->where('rank', '<=', 20)
            ->orderBy('rank')
            ->get();

        DB::transaction(function () use ($rankings, $yearMonth) {

            foreach ($rankings as $row) {

                $rewards = $this->rewardsForRank($row->rank);

                foreach ($rewards as $reward) {
                    $this->sendGiftIfNotExists(
                        userId: $row->user_id,
                        yearMonth: $yearMonth,
                        rank: $row->rank,
                        rewardType: $reward['reward_type'],
                        rewardCode: $reward['reward_code'],
                        rewardAmount: $reward['reward_amount']
                    );
                }
            }
        });

        $this->info('monthly ranking rewards sent');
    }

    private function rewardsForRank(int $rank): array
    {
        if ($rank === 1) {
            return [
                ['reward_type' => 'item', 'reward_code' => '31', 'reward_amount' => 1],
                ['reward_type' => 'item', 'reward_code' => '32', 'reward_amount' => 1],
                ['reward_type' => 'item', 'reward_code' => '33', 'reward_amount' => 1],
                ['reward_type' => 'currency', 'reward_code' => 'orb', 'reward_amount' => 1000],
            ];
        }

        if ($rank <= 10) {
            return [
                ['reward_type' => 'item', 'reward_code' => '32', 'reward_amount' => 1],
                ['reward_type' => 'item', 'reward_code' => '33', 'reward_amount' => 1],
                ['reward_type' => 'currency', 'reward_code' => 'orb', 'reward_amount' => 500],
            ];
        }

        if ($rank <= 20) {
            return [
                ['reward_type' => 'item', 'reward_code' => '33', 'reward_amount' => 1],
                ['reward_type' => 'currency', 'reward_code' => 'orb', 'reward_amount' => 300],
            ];
        }

        return [];
    }

    private function sendGiftIfNotExists(
        string $userId,
        string $yearMonth,
        int $rank,
        string $rewardType,
        string $rewardCode,
        int $rewardAmount
    ): void {
        $title = '月間クラウンランキング報酬';

        $body = "{$yearMonth} 月間クラウンランキング {$rank}位 の報酬です。";

        $exists = DB::table('gifts')
            ->where('title', $title)
            ->where('body', $body)
            ->where('case', 3)
            ->where('user_id', $userId)
            ->where('reward_type', $rewardType)
            ->where('reward_code', $rewardCode)
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('gifts')->insert([
            'title' => $title,
            'body' => $body,
            'case' => 3,
            'user_id' => $userId,
            'reward_type' => $rewardType,
            'reward_code' => $rewardCode,
            'reward_amount' => $rewardAmount,
            'expires_at' => null,
            'from_date' => now(),
            'send_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
