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
    $userId = $request->user()->id;
    $sort = $request->query('sort', 'popular');
    $likedOnly = $request->query('liked_only');
    $query = Ranking::with('tags')
    ->where('ranking_type', 0);
if ($userId) {

    $query->where(function ($q) use ($userId) {

        $q->where('vote_permission', '!=', 'invite_only_hidden')
          ->orWhere('user_id', $userId)
          ->orWhereExists(function ($sub) use ($userId) {

              $sub->select(DB::raw(1))
                  ->from('ranking_invites')
                  ->whereColumn(
                      'ranking_invites.ranking_id',
                      'rankings.id'
                  )
                  ->where(
                      'ranking_invites.user_id',
                      $userId
                  );
          });
    });

} else {

    $query->where(
        'vote_permission',
        '!=',
        'invite_only_hidden'
    );
}
if ($userId) {

    $query->withExists([
        'likes as is_liked' => function ($q) use ($userId) {

            $q->where('user_id', $userId);
        }
    ]);
}

if ($likedOnly) {

    $query->whereExists(function ($q) use ($userId) {

        $q->select(DB::raw(1))
            ->from('likes')
            ->whereColumn('likes.ranking_id', 'rankings.id')
            ->where('likes.user_id', $userId);
    });
}

    if ($sort === 'newest') {

        $query->orderByDesc('created_at');

    } else {

        $query
    ->leftJoin(
        'votes',
        'rankings.id',
        '=',
        'votes.ranking_id'
    )
            ->where(function ($query) {

                $query->where(
                    'votes.created_at',
                    '>=',
                    now()->subDay()
                )
                ->orWhereNull('votes.id');
            })
            ->groupBy('rankings.id')
            ->orderByRaw(
    'COUNT(DISTINCT votes.user_identifier) DESC'
)
->select('rankings.*');
    }

    $rankings = $query->paginate(20);

    return response()->json([
        'data' => $rankings->getCollection()->map(function ($ranking) use ($userId) {

            return [
                'id' => $ranking->id,
                'title' => $ranking->title,
                'reading' => $ranking->reading,
                'is_liked' => (int)($ranking->is_liked ?? 0),
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
        }),
        'current_page' => $rankings->currentPage(),
        'last_page' => $rankings->lastPage(),
    ]);
}

public function show($id, Request $request)
{
    Log::info('RankingController@show called');
    $ranking = Ranking::with(['items', 'tags'])
    ->where('ranking_type', 0)
    ->find($id);
    if (!$ranking) {
    return response()->json([
        'message' => 'Ranking not found'
    ], 404);
}
$userId = $request->query('user_id');

$isInvited = DB::table('ranking_invites')
    ->where('ranking_id', $ranking->id)
    ->where('user_id', $userId)
    ->exists();

$isOwner = $ranking->user_id === $userId;

if (
    $ranking->vote_permission === 'invite_only_hidden'
    && !$isInvited
    && !$isOwner
) {

    return response()->json([
        'message' => 'Forbidden'
    ], 403);
}
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
   $userId = $request->user()->id;

    $ranking = Ranking::with(['items', 'user', 'tags'])
        ->find($id);

    if (!$ranking) {

        return response()->json([
            'message' => 'Ranking not found'
        ], 404);
    }

    $isInvited = DB::table('ranking_invites')
        ->where('ranking_id', $ranking->id)
        ->where('user_id', $userId)
        ->exists();
$isOwner = $ranking->user_id === $userId;

if (
    $ranking->vote_permission === 'invite_only_hidden'
    && !$isInvited
    && !$isOwner
) {

        return response()->json([
            'message' => 'Forbidden'
        ], 403);
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
    'can_vote' => (
    $ranking->vote_permission === 'public_access'
    || $ranking->user_id === $userId
    || (
        in_array(
            $ranking->vote_permission,
            ['invite_only_view', 'invite_only_hidden']
        )
        && $isInvited
    )
),
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
        'ranking_type' => 0,
        'title' => $request->title,
        'reading' => $reading,
        'image_name' => $request->image_name,
        'is_item_add_limited' => $request->is_item_add_limited,
        'daily_vote_limit' => $request->daily_vote_limit,
        'total_vote_limit' => $request->total_vote_limit,
        'vote_permission' => $request->vote_permission,
        'user_id' => $request->user()->id,
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
        $query->orderByDesc('votes')->limit(5);
    }])
    ->where('ranking_type', 0)
    ->inRandomOrder()
    ->limit(10) // ここは好みで3〜10くらい
    ->get();

    return response()->json($rankings);
}

 public function getByUser($userId)
    {
        $rankings = Ranking::where('user_id', $userId)
    ->where('ranking_type', 0)
    ->orderBy('created_at', 'desc')
    ->get();
        
        return response()->json($rankings);
    }

    public function officialLatest(Request $request)
{
    Log::info('RankingController@officialLatest called');

    $rankings = Ranking::with(['tags'])
        ->where('ranking_type', 1)
        ->orderBy('created_at', 'desc')
        ->limit(2)
        ->get();

    return response()->json(
        $rankings->map(function ($ranking) {
            return [
                'id' => $ranking->id,
                'title' => $ranking->title,
                'reading' => $ranking->reading,
                'image_name' => $ranking->image_name,
                'created_at' => $ranking->created_at,
                'is_liked' => 0,
                'tags' => $ranking->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                }),
                'items' => [],
            ];
        })
    );
}
}


