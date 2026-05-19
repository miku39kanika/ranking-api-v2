<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id()->primary();

            // アイテム基本情報
            $table->string('name', 100);
            $table->text('description')->nullable();
            // タイプ
            $table->string('type', 20)->default('item');
            // icon:アイコン画像
            // item:消費アイテム
            // title: 称号

            // レアリティ
            $table->string('rarity', 20)->default('common');

            // 表示用画像（SF Symbolsでもasset名でもOK）
            $table->string('image_name', 100)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
