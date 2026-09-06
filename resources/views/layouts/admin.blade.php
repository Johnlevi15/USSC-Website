<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'USSC Admin Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-100 text-gray-800">
<header class="sticky top-0 z-40 border-b-2 border-yellow-500 bg-red-950 text-white shadow-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('logo1.png') }}" alt="USSC Logo" class="h-9 w-9 object-contain">
            <div>
                <h1 class="text-sm font-bold tracking-wide">USSC ADMIN PORTAL</h1>
                <p class="text-[10px] text-yellow-400">Central Luzon State University</p>
            </div>
        </a>
        <div class="flex items-center gap-3 text-xs">
            <span class="hidden text-gray-300 sm:inline">{{ auth()->user()->name }}</span>
            <a href="{{ route('home') }}" class="rounded-lg border border-red-700 px-3 py-1.5 hover:bg-red-900">View portal</a>
            <form method="POST" action="{{ route('admin-logout') }}">@csrf<button class="rounded-lg border border-red-700 px-3 py-1.5 hover:bg-red-900">Logout</button></form>
        </div>
    </div>
    <nav class="border-t border-red-900 bg-red-900">
        <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 text-xs font-semibold">
            <a href="{{ route('admin.dashboard') }}" class="whitespace-nowrap px-4 py-2.5 {{ request()->routeIs('admin.dashboard') ? 'border-b-2 border-yellow-400 text-yellow-400' : 'text-gray-200 hover:text-white' }}"><i class="fa-solid fa-chart-line mr-1"></i>Dashboard</a>
            <a href="{{ route('admin.documents') }}" class="whitespace-nowrap px-4 py-2.5 {{ request()->routeIs('admin.documents') ? 'border-b-2 border-yellow-400 text-yellow-400' : 'text-gray-200 hover:text-white' }}"><i class="fa-solid fa-file-signature mr-1"></i>Document Requests</a>
            <a href="{{ route('admin.lost-found') }}" class="whitespace-nowrap px-4 py-2.5 {{ request()->routeIs('admin.lost-found') ? 'border-b-2 border-yellow-400 text-yellow-400' : 'text-gray-200 hover:text-white' }}"><i class="fa-solid fa-boxes-packing mr-1"></i>Lost & Found</a>
            <a href="{{ route('admin.events') }}" class="whitespace-nowrap px-4 py-2.5 {{ request()->routeIs('admin.events') ? 'border-b-2 border-yellow-400 text-yellow-400' : 'text-gray-200 hover:text-white' }}"><i class="fa-solid fa-calendar-plus mr-1"></i>Events</a>
            <a href="{{ route('admin.logs') }}" class="whitespace-nowrap px-4 py-2.5 {{ request()->routeIs('admin.logs') ? 'border-b-2 border-yellow-400 text-yellow-400' : 'text-gray-200 hover:text-white' }}"><i class="fa-solid fa-clock-rotate-left mr-1"></i>Activity Logs</a>
        </div>
    </nav>
</header>
<main class="mx-auto max-w-7xl space-y-6 p-4 md:p-6">
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
@stack('scripts')
</body>
</html>
