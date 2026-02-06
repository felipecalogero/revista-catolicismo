<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PagBankService
{
    private string $apiUrl;
    private string $apiToken;
    private string $webhookUrl;

    public function __construct()
    {
        $this->apiUrl = config('subscriptions.pagbank_api_url', 'https://sandbox.api.pagseguro.com');
        $this->apiToken = trim(config('subscriptions.pagbank_api_token', ''));
        $this->webhookUrl = config('subscriptions.pagbank_webhook_url', '');
    }

    /**
     * Cria um checkout no PagBank via API
     */
    public function createCheckout(array $data): ?array
    {
        try {
            if (empty($this->apiToken)) {
                Log::error('PagBank API: Token not configured');
                return null;
            }

            if (!isset($data['notification_urls']) && $this->webhookUrl) {
                $data['notification_urls'] = [$this->webhookUrl];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(30)->post($this->apiUrl . '/checkouts', $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PagBank API: Checkout creation failed', [
                'status' => $response->status(),
                'error' => $response->json() ?? $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('PagBank API: Exception creating checkout', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Cria um checkout simples
     */
    public function createSimpleCheckout(
        int $subscriptionId,
        float $amount,
        string $customerName,
        string $customerEmail,
        string $planName = 'Assinatura Anual'
    ): ?array {
        $amountInCents = (int) ($amount * 100);

        $checkoutData = [
            'reference_id' => (string) $subscriptionId,
            'description' => $planName,
            'amount' => [
                'value' => $amountInCents,
                'currency' => 'BRL',
            ],
            'items' => [
                [
                    'reference_id' => (string) $subscriptionId,
                    'name' => $planName,
                    'quantity' => 1,
                    'unit_amount' => $amountInCents,
                ],
            ],
            'customer' => [
                'name' => $customerName,
                'email' => $customerEmail,
            ],
        ];

        return $this->createCheckout($checkoutData);
    }
}
