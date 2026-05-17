<?php


namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class FollowController extends Controller
{   
public function follow(Request $request)
{
    Log::info('FollowController@follow called');
    $me = $request->user()->id;
    $target = $request->input('target_id');

    if ($me === $target) {
        return response()->json(['message' => '自分はフォローできません'], 400);
    }

    $exists = DB::table('follows')
        ->where('follower_id', $me)
        ->where('followed_id', $target)
        ->exists();

    if ($exists) {
        // 👇 フォロー済み → 解除
        DB::table('follows')
            ->where('follower_id', $me)
            ->where('followed_id', $target)
            ->delete();

        return response()->json([
            'success' => true,
            'following' => false
        ]);
    } else {
        if ($this->isBlocked($me, $target))  {

    return response()->json([
        'success' => false
    ], 403);
}
        // 👇 未フォロー → 追加
        DB::table('follows')->insert([
            'follower_id' => $me,
            'followed_id' => $target,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'following' => true
        ]);
    }
    
}

public function counts($userId)
{
    Log::info('FollowController@counts called');
    $following = DB::table('follows')
        ->where('follower_id', $userId)
        ->count();

    $followers = DB::table('follows')
        ->where('followed_id', $userId)
        ->count();

    return response()->json([
        'following' => $following,
        'followers' => $followers
    ]);
}

public function followings($userId)
{
    Log::info('FollowController@followings called');
    $users = DB::table('follows')
        ->join('users', 'follows.followed_id', '=', 'users.id')
        ->where('follows.follower_id', $userId)
        ->select('users.*')
        ->get();

    return response()->json($users);
}

public function followers($userId)
{
    Log::info('FollowController@followers called');
    $users = DB::table('follows')
        ->join('users', 'follows.follower_id', '=', 'users.id')
        ->where('follows.followed_id', $userId)
        ->select('users.*')
        ->get();

    return response()->json($users);
}

 // =====================
    // ブロック判定
    // =====================

    private function isBlocked($me, $target): bool
    {
        return DB::table('blocks')

            ->where(function ($query) use ($me, $target) {

                // 自分 → 相手
                $query->where([
                    'user_id' => $me,
                    'blocked_user_id' => $target
                ]);

            })

            ->orWhere(function ($query) use ($me, $target) {

                // 相手 → 自分
                $query->where([
                    'user_id' => $target,
                    'blocked_user_id' => $me
                ]);

            })

            ->exists();
    }
}