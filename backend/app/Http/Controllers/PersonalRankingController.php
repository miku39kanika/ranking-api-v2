<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalRanking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ContentFilterService;
class PersonalRankingController extends Controller
{
    public function show($userId)
{
    Log::info('PersonalRankingController@show called');
    $ranking = PersonalRanking::with('items')
        ->where('user_id', $userId)
        ->first();

        if (!$ranking) {
        $ranking = new PersonalRanking();
        $ranking->id = 0;
        $ranking->user_id = $userId;
        $ranking->title = "ランキング未作成";
        $ranking->setRelation('items', collect([]));
    }
    return response()->json($ranking);
}

public function update(Request $request, ContentFilterService $filter)
{ Log::info('PersonalRankingController@update called');

// バリデーション
$request->validate([
    'title' => 'nullable|string|max:30',

    'items' => 'nullable|array',

    'items.*.word' => 'nullable|string|max:15',

    'items.*.rank' => 'required|integer',
]);

    // =====================
    // NGチェック（先に全部まとめて）
    // =====================
    if ($request->filled('title')) {
        if ($filter->containsNgWord($request->title)) {
            return response()->json(['error' => 'NG_WORD'], 422);
        }
    }

    if (is_array($request->items)) {
        foreach ($request->items as $item) {

            if (!isset($item['word']) || $item['word'] === null) {
                continue;
            }

            if ($filter->containsNgWord($item['word'])) {
                return response()->json(['error' => 'NG_WORD'], 422);
            }
        }
    }

    $ranking = PersonalRanking::where(
        'user_id',
        $request->user()->id
    )->first();

    if (!$ranking) {

        return response()->json([
            'message' => 'ranking not found'
        ], 404);
    }

   DB::transaction(function () use ($ranking, $request) {

    // =====================
    // title更新
    // =====================

    if (
        $request->has('title') &&
        $request->title !== null
    ) {
        $ranking->title = $request->title;
        $ranking->save();
    }

    // =====================
    // items更新
    // =====================

    if (is_array($request->items)) {

        foreach ($request->items as $item) {

            // wordが無い/nullならスキップ
            if (
                !isset($item['word']) ||
                $item['word'] === null
            ) {
                continue;
            }

            // rankが無いならスキップ
            if (!isset($item['rank'])) {
                continue;
            }

            $ranking->items()
                ->where('rank', $item['rank'])
                ->update([
                    'word' => $item['word']
                ]);
        }
    }
    
});

    return response()->json([
        'success' => true
    ]);
}
}
