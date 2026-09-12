<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', $title ?? 'School Safe Zone SMAN 1 Ciruas')</title>

<meta name="description" content="School Safe Zone SMAN 1 Ciruas — aman berangkat, aman pulang, orang tua tenang.">
<meta name="theme-color" content="#1D4ED8">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="SSZ Ciruas">

<link rel="manifest" href="/manifest.webmanifest">
<link rel="icon" href="/images/logo-sman1ciruas.png" sizes="any">
<link rel="apple-touch-icon" href="/icons/icon-192.png">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
@stack('head')
