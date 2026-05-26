<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankingTagTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ranking_tag')->insert([

            [
                'ranking_id' => 31,
                'tag_id' => 16,
            ],

            [
                'ranking_id' => 32,
                'tag_id' => 16,
            ],
        ]);
    }
}
