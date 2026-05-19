<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_gifts', function (Blueprint $table) {

            $table->id();

            $table->string('user_id')->index();
            $table->unsignedBigInteger('gift_id')->index();

            $table->timestamp('received_at')->nullable();

            $table->timestamps();

            // 二重受け取り防止
            $table->unique(['user_id', 'gift_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_gifts');
    }
};
