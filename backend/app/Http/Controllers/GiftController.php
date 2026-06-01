<?php

namespace App\Http\Controllers;

use App\Models\Gift;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\UserGift;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GiftController extends Controller
{
    public function index(Request $request)
    {
        Log::info('GiftController@index called');

        $user = $request->user();
        $userId = $user->id;

        // ★ここを user()->created_at に置き換え
        $from = $user->created_at->format('Y-m-d');

        $query = Gift::query();

        // =====================
        // 有効期限フィルタ
        // =====================
        $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now());
        });

        // =====================
        // case条件
        // =====================
        $query->where(function ($q) use ($userId, $from) {

            $q->orWhere('case', 1);

            $q->orWhere(function ($sub) use ($from) {
                $sub->where('case', 2)
                    ->whereDate('from_date', '>=', $from);
            });

            $q->orWhere(function ($sub) use ($userId) {
                $sub->where('case', 3)
                    ->where('user_id', $userId);
            });
        });

        $gifts = $query
            ->with('item') // ←追加
            ->latest()
            ->get();
        $receivedGiftIds = DB::table('user_gifts')
            ->where('user_id', $userId)
            ->pluck('gift_id')
            ->toArray();

        return response()->json(
            $gifts->map(function ($gift) use ($receivedGiftIds) {
                Log::info($gift->expires_at);
                Log::info($gift->expires_at?->toJson());
                return [
                    'id' => $gift->id,
                    'title' => $gift->title,
                    'body' => $gift->body,
                    'reward_type' => $gift->reward_type,
                    'item_image_name' => $gift->item?->image_name,
                    'item_name' => $gift->item?->name,
                    'case' => $gift->case,
                    'user_id' => $gift->user_id,
                    'from_date' => $gift->from_date,
                    'expires_at' => $gift->expires_at,
                    'created_at' => $gift->created_at?->format('Y-m-d'),
                    'is_received' => in_array($gift->id, $receivedGiftIds),
                ];
            })
        );
    }


    public function receive(Request $request)
    {
        Log::info('GiftController@receive called');
        $userId = $request->user()->id;
        $giftId = $request->gift_id;

        // =====================
        // ギフト取得
        // =====================
        $gift = DB::table('gifts')->where('id', $giftId)->first();

        if (!$gift) {
            return response()->json(['message' => 'gift not found'], 404);
        }

        // =====================
        // 二重受け取り防止
        // =====================
        $exists = DB::table('user_gifts')
            ->where('user_id', $userId)
            ->where('gift_id', $giftId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'already received'], 200);
        }

        DB::transaction(function () use ($userId, $gift) {

            // =====================
            // 受け取り履歴
            // =====================
            DB::table('user_gifts')->insert([
                'user_id' => $userId,
                'gift_id' => $gift->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // =====================
            // reward処理
            // =====================
            switch ($gift->reward_type) {

                // =====================
                // currency付与
                // =====================
                case 'currency':

                    DB::table('user_currencies')
                        ->updateOrInsert(
                            [
                                'user_id' => $userId,
                                'currency_id' => $gift->reward_code, // ←そのままID
                            ],
                            [
                                'amount' => DB::raw('amount + ' . $gift->reward_amount),
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );

                    DB::table('currency_histories')->insert([
                        'user_id' => $userId,
                        'currency_id' => $gift->reward_code,
                        'amount' => $gift->reward_amount,
                        'reason' => 'gift',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    break;

                // =====================
                // item付与
                // =====================
                case 'item':

                    $existingItem = DB::table('user_items')
                        ->where('user_id', $userId)
                        ->where('item_id', $gift->reward_code)
                        ->where('expires_at', $gift->expires_at)
                        ->first();

                    if ($existingItem) {

                        DB::table('user_items')
                            ->where('id', $existingItem->id)
                            ->update([
                                'quantity' =>
                                $existingItem->quantity + $gift->reward_amount,

                                'updated_at' => now(),
                            ]);
                    } else {

                        DB::table('user_items')
                            ->insert([
                                'user_id' => $userId,
                                'item_id' => $gift->reward_code,
                                'quantity' => $gift->reward_amount,
                                'expires_at' => $gift->expires_at,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                    }

                    break;

                // =====================
                // rewarded_ad
                // =====================
                case 'rewarded_ad':

                    $existingItem = DB::table('user_items')
                        ->where('user_id', $userId)
                        ->where('item_id', 8)
                        ->where('expires_at', $gift->expires_at)
                        ->first();

                    if ($existingItem) {

                        DB::table('user_items')
                            ->where('id', $existingItem->id)
                            ->update([
                                'quantity' =>
                                $existingItem->quantity + 1,

                                'updated_at' => now(),
                            ]);
                    } else {

                        DB::table('user_items')
                            ->insert([
                                'user_id' => $userId,
                                'item_id' => 8,
                                'quantity' => 1,
                                'expires_at' => $gift->expires_at,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                    }

                    break;
            }
        });

        return response()->json([
            'message' => 'received'
        ]);
    }
}
