<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        Announcement::create([
            'title' => '05/11 アップデート',
            'body' => "・コメント機能を追加しました\n・通知画面を追加しました\n・軽微な不具合を修正しました",
            'important' => true,
        ]);

        Announcement::create([
            'title' => 'GWイベント開催！',
            'body' => "イベント期間中はランキング作成でorbを追加獲得できます！",
            'important' => false,
        ]);

        Announcement::create([
            'title' => '今後のアップデート予定',
            'body' => "今後はガチャ機能、アイコン追加、ランキングフレーム追加を予定しています。",
            'important' => false,
        ]);
    }
}