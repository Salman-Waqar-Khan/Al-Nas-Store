<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sizes for {{ $product->name }} | Al-Nas Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
<header class="border-b bg-white"><div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-5"><div><p class="text-xs font-bold uppercase tracking-[.2em] text-amber-700">{{ $product->category->name }}</p><h1 class="font-display text-3xl font-semibold">{{ $product->name }} sizes</h1></div><a href="{{ route('admin.index') }}" class="rounded-full border border-stone-300 px-4 py-2 text-sm font-bold">← Dashboard</a></div></header>
<main class="mx-auto grid max-w-6xl gap-6 px-5 py-8 lg:grid-cols-[360px_1fr]">
    <section class="h-fit rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="font-display text-3xl font-semibold">Add size</h2>
        <p class="mt-2 text-sm text-stone-500">Each size has its own price, stock and photo.</p>
        @if($errors->any())<div class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.products.variants.store',$product) }}" class="mt-5 space-y-4">@csrf
            <label class="block text-sm font-semibold">Size label<input name="label" required value="{{ old('label') }}" placeholder="e.g. 3.5ml" class="mt-2 w-full rounded-xl border px-4 py-3 font-normal"></label>
            <div class="grid grid-cols-2 gap-3"><label class="text-sm font-semibold">Price<input type="number" name="price" required min="0" value="{{ old('price') }}" class="mt-2 w-full rounded-xl border px-3 py-3 font-normal"></label><label class="text-sm font-semibold">Old price<input type="number" name="old_price" min="0" value="{{ old('old_price') }}" class="mt-2 w-full rounded-xl border px-3 py-3 font-normal"></label></div>
            <label class="block text-sm font-semibold">Stock<input type="number" name="stock" required min="0" value="{{ old('stock',10) }}" class="mt-2 w-full rounded-xl border px-3 py-3 font-normal"></label>
            <label class="block rounded-xl border border-dashed p-3 text-sm font-semibold">Size-specific photo<input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-xs font-normal"></label>
            <label class="block text-sm font-semibold">Or image URL<input type="url" name="image" value="{{ old('image') }}" class="mt-2 w-full rounded-xl border px-4 py-3 font-normal"></label>
            <label class="block text-sm"><input type="checkbox" name="active" value="1" checked> Available to customers</label>
            <button class="w-full rounded-full bg-[#081426] py-3 font-bold text-white">Add size variant</button>
        </form>
    </section>
    <section>
        @if(session('status'))<div class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
        <div class="grid gap-5 sm:grid-cols-2">
        @forelse($product->variants as $variant)
            <article class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <img src="{{ $variant->image }}" alt="{{ $product->name }} {{ $variant->label }}" class="h-52 w-full object-cover">
                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.products.variants.update',[$product,$variant]) }}" class="grid gap-3 p-5">@csrf @method('PUT')
                    <div class="flex items-center justify-between"><h3 class="font-display text-2xl font-semibold">{{ $variant->label }}</h3><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $variant->active ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-500' }}">{{ $variant->active ? 'Active' : 'Hidden' }}</span></div>
                    <input name="label" required value="{{ $variant->label }}" class="rounded-xl border px-3 py-2">
                    <div class="grid grid-cols-2 gap-3"><input type="number" name="price" required min="0" value="{{ $variant->price }}" aria-label="Price" class="rounded-xl border px-3 py-2"><input type="number" name="old_price" min="0" value="{{ $variant->old_price }}" aria-label="Old price" class="rounded-xl border px-3 py-2"></div>
                    <label class="text-xs font-bold">Stock<input type="number" name="stock" required min="0" value="{{ $variant->stock }}" class="mt-1 w-full rounded-xl border px-3 py-2 font-normal"></label>
                    <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="rounded-xl border border-dashed p-2 text-xs">
                    <input type="url" name="image" value="{{ str_starts_with($variant->image,'http') ? $variant->image : '' }}" placeholder="Replacement image URL" class="rounded-xl border px-3 py-2">
                    <label class="text-sm"><input type="checkbox" name="active" value="1" @checked($variant->active)> Available to customers</label>
                    <button class="rounded-full bg-[#081426] py-2.5 text-sm font-bold text-white">Save changes</button>
                </form>
                <form method="POST" action="{{ route('admin.products.variants.destroy',[$product,$variant]) }}" class="px-5 pb-5">@csrf @method('DELETE')<button class="w-full rounded-full border border-red-200 py-2 text-sm font-bold text-red-700">Delete size</button></form>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-10 text-center text-stone-500 sm:col-span-2">No size variants yet. Add 3.5ml, 6ml or 12ml from the form.</div>
        @endforelse
        </div>
    </section>
</main>
</body></html>
