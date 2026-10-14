<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') — LMS Ujian Online</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top left, #1e3a8a 0%, #0f2e5a 60%, #0a1f3d 100%);
            padding: 1.5rem;
        }
        .auth-container {
            width: 100%;
            max-width: 440px;
        }
        .auth-brand {
            text-align: center;
            margin-bottom: 1.8rem;
            color: #fff;
        }
        .auth-brand .logo-circle {
            width: 60px;
            height: 60px;
            margin: 0 auto 0.75rem;
            border-radius: 16px;
            background: linear-gradient(135deg, #10b981, #047857);
            display: grid;
            place-items: center;
            font-size: 2rem;
            color: #fff;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
        }
        .auth-brand h1 {
            color: #fff;
            font-size: 1.6rem;
            margin: 0;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .auth-brand p {
            color: #94a3b8;
            font-size: 0.88rem;
            margin: 0.25rem 0 0;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 2.2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            border-top: 4px solid #f59e0b;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-brand">
            <div class="logo-circle">
                <i class="ri-graduation-cap-fill"></i>
            </div>
            <h1>LMS Ujian Online</h1>
        </div>

        <div class="auth-card">
            @yield('content')
        </div>
    </div>
</body>
</html>
