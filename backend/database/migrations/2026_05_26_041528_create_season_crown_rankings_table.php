<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_crown_rankings', function (Blueprint $table) {

            $table->id();

            $table->integer('season');

            $table->string('user_id');

            $table->integer('crown_amount')
                ->default(0);

            $table->integer('rank')
                ->nullable();

            $table->date('snapshot_date');

            $table->timestamps();

            $table->unique([
                'season',
                'user_id',
                'snapshot_date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'season_crown_rankings'
        );
    }
};
