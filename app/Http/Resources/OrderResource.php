<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'status'          => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'payment_method'  => [
                'value' => $this->payment_method->value,
                'label' => $this->payment_method->label(),
            ],
            'customer'        => [
                'name'  => $this->customer_name,
                'phone' => $this->customer_phone,
            ],
            'shipping'        => [
                'governorate' => $this->governorate,
                'city'        => $this->city,
                'street'      => $this->street,
            ],
            'product'         => $this->whenLoaded('product', fn() => [
                'id'    => $this->product->id,
                'name'  => $this->product->name,
                'price' => $this->product->price,
            ]),
            'amount'          => $this->amount,
            'quantity'        => $this->quantity,
            'notes'           => $this->notes,
            'payment_reference' => $this->payment_reference,
            'created_at'      => $this->created_at?->toDateTimeString(),
            'updated_at'      => $this->updated_at?->toDateTimeString(),
        ];
    }
}
