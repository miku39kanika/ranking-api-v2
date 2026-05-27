<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_crown_rankings', function (Blueprint $table) {

            $table->id();

            // 2026-05
            $table->string('year_month');

            $table->string('user_id');

            $table->integer('crown_amount')
                ->default(0);

            $table->integer('rank')
                ->nullable();

            $table->date('snapshot_date');

            $table->timestamps();

            $table->unique([
                'year_month',
                'user_id',
                'snapshot_date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'monthly_crown_rankings'
        );
    }
};
