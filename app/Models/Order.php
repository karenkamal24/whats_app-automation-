<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customer_phone',
        'customer_name',
        'payment_method',
        'status',
        'amount',
        'quantity',
        'governorate',
        'city',
        'street',
        'notes',
        'payment_reference',
    ];

    protected function casts(): array
    {
        return [
            'amount'         => 'decimal:2',
            'quantity'       => 'integer',
            'status'         => OrderStatus::class,
            'payment_method' => PaymentMethod::class,
        ];
    }

   

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }



    public function scopeByPhone($query, string $phone)
    {
        return $query->where('customer_phone', $phone);
    }

    public function scopeByStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status->value);
    }



    public function isPaid(): bool
    {
        return $this->status === OrderStatus::PAID;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            OrderStatus::PENDING,
            OrderStatus::PENDING_PAYMENT,
        ]);
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }
}



