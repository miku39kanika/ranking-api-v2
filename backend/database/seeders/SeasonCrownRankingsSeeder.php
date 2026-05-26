<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeasonCrownRankingsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];

        for ($i = 1; $i <= 10; $i++) {

            $rows[] = [
                'season' => 1,
                'user_id' => sprintf('user_%02d', $i),
                'crown_amount' => rand(500, 5000),
                'rank' => $i,
                'snapshot_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('season_crown_rankings')
            ->insert($rows);
    }
}
