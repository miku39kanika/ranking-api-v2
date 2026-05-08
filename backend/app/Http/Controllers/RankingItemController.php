<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RankingItem;
use Illuminate\Support\Facades\Log;
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
    public function store(Request $request)
{
    $item = RankingItem::create([
        'ranking_id' => $request->ranking_id,
        'name' => $request->name,
        'votes' => 0,
        'aliases' => $request->aliases
    ]);
    Log::info('RankingItemController@store called');
    return response()->json($item);
}
public function addAlias(Request $request, $id)
{
    $item = RankingItem::find($id);

    if (!$item) {
        return response()->json(['message' => 'Item not found'], 404);
    }
    Log::info('RankingItemController@addAlias called');

    $aliases = $item->aliases ?? [];

    // 重複防止（任意）
    if (!in_array($request->alias, $aliases)) {
        $aliases[] = $request->alias;
    }

    $item->aliases = $aliases;
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