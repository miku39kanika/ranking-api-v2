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
        // case 1：全員・常時
        // =====================
        Gift::create([
            'title' => 'ログインボーナス',
            'body' => "全ユーザーにorb×100を配布しました。",
            'case' => 1,
            'user_id' => null,
            'from_date' => null,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        // =====================
        // case 2：全員・期間制限あり
        // =====================
        Gift::create([
            'title' => 'GWイベント報酬',
            'body' => "GW期間中の参加報酬です。orb×200を配布しました。",
            'case' => 2,
            'user_id' => null,
            'from_date' => Carbon::now()->subDays(3),
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        // =====================
        // case 3：個別ユーザー
        // =====================
        Gift::create([
            'title' => '個別補填',
            'body' => "不具合のお詫びとしてorb×50を付与しました。",
            'case' => 3,
            'user_id' => "6A0552D5-6C20-4A7D-8B23-373B90F1033F", // ←テストユーザーID
            'from_date' => null,
            'expires_at' => Carbon::now()->addDays(14),
        ]);

        // =====================
        // case 3（別ユーザー用）
        // =====================
        Gift::create([
            'title' => '運営からの特別ギフト',
            'body' => "特別ユーザー限定報酬です。",
            'case' => 3,
            'user_id' => "2",
            'from_date' => null,
            'expires_at' => Carbon::now()->addDays(14),
        ]);
    }
}