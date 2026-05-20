<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StarterItemsTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('starter_items')->insert([
            [
                'item_id' => 1, // ast01（アイコン）
                'quantity' => 1,
                'is_active' => true,
                'trigger' => 'register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 2, // ast02（アイコン）
                'quantity' => 3,
                'is_active' => true,
                'trigger' => 'register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 3, // ast03（アイコン）
                'quantity' => 1,
                'is_active' => true,
                'trigger' => 'register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 4, // 称号：初心者
                'quantity' => 1,
                'is_active' => true,
                'trigger' => 'register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 7, // アイテム：ガチャチケ
                'quantity' => 5,
                'is_active' => true,
                'trigger' => 'register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'item_id' => 8, // アイテム：ランキング作成チケット
                'quantity' => 3,
                'is_active' => true,
                'trigger' => 'register',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
