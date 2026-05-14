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
    Schema::create('rankings', function (Blueprint $table) {
        $table->id();
        $table->tinyInteger('ranking_type')->default(0);
        $table->string('title',60); // ランキング名
        $table->string('reading',50)->nullable();
        $table->string('tag',100)->nullable();
        $table->string('image_name')->nullable();
        $table->boolean('is_item_add_limited')->default(false);
        $table->integer('daily_vote_limit')->default(1);
        $table->integer('total_vote_limit')->default(10);
        $table->string('vote_permission',30)->default('publicAccess');
        $table->uuid('user_id');
        $table->timestamps();
        
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rankings');
    }
};
