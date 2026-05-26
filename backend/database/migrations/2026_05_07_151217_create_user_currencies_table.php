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
        Schema::create('user_currencies', function (Blueprint $table) {

            $table->id();

            $table->string('user_id');

            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete();

            // crown用シーズン
            // orbなどはnull
            $table->integer('season')->nullable();

            $table->integer('amount')->default(0);

            $table->timestamps();

            // 同一ユーザー + 同一通貨 + 同一シーズン
            // の重複防止
            $table->unique([
                'user_id',
                'currency_id',
                'season'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_currencies');
    }
};
