<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING         = 'pending';
    case PENDING_PAYMENT = 'pending_payment';
    case PAID            = 'paid';
    case PROCESSING      = 'processing';
    case SHIPPED         = 'shipped';
    case DELIVERED       = 'delivered';
    case CANCELLED       = 'cancelled';

   
    public static function adminAllowed(): array
    {
        return [
            self::PROCESSING,
            self::SHIPPED,
            self::DELIVERED,
            self::CANCELLED,
        ];
    }


    public function label(): string
    {
        return match($this) {
            self::PENDING         => 'قيد الانتظار',
            self::PENDING_PAYMENT => 'في انتظار الدفع',
            self::PAID            => 'تم الدفع',
            self::PROCESSING      => 'قيد التجهيز',
            self::SHIPPED         => 'تم الشحن',
            self::DELIVERED       => 'تم التسليم',
            self::CANCELLED       => 'ملغي',
        };
    }


    public function isActive(): bool
    {
        return ! in_array($this, [
            self::CANCELLED,
            self::DELIVERED,
        ]);
    }
}
