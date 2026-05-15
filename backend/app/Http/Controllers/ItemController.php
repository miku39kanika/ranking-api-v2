<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
class ItemController extends Controller
{
    /**
     * アイテム一覧取得（ランキング単位）
     */
public function myIcons(Request $request)
{
    Log::info('ItemController@myIcons called');
    $userId = $request->user()->id;

    $items = DB::table('user_items')
        ->join('items', 'user_items.item_id', '=', 'items.id')
        ->where('user_items.user_id', $userId)
        ->where('user_items.quantity', '>', 0)
        ->where('items.type', '=', 'icon')
        ->select('items.id', 'items.name', 'items.image_name')
        ->get();
 Log::info('myIcons result', [
        'user_id' => $userId,
        'items' => $items->toArray()
    ]);
    return response()->json($items);
}

public function myItems(Request $request)
{
    Log::info('ItemController@myItems called');
    $userId = $request->user()->id;

    $items = DB::table('user_items')
        ->join('items', 'user_items.item_id', '=', 'items.id')
        ->where('user_items.user_id', $userId)
        ->where('user_items.quantity', '>', 0)
        ->where('items.type', '=', 'item')
        ->select(
    'user_items.id as user_item_id',
    'items.id as item_id',
    'items.name',
    'items.image_name',
    'user_items.quantity',
    'user_items.expires_at'
)
        ->get();
 Log::info('myItems result', [
        'user_id' => $userId,
        'items' => $items->toArray()
    ]);
    return response()->json($items);
}
}