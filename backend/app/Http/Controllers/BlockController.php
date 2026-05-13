<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Block;
use Illuminate\Support\Facades\DB;
use App\Models\Follow;
use Illuminate\Support\Facades\Log;

class BlockController extends Controller
{
    // =====================
    // ブロック
    // =====================
public function index($userId)
{
    Log::info('BlockController@index called');

    $blockedUserIds = Block::where('user_id', $userId)
        ->pluck('blocked_user_id');

    return response()->json([
        'blocked_user_ids' => $blockedUserIds
    ]);
}
    public function store(Request $request)
    {
        Log::info('BlockController@store called');
        $userId = $request->user_id;

        $blockedUserId = $request->blocked_user_id;

        // =====================
        // ブロック作成
        // =====================

        Block::firstOrCreate([
            'user_id' => $userId,
            'blocked_user_id' => $blockedUserId,
        ]);

        // =====================
        // 相互フォロー解除
        // =====================

        Follow::where(function ($query) use ($userId, $blockedUserId) {

            // 自分 → 相手
            $query->where([
                'follower_id' => $userId,
                'followed_id' => $blockedUserId,
            ]);

        })->orWhere(function ($query) use ($userId, $blockedUserId) {

            // 相手 → 自分
            $query->where([
                'follower_id' => $blockedUserId,
                'followed_id' => $userId,
            ]);

        })->delete();

        return response()->json([
            'success' => true
        ]);
    }

    // =====================
    // ブロック解除
    // =====================

    public function destroy(Request $request)
    {
        Log::info('BlockController@destroy called');
        Block::where('user_id', $request->user_id)
            ->where('blocked_user_id', $request->blocked_user_id)
            ->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function status(Request $request)
{
    Log::info('BlockController@status called');
    $userId = $request->query('user_id');
    $blockedUserId = $request->query('blocked_user_id');

    if (!$userId || !$blockedUserId) {
        return response()->json([
            'is_blocked' => false
        ], 400);
    }

    $isBlocked = Block::where('user_id', $userId)
        ->where('blocked_user_id', $blockedUserId)
        ->exists();

    return response()->json([
        'is_blocked' => $isBlocked
    ]);
}
}

function isBlocked($me, $target)
{
    Log::info('BlockController@isBlocked called');
    return DB::table('blocks')
        ->where(function ($q) use ($me, $target) {

            $q->where([
                'user_id' => $me,
                'blocked_user_id' => $target
            ]);

        })->orWhere(function ($q) use ($me, $target) {

            $q->where([
                'user_id' => $target,
                'blocked_user_id' => $me
            ]);

        })->exists();
        
}