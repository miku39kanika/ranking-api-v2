<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {

            $table->id();

            // ブロックした側
            $table->uuid('user_id');

            // ブロックされた側
            $table->uuid('blocked_user_id');

            $table->timestamps();

            $table->unique([
                'user_id',
                'blocked_user_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
