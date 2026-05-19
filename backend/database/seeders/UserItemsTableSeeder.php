<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserItemsTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $user_items = [];

        // 固定ユーザー（自分用）
        for ($i = 1; $i <= 4; $i++) {
            $user_items[] = [
                'user_id' => '00000000-0000-0000-0000-000000000000',
                'item_id' => $i, // ast01~03（アイコン）と称号：初心者
                'quantity' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // 追加19人
            for ($i = 2; $i <= 20; $i++) {
                $uuid = (string) Str::uuid();
                for ($j = 1; $j <= 4; $j++) {
                    $user_items[] = [
                        'user_id' => $uuid,
                        'item_id' => $j, // ast01~03（アイコン）と称号：初心者
                        'quantity' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            DB::table('user_items')->insert($user_items);
        }
    }
}
