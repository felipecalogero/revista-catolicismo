<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PagBankWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $rawBody = $request->getContent();
        $data = $request->all();
        
        $logFile = storage_path('logs/pagbank-webhook-raw.log');
        file_put_contents($logFile, json_encode([
            'timestamp' => now()->toIso8601String(),
            'raw_body' => $rawBody,
            'parsed_body' => $data,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n\n" . str_repeat("=", 80) . "\n\n", FILE_APPEND);

        Log::info('PagBank Webhook received', [
            'reference_id' => $data['reference_id'] ?? null,
        ]);

        $subscription = null;

        if (isset($data['reference_id'])) {
            $subscription = Subscription::find($data['reference_id']);
        }

        if (!$subscription && isset($data['charges'][0]['id'])) {
            $subscription = Subscription::where('pagbank_transaction_id', $data['charges'][0]['id'])->first();
        }

        if (!$subscription && isset($data['transaction_id'])) {
            $subscription = Subscription::where('pagbank_transaction_id', $data['transaction_id'])->first();
        }

        if (!$subscription) {
            Log::warning('PagBank Webhook: Subscription not found', ['data' => $data]);
            return response()->json(['error' => 'Subscription not found'], 404);
        }

        // Processa o status do pagamento
        $status = $this->mapPagBankStatus($data);

        if ($status) {
            $this->updateSubscriptionStatus($subscription, $status, $data);
        }

        return response()->json(['success' => true], 200);
    }

    private function mapPagBankStatus(array $data): ?string
    {
        $status = $data['charges'][0]['status'] ?? $data['status'] ?? $data['payment_status'] ?? null;
        
        if (!$status) {
            return null;
        }

        $status = strtoupper($status);
        
        $statusMap = [
            'PAID' => 'active',
            'APPROVED' => 'active',
            'CONFIRMED' => 'active',
            'PENDING' => 'pending',
            'WAITING_PAYMENT' => 'pending',
            'CANCELLED' => 'cancelled',
            'CANCELED' => 'cancelled',
            'REFUNDED' => 'cancelled',
            'EXPIRED' => 'expired',
        ];

        return $statusMap[$status] ?? null;
    }

    private function updateSubscriptionStatus(Subscription $subscription, string $status, array $data): void
    {
        $updateData = ['status' => $status];

        if ($status === 'active' && $subscription->status !== 'active') {
            $updateData['start_date'] = Carbon::now();
            $updateData['end_date'] = Carbon::now()->addYear();
            $updateData['renewal_date'] = Carbon::now()->addYear();

            if (isset($data['charges'][0])) {
                $charge = $data['charges'][0];
                $updateData['pagbank_transaction_id'] = $charge['id'] ?? null;
                $updateData['payment_method'] = $charge['payment_method']['type'] ?? null;
            } elseif (isset($data['transaction_id'])) {
                $updateData['pagbank_transaction_id'] = $data['transaction_id'];
            }
        }

        $subscription->update($updateData);
        
        Log::info('Subscription updated via webhook', [
            'subscription_id' => $subscription->id,
            'status' => $status,
        ]);
    }

}
