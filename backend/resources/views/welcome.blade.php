<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} – কৃষি সাথী</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fonts/hind-siliguri.css') }}" rel="stylesheet">
    <style>body { font-family: "Hind Siliguri", system-ui, sans-serif; }</style>
</head>
<body class="bg-light">
<div class="container py-5" style="max-width: 720px">
    <div class="text-center mb-4">
        <div style="font-size: 4rem">🌾</div>
        <h1>কৃষি সাথী · {{ config('app.name') }}</h1>
        <p class="lead text-muted">Farm advice, market prices and weather for Bangladesh – server is running.</p>
    </div>
    <div class="list-group">
        <a class="list-group-item list-group-item-action" href="{{ route('admin.login') }}">🔐 Admin / expert panel</a>
        <a class="list-group-item list-group-item-action" href="{{ url('/api/v1/meta') }}">🔌 API status (<code>/api/v1/meta</code>)</a>
        <a class="list-group-item list-group-item-action" href="{{ url('/up') }}">💚 Health check (<code>/up</code>)</a>
    </div>
</div>
</body>
</html>
