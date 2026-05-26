<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('currencies')->insert([

            [
                'code' => 'orb',
                'name' => 'オーブ',
                'icon' => '🔮',
                'description' => 'ガチャや投票に使用する通貨',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'code' => 'crown',
                'name' => 'クラウン',
                'icon' => '👑',
                'description' => '特別な報酬用通貨',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
