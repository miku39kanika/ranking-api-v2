<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {

            $table->id();

            // 通報した人
            $table->uuid('user_id');

            // 対象タイプ
            $table->string('target_type');
            // user / ranking / comment

            // 対象ID
            $table->string('target_id');

            // 理由
            $table->string('reason');

            // 詳細
            $table->text('body')->nullable();

            $table->timestamps();

            // ★ ここ追加（重複防止）
            $table->unique(['user_id', 'target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
