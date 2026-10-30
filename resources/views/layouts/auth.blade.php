<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Masuk') — LMS Kids</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #ffffff 0%, #f4f9f6 55%, #fbf3f5 100%);
            padding: 1.5rem;
        }
        .auth-container {
            width: 100%;
            max-width: 440px;
        }
        .auth-brand {
            text-align: center;
            margin-bottom: 1.8rem;
            color: var(--gray-900);
        }
        .auth-brand .logo-img {
            width: 100%;
            max-width: 320px;
            height: auto;
            display: block;
            margin: 0 auto 0.5rem;
        }
        .auth-brand p {
            color: var(--gray-500);
            font-size: 0.88rem;
            margin: 0.25rem 0 0;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 2.2rem;
            box-shadow: 0 20px 40px rgba(47, 111, 79, 0.12);
            border: 1px solid var(--gray-200);
            border-top: 4px solid var(--maroon-700);
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-brand">
            <img class="logo-img" src="{{ asset('images/logo.png') }}" alt="LMS Kids by SOKID">
        </div>

        <div class="auth-card">
            @yield('content')
        </div>
    </div>
</body>
</html>
