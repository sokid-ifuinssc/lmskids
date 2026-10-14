@extends('layouts.app')

@php
    $isEdit = $soal->exists;
    $title = $isEdit ? 'Edit Soal' : 'Tambah Soal Baru';
@endphp

@section('title', $title)
@section('page-title')
    <i class="ri-question-line" style="vertical-align: middle; margin-right: 0.35rem; color: #10b981;"></i> {{ $title }}
@endsection
@section('page-subtitle', $isEdit ? 'Perbarui Soal Bank' : 'Simpan Soal ke Bank Soal')

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <div class="card card-accent">
        <div class="card-header">
            <h2>
                <i class="ri-edit-2-line" style="color: #4f46e5;"></i>
                <span>{{ $title }}</span>
            </h2>
            <a href="{{ route('admin.soal.index', ['mk' => $soal->mata_kuliah_id]) }}" class="btn btn-sm btn-outline">
                <i class="ri-arrow-left-line"></i> Kembali ke Bank Soal
            </a>
        </div>
        <div class="card-body" style="padding: 1.75rem;">
            <form action="{{ $isEdit ? route('admin.soal.update', $soal) : route('admin.soal.store') }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="grid grid-2" style="gap: 1.25rem;">
                    <!-- Mata Kuliah -->
                    <div class="form-group mb-0">
                        <label for="mata_kuliah_id" class="form-label">
                            Mata Kuliah <span class="req">*</span>
                        </label>
                        <select name="mata_kuliah_id" id="mata_kuliah_id" class="form-select @error('mata_kuliah_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($mkList as $mk)
                                <option value="{{ $mk->id }}" {{ old('mata_kuliah_id', $soal->mata_kuliah_id) == $mk->id ? 'selected' : '' }}>
                                    {{ $mk->kode }} - {{ $mk->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('mata_kuliah_id')
                            <div class="invalid">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tipe Soal (PG vs Essay) -->
                    <div class="form-group mb-0">
                        <label class="form-label">Jenis / Tipe Soal <span class="req">*</span></label>
                        <div class="seg" style="width: 100%; display: flex;">
                            <label style="flex: 1; text-align: center;">
                                <input type="radio" name="tipe" value="pg"
                                       {{ old('tipe', $soal->tipe ?? 'pg') === 'pg' ? 'checked' : '' }}
                                       onchange="toggleSoalType(this.value)">
                                <span style="display: block;">Pilihan Ganda (4 Opsi)</span>
                            </label>
                            <label style="flex: 1; text-align: center;">
                                <input type="radio" name="tipe" value="essay"
                                       {{ old('tipe', $soal->tipe ?? 'pg') === 'essay' ? 'checked' : '' }}
                                       onchange="toggleSoalType(this.value)">
                                <span style="display: block;">Essay (Kata Kunci)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Isi Pertanyaan -->
                <div class="form-group" style="margin-top: 1.25rem;">
                    <label for="pertanyaan" class="form-label">
                        Pertanyaan Soal <span class="req">*</span>
                    </label>
                    <textarea name="pertanyaan"
                              id="pertanyaan"
                              class="form-control @error('pertanyaan') is-invalid @enderror"
                              rows="4"
                              placeholder="Tuliskan teks pertanyaan soal..."
                              required>{{ old('pertanyaan', $soal->pertanyaan) }}</textarea>
                    @error('pertanyaan')
                        <div class="invalid">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Container Pilihan Ganda -->
                <div id="pgBox" style="{{ old('tipe', $soal->tipe ?? 'pg') === 'essay' ? 'display: none;' : '' }}">
                    <div style="font-weight: 700; font-size: 0.9rem; color: #1e293b; margin: 1.25rem 0 0.75rem; display: flex; align-items: center; gap: 0.35rem;">
                        <i class="ri-list-check-2" style="color: #2563eb;"></i> 4 Pilihan Jawaban & Kunci
                    </div>

                    <div class="grid grid-2" style="gap: 1rem;">
                        <div class="form-group mb-2">
                            <label class="form-label">Pilihan A <span class="req">*</span></label>
                            <input type="text" name="opsi_a" class="form-control @error('opsi_a') is-invalid @enderror"
                                   value="{{ old('opsi_a', $soal->opsi_a) }}" placeholder="Isi pilihan A...">
                            @error('opsi_a') <div class="invalid">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label class="form-label">Pilihan B <span class="req">*</span></label>
                            <input type="text" name="opsi_b" class="form-control @error('opsi_b') is-invalid @enderror"
                                   value="{{ old('opsi_b', $soal->opsi_b) }}" placeholder="Isi pilihan B...">
                            @error('opsi_b') <div class="invalid">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label class="form-label">Pilihan C <span class="req">*</span></label>
                            <input type="text" name="opsi_c" class="form-control @error('opsi_c') is-invalid @enderror"
                                   value="{{ old('opsi_c', $soal->opsi_c) }}" placeholder="Isi pilihan C...">
                            @error('opsi_c') <div class="invalid">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label class="form-label">Pilihan D <span class="req">*</span></label>
                            <input type="text" name="opsi_d" class="form-control @error('opsi_d') is-invalid @enderror"
                                   value="{{ old('opsi_d', $soal->opsi_d) }}" placeholder="Isi pilihan D...">
                            @error('opsi_d') <div class="invalid">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.5rem;">
                        <label for="kunci" class="form-label">
                            Kunci Jawaban yang Benar <span class="req">*</span>
                        </label>
                        <select name="kunci" id="kunci" class="form-select @error('kunci') is-invalid @enderror" style="max-width: 220px;">
                            <option value="">-- Pilih Kunci Benar --</option>
                            <option value="A" {{ strtoupper((string) old('kunci', $soal->kunci)) === 'A' ? 'selected' : '' }}>Pilihan A</option>
                            <option value="B" {{ strtoupper((string) old('kunci', $soal->kunci)) === 'B' ? 'selected' : '' }}>Pilihan B</option>
                            <option value="C" {{ strtoupper((string) old('kunci', $soal->kunci)) === 'C' ? 'selected' : '' }}>Pilihan C</option>
                            <option value="D" {{ strtoupper((string) old('kunci', $soal->kunci)) === 'D' ? 'selected' : '' }}>Pilihan D</option>
                        </select>
                        @error('kunci') <div class="invalid">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Container Essay -->
                <div id="essayBox" style="{{ old('tipe', $soal->tipe ?? 'pg') === 'essay' ? '' : 'display: none;' }}">
                    <div style="font-weight: 700; font-size: 0.9rem; color: #1e293b; margin: 1.25rem 0 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                        <i class="ri-key-fill" style="color: #f59e0b;"></i> Kata Kunci Penilaian Otomatis Essay
                    </div>

                    <div class="form-group">
                        <label for="kata_kunci" class="form-label">
                            Daftar Kata Kunci <span class="req">*</span>
                        </label>
                        <textarea name="kata_kunci"
                                  id="kata_kunci"
                                  class="form-control @error('kata_kunci') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Contoh: internet, privasi, keamanan, enkripsi, autentikasi">{{ old('kata_kunci', $soal->kata_kunci) }}</textarea>
                        <div class="form-hint">
                            Pisahkan setiap kata kunci dengan koma (,). Sistem akan otomatis menghitung persentase kata kunci yang terkandung dalam jawaban mahasiswa untuk menghasilkan poin skor. Dosen juga tetap dapat mengoreksi/menyesuaikan nilai secara manual.
                        </div>
                        @error('kata_kunci') <div class="invalid">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Bobot Poin -->
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="bobot" class="form-label">
                        Bobot Poin Soal <span class="req">*</span>
                    </label>
                    <input type="number"
                           name="bobot"
                           id="bobot"
                           class="form-control @error('bobot') is-invalid @enderror"
                           value="{{ old('bobot', $soal->bobot ?? 10) }}"
                           min="1"
                           max="100"
                           style="max-width: 160px;"
                           required>
                    <div class="form-hint">Poin maksimal yang diperoleh jika soal dijawab dengan benar sempurna.</div>
                    @error('bobot') <div class="invalid">{{ $message }}</div> @enderror
                </div>

                @if(! $isEdit)
                    <div style="margin-top: 1.25rem;">
                        <label class="check">
                            <input type="checkbox" name="lagi" value="1" {{ old('lagi') ? 'checked' : '' }}>
                            <span>Simpan dan langsung tambah soal berikutnya</span>
                        </label>
                    </div>
                @endif

                <div style="margin-top: 2rem; display: flex; gap: 0.75rem;">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                        <i class="ri-save-line"></i> {{ $isEdit ? 'Perbarui Soal' : 'Simpan ke Bank Soal' }}
                    </button>
                    <a href="{{ route('admin.soal.index', ['mk' => $soal->mata_kuliah_id]) }}" class="btn btn-outline btn-lg">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleSoalType(val) {
        document.getElementById('pgBox').style.display = (val === 'pg') ? 'block' : 'none';
        document.getElementById('essayBox').style.display = (val === 'essay') ? 'block' : 'none';
    }
</script>
@endsection
