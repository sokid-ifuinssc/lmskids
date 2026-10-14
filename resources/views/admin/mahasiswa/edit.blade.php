@extends('layouts.app')

@section('title', 'Edit Mahasiswa: ' . $mhs->name)
@section('page-title')
    <i class="ri-user-settings-line" style="vertical-align: middle; margin-right: 0.35rem; color: #ea580c;"></i> Edit Data Mahasiswa
@endsection
@section('page-subtitle', $mhs->name . ' (' . $mhs->username . ')')

@section('content')
<div style="max-width: 600px; margin: 0 auto;">
    <div class="card card-accent">
        <div class="card-header">
            <h2>
                <i class="ri-user-line" style="color: #4f46e5;"></i>
                <span>Perbarui Data Mahasiswa</span>
            </h2>
            <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-sm btn-outline">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
        </div>
        <div class="card-body" style="padding: 1.75rem;">
            <form action="{{ route('admin.mahasiswa.update', $mhs) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="username" class="form-label">NIM (Username) <span class="req">*</span></label>
                    <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username', $mhs->username) }}" required>
                    @error('username') <div class="invalid">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="name" class="form-label">Nama Lengkap <span class="req">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $mhs->name) }}" required>
                    @error('name') <div class="invalid">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="kelas" class="form-label">Kelas / Semester <span class="req">*</span></label>
                    <input type="text" name="kelas" id="kelas" class="form-control @error('kelas') is-invalid @enderror"
                           list="listKelasEdit" value="{{ old('kelas', $mhs->kelas?->nama) }}" required>
                    <datalist id="listKelasEdit">
                        @foreach($kelasList as $k)
                            <option value="{{ $k->nama }}">
                        @endforeach
                    </datalist>
                    @error('kelas') <div class="invalid">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Reset Kata Sandi</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan jika tidak ingin mengubah password">
                    <div class="form-hint">Isi jika mahasiswa lupa kata sandi akunnya.</div>
                    @error('password') <div class="invalid">{{ $message }}</div> @enderror
                </div>

                <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                        <i class="ri-save-line"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline btn-lg">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
