<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ContentFilterService;

class CommentController extends Controller
{
    /**
     * コメント一覧取得（ランキング単位）
     */
    public function index(Request $request, $rankingId)
    {
        Log::info('CommentController@index called');

        $userId = $request->user()->id;

        // ブロック中ユーザー取得
        $blockedUserIds = DB::table('blocks')
            ->where('user_id', $userId)
            ->pluck('blocked_user_id');

        $comments = Comment::with('user')
            ->where('ranking_id', $rankingId)

            // ブロックユーザー除外
            ->whereNotIn('user_id', $blockedUserIds)
            ->whereHas('user', function ($q) {
                $q->where('is_deleted', 0);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        Log::info('Fetched comments:', $comments->toArray());

        return response()->json($comments);
    }

    /**
     * コメント投稿
     */
    public function store(Request $request, ContentFilterService $filter)
    {
        Log::info('CommentController@store called');
        if ($filter->containsNgWord($request->body)) {
            return response()->json([
                'error' => 'NG_WORD'
            ], 422);
        }

        $request->validate([
            'ranking_id' => 'required|integer',
            'body' => 'required|string|max:200',
        ]);

        $comment = Comment::create([
            'ranking_id' => $request->ranking_id,
            'user_id' => $request->user()->id,
            'body' => $request->body,
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment
        ]);
    }
}
