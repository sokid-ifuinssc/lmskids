@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title')
    <i class="ri-user-settings-line" style="vertical-align: middle; margin-right: 0.35rem;"></i> Profil Saya
@endsection
@section('page-subtitle', 'Pengaturan Akun & Kata Sandi')

@section('content')
<div style="max-width: 620px; margin: 1.5rem auto;">
    <div class="card card-accent" style="box-shadow: 0 10px 30px rgba(0,0,0,0.06); border-radius: 20px;">
        <div class="card-body" style="padding: 2.2rem;">
            <!-- Header Profil Icon -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 72px; height: 72px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #4f46e5); color: #fff; display: grid; place-items: center; font-size: 2.4rem; box-shadow: 0 8px 20px rgba(124, 58, 237, 0.3);">
                    <i class="ri-user-fill"></i>
                </div>
                <h2 style="font-size: 1.35rem; font-weight: 700; margin: 0 0 0.35rem; color: #1e293b;">
                    Profil Saya ({{ $user->isAdmin() ? 'Dosen' : 'Mahasiswa' }})
                </h2>
                <p style="font-size: 0.85rem; color: #64748b; margin: 0;">
                    Perbarui nama atau ubah kata sandi akun Anda
                </p>
            </div>

            <form action="{{ route('profil.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- NIP / Username (Readonly) -->
                <div class="form-group">
                    <label class="form-label">
                        {{ $user->isAdmin() ? 'NIP / Username' : 'NIM (Nomor Induk Mahasiswa)' }}
                    </label>
                    <input type="text"
                           class="form-control"
                           value="{{ $user->username }}"
                           readonly
                           disabled
                           style="background: #f1f5f9; color: #64748b; font-weight: 600;">
                    <div class="form-hint">{{ $user->isAdmin() ? 'NIP digunakan sebagai identitas akun pengajar.' : 'NIM terdaftar tidak dapat diubah.' }}</div>
                </div>

                @if($user->isMahasiswa() && $user->kelas)
                    <div class="form-group">
                        <label class="form-label">Kelas / Semester</label>
                        <input type="text"
                               class="form-control"
                               value="{{ $user->kelas->nama }}"
                               readonly
                               disabled
                               style="background: #f1f5f9; color: #64748b; font-weight: 600;">
                    </div>
                @endif

                <!-- Nama Lengkap -->
                <div class="form-group">
                    <label for="name" class="form-label">
                        Nama Lengkap <span class="req">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}"
                           {{ $user->isAdmin() ? 'required' : 'readonly style=background:#f1f5f9;' }}>
                    @error('name')
                        <div class="invalid">{{ $message }}</div>
                    @enderror
                </div>

                <hr style="border: 0; border-top: 1px dashed #e2e8f0; margin: 1.75rem 0 1.25rem;">

                <div style="font-weight: 700; font-size: 0.95rem; color: #1e293b; margin-bottom: 0.35rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="ri-lock-2-line" style="color: #f59e0b;"></i> Ubah Kata Sandi
                </div>
                <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 1.25rem;">
                    Kosongkan kolom di bawah jika tidak ingin mengganti kata sandi akun.
                </div>

                <!-- Password Lama -->
                <div class="form-group">
                    <label for="password_lama" class="form-label">Kata Sandi Lama</label>
                    <input type="password"
                           name="password_lama"
                           id="password_lama"
                           class="form-control @error('password_lama') is-invalid @enderror"
                           placeholder="Masukkan kata sandi saat ini jika ingin mengubah">
                    @error('password_lama')
                        <div class="invalid">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password Baru -->
                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi Baru</label>
                    <input type="password"
                           name="password"
                           id="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Kosongkan jika tidak ingin diubah...">
                    @error('password')
                        <div class="invalid">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Konfirmasi Password Baru -->
                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Ulangi Kata Sandi Baru</label>
                    <input type="password"
                           name="password_confirmation"
                           id="password_confirmation"
                           class="form-control"
                           placeholder="Ulangi kata sandi baru untuk verifikasi">
                </div>

                <div style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="box-shadow: 0 4px 14px rgba(15, 46, 90, 0.3);">
                        <i class="ri-save-line"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
