<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? config('app.name', 'Gumi Coffee') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen flex flex-col bg-[#fffaee] text-[#020202] antialiased selection:bg-[#b22e3b] selection:text-[#fffaee]">
        @include('layouts.navigation')

        <main class="flex-1 w-full py-12 px-4 sm:px-6 lg:px-8 flex items-center justify-center">
            <div class="w-full max-w-md bg-[#fffaee] border-2 border-[#020202] p-6 sm:p-8">
                {{ $slot }}
            </div>
        </main>

        <footer class="w-full bg-[#b22e3b] text-[#fffaee] py-6 px-4 text-center text-xs font-black uppercase tracking-[0.18em]">
            GUMI COFFEE CLUB • {{ config('gumi.location') }} • {{ config('gumi.operating_hours.open') }} - {{ config('gumi.operating_hours.close') }} WIB
        </footer>
    </body>
</html>
