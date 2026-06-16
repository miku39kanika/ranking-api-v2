<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SubscriptionRewardService
{
    public function grantMonthlyReward(
        string $userId,
        string $originalTransactionId,
        string $productId,
        string $rewardMonth
    ): bool {
        return DB::transaction(function () use (
            $userId,
            $originalTransactionId,
            $productId,
            $rewardMonth
        ) {
            $exists = DB::table('subscription_monthly_rewards')
                ->where('original_transaction_id', $originalTransactionId)
                ->where('reward_month', $rewardMonth)
                ->exists();

            if ($exists) {
                return false;
            }

            // オーブ500
            $this->giveCurrency($userId, 1, 500, 'premium_monthly_v2_reward');

            // ランキング作成チケット5枚
            $this->giveItemByImageName($userId, 'item02', 5);

            DB::table('subscription_monthly_rewards')->insert([
                'user_id' => $userId,
                'original_transaction_id' => $originalTransactionId,
                'product_id' => $productId,
                'reward_month' => $rewardMonth,
                'granted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }

    private function giveCurrency(string $userId, int $currencyId, int $amount, string $reason): void
    {
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
            'note' => 'premium monthly reward',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function giveItemByImageName(string $userId, string $imageName, int $quantity): void
    {
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
