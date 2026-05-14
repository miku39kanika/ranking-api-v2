<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ranking;
use App\Models\RankingItem;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ReadingService;
use App\Services\ContentFilterService;

class RankingController extends Controller
{
public function index(Request $request)
{
    Log::info('RankingController@index called');

    $userId = $request->query('user_id');

    $rankings = Ranking::with('tags')

        ->leftJoin(
            'votes',
            'rankings.id',
            '=',
            'votes.ranking_id'
        )

        ->select(
            'rankings.*',
            DB::raw('COUNT(DISTINCT votes.user_identifier) as recent_users')
        )

        ->where(function ($query) {

            $query->where(
                'votes.created_at',
                '>=',
                now()->subDay()
            )
            ->orWhereNull('votes.id');
        })

        ->selectRaw("
            EXISTS (
                SELECT 1 FROM likes
                WHERE likes.ranking_id = rankings.id
                AND likes.user_id = ?
            ) as is_liked
        ", [$userId])

        ->groupBy('rankings.id')

        ->orderByDesc('recent_users')

        ->get();

    return response()->json(
        $rankings->map(function ($ranking) {

            return [
                'id' => $ranking->id,
                'title' => $ranking->title,
                'reading' => $ranking->reading,
                'is_liked' => $ranking->is_liked,
                'created_at' => $ranking->created_at,
                'tags' => $ranking->tags->map(function ($tag) {

                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                }),
                'image_name' => $ranking->image_name,

                'items' => [],
            ];
        })
    );
}
public function show($id)
{
    Log::info('RankingController@show called');
    $ranking = Ranking::with(['items', 'tags'])->find($id);

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
        'tags' => $ranking->tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
            ];
        }),
    ]);
}
public function rowShow($id,Request $request)
{
    Log::info('RankingController@rowShow called');
    $userId = $request->query('user_id');
    $ranking = Ranking::with(['items', 'user', 'tags'])->find($id);
    
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
        'tags' => $ranking->tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
            ];
        }),
        'items' => $ranking->items->map(function ($item) use ($userId) {

    $myVotes = Vote::where('ranking_item_id', $item->id)
        ->where('user_identifier', $userId)
        ->count();

    $myVotesToday = Vote::where('ranking_item_id', $item->id)
        ->where('user_identifier', $userId)
        ->whereDate('vote_date', today())
        ->count();

    return [
        'id' => $item->id,
        'name' => $item->name,
        'votes' => $item->votes,
        'aliases' => $item->aliases,
        'my_votes' => $myVotes,
        'my_votes_today' => $myVotesToday,
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
public function store(Request $request, ContentFilterService $filter)
{
    Log::info('RankingController@store called');
    if ($filter->containsNgWord($request->title)) {
        return response()->json([
            'error' => 'NG_WORD'
        ], 422);
    }
    
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
$ranking->tags()->sync($request->tag_ids);

    return response()->json([
    'id' => $ranking->id,
    'title' => $ranking->title,
    'reading' => $ranking->reading,
    'image_name' => $ranking->image_name,
    'created_at' => $ranking->created_at,
]);
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


