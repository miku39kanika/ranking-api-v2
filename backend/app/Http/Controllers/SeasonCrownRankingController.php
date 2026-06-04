<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Ranking;

class SeasonCrownRankingController extends Controller
{
    public function index(Request $request)
    {
        Log::info('SeasonCrownRankingController@index called');

        $ranking = Ranking::with(['items', 'user', 'tags'])
            ->find(1);

        if (!$ranking) {

            return response()->json([
                'message' => 'Ranking not found'
            ], 404);
        }

        // =====================
        // 最新 month 取得
        // =====================

        $latestYearMonth = DB::table('monthly_crown_rankings')
            ->max('year_month');

        $latestSnapshot = DB::table('monthly_crown_rankings')
            ->where('year_month', $latestYearMonth)
            ->max('snapshot_date');

        // =====================
        // ランキング取得
        // =====================

        $items = DB::table('monthly_crown_rankings')
            ->join('users', function ($join) {
                $join->on('monthly_crown_rankings.user_id', '=', 'users.id')
                    ->where('users.is_deleted', 0);
            })
            ->where(
                'monthly_crown_rankings.year_month',
                $latestYearMonth
            )
            ->where(
                'monthly_crown_rankings.snapshot_date',
                $latestSnapshot
            )
            ->orderBy('monthly_crown_rankings.rank')
            ->limit(100)
            ->get([
                'users.id as user_id',
                'users.user_name',
                'users.icon_type',
                'users.icon_name',
                'users.public_id',
                'users.about_self',
                'users.plan_type',

                'monthly_crown_rankings.crown_amount',
                'monthly_crown_rankings.rank',
            ]);

        return response()->json([
            'id' => $ranking->id,
            'title' => $ranking->title,
            'reading' => $ranking->reading,
            'image_name' => $ranking->image_name,
            'image_type' => $ranking->image_type,
            'image_path' => $ranking->image_path,
            'is_liked' => 0,

            'tags' => $ranking->tags->map(function ($tag) {

                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            }),

            'items' => $items->values()->map(function ($item, $index) {

                return [
                    'id' => $index + 1,

                    'name' => $item->user_name,

                    'votes' => $item->crown_amount,

                    'aliases' => [],

                    'my_votes' => 0,

                    'my_votes_today' => 0,

                    // ユーザー情報
                    'user_id' => $item->user_id,

                    'icon_type' => $item->icon_type,

                    'icon_name' => $item->icon_name,

                    'about_self' =>
                    $item->about_self ?? '',

                    'plan_type' =>
                    $item->plan_type ?? 0,

                    'public_id' =>
                    $item->public_id ?? '',
                ];
            }),

            'creator' => $ranking->user ? [

                'id' => $ranking->user->id,

                'user_name' =>
                $ranking->user->user_name,

                'icon_type' =>
                $ranking->user->icon_type,

                'icon_name' =>
                $ranking->user->icon_name,

                'about_self' =>
                $ranking->user->about_self,

                'plan_type' =>
                $ranking->user->plan_type,

            ] : null,

            'is_item_add_limited' => true,

            'can_vote' => false,

            'daily_vote_limit' => 0,

            'total_vote_limit' => 0,

            'invite_code' => null,

            // 追加
            'year_month' => $latestYearMonth,
        ]);
    }
}
