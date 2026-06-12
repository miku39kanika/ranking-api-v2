<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|string',
            'transaction_id' => 'required|string',
            'original_transaction_id' => 'nullable|string',
        ]);

        $user = $request->user();

        return DB::transaction(function () use ($request, $user) {
            $rewards = [];
            $exists = DB::table('purchases')
                ->where('transaction_id', $request->transaction_id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'already processed',
                ]);
            }

            $productId = $request->product_id;

            if (
                $productId === 'premium_monthly'
                && !$request->original_transaction_id
            ) {
                return response()->json([
                    'message' =>
                    'original_transaction_id is required for subscription',
                ], 422);
            }

            $type = $productId === 'premium_monthly'
                ? 'subscription'
                : 'consumable';

            $expiresAt = $productId === 'premium_monthly'
                ? now()->addMonth()
                : null;

            DB::table('purchases')->insert([
                'user_id' => $user->id,
                'product_id' => $productId,
                'transaction_id' => $request->transaction_id,
                'original_transaction_id' => $request->original_transaction_id,
                'type' => $type,
                'purchased_at' => now(),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            switch ($productId) {
                case 'orb_500':
                    $this->giveCurrency($user->id, 1, 500, 'purchase_orb_500');
                    $rewards[] = [
                        'type' => 'currency',
                        'name' => 'オーブ',
                        'image_name' => 'orb',
                        'amount' => 500,
                    ];
                    break;

                case 'orb_1200':
                    $this->giveCurrency($user->id, 1, 1200, 'purchase_orb_1200');
                    $rewards[] = [
                        'type' => 'currency',
                        'name' => 'オーブ',
                        'image_name' => 'orb',
                        'amount' => 1200,
                    ];
                    break;
                case 'premium_monthly':
                    $this->activatePremium($user->id, $expiresAt);

                    $rewardMonth = now()->format('Y-m');

                    app(\App\Services\SubscriptionRewardService::class)
                        ->grantMonthlyReward(
                            $user->id,
                            $request->original_transaction_id,
                            $productId,
                            $rewardMonth
                        );

                    // 限定アイコンは初回だけ
                    $this->giveItemByImageName($user->id, 'sp_real01', 1);
                    $this->giveItemByImageName($user->id, 'sp02', 1);

                    $rewards[] = [
                        'type' => 'currency',
                        'name' => 'オーブ',
                        'image_name' => 'orb',
                        'amount' => 500,
                    ];

                    $rewards[] = [
                        'type' => 'item',
                        'name' => 'ランキング作成チケット',
                        'image_name' => 'item02',
                        'amount' => 5,
                    ];

                    $rewards[] = [
                        'type' => 'icon',
                        'name' => 'わちゃわちゃアイコン(実写)',
                        'image_name' => 'sp_real01',
                        'amount' => 1,
                    ];
                    $rewards[] = [
                        'type' => 'icon',
                        'name' => 'ぷりめアイコン(原画)',
                        'image_name' => 'sp02',
                        'amount' => 1,
                    ];
                    break;
                default:
                    return response()->json([
                        'message' => 'unknown product',
                    ], 400);
            }

            return response()->json([
                'message' => 'purchase processed',
                'product_id' => $productId,
                'rewards' => $rewards,
            ]);
        });
    }

    private function activatePremium(string $userId, Carbon $expiresAt): void
    {
        DB::table('users')
            ->where('id', $userId)
            ->update([
                'plan_type' => 1,
                'plan_expires_at' => $expiresAt,
                'updated_at' => now(),
            ]);
    }

    private function giveCurrency(
        string $userId,
        int $currencyId,
        int $amount,
        string $reason
    ): void {
        DB::table('user_currencies')->updateOrInsert(
            [
                'user_id' => $userId,
                'currency_id' => $currencyId,
            ],
            [
                'amount' => DB::raw("amount + {$amount}"),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('currency_histories')->insert([
            'user_id' => $userId,
            'currency_id' => $currencyId,
            'amount' => $amount,
            'reason' => $reason,
            'note' => 'purchase reward',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function giveItemByImageName(
        string $userId,
        string $imageName,
        int $quantity
    ): void {
        $item = DB::table('items')
            ->where('image_name', $imageName)
            ->first();

        if (!$item) {
            throw new \Exception("Item not found: {$imageName}");
        }

        $existing = DB::table('user_items')
            ->where('user_id', $userId)
            ->where('item_id', $item->id)
            ->whereNull('expires_at')
            ->first();

        if ($existing) {
            DB::table('user_items')
                ->where('id', $existing->id)
                ->update([
                    'quantity' => $existing->quantity + $quantity,
                    'updated_at' => now(),
                ]);
        } else {
            DB::table('user_items')->insert([
                'user_id' => $userId,
                'item_id' => $item->id,
                'quantity' => $quantity,
                'expires_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
