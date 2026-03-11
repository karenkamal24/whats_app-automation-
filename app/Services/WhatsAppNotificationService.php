<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    private string $webhookUrl;

    public function __construct()
    {
        $this->webhookUrl = config(
            'services.whatsapp.webhook_url',
            'https://n8n.softigital.com/webhook/send-whatsapp'
        );
    }

    public function sendStatusUpdate(Order $order): void
    {
        $productName = $order->product?->name ?? 'منتجك';

        $message = match ($order->status) {
            OrderStatus::PAID        => $this->paidMessage($order, $productName),
            OrderStatus::PROCESSING  => $this->processingMessage($order, $productName),
            OrderStatus::SHIPPED     => $this->shippedMessage($order, $productName),
            OrderStatus::DELIVERED   => $this->deliveredMessage($order, $productName),
            OrderStatus::CANCELLED   => $this->cancelledMessage($order, $productName),
            default                  => null,
        };

        if ($message) {
            $this->send($order->customer_phone, $message);
        }
    }


    private function paidMessage(Order $order, string $productName): string
    {
        return
            "✅ تم استلام دفعتك بنجاح! 🎉\n\n" .
            "🧾 رقم الطلب: #{$order->id}\n" .
            "📦 المنتج: {$productName}\n" .
            "💰 المبلغ: {$order->amount} EGP\n\n" .
            "⏳ طلبك قيد المراجعة.\n" .
            "هنبعتلك تحديث لما يبدأ التجهيز 🚀";
    }

    private function processingMessage(Order $order, string $productName): string
    {
        return
            "⚙️ طلبك بدأ يتجهز! 📦\n\n" .
            "🧾 رقم الطلب: #{$order->id}\n" .
            "📦 المنتج: {$productName}\n\n" .
            "فريقنا شغال على طلبك دلوقتي.\n" .
            "هنبعتلك تحديث لما يتشحن 🚚";
    }

    private function shippedMessage(Order $order, string $productName): string
    {
        return
            "🚚 طلبك اتشحن وفي الطريق إليك! 🎉\n\n" .
            "🧾 رقم الطلب: #{$order->id}\n" .
            "📦 المنتج: {$productName}\n\n" .
            "متوقع يوصلك خلال يومين أو 3 أيام عمل.\n" .
            "لو عندك أي استفسار تواصل معانا 💬";
    }

    private function deliveredMessage(Order $order, string $productName): string
    {
        return
            "🎊 تم توصيل طلبك بنجاح!\n\n" .
            "🧾 رقم الطلب: #{$order->id}\n" .
            "📦 المنتج: {$productName}\n\n" .
            "نتمنى تكون راضي عن طلبك 😊\n" .
            "لو عايز تطلب تاني، إحنا هنا! 💙";
    }

    private function cancelledMessage(Order $order, string $productName): string
    {
        return
            "❌ تم إلغاء طلبك.\n\n" .
            "🧾 رقم الطلب: #{$order->id}\n" .
            "📦 المنتج: {$productName}\n\n" .
            "لو في أي مشكلة تواصل معانا 💬";
    }



    private function send(string $phone, string $message): void
    {
        try {
            Http::post($this->webhookUrl, [
                'phone'   => $phone,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            Log::error('WhatsApp notification failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

   
    public function sendMessage(string $phone, string $message): void
    {
        $this->send($phone, $message);
    }
}
