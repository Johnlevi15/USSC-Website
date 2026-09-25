<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'USSC Admin Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-100 text-gray-800">
@php
    $adminNavigationItems = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'fa-chart-line'],
        ['label' => 'Document Requests', 'route' => 'admin.documents', 'active' => 'admin.documents*', 'icon' => 'fa-file-signature'],
        ['label' => 'Document Types', 'route' => 'admin.document-types.index', 'active' => 'admin.document-types.*', 'icon' => 'fa-list-check'],
        ['label' => 'Lost & Found', 'route' => 'admin.lost-found', 'active' => 'admin.lost-found*', 'icon' => 'fa-boxes-packing'],
        ['label' => 'Events', 'route' => 'admin.events', 'active' => 'admin.events*', 'icon' => 'fa-calendar-plus'],
        ['label' => 'Activity Logs', 'route' => 'admin.logs', 'active' => 'admin.logs', 'icon' => 'fa-clock-rotate-left'],
    ];
@endphp

<header class="sticky top-0 z-40 border-b-2 border-yellow-500 bg-red-950 text-white shadow-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
        <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 items-center gap-3">
            <img src="{{ asset('logo1.png') }}" alt="USSC Logo" class="h-9 w-9 object-contain">
            <div class="min-w-0">
                <h1 class="text-sm font-bold tracking-wide">USSC ADMIN PORTAL</h1>
                <p class="text-[10px] text-yellow-400">Central Luzon State University</p>
            </div>
        </a>
        <div class="flex items-center gap-2">
            <span class="hidden max-w-48 truncate text-xs text-gray-300 sm:inline">{{ auth('admin')->user()->name }}</span>
            <button
                id="admin-menu-toggle"
                type="button"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-red-800 bg-red-900 px-3 text-xs font-bold text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                aria-controls="admin-menu-panel"
                aria-expanded="false"
                aria-label="Open admin navigation menu"
            >
                <i class="fa-solid fa-bars"></i>
                <span>Menu</span>
            </button>
        </div>
    </div>
</header>

<div id="admin-menu-overlay" class="fixed inset-0 z-50 hidden bg-black/60" aria-hidden="true"></div>
<aside
    id="admin-menu-panel"
    class="fixed inset-y-0 right-0 z-[60] flex w-80 max-w-[88vw] translate-x-full flex-col bg-white text-gray-900 shadow-2xl transition-transform duration-300 ease-out"
    aria-labelledby="admin-menu-title"
    aria-hidden="true"
>
    <div class="flex items-center justify-between border-b border-gray-200 bg-red-950 px-4 py-3 text-white">
        <div class="min-w-0">
            <h2 id="admin-menu-title" class="text-sm font-extrabold tracking-wide">Admin Menu</h2>
            <p class="truncate text-xs text-red-100">{{ auth('admin')->user()->name }}</p>
        </div>
        <button
            id="admin-menu-close"
            type="button"
            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-white transition hover:bg-red-800 focus:outline-none focus:ring-2 focus:ring-yellow-400"
            aria-label="Close admin navigation menu"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="flex flex-1 flex-col gap-2 overflow-y-auto p-4 text-sm font-semibold">
        @foreach($adminNavigationItems as $item)
            <a href="{{ route($item['route']) }}" @class([
                'flex items-center gap-3 rounded-lg px-4 py-3 transition',
                'bg-red-900 text-white shadow-sm' => request()->routeIs($item['active']),
                'text-gray-700 hover:bg-red-50 hover:text-red-900' => ! request()->routeIs($item['active']),
            ])>
                <i class="fa-solid {{ $item['icon'] }} w-5 text-center text-xs"></i>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="space-y-2 border-t border-gray-200 bg-gray-50 p-4 text-sm font-semibold">
        <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-900">
            <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center text-xs"></i>
            View portal
        </a>
        <form method="POST" action="{{ route('admin-logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg bg-red-900 px-4 py-3 text-left text-white hover:bg-red-800">
                <i class="fa-solid fa-right-from-bracket w-5 text-center text-xs"></i>
                Logout
            </button>
        </form>
    </div>
</aside>

<main class="mx-auto max-w-7xl space-y-6 p-4 md:p-6">
    @if(session('success') && ! request()->routeIs('admin.lost-found*'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif
    @yield('content')
</main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const adminMenuToggle = document.getElementById('admin-menu-toggle');
        const adminMenuClose = document.getElementById('admin-menu-close');
        const adminMenuOverlay = document.getElementById('admin-menu-overlay');
        const adminMenuPanel = document.getElementById('admin-menu-panel');

        function openAdminMenu() {
            adminMenuOverlay.classList.remove('hidden');
            adminMenuPanel.classList.remove('translate-x-full');
            adminMenuToggle.setAttribute('aria-expanded', 'true');
            adminMenuPanel.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        }

        function closeAdminMenu() {
            adminMenuOverlay.classList.add('hidden');
            adminMenuPanel.classList.add('translate-x-full');
            adminMenuToggle.setAttribute('aria-expanded', 'false');
            adminMenuPanel.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('overflow-hidden');
        }

        adminMenuToggle.addEventListener('click', openAdminMenu);
        adminMenuClose.addEventListener('click', closeAdminMenu);
        adminMenuOverlay.addEventListener('click', closeAdminMenu);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && adminMenuPanel.getAttribute('aria-hidden') === 'false') {
                closeAdminMenu();
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>
