<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    private string $baseUrl;
    private string $secretKey;
    private string $integrationId;
    private string $iframeId;
    private string $hmacSecret;
    private string $currency;

    public function __construct()
    {
        $this->baseUrl       = config('paymob.base_url', 'https://accept.paymob.com');
        $this->secretKey     = config('paymob.secret_key') ?? '';
        $this->integrationId = config('paymob.integration_id') ?? '';
        $this->iframeId      = config('paymob.iframe_id') ?? '';
        $this->hmacSecret    = config('paymob.hmac_secret') ?? '';
        $this->currency      = config('paymob.currency', 'EGP');
    }

    public function createPaymentUrl(Order $order): string
    {
        return $this->createPaymentViaIntention($order);
    }

    private function createPaymentViaIntention(Order $order): string
    {
        $amountCents = (int) round($order->amount * 100);

        $payload = [
            'amount'           => $amountCents,
            'currency'         => $this->currency,
            'payment_methods'  => [(int) $this->integrationId],

            /*
            |----------------------------------------------------
            | ⭐ الإصلاح: special_reference بدل extras
            | لأن extras مش بتتبعت صح في الـ intention API
            |----------------------------------------------------
            */
            'special_reference' => (string) $order->id,

            'billing_data' => [
                'first_name'      => 'WhatsApp',
                'last_name'       => 'Customer',
                'email'           => 'customer@shop.com',
                'phone_number'    => $order->customer_phone,
                'apartment'       => 'N/A',
                'floor'           => 'N/A',
                'street'          => 'N/A',
                'building'        => 'N/A',
                'shipping_method' => 'N/A',
                'postal_code'     => 'N/A',
                'city'            => 'N/A',
                'country'         => 'EG',
                'state'           => 'N/A',
            ],

            'items' => [
                [
                    'name'        => $order->product->name ?? 'Product',
                    'amount'      => $amountCents,
                    'quantity'    => 1,
                    'description' => "Order #{$order->id}",
                ],
            ],
        ];

        $response = Http::withToken($this->secretKey)
            ->post("{$this->baseUrl}/v1/intention/", $payload);

        if ($response->failed()) {
            Log::error('Paymob intention failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \RuntimeException('Failed to create Paymob payment.');
        }

        $data = $response->json();

        /*
        |----------------------------------------------------
        | ⭐ الإصلاح: intention_order_id هو الـ ID الصح
        | اللي بييجي في الـ webhook كـ obj.order.id
        |----------------------------------------------------
        */
        $paymobOrderId = $data['intention_order_id'] ?? null;

        Log::info('Paymob intention response', [
            'intention_id'    => $data['id'] ?? null,
            'paymob_order_id' => $paymobOrderId,
        ]);

        if ($paymobOrderId) {
            $order->update([
                'payment_reference' => $paymobOrderId,
            ]);
        }

        $clientSecret = $data['client_secret'] ?? null;

        if (! $clientSecret) {
            Log::error('Paymob: Missing client_secret', $data);
            throw new \RuntimeException('Invalid Paymob response.');
        }

        $publicKey = config('paymob.public_key');

        return "{$this->baseUrl}/unifiedcheckout/?publicKey={$publicKey}&clientSecret={$clientSecret}";
    }

    public function verifyHmac(array $data, string $receivedHmac): bool
    {
        if (empty($this->hmacSecret)) {
            return true;
        }

        $fields = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order.id',
            'owner',
            'pending',
            'source_data.pan',
            'source_data.sub_type',
            'source_data.type',
            'success',
        ];

        $concatenated = '';

        foreach ($fields as $field) {
            $value = data_get($data, $field, '');

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $concatenated .= $value;
        }

        $calculatedHmac = hash_hmac('sha512', $concatenated, $this->hmacSecret);

        return hash_equals($calculatedHmac, $receivedHmac);
    }

    public function getTransaction(int $transactionId): ?array
    {
        $response = Http::withToken($this->secretKey)
            ->get("{$this->baseUrl}/api/acceptance/transactions/{$transactionId}");

        if ($response->failed()) {
            Log::error('Paymob getTransaction failed', [
                'transaction_id' => $transactionId,
                'status'         => $response->status(),
            ]);

            return null;
        }

        return $response->json();
    }
}
