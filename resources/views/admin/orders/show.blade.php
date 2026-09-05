<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $order->order_number }} | Al-Nas Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
<header class="border-b border-stone-200 bg-white"><div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-5"><div><p class="text-xs font-semibold uppercase tracking-[.25em] text-amber-700">Order details</p><h1 class="text-2xl font-semibold">{{ $order->order_number }}</h1></div><a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold">← All orders</a></div></header>
<main class="mx-auto grid max-w-5xl gap-6 px-5 py-8 lg:grid-cols-[1fr_340px]">
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="mb-5 text-xl font-semibold">Items</h2>
        <div class="divide-y divide-stone-100">@foreach($order->items as $item)<div class="flex justify-between gap-4 py-4"><div><p class="font-semibold">{{ $item['name'] }} @if(!empty($item['size']))<span class="text-amber-700">· {{ $item['size'] }}</span>@endif</p><p class="text-sm text-stone-500">Quantity: {{ $item['quantity'] }}</p></div><p class="font-semibold">৳{{ number_format($item['price'] * $item['quantity']) }}</p></div>@endforeach</div>
        <div class="mt-5 space-y-2 border-t border-stone-200 pt-5 text-sm"><div class="flex justify-between"><span>Subtotal</span><span>৳{{ number_format($order->subtotal) }}</span></div><div class="flex justify-between"><span>Delivery fee</span><span>৳{{ number_format($order->delivery_fee) }}</span></div><div class="flex justify-between pt-2 text-lg font-bold"><span>Total</span><span>৳{{ number_format($order->total) }}</span></div></div>
    </section>
    <aside class="space-y-6">
        @if(session('status'))<div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
        <section class="rounded-2xl bg-white p-6 shadow-sm"><h2 class="mb-4 text-lg font-semibold">Customer</h2><dl class="space-y-3 text-sm"><div><dt class="text-stone-500">Name</dt><dd class="font-medium">{{ $order->customer_name }}</dd></div><div><dt class="text-stone-500">Phone</dt><dd class="font-medium">{{ $order->phone }}</dd></div><div><dt class="text-stone-500">Address</dt><dd class="font-medium">{{ $order->address }}</dd></div><div><dt class="text-stone-500">Placed</dt><dd class="font-medium">{{ $order->created_at->format('d M Y, h:i A') }}</dd></div></dl></section>
        <section class="rounded-2xl bg-white p-6 shadow-sm"><h2 class="mb-4 text-lg font-semibold">Update status</h2><form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">@csrf @method('PATCH')<select name="status" class="w-full rounded-lg border border-stone-300 px-3 py-2.5">@foreach(['pending','confirmed','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="w-full rounded-lg bg-stone-900 px-4 py-2.5 font-semibold text-white">Save status</button></form></section>
    </aside>
</main>
</body>
</html>
