<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * POST /api/v1/orders
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->create($request->validated());

            return ApiResponse::created(
                message: 'Order created successfully.',
                data: new OrderResource($order->load('product')),
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }

    /**
     * PATCH /api/v1/orders/{order}/status
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $updated = $this->orderService->updateStatus(
                $order,
                OrderStatus::from($request->validated('status'))
            );

            return ApiResponse::success(
                message: 'Order status updated successfully.',
                data: new OrderResource($updated->load('product')),
            );
        } catch (\RuntimeException $e) {
            return ApiResponse::badRequest($e->getMessage());
        }
    }
}
