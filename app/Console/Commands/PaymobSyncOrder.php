<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymobService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Console\Command;

class PaymobSyncOrder extends Command
{
    protected $signature = 'paymob:sync
        {transaction_id : The Paymob transaction ID from the dashboard}
        {--order= : Our internal order ID (optional, will auto-detect from Paymob)}
        {--notify : Send WhatsApp notification even if order is already paid}';

    protected $description = 'Manually verify a Paymob transaction and update the order status';

    public function __construct(
        private readonly PaymobService $paymobService,
        private readonly OrderService  $orderService,
        private readonly WhatsAppNotificationService $whatsAppNotifier,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $transactionId = (int) $this->argument('transaction_id');

        $this->info("🔍 Fetching transaction #{$transactionId} from Paymob...");

        $txn = $this->paymobService->getTransaction($transactionId);

        if (! $txn) {
            $this->error('❌ Could not retrieve transaction from Paymob.');
            return self::FAILURE;
        }

        $success  = filter_var(data_get($txn, 'success', false), FILTER_VALIDATE_BOOLEAN);
        $amount   = data_get($txn, 'amount_cents', 0) / 100;
        $currency = data_get($txn, 'currency', 'EGP');

        $this->table(
            ['Field', 'Value'],
            [
                ['Transaction ID', $transactionId],
                ['Amount', "{$currency} {$amount}"],
                ['Success', $success ? '✅ Yes' : '❌ No'],
                ['Created At', data_get($txn, 'created_at', 'N/A')],
            ]
        );

        if (! $success) {
            $this->warn('⚠️  Transaction was not successful. No order updated.');
            return self::SUCCESS;
        }

        // Find the order
        $orderId = $this->option('order');

        if (! $orderId) {
            // Try to extract from the transaction
            $orderId = $this->paymobService->extractOrderId($txn);
        }

        if (! $orderId) {
            $this->warn('Could not auto-detect order ID from transaction.');
            $orderId = $this->ask('Enter the internal order ID manually');
        }

        if (! $orderId) {
            $this->error('❌ No order ID provided.');
            return self::FAILURE;
        }

        $order = Order::find($orderId);

        if (! $order) {
            $this->error("❌ Order #{$orderId} not found.");
            return self::FAILURE;
        }

        $this->info("📦 Order #{$order->id} — Status: {$order->status}");

        if ($order->isPaid()) {
            $this->info('✅ Order is already marked as paid.');

            if ($this->option('notify')) {
                $order->loadMissing('product');
                $this->whatsAppNotifier->sendOrderPaid(
                    phone:       $order->customer_phone,
                    orderId:     $order->id,
                    amount:      (float) $order->amount,
                    productName: $order->product?->name ?? 'منتج',
                );
                $this->info('📨 Notification sent (notify flag).');
            } else {
                $this->info('Nothing to do.');
            }
            return self::SUCCESS;
        }

        if ($this->confirm("Mark order #{$order->id} as paid?", true)) {
            $this->orderService->markAsPaid($order, (string) $transactionId);
            $this->info("✅ Order #{$order->id} marked as paid! (ref: {$transactionId})");

            $order->loadMissing('product');
            $this->whatsAppNotifier->sendOrderPaid(
                phone:       $order->customer_phone,
                orderId:     $order->id,
                amount:      (float) $order->amount,
                productName: $order->product?->name ?? 'منتج',
            );
            $this->info('📨 Notification sent.');
        }

        return self::SUCCESS;
    }
}
