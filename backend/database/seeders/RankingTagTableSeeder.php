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
                'ranking_id' => 1,
                'tag_id' => 16,
            ],

            [
                'ranking_id' => 2,
                'tag_id' => 16,
            ],
        ]);
    }
}
