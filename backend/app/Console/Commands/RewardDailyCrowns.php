<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RewardDailyCrowns extends Command
{
    protected $signature = 'reward:daily-crowns';

    protected $description =
    'Reward crowns based on yesterday votes';

    public function handle()
    {
        // =====================
        // 昨日
        // =====================

        $date = now()->subDay()->toDateString();

        // =====================
        // ランキング獲得票 reward
        // =====================

        $results = DB::table('votes')
            ->join(
                'rankings',
                'votes.ranking_id',
                '=',
                'rankings.id'
            )
            ->where('votes.vote_date', $date)
            ->where('rankings.vote_permission', 'public_access')
            ->select(
                'rankings.user_id',
                DB::raw('COUNT(votes.id) as vote_count')
            )
            ->groupBy('rankings.user_id')
            ->get();

        foreach ($results as $row) {

            $userId = $row->user_id;

            $amount = $row->vote_count;

            if ($amount <= 0) {
                continue;
            }

            // なければ作成
            DB::table('user_currencies')
                ->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'currency_id' => 2,
                    ],
                    [
                        'amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

            // 加算
            DB::table('user_currencies')
                ->where('user_id', $userId)
                ->where('currency_id', 2)
                ->increment('amount', $amount);

            DB::table('currency_histories')
                ->insert([
                    'user_id' => $userId,
                    'currency_id' => 2,
                    'amount' => $amount,
                    'reason' => 'DAILY_VOTE_REWARD',
                    'note' => "daily votes: {$amount}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        // =====================
        // 投票回数 reward
        // 1vote = 2 crown
        // =====================

        $voteRewards = DB::table('votes')
            ->where('vote_date', $date)
            ->select(
                'user_identifier',
                DB::raw('COUNT(id) as vote_count')
            )
            ->groupBy('user_identifier')
            ->get();

        foreach ($voteRewards as $row) {

            $userId = $row->user_identifier;

            $amount = $row->vote_count * 5;

            if ($amount <= 0) {
                continue;
            }

            DB::table('user_currencies')
                ->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'currency_id' => 2,
                    ],
                    [
                        'amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

            DB::table('user_currencies')
                ->where('user_id', $userId)
                ->where('currency_id', 2)
                ->increment('amount', $amount);

            DB::table('currency_histories')
                ->insert([
                    'user_id' => $userId,
                    'currency_id' => 2,
                    'amount' => $amount,
                    'reason' => 'DAILY_CAST_REWARD',
                    'note' => "daily cast votes: {$amount}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $this->info('daily crowns rewarded');
    }
}
