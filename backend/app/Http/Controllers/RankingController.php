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
use Illuminate\Support\Str;
use App\Models\Tag;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:50',
        ]);
        $userId = $request->user()->id;
        $sort = $request->query('sort', 'popular');
        $likedOnly = $request->query('liked_only');
        $query = Ranking::with('tags')
            ->where('ranking_type', 0);
        $search = $request->query('search');

        if ($search) {

            $query->where(function ($q) use ($search) {

                // タイトル検索
                $q->where('title', 'like', "%{$search}%")

                    // タグ検索
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {

                        $tagQuery->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    })

                    // 項目検索
                    ->orWhereHas('items', function ($itemQuery) use ($search) {

                        $itemQuery->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }
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
                ->orderByDesc('rankings.id')
                ->select('rankings.*')
                ->selectSub(function ($q) use ($userId) {

                    $q->from('likes')
                        ->selectRaw('COUNT(*) > 0')
                        ->whereColumn('likes.ranking_id', 'rankings.id')
                        ->where('likes.user_id', $userId);
                }, 'is_liked');
        }

        $rankings = $query->paginate(20);
        Log::info($rankings->pluck('vote_permission'));
        return response()->json([
            'data' => $rankings->getCollection()->map(function ($ranking) use ($userId) {

                return [
                    'id' => $ranking->id,
                    'title' => $ranking->title,
                    'reading' => $ranking->reading,
                    'is_liked' => (int)($ranking->is_liked ?? 0),
                    'daily_vote_limit' => $ranking->daily_vote_limit,
                    'total_vote_limit' => $ranking->total_vote_limit,
                    'created_at' => $ranking->created_at,
                    'tags' => $ranking->tags->map(function ($tag) {

                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                        ];
                    }),
                    'image_name' => $ranking->image_name,
                    'image_type' => $ranking->image_type,
                    'image_path' => $ranking->image_path,
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


        /** @var Ranking|null $ranking */
        return response()->json([
            'id' => $ranking->id,
            'title' => $ranking->title,
            'reading' => $ranking->reading,
            'image_name' => $ranking->image_name,
            'image_type' => $ranking->image_type,
            'image_path' => $ranking->image_path,
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
            'daily_vote_limit' => $ranking->daily_vote_limit,
            'total_vote_limit' => $ranking->total_vote_limit,
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
    public function rowShow($id, Request $request)
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
        $myTotalVotes = Vote::where('ranking_id', $ranking->id)
            ->where('user_identifier', $userId)
            ->count();

        $myTodayVotes = Vote::where('ranking_id', $ranking->id)
            ->where('user_identifier', $userId)
            ->whereDate('vote_date', today())
            ->count();
        $hasInvitePermission =
            $ranking->vote_permission === 'public_access'
            || $ranking->user_id === $userId
            || (
                in_array(
                    $ranking->vote_permission,
                    ['invite_only_view', 'invite_only_hidden']
                )
                && $isInvited
            );

        $withinDailyLimit =
            $myTodayVotes < $ranking->daily_vote_limit;

        $withinTotalLimit =
            $myTotalVotes < $ranking->total_vote_limit;

        $canVote =
            $hasInvitePermission
            && $withinDailyLimit
            && $withinTotalLimit;
        $isOwner = $ranking->user_id === $userId;

        /** @var Ranking|null $ranking */
        return response()->json([
            'id' => $ranking->id,
            'title' => $ranking->title,
            'reading' => $ranking->reading,
            'image_name' => $ranking->image_name,
            'image_type' => $ranking->image_type,
            'image_path' => $ranking->image_path,
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
                'public_id' => $ranking->user->public_id,
                'user_name' => $ranking->user->user_name,
                'icon_type' => $ranking->user->icon_type,
                'icon_name' => $ranking->user->icon_name,
                'about_self' => $ranking->user->about_self,
                'plan_type' => $ranking->user->plan_type,
            ] : null,
            'is_item_add_limited' => (bool)$ranking->is_item_add_limited,
            'can_vote' => $canVote,
            'daily_vote_limit' => $ranking->daily_vote_limit,
            'total_vote_limit' => $ranking->total_vote_limit,
            'invite_code' => $ranking->invite_code,
        ]);
    }
    public function store(Request $request, ContentFilterService $filter)
    {
        Log::info('RankingController@store called');

        Log::info($request->all());

        Log::info($request->hasFile('image'));

        Log::info($request->file('image'));
        $request->validate([
            'title' => 'required|string|max:30',
            'daily_vote_limit' => 'required|integer|min:1|max:100',
            'total_vote_limit' => 'required|integer|min:1|max:1000',
        ]);
        $request->validate([
            'image' => 'nullable|image|max:5120',
        ]);

        $userId = $request->user()->id;
        // =====================
        // 公式タグ禁止
        // =====================

        $officialTagExists = Tag::whereIn(
            'id',
            $request->tag_ids ?? []
        )
            ->where('name', '公式')
            ->exists();

        if ($officialTagExists) {

            return response()->json([
                'error' => 'OFFICIAL_TAG_NOT_ALLOWED'
            ], 403);
        }
        // =====================
        // ランキング作成コスト処理
        // ticket優先
        // なければorb100
        // =====================

        DB::beginTransaction();

        try {

            $ticketItems = DB::table('user_items')
                ->where('user_id', $userId)
                ->where('item_id', 8)
                ->where('quantity', '>', 0)
                ->orderByRaw('expires_at IS NULL')
                ->orderBy('expires_at', 'asc')
                ->get();

            // =====================
            // ticket消費
            // =====================

            if ($ticketItems->isNotEmpty()) {

                $remaining = 1;

                foreach ($ticketItems as $item) {

                    if ($remaining <= 0) {
                        break;
                    }

                    $consume = min(
                        $item->quantity,
                        $remaining
                    );

                    $newQuantity =
                        $item->quantity - $consume;

                    if ($newQuantity <= 0) {

                        DB::table('user_items')
                            ->where('id', $item->id)
                            ->delete();
                    } else {

                        DB::table('user_items')
                            ->where('id', $item->id)
                            ->update([
                                'quantity' => $newQuantity
                            ]);
                    }

                    $remaining -= $consume;
                }
            } else {

                // =====================
                // orb消費
                // =====================

                $currency = DB::table('user_currencies')
                    ->where('user_id', $userId)
                    ->where('currency_id', 1)
                    ->first();

                if (!$currency || $currency->amount < 100) {

                    DB::rollBack();

                    return response()->json([
                        'error' => 'NOT_ENOUGH_ORB'
                    ], 400);
                }

                $newAmount = $currency->amount - 100;

                if ($newAmount <= 0) {

                    DB::table('user_currencies')
                        ->where('id', $currency->id)
                        ->delete();
                } else {

                    DB::table('user_currencies')
                        ->where('id', $currency->id)
                        ->update([
                            'amount' => $newAmount
                        ]);
                }

                DB::table('currency_histories')
                    ->insert([
                        'user_id' => $userId,
                        'currency_id' => 1,
                        'amount' => -100,
                        'reason' => 'CREATE_RANKING',
                        'note' => 'ランキング作成',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
            }


            if ($filter->containsNgWord($request->title)) {
                return response()->json([
                    'error' => 'NG_WORD'
                ], 422);
            }
            $imagePath = null;
            $imageType = 'asset';

            if ($request->hasFile('image')) {

                $imagePath = $request
                    ->file('image')
                    ->store('rankings', 'public');

                $imageType = 'uploaded';
            }




            $reading = app(ReadingService::class)->generate($request->title);
            $inviteCode =
                Str::upper(Str::random(8));

            Log::info("CREATE START");

            $ranking = Ranking::create([
                'ranking_type' => 0,
                'title' => $request->title,
                'reading' => $reading,
                'image_name' => $request->image_name,
                'image_type' => $imageType,
                'image_path' => $imagePath,
                'is_item_add_limited' => $request->is_item_add_limited,
                'daily_vote_limit' => $request->daily_vote_limit,
                'total_vote_limit' => $request->total_vote_limit,
                'vote_permission' => $request->vote_permission,
                'user_id' => $request->user()->id,
                'invite_code' => $inviteCode,
            ]);

            $tagIds = $request->tag_ids ?? [];

            $ranking->tags()->sync($tagIds);

            $this->createTagImageGiftIfNeeded(
                $userId,
                $tagIds
            );

            DB::commit();
            Log::info("CREATE SUCCESS");
            $ranking->tags()->sync($request->tag_ids);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e);

            return response()->json([
                'error' => 'CREATE_FAILED'
            ], 500);
        }
        return response()->json([
            'id' => $ranking->id,
            'title' => $ranking->title,
            'reading' => $ranking->reading,
            'image_name' => $ranking->image_name,
            'image_type' => $ranking->image_type,
            'image_path' => $ranking->image_path,
            'created_at' => $ranking->created_at,
            'daily_vote_limit' => $ranking->daily_vote_limit,
            'total_vote_limit' => $ranking->total_vote_limit,
        ]);
    }

    public function random(Request $request)
    {
        $userId = $request->user()->id;

        $rankings = Ranking::with(['items' => function ($query) {
            $query->orderByDesc('votes')->limit(5);
        }])
            ->where('ranking_type', 0)
            ->where('vote_permission', 'public_access')
            ->inRandomOrder()
            ->limit(10)
            ->get();

        return response()->json(
            $rankings->map(function ($ranking) use ($userId) {

                $myTotalVotes = Vote::where('ranking_id', $ranking->id)
                    ->where('user_identifier', $userId)
                    ->count();

                return [
                    'id' => $ranking->id,
                    'title' => $ranking->title,
                    'has_voted' => $myTotalVotes > 0,
                    'items' => $ranking->items->map(function ($item) use ($userId) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'votes' => $item->votes,
                            'aliases' => $item->aliases,
                            'my_votes' => Vote::where('ranking_item_id', $item->id)
                                ->where('user_identifier', $userId)
                                ->count(),
                        ];
                    }),
                ];
            })
        );
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
            ->where('vote_permission', '!=', 'invite_only_hidden')
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
                    'image_type' => $ranking->image_type,
                    'image_path' => $ranking->image_path,
                    'created_at' => $ranking->created_at,
                    'is_liked' => 0,
                    'tags' => $ranking->tags->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                        ];
                    }),
                    'items' => [],
                    'daily_vote_limit' => $ranking->daily_vote_limit,
                    'total_vote_limit' => $ranking->total_vote_limit,
                ];
            })
        );
    }
    public function showByInviteCode($inviteCode, Request $request)
    {
        $userId = $request->user()->id;

        $ranking = Ranking::with(['tags', 'items'])
            ->where('invite_code', $inviteCode)
            ->first();

        if (!$ranking) {

            return response()->json([
                'message' => 'Ranking not found'
            ], 404);
        }

        DB::table('ranking_invites')->updateOrInsert(
            [
                'ranking_id' => $ranking->id,
                'user_id' => $userId,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $myTotalVotes = Vote::where('ranking_id', $ranking->id)
            ->where('user_identifier', $userId)
            ->count();

        $myTodayVotes = Vote::where('ranking_id', $ranking->id)
            ->where('user_identifier', $userId)
            ->whereDate('vote_date', today())
            ->count();

        $withinDailyLimit =
            $myTodayVotes < $ranking->daily_vote_limit;

        $withinTotalLimit =
            $myTotalVotes < $ranking->total_vote_limit;

        $canVote =
            $withinDailyLimit
            && $withinTotalLimit;

        return response()->json([

            'id' => $ranking->id,
            'title' => $ranking->title,
            'reading' => $ranking->reading,
            'image_name' => $ranking->image_name,
            'image_type' => $ranking->image_type,
            'image_path' => $ranking->image_path,
            'created_at' => $ranking->created_at,
            'is_liked' => 0,

            'tags' => $ranking->tags->map(function ($tag) {

                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            }),

            'items' => $ranking->items->map(function ($item) {

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'votes' => $item->votes,
                    'aliases' => $item->aliases,
                ];
            }),
            'is_item_add_limited' => (bool)$ranking->is_item_add_limited,
            'daily_vote_limit' => $ranking->daily_vote_limit,
            'total_vote_limit' => $ranking->total_vote_limit,
            'can_vote' => $canVote,
        ]);
    }

    private function createTagImageGiftIfNeeded(string $userId, array $tagIds): void
    {
        if (empty($tagIds)) {
            return;
        }

        $item = DB::table('tags')
            ->join('items', 'tags.tag_image_name', '=', 'items.image_name')
            ->whereIn('tags.id', $tagIds)
            ->whereNotNull('tags.tag_image_name')
            ->whereNotExists(function ($q) use ($userId) {
                $q->select(DB::raw(1))
                    ->from('user_items')
                    ->whereColumn('user_items.item_id', 'items.id')
                    ->where('user_items.user_id', $userId);
            })
            ->select(
                'items.id',
                'items.name',
                'items.image_name'
            )
            ->distinct()
            ->first();

        if (!$item) {
            return;
        }

        DB::table('gifts')->insert([
            'title' => 'タグアイコン獲得！',
            'body' => "ランキング投稿で「{$item->name}」を獲得しました。",
            'case' => 3,
            'user_id' => $userId,
            'reward_type' => 'item',
            'reward_code' => (string)$item->id,
            'reward_amount' => 1,
            'expires_at' => null,
            'from_date' => null,
            'send_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
