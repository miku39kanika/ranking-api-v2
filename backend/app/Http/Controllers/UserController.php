<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ContentFilterService;
use App\Models\Gift;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function show($device_id)
    {
        Log::info('UserController@show called');

        $user = User::where('device_id', $device_id)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }
        Log::info('USER', ['user' => $user]);
        return response()->json([
            'id' => (string) $user->id,
            'public_id' => $user->public_id,
            'user_name' => $user->user_name,
            'device_id' => $user->device_id,
            'email' => $user->email,
            'plan_type' => $user->plan_type,
            'icon_name' => $user->icon_name,
            'about_self' => $user->about_self,
            'is_deleted' => $user->is_deleted,
            'banned_at' => $user->banned_at,
            'invite_code' => $user->invite_code,
            'invited_by' => $user->invited_by,
        ]);
    }

    public function update(Request $request, ContentFilterService $filter)
    {
        Log::info('UserController@update called');
        $request->validate([
            'user_name' => 'nullable|string|max:15',
            'about_self' => 'nullable|string|max:60',
        ]);
        if ($filter->containsNgWord($request->user_name) || $filter->containsNgWord($request->about_self)) {
            return response()->json([
                'error' => 'NG_WORD'
            ], 422);
        }

        $user = User::find($request->user()->id);

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // ✅ バリデーション
        $validated = $request->validate([
            'user_name' => 'required|string|max:50',
            'about_self' => 'nullable|string|max:255',
            'icon_name' => 'nullable|string|max:50',
        ]);

        // ✅ 更新（来てるものだけ更新）
        if (isset($validated['user_name'])) {
            $user->user_name = trim($validated['user_name']);
        }

        if (isset($validated['about_self'])) {
            $user->about_self = $validated['about_self'];
        }

        if (isset($validated['icon_name'])) {
            $user->icon_name = $validated['icon_name'];
        }

        $user->save();
        $user->refresh();

        return response()->json($user);
    }

    public function findByPublicId($publicId)
    {
        $user = User::where(
            'public_id',
            $publicId
        )->first();

        if (!$user) {

            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'id' => $user->id,
            'public_id' => $user->public_id,
            'user_name' => $user->user_name,
            'icon_type' => $user->icon_type,
            'icon_name' => $user->icon_name,
            'about_self' => $user->about_self,
        ]);
    }

    public function applyInvite(Request $request)
    {
        $user = $request->user();

        $code = $request->invite_code;
        $inviter = User::where('invite_code', $code)->first();

        if (!$inviter) {
            return response()->json(['message' => 'Invalid code'], 404);
        }

        if ($inviter->id === $user->id) {
            return response()->json(['message' => 'Cannot self invite'], 400);
        }
        if ($user->invited_by) {
            return response()->json(['message' => 'Already invited'], 400);
        }

        DB::transaction(function () use ($user, $inviter) {

            $user->invited_by = $inviter->id;
            $user->save();
            $user->refresh();
            Gift::create([
                'title' => '招待報酬',
                'body' => '招待限定報酬です！招待コードを入力してくれてありがとうございます！',
                'case' => 3,
                'user_id' => $user->id, // ←招待された人
                'reward_type' => 'currency',
                'reward_code' => 2,
                'reward_amount' => 50,
                'from_date' => null,
                'expires_at' => now()->addDays(14),
            ]);
            Gift::create([
                'title' => '招待報酬',
                'body' => 'あなたが招待した人がアプリへやってきました！招待ありがとうございます！',
                'case' => 3,
                'user_id' => $inviter->id, // ←招待した人

                'reward_type' => 'currency',
                'reward_code' => 2,
                'reward_amount' => 50,

                'from_date' => null,
                'expires_at' => now()->addDays(14),
            ]);
        });

        return response()->json($user);
    }

    public function sendVerifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        $user = $request->user();

        $code = random_int(100000, 999999);

        $user->email = $request->email;

        // 👇 未認証に戻す
        $user->email_verified_at = null;

        $user->email_verify_code = $code;

        $user->save();

        Mail::raw(
            "認証コード: {$code}",
            function ($message) use ($user) {

                $message->to($user->email)
                    ->subject('メール認証');
            }
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'code' => 'required',
        ]);

        $user = $request->user();

        if ($user->email_verify_code !== $request->code) {

            return response()->json([
                'message' => 'invalid code'
            ], 400);
        }

        $user->email_verified_at = now();
        $user->email_verify_code = null;
        $user->save();

        return response()->json($user);
    }

    public function transferAccount(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required',
        ]);

        // 👇 引き継ぎ先（今の端末）
        $currentUser = $request->user();

        // 👇 引き継ぎ元
        $targetUser = User::where(
            'email',
            $request->email
        )->first();

        if (!$targetUser) {

            return response()->json([
                'message' => 'user not found'
            ], 404);
        }
        if (!$targetUser->email_verified_at) {

            return response()->json([
                'message' => 'email not verified'
            ], 400);
        }
        // 👇 認証コード確認
        if (
            $targetUser->email_verify_code !==
            $request->code
        ) {

            return response()->json([
                'message' => 'invalid code'
            ], 400);
        }

        // 👇 認証済みにする
        $targetUser->email_verified_at = now();
        $targetUser->email_verify_code = null;

        // 👇 device_id を新端末へ
        // 👇 今の端末ID
        $newDeviceId = $currentUser->device_id;

        // 👇 現在の仮アカウントを切り離す
        $currentUser->device_id = null;
        $currentUser->save();

        // 👇 引き継ぎ先へ端末紐付け
        $targetUser->device_id = $newDeviceId;

        // 👇 認証コード削除
        $targetUser->email_verify_code = null;

        $targetUser->save();

        return response()->json($targetUser);
    }

    public function sendTransferCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $targetUser = User::where(
            'email',
            $request->email
        )->first();

        if (!$targetUser) {

            return response()->json([
                'message' => 'user not found'
            ], 404);
        }

        if (!$targetUser->email_verified_at) {

            return response()->json([
                'message' => 'email not verified'
            ], 400);
        }

        $code = random_int(100000, 999999);

        $targetUser->email_verify_code = $code;

        $targetUser->save();

        Mail::raw(
            "引き継ぎ認証コード: {$code}",
            function ($message) use ($targetUser) {

                $message->to($targetUser->email)
                    ->subject('アカウント引き継ぎ');
            }
        );

        return response()->json([
            'success' => true
        ]);
    }
    public function delete(Request $request)
    {
        Log::info('UserController@delete called');

        $user = $request->user(); // Sanctum認証ユーザー

        if (!$user) {
            return response()->json([
                'message' => 'unauthorized'
            ], 401);
        }

        // すでに削除済みチェック（任意）
        if ($user->is_deleted) {
            return response()->json([
                'message' => 'already deleted'
            ], 400);
        }

        try {

            DB::transaction(function () use ($user) {

                $user->is_deleted = 1;
                $user->email = null; // メールアドレスは削除
                $user->save();

                // 👇 必要ならトークン無効化（ログアウト扱い）
                $user->tokens()->delete();
            });

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {

            Log::error('DELETE USER FAILED', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'server error'
            ], 500);
        }
    }
}
