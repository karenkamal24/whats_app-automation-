<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where('is_active', true),
            ],
            'customer_phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^\+?[0-9]{10,15}$/',
            ],
            'payment_method' => [
                'required',
                'string',
                Rule::in([Order::PAYMENT_CASH, Order::PAYMENT_VISA]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists'         => 'The selected product is not available.',
            'customer_phone.regex'      => 'Phone number must be a valid international format.',
            'payment_method.in'         => 'Payment method must be either cash or visa.',
        ];
    }
}



