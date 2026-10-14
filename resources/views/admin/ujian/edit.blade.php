@extends('layouts.app')

@section('title', 'Edit Ujian: ' . $ujian->kode)
@section('page-title')
    <i class="ri-edit-line" style="vertical-align: middle; margin-right: 0.35rem; color: #ea580c;"></i> Edit Ujian
@endsection
@section('page-subtitle', $ujian->judul . ' (' . $ujian->kode . ')')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <div class="card card-accent">
        <div class="card-header">
            <h2>
                <i class="ri-settings-4-line" style="color: #4f46e5;"></i>
                <span>Perbarui Data Ujian</span>
            </h2>
            <a href="{{ route('admin.ujian.index') }}" class="btn btn-sm btn-outline">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.ujian.update', $ujian) }}" method="POST">
                @csrf
                @method('PUT')

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
                               value="{{ old('kode', $ujian->kode) }}"
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
                            @foreach($mkList as $mk)
                                <option value="{{ $mk->id }}" {{ old('mata_kuliah_id', $ujian->mata_kuliah_id) == $mk->id ? 'selected' : '' }}>
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
                            Judul Ujian <span class="req">*</span>
                        </label>
                        <input type="text"
                               name="judul"
                               id="judul"
                               class="form-control @error('judul') is-invalid @enderror"
                               value="{{ old('judul', $ujian->judul) }}"
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
                               value="{{ old('durasi', $ujian->durasi) }}"
                               min="1"
                               max="600"
                               required>
                        @error('durasi')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Kategori Ujian & CPMK -->
                    <div class="form-group mb-0">
                        <label for="jenis" class="form-label">
                            Kategori Ujian <span class="req">*</span>
                        </label>
                        <div style="display: flex; gap: 0.6rem;">
                            <select name="jenis" id="jenis" class="form-select" onchange="toggleCpmk(this.value)" required style="flex: 1;">
                                <option value="CPMK" {{ old('jenis', $ujian->jenis) == 'CPMK' ? 'selected' : '' }}>Ujian CPMK</option>
                                <option value="UTS" {{ old('jenis', $ujian->jenis) == 'UTS' ? 'selected' : '' }}>UTS</option>
                                <option value="UAS" {{ old('jenis', $ujian->jenis) == 'UAS' ? 'selected' : '' }}>UAS</option>
                            </select>
                            <div id="cpmkBox" style="width: 140px; {{ old('jenis', $ujian->jenis) != 'CPMK' ? 'display:none;' : '' }}">
                                <input type="number"
                                       name="cpmk_ke"
                                       id="cpmk_ke"
                                       class="form-control"
                                       value="{{ old('cpmk_ke', $ujian->cpmk_ke ?: 1) }}"
                                       min="1"
                                       max="20"
                                       placeholder="CPMK Ke-">
                            </div>
                        </div>
                    </div>

                    <!-- Kelas Tujuan -->
                    <div class="form-group mb-0">
                        <label class="form-label">
                            Kelas / Semester Tujuan <span class="req">*</span>
                        </label>
                        @php
                            $selectedKelas = old('kelas_ids', $ujian->kelas->pluck('id')->all());
                        @endphp
                        <div class="check-grid" style="padding-top: 0.2rem;">
                            @foreach($kelasList as $k)
                                <label class="chip-check">
                                    <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}"
                                           {{ in_array($k->id, $selectedKelas) ? 'checked' : '' }}>
                                    <span>{{ $k->nama }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Waktu Mulai & Waktu Selesai -->
                    <div class="form-group mb-0">
                        <label for="waktu_mulai" class="form-label">Waktu Mulai <span class="req">*</span></label>
                        <input type="datetime-local"
                               name="waktu_mulai"
                               id="waktu_mulai"
                               class="form-control"
                               value="{{ old('waktu_mulai', $ujian->waktu_mulai ? $ujian->waktu_mulai->format('Y-m-d\TH:i') : '') }}"
                               required>
                    </div>

                    <div class="form-group mb-0">
                        <label for="waktu_selesai" class="form-label">Waktu Selesai <span class="req">*</span></label>
                        <input type="datetime-local"
                               name="waktu_selesai"
                               id="waktu_selesai"
                               class="form-control"
                               value="{{ old('waktu_selesai', $ujian->waktu_selesai ? $ujian->waktu_selesai->format('Y-m-d\TH:i') : '') }}"
                               required>
                    </div>
                </div>

                <!-- Opsi Tambahan -->
                <div style="margin-top: 1.25rem; padding: 1rem; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: center;">
                        <label class="check">
                            <input type="checkbox" name="acak_soal" value="1" {{ old('acak_soal', $ujian->acak_soal) ? 'checked' : '' }}>
                            <span>Acak Urutan Soal Tiap Siswa</span>
                        </label>
                        <label class="check">
                            <input type="checkbox" name="tampilkan_nilai" value="1" {{ old('tampilkan_nilai', $ujian->tampilkan_nilai) ? 'checked' : '' }}>
                            <span>Tampilkan Nilai Selesai Ujian</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                            <label class="form-label mb-0" style="font-size: 0.8rem;">Batas Pelanggaran Tab:</label>
                            <input type="number" name="max_pelanggaran" value="{{ old('max_pelanggaran', $ujian->max_pelanggaran) }}" min="1" max="10" class="form-control" style="width: 70px; min-height: 32px; padding: 0.2rem 0.5rem;">
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                        <i class="ri-save-line"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.ujian.index') }}" class="btn btn-outline btn-lg">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleCpmk(val) {
        document.getElementById('cpmkBox').style.display = (val === 'CPMK') ? 'block' : 'none';
    }
</script>
@endsection
