<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\UserCurrency;
use App\Models\Currency;
use App\Models\CurrencyHistory;
use App\Models\PersonalRanking;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function guestLogin(Request $request)
{Log::info('guestLogin called');
        // 既存ユーザー確認（同じ端末）
        $user = User::where('device_id', $request->device_id)->first();

        // なければ作成
        if (!$user) {
            Log::info('guestcreated called');
            $user = User::create([
    'id' => (string) Str::uuid(),
    'public_id' => $this->generatePublicId(),
    'user_name' => '名無しのユーザー',
    'device_id' => $request->device_id,
    'email' => null,
    'plan_type'=> 0,
    'icon_type' => 'ast',
    'icon_name' => "ast01",
    'about_self' => 'よろしくお願いします！',
    'is_deleted' => false,
    'banned_at' => null,
]);
 // orb取得
            $orb = Currency::where('code', 'orb')->first();

            // 現在所持数
            UserCurrency::create([
                'user_id' => $user->id,
                'currency_id' => $orb->id,
                'amount' => 100,
            ]);

            // 履歴
            CurrencyHistory::create([
                'user_id' => $user->id,
                'currency_id' => $orb->id,
                'amount' => 100,
                'reason' => '初回登録ボーナス',
            ]);
            
            $ranking = PersonalRanking::create([
    'user_id' => $user->id,
    'title' => '',
]);

$ranking->items()->createMany([
    [
        'rank' => 1,
        'word' => '',
    ],
    [
        'rank' => 2,
        'word' => '',
    ],
    [
        'rank' => 3,
        'word' => '',
    ],
]);

            $starterItems = DB::table('starter_items')
    ->where('is_active', true)
    ->where('trigger', 'register')
    ->get();
    foreach ($starterItems as $item) {
    DB::table('user_items')->updateOrInsert(
        [
            'user_id' => $user->id,
            'item_id' => $item->item_id,
        ],
        [
            'quantity' => DB::raw('quantity + ' . $item->quantity),
            'updated_at' => now(),
            'created_at' => now(),
        ]
    );
}
        }
// 🔥 ログ出力
Log::info('USER', ['user' => $user]);
        return response()->json([
    'id' => (string) $user->id,
    'public_id' => $user->public_id,
    'user_name' => $user->user_name,
    'device_id' => $user->device_id,
    'email' => $user->email,
    'plan_type'=> $user->plan_type,
    'icon_type' => $user->icon_type,
    'icon_name' => $user->icon_name,
    'about_self' => $user->about_self,
    'is_deleted' => $user->is_deleted,
    'banned_at' => $user->banned_at,
]);
}
private function generatePublicId(): string
{
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    do {

        $id = '';

        for ($i = 0; $i < 10; $i++) {
            $id .= $chars[random_int(0, strlen($chars) - 1)];
        }

    } while (
        User::where('public_id', $id)->exists()
    );

    return $id;
}
}