<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommentsTableSeeder extends Seeder
{
    public function run(): void
    {
        // user_01 ~ user_10
        $userIds = [];
        for ($i = 1; $i <= 10; $i++) {
            $userIds[] = "user_" . str_pad($i, 2, "0", STR_PAD_LEFT);
        }
        
        $rankingIds = [1, 2, 3, 4, 5]; // ←実際のrankingに合わせて調整

        $sampleComments = [
            'これめっちゃわかる',
            '1位これでいいの草',
            '異論は認める',
            '意外すぎるランキング',
            'これは納得',
            '自分は違うかな〜',
            '2位が一番好き',
            'このランキングセンスある',
            'これ作った人天才か？',
            'めっちゃ参考になる'
        ];

        foreach ($rankingIds as $rankingId) {
            foreach ($sampleComments as $comment) {
                DB::table('comments')->insert([
                    'ranking_id' => $rankingId,
                    'user_id' => $userIds[array_rand($userIds)], // 仮ユーザー
                    'body' => $comment,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}