<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'activeVariants'])->where('active', true)->orderByDesc('featured')->latest()->get()->map(fn ($p) => [
            'id' => $p->id, 'name' => $p->name, 'slug' => $p->slug, 'category' => $p->category->name, 'size' => $p->size, 'notes' => $p->notes,
            'price' => $p->activeVariants->min('price') ?? $p->price, 'old_price' => $p->activeVariants->isNotEmpty() ? null : $p->old_price,
            'price_min' => $p->activeVariants->min('price') ?? $p->price,
            'price_max' => $p->activeVariants->max('price') ?? $p->price,
            'stock' => $p->activeVariants->isNotEmpty() ? $p->activeVariants->sum('stock') : $p->stock,
            'image' => $p->activeVariants->first()?->image ?: ($p->image ?: 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&w=900&q=85'),
            'has_variants' => $p->activeVariants->isNotEmpty(),
            'variants' => $p->activeVariants->map(fn ($variant) => ['id' => $variant->id, 'label' => $variant->label, 'price' => $variant->price, 'old_price' => $variant->old_price, 'stock' => $variant->stock, 'image' => $variant->image])->values()->all(),
        ])->values()->all();
        $settings = StoreSetting::current();
        return view('store', compact('products', 'settings'));
    }

    public function product(Product $product)
    {
        abort_unless($product->active, 404);
        $product->load(['category', 'activeVariants']);
        $storeProducts = [[
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->activeVariants->min('price') ?? $product->price,
            'price_min' => $product->activeVariants->min('price') ?? $product->price,
            'price_max' => $product->activeVariants->max('price') ?? $product->price,
            'stock' => $product->activeVariants->isNotEmpty() ? $product->activeVariants->sum('stock') : $product->stock,
            'image' => $product->activeVariants->first()?->image ?: $product->image,
            'has_variants' => $product->activeVariants->isNotEmpty(),
            'variants' => $product->activeVariants->map(fn ($variant) => ['id' => $variant->id, 'label' => $variant->label, 'price' => $variant->price, 'old_price' => $variant->old_price, 'stock' => $variant->stock, 'image' => $variant->image])->values()->all(),
        ]];
        return view('product', compact('product', 'storeProducts'));
    }

    public function trackForm()
    {
        return view('track-order');
    }

    public function track(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:30'],
        ]);

        $order = Order::where('order_number', strtoupper(trim($data['order_number'])))
            ->where('phone', trim($data['phone']))
            ->first();

        if (! $order) {
            return back()->withInput()->withErrors(['order_number' => 'We could not find an order matching those details.']);
        }

        return view('track-order', compact('order'));
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:500'],
            'area' => ['required', 'in:inside,outside'],
            'items' => ['required', 'json'],
        ]);

        $requested = collect(json_decode($data['items'], true))
            ->groupBy(fn ($item) => ((int) ($item['id'] ?? 0)).':'.((int) ($item['variant_id'] ?? 0)))
            ->map(function ($group) {
                $first = $group->first();
                return ['id' => (int) ($first['id'] ?? 0), 'variant_id' => (int) ($first['variant_id'] ?? 0) ?: null, 'quantity' => $group->sum(fn ($item) => (int) ($item['quantity'] ?? 0))];
            })
            ->values();
        if ($requested->isEmpty() || $requested->contains(fn ($item) => $item['id'] < 1 || $item['quantity'] < 1 || $item['quantity'] > 10)) {
            throw ValidationException::withMessages(['items' => 'Your cart contains an invalid quantity.']);
        }

        $order = DB::transaction(function () use ($requested, $data) {
            $catalog = Product::with(['category', 'activeVariants'])->whereIn('id', $requested->pluck('id'))->lockForUpdate()->get()->keyBy('id');
            $variantIds = $requested->pluck('variant_id')->filter()->unique();
            $variants = ProductVariant::whereIn('id', $variantIds)->lockForUpdate()->get()->keyBy('id');
            if ($catalog->count() !== $requested->count()) {
                throw ValidationException::withMessages(['items' => 'One or more products are no longer available.']);
            }
            $items = $requested->map(function ($item) use ($catalog, $variants) {
                $product = $catalog->get($item['id']);
                $hasVariants = $product->activeVariants->isNotEmpty();
                if ($hasVariants) {
                    $variant = $item['variant_id'] ? $variants->get($item['variant_id']) : null;
                    if (! $product->active || ! $variant || ! $variant->active || $variant->product_id !== $product->id || $variant->stock < $item['quantity']) {
                        throw ValidationException::withMessages(['items' => "{$product->name} size is unavailable or does not have enough stock."]);
                    }
                    return ['id' => $product->id, 'variant_id' => $variant->id, 'name' => $product->name, 'size' => $variant->label, 'price' => $variant->price, 'quantity' => $item['quantity']];
                }
                if ($item['variant_id'] || ! $product->active || $product->stock < $item['quantity']) {
                    throw ValidationException::withMessages(['items' => "{$product->name} does not have enough stock."]);
                }
                return ['id' => $product->id, 'variant_id' => null, 'name' => $product->name, 'size' => $product->size, 'price' => $product->price, 'quantity' => $item['quantity']];
            });
            foreach ($items as $item) {
                $item['variant_id'] ? $variants->get($item['variant_id'])->decrement('stock', $item['quantity']) : $catalog->get($item['id'])->decrement('stock', $item['quantity']);
            }
            $subtotal = $items->sum(fn ($item) => $item['price'] * $item['quantity']);
            $delivery = $data['area'] === 'inside' ? 70 : 130;
            return Order::create([
                'order_number' => 'YS-'.strtoupper(Str::random(8)), 'customer_name' => trim($data['name']), 'phone' => trim($data['phone']),
                'address' => trim($data['address']), 'delivery_area' => $data['area'], 'items' => $items->values(), 'subtotal' => $subtotal,
                'delivery_fee' => $delivery, 'total' => $subtotal + $delivery, 'stock_deducted_at' => now(),
            ]);
        }, 3);

        return redirect()->route('store.success', ['order' => $order->public_id]);
    }

    public function success(Order $order)
    {
        return view('success', compact('order'));
    }
}
