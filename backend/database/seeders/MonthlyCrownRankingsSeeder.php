<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MonthlyCrownRankingsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];

        for ($i = 1; $i <= 20; $i++) {

            $rows[] = [
                'year_month' => '2026-05',
                'user_id' => sprintf('user_%02d', $i),
                'crown_amount' => rand(500, 5000),
                'rank' => $i,
                'snapshot_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('monthly_crown_rankings')
            ->insert($rows);
    }
}
