<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemsTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('items')->insert([
            [
                'name' => 'アセット01',
                'description' => 'アイコン：初期登録時から所有',
                'type' => 'icon',
                'rarity' => 'common',
                'image_name' => 'ast01',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'アセット02',
                'description' => 'アイコン：初期登録時から所有',
                'type' => 'icon',
                'rarity' => 'common',
                'image_name' => 'ast02',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'アセット03',
                'description' => 'アイコン：初期登録時から所有',
                'type' => 'icon',
                'rarity' => 'common',
                'image_name' => 'ast03',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '初心者',
                'description' => '称号：初期登録時から所有',
                'type' => 'title',
                'rarity' => 'common',
                'image_name' => 'title01',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '伝説の勇者',
                'description' => '称号：特別な条件でのみ取得できる',
                'type' => 'title',
                'rarity' => 'epic',
                'image_name' => 'title99',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '伝説の勇者アイコン',
                'description' => 'アイコン：特別な条件でのみ取得できる',
                'type' => 'icon',
                'rarity' => 'epic',
                'image_name' => 'icon99',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}