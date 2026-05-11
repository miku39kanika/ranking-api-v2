<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserCurrency;
use App\Models\Currency;
use App\Models\CurrencyHistory;
use Illuminate\Support\Facades\Log;
class CurrencyController extends Controller
{
    public function index($userId)
    {
        Log::info('CurrencyController@index called');
        $currencies = UserCurrency::with('currency')
            ->where('user_id', $userId)
            ->get();

        $result = $currencies->map(function ($item) {
            return [
                'code' => $item->currency->code,
                'name' => $item->currency->name,
                'amount' => $item->amount,
            ];
        });

        return response()->json($result);
    }

    public function change(Request $request)
{
    Log::info('CurrencyController@change called');
    $userCurrency = UserCurrency::with('currency')
        ->where('user_id', $request->user_id)
        ->whereHas('currency', function ($q) use ($request) {
            $q->where('code', $request->code);
        })
        ->first();

    if (!$userCurrency) {
        return response()->json([
            'message' => 'Currency not found'
        ], 404);
    }

    $newAmount = $userCurrency->amount + $request->amount;

    // マイナス防止
    if ($newAmount < 0) {
        return response()->json([
            'message' => 'Not enough currency'
        ], 400);
    }

    $userCurrency->amount = $newAmount;
    $userCurrency->save();

    CurrencyHistory::create([
        'user_id' => $request->user_id,
        'currency_id' => $userCurrency->currency_id,
        'amount' => $request->amount,
        'reason' => 'API change',
    ]);

    return response()->json([
        'success' => true,
        'amount' => $userCurrency->amount,
    ]);
}
}