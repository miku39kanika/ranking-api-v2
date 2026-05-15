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
        Schema::create('ranking_invites', function (Blueprint $table) {

            $table->id();

            $table->foreignId('ranking_id')
                ->constrained()
                ->onDelete('cascade');

            $table->string('user_id');

            $table->timestamps();

            $table->unique([
                'ranking_id',
                'user_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranking_invites');
    }
};