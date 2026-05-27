<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateSeasonCrownRanking extends Command
{
    protected $signature =
    'ranking:update-monthly-crowns';

    protected $description =
    'Update monthly crown rankings';

    public function handle()
    {
        // =====================
        // 今月
        // 例: 2026-05
        // =====================

        $yearMonth = now()->format('Y-m');

        // =====================
        // 今月開始
        // =====================

        $startOfMonth =
            now()->startOfMonth();

        // =====================
        // 今月のクラウン獲得数集計
        // currency_id = 2
        // amount > 0 のみ
        // =====================

        $users = DB::table('currency_histories')
            ->select(
                'user_id',
                DB::raw('SUM(amount) as crown_amount')
            )
            ->where('currency_id', 2)
            ->where('amount', '>', 0)
            ->where(
                'created_at',
                '>=',
                $startOfMonth
            )
            ->groupBy('user_id')
            ->orderByDesc('crown_amount')
            ->get();

        $rank = 1;

        foreach ($users as $user) {

            DB::table('monthly_crown_rankings')
                ->updateOrInsert(
                    [
                        'year_month' => $yearMonth,

                        'user_id' => $user->user_id,

                        'snapshot_date' => today(),
                    ],
                    [
                        'crown_amount' =>
                        (int) $user->crown_amount,

                        'rank' => $rank,

                        'updated_at' => now(),

                        'created_at' => now(),
                    ]
                );

            $rank++;
        }

        $this->info(
            'monthly crown rankings updated'
        );
    }
}
