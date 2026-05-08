<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        Log::info('LoginController@login called');
        $userIdentifier = $request->input('user_identifier');

        if (!$userIdentifier) {
            return response()->json([
                'message' => 'ユーザー識別子が必要です'
            ], 400);
        }

        // --- IGNORE ---
    }

    public function register(Request $request)
    {
        $userIdentifier = $request->input('user_identifier');

        if (!$userIdentifier) {
            return response()->json([
                'message' => 'ユーザー識別子が必要です'
            ], 400);
        }

        // --- IGNORE ---
    }

    public function logout(Request $request)
    {
        // --- IGNORE ---
    }

    public function user(Request $request)
    {
        // --- IGNORE ---
    }
}