<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = ['public_id', 'order_number', 'customer_name', 'phone', 'address', 'delivery_area', 'items', 'subtotal', 'delivery_fee', 'total', 'status', 'stock_deducted_at', 'stock_restored_at'];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->public_id ??= (string) Str::uuid();
        });
    }

    protected function casts(): array
    {
        return ['items' => 'array', 'subtotal' => 'integer', 'delivery_fee' => 'integer', 'total' => 'integer', 'stock_deducted_at' => 'datetime', 'stock_restored_at' => 'datetime'];
    }
}
