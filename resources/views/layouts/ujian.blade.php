<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ujian Berlangsung') — UIN Siber</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            user-select: none;
            -webkit-user-select: none;
        }
        .exam-header {
            background: #0f2e5a;
            color: #fff;
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        .exam-title-box h1 {
            font-size: 1.05rem;
            color: #fff;
            margin: 0;
            font-weight: 700;
        }
        .exam-title-box .sub {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .timer-badge {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.4rem 1rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-family: ui-monospace, Consolas, monospace;
            font-size: 1.25rem;
            font-weight: 700;
            color: #38bdf8;
            letter-spacing: 0.05em;
        }
        .timer-badge.urgent {
            background: #ef4444;
            color: #fff;
            border-color: #dc2626;
            animation: pulseUrgent 1s infinite;
        }
        @keyframes pulseUrgent {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.02); }
        }
        .sync-badge {
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            font-weight: 500;
            transition: all 0.2s;
        }
        .sync-badge.saving {
            background: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }
        .nav-palette {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.45rem;
        }
        .btn-num {
            width: 100%;
            aspect-ratio: 1;
            padding: 0;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.88rem;
            display: grid;
            place-items: center;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
            transition: all 0.15s;
        }
        .btn-num:hover {
            border-color: #3b82f6;
            color: #1d4ed8;
        }
        .btn-num.active {
            border: 2px solid #2563eb !important;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.25);
        }
        .btn-num.answered {
            background: #10b981;
            border-color: #059669;
            color: #fff;
        }
        .btn-num.answered.active {
            border-color: #0f172a !important;
        }
        .opt-label {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.9rem 1.1rem;
            border-radius: 12px;
            border: 1.5px solid #e2e8f0;
            background: #fff;
            cursor: pointer;
            margin-bottom: 0.65rem;
            transition: all 0.15s ease;
        }
        .opt-label:hover {
            border-color: #93c5fd;
            background: #f8fafc;
        }
        .opt-label input[type="radio"] {
            margin-top: 3px;
            width: 18px;
            height: 18px;
            accent-color: #2563eb;
        }
        .opt-label.selected {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 2px 8px rgba(37,99,235,0.12);
        }
        .opt-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            font-size: 0.85rem;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .opt-label.selected .opt-circle {
            background: #2563eb;
            color: #fff;
        }
        @media (max-width: 900px) {
            .exam-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
