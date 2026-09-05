<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'size', 'notes', 'description', 'price', 'old_price', 'stock', 'image', 'featured', 'active'];
    protected function casts(): array { return ['featured' => 'boolean', 'active' => 'boolean']; }
    public function category() { return $this->belongsTo(Category::class); }
    public function variants() { return $this->hasMany(ProductVariant::class)->orderByRaw('CAST(label AS DECIMAL(8,2))')->orderBy('id'); }
    public function activeVariants() { return $this->hasMany(ProductVariant::class)->where('active', true)->orderByRaw('CAST(label AS DECIMAL(8,2))')->orderBy('id'); }
    public function getStockAttribute($value): int
    {
        if ($this->relationLoaded('variants') && $this->variants->isNotEmpty()) {
            return (int) $this->variants->sum('stock');
        }

        return (int) $value;
    }
    public function supportsVariants(): bool { return (bool) preg_match('/attar|perfume/i', (string) $this->category?->name); }
}
