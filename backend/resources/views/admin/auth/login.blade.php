<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in · {{ config('app.name') }} Admin</title>
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/fonts/hind-siliguri.css') }}" rel="stylesheet">
    <style>body { font-family: "Hind Siliguri", system-ui, sans-serif; }</style>
    <style>
        body { background: linear-gradient(135deg, #1b5e20, #66bb6a); min-height: 100vh; }
        .login-card { max-width: 400px; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
<div class="card login-card w-100 shadow">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <div style="font-size: 3rem">🌾</div>
            <h1 class="h4 mb-0">{{ config('app.name') }}</h1>
            <p class="text-muted">Admin & expert panel</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="login">Email or mobile number</label>
                <input class="form-control" id="login" name="login" value="{{ old('login') }}" required autofocus autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">Keep me signed in</label>
            </div>
            <button class="btn btn-success w-100" type="submit">Sign in</button>
        </form>
    </div>
</div>
</body>
</html>
