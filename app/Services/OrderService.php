<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private readonly WhatsAppNotificationService $whatsAppNotifier,
    ) {}

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {

            $quantity = (int) ($data['quantity'] ?? 1);
            $product  = Product::lockForUpdate()->findOrFail($data['product_id']);

            if (! $product->is_active) {
                throw new \RuntimeException('Product is not available.');
            }

            if ($product->stock < $quantity) {
                throw new \RuntimeException("المتاح في المخزن فقط {$product->stock} قطعة.");
            }

            $product->decrement('stock', $quantity);

            $status = $data['payment_method'] === PaymentMethod::VISA->value
                ? OrderStatus::PENDING_PAYMENT
                : OrderStatus::PENDING;

            return Order::create([
                'product_id'      => $product->id,
                'customer_phone'  => $data['customer_phone'],
                'customer_name'   => $data['customer_name']  ?? null,
                'payment_method'  => $data['payment_method'],
                'status'          => $status,
                'amount'          => $product->price * $quantity,
                'quantity'        => $quantity,
                'governorate'     => $data['governorate']    ?? null,
                'city'            => $data['city']           ?? null,
                'street'          => $data['street']         ?? null,
                'notes'           => $data['notes']          ?? null,
            ]);
        });
    }

    public function markAsPaid(Order $order, ?string $paymentReference = null): Order
    {
        if ($order->status === OrderStatus::PAID) {
            return $order;
        }

        return DB::transaction(function () use ($order, $paymentReference) {

            $order->update([
                'status'            => OrderStatus::PAID,
                'payment_reference' => $paymentReference,
            ]);

            $order->load('product');

            $this->whatsAppNotifier->sendStatusUpdate($order->fresh());

            return $order->fresh();
        });
    }

    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        if ($order->status === $newStatus) {
            return $order;
        }

        return DB::transaction(function () use ($order, $newStatus) {

            if ($newStatus === OrderStatus::CANCELLED && $order->isActive()) {
                $order->product()->increment('stock', $order->quantity ?? 1);
            }

            $order->update(['status' => $newStatus]);

            $order->load('product');

            $this->whatsAppNotifier->sendStatusUpdate($order->fresh());

            Log::info('Order status updated', [
                'order_id'   => $order->id,
                'new_status' => $newStatus->value,
            ]);

            return $order->fresh();
        });
    }

    public function cancel(Order $order): Order
    {
        return $this->updateStatus($order, OrderStatus::CANCELLED);
    }
}



