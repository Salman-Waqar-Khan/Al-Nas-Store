<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="description" content="Track your AL-NAS order securely using your order number and phone number.">
    <title>Track Order — {{ config('store.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/al-nas-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="grain min-h-screen bg-[#f6f2e8] antialiased">
<header class="border-b border-black/5 bg-[#f7f5f0]/90">
    <div class="mx-auto flex h-28 max-w-6xl items-center justify-between px-5 sm:h-32 lg:px-8">
        <a href="{{ route('store.home') }}" class="rounded-full"><img src="{{ config('store.logo') }}" alt="Al-Nas logo" class="h-24 w-24 rounded-full object-contain shadow-sm ring-1 ring-[#c8a34f]/40 sm:h-28 sm:w-28"></a>
        <a href="{{ route('store.home') }}" class="text-sm font-semibold hover:text-amber-700">Back to store</a>
    </div>
</header>
<main class="mx-auto max-w-6xl px-5 py-12 lg:px-8 lg:py-20">
    <div class="grid gap-10 lg:grid-cols-[.8fr_1.2fr] lg:items-start">
        <section>
            <p class="text-xs font-bold tracking-[.24em] text-amber-700">ORDER TRACKING</p>
            <h1 class="font-display mt-3 text-5xl font-semibold leading-none sm:text-6xl">Where is your order?</h1>
            <p class="mt-5 max-w-md leading-7 text-stone-600">@isset($order)Your details matched successfully. The latest progress is shown beside this confirmation.@else Enter the order number from your confirmation page and the same phone number used at checkout. @endisset</p>
            @isset($order)
            <div class="mt-8 rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
                <div class="flex items-start gap-3"><span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-600 font-bold text-white">✓</span><div><h2 class="font-semibold text-emerald-950">Order verified</h2><p class="mt-1 text-sm leading-6 text-emerald-800">Tracking details for <b>{{ $order->order_number }}</b> are now displayed.</p></div></div>
                <a href="{{ route('store.track') }}" class="mt-5 inline-flex rounded-full border border-emerald-300 bg-white px-5 py-2.5 text-sm font-bold text-emerald-900 transition hover:bg-emerald-100">Track another order</a>
            </div>
            @else
            <form method="POST" action="{{ route('store.track.lookup') }}" class="mt-8 space-y-4 rounded-2xl bg-white p-5 shadow-sm sm:p-7">
                @csrf
                <label class="block text-sm font-semibold">Order number<input name="order_number" required value="{{ old('order_number') }}" placeholder="YS-XXXXXXXX" autocomplete="off" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3 font-normal uppercase outline-none focus:border-amber-700 focus:ring-2 focus:ring-amber-700/10"></label>
                <label class="block text-sm font-semibold">Phone number<input name="phone" required value="{{ old('phone') }}" placeholder="01XXXXXXXXX" inputmode="tel" autocomplete="tel" class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3 font-normal outline-none focus:border-amber-700 focus:ring-2 focus:ring-amber-700/10"></label>
                @if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>@endif
                <button class="w-full rounded-full bg-[#081426] py-3.5 font-bold text-white transition hover:bg-[#142744]">Track order</button>
            </form>
            @endisset
        </section>

        @isset($order)
        @php
            $steps = ['pending' => 'Order received', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
            $currentIndex = array_search($order->status, array_keys($steps), true);
        @endphp
        <section class="rounded-[2rem] bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-9">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-6">
                <div><p class="text-xs font-bold tracking-[.18em] text-stone-500">ORDER</p><h2 class="font-display mt-1 text-3xl font-semibold">{{ $order->order_number }}</h2><p class="mt-1 text-sm text-stone-500">Placed {{ $order->created_at->format('d M Y, g:i A') }}</p></div>
                <span @class(['rounded-full px-4 py-2 text-xs font-bold uppercase tracking-wider','bg-amber-100 text-amber-800'=>$order->status==='pending','bg-blue-100 text-blue-800'=>in_array($order->status,['confirmed','shipped']),'bg-emerald-100 text-emerald-800'=>$order->status==='delivered','bg-rose-100 text-rose-800'=>$order->status==='cancelled'])>{{ $order->status }}</span>
            </div>
            @if($order->status === 'cancelled')
                <div class="mt-7 rounded-2xl bg-rose-50 p-5"><h3 class="font-semibold text-rose-900">This order was cancelled</h3><p class="mt-1 text-sm leading-6 text-rose-700">Please contact our Facebook page if you believe this is a mistake or want help placing a new order.</p></div>
            @else
                <ol class="mt-8 grid gap-5 sm:grid-cols-4">
                    @foreach($steps as $key => $label)
                    @php $active = $currentIndex !== false && array_search($key, array_keys($steps), true) <= $currentIndex; @endphp
                    <li><span @class(['grid h-9 w-9 place-items-center rounded-full text-sm font-bold','bg-[#081426] text-white'=>$active,'bg-stone-100 text-stone-400'=>!$active])>{{ $loop->iteration }}</span><p @class(['mt-3 text-sm font-semibold','text-[#081426]'=>$active,'text-stone-400'=>!$active])>{{ $label }}</p></li>
                    @endforeach
                </ol>
            @endif
            <div class="mt-8 border-t border-stone-200 pt-6"><h3 class="font-display text-2xl font-semibold">Order summary</h3><div class="mt-4 space-y-3">@foreach($order->items as $item)<div class="flex justify-between gap-4 text-sm"><span>{{ $item['name'] }} @if(!empty($item['size']))<b class="text-amber-700">· {{ $item['size'] }}</b>@endif <span class="text-stone-400">× {{ $item['quantity'] }}</span></span><b>৳{{ number_format($item['price'] * $item['quantity']) }}</b></div>@endforeach</div><div class="mt-5 flex justify-between border-t border-stone-200 pt-4"><span class="font-semibold">Total including delivery</span><b class="text-lg">৳{{ number_format($order->total) }}</b></div><p class="mt-4 text-xs text-stone-500">Last updated {{ $order->updated_at->diffForHumans() }}</p></div>
        </section>
        @else
        <section class="hidden min-h-[420px] place-items-center rounded-[2rem] border border-dashed border-stone-300 bg-white/40 p-10 text-center lg:grid"><div><div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-white text-2xl shadow-sm">⌕</div><h2 class="font-display mt-5 text-3xl font-semibold">Your progress will appear here</h2><p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-stone-500">For privacy, both your order number and checkout phone number must match.</p></div></section>
        @endisset
    </div>
</main>
</body>
</html>
