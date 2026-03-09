<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WhatsAppWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone'   => ['required', 'string', 'max:20'],
            'message' => ['required', 'string', 'max:500'],
            'intent'  => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required'   => 'Customer phone number is required.',
            'message.required' => 'Message body is required.',
        ];
    }
}






