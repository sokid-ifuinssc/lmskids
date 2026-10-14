@extends('layouts.app')

@section('title', 'Kelola Ujian')
@section('page-title')
    <i class="ri-file-list-3-line" style="vertical-align: middle; margin-right: 0.35rem; color: #f59e0b;"></i> Kelola Ujian
@endsection
@section('page-subtitle', 'Buat & Kelola Jadwal Ujian Mahasiswa')

@section('content')
    <!-- Form Buat Ujian Baru (Sesuai Referensi) -->
    <div class="card card-accent" style="margin-bottom: 2rem;">
        <div class="card-header" style="border-bottom: 1px solid #f1f5f9;">
            <h2 style="font-size: 1.15rem; color: #1e293b; font-weight: 700;">
                <i class="ri-add-circle-fill" style="color: #4f46e5;"></i>
                <span>Buat Ujian Baru</span>
            </h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.ujian.store') }}" method="POST">
                @csrf

                <div class="grid grid-2" style="gap: 1.25rem;">
                    <!-- ID Ujian (Kode) -->
                    <div class="form-group mb-0">
                        <label for="kode" class="form-label">
                            ID Ujian (Kode) <span class="req">*</span>
                        </label>
                        <input type="text"
                               name="kode"
                               id="kode"
                               class="form-control @error('kode') is-invalid @enderror"
                               value="{{ old('kode', 'UJ-' . strtoupper(Str::random(5))) }}"
                               placeholder="Contoh: SD001 atau SNJ625006"
                               required>
                        @error('kode')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Mata Kuliah -->
                    <div class="form-group mb-0">
                        <label for="mata_kuliah_id" class="form-label">
                            Nama Mata Kuliah <span class="req">*</span>
                        </label>
                        <select name="mata_kuliah_id" id="mata_kuliah_id" class="form-select @error('mata_kuliah_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($mkList as $mk)
                                <option value="{{ $mk->id }}" {{ old('mata_kuliah_id') == $mk->id ? 'selected' : '' }}>
                                    {{ $mk->kode }} - {{ $mk->nama }} ({{ $mk->sks }} SKS)
                                </option>
                            @endforeach
                        </select>
                        @error('mata_kuliah_id')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Judul Ujian -->
                    <div class="form-group mb-0">
                        <label for="judul" class="form-label">
                            Judul / Deskripsi Singkat Ujian <span class="req">*</span>
                        </label>
                        <input type="text"
                               name="judul"
                               id="judul"
                               class="form-control @error('judul') is-invalid @enderror"
                               value="{{ old('judul') }}"
                               placeholder="Contoh: Ujian Simulasi Cyber Culture atau UTS Algoritma"
                               required>
                        @error('judul')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Durasi (Menit) -->
                    <div class="form-group mb-0">
                        <label for="durasi" class="form-label">
                            Durasi (Menit) <span class="req">*</span>
                        </label>
                        <input type="number"
                               name="durasi"
                               id="durasi"
                               class="form-control @error('durasi') is-invalid @enderror"
                               value="{{ old('durasi', 60) }}"
                               min="1"
                               max="600"
                               placeholder="60"
                               required>
                        @error('durasi')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Jenis Ujian & CPMK -->
                    <div class="form-group mb-0">
                        <label for="jenis" class="form-label">
                            Kategori Ujian <span class="req">*</span>
                        </label>
                        <div style="display: flex; gap: 0.6rem;">
                            <select name="jenis" id="jenis" class="form-select @error('jenis') is-invalid @enderror" onchange="toggleCpmkInput(this.value)" required style="flex: 1;">
                                <option value="CPMK" {{ old('jenis') == 'CPMK' ? 'selected' : '' }}>Ujian CPMK</option>
                                <option value="UTS" {{ old('jenis') == 'UTS' ? 'selected' : '' }}>UTS (Tengah Semester)</option>
                                <option value="UAS" {{ old('jenis') == 'UAS' ? 'selected' : '' }}>UAS (Akhir Semester)</option>
                            </select>
                            <div id="cpmkBox" style="width: 140px; {{ old('jenis') == 'UTS' || old('jenis') == 'UAS' ? 'display:none;' : '' }}">
                                <input type="number"
                                       name="cpmk_ke"
                                       id="cpmk_ke"
                                       class="form-control @error('cpmk_ke') is-invalid @enderror"
                                       value="{{ old('cpmk_ke', 1) }}"
                                       min="1"
                                       max="20"
                                       placeholder="CPMK Ke-">
                            </div>
                        </div>
                    </div>

                    <!-- Kelas/Semester Tujuan -->
                    <div class="form-group mb-0">
                        <label class="form-label">
                            Kelas / Semester Tujuan <span class="req">*</span>
                        </label>
                        <div class="check-grid" style="padding-top: 0.2rem;">
                            @foreach($kelasList as $k)
                                <label class="chip-check">
                                    <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}"
                                           {{ in_array($k->id, old('kelas_ids', [])) ? 'checked' : '' }}>
                                    <span>{{ $k->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('kelas_ids')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Waktu Mulai & Waktu Selesai -->
                    <div class="form-group mb-0">
                        <label for="waktu_mulai" class="form-label">
                            Waktu Mulai Ujian <span class="req">*</span>
                        </label>
                        <input type="datetime-local"
                               name="waktu_mulai"
                               id="waktu_mulai"
                               class="form-control @error('waktu_mulai') is-invalid @enderror"
                               value="{{ old('waktu_mulai', now()->format('Y-m-d\TH:i')) }}"
                               required>
                        @error('waktu_mulai')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <label for="waktu_selesai" class="form-label">
                            Waktu Selesai Ujian <span class="req">*</span>
                        </label>
                        <input type="datetime-local"
                               name="waktu_selesai"
                               id="waktu_selesai"
                               class="form-control @error('waktu_selesai') is-invalid @enderror"
                               value="{{ old('waktu_selesai', now()->addDays(2)->format('Y-m-d\TH:i')) }}"
                               required>
                        @error('waktu_selesai')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Opsi Tambahan & Anti-Cheat -->
                <div style="margin-top: 1.25rem; padding: 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center;">
                        <label class="check">
                            <input type="checkbox" name="acak_soal" value="1" {{ old('acak_soal', true) ? 'checked' : '' }}>
                            <span>Acak Urutan Soal Tiap Siswa</span>
                        </label>
                        <label class="check">
                            <input type="checkbox" name="tampilkan_nilai" value="1" {{ old('tampilkan_nilai', true) ? 'checked' : '' }}>
                            <span>Tampilkan Nilai Selesai Ujian</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                            <label class="form-label mb-0" style="font-size: 0.8rem; white-space: nowrap;">Batas Pelanggaran Tab:</label>
                            <input type="number" name="max_pelanggaran" value="{{ old('max_pelanggaran', 3) }}" min="1" max="10" class="form-control" style="width: 70px; min-height: 32px; padding: 0.2rem 0.5rem;">
                        </div>
                    </div>
                </div>

                <!-- Tombol Simpan Ujian -->
                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary btn-block btn-lg" style="box-shadow: 0 4px 14px rgba(15, 46, 90, 0.35);">
                        <i class="ri-check-line"></i>
                        <span>Simpan Ujian</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card Daftar Ujian Anda (Sesuai Referensi) -->
    <div class="card card-accent">
        <div class="card-header">
            <h2 style="font-size: 1.15rem; color: #1e293b; font-weight: 700;">
                <i class="ri-draft-line" style="color: #2563eb;"></i>
                <span>Daftar Ujian Anda</span>
            </h2>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <a href="{{ route('admin.ujian.index') }}" class="btn btn-outline btn-sm">
                    <i class="ri-refresh-line"></i> Refresh
                </a>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID UJIAN</th>
                        <th>NAMA MK</th>
                        <th>KATEGORI</th>
                        <th>DURASI</th>
                        <th>KELAS</th>
                        <th>WAKTU UJIAN</th>
                        <th>STATUS</th>
                        <th style="text-align: right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ujian as $u)
                        <tr>
                            <td>
                                <strong class="mono" style="color: #1e3a8a;">{{ $u->kode }}</strong>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $u->judul }}</div>
                                <div class="text-xs text-muted">{{ $u->mataKuliah->nama ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge badge-blue">
                                    {{ $u->label }}
                                </span>
                            </td>
                            <td>{{ $u->durasi }} menit</td>
                            <td>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.25rem;">
                                    @forelse($u->kelas as $k)
                                        <span class="badge" style="background: #e2e8f0; color: #334155;">{{ $k->nama }}</span>
                                    @empty
                                        <span class="text-muted text-xs">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td>
                                <div class="text-xs" style="font-weight: 500;">
                                    {{ $u->waktu_mulai ? $u->waktu_mulai->format('d/m/Y, H.i') : '-' }}
                                </div>
                                <div class="text-xs text-muted">
                                    s.d {{ $u->waktu_selesai ? $u->waktu_selesai->format('d/m/Y, H.i') : '-' }}
                                </div>
                            </td>
                            <td>
                                <form action="{{ route('admin.ujian.toggle', $u) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="btn btn-sm {{ $u->is_active ? 'btn-soft' : 'btn-outline' }}"
                                            style="padding: 0.2rem 0.6rem; border-radius: 999px;"
                                            title="Klik untuk {{ $u->is_active ? 'nonaktifkan' : 'aktifkan' }}">
                                        @if($u->is_active)
                                            <span class="badge badge-green" style="padding: 0;"><span class="dot"></span> Aktif</span>
                                        @else
                                            <span class="badge badge-red" style="padding: 0;"><span class="dot"></span> Non-Aktif</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="text-align: right;">
                                <div class="actions">
                                    <a href="{{ route('admin.ujian.soal', $u) }}" class="btn btn-sm btn-outline" title="Kelola Soal">
                                        <i class="ri-list-check"></i> Soal ({{ $u->soal_count }})
                                    </a>
                                    <a href="{{ route('admin.ujian.hasil', $u) }}" class="btn btn-sm btn-soft" title="Hasil & Nilai">
                                        <i class="ri-bar-chart-2-line"></i> Nilai ({{ $u->peserta_count }})
                                    </a>
                                    <a href="{{ route('admin.ujian.edit', $u) }}" class="btn-icon" title="Edit Ujian">
                                        <i class="ri-edit-line" style="color: #ea580c;"></i>
                                    </a>
                                    <form action="{{ route('admin.ujian.destroy', $u) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ujian ini beserta seluruh riwayat nilainya?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon danger" title="Hapus Ujian">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">
                                <i class="ri-inbox-line" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.5rem;"></i>
                                Belum ada ujian. Silakan buat ujian baru pada formulir di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ujian->hasPages())
            <div class="pager">
                <div class="info">
                    Menampilkan {{ $ujian->firstItem() }} - {{ $ujian->lastItem() }} dari {{ $ujian->total() }} ujian
                </div>
                {{ $ujian->links() }}
            </div>
        @endif
    </div>

    <script>
        function toggleCpmkInput(val) {
            const box = document.getElementById('cpmkBox');
            if (val === 'CPMK') {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }
    </script>
@endsection
