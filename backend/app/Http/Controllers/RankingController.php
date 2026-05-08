<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ranking;
use App\Models\RankingItem;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ReadingService;

class RankingController extends Controller
{
 public function index(Request $request)
{
    Log::info('RankingController@index called');

    // ① リクエスト内容
    $userId = $request->query('user_id');

    $rankings = Ranking::select('rankings.*')
        ->selectRaw("
            EXISTS (
                SELECT 1 FROM likes
                WHERE likes.ranking_id = rankings.id
                AND likes.user_id = ?
            ) as is_liked
        ", [$userId])
        ->get();

    return response()->json($rankings);
}
public function show($id)
{
    Log::info('RankingController@show called');
    $ranking = Ranking::with('items')->find($id);

    if (!$ranking) {
        return response()->json([
            'message' => 'Ranking not found'
        ], 404);
    }


    
    return response()->json([
        'id' => $ranking->id,
        'title' => $ranking->title,
        'reading' => $ranking->reading,
        'items' => $ranking->items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'votes' => $item->votes,
                'aliases' => $item->aliases,
            ];
        }),
    ]);
}
public function rowShow($id)
{
    Log::info('RankingController@rowShow called');

    $ranking = Ranking::with('items')->find($id);
    $ranking = Ranking::with(['items', 'user'])->find($id);
    if (!$ranking) {
        return response()->json([
            'message' => 'Ranking not found'
        ], 404);
    }

    return response()->json([
        'id' => $ranking->id,
        'title' => $ranking->title,
        'reading' => $ranking->reading,
        'is_liked' => 0,
        'items' => $ranking->items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'votes' => $item->votes,
                'aliases' => $item->aliases,
            ];
        }),
        'creator' => $ranking->user ? [
        'id' => $ranking->user->id,
        'user_name' => $ranking->user->user_name,
        'icon_type' => $ranking->user->icon_type,
        'icon_name' => $ranking->user->icon_name,
        'about_self' => $ranking->user->about_self,
        'plan_type' => $ranking->user->plan_type,
    ] : null,
    ]);
}
public function store(Request $request)
{
    Log::info('RankingController@store called');
    $reading = app(ReadingService::class)->generate($request->title);

    $ranking = Ranking::create([
        'title' => $request->title,
        'reading' => $reading,
        'image_name' => $request->image_name,
        'is_item_add_limited' => $request->is_item_add_limited,
        'daily_vote_limit' => $request->daily_vote_limit,
        'total_vote_limit' => $request->total_vote_limit,
        'vote_permission' => $request->vote_permission,
        'user_id' => $request->user_id,
    ]);

    return response()->json($ranking);
}

public function random()
{
    $rankings = Ranking::with(['items' => function ($query) {
        $query->orderByDesc('votes')
              ->limit(5);
    }])
    ->inRandomOrder()
    ->limit(10) // ここは好みで3〜10くらい
    ->get();

    return response()->json($rankings);
}

 public function getByUser($userId)
    {
        $rankings = Ranking::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($rankings);
    }
}


