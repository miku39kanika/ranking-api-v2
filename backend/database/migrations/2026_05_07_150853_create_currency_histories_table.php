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
        Schema::create('currency_histories', function (Blueprint $table) {
            $table->id();

            // ユーザー
            $table->string('user_id');

            // 通貨
            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete();

            // 増減量
            $table->integer('amount');

            // 理由
            $table->string('reason')->nullable();

            // ログ用メモ
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_histories');
    }
};
