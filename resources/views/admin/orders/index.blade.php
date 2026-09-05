<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders | Al-Nas Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
<header class="border-b border-stone-200 bg-white">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5">
        <div><p class="text-xs font-semibold uppercase tracking-[.25em] text-amber-700">Al-Nas Store</p><h1 class="text-2xl font-semibold">Order management</h1></div>
        <div class="flex gap-3"><a href="{{ route('admin.index') }}" class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold">Dashboard</a><a href="{{ route('store.home') }}" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white">View store</a></div>
    </div>
</header>
<main class="mx-auto max-w-7xl px-5 py-8">
    @if(session('status'))<div class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-800">{{ $errors->first() }}</div>@endif
    <form method="GET" class="mb-6 grid gap-3 rounded-2xl bg-white p-4 shadow-sm md:grid-cols-[1fr_220px_auto]">
        <input name="search" value="{{ request('search') }}" placeholder="Order number, customer or phone" class="rounded-lg border border-stone-300 px-3 py-2.5">
        <select name="status" class="rounded-lg border border-stone-300 px-3 py-2.5">
            <option value="">All statuses</option>
            @foreach(['pending','confirmed','shipped','delivered','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
        </select>
        <button class="rounded-lg bg-stone-900 px-5 py-2.5 font-semibold text-white">Search</button>
    </form>
    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm">
        <table class="w-full min-w-[850px] text-left text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wider text-stone-500"><tr><th class="p-4">Order</th><th class="p-4">Customer</th><th class="p-4">Date</th><th class="p-4">Total</th><th class="p-4">Status</th><th class="p-4"></th></tr></thead>
            <tbody class="divide-y divide-stone-100">
            @forelse($orders as $order)
                <tr><td class="p-4 font-semibold">{{ $order->order_number }}</td><td class="p-4"><div>{{ $order->customer_name }}</div><div class="text-stone-500">{{ $order->phone }}</div></td><td class="p-4 text-stone-600">{{ $order->created_at->format('d M Y, h:i A') }}</td><td class="p-4 font-semibold">৳{{ number_format($order->total) }}</td><td class="p-4"><span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold">{{ ucfirst($order->status) }}</span></td><td class="p-4 text-right"><a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-amber-700 hover:text-amber-900">View details →</a></td></tr>
            @empty<tr><td colspan="6" class="p-10 text-center text-stone-500">No matching orders found.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</main>
</body>
</html>
