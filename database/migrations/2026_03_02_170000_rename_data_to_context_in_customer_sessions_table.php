<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_sessions', function (Blueprint $table) {
            $table->renameColumn('data', 'context');
        });
    }

    public function down(): void
    {
        Schema::table('customer_sessions', function (Blueprint $table) {
            $table->renameColumn('context', 'data');
        });
    }
};



