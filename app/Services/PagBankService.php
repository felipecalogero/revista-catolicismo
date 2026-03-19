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
        string $planName = 'Assinatura Anual',
        ?string $cpf = null,
        ?string $phone = null,
        ?string $address = null
    ): ?array {
        $amountInCents = (int) ($amount * 100);

        $customer = [
            'name' => $customerName,
            'email' => $customerEmail,
        ];

        if ($cpf) {
            $customer['tax_id'] = preg_replace('/[^0-9]/', '', $cpf);
        }

        if ($phone) {
            $digits = preg_replace('/[^0-9]/', '', $phone);
            if (strlen($digits) >= 10) {
                $customer['phones'] = [
                    [
                        'country' => '55',
                        'area' => substr($digits, 0, 2),
                        'number' => substr($digits, 2),
                        'type' => strlen($digits) === 11 ? 'MOBILE' : 'HOME',
                    ]
                ];
            }
        }

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
            'customer' => $customer,
        ];

        // Se tiver endereço, tenta mapear para o PagBank (opcional)
        if ($address) {
            $parts = array_map('trim', explode(',', $address));
            if (count($parts) >= 1) {
                $checkoutData['shipping'] = [
                    'address' => [
                        'street' => $parts[0],
                        'number' => $parts[1] ?? 'SN',
                        'locality' => $parts[2] ?? '',
                        'city' => count($parts) > 3 ? explode('-', $parts[3])[0] : '',
                        'region_code' => count($parts) > 3 && strpos($parts[3], '-') !== false ? trim(explode('-', $parts[3])[1]) : 'SP',
                        'country' => 'BRA',
                        'postal_code' => $parts[count($parts)-1] && strpos($parts[count($parts)-1], 'CEP:') !== false 
                            ? preg_replace('/[^0-9]/', '', $parts[count($parts)-1]) 
                            : '00000000',
                    ]
                ];
            }
        }

        return $this->createCheckout($checkoutData);
    }
}
