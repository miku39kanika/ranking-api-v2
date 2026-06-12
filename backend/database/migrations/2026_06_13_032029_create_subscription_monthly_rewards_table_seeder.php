<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('subscription_monthly_rewards', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->string('original_transaction_id');
            $table->string('product_id');
            $table->string('reward_month'); // 例: 2026-06
            $table->timestamp('granted_at');
            $table->timestamps();

            $table->unique([
                'original_transaction_id',
                'reward_month',
            ], 'subscription_reward_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_monthly_rewards');
    }
};
