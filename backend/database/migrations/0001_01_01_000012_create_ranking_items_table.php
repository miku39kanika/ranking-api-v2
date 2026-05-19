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
        Schema::create('ranking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ranking_id')->constrained()->onDelete('cascade');
            $table->string('name', 100); // 項目名（ラーメンとか）
            $table->integer('votes')->default(0); // 票数
            $table->json('aliases')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_items');
    }
};
