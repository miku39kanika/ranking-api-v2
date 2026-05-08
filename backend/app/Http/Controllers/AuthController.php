<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\UserCurrency;
use App\Models\Currency;
use App\Models\CurrencyHistory;
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
        }
// 🔥 ログ出力
Log::info('USER', ['user' => $user]);
        return response()->json([
    'id' => (string) $user->id,
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
}