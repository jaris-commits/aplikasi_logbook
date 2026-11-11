<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-slate-100 via-white to-blue-50 px-4 py-10 sm:px-6">
            <div class="pointer-events-none absolute -left-24 top-12 h-72 w-72 rounded-full bg-blue-100/70 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 -right-20 h-80 w-80 rounded-full bg-amber-100/60 blur-3xl"></div>

            <div class="relative w-full max-w-md">
                <div class="mb-7 flex flex-col items-center text-center">
                    <a href="/" class="inline-flex flex-col items-center">
                        <x-application-logo class="h-16 w-16" />
                        <span class="mt-3 text-xl font-bold text-slate-900">Logbook Magang</span>
                        <span class="mt-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">SMKN 20 Jakarta</span>
                    </a>
                </div>

                <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 p-6 shadow-xl shadow-slate-900/5 sm:p-8">
                    {{ $slot }}
                </section>

                <p class="mt-6 text-center text-xs text-slate-500">Portal pencatatan kegiatan Praktik Kerja Lapangan</p>
            </div>
        </main>
    </body>
</html>
