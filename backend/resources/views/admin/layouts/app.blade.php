<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }} Admin</title>
    {{-- All assets are served locally (public/vendor) so the panel works offline / on an intranet. --}}
    <link href="{{ asset('vendor/fonts/hind-siliguri.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root { --brand: #2e7d32; --brand-dark: #1b5e20; }
        body { font-family: "Hind Siliguri", system-ui, -apple-system, "Segoe UI", sans-serif; background: #f4f6f3; }
        .sidebar { width: 240px; flex-shrink: 0; min-height: 100vh; background: var(--brand-dark); }
        .sidebar .nav-link { color: rgba(255,255,255,.85); border-radius: .5rem; padding: .5rem .75rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: rgba(255,255,255,.15); color: #fff; }
        .sidebar .nav-section { color: rgba(255,255,255,.5); font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; margin: 1rem .75rem .25rem; }
        .brand { color: #fff; font-weight: 600; font-size: 1.15rem; }
        .btn-brand { background: var(--brand); color: #fff; }
        .btn-brand:hover { background: var(--brand-dark); color: #fff; }
        .card { border: 0; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .stat-value { font-size: 1.8rem; font-weight: 600; }
        [lang="bn"] { font-family: "Hind Siliguri", sans-serif; }
        .table td, .table th { vertical-align: middle; }
        @media (max-width: 991.98px) { .sidebar { width: 100%; min-height: auto; } }
    </style>
    @stack('head')
</head>
<body>
<div class="d-lg-flex">
    <nav class="sidebar p-3 navbar-dark">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.dashboard') }}" class="brand text-decoration-none">🌾 {{ config('app.name') }}</a>
            <button class="navbar-toggler d-lg-none border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sideNav" aria-label="Menu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="collapse d-lg-block" id="sideNav">
            <ul class="nav flex-column gap-1">
                <li><a class="nav-link @if(request()->routeIs('admin.dashboard')) active @endif" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>

                @can('answer-questions')
                    <li class="nav-section">Farmers</li>
                    <li><a class="nav-link @if(request()->routeIs('admin.questions.*')) active @endif" href="{{ route('admin.questions.index') }}"><i class="bi bi-chat-dots me-2"></i>Questions</a></li>
                    <li><a class="nav-link @if(request()->routeIs('admin.ai.*')) active @endif" href="{{ route('admin.ai.index') }}"><i class="bi bi-robot me-2"></i>AI doctor</a></li>
                @endcan

                @can('manage-content')
                    <li class="nav-section">Knowledge base</li>
                    <li><a class="nav-link @if(request()->routeIs('admin.crops.*', 'admin.stages.*')) active @endif" href="{{ route('admin.crops.index') }}"><i class="bi bi-flower1 me-2"></i>Crops & calendars</a></li>
                    <li><a class="nav-link @if(request()->routeIs('admin.problems.*')) active @endif" href="{{ route('admin.problems.index') }}"><i class="bi bi-bug me-2"></i>Pests & diseases</a></li>
                    <li><a class="nav-link @if(request()->routeIs('admin.articles.*')) active @endif" href="{{ route('admin.articles.index') }}"><i class="bi bi-journal-text me-2"></i>Articles & tips</a></li>
                @endcan

                @can('manage-prices')
                    <li class="nav-section">Market</li>
                    <li><a class="nav-link @if(request()->routeIs('admin.prices.entry')) active @endif" href="{{ route('admin.prices.entry') }}"><i class="bi bi-pencil-square me-2"></i>Enter daily prices</a></li>
                    <li><a class="nav-link @if(request()->routeIs('admin.prices.index', 'admin.prices.import')) active @endif" href="{{ route('admin.prices.index') }}"><i class="bi bi-currency-exchange me-2"></i>Price records</a></li>
                    <li><a class="nav-link @if(request()->routeIs('admin.markets.*')) active @endif" href="{{ route('admin.markets.index') }}"><i class="bi bi-shop me-2"></i>Markets</a></li>
                    <li><a class="nav-link @if(request()->routeIs('admin.commodities.*')) active @endif" href="{{ route('admin.commodities.index') }}"><i class="bi bi-basket me-2"></i>Commodities</a></li>
                    <li><a class="nav-link @if(request()->routeIs('admin.fertilizers.*')) active @endif" href="{{ route('admin.fertilizers.index') }}"><i class="bi bi-droplet me-2"></i>Fertilizer prices</a></li>
                @endcan

                @can('manage-alerts')
                    <li class="nav-section">Broadcast</li>
                    <li><a class="nav-link @if(request()->routeIs('admin.alerts.*')) active @endif" href="{{ route('admin.alerts.index') }}"><i class="bi bi-megaphone me-2"></i>Alerts</a></li>
                @endcan

                @can('manage-users')
                    <li class="nav-section">System</li>
                    <li><a class="nav-link @if(request()->routeIs('admin.users.*')) active @endif" href="{{ route('admin.users.index') }}"><i class="bi bi-people me-2"></i>Users & staff</a></li>
                @endcan
            </ul>
        </div>
    </nav>

    <main class="flex-grow-1 p-3 p-lg-4" style="min-width: 0">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h1 class="h4 mb-0">@yield('title', 'Dashboard')</h1>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small"><i class="bi bi-person-circle"></i> {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-box-arrow-right"></i> Log out</button>
                </form>
            </div>
        </div>

        <x-flash />

        @yield('content')
    </main>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
@stack('scripts')
</body>
</html>
