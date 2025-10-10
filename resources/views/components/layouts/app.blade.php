<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" theme="light" data-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css'])
    @vite(['resources/js/app.js'])

    <style>
        :root,
        html,
        body {
            color-scheme: only light !important;
            forced-color-adjust: none;
        }
    </style>
    @livewireStyles
</head>

<body style="color-scheme: light;" class="bg-white min-h-[100vh]">
    <div class="min-h-screen flex justify-center">
        <div class="w-[450px] min-h-screen bg-white flex flex-col relative">
            <div class="flex-1 pb-16">
                {{ $slot }}
            </div>
            <div class="fixed bottom-0 left-1/2 transform -translate-x-1/2 w-[450px] z-50">
                @if (request()->route()->getName() !== 'register' && request()->route()->getName() !== 'login')
                    <x-layouts.footer />
                @endif
            </div>
        </div>
    </div>
    @livewireScripts
</body>

</html>
