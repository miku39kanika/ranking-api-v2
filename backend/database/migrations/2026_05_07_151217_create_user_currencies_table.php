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

            $table->integer('amount')->default(0);

            $table->timestamps();

            $table->unique(['user_id', 'currency_id']);
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
