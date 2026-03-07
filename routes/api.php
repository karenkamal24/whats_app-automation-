<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymobController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WhatsAppWebhookController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::get('/products/search', [ProductController::class, 'search']);

    Route::post('/orders', [OrderController::class, 'store']);

    Route::post('/paymob/create', [PaymobController::class, 'create']);

    // Paymob callback — accept both GET and POST (Paymob may use either)
    Route::match(['get', 'post'], '/paymob/webhook', [PaymobController::class, 'webhook']);

    // Manual payment verification
    Route::get('/paymob/verify/{order}', [PaymobController::class, 'verify']);

    // WhatsApp conversational webhook (called by n8n)
    Route::post('/whatsapp/webhook', WhatsAppWebhookController::class)
        ->name('whatsapp.webhook');
});
