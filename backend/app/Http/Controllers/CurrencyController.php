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
                    'amount' => $item->amount,
                ];
            })
            ->values();

        // crownのみ
        $crowns = $currencies
            ->filter(fn($item) => $item->currency->code === 'crown');

        // crown全シーズン合計
        $totalCrown = $crowns->sum('amount');

        // 最新season
        $latestSeason = $crowns
            ->max('season');

        // 最新seasonのcrown合計
        $latestSeasonAmount = $crowns
            ->filter(
                fn($item) =>
                $item->season === $latestSeason
            )
            ->sum('amount');

        // crown情報
        $crownData = [
            'code' => 'crown',
            'name' => 'クラウン',

            // 全シーズン合計
            'amount' => $totalCrown,

            // 最新シーズン
            'latest_season' => $latestSeason,

            // 最新シーズン量
            'latest_season_amount' =>
            $latestSeasonAmount,
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

        // crown
        if ($request->code === 'crown') {

            $userCurrency = UserCurrency::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'currency_id' => $currency->id,
                    'season' => $request->season,
                ],
                [
                    'amount' => 0
                ]
            );
        } else {

            // orbなど
            $userCurrency = UserCurrency::firstOrCreate(
                [
                    'user_id' => $request->user()->id,
                    'currency_id' => $currency->id,
                    'season' => null,
                ],
                [
                    'amount' => 0
                ]
            );
        }

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
