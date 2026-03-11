<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePaymobPaymentRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymobService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymobController extends Controller
{
    public function __construct(
        private readonly PaymobService $paymobService,
        private readonly OrderService  $orderService,
    ) {}

  
    public function create(CreatePaymobPaymentRequest $request): JsonResponse
    {
        try {
            $order = Order::with('product')->findOrFail($request->validated('order_id'));

            if ($order->payment_method !== PaymentMethod::VISA) {
                return ApiResponse::badRequest('This order does not require online payment.');
            }

            if ($order->isPaid()) {
                return ApiResponse::badRequest('This order is already paid.');
            }

            $iframeUrl = $this->paymobService->createPaymentUrl($order);

            return ApiResponse::success(
                message: 'Payment URL generated successfully.',
                data: ['iframe_url' => $iframeUrl],
            );
        } catch (\RuntimeException $e) {
            Log::error('Paymob create payment failed', [
                'order_id' => $request->validated('order_id'),
                'error'    => $e->getMessage(),
            ]);

            return ApiResponse::error('Failed to create payment session.');
        }
    }


    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Paymob webhook received', [
            'method'       => $request->method(),
            'has_obj'      => isset($payload['obj']),
            'query_params' => array_keys($request->query()),
        ]);

        $receivedHmac = $request->query('hmac', $request->header('hmac', ''));
        $transaction  = data_get($payload, 'obj', $payload);

        if (! $this->paymobService->verifyHmac($transaction, $receivedHmac)) {
            Log::warning('Paymob webhook invalid signature');
            return ApiResponse::forbidden('Invalid signature.');
        }

        $success       = filter_var(data_get($transaction, 'success', false), FILTER_VALIDATE_BOOLEAN);
        $transactionId = data_get($transaction, 'id');
        $paymobOrderId = data_get($transaction, 'order.id');

        Log::info('Paymob webhook parsed', [
            'success'         => $success,
            'transaction_id'  => $transactionId,
            'paymob_order_id' => $paymobOrderId,
        ]);

        if (! $paymobOrderId) {
            Log::warning('Paymob webhook missing order id');
            return ApiResponse::badRequest('Missing order ID.');
        }

        $order = Order::with('product')
            ->where('payment_reference', $paymobOrderId)
            ->first();

        if (! $order) {
            Log::warning('Paymob webhook order not found', [
                'paymob_order_id' => $paymobOrderId,
            ]);
            return ApiResponse::notFound('Order not found.');
        }

        if ($success && ! $order->isPaid()) {

            $this->orderService->markAsPaid($order, (string) $transactionId);

            Log::info('Paymob webhook order marked as paid', [
                'order_id' => $order->id,
            ]);
        }

        return ApiResponse::success('Webhook processed.');
    }

    public function verify(Request $request, Order $order): JsonResponse
    {
        if ($order->isPaid()) {
            return ApiResponse::success('Order is already paid.', [
                'order_id' => $order->id,
                'status'   => $order->status->value,
            ]);
        }

        if ($order->payment_method !== PaymentMethod::VISA) {
            return ApiResponse::badRequest('This is not an online payment order.');
        }

        $transactionId = $request->query('transaction_id', $order->payment_reference);

        if (! $transactionId) {
            return ApiResponse::badRequest(
                'No transaction ID provided. Pass ?transaction_id=XXX from Paymob dashboard.'
            );
        }

        $txn = $this->paymobService->getTransaction((int) $transactionId);

        if (! $txn) {
            return ApiResponse::notFound('Transaction not found on Paymob.');
        }

        $success = filter_var(data_get($txn, 'success', false), FILTER_VALIDATE_BOOLEAN);

        if ($success) {
            $this->orderService->markAsPaid($order, (string) $transactionId);

            return ApiResponse::success('Order payment verified and marked as paid ✅', [
                'order_id'       => $order->id,
                'transaction_id' => $transactionId,
                'status'         => 'paid',
            ]);
        }

        return ApiResponse::success('Transaction found but payment was not successful.', [
            'order_id'       => $order->id,
            'transaction_id' => $transactionId,
            'paymob_success' => $success,
        ]);
    }
}
