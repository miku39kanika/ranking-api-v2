<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankingItemsTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // =========================
        // 🍜 食べ物（ranking_id = 1）
        // =========================
        $foodItems = [
            'ラーメン味噌',
            '塩ラーメン',
            '家系ラーメン',
            '担々麺',
            'カツ丼',
            '天丼',
            '親子丼',
            '焼き鳥',
            'もつ鍋',
            '寿司（炙り）'
        ];

        foreach ($foodItems as $name) {
            DB::table('ranking_items')->insert([
                'ranking_id' => 1,
                'name' => $name,
                'votes' => rand(1, 100),
                'aliases' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // =========================
        // 🎬 アニメ（ranking_id = 2）
        // =========================
        $animeItems = [
            '進撃の巨人',
            '呪術廻戦',
            '鬼滅の刃',
            'ワンピース',
            'ナルト',
            'ハンターハンター',
            'リゼロ',
            'SAO',
            'ヴァイオレット・エヴァーガーデン',
            'ヒロアカ'
        ];

        foreach ($animeItems as $name) {
            DB::table('ranking_items')->insert([
                'ranking_id' => 2,
                'name' => $name,
                'votes' => rand(1, 100),
                'aliases' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // =========================
        // ✈️ 旅行（ranking_id = 3）
        // =========================
        $travelItems = [
            'ハワイ',
            'パリ',
            'ニューヨーク',
            'バリ島',
            '北海道',
            '京都',
            '沖縄',
            'ロンドン',
            'ローマ',
            'シンガポール'
        ];

        foreach ($travelItems as $name) {
            DB::table('ranking_items')->insert([
                'ranking_id' => 3,
                'name' => $name,
                'votes' => rand(1, 100),
                'aliases' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // =========================
        // 🎮 ゲーム（ranking_id = 4）
        // =========================
        $gameItems = [
            'ゼルダの伝説',
            'マリオ',
            'ポケモン',
            'スプラトゥーン',
            'エルデンリング',
            'ダークソウル',
            'FF7',
            '原神',
            'APEX',
            'マイクラ'
        ];

        foreach ($gameItems as $name) {
            DB::table('ranking_items')->insert([
                'ranking_id' => 4,
                'name' => $name,
                'votes' => rand(1, 100),
                'aliases' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
