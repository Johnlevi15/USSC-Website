<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CLSU USSC Portal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            <a href="{{ route('document-request') }}" class="px-3 py-2 {{ request()->routeIs('document-request') ? 'border-b-2 border-yellow-400 text-yellow-400 font-bold' : 'hover:text-yellow-300' }}"><i class="fa-solid fa-file-lines text-xs"></i> Request Document</a>
            <a href="{{ route('track-request') }}" class="px-3 py-2 {{ request()->routeIs('track-request') ? 'border-b-2 border-yellow-400 text-yellow-400 font-bold' : 'hover:text-yellow-300' }}"><i class="fa-solid fa-route text-xs"></i> Track Request</a>
            @if(auth('admin')->check())
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 hover:text-yellow-300"><i class="fa-solid fa-gauge-high text-xs"></i> Admin Dashboard</a>
            @endif
        </nav>
        <select class="md:hidden bg-red-950 border border-red-700 rounded px-2 py-1 text-xs" onchange="location.href=this.value" aria-label="Navigate">
            <option selected disabled>Menu</option>
            <option value="{{ route('home') }}">Home</option><option value="{{ route('lost-found') }}">Lost & Found</option><option value="{{ route('document-request') }}">Request Document</option><option value="{{ route('track-request') }}">Track Request</option>
            @if(auth('admin')->check())
                <option value="{{ route('admin.dashboard') }}">Admin Dashboard</option>
            @endif
        </select>
    </div>
</header>
<main class="flex-grow">@yield('content')</main>
<footer class="mt-12 overflow-hidden border-t-4 border-yellow-500 bg-neutral-900 text-white" style="background-color: #171717;">
    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-6 py-10 text-sm md:grid-cols-2 lg:grid-cols-4">
        <div class="space-y-4">
            <div class="flex items-center gap-3">
                <img src="{{ asset('clsu-logo.png') }}" class="h-12 w-12" alt="CLSU Seal">
                <img src="{{ asset('logo1.png') }}" class="h-14 w-14" alt="USSC Logo">
            </div>

            <div>
                <h3 class="text-base font-extrabold uppercase tracking-wide text-white">Central Luzon State University</h3>
                <p class="text-xs text-gray-300">Science City of Muñoz, Nueva Ecija, Philippines 3120</p>
                <div class="mt-2 h-0.5 w-16 bg-yellow-500"></div>
            </div>

            <ul class="space-y-2 text-xs text-gray-300">
                <li class="flex items-start gap-2">
                    <i class="fa-solid fa-location-dot mt-0.5 text-yellow-500"></i>
                    <span>Central Luzon State University, Science City of Muñoz, Nueva Ecija, Philippines</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-envelope text-yellow-500"></i>
                    <a href="mailto:clsuussc@clsu2.edu.ph" class="hover:underline">clsuussc@clsu2.edu.ph</a>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-phone text-yellow-500"></i>
                    <span>(044) 940 8785</span>
                </li>
            </ul>

            <div class="overflow-hidden rounded-lg border border-gray-600 shadow-md">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3844.75782747183!2d120.92383827581177!3d15.738676247514758!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3390d63503f3fb67%3A0xb304bb92b52d921b!2sCentral%20Luzon%20State%20University!5e0!3m2!1sen!2sph!4v1710000000000!5m2!1sen!2sph"
                    class="h-32 w-full border-0"
                    title="Central Luzon State University location"
                    loading="lazy"
                    allowfullscreen>
                </iframe>
            </div>
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-bold uppercase tracking-wider text-yellow-400">Vision</h4>
            <p class="text-xs leading-relaxed text-gray-300">CLSU as a world-class National Research University for science and technology in agriculture and allied fields.</p>
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-bold uppercase tracking-wider text-yellow-400">Mission</h4>
            <p class="text-xs leading-relaxed text-gray-300">CLSU shall develop globally competitive, work-ready, socially-responsible and empowered human resources who value life-long learning; and to generate, disseminate, and apply knowledge and technologies for poverty alleviation, environmental protection, and sustainable development.</p>
        </div>

        <div class="space-y-3">
            <h4 class="text-sm font-bold uppercase tracking-wider text-yellow-400">Feedback and Grievance Desk</h4>
            <p class="text-xs leading-relaxed text-gray-300">Central Luzon State University values the voices of its students, faculty, staff, and the people it serves and is committed to continuously improve its services. As part of our commitment to quality and excellence, we encourage you to share your <span class="font-bold text-white">feedback, concerns, and suggestions</span>.</p>
            <p class="text-xs leading-relaxed text-gray-300">To ensure your inputs are heard and addressed, we provide the official channels above or our online portal desk for receiving feedback and grievances.</p>

            <ul class="space-y-2 border-t border-gray-800 pt-2 text-xs text-gray-300">
                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-envelope text-yellow-500"></i>
                    <a href="mailto:clsuussc@clsu2.edu.ph" class="hover:underline">clsuussc@clsu2.edu.ph</a>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-mobile-screen-button text-yellow-500"></i>
                    <span>+63 9537 267 511</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fa-solid fa-phone text-yellow-500"></i>
                    <span>(044) 940 7030</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="border-t border-neutral-800 py-3 text-center text-xs text-gray-400" style="background-color: #0a0a0a;">
        <p>&copy; {{ now()->year }} Central Luzon State University - All Rights Reserved.</p>
    </div>
</footer>
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
@include('partials.chatbot')
@stack('scripts')
</body>
</html>
