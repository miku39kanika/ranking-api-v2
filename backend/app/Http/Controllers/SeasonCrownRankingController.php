<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Ranking;
use App\Models\Vote;

class SeasonCrownRankingController extends Controller
{
    public function index(Request $request)
    {
        Log::info('SeasonCrownRankingController@index called');
        $userId = $request->user()->id;

        $ranking = Ranking::with(['items', 'user', 'tags'])
            ->find(1); // 公式ランキングのIDを指定

        if (!$ranking) {

            return response()->json([
                'message' => 'Ranking not found'
            ], 404);
        }
        // =====================
        // 公式：今月の獲得クラウンランキング
        // ranking_id = 1
        // =====================


        // 最新season取得
        $latestSeason = DB::table('season_crown_rankings')
            ->max('season');

        $items = DB::table('season_crown_rankings')
            ->join(
                'users',
                'season_crown_rankings.user_id',
                '=',
                'users.id'
            )
            ->where(
                'season_crown_rankings.season',
                $latestSeason
            )
            ->orderByDesc('season_crown_rankings.crown_amount')
            ->limit(100)
            ->get([
                'users.id as user_id',
                'users.user_name',
                'users.icon_type',
                'users.icon_name',
                'users.public_id',
                'users.about_self',
                'users.plan_type',
                'season_crown_rankings.crown_amount',
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

            // 通常ランキングと同じ形式
            'items' => $items->map(function ($item, $index) {

                return [
                    'id' => $index + 1,
                    'name' => $item->user_name,
                    'votes' => $item->crown_amount,
                    'aliases' => [],
                    'my_votes' => 0,
                    'my_votes_today' => 0,

                    // フロント用追加情報
                    'user_id' => $item->user_id,
                    'icon_type' => $item->icon_type,
                    'icon_name' => $item->icon_name,
                    'about_self' => $item->about_self ?? '',
                    'plan_type' => $item->plan_type ?? 0,
                    'public_id' => $item->public_id ?? '',
                ];
            }),

            'creator' => $ranking->user ? [
                'id' => $ranking->user->id,
                'user_name' => $ranking->user->user_name,
                'icon_type' => $ranking->user->icon_type,
                'icon_name' => $ranking->user->icon_name,
                'about_self' => $ranking->user->about_self,
                'plan_type' => $ranking->user->plan_type,
            ] : null,

            'is_item_add_limited' => true,

            'can_vote' => false,

            'daily_vote_limit' => 0,

            'total_vote_limit' => 0,

            'invite_code' => null,
        ]);
    }
}
