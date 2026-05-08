<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starter_items', function (Blueprint $table) {
            $table->id();

            // どのアイテムを配るか
            $table->foreignId('item_id')
                ->constrained('items')
                ->cascadeOnDelete();

            // 配布数
            $table->integer('quantity')->default(1);

            // 有効フラグ（配布ON/OFF切り替え用）
            $table->boolean('is_active')->default(true);

            // どのタイミング用か（拡張用）
            // 例: register / tutorial / event
            $table->string('trigger', 30)->default('register');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starter_items');
    }
};