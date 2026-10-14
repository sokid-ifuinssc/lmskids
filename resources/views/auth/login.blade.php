@extends('layouts.auth')

@section('title', 'Masuk ke Sistem Ujian')

@section('content')
    <div style="margin-bottom: 1.5rem; text-align: center;">
        <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 0.35rem; color: #1e293b;">Selamat Datang</h2>
        <p style="font-size: 0.85rem; color: #64748b; margin: 0;">Silakan masukkan NIM atau Username dan Kata Sandi</p>
    </div>

    @if(session('error'))
        <div class="alert alert-error" style="margin-bottom: 1.25rem;">
            <i class="ri-error-warning-fill"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST" autocomplete="off">
        @csrf

        <div class="form-group">
            <label for="username" class="form-label">
                NIM / NIP / Username <span class="req">*</span>
            </label>
            <div style="position: relative;">
                <input type="text"
                       name="username"
                       id="username"
                       class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}"
                       placeholder="Contoh: 2530801036 atau admin"
                       required
                       autofocus
                       style="padding-left: 2.4rem;">
                <i class="ri-user-line" style="position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem;"></i>
            </div>
            @error('username')
                <div class="invalid">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">
                Kata Sandi <span class="req">*</span>
            </label>
            <div style="position: relative;">
                <input type="password"
                       name="password"
                       id="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="Masukkan kata sandi..."
                       required
                       style="padding-left: 2.4rem; padding-right: 2.5rem;">
                <i class="ri-lock-password-line" style="position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem;"></i>
                <button type="button"
                        onclick="togglePassword()"
                        style="position: absolute; right: 0.6rem; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: #94a3b8; cursor: pointer; padding: 0.2rem;"
                        title="Tampilkan / Sembunyikan Password">
                    <i id="toggleIcon" class="ri-eye-line"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
            <label class="check">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <span style="font-size: 0.82rem; color: #475569;">Ingat Saya di Perangkat Ini</span>
            </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="box-shadow: 0 4px 14px rgba(15, 46, 90, 0.35);">
            <i class="ri-login-box-line"></i>
            <span>Masuk ke Akun</span>
        </button>
    </form>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.className = 'ri-eye-off-line';
            } else {
                pwd.type = 'password';
                icon.className = 'ri-eye-line';
            }
        }
    </script>
@endsection
