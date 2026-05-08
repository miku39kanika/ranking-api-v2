<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            // 誰がいいねしたか
            $table->string('user_id');

            // 何にいいねしたか（ランキングなど）
            $table->string('ranking_id');

            $table->timestamps();

            // 重複防止（同じユーザーが同じランキングに複数いいねできない）
            $table->unique(['user_id', 'ranking_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
