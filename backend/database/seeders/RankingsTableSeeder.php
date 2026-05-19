<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RankingsTableSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $userIds[] = "user_" . str_pad($i, 2, "0", STR_PAD_LEFT);
        }

        $rankings = [];
        $rankingIds = [];

        // -------------------------
        // ① rankings 100件作成
        // -------------------------
        for ($i = 1; $i <= 100; $i++) {

            $titleList = [
                "好きな食べ物ランキング",
                "人気アニメランキング",
                "行きたい旅行先ランキング",
                "好きな映画ランキング",
                "よく使うアプリランキング",
                "好きな動物ランキング",
                "学生時代の思い出ランキング",
                "コンビニ商品ランキング",
                "好きなゲームランキング",
                "朝食に食べたいものランキング"
            ];

            $title = $titleList[array_rand($titleList)];

            DB::table('rankings')->insert([
                'title' => $title,
                'reading' => null,
                'tag' => "test,ranking,game",
                'image_name' => "img_" . rand(1, 10) . ".jpg",
                'is_item_add_limited' => rand(0, 1),
                'daily_vote_limit' => rand(1, 3),
                'total_vote_limit' => rand(5, 20),
                'vote_permission' => 'publicAccess',
                'user_id' => $userIds[array_rand($userIds)],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            $rankingIds[] = DB::getPdo()->lastInsertId();
        }

        // -------------------------
        // ② ranking_items 500件作成
        // -------------------------

        $itemPool = [
            "ラーメン",
            "寿司",
            "焼肉",
            "カレー",
            "ハンバーグ",
            "うどん",
            "そば",
            "ピザ",
            "パスタ",
            "寿司ロール",
            "鬼滅の刃",
            "ワンピース",
            "ナルト",
            "進撃の巨人",
            "呪術廻戦",
            "東京",
            "大阪",
            "京都",
            "北海道",
            "沖縄",
            "iPhone",
            "Android",
            "MacBook",
            "iPad",
            "Windows PC",
            "猫",
            "犬",
            "ハムスター",
            "うさぎ",
            "鳥",
            "YouTube",
            "TikTok",
            "Instagram",
            "Twitter",
            "LINE"
        ];

        for ($i = 1; $i <= 500; $i++) {

            DB::table('ranking_items')->insert([
                'ranking_id' => $rankingIds[array_rand($rankingIds)],
                'name' => $itemPool[array_rand($itemPool)] . " #" . rand(1, 100),
                'votes' => rand(0, 1000),
                'aliases' => json_encode([]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
