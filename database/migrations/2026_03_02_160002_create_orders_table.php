<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('customer_phone', 20);
            $table->enum('payment_method', ['cash', 'visa']);
            $table->enum('status', ['pending', 'pending_payment', 'paid', 'cancelled'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->index('customer_phone');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};






