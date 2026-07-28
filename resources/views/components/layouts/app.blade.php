<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" theme="light" data-theme="light">

<head>
    <!-- Cơ bản -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Vicayvang">

    <!-- SEO Title & Description -->
    <title>@yield('title', 'Vicayvang - Tài chính tiện lợi')</title>
    <meta name="description"
        content="Cây vàng của bạn click là nhận tiền - Giải pháp tài chính nhanh chóng, tiện lợi và minh bạch cùng Vicayvang.">
    <meta name="keywords"
        content="vicayvang, cây vàng, vay tiền online, tài chính nhanh, vay vốn, tiền mặt, dịch vụ tài chính">

    <!-- Canonical -->
    <link rel="canonical" href="https://vicayvang.com">

    <!-- Favicons (đa kích thước) -->
    <link type="image/x-icon" rel="icon" href="/images/logo.jpg">
    <link href="/images/logo.jpg" sizes="48x48" type="image/png" rel="icon">

    <!-- Open Graph / Facebook / Zalo -->
    <meta property="og:locale" content="vi_VN">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Vicayvang - Tài chính tiện lợi">
    <meta property="og:description" content="Cây vàng của bạn click là nhận tiền - Vicayvang.">
    <meta property="og:url" content="https://vicayvang.com/">
    <meta property="og:site_name" content="Vicayvang">
    <meta property="og:image" content="https://cayvang.vn/images/logo-og.jpg">
    <meta property="og:image:alt" content="Vicayvang - Tài chính tiện lợi">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <!-- Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Vicayvang - Tài chính tiện lợi">
    <meta name="twitter:description" content="Cây vàng của bạn click là nhận tiền - Vicayvang.">
    <meta name="twitter:image" content="https://cayvang.vn/images/logo-og.jpg">
    <meta name="twitter:site" content="@Vicayvang">

    <!-- Theme & Color -->
    <meta name="theme-color" content="#ffffff">
    <meta name="msapplication-TileColor" content="#ffffff">

    <!-- CSS / JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root,
        html,
        body {
            color-scheme: only light !important;
            forced-color-adjust: none;
        }
    </style>

    @livewireStyles

    <meta name="google-site-verification" content="TdBaUWbpOdYTHgdAxVnjHYpLlWD2WnrWtlHUuWiCcQw" />
</head>


<body style="color-scheme: light;" class="bg-white min-h-[100vh]">
    <div class="min-h-screen flex justify-center">
        <div class="w-[450px] min-h-screen bg-white flex flex-col relative">
            <div class="flex-1 pb-16">
                {{ $slot }}
            </div>
            <div class="fixed bottom-0 left-0 right-0 w-full z-50">
                <div class="max-w-[450px] mx-auto px-2">
                @if (request()->route()->getName() !== 'register' && request()->route()->getName() !== 'login')
                    <x-layouts.footer />
                @endif
                </div>
            </div>
        </div>
    </div>
    @livewireScripts
</body>

</html>
