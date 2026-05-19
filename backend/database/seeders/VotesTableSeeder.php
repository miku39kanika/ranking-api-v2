<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VotesTableSeeder extends Seeder
{
    public function run(): void
    {
        $votes = [];

        // 👇 適当に100件くらい生成
        for ($i = 0; $i < 100; $i++) {
            $votes[] = [
                'ranking_item_id' => rand(1, 5), // ← item数に合わせる
                'ranking_id' => rand(1, 3), // ← ranking数に合わせる
                'user_identifier' => (string) Str::uuid(),
                'vote_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('votes')->insert($votes);
    }
}
