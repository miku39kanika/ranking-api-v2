<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSeasonCrownRanking extends Command
{
    protected $signature =
    'ranking:update-season-crowns';

    protected $description =
    'Update season crown rankings';

    public function handle()
    {
        // =====================
        // 最新season取得
        // =====================

        $latestSeason = DB::table('user_currencies')
            ->max('season');

        if (!$latestSeason) {

            $this->info('season not found');

            return;
        }

        // =====================
        // 最新seasonのcrown取得
        // currency_id = 2
        // =====================

        $users = DB::table('user_currencies')
            ->where('currency_id', 2)
            ->where('season', $latestSeason)
            ->orderByDesc('amount')
            ->get();

        $rank = 1;

        foreach ($users as $user) {

            DB::table('season_crown_rankings')
                ->updateOrInsert(
                    [
                        'season' => $latestSeason,
                        'user_id' => $user->user_id,
                        'snapshot_date' => today(),
                    ],
                    [
                        'crown_amount' => $user->amount,
                        'rank' => $rank,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );

            $rank++;
        }

        $this->info(
            'season crown rankings updated'
        );
    }
}
