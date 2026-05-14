<?php


namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ContentFilterService;

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
    'plan_type'=> $user->plan_type,
    'icon_name' => $user->icon_name,
    'about_self' => $user->about_self,
    'is_deleted' => $user->is_deleted,
    'banned_at' => $user->banned_at,
    
]);
}

public function update(Request $request, $id, ContentFilterService $filter)
{
    Log::info('UserController@update called');
    if ($filter->containsNgWord($request->user_name) || $filter->containsNgWord($request->about_self)) {
        return response()->json([
            'error' => 'NG_WORD'
        ], 422);
    }

    $user = User::find($id);

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

}