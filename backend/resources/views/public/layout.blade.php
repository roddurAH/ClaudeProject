@php($bn = app()->getLocale() === 'bn')
<!DOCTYPE html>
<html lang="{{ $bn ? 'bn' : 'en' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ $bn ? 'কৃষি সাথী' : 'Krishi Sathi' }}</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fonts/hind-siliguri.css') }}" rel="stylesheet">
    <style>
        body { font-family: "Hind Siliguri", system-ui, sans-serif; background: #f4f6f3; font-size: 1.05rem; line-height: 1.7; }
        .page { max-width: 760px; }
        header { background: #2e7d32; color: #fff; }
        header a { color: #fff; }
        h2 { font-size: 1.25rem; margin-top: 1.75rem; color: #1b5e20; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
    </style>
</head>
<body>
<header class="py-3 mb-4">
    <div class="container page d-flex align-items-center gap-2">
        <span style="font-size: 1.8rem">🌱</span>
        <strong class="fs-5 me-auto">{{ $bn ? 'কৃষি সাথী' : 'Krishi Sathi' }}</strong>
        <a href="{{ url()->current() }}{{ $bn ? '?lang=en' : '' }}" class="small">{{ $bn ? 'English' : 'বাংলা' }}</a>
    </div>
</header>
<main class="container page pb-5">
    @yield('content')
</main>
</body>
</html>
