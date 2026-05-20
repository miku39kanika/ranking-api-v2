<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gift;
use Carbon\Carbon;

class GiftSeeder extends Seeder
{
    public function run(): void
    {
        // =====================
        // case 1：全員・常時（currency）
        // =====================
        Gift::create([
            'title' => 'DLありがとうございます！',
            'body' => "このアプリをDLしてくださりありがとうございます。オーブ×100のプレゼントとなります。",
            'case' => 1,
            'user_id' => null,

            'reward_type' => 'currency',
            'reward_code' => 1,
            'reward_amount' => 100,

            'from_date' => null,
            'expires_at' => Carbon::now()->addDays(30),
        ]);
        // =====================
        // case 1(2つ目)：全員・常時（item_アイコン）
        // =====================
        Gift::create([
            'title' => 'DLありがとうございます！',
            'body' => "このアプリをDLしてくださりありがとうございます。限定アイコンのプレゼントとなります。",
            'case' => 1,
            'user_id' => null,

            'reward_type' => 'item',
            'reward_code' => 6,
            'reward_amount' => 1,

            'from_date' => null,
            'expires_at' => Carbon::now()->addDays(30),
        ]);
        // =====================
        // case 1(3つ目)：全員・常時（item_アイテム）
        // =====================
        Gift::create([
            'title' => 'DLありがとうございます！',
            'body' => "このアプリをDLしてくださりありがとうございます。限定アイテムのプレゼントとなります。",
            'case' => 1,
            'user_id' => null,

            'reward_type' => 'item',
            'reward_code' => 8, // ランキング作成チケット
            'reward_amount' => 3,

            'from_date' => null,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        // =====================
        // case 2：全員・期間制限あり（currency）
        // =====================
        Gift::create([
            'title' => 'GWイベント報酬',
            'body' => "GW期間中の参加報酬です。オーブ×200を配布しました。",
            'case' => 2,
            'user_id' => null,

            'reward_type' => 'currency',
            'reward_code' => 1,
            'reward_amount' => 200,

            'from_date' => Carbon::now()->subDays(3),
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        // =====================
        // case 3：個別ユーザー（item）
        // =====================
        Gift::create([
            'title' => '個別補填アイテム',
            'body' => "不具合のお詫びとしてレアアイテムを付与しました。",
            'case' => 3,
            'user_id' => "6A0552D5-6C20-4A7D-8B23-373B90F1033F",

            'reward_type' => 'item',
            'reward_code' => 1, // item_id
            'reward_amount' => 1,

            'from_date' => null,
            'expires_at' => Carbon::now()->addDays(14),
        ]);

        // =====================
        // case 3（別ユーザー用・currency）
        // =====================
        Gift::create([
            'title' => '運営からの特別ギフト',
            'body' => "特別ユーザー限定報酬です。",
            'case' => 3,
            'user_id' => "2",

            'reward_type' => 'currency',
            'reward_code' => 2,
            'reward_amount' => 50,

            'from_date' => null,
            'expires_at' => Carbon::now()->addDays(14),
        ]);
    }
}
