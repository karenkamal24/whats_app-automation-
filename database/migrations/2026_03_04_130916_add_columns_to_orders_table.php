<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('customer_name')->nullable()->after('customer_phone');
        $table->unsignedInteger('quantity')->default(1)->after('amount');
        $table->string('governorate')->nullable()->after('quantity');
        $table->string('city')->nullable()->after('governorate');
        $table->string('street')->nullable()->after('city');
        $table->text('notes')->nullable()->after('street');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
