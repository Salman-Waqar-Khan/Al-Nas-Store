<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Al-Nas Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="grain grid min-h-screen place-items-center p-5">
<form method="POST" action="{{ route('admin.authenticate') }}" class="w-full max-w-sm rounded-3xl bg-white p-8 shadow-xl">
    @csrf
    <a href="{{ route('store.home') }}" title="Back to store" class="mx-auto block w-fit rounded-full focus:outline-none focus:ring-4 focus:ring-[#c8a34f]/30">
        <img src="{{ config('store.logo') }}" alt="Al-Nas logo" class="h-28 w-28 rounded-full object-cover ring-2 ring-[#c8a34f]/50 transition hover:scale-105">
    </a>
    <p class="mt-5 text-center text-xs font-bold tracking-[.2em] text-amber-700">AL-NAS</p>
    <h1 class="font-display mt-2 text-4xl font-semibold">Store admin</h1>
    <p class="mt-2 text-sm text-stone-500">Sign in with your administrator account.</p>
    @if($errors->any())<p class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
    <label class="mt-6 block text-sm font-semibold">Email
        <input type="email" name="email" value="{{ old('email', env('ADMIN_EMAIL', 'admin@al-nas.test')) }}" autocomplete="username" required autofocus class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3 outline-none focus:border-amber-700">
    </label>
    <label class="mt-4 block text-sm font-semibold">Password
        <input type="password" name="password" autocomplete="current-password" required class="mt-2 w-full rounded-xl border border-stone-300 px-4 py-3 outline-none focus:border-amber-700">
    </label>
    <button class="mt-5 w-full rounded-full bg-[#081426] py-3.5 font-bold text-white">Sign in</button>
    <a href="{{ route('store.home') }}" class="mt-4 block text-center text-sm underline">Back to store</a>
</form>
</body>
</html>
