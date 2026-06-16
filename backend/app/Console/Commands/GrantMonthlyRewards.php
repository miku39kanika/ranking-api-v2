<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GrantMonthlyRewards extends Command
{
    protected $signature = 'subscriptions:grant-monthly-rewards';

    public function handle()
    {
        $rewardMonth = now()->format('Y-m');

        $users = DB::table('users')
            ->where('plan_type', 1)
            ->where('plan_expires_at', '>=', now())
            ->get();

        foreach ($users as $user) {
            $purchase = DB::table('purchases')
                ->where('user_id', $user->id)
                ->where('product_id', 'premium_monthly_v2')
                ->whereNotNull('original_transaction_id')
                ->latest()
                ->first();

            if (!$purchase) {
                continue;
            }

            app(\App\Services\SubscriptionRewardService::class)
                ->grantMonthlyReward(
                    $user->id,
                    $purchase->original_transaction_id,
                    'premium_monthly_v2',
                    $rewardMonth
                );
        }
    }
}
