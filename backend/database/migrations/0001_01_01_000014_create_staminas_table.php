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
    Schema::create('staminas', function (Blueprint $table) {
    $table->id();

    $table->uuid('user_id');
    $table->integer('vote_stamina')->default(10);
    $table->integer('create_stamina')->default(3);

    $table->timestamp('last_recovered_at')->nullable();

    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staminas');
    }
};
