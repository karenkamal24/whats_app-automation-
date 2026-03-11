<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WhatsAppWebhookRequest;
use App\Services\WebhookService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService,
    ) {}

   
    public function __invoke(WhatsAppWebhookRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->webhookService->handle(
            phone:   $validated['phone'],
            message: $validated['message'],
            intent:  $validated['intent'] ?? null,
        );

        return ApiResponse::success(
            message: 'Webhook processed.',
            data:    $result,
        );
    }
}




