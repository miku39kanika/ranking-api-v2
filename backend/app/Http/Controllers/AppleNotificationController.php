<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SubscriptionRewardService;

class AppleNotificationController extends Controller
{
    public function handle(Request $request, SubscriptionRewardService $rewardService)
    {
        $signedPayload = $request->input('signedPayload');

        if (!$signedPayload) {
            return response()->json(['message' => 'missing signedPayload'], 400);
        }

        // 本番ではJWS署名検証が必要
        $payload = $this->decodeJwsPayload($signedPayload);

        $notificationType = $payload['notificationType'] ?? null;

        if (!in_array($notificationType, ['SUBSCRIBED', 'DID_RENEW'])) {
            return response()->json(['message' => 'ignored']);
        }

        $data = $payload['data'] ?? [];

        $signedTransactionInfo = $data['signedTransactionInfo'] ?? null;

        if (!$signedTransactionInfo) {
            return response()->json(['message' => 'missing transaction info'], 400);
        }

        $transaction = $this->decodeJwsPayload($signedTransactionInfo);

        $originalTransactionId = $transaction['originalTransactionId'] ?? null;
        $productId = $transaction['productId'] ?? null;

        if (!$originalTransactionId || $productId !== 'premium_monthly') {
            return response()->json(['message' => 'not target product']);
        }

        $purchase = DB::table('purchases')
            ->where('original_transaction_id', $originalTransactionId)
            ->first();

        if (!$purchase) {
            return response()->json(['message' => 'purchase not found']);
        }

        $rewardMonth = now()->format('Y-m');

        $rewardService->grantMonthlyReward(
            $purchase->user_id,
            $originalTransactionId,
            $productId,
            $rewardMonth
        );

        DB::table('users')
            ->where('id', $purchase->user_id)
            ->update([
                'plan_type' => 1,
                'plan_expires_at' => now()->addMonth(),
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'ok']);
    }

    private function decodeJwsPayload(string $jws): array
    {
        $parts = explode('.', $jws);

        if (count($parts) < 2) {
            throw new \Exception('Invalid JWS');
        }

        $payload = $parts[1];

        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);

        return json_decode(base64_decode($payload), true);
    }
}
