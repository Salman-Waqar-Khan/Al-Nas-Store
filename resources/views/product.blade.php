<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="{{ Str::limit($product->description ?: $product->notes ?: $product->name, 155) }}">
    <title>{{ $product->name }} — {{ config('store.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/al-nas-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="grain antialiased">
@php($hasVariants = $product->activeVariants->isNotEmpty())
<header class="border-b border-black/5 bg-[#f7f5f0]/90"><div class="mx-auto flex h-28 max-w-7xl items-center justify-between px-5 sm:h-32 lg:px-8"><a href="{{ route('store.home') }}" class="rounded-full"><img src="{{ config('store.logo') }}" alt="Al-Nas logo" class="h-24 w-24 rounded-full object-contain shadow-sm ring-1 ring-[#c8a34f]/40 sm:h-28 sm:w-28"></a><div class="flex items-center gap-4"><a href="{{ route('store.home') }}#shop" class="hidden text-sm font-semibold sm:block">Back to shop</a><button data-open-cart class="relative flex items-center gap-2 rounded-full border border-stone-300 px-4 py-2 text-sm font-semibold">Bag <span data-cart-count class="grid h-5 w-5 place-items-center rounded-full bg-[#081426] text-[10px] text-white">0</span></button></div></div></header>
<main class="mx-auto grid max-w-7xl gap-10 px-5 py-10 lg:grid-cols-2 lg:items-center lg:px-8 lg:py-16">
    <div class="aspect-[4/4.7] overflow-hidden rounded-[2rem] bg-stone-100"><img id="product-main-image" src="{{ $product->image ?: $product->activeVariants->first()?->image }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-opacity duration-200"></div>
    <section class="max-w-xl">
        <p class="text-xs font-bold uppercase tracking-[.22em] text-amber-700">{{ $product->category->name }}</p>
        <h1 class="font-display mt-3 text-5xl font-semibold leading-none sm:text-6xl">{{ $product->name }}</h1>
        <div class="mt-5 flex items-baseline gap-3"><b id="product-price" class="text-2xl">@if($hasVariants && $product->activeVariants->min('price') !== $product->activeVariants->max('price'))৳{{ number_format($product->activeVariants->min('price')) }} – ৳{{ number_format($product->activeVariants->max('price')) }}@else৳{{ number_format($hasVariants ? $product->activeVariants->min('price') : $product->price) }}@endif</b><s id="product-old-price" class="hidden text-stone-400"></s></div>
        @if($hasVariants)
            <div class="mt-6"><p id="size-prompt" class="mb-3 text-sm font-bold">Please select a product size</p><div class="flex flex-wrap gap-2">@foreach($product->activeVariants as $variant)<button type="button" data-variant-option data-variant-id="{{ $variant->id }}" data-price="{{ $variant->price }}" data-old-price="{{ $variant->old_price }}" data-stock="{{ $variant->stock }}" data-image="{{ $variant->image }}" class="rounded-full border border-stone-300 px-5 py-2.5 text-sm font-bold transition hover:border-stone-700">{{ $variant->label }}</button>@endforeach</div></div>
        @elseif($product->size)<p class="mt-5 text-sm"><b>Size:</b> {{ $product->size }}</p>@endif
        @if($product->notes)<p class="mt-2 text-sm text-stone-600"><b class="text-[#081426]">Notes:</b> {{ $product->notes }}</p>@endif
        @if($product->description)<p class="mt-6 leading-7 text-stone-600">{{ $product->description }}</p>@endif
        @php($displayStock = $hasVariants ? null : $product->stock)
        <div class="mt-7"><span id="product-stock" class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $hasVariants ? 'bg-amber-100 text-amber-800' : ($displayStock > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') }}">{{ $hasVariants ? 'Select a size to check availability' : ($displayStock > 0 ? 'In stock · '.$displayStock.' available' : 'Out of stock') }}</span></div>
        <div data-product-detail class="mt-7 flex flex-wrap gap-3"><label class="flex items-center gap-2 rounded-full border border-stone-300 px-4 text-sm font-semibold">Qty <input id="product-quantity" type="number" min="1" max="{{ $hasVariants ? 1 : min(10, max(1,$displayStock)) }}" value="1" @disabled($hasVariants) class="w-12 bg-transparent py-3 text-center outline-none disabled:opacity-50"></label><button data-add="{{ $product->id }}" data-quantity-input="#product-quantity" @disabled($hasVariants || $displayStock < 1) class="rounded-full border border-[#081426] px-7 py-3.5 font-bold text-[#081426] transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-50">{{ $hasVariants ? 'Select a size' : ($displayStock > 0 ? 'Add to bag' : 'Out of stock') }}</button><button data-buy-now="{{ $product->id }}" data-quantity-input="#product-quantity" @disabled($hasVariants || $displayStock < 1) class="rounded-full bg-[#081426] px-8 py-3.5 font-bold text-white transition hover:bg-[#142744] disabled:cursor-not-allowed disabled:opacity-50">{{ $hasVariants ? 'Select a size' : ($displayStock > 0 ? 'Buy now' : 'Out of stock') }}</button></div>
        <div class="mt-8 grid grid-cols-3 gap-3 border-t border-stone-200 pt-6 text-center text-xs text-stone-500"><div><b class="block text-sm text-[#081426]">COD</b>Pay on delivery</div><div><b class="block text-sm text-[#081426]">64 districts</b>Nationwide</div><div><b class="block text-sm text-[#081426]">Checked</b>Before dispatch</div></div>
    </section>
</main>
<div id="cart-backdrop" data-close-cart class="fixed inset-0 z-40 hidden bg-black/40 backdrop-blur-sm"></div>
<aside id="cart-drawer" class="drawer fixed right-0 top-0 z-50 flex h-full w-full max-w-md translate-x-full flex-col bg-[#faf9f6] p-6 shadow-2xl"><div class="flex items-center justify-between"><h2 class="font-display text-3xl font-semibold">Your bag</h2><button data-close-cart class="grid h-10 w-10 place-items-center rounded-full border text-xl">×</button></div><div id="cart-empty" class="my-auto text-center"><p class="font-display text-3xl">Your bag is empty</p></div><div id="cart-items" class="mt-8 flex-1 space-y-5 overflow-y-auto"></div><div class="border-t pt-5"><div class="flex justify-between text-lg font-bold"><span>Subtotal</span><span id="cart-subtotal">৳0</span></div><a href="{{ route('store.home') }}#shop" class="mt-5 block w-full rounded-full bg-[#081426] py-4 text-center font-bold text-white">Continue to store checkout</a></div></aside>
<script>window.storeProducts = @json($storeProducts);</script>
</body></html>
