<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>

<body style="color-scheme: light;" class="bg-white min-h-[100vh]">
    <div class="min-h-screen flex justify-center">
        <div class="w-[450px] min-h-screen bg-white flex flex-col">
            <div class="flex-1">
                {{ $slot }}
            </div>
            <x-layouts.footer />
        </div>
    </div>
    @livewireScripts
</body>

</html>
