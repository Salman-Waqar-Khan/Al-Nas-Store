<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function login(Request $request) {
        if ($request->user()?->is_admin) return redirect()->route('admin.index');
        return view('admin.login');
    }
    public function authenticate(Request $request) {
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($credentials, false)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'The email or password is incorrect.']);
        }
        if (! $request->user()?->is_admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['email' => 'This account does not have admin access.']);
        }
        $request->session()->regenerate();
        return redirect()->route('admin.index');
    }
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
    public function index() { return view('admin.index', ['products' => Product::with(['category', 'variants'])->latest()->get(), 'categories' => Category::withCount('products')->orderBy('name')->get(), 'orders' => Order::latest()->limit(10)->get(), 'settings' => StoreSetting::current()]); }
    public function orders(Request $request) {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:pending,confirmed,shipped,delivered,cancelled']]);
        $orders = Order::query()
            ->when($validated['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('order_number', 'like', "%{$search}%")->orWhere('customer_name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")))
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()->paginate(15)->withQueryString();
        return view('admin.orders.index', compact('orders'));
    }
    public function showOrder(Order $order) { return view('admin.orders.show', compact('order')); }
    public function updateHomepage(Request $request) {
        $data = $request->validate([
            'fresh_label' => ['required', 'string', 'max:50'],
            'fresh_title' => ['required', 'string', 'max:100'],
            'fresh_image' => ['nullable', 'url', 'max:1000'],
            'fresh_image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        unset($data['fresh_image_file']);
        $settings = StoreSetting::current();
        if ($request->hasFile('fresh_image_file')) {
            if ($settings->fresh_image && str_starts_with($settings->fresh_image, '/storage/')) Storage::disk('public')->delete(str_replace('/storage/', '', $settings->fresh_image));
            $data['fresh_image'] = '/storage/'.$request->file('fresh_image_file')->store('homepage', 'public');
        } elseif (empty($data['fresh_image'])) $data['fresh_image'] = $settings->fresh_image;
        $settings->update($data);
        return back()->with('status', 'Fresh arrival section updated.');
    }
    public function storeCategory(Request $request) {
        $data = $request->validate(['name' => ['required', 'string', 'max:80', 'unique:categories,name']]);
        Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4))]);
        return back()->with('status', 'Category added.');
    }
    public function destroyCategory(Category $category) { if ($category->products()->exists()) return back()->withErrors(['category' => 'Move or delete its products first.']); $category->delete(); return back()->with('status', 'Category deleted.'); }
    private function productData(Request $request, ?Product $product = null): array {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'], 'name' => ['required', 'string', 'max:120'], 'size' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:160'], 'description' => ['nullable', 'string', 'max:1000'], 'price' => ['required', 'integer', 'min:0'],
            'old_price' => ['nullable', 'integer', 'min:0'], 'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'url', 'max:1000'],
            'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        unset($data['image_file']);
        if ($request->hasFile('image_file')) {
            if ($product?->image && str_starts_with($product->image, '/storage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
            }
            $data['image'] = '/storage/'.$request->file('image_file')->store('products', 'public');
        } elseif ($product && empty($data['image'])) {
            $data['image'] = $product->image;
        }
        $data['slug'] = Str::slug($data['name']).'-'.($product?->id ?? Str::lower(Str::random(5)));
        $data['active'] = $request->boolean('active'); $data['featured'] = $request->boolean('featured'); return $data;
    }
    public function storeProduct(Request $request) {
        $variants = collect($request->validate([
            'variants' => ['nullable', 'array', 'max:10'],
            'variants.*.label' => ['nullable', 'string', 'max:30', 'distinct', 'regex:/^\d+(\.\d+)?(?:\s*ml)?$/i'],
            'variants.*.price' => ['nullable', 'integer', 'min:0'],
            'variants.*.old_price' => ['nullable', 'integer', 'min:0'],
            'variants.*.stock' => ['nullable', 'integer', 'min:0'],
            'variants.*.image' => ['nullable', 'url', 'max:1000'],
            'variants.*.image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ])['variants'] ?? [])->filter(fn ($variant) => filled($variant['label'] ?? null));
        foreach ($variants as $index => $variant) {
            if (! isset($variant['price'], $variant['stock']) || (! $request->hasFile("variants.$index.image_file") && empty($variant['image']))) {
                throw ValidationException::withMessages(['variants' => 'Every size needs a label, price, stock and its own image.']);
            }
        }
        $data = $this->productData($request);
        $category = Category::findOrFail($data['category_id']);
        if ($variants->isNotEmpty() && ! preg_match('/attar|perfume/i', $category->name)) {
            throw ValidationException::withMessages(['variants' => 'Size variants are available only for Attar or Perfume categories.']);
        }
        DB::transaction(function () use ($request, $data, $variants) {
            if ($variants->isNotEmpty()) {
                $data['price'] = (int) $variants->min('price');
                $data['stock'] = 0;
                $data['size'] = null;
            }
            $product = Product::create($data);
            foreach ($variants as $index => $variant) {
                if ($request->hasFile("variants.$index.image_file")) {
                    $variant['image'] = '/storage/'.$request->file("variants.$index.image_file")->store('product-variants', 'public');
                }
                $product->variants()->create([
                    'label' => trim($variant['label']),
                    'price' => $variant['price'],
                    'old_price' => $variant['old_price'] ?? null,
                    'stock' => $variant['stock'],
                    'image' => $variant['image'],
                    'position' => (int) round((float) $variant['label'] * 10),
                    'active' => true,
                ]);
            }
        });
        return back()->with('status', $variants->isNotEmpty() ? 'Product and size variants added.' : 'Product added.');
    }
    public function updateProduct(Request $request, Product $product) { $product->update($this->productData($request, $product)); return back()->with('status', 'Product updated.'); }
    public function destroyProduct(Product $product) {
        foreach ($product->variants as $variant) {
            if ($variant->image && str_starts_with($variant->image, '/storage/')) Storage::disk('public')->delete(str_replace('/storage/', '', $variant->image));
        }
        if ($product->image && str_starts_with($product->image, '/storage/')) Storage::disk('public')->delete(str_replace('/storage/', '', $product->image));
        $product->delete(); return back()->with('status', 'Product deleted.');
    }
    public function variants(Product $product) {
        $product->load(['category', 'variants']);
        abort_unless($product->supportsVariants(), 404);
        return view('admin.products.variants', compact('product'));
    }
    private function variantData(Request $request, Product $product, ?ProductVariant $variant = null): array {
        abort_unless($product->supportsVariants(), 404);
        $data = $request->validate([
            'label' => ['required', 'string', 'max:30', 'regex:/^\d+(\.\d+)?(?:\s*ml)?$/i', Rule::unique('product_variants', 'label')->where('product_id', $product->id)->ignore($variant?->id)],
            'price' => ['required', 'integer', 'min:0'],
            'old_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'url', 'max:1000'],
            'image_file' => [$variant ? 'nullable' : 'required_without:image', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);
        unset($data['image_file']);
        if ($request->hasFile('image_file')) {
            if ($variant?->image && str_starts_with($variant->image, '/storage/')) Storage::disk('public')->delete(str_replace('/storage/', '', $variant->image));
            $data['image'] = '/storage/'.$request->file('image_file')->store('product-variants', 'public');
        } elseif ($variant && empty($data['image'])) {
            $data['image'] = $variant->image;
        }
        $data['label'] = trim($data['label']);
        $data['position'] = (int) round((float) $data['label'] * 10);
        $data['active'] = $request->boolean('active');
        return $data;
    }
    public function storeVariant(Request $request, Product $product) {
        $data = $this->variantData($request, $product);
        $product->variants()->create($data);
        return back()->with('status', 'Size variant added.');
    }
    public function updateVariant(Request $request, Product $product, ProductVariant $variant) {
        abort_unless($variant->product_id === $product->id, 404);
        $data = $this->variantData($request, $product, $variant);
        $variant->update($data);
        return back()->with('status', 'Size variant updated.');
    }
    public function destroyVariant(Product $product, ProductVariant $variant) {
        abort_unless($variant->product_id === $product->id, 404);
        if ($variant->image && str_starts_with($variant->image, '/storage/')) Storage::disk('public')->delete(str_replace('/storage/', '', $variant->image));
        $variant->delete();
        return back()->with('status', 'Size variant deleted.');
    }
    public function updateOrderStatus(Request $request, Order $order) {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,shipped,delivered,cancelled'],
        ]);
        DB::transaction(function () use ($order, $data) {
            $lockedOrder = Order::lockForUpdate()->findOrFail($order->id);
            if ($data['status'] === 'cancelled' && $lockedOrder->stock_deducted_at && ! $lockedOrder->stock_restored_at) {
                foreach ($lockedOrder->items as $item) {
                    if (! empty($item['variant_id'])) {
                        ProductVariant::whereKey($item['variant_id'])->lockForUpdate()->first()?->increment('stock', (int) $item['quantity']);
                    } else {
                        Product::whereKey($item['id'])->lockForUpdate()->first()?->increment('stock', (int) $item['quantity']);
                    }
                }
                $lockedOrder->stock_restored_at = now();
            } elseif ($lockedOrder->status === 'cancelled' && $data['status'] !== 'cancelled' && $lockedOrder->stock_restored_at) {
                foreach ($lockedOrder->items as $item) {
                    $stockItem = ! empty($item['variant_id'])
                        ? ProductVariant::whereKey($item['variant_id'])->lockForUpdate()->first()
                        : Product::whereKey($item['id'])->lockForUpdate()->first();
                    if (! $stockItem || $stockItem->stock < (int) $item['quantity']) {
                        throw ValidationException::withMessages(['status' => 'This order cannot be reopened because one or more products have insufficient stock.']);
                    }
                    $stockItem->decrement('stock', (int) $item['quantity']);
                }
                $lockedOrder->stock_restored_at = null;
            }
            $lockedOrder->status = $data['status'];
            $lockedOrder->save();
        }, 3);
        return back()->with('status', "Order {$order->order_number} marked as ".ucfirst($data['status']).'.');
    }
}
