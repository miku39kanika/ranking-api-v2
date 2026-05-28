<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    // 👍 いいねトグル
    public function toggle(Request $request)
    {
        Log::info('LikeController@toggle called');
        $request->validate([
            'user_id' => 'required|string',
            'ranking_id' => 'required',
        ]);

        $like = Like::where('user_id', $request->user()->id)
            ->where('ranking_id', $request->ranking_id)
            ->first();

        // すでにあれば削除（解除）
        if ($like) {
            $like->delete();

            return response()->json([
                'liked' => false
            ]);
        }

        // なければ作成（いいね）
        Like::create([
            'user_id' => $request->user()->id,
            'ranking_id' => $request->ranking_id,
        ]);

        return response()->json([
            'liked' => true
        ]);
    }
}
