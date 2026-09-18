<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'USSC Admin Login')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gray-100 text-gray-800">
    <main class="flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-md">
            <div class="mb-5 text-center">
                <div class="mx-auto mb-3 flex items-center justify-center gap-3">
                    <img src="{{ asset('clsu-logo.png') }}" alt="CLSU Seal" class="h-14 w-14 object-contain">
                    <img src="{{ asset('logo1.png') }}" alt="USSC Logo" class="h-14 w-14 object-contain">
                </div>
                <h1 class="text-lg font-extrabold text-red-950">USSC ADMIN PORTAL</h1>
                <p class="text-xs text-gray-500">Central Luzon State University</p>
            </div>

            @yield('content')

            <p class="mt-5 text-center text-[11px] text-gray-400">
                Authorized USSC administrators only.
            </p>
        </div>
    </main>
</body>
</html>
