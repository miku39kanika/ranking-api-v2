<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Announcement;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        Announcement::create([
            'title' => 'DLありがとうございます！',
            'body' => "・この度は当アプリをダウンロードいただきありがとうございます！\nこのアプリは個人開発となっており、また、現在はベータ版としてのプレリリースとなっております。\nそのため多くのバグが見受けられるかと思います。\n一時的にホーム画面にフィードバック用のフォームを設置しておりますので、\n不具合がありましたらお手数ですがご報告いただけますと幸いです。\nps.「楽しいよ！」などの意見も言って欲しいです。\nよろしくお願いいたします。",
            'important' => true,
        ]);

        Announcement::create([
            'title' => '今後のアップデート予定/構想',
            'body' => "今後はガチャ機能、ランキングフレームや称号の追加を予定しています。",
            'important' => false,
        ]);
    }
}
