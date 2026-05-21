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
        // ユーザーごとの獲得vote数
        // =====================

        $results = DB::table('votes')
            ->join(
                'rankings',
                'votes.ranking_id',
                '=',
                'rankings.id'
            )
            ->where('votes.vote_date', $date)
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

            // =====================
            // crown付与
            // currency_id = 2
            // =====================

            DB::table('user_currencies')
                ->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'currency_id' => 2,
                    ],
                    [
                        'amount' => DB::raw(
                            'amount + ' . $amount
                        ),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

            // =====================
            // history
            // =====================

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

        $this->info('daily crowns rewarded');
    }
}
