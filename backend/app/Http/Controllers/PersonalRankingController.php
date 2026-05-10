<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PersonalRanking;

class PersonalRankingController extends Controller
{
    public function show($userId)
{
    $ranking = PersonalRanking::with('items')
        ->where('user_id', $userId)
        ->first();

    return response()->json($ranking);
}
public function update(Request $request)
{
    $ranking = PersonalRanking::where(
        'user_id',
        $request->user_id
    )->first();

    if (!$ranking) {

        return response()->json([
            'message' => 'ranking not found'
        ], 404);
    }

    $ranking->title = $request->title;

    $ranking->save();

    // 一旦全削除
    $ranking->items()->delete();

    // 再作成
    foreach ($request->items as $item) {

        $ranking->items()->create([

            'rank' => $item['rank'],
            'word' => $item['word'],
        ]);
    }

    return response()->json([
        'success' => true
    ]);
}
}
