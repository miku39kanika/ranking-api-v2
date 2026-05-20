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

    public function consume(Request $request)
    {
        Log::info('ItemController@consume called');
        Log::info('consume request', [
            'user_id' => $request->user()->id,
            'item_id' => $request->item_id,
            'amount' => $request->amount,
        ]);
        $request->validate([
            'item_id' => 'required|integer',
            'amount' => 'required|integer|min:1',
        ]);

        $userId = $request->user()->id;

        $remaining = $request->amount;

        // =====================
        // 消費対象取得
        // 期限あり優先 → 古い順
        // nullは最後
        // =====================

        $items = DB::table('user_items')
            ->where('user_id', $userId)
            ->where('item_id', $request->item_id)
            ->where('quantity', '>', 0)
            ->orderByRaw('expires_at IS NULL')
            ->orderBy('expires_at', 'asc')
            ->get();

        if ($items->isEmpty()) {

            return response()->json([
                'error' => 'ITEM_NOT_FOUND'
            ], 404);
        }

        // =====================
        // 総所持数チェック
        // =====================

        $total = $items->sum('quantity');

        if ($total < $request->amount) {

            return response()->json([
                'error' => 'NOT_ENOUGH_ITEM'
            ], 400);
        }

        DB::beginTransaction();

        try {

            foreach ($items as $item) {

                if ($remaining <= 0) {
                    break;
                }

                $consume = min(
                    $item->quantity,
                    $remaining
                );

                $newQuantity =
                    $item->quantity - $consume;

                if ($newQuantity <= 0) {

                    DB::table('user_items')
                        ->where('id', $item->id)
                        ->delete();
                } else {

                    DB::table('user_items')
                        ->where('id', $item->id)
                        ->update([
                            'quantity' => $newQuantity
                        ]);
                }

                $remaining -= $consume;
            }

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e);

            return response()->json([
                'error' => 'CONSUME_FAILED'
            ], 500);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function consume_ticket(Request $request)
    {
        Log::info('ItemController@consume_ticket called');

        $userId = $request->user()->id;

        $remaining = 1;

        $items = DB::table('user_items')
            ->where('user_id', $userId)
            ->where('item_id', 8)
            ->where('quantity', '>', 0)
            ->orderByRaw('expires_at IS NULL')
            ->orderBy('expires_at', 'asc')
            ->get();

        if ($items->isEmpty()) {

            return response()->json([
                'error' => 'ITEM_NOT_FOUND'
            ], 404);
        }

        DB::beginTransaction();

        try {

            foreach ($items as $item) {

                if ($remaining <= 0) {
                    break;
                }

                $consume = min(
                    $item->quantity,
                    $remaining
                );

                $newQuantity =
                    $item->quantity - $consume;

                if ($newQuantity <= 0) {

                    DB::table('user_items')
                        ->where('id', $item->id)
                        ->delete();
                } else {

                    DB::table('user_items')
                        ->where('id', $item->id)
                        ->update([
                            'quantity' => $newQuantity
                        ]);
                }

                $remaining -= $consume;
            }

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error($e);

            return response()->json([
                'error' => 'CONSUME_FAILED'
            ], 500);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
