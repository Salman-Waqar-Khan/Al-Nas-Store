<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MySqlDatabaseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_database_price_and_deducts_variant_stock(): void
    {
        $category = Category::create([
            'name' => 'Classic Attar',
            'slug' => 'classic-attar',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Hawas Ice',
            'slug' => 'hawas-ice',
            'price' => 200,
            'stock' => 0,
            'active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'label' => '12ml',
            'price' => 500,
            'stock' => 15,
            'image' => '/images/hawas-ice-12ml.png',
            'active' => true,
        ]);

        $response = $this->post(route('store.checkout'), [
            'name' => 'Test Customer',
            'phone' => '01700000000',
            'address' => 'Chattogram, Bangladesh',
            'area' => 'inside',
            'items' => json_encode([[
                'id' => $product->id,
                'variant_id' => $variant->id,
                'quantity' => 2,
                'price' => 1,
            ]]),
        ]);

        $order = Order::firstOrFail();

        $response->assertRedirect(route('store.success', $order));
        $this->assertSame(1000, $order->subtotal);
        $this->assertSame(1070, $order->total);
        $this->assertSame(13, $variant->fresh()->stock);
        $this->assertSame('12ml', $order->items[0]['size']);
    }

    public function test_category_deletion_is_restricted_when_products_exist(): void
    {
        $category = Category::create(['name' => 'Perfume', 'slug' => 'perfume']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Perfume',
            'slug' => 'test-perfume',
            'price' => 300,
            'stock' => 0,
            'active' => true,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'label' => '6ml',
            'price' => 300,
            'stock' => 5,
            'image' => '/images/test-perfume.png',
            'active' => true,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $category->delete();
    }

    public function test_product_deletion_cascades_to_variants(): void
    {
        $category = Category::create(['name' => 'Perfume', 'slug' => 'perfume']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Perfume',
            'slug' => 'test-perfume',
            'price' => 300,
            'stock' => 0,
            'active' => true,
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'label' => '6ml',
            'price' => 300,
            'stock' => 5,
            'image' => '/images/test-perfume.png',
            'active' => true,
        ]);

        $product->delete();
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }
}
