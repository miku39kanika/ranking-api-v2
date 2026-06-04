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
            'body' => "・この度は当アプリをダウンロードいただきありがとうございます！\nこのアプリは個人開発となっており、また、現在はベータ版としてのプレリリースとなっております。\nそのため多くのバグが見受けられるかと思います。\n一時的にホーム画面にフィードバック用のフォームを設置しておりますので、\n不具合がありましたらお手数ですがご報告いただけますと幸いです。\n何卒よろしくお願いいたします。",
            'important' => true,
        ]);

        Announcement::create([
            'title' => '今後のアップデート予定/構想',
            'body' => "■ 今後の見通し\n
\n
【国外リリース】\n
・各国のランキングを閲覧できる！\n
・ランキングタイトルは共有で、投票ページは各国のものを用意！\n
・国毎の価値観の差がわかるかも！？\n
\n
【プロフィール装飾】\n
・現在アイコンのみのところ、称号やフレームも実装予定！\n
・それに伴ってガチャ機能も追加を想定！\n
\n
\n
【ランキング装飾】\n
・ランキング毎にフレームやテーマを変えられる！？\n
・上位の項目にはオリジナル画像を設定可能に",
            'important' => false,
        ]);
    }
}
