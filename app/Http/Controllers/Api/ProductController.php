<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

 
    public function search(Request $request): JsonResponse
    {
        $name = trim($request->query('name', ''));

        if ($name === '') {
            return ApiResponse::error(
                message: 'Search term is required.',
                status: 422
            );
        }

        $products = $this->productService->search($name);

        return ApiResponse::success(
            message: $products->isEmpty() ? 'No products found.' : 'Products found.',
            data: ProductResource::collection($products),
        );
    }
}
