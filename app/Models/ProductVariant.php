<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $fillable = ['product_id', 'label', 'price', 'old_price', 'stock', 'image', 'position', 'active'];

    protected function casts(): array
    {
        return ['price' => 'integer', 'old_price' => 'integer', 'stock' => 'integer', 'position' => 'integer', 'active' => 'boolean'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
