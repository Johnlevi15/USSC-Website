<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CLSU USSC Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex flex-col font-sans">
<header class="bg-red-900 text-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
        <a href="{{ route('home') }}" class="flex items-center gap-3">
            <img src="{{ asset('logo1.png') }}" alt="USSC Logo" class="w-10 h-10" onerror="this.src='https://via.placeholder.com/40'">
            <div><h1 class="font-bold text-lg leading-none tracking-wide">USSC PORTAL</h1><p class="text-xs text-red-200">Central Luzon State University</p></div>
        </a>
        <nav class="hidden md:flex items-center gap-1 font-medium text-sm">
            <a href="{{ route('home') }}" class="px-3 py-2 {{ request()->routeIs('home') ? 'border-b-2 border-yellow-400 text-yellow-400 font-bold' : 'hover:text-yellow-300' }}"><i class="fa-solid fa-house text-xs"></i> Home</a>
            <a href="{{ route('lost-found') }}" class="px-3 py-2 {{ request()->routeIs('lost-found') ? 'border-b-2 border-yellow-400 text-yellow-400 font-bold' : 'hover:text-yellow-300' }}"><i class="fa-solid fa-box-archive text-xs"></i> Lost & Found</a>
            <a href="{{ route('report-item') }}" class="px-3 py-2 {{ request()->routeIs('report-item') ? 'border-b-2 border-yellow-400 text-yellow-400 font-bold' : 'hover:text-yellow-300' }}"><i class="fa-solid fa-cloud-arrow-up text-xs"></i> Report Item</a>
            <a href="{{ route('document-request') }}" class="px-3 py-2 {{ request()->routeIs('document-request') ? 'border-b-2 border-yellow-400 text-yellow-400 font-bold' : 'hover:text-yellow-300' }}"><i class="fa-solid fa-file-lines text-xs"></i> Request Document</a>
            <a href="{{ route('track-request') }}" class="px-3 py-2 {{ request()->routeIs('track-request') ? 'border-b-2 border-yellow-400 text-yellow-400 font-bold' : 'hover:text-yellow-300' }}"><i class="fa-solid fa-route text-xs"></i> Track Request</a>
            <a href="{{ route('admin-login') }}" class="px-3 py-2 {{ request()->routeIs('admin-login') ? 'border-b-2 border-yellow-400 text-yellow-400 font-bold' : 'hover:text-yellow-300' }}"><i class="fa-solid fa-user-shield text-xs"></i> Admin Login</a>
        </nav>
        <select class="md:hidden bg-red-950 border border-red-700 rounded px-2 py-1 text-xs" onchange="location.href=this.value" aria-label="Navigate">
            <option selected disabled>Menu</option>
            <option value="{{ route('home') }}">Home</option><option value="{{ route('lost-found') }}">Lost & Found</option><option value="{{ route('report-item') }}">Report Item</option><option value="{{ route('document-request') }}">Request Document</option><option value="{{ route('track-request') }}">Track Request</option><option value="{{ route('admin-login') }}">Admin Login</option>
        </select>
    </div>
</header>
<main class="flex-grow">@yield('content')</main>
<footer class="bg-red-950 text-gray-300 text-center text-xs py-4 border-t-4 border-yellow-500">Central Luzon State University - USSC Portal</footer>
<div id="portal-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true" aria-labelledby="portal-modal-title">
    <div class="w-full max-w-md rounded-xl bg-white p-6 text-center shadow-xl">
        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-xl text-green-600">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2 id="portal-modal-title" class="text-lg font-bold text-gray-800"></h2>
        <p id="portal-modal-message" class="mt-2 text-sm text-gray-600"></p>
        <div class="mt-5 flex gap-2">
            <button id="portal-modal-action" type="button" class="hidden flex-1 rounded-lg bg-red-900 px-4 py-2 text-xs font-bold text-white hover:bg-red-800"></button>
            <button type="button" onclick="closePortalModal()" class="flex-1 rounded-lg bg-gray-200 px-4 py-2 text-xs font-bold text-gray-700 hover:bg-gray-300">Close</button>
        </div>
    </div>
</div>
<script>
    function showPortalModal(title, message, actionLabel = '', actionUrl = '') {
        const modal = document.getElementById('portal-modal');
        const action = document.getElementById('portal-modal-action');

        document.getElementById('portal-modal-title').textContent = title;
        document.getElementById('portal-modal-message').textContent = message;
        action.textContent = actionLabel;
        action.classList.toggle('hidden', !actionLabel || !actionUrl);
        action.onclick = actionUrl ? () => { window.location.href = actionUrl; } : null;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePortalModal() {
        const modal = document.getElementById('portal-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@stack('scripts')
</body>
</html>