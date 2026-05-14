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

        // user_01 ~ user_10
        $userIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $userIds[] = "user_" . str_pad($i, 2, "0", STR_PAD_LEFT);
        }

        // -------------------------
        // ランキングテーマ + アイテム
        // -------------------------
        $data = [

            "好きなラーメンの種類ランキング" => [
                "醤油ラーメン","味噌ラーメン","塩ラーメン","豚骨ラーメン","家系ラーメン",
                "二郎系ラーメン","担々麺","魚介系ラーメン","つけ麺","油そば"
            ],

            "人気アニメランキング" => [
                "進撃の巨人","呪術廻戦","鬼滅の刃","ワンピース","ナルト",
                "ハンターハンター","リゼロ","SAO","ヒロアカ","スパイファミリー"
            ],

            "行きたい旅行先ランキング" => [
                "ハワイ","パリ","ニューヨーク","バリ島","北海道",
                "京都","沖縄","ロンドン","ローマ","シンガポール"
            ],

            "よく使うSNSランキング" => [
                "LINE","Instagram","X（Twitter）","TikTok","YouTube",
                "Facebook","Discord","Snapchat","BeReal","Threads"
            ],

            "好きなゲームランキング" => [
                "ゼルダの伝説","マリオ","ポケモン","スプラトゥーン","APEX",
                "マインクラフト","原神","FF7","エルデンリング","ダークソウル"
            ],

            "好きなコンビニ商品ランキング" => [
                "おにぎり","からあげ","サンドイッチ","弁当","スイーツ",
                "カップラーメン","パン","ホットスナック","アイス","コーヒー"
            ],

            "好きな動物ランキング" => [
                "犬","猫","ハムスター","うさぎ","鳥",
                "フェレット","リス","カメ","熱帯魚","チンチラ"
            ],

            "朝食に食べたいものランキング" => [
                "トースト","ご飯","卵かけご飯","ヨーグルト","シリアル",
                "パンケーキ","サンドイッチ","味噌汁","フルーツ","納豆"
            ],

            "好きな映画ジャンルランキング" => [
                "アクション","コメディ","ホラー","恋愛","SF",
                "ファンタジー","ミステリー","アニメ","ドキュメンタリー","サスペンス"
            ],

            "学生時代の思い出ランキング" => [
                "修学旅行","文化祭","体育祭","部活","放課後",
                "テスト","友達との遊び","恋愛","卒業式","合唱コンクール"
            ],
        ];

        // 👇 30件作るために3周
        $loop = 3;

        foreach (range(1, $loop) as $round) {

            foreach ($data as $title => $items) {

                // --- ranking作成 ---
                $rankingId = DB::table('rankings')->insertGetId([
                    'ranking_type' => 0,
                    'title' => $title,
                    'reading' => null,
                    'tag' => "test,ranking,game",
                    'image_name' => "sample1",
                    'is_item_add_limited' => rand(0, 1),
                    'daily_vote_limit' => rand(1, 3),
                    'total_vote_limit' => rand(5, 20),
                    'vote_permission' => 'publicAccess',
                    'user_id' => $userIds[array_rand($userIds)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // --- items作成（10件） ---
                foreach ($items as $name) {
                    DB::table('ranking_items')->insert([
                        'ranking_id' => $rankingId,
                        'name' => $name,
                        'votes' => rand(10, 500), // ある程度差をつける
                        'aliases' => json_encode([]),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
        $rankingId = DB::table('rankings')->insertGetId([
                    'ranking_type' => 1,
                    'title' => "公式：サンプルアンケート",
                    'reading' => "こうしき：さんぷるあんけーと",
                    'tag' => "official",
                    'image_name' => "official1",
                    'is_item_add_limited' => 0,
                    'daily_vote_limit' => 1,
                    'total_vote_limit' => 1,
                    'vote_permission' => 'publicAccess',
                    'user_id' => "user_99",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                 $rankingId = DB::table('rankings')->insertGetId([
                    'ranking_type' => 1,
                    'title' => "公式：サンプルアンケート",
                    'reading' => "こうしき：さんぷるあんけーと",
                    'tag' => "official",
                    'image_name' => "official1",
                    'is_item_add_limited' => 1,
                    'daily_vote_limit' => 0,
                    'total_vote_limit' => 0,
                    'vote_permission' => 'publicAccess',
                    'user_id' => "user_99",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
    }
}