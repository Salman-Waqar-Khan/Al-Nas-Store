<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('phone', 30);
            $table->text('address');
            $table->string('delivery_area', 20);
            $table->json('items');
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('delivery_fee');
            $table->unsignedInteger('total');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('orders'); }
};
