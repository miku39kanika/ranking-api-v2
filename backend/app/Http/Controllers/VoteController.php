<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ranking;
use App\Models\RankingItem;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\ReadingService;
use App\Jobs\IncrementVoteJob;

class VoteController extends Controller
{

public function vote(Request $request)
{
    Log::info('VoteController@vote called');

    $itemId = $request->input('item_id');
    $userId = $request->input('user_id');
    $today = now()->toDateString();

    // item取得（ranking_limit見るため）
    $item = RankingItem::find($itemId);

    if (!$item) {
        return response()->json([
            'message' => 'アイテムが見つかりません'
        ], 404);
    }

    $ranking = Ranking::find($item->ranking_id);

    if (!$ranking) {
        return response()->json([
            'message' => 'ランキングが見つかりません'
        ], 404);
    }

    // ① 今日そのランキングに何回投票したか
    $voteCount = Vote::where('user_identifier', $userId)
        ->where('vote_date', $today)
        ->whereHas('rankingItem', function ($q) use ($ranking) {
            $q->where('ranking_id', $ranking->id);
        })
        ->count();

    // ② 制限チェック
    if ($voteCount >= $ranking->daily_vote_limit) {
        return response()->json([
            'message' => '今日の投票回数を超えています'
        ], 403);
    }

    // ③ 保存
    Vote::create([
        'ranking_item_id' => $itemId,
        'user_identifier' => $userId,
        'vote_date' => $today
    ]);

    IncrementVoteJob::dispatch($itemId);

    return response()->json([
        'success' => true,
        'queued' => true
    ]);
}
}
