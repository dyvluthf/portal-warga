<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $baseUrl;
    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key');
        $this->isProduction = config('services.midtrans.is_production', false);
        $this->baseUrl = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    public function createQrisCharge(int $orderId, string $nama, int $amount, array $bulanList): ?array
    {
        $orderIdStr = "IURAN-{$orderId}-" . time();

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/charge", [
                    'payment_type' => 'qris',
                    'transaction_details' => [
                        'order_id' => $orderIdStr,
                        'gross_amount' => $amount,
                    ],
                    'customer_details' => [
                        'first_name' => $nama,
                    ],
                    'item_details' => array_map(fn($b) => [
                        'id' => "iuran-{$b}",
                        'price' => $amount / count($bulanList),
                        'quantity' => 1,
                        'name' => "Iuran bulan {$b}",
                    ], $bulanList),
                ]);

            if ($response->successful()) {
                $body = $response->json();
                return [
                    'transaction_id' => $body['transaction_id'] ?? null,
                    'order_id' => $orderIdStr,
                    'payment_url' => null,
                    'qr_string' => $body['actions'][0]['url'] ?? null,
                    'qr_image' => $body['qr_string'] ?? null,
                ];
            }

            Log::error('Midtrans charge failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans exception: ' . $e->getMessage());
            return null;
        }
    }

    public function verifyCallback(array $payload): ?array
    {
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $serverKey = $this->serverKey;

        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($signature !== ($payload['signature_key'] ?? '')) {
            Log::warning('Midtrans callback signature mismatch');
            return null;
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus = $payload['fraud_status'] ?? '';

        $isSuccess = in_array($transactionStatus, ['capture', 'settlement'])
            && $fraudStatus === 'accept';

        return [
            'order_id' => $orderId,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'transaction_status' => $transactionStatus,
            'is_success' => $isSuccess,
        ];
    }
}
