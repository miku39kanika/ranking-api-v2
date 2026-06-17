<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RankingWithItemsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('rankings')->insert([
            'id' => 1,
            'ranking_type' => 1,
            'title' => "公式：今月の獲得クラウンランキング",
            'reading' => "こうしき：こんげつのかくとくくらうんらんきんぐ",
            'image_name' => "official_default",
            'is_item_add_limited' => 1,
            'daily_vote_limit' => 0,
            'total_vote_limit' => 0,
            'vote_permission' => 'invite_only_view',
            'user_id' => "user_99",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $rankingId = DB::table('rankings')->insertGetId([
            'id' => 2,
            'ranking_type' => 1,
            'title' => "公式：要望アンケート",
            'reading' => "こうしき：ようぼうあんけーと",
            'tag' => "公式",
            'image_name' => "official_default",
            'is_item_add_limited' => 0,
            'daily_vote_limit' => 1,
            'total_vote_limit' => 1,
            'vote_permission' => 'public_access',
            'user_id' => "user_99",
            'created_at' => $now,
            'updated_at' => $now,
        ]);


        // user_01 ~ user_10
        $userIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $userIds[] = "user_" . str_pad($i, 2, "0", STR_PAD_LEFT);
        }

        // -------------------------
        // ランキングテーマ + アイテム
        // -------------------------
        $createRanking = function (array $ranking, array $items) use ($now) {

            $rankingId = DB::table('rankings')->insertGetId(array_merge([
                'ranking_type' => 0,
                'reading' => null,
                'tag' => null,
                'image_name' => 'sample1',
                'is_item_add_limited' => 0,
                'daily_vote_limit' => 1,
                'total_vote_limit' => 10,
                'vote_permission' => 'public_access',
                'created_at' => $now,
                'updated_at' => $now,
            ], $ranking));

            foreach ($items as $item) {
                DB::table('ranking_items')->insert([
                    'ranking_id' => $rankingId,
                    'name' => $item['name'],
                    'votes' => $item['votes'] ?? 0,
                    'aliases' => json_encode($item['aliases'] ?? []),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        };
        $createRanking([
            'title' => '好きなラーメンの種類ランキング',
            'reading' => 'すきならーめんのしゅるいらんきんぐ',
            'tag' => 'グルメ',
            'image_name' => 'food_default',
            'is_item_add_limited' => 1,
            'daily_vote_limit' => 1,
            'total_vote_limit' => 10,
            'vote_permission' => 'public_access',
            'user_id' => 'user_01',
        ], [
            ['name' => '醤油ラーメン', 'votes' => 42],
            ['name' => '味噌ラーメン', 'votes' => 38],
            ['name' => '塩ラーメン', 'votes' => 26],
            ['name' => '豚骨ラーメン', 'votes' => 51],
            ['name' => '家系ラーメン', 'votes' => 33],
            ['name' => '二郎系ラーメン', 'votes' => 21],
            ['name' => '担々麺', 'votes' => 19],
            ['name' => '魚介系ラーメン', 'votes' => 16],
            ['name' => 'つけ麺', 'votes' => 29],
            ['name' => '油そば', 'votes' => 9],
        ]);

        $createRanking([
            'title' => '人気アニメランキング',
            'reading' => 'にんきあにめらんきんぐ',
            'tag' => 'アニメ',
            'image_name' => 'anime_default',
            'is_item_add_limited' => 1,
            'daily_vote_limit' => 1,
            'total_vote_limit' => 10,
            'vote_permission' => 'public_access',
            'user_id' => 'user_02',
        ], [
            ['name' => '進撃の巨人', 'votes' => 19],
            ['name' => '呪術廻戦', 'votes' => 36],
            ['name' => '鬼滅の刃', 'votes' => 29],
            ['name' => 'ワンピース', 'votes' => 32],
            ['name' => 'ナルト', 'votes' => 27],
            ['name' => 'ハンターハンター', 'votes' => 26],
            ['name' => 'リゼロ', 'votes' => 18],
            ['name' => 'SAO', 'votes' => 11],
            ['name' => 'ヒロアカ', 'votes' => 28],
            ['name' => 'スパイファミリー', 'votes' => 7],
        ]);

        $createRanking([
            'title' => '行きたい旅行先ランキング',
            'reading' => 'いきたいりょこうさきらんきんぐ',
            'tag' => '旅行',
            'image_name' => 'travel_default',
            'is_item_add_limited' => 0,
            'daily_vote_limit' => 1,
            'total_vote_limit' => 5,
            'vote_permission' => 'public_access',
            'user_id' => 'user_03',
        ], [
            ['name' => '北海道', 'votes' => 45],
            ['name' => '沖縄', 'votes' => 36],
            ['name' => '京都', 'votes' => 23],
            ['name' => '大阪', 'votes' => 16],
            ['name' => '福岡', 'votes' => 9],
            ['name' => 'ハワイ', 'votes' => 28],
            ['name' => 'パリ', 'votes' => 13],
            ['name' => '台湾', 'votes' => 10],
            ['name' => '韓国', 'votes' => 16],
            ['name' => 'シンガポール', 'votes' => 11],
        ]);
        //'public_access','invite_only_view', 'invite_only_hidden'
    }
}
