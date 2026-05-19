<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RankingItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ContentFilterService;

class RankingItemController extends Controller
{
    public function show($id)
    {
        Log::info('RankingItemController@show called');
        $item = RankingItem::find($id);

        if (!$item) {
            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'votes' => $item->votes,
            'aliases' => $item->aliases
        ]);
    }
    public function store(
        Request $request,
        ContentFilterService $filter
    ) {

        Log::info('RankingItemController@store called');

        $request->validate([
            'ranking_id' => 'required|integer',
            'name' => 'required|string|max:30',
            'aliases.*' => 'nullable|string|max:15',
        ]);

        // =====================
        // NGワード
        // =====================

        if ($filter->containsNgWord($request->name)) {

            return response()->json([
                'error' => 'NG_WORD'
            ], 422);
        }

        // =====================
        // aliases整形
        // =====================

        $aliases = collect($request->aliases ?? [])
            ->map(fn($a) => trim($a))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // =====================
        // 自分自身との重複
        // =====================

        if (in_array($request->name, $aliases)) {

            return response()->json([
                'error' => 'DUPLICATE_NAME'
            ], 422);
        }

        // =====================
        // 既存アイテム取得
        // =====================

        $items = RankingItem::where(
            'ranking_id',
            $request->ranking_id
        )->get();

        // =====================
        // 重複チェック
        // =====================

        foreach ($items as $item) {

            $existingNames = collect([
                $item->name,
                ...($item->aliases ?? [])
            ]);

            // name重複
            if ($existingNames->contains($request->name)) {

                return response()->json([
                    'error' => 'ITEM_ALREADY_EXISTS'
                ], 422);
            }

            // alias重複
            foreach ($aliases as $alias) {

                if ($existingNames->contains($alias)) {

                    return response()->json([
                        'error' => 'ALIAS_ALREADY_EXISTS'
                    ], 422);
                }
            }
        }

        // =====================
        // 作成
        // =====================

        $item = RankingItem::create([
            'ranking_id' => $request->ranking_id,
            'name' => $request->name,
            'votes' => 0,
            'aliases' => $aliases,
        ]);

        return response()->json($item);
    }

    public function addAlias(
        Request $request,
        $id,
        ContentFilterService $filter
    ) {

        Log::info('RankingItemController@addAlias called');

        $request->validate([
            'alias' => 'required|string|max:15',
        ]);

        $alias = trim($request->alias);

        // =====================
        // NGワード
        // =====================

        if ($filter->containsNgWord($alias)) {

            return response()->json([
                'error' => 'NG_WORD'
            ], 422);
        }

        $item = RankingItem::find($id);

        if (!$item) {

            return response()->json([
                'message' => 'Item not found'
            ], 404);
        }

        // =====================
        // 自分自身チェック
        // =====================

        if ($alias === $item->name) {

            return response()->json([
                'error' => 'SAME_AS_NAME'
            ], 422);
        }

        // =====================
        // 同ランキング全item取得
        // =====================

        $items = RankingItem::where(
            'ranking_id',
            $item->ranking_id
        )->get();

        foreach ($items as $target) {

            $names = collect([
                $target->name,
                ...($target->aliases ?? [])
            ]);

            if ($names->contains($alias)) {

                return response()->json([
                    'error' => 'ALIAS_ALREADY_EXISTS'
                ], 422);
            }
        }

        // =====================
        // 保存
        // =====================

        $aliases = $item->aliases ?? [];

        $aliases[] = $alias;

        $item->aliases = array_values(
            array_unique($aliases)
        );

        $item->save();

        return response()->json($item);
    }

    public function deleteAlias($id, $alias)
    {
        Log::info('RankingItemController@deleteAlias called');
        $item = RankingItem::find($id);

        $aliases = $item->aliases ?? [];

        $aliases = array_values(array_filter($aliases, fn($a) => $a !== $alias));

        $item->aliases = $aliases;
        $item->save();

        return response()->json($item);
    }
}
