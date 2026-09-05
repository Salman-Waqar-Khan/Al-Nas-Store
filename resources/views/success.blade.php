<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Order received</title>@vite(['resources/css/app.css'])</head>
<body class="grain grid min-h-screen place-items-center p-5">
<main class="w-full max-w-lg rounded-[2rem] bg-white p-8 text-center shadow-xl sm:p-12">
    <div class="mx-auto grid h-16 w-16 place-items-center rounded-full bg-amber-100 text-3xl text-amber-700">✓</div>
    <p class="mt-6 text-xs font-bold tracking-[.2em] text-amber-700">ORDER RECEIVED</p>
    <h1 class="font-display mt-2 text-5xl font-semibold">Thank you!</h1>
    <p class="mt-4 text-stone-500">We have received your order and will call you shortly to confirm delivery.</p>
    <div class="mt-7 rounded-2xl bg-stone-50 p-5 text-left">
        <div class="flex justify-between"><span class="text-stone-500">Order number</span><b>{{ $order->order_number }}</b></div>
        <div class="mt-3 flex justify-between"><span class="text-stone-500">Total</span><b>৳{{ number_format($order->total) }}</b></div>
        <div class="mt-3 flex justify-between"><span class="text-stone-500">Payment</span><b>Cash on delivery</b></div>
    </div>
    <p class="mt-5 text-sm text-stone-500">Keep your order number and checkout phone number to track progress.</p>
    <div class="mt-7 flex flex-col justify-center gap-3 sm:flex-row">
        <a href="{{ route('store.track') }}" class="rounded-full bg-[#081426] px-7 py-3.5 font-bold text-white">Track this order</a>
        <a href="{{ route('store.home') }}" class="rounded-full border border-stone-300 px-7 py-3.5 font-bold">Continue shopping</a>
    </div>
</main>
<script>localStorage.removeItem('your-store-cart')</script>
</body>
</html>
