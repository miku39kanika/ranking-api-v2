<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BotVoteRankings extends Command
{
    protected $signature = 'bot:vote-rankings';
    protected $description = 'Bot votes randomly for rankings during beta';

    public function handle()
    {
        $botUserId = "bot_user"; // Bot用ユーザーID

        $rankings = DB::table('rankings')
            ->where('ranking_type', 0)
            ->where('vote_permission', 'public_access')
            ->inRandomOrder()
            ->limit(1)
            ->get();

        foreach ($rankings as $ranking) {

            $choices = DB::table('ranking_items')
                ->where('ranking_id', $ranking->id)
                ->get();

            if ($choices->isEmpty()) {
                continue;
            }

            $weightedChoices = [];

            foreach ($choices as $choice) {

                $votes = DB::table('votes')
                    ->where('ranking_id', $ranking->id)
                    ->where('ranking_item_id', $choice->id)
                    ->count();

                $weight = $votes + 3;

                for ($i = 0; $i < $weight; $i++) {
                    $weightedChoices[] = $choice;
                }
            }

            $selected = $weightedChoices[array_rand($weightedChoices)];

            DB::table('votes')->insert([
                'user_id' => $botUserId,
                'ranking_id' => $ranking->id,
                'ranking_item_id' => $selected->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->info('Bot voting completed.');
    }
}
