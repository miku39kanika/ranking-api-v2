<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserCurrency;
use App\Models\Currency;
use App\Models\CurrencyHistory;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    public function index(Request $request)
    {
        Log::info('CurrencyController@index called');

        $userId = $request->user()->id;

        $currencies = UserCurrency::with('currency')
            ->where('user_id', $userId)
            ->get();

        // crown以外
        $normalCurrencies = $currencies
            ->filter(fn($item) => $item->currency->code !== 'crown')
            ->map(function ($item) {

                return [
                    'code' => $item->currency->code,
                    'name' => $item->currency->name,
                    'amount' => (int) $item->amount,
                ];
            })
            ->values();

        // crown currency取得
        $crownCurrency = Currency::where(
            'code',
            'crown'
        )->first();

        // crown現在所持数
        $currentCrown = UserCurrency::where(
            'user_id',
            $userId
        )
            ->where(
                'currency_id',
                $crownCurrency->id
            )
            ->value('amount') ?? 0;

        // 今月開始
        $startOfMonth = now()->startOfMonth();

        // 今月の取得数（マイナス除外）
        $monthlyEarnedCrown =
            CurrencyHistory::where(
                'user_id',
                $userId
            )
            ->where(
                'currency_id',
                $crownCurrency->id
            )
            ->where('amount', '>', 0)
            ->where(
                'created_at',
                '>=',
                $startOfMonth
            )
            ->sum('amount');

        // 総取得数（マイナス除外）
        $totalEarnedCrown =
            CurrencyHistory::where(
                'user_id',
                $userId
            )
            ->where(
                'currency_id',
                $crownCurrency->id
            )
            ->where('amount', '>', 0)
            ->sum('amount');

        // crown情報
        $crownData = [
            'code' => 'crown',
            'name' => 'クラウン',

            // 現在所持数
            'amount' => (int) $currentCrown,
            // 今月の取得数
            'monthly_earned' =>
            (int) $monthlyEarnedCrown,
            // 総取得数
            'total_earned' =>
            (int) $totalEarnedCrown,
        ];

        return response()->json([
            ...$normalCurrencies,
            $crownData
        ]);
    }

    public function change(Request $request)
    {
        Log::info('CurrencyController@change called');

        $currency = Currency::where(
            'code',
            $request->code
        )->first();

        if (!$currency) {

            return response()->json([
                'message' => 'Currency not found'
            ], 404);
        }
        // orbなど
        $userCurrency = UserCurrency::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'currency_id' => $currency->id,
            ],
            [
                'amount' => 0
            ]
        );

        $newAmount =
            $userCurrency->amount + $request->amount;

        // マイナス防止
        if ($newAmount < 0) {

            return response()->json([
                'message' => 'Not enough currency'
            ], 400);
        }

        $userCurrency->amount = $newAmount;
        $userCurrency->save();

        CurrencyHistory::create([
            'user_id' => $request->user()->id,
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
