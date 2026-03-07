<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case VISA = 'visa';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'كاش',
            self::VISA => 'فيزا',
        };
    }
}
