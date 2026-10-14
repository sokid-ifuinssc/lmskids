@extends('layouts.app')

@section('title', 'Data Pengguna')
@section('page-title')
    <i class="ri-group-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #4f46e5;"></i> Data Pengguna
@endsection
@section('page-subtitle', 'Daftar dosen dan mahasiswa yang terdaftar di sistem')

@section('content')
    <div class="card card-accent">
        <div class="card-header">
            <div>
                <h2 style="font-size: 1.15rem; color: #1e293b; font-weight: 700; margin: 0 0 0.2rem;">
                    <i class="ri-team-line" style="color: #4f46e5;"></i>
                    <span>Data Pengguna</span>
                </h2>
                <div class="sub">Daftar dosen dan mahasiswa yang terdaftar di sistem</div>
            </div>

            <!-- Filters & Actions Matching Reference Screenshot -->
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <form action="{{ route('admin.mahasiswa.index') }}" method="GET" style="display: flex; gap: 0.4rem; align-items: center;">
                    <select name="kelas" class="form-select" onchange="this.form.submit()" style="min-width: 140px; min-height: 36px; font-size: 0.85rem;">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ request('kelas') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama }} ({{ $k->mahasiswa_count }})
                            </option>
                        @endforeach
                    </select>

                    <div class="search">
                        <i class="ri-search-line"></i>
                        <input type="text" name="q" class="form-control" placeholder="Cari NIM / Nama..." value="{{ request('q') }}" style="min-height: 36px; font-size: 0.85rem; width: 170px;">
                    </div>

                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline btn-sm" title="Refresh Data">
                        <i class="ri-refresh-line"></i> Refresh
                    </a>
                </form>

                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('modalImportMhs').showModal()" title="Import Mahasiswa dari File Excel">
                    <i class="ri-file-excel-2-line" style="color: #10b981;"></i> Import Excel
                </button>
                <a href="{{ route('admin.mahasiswa.export', ['kelas' => request('kelas')]) }}" class="btn btn-outline btn-sm" title="Export Excel">
                    <i class="ri-download-2-line"></i> Export
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('modalTambahMhs').showModal()">
                    <i class="ri-user-add-line"></i> Tambah Mahasiswa
                </button>
            </div>
        </div>

        <!-- Table of Users Matching Reference Screenshot media_1791971082538.png -->
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 170px;">NIM/NIP</th>
                        <th>NAMA LENGKAP</th>
                        <th style="width: 140px;">ROLE</th>
                        <th style="width: 170px;">KELAS/SEMESTER</th>
                        <th style="width: 100px; text-align: right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswa as $m)
                        <tr>
                            <td>
                                <strong class="mono" style="color: #1d4ed8; font-size: 0.9rem;">
                                    {{ $m->username }}
                                </strong>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $m->name }}</div>
                            </td>
                            <td>
                                <span class="badge badge-blue" style="font-size: 0.74rem; text-transform: lowercase;">
                                    {{ $m->role }}
                                </span>
                            </td>
                            <td>
                                @if($m->kelas)
                                    <span style="font-weight: 600; color: #334155;">{{ $m->kelas->nama }}</span>
                                @else
                                    <span class="text-xs text-muted">-</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div class="actions">
                                    <a href="{{ route('admin.mahasiswa.edit', $m) }}" class="btn-icon" title="Edit Mahasiswa">
                                        <i class="ri-edit-line" style="color: #ea580c;"></i>
                                    </a>
                                    <form action="{{ route('admin.mahasiswa.destroy', $m) }}" method="POST" onsubmit="return confirm('Hapus mahasiswa {{ $m->name }} ({{ $m->username }})?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon danger" title="Hapus Mahasiswa">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">
                                <i class="ri-team-line" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.5rem;"></i>
                                Tidak ada data pengguna ditemukan. Silakan tambahkan manual atau import Excel.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mahasiswa->hasPages())
            <div class="pager">
                <div class="info">
                    Menampilkan {{ $mahasiswa->firstItem() }} - {{ $mahasiswa->lastItem() }} dari {{ $mahasiswa->total() }} pengguna
                </div>
                {{ $mahasiswa->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Mahasiswa Manual -->
    <dialog class="modal" id="modalTambahMhs">
        <div class="modal-header">
            <h3><i class="ri-user-add-line" style="color: #4f46e5;"></i> Tambah Mahasiswa</h3>
            <button type="button" class="btn-icon" onclick="this.closest('dialog').close()">&times;</button>
        </div>
        <form action="{{ route('admin.mahasiswa.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="mhs_username" class="form-label">NIM (Sebagai Username) <span class="req">*</span></label>
                    <input type="text" name="username" id="mhs_username" class="form-control" placeholder="Contoh: 2530801036" required>
                    <div class="form-hint">NIM digunakan oleh mahasiswa untuk masuk ke sistem ujian.</div>
                </div>

                <div class="form-group">
                    <label for="mhs_name" class="form-label">Nama Lengkap Mahasiswa <span class="req">*</span></label>
                    <input type="text" name="name" id="mhs_name" class="form-control" placeholder="Contoh: Ananda Rizqi Kurniawan" required>
                </div>

                <div class="form-group">
                    <label for="mhs_password" class="form-label">Kata Sandi Default <span class="req">*</span></label>
                    <input type="text" name="password" id="mhs_password" class="form-control" value="123456" required>
                </div>

                <div class="form-group mb-0">
                    <label for="mhs_kelas" class="form-label">Kelas / Semester <span class="req">*</span></label>
                    <input type="text" name="kelas" id="mhs_kelas" class="form-control" list="listKelasOptions" placeholder="Contoh: 2B atau Semester PBA-3ABC" required>
                    <datalist id="listKelasOptions">
                        @foreach($kelasList as $k)
                            <option value="{{ $k->nama }}">
                        @endforeach
                    </datalist>
                    <div class="form-hint">Ketikkan nama kelas. Jika belum ada di sistem, kelas akan otomatis dibuat.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Mahasiswa</button>
            </div>
        </form>
    </dialog>

    <!-- Modal Import Mahasiswa Excel (Sesuai Referensi media_1791971023280.png) -->
    <dialog class="modal modal-lg" id="modalImportMhs">
        <div class="modal-header">
            <h3><i class="ri-file-excel-fill" style="color: #10b981;"></i> Import Data Mahasiswa dari Excel</h3>
            <button type="button" class="btn-icon" onclick="this.closest('dialog').close()">&times;</button>
        </div>
        <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
                    Upload file Excel berisi daftar mahasiswa. Akun mahasiswa dan kelas akan otomatis dibuat jika belum terdaftar.
                </p>

                <!-- Format File Excel yang Diperlukan (Sesuai Referensi) -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="font-weight: 600; font-size: 0.82rem; color: #166534; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                        <i class="ri-lightbulb-line"></i> Format Kolom Excel yang Diperlukan:
                    </div>
                    <div class="code-tags">
                        <code>nim</code>
                        <code>nama</code>
                        <code>password</code>
                        <code>kelas</code>
                    </div>
                </div>

                <!-- Drag & Drop Upload Zone -->
                <div class="dropzone" id="dropzoneMhs" onclick="document.getElementById('fileMhs').click()">
                    <i class="ri-folder-upload-fill" style="color: #f59e0b; font-size: 3rem;"></i>
                    <div style="font-weight: 700; color: #1e293b; margin-top: 0.5rem; font-size: 1.05rem;">
                        Drag & Drop atau Klik untuk Upload
                    </div>
                    <div class="text-xs text-muted" style="margin-top: 0.25rem;">Format didukung: .xlsx atau .xls (Maks 5MB)</div>
                    <div class="fname" id="fileMhsName"></div>
                    <input type="file" name="file" id="fileMhs" accept=".xlsx,.xls,.csv" required onchange="displayFilename(this, 'fileMhsName')">
                </div>

                <!-- Download Template Button -->
                <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; justify-content: center;">
                    <a href="{{ route('admin.mahasiswa.template') }}" class="btn btn-outline btn-sm">
                        <i class="ri-download-cloud-2-line"></i> Download Template Excel
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-upload-2-line"></i> Upload & Import
                </button>
            </div>
        </form>
    </dialog>

    <script>
        function displayFilename(input, targetId) {
            if (input.files && input.files[0]) {
                document.getElementById(targetId).textContent = '📄 ' + input.files[0].name;
            }
        }
    </script>
@endsection
