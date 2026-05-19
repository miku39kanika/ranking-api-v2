<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaminasTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->get();

        $staminas = [];

        foreach ($users as $user) {
            $staminas[] = [
                'user_id' => $user->id,
                'vote_stamina' => 10,     // 初期値
                'create_stamina' => 3,    // 初期値
                'last_recovered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('staminas')->insert($staminas);
    }
}
