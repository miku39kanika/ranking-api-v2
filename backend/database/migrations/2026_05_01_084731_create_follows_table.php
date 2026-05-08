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
        Schema::create('follows', function (Blueprint $table) {
    $table->id();
    $table->uuid('follower_id'); // フォローする人
    $table->uuid('followed_id'); // フォローされる人
    $table->timestamps();

    $table->unique(['follower_id', 'followed_id']); // 重複防止
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};
