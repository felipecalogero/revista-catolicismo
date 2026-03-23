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

            // Sync user data from PagBank
            $this->syncUserData($subscription->user, $data);
        }

        $subscription->update($updateData);
        
        Log::info('Subscription updated via webhook', [
            'subscription_id' => $subscription->id,
            'status' => $status,
        ]);
    }

    /**
     * Sincroniza dados do usuário recebidos pelo PagBank
     */
    private function syncUserData($user, array $data): void
    {
        if (!$user) return;

        $userData = [];

        // Extrair CPF (tax_id)
        $taxId = $data['customer']['tax_id'] ?? null;
        if ($taxId && empty($user->cpf)) {
            $userData['cpf'] = preg_replace('/[^0-9]/', '', $taxId);
        }

        // Extrair Telefone
        $phone = $data['customer']['phones'][0] ?? null;
        if ($phone && empty($user->phone)) {
            $area = $phone['area'] ?? '';
            $number = $phone['number'] ?? '';
            if ($area && $number) {
                $userData['phone'] = preg_replace('/[^0-9]/', '', $area . $number);
            }
        }

        // Extrair Endereço (Shipping ou Billing)
        $addressData = $data['shipping']['address'] ?? $data['billing']['address'] ?? null;
        if ($addressData) {
            $street = $addressData['street'] ?? '';
            $number = $addressData['number'] ?? '';
            $complement = $addressData['complement'] ?? '';
            $neighborhood = $addressData['locality'] ?? ''; // Bairro
            $city = $addressData['city'] ?? '';
            $region = $addressData['region_code'] ?? '';
            $postalCode = $addressData['postal_code'] ?? '';

            if ($street) {
                $userData['address'] = $street . ($number ? ", $number" : "") . ($complement ? " - $complement" : "");
            }
            if ($neighborhood) {
                $userData['neighborhood'] = $neighborhood;
            }
            if ($city) {
                $userData['city'] = $city;
            }
            if ($region) {
                $userData['state'] = $region;
            }
            if ($postalCode) {
                $userData['zip_code'] = preg_replace('/[^0-9]/', '', $postalCode);
            }
        }

        if (!empty($userData)) {
            $user->update($userData);
            Log::info('User data synced from PagBank webhook', [
                'user_id' => $user->id,
                'fields' => array_keys($userData),
            ]);
        }
    }

}
