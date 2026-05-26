<?php

namespace App\Http\Controllers;

use App\Models\Ranking;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\UserCurrency;
use App\Models\CurrencyHistory;

class GameController extends Controller
{
    public function getSession()
    {
        Log::info('GameController@getSession called');
        $questions = DB::table('game_questions')
            // ->where('generated_date', today())
            ->inRandomOrder()
            ->take(5)
            ->get();

        return response()->json([
            'questions' => $questions->map(function ($q) {
                return json_decode($q->data, true);
            })
        ]);
    }
    private function makeChoice($ranking)
    {
        Log::info('GameController@makeChoice called');
        $items = $ranking->items->sortByDesc('votes')->values();

        if ($items->count() < 2) return null;

        $top = $items[0];
        $second = $items[1];

        $isLeftCorrect = rand(0, 1);

        return [
            'id' => $ranking->id,
            'type' => 1,
            'rankingTitle' => $ranking->title,
            'leftItem' => [
                'id' => $isLeftCorrect ? $top->id : $second->id,
                'name' => $isLeftCorrect ? $top->name : $second->name
            ],
            'rightItem' => [
                'id' => $isLeftCorrect ? $second->id : $top->id,
                'name' => $isLeftCorrect ? $second->name : $top->name
            ],
            'correctAnswer' => $isLeftCorrect ? 'left' : 'right'
        ];
    }
    private function makeTop5($ranking)
    {
        Log::info('GameController@makeTop5 called');
        $items = $ranking->items->sortByDesc('votes')->values();

        if ($items->count() < 10) return null;

        $choices = $items->take(10);

        $correctIds = $items->take(5)->pluck('id');

        return [
            'id' => $ranking->id,
            'type' => 2,
            'rankingTitle' => $ranking->title,
            'choices' => $choices->map(fn($i) => [
                'id' => $i->id,
                'name' => $i->name
            ])->values(),
            'correctIds' => $correctIds
        ];
    }
    private function makePercent($ranking)
    {
        Log::info('GameController@makePercent called');
        $items = $ranking->items;

        $totalVotes = $items->sum('votes');

        if ($items->count() < 5 || $totalVotes == 0) return null;

        $selected = $items->random(5);

        return [
            'id' => $ranking->id,
            'type' => 3,
            'rankingTitle' => $ranking->title,
            'percentItems' => $selected->map(function ($i) use ($totalVotes) {
                return [
                    'id' => $i->id,
                    'name' => $i->name,
                    'voteRate' => $i->votes / $totalVotes
                ];
            })->values(),
            'threshold' => 0.5
        ];
    }

    public function reward(Request $request)
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:0'
        ]);
        //$reward = floor($validated['score'] / 10); 
        $reward = floor($validated['score']);

        // crown = currency_id 2
        $userCurrency = UserCurrency::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'currency_id' => 2,
            ],
            [
                'amount' => 0
            ]
        );

        $userCurrency->amount += $reward;
        $userCurrency->save();

        CurrencyHistory::create([
            'user_id' => $request->user()->id,
            'currency_id' => 2,
            'amount' => $reward,
            'reason' => 'game_reward',
            'note' => 'score: ' . $validated['score'],
        ]);

        return response()->json([
            'success' => true,
            'reward' => $reward,
            'total' => $userCurrency->amount,
        ]);
    }
}
