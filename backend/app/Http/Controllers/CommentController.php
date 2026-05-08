<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class CommentController extends Controller
{
    /**
     * コメント一覧取得（ランキング単位）
     */
public function index($rankingId)
{
    Log::info('CommentController@index called');

    $comments = Comment::with('user')
        ->where('ranking_id', $rankingId)
        ->orderBy('created_at', 'desc')
        ->get();

    Log::info('Fetched comments:', $comments->toArray());

    return response()->json($comments);
}

    /**
     * コメント投稿
     */
    public function store(Request $request)
    {
        Log::info('CommentController@store called');
        $request->validate([
            'ranking_id' => 'required|integer',
            'user_id' => 'required|string',
            'body' => 'required|string|max:200',
        ]);

        $comment = Comment::create([
            'ranking_id' => $request->ranking_id,
            'user_id' => $request->user_id,
            'body' => $request->body,
        ]);

        return response()->json([
            'success' => true,
            'comment' => $comment
        ]);
    }
}