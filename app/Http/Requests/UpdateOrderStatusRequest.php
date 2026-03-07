<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                new Enum(OrderStatus::class),
                function ($attribute, $value, $fail) {
                    $status = OrderStatus::from($value);
                    if (! in_array($status, OrderStatus::adminAllowed())) {
                        $allowed = implode(', ', array_map(
                            fn($s) => $s->value,
                            OrderStatus::adminAllowed()
                        ));
                        $fail("الحالة غير مسموح بها. الحالات المتاحة: {$allowed}");
                    }
                },
            ],
        ];
    }
}
