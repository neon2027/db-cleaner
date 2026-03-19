<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DB Cleaner — {{ $title ?? 'Dashboard' }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1e293b; }
        .navbar { background: #1e293b; color: white; padding: 1rem 2rem; display: flex; align-items: center; gap: 1rem; }
        .navbar a { color: #94a3b8; text-decoration: none; font-size: 0.875rem; }
        .navbar a:hover { color: white; }
        .navbar-brand { font-weight: 700; font-size: 1.125rem; color: white; }
        .container { max-width: 1280px; margin: 0 auto; padding: 2rem; }
        .card { background: white; border-radius: 0.5rem; border: 1px solid #e2e8f0; padding: 1.5rem; margin-bottom: 1.5rem; }
        .card-title { font-size: 1rem; font-weight: 600; margin-bottom: 1rem; color: #374151; }
        .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .badge-A { background: #dcfce7; color: #15803d; }
        .badge-B { background: #dbeafe; color: #1d4ed8; }
        .badge-C { background: #fef9c3; color: #a16207; }
        .badge-D { background: #fed7aa; color: #c2410c; }
        .badge-F { background: #fee2e2; color: #b91c1c; }
        .btn { padding: 0.5rem 1rem; border-radius: 0.375rem; border: none; cursor: pointer; font-size: 0.875rem; font-weight: 500; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-secondary { background: #e2e8f0; color: #374151; }
        .btn-secondary:hover { background: #cbd5e1; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .score-large { font-size: 3rem; font-weight: 800; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        .grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
        @media (max-width: 768px) { .grid-3, .grid-4 { grid-template-columns: 1fr; } }
        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        th { text-align: left; padding: 0.75rem; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
        td { padding: 0.75rem; border-bottom: 1px solid #f1f5f9; }
        tr:hover td { background: #f8fafc; }
        .alert { padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 1rem; font-size: 0.875rem; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .text-muted { color: #94a3b8; font-size: 0.875rem; }
        .stat-value { font-size: 2rem; font-weight: 700; }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
    </style>
    @livewireStyles
</head>
<body>
    <nav class="navbar">
        <span class="navbar-brand">DB Cleaner</span>
        <a href="{{ route('db-cleaner.dashboard') }}">Dashboard</a>
    </nav>
    <div class="container">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
