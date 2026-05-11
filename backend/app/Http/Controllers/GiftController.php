<?php

namespace App\Http\Controllers;

use App\Models\Gift;

class GiftController extends Controller
{
    public function index()
{
    $userId = request('user_id');
    $from = request('from');

    $query = Gift::query();

    $query->where(function ($q) use ($userId, $from) {

        // =====================
        // case 1：全員
        // =====================
        $q->orWhere('case', 1);

        // =====================
        // case 2：全員 + from
        // =====================
        if ($from) {
            $q->orWhere(function ($sub) use ($from) {
                $sub->where('case', 2)
                    ->whereDate('from_date', '>=', $from);
            });
        }

        // =====================
        // case 3：個別ユーザー
        // =====================
        if ($userId) {
            $q->orWhere(function ($sub) use ($userId) {
                $sub->where('case', 3)
                    ->where('user_id', $userId);
            });
        }
    });

    // =====================
    // 共通：有効期限
    // =====================
    $query->where(function ($q) {
        $q->whereNull('expires_at')
          ->orWhere('expires_at', '>=', now());
    });

    return response()->json(
        $query->latest()->get()
    );
}
}