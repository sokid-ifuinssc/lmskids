@extends('layouts.app')

@section('title', 'Kelola Soal: ' . $ujian->judul)
@section('page-title')
    <i class="ri-list-check" style="vertical-align: middle; margin-right: 0.35rem; color: #2563eb;"></i> Kelola Soal Ujian
@endsection
@section('page-subtitle', $ujian->judul . ' (' . $ujian->kode . ')')

@section('content')
    <!-- Exam Header Info Bar -->
    <div class="card" style="margin-bottom: 1.25rem; background: #ffffff; border-left: 4px solid #10b981;">
        <div class="card-body" style="padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <h2 style="font-size: 1.15rem; margin: 0; color: #0f172a;">{{ $ujian->judul }}</h2>
                    <span class="badge badge-blue">{{ $ujian->label }}</span>
                    @if($ujian->is_active)
                        <span class="badge badge-green"><span class="dot"></span> Aktif</span>
                    @else
                        <span class="badge badge-red"><span class="dot"></span> Non-Aktif</span>
                    @endif
                </div>
                <div class="text-xs text-muted" style="margin-top: 0.25rem;">
                    Mata Kuliah: <strong>{{ $ujian->mataKuliah->nama }}</strong> &bull;
                    Durasi: <strong>{{ $ujian->durasi }} Menit</strong> &bull;
                    Total Soal Terpasang: <strong style="color: #2563eb;">{{ $ujian->soal->count() }} Soal</strong>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('modalGenerate').showModal()">
                    <i class="ri-magic-line" style="color: #8b5cf6;"></i> Generate Acak dari Bank
                </button>
                <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('modalInputBaru').showModal()">
                    <i class="ri-add-line"></i> Input Soal Baru
                </button>
                <a href="{{ route('admin.ujian.index') }}" class="btn btn-outline btn-sm">
                    <i class="ri-arrow-left-line"></i> Selesai / Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-2" style="align-items: start;">
        <!-- Left Column: Soal yang Sudah Terpasang -->
        <div class="card card-accent">
            <div class="card-header">
                <h2>
                    <i class="ri-checkbox-multiple-line" style="color: #10b981;"></i>
                    <span>Soal Terpasang ({{ $ujian->soal->count() }})</span>
                </h2>
                @if($ujian->soal->count() > 0)
                    <form action="{{ route('admin.ujian.soal.detach-all', $ujian) }}" method="POST" onsubmit="return confirm('Kosongkan semua soal dari ujian ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-ghost text-red" style="font-size: 0.76rem;">
                            <i class="ri-delete-bin-line"></i> Kosongkan
                        </button>
                    </form>
                @endif
            </div>

            <div class="card-body" style="padding: 1rem;">
                @forelse($ujian->soal as $idx => $s)
                    <div style="padding: 0.9rem; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                            <div style="display: flex; gap: 0.6rem;">
                                <span style="font-weight: 700; color: #475569; font-size: 0.9rem;">#{{ $idx + 1 }}</span>
                                <div>
                                    <div style="font-size: 0.88rem; font-weight: 600; color: #1e293b; margin-bottom: 0.35rem;">
                                        {!! nl2br(e($s->pertanyaan)) !!}
                                    </div>

                                    @if($s->isPg())
                                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.35rem; font-size: 0.8rem; margin: 0.5rem 0;">
                                            @foreach(['A' => $s->opsi_a, 'B' => $s->opsi_b, 'C' => $s->opsi_c, 'D' => $s->opsi_d] as $opt => $txt)
                                                <div style="padding: 0.25rem 0.5rem; border-radius: 6px; {{ strtoupper($s->kunci) === $opt ? 'background: #dcfce7; color: #166534; font-weight: 600;' : 'background: #fff; color: #475569;' }}">
                                                    <strong>{{ $opt }}.</strong> {{ $txt }}
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="font-size: 0.78rem; background: #fef3c7; color: #92400e; padding: 0.35rem 0.6rem; border-radius: 6px; margin: 0.4rem 0;">
                                            <strong>Kata Kunci:</strong> {{ $s->kata_kunci }}
                                        </div>
                                    @endif

                                    <div style="display: flex; gap: 0.4rem; align-items: center; margin-top: 0.35rem;">
                                        <span class="badge {{ $s->isPg() ? 'badge-blue' : 'badge-amber' }}">
                                            {{ $s->tipe_label }}
                                        </span>
                                        <span class="badge" style="background: #e2e8f0;">Bobot: {{ $s->bobot }}</span>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('admin.ujian.soal.detach', [$ujian, $s]) }}" method="POST" onsubmit="return confirm('Lepas soal ini dari ujian?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon danger" title="Lepas Soal">
                                    <i class="ri-close-line"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty" style="padding: 2.5rem 1rem; text-align: center;">
                        <i class="ri-file-warning-line" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.5rem;"></i>
                        <p style="margin: 0; color: #64748b;">Belum ada soal yang dipasang di ujian ini.</p>
                        <p class="text-xs text-muted" style="margin-top: 0.25rem;">
                            Gunakan tombol <strong>Generate Acak</strong> atau <strong>Pilih dari Bank Soal</strong> di sebelah kanan.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column: Pilih dari Bank Soal -->
        <div class="card card-accent-blue">
            <div class="card-header" style="flex-wrap: wrap; gap: 0.5rem; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="font-size: 1.05rem; margin: 0; color: #1e293b; display: flex; align-items: center; gap: 0.35rem;">
                        <i class="ri-archive-line" style="color: #2563eb;"></i>
                        <span>Pilih dari Bank Soal ({{ $bank->count() }} Tersedia)</span>
                    </h2>
                    @php
                        $activeMkObj = $mkList->firstWhere('id', $selectedMk);
                    @endphp
                    <div class="text-xs text-muted" style="margin-top: 3px;">
                        @if($selectedMk === 'all')
                            Menampilkan soal dari: <strong>Semua Mata Kuliah</strong>
                        @else
                            Menampilkan soal khusus MK: <strong style="color: #2563eb;">{{ $activeMkObj->nama ?? $ujian->mataKuliah->nama }}</strong>
                        @endif
                    </div>
                </div>

                @if($bank->count() > 0)
                    <button type="button" class="btn btn-outline btn-sm" id="btnToggleSelectAllBank" onclick="toggleSelectAllBank()" style="font-size: 0.78rem; padding: 0.25rem 0.6rem;">
                        <i class="ri-checkbox-multiple-line"></i> <span id="labelToggleSelectAll">Centang Semua</span>
                    </button>
                @endif
            </div>

            <div class="card-body" style="padding: 1rem;">
                <!-- Filter Pengelompokan Mata Kuliah & Pencarian -->
                <form action="{{ route('admin.ujian.soal', $ujian) }}" method="GET" style="margin-bottom: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.85rem;">
                    <!-- Dropdown Filter Pengelompokan MK -->
                    <div class="form-group" style="margin-bottom: 0.6rem;">
                        <label class="form-label" style="font-size: 0.76rem; font-weight: 700; color: #334155; text-transform: uppercase; display: flex; align-items: center; justify-content: space-between;">
                            <span><i class="ri-filter-3-line" style="color: #2563eb;"></i> Kelompokkan Bank Soal per Mata Kuliah:</span>
                            <span class="badge" style="background: #e2e8f0; font-size: 0.68rem; font-weight: 600;">{{ $bank->count() }} siap dipasang</span>
                        </label>
                        <select name="mk" class="form-select" style="min-height: 36px; font-size: 0.85rem; font-weight: 600; background-color: #fff; border-color: #cbd5e1;" onchange="this.form.submit()">
                            <optgroup label="Mata Kuliah Ujian Ini">
                                @php
                                    $ujianMk = $mkList->firstWhere('id', $ujian->mata_kuliah_id);
                                @endphp
                                <option value="{{ $ujian->mata_kuliah_id }}" {{ (string)$selectedMk === (string)$ujian->mata_kuliah_id ? 'selected' : '' }}>
                                    ★ {{ $ujian->mataKuliah->nama }} ({{ $ujian->mataKuliah->kode }}) &mdash; {{ $ujianMk?->soal_count ?? 0 }} Soal di Bank
                                </option>
                            </optgroup>

                            @if($mkList->where('id', '!=', $ujian->mata_kuliah_id)->count() > 0)
                                <optgroup label="Mata Kuliah Lainnya (Pilih jika ingin mengambil dari MK lain)">
                                    @foreach($mkList->where('id', '!=', $ujian->mata_kuliah_id) as $mk)
                                        <option value="{{ $mk->id }}" {{ (string)$selectedMk === (string)$mk->id ? 'selected' : '' }}>
                                            {{ $mk->nama }} ({{ $mk->kode }}) &mdash; {{ $mk->soal_count }} Soal di Bank
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            <optgroup label="Semua">
                                <option value="all" {{ $selectedMk === 'all' ? 'selected' : '' }}>
                                    -- Tampilkan Semua Bank Soal (Semua Mata Kuliah) --
                                </option>
                            </optgroup>
                        </select>
                    </div>

                    <!-- Filter Pencarian & Tipe -->
                    <div style="display: flex; gap: 0.4rem;">
                        <input type="text" name="q" class="form-control" placeholder="Cari pertanyaan di bank soal..." value="{{ request('q') }}" style="min-height: 34px; font-size: 0.82rem; background: #fff;">
                        <select name="tipe" class="form-select" style="min-height: 34px; font-size: 0.82rem; width: 110px; background: #fff;" onchange="this.form.submit()">
                            <option value="">Semua Tipe</option>
                            <option value="pg" {{ request('tipe') === 'pg' ? 'selected' : '' }}>PG</option>
                            <option value="essay" {{ request('tipe') === 'essay' ? 'selected' : '' }}>Essay</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-outline" style="padding: 0 0.75rem;">
                            <i class="ri-search-line"></i> Cari
                        </button>
                        @if(request('q') || request('tipe') || (request()->has('mk') && request('mk') != $ujian->mata_kuliah_id))
                            <a href="{{ route('admin.ujian.soal', $ujian) }}" class="btn btn-sm btn-ghost" title="Reset ke Mata Kuliah Ujian Ini">
                                <i class="ri-refresh-line"></i>
                            </a>
                        @endif
                    </div>
                </form>

                <form action="{{ route('admin.ujian.soal.attach', $ujian) }}" method="POST" id="formAttachSoal">
                    @csrf

                    <div style="max-height: 520px; overflow-y: auto; padding-right: 0.35rem;">
                        @forelse($bank as $b)
                            <div style="padding: 0.75rem; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 0.6rem; background: #fff; transition: background 0.15s ease;">
                                <label class="check" style="align-items: flex-start; width: 100%; cursor: pointer;">
                                    <input type="checkbox" name="ids[]" value="{{ $b->id }}" class="bank-check-item" style="margin-top: 3px;" onchange="updateAttachCount()">
                                    <div style="flex: 1; min-width: 0;">
                                        <div style="font-size: 0.84rem; color: #1e293b; margin-bottom: 0.35rem; line-height: 1.45;">
                                            {{ Str::limit($b->pertanyaan, 120) }}
                                        </div>
                                        <div style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                                            <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.68rem; font-weight: 600;">
                                                <i class="ri-book-mark-line"></i> {{ $b->mataKuliah->nama ?? '-' }}
                                            </span>
                                            <span class="badge {{ $b->isPg() ? 'badge-blue' : 'badge-amber' }}" style="font-size: 0.68rem;">
                                                {{ $b->tipe_label }}
                                            </span>
                                            <span class="text-xs text-muted">Bobot: {{ $b->bobot }}</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @empty
                            <div class="empty" style="padding: 2.5rem 1rem; text-align: center;">
                                <i class="ri-inbox-archive-line" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.5rem;"></i>
                                <p style="color: #64748b; margin: 0; font-weight: 500;">
                                    Tidak ada soal yang belum terpasang untuk mata kuliah ini di bank soal.
                                </p>
                                <p class="text-xs text-muted" style="margin-top: 0.25rem;">
                                    Ganti filter mata kuliah di atas jika ingin mengambil bank soal dari mata kuliah lain.
                                </p>
                            </div>
                        @endforelse
                    </div>

                    @if($bank->count() > 0)
                        <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #e2e8f0;">
                            <button type="submit" class="btn btn-primary btn-block" id="btnSubmitAttach" disabled>
                                <i class="ri-add-line"></i> Pasang Soal yang Dicentang (<span id="countSelectedBank">0</span>)
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Generate Acak dari Bank Soal -->
    <dialog class="modal" id="modalGenerate">
        <div class="modal-header">
            <h3><i class="ri-magic-line" style="color: #8b5cf6;"></i> Generate Acak dari Bank Soal</h3>
            <button type="button" class="btn-icon" onclick="this.closest('dialog').close()">&times;</button>
        </div>
        <form action="{{ route('admin.ujian.soal.generate', $ujian) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
                    Sistem akan mengambil sejumlah soal secara acak dari Bank Soal mata kuliah yang dipilih dan memasangkannya ke ujian ini.
                </p>

                <!-- Pilihan Sumber Bank Soal Mata Kuliah -->
                <div class="form-group">
                    <label class="form-label">
                        Sumber Bank Soal Mata Kuliah <span class="req">*</span>
                    </label>
                    <select name="mata_kuliah_id" class="form-select" required>
                        @foreach($mkList as $mk)
                            <option value="{{ $mk->id }}" {{ (string)$selectedMk === (string)$mk->id ? 'selected' : ($mk->id === $ujian->mata_kuliah_id ? 'selected' : '') }}>
                                {{ $mk->nama }} ({{ $mk->kode }}) &mdash; {{ $mk->soal_count }} Soal di Bank
                                @if($mk->id === $ujian->mata_kuliah_id) [Mata Kuliah Ujian Ini] @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Jumlah Soal Pilihan Ganda (PG)
                        <span class="badge badge-blue" style="margin-left: 0.35rem;">Tersedia di bank: {{ $sisaPg }}</span>
                    </label>
                    <input type="number" name="jumlah_pg" class="form-control" value="{{ min(10, $sisaPg) }}" min="0" max="{{ $sisaPg }}">
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Jumlah Soal Essay
                        <span class="badge badge-amber" style="margin-left: 0.35rem;">Tersedia di bank: {{ $sisaEssay }}</span>
                    </label>
                    <input type="number" name="jumlah_essay" class="form-control" value="{{ min(2, $sisaEssay) }}" min="0" max="{{ $sisaEssay }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-magic-line"></i> Generate Sekarang
                </button>
            </div>
        </form>
    </dialog>

    <!-- Modal Input Soal Baru Langsung -->
    <dialog class="modal modal-lg" id="modalInputBaru">
        <div class="modal-header">
            <h3><i class="ri-add-circle-line" style="color: #10b981;"></i> Input Soal Baru untuk Ujian Ini</h3>
            <button type="button" class="btn-icon" onclick="this.closest('dialog').close()">&times;</button>
        </div>
        <form action="{{ route('admin.ujian.soal.baru', $ujian) }}" method="POST">
            @csrf
            <div class="modal-body">
                <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 1rem;">
                    Soal yang diinput di sini akan otomatis disimpan ke Bank Soal <strong>{{ $ujian->mataKuliah->nama }}</strong> dan langsung dikaitkan ke ujian ini.
                </div>

                <div class="form-group">
                    <label class="form-label">Tipe Soal <span class="req">*</span></label>
                    <div class="seg">
                        <label>
                            <input type="radio" name="tipe" value="pg" checked onchange="toggleTipeSoal(this.value)">
                            <span>Pilihan Ganda (4 Opsi)</span>
                        </label>
                        <label>
                            <input type="radio" name="tipe" value="essay" onchange="toggleTipeSoal(this.value)">
                            <span>Essay (Kata Kunci)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="pertanyaan" class="form-label">Isi Pertanyaan <span class="req">*</span></label>
                    <textarea name="pertanyaan" id="pertanyaan" class="form-control" rows="3" placeholder="Tuliskan pertanyaan soal..." required></textarea>
                </div>

                <!-- Opsi PG -->
                <div id="pgContainer">
                    <div class="grid grid-2" style="gap: 0.75rem;">
                        <div class="form-group mb-2">
                            <label class="form-label">Pilihan A</label>
                            <input type="text" name="opsi_a" class="form-control" placeholder="Pilihan jawaban A">
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label">Pilihan B</label>
                            <input type="text" name="opsi_b" class="form-control" placeholder="Pilihan jawaban B">
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label">Pilihan C</label>
                            <input type="text" name="opsi_c" class="form-control" placeholder="Pilihan jawaban C">
                        </div>
                        <div class="form-group mb-2">
                            <label class="form-label">Pilihan D</label>
                            <input type="text" name="opsi_d" class="form-control" placeholder="Pilihan jawaban D">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 0.5rem;">
                        <label class="form-label">Kunci Jawaban Benar <span class="req">*</span></label>
                        <select name="kunci" class="form-select" style="max-width: 200px;">
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="C">C</option>
                            <option value="D">D</option>
                        </select>
                    </div>
                </div>

                <!-- Opsi Essay -->
                <div id="essayContainer" style="display: none;">
                    <div class="form-group">
                        <label for="kata_kunci" class="form-label">
                            Kata Kunci Jawaban <span class="req">*</span>
                        </label>
                        <textarea name="kata_kunci" id="kata_kunci" class="form-control" rows="2" placeholder="Contoh: enkripsi, password, autentikasi, privasi (pisahkan dengan koma)"></textarea>
                        <div class="form-hint">
                            Sistem akan otomatis menghitung kecocokan kata kunci jawaban mahasiswa dan memberikan skor persentase.
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bobot" class="form-label">Bobot Poin Soal <span class="req">*</span></label>
                    <input type="number" name="bobot" id="bobot" class="form-control" value="10" min="1" max="100" style="max-width: 160px;" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan & Pasang ke Ujian</button>
            </div>
        </form>
    </dialog>

    <script>
        function toggleTipeSoal(tipe) {
            document.getElementById('pgContainer').style.display = (tipe === 'pg') ? 'block' : 'none';
            document.getElementById('essayContainer').style.display = (tipe === 'essay') ? 'block' : 'none';
        }

        let isAllBankSelected = false;
        function toggleSelectAllBank() {
            const checkboxes = document.querySelectorAll('.bank-check-item');
            isAllBankSelected = !isAllBankSelected;
            checkboxes.forEach(cb => {
                cb.checked = isAllBankSelected;
            });
            const label = document.getElementById('labelToggleSelectAll');
            if (label) {
                label.textContent = isAllBankSelected ? 'Batal Centang' : 'Centang Semua';
            }
            updateAttachCount();
        }

        function updateAttachCount() {
            const checkboxes = document.querySelectorAll('.bank-check-item:checked');
            const count = checkboxes.length;
            const counter = document.getElementById('countSelectedBank');
            const submitBtn = document.getElementById('btnSubmitAttach');
            if (counter) counter.textContent = count;
            if (submitBtn) {
                submitBtn.disabled = (count === 0);
            }
        }

        // Jalankan saat pertama kali dibuka untuk sync tombol submit
        document.addEventListener('DOMContentLoaded', updateAttachCount);
    </script>
@endsection
