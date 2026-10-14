@extends('layouts.app')

@section('title', 'Bank Soal')
@section('page-title')
    <i class="ri-questionnaire-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #10b981;"></i> Kelola Bank Soal
@endsection
@section('page-subtitle', 'Bank Soal Pilihan Ganda & Essay per Mata Kuliah')

@section('content')
    <!-- Action Bar & Filters -->
    <div class="card card-accent" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <!-- Filter Form -->
                <form action="{{ route('admin.soal.index') }}" method="GET" class="filters">
                    <select name="mk" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Mata Kuliah --</option>
                        @foreach($mkList as $mk)
                            <option value="{{ $mk->id }}" {{ request('mk') == $mk->id ? 'selected' : '' }}>
                                {{ $mk->kode }} - {{ $mk->nama }}
                            </option>
                        @endforeach
                    </select>

                    <select name="tipe" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Tipe --</option>
                        <option value="pg" {{ request('tipe') === 'pg' ? 'selected' : '' }}>Pilihan Ganda (PG)</option>
                        <option value="essay" {{ request('tipe') === 'essay' ? 'selected' : '' }}>Essay</option>
                    </select>

                    <div class="search">
                        <i class="ri-search-line"></i>
                        <input type="text" name="q" class="form-control" placeholder="Cari pertanyaan..." value="{{ request('q') }}">
                    </div>

                    <button type="submit" class="btn btn-sm btn-outline">Filter</button>
                    <a href="{{ route('admin.soal.index') }}" class="btn btn-sm btn-ghost">Reset</a>
                </form>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('modalImportSoal').showModal()">
                        <i class="ri-file-excel-2-line" style="color: #10b981;"></i> Import Excel
                    </button>
                    <div style="position: relative; display: inline-block;">
                        <a href="{{ route('admin.soal.export', ['tipe' => request('tipe', 'pg'), 'mk' => request('mk')]) }}" class="btn btn-outline btn-sm" title="Export Soal Sesuai Filter">
                            <i class="ri-download-2-line"></i> Export Excel
                        </a>
                    </div>
                    <a href="{{ route('admin.soal.create', ['mk' => request('mk')]) }}" class="btn btn-primary btn-sm">
                        <i class="ri-add-line"></i> Tambah Soal Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Table of Bank Soal -->
    <div class="card card-accent-blue">
        <div class="card-header">
            <h2>
                <i class="ri-folder-open-line" style="color: #2563eb;"></i>
                <span>Daftar Soal di Bank (Total: {{ $soal->total() }})</span>
            </h2>

            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button type="button" id="btnHapusTerpilih" class="btn btn-danger btn-sm" style="display: none;" onclick="submitBulkDelete()">
                    <i class="ri-delete-bin-line"></i> Hapus yang Dicentang
                </button>
            </div>
        </div>

        <form id="formBulkDelete" action="{{ route('admin.soal.bulk-destroy') }}" method="POST">
            @csrf
            @method('DELETE')

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 35px; text-align: center;">
                                <input type="checkbox" id="checkAll" onclick="toggleSelectAll(this)">
                            </th>
                            <th style="width: 45px;">NO</th>
                            <th style="width: 140px;">MATA KULIAH</th>
                            <th style="width: 90px;">TIPE</th>
                            <th>PERTANYAAN & KUNCI / KATA KUNCI</th>
                            <th style="width: 80px; text-align: center;">BOBOT</th>
                            <th style="width: 100px; text-align: center;">DI UJIAN</th>
                            <th style="width: 90px; text-align: right;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($soal as $idx => $s)
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="ids[]" value="{{ $s->id }}" class="check-item" onchange="checkSelection()">
                                </td>
                                <td>{{ $soal->firstItem() + $idx }}</td>
                                <td>
                                    <span class="badge" style="background: #e2e8f0; font-weight: 600;">
                                        {{ $s->mataKuliah->kode ?? '-' }}
                                    </span>
                                    <div class="text-xs text-muted" style="margin-top: 2px;">{{ $s->mataKuliah->nama ?? '-' }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $s->isPg() ? 'badge-blue' : 'badge-amber' }}">
                                        {{ $s->tipe_label }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b; margin-bottom: 0.35rem;">
                                        {!! nl2br(e($s->pertanyaan)) !!}
                                    </div>

                                    @if($s->isPg())
                                        <div style="display: flex; flex-wrap: wrap; gap: 0.4rem; font-size: 0.78rem;">
                                            <span style="color: {{ strtoupper($s->kunci) === 'A' ? '#166534; font-weight: 700;' : '#64748b;' }}">
                                                A: {{ Str::limit($s->opsi_a, 40) }}
                                            </span>
                                            &bull;
                                            <span style="color: {{ strtoupper($s->kunci) === 'B' ? '#166534; font-weight: 700;' : '#64748b;' }}">
                                                B: {{ Str::limit($s->opsi_b, 40) }}
                                            </span>
                                            &bull;
                                            <span style="color: {{ strtoupper($s->kunci) === 'C' ? '#166534; font-weight: 700;' : '#64748b;' }}">
                                                C: {{ Str::limit($s->opsi_c, 40) }}
                                            </span>
                                            &bull;
                                            <span style="color: {{ strtoupper($s->kunci) === 'D' ? '#166534; font-weight: 700;' : '#64748b;' }}">
                                                D: {{ Str::limit($s->opsi_d, 40) }}
                                            </span>
                                        </div>
                                        <div class="text-xs" style="color: #166534; font-weight: 600; margin-top: 0.25rem;">
                                            Kunci Jawaban: [{{ strtoupper($s->kunci) }}]
                                        </div>
                                    @else
                                        <div class="text-xs" style="background: #fef3c7; color: #92400e; padding: 0.25rem 0.5rem; border-radius: 6px; display: inline-block;">
                                            Kata Kunci: {{ $s->kata_kunci }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge" style="background: #f1f5f9; font-weight: 700;">{{ $s->bobot }}</span>
                                </td>
                                <td style="text-align: center;">
                                    <span class="text-xs">{{ $s->ujian_count }} Ujian</span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="actions">
                                        <a href="{{ route('admin.soal.edit', $s) }}" class="btn-icon" title="Edit Soal">
                                            <i class="ri-edit-line" style="color: #ea580c;"></i>
                                        </a>
                                        <form action="{{ route('admin.soal.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus soal ini dari bank soal?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon danger" title="Hapus Soal">
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
                                    Belum ada soal pada bank soal. Tambahkan soal baru atau import dari Excel.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @if($soal->hasPages())
            <div class="pager">
                <div class="info">
                    Menampilkan {{ $soal->firstItem() }} - {{ $soal->lastItem() }} dari {{ $soal->total() }} soal
                </div>
                {{ $soal->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Import Data Soal Excel Sesuai Referensi Screenshot media_1791971023280.png -->
    <dialog class="modal modal-lg" id="modalImportSoal">
        <div class="modal-header">
            <h3><i class="ri-file-excel-fill" style="color: #10b981;"></i> Import Soal dari Excel</h3>
            <button type="button" class="btn-icon" onclick="this.closest('dialog').close()">&times;</button>
        </div>
        <form action="{{ route('admin.soal.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 1.25rem;">
                    Upload file Excel berisi daftar soal untuk dimasukkan ke dalam Bank Soal secara otomatis.
                </p>

                <div class="grid grid-2" style="gap: 1rem; margin-bottom: 1.25rem;">
                    <div class="form-group mb-0">
                        <label class="form-label">Pilih Mata Kuliah Tujuan <span class="req">*</span></label>
                        <select name="mata_kuliah_id" class="form-select" required>
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach($mkList as $mk)
                                <option value="{{ $mk->id }}" {{ request('mk') == $mk->id ? 'selected' : '' }}>
                                    {{ $mk->kode }} - {{ $mk->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label">Tipe Soal yang Di-import <span class="req">*</span></label>
                        <select name="tipe" id="importTipe" class="form-select" onchange="updateImportFormat(this.value)" required>
                            <option value="pg">Pilihan Ganda (PG)</option>
                            <option value="essay">Essay (Kata Kunci)</option>
                        </select>
                    </div>
                </div>

                <!-- Format File Excel yang Diperlukan (Sesuai Referensi) -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1rem; margin-bottom: 1.25rem;">
                    <div style="font-weight: 600; font-size: 0.82rem; color: #166534; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.35rem;">
                        <i class="ri-lightbulb-line"></i> Format Kolom Excel yang Diperlukan:
                    </div>
                    <div id="formatPillsPG" class="code-tags">
                        <code>pertanyaan</code>
                        <code>a</code>
                        <code>b</code>
                        <code>c</code>
                        <code>d</code>
                        <code>kunci</code>
                        <code>bobot</code>
                    </div>
                    <div id="formatPillsEssay" class="code-tags" style="display: none;">
                        <code>pertanyaan</code>
                        <code>kata_kunci</code>
                        <code>bobot</code>
                    </div>
                </div>

                <!-- Drag & Drop Upload Zone -->
                <div class="dropzone" id="dropzoneSoal" onclick="document.getElementById('fileSoal').click()">
                    <i class="ri-folder-upload-fill" style="color: #f59e0b; font-size: 3rem;"></i>
                    <div style="font-weight: 700; color: #1e293b; margin-top: 0.5rem; font-size: 1.05rem;">
                        Drag & Drop atau Klik untuk Upload
                    </div>
                    <div class="text-xs text-muted" style="margin-top: 0.25rem;">Format didukung: .xlsx atau .xls (Maks 5MB)</div>
                    <div class="fname" id="fileSoalName"></div>
                    <input type="file" name="file" id="fileSoal" accept=".xlsx,.xls,.csv" required onchange="displayFilename(this, 'fileSoalName')">
                </div>

                <!-- Download Template Button -->
                <div style="margin-top: 1.25rem; display: flex; gap: 0.75rem; justify-content: center;">
                    <a id="btnDownloadTemplate" href="{{ route('admin.soal.template', 'pg') }}" class="btn btn-outline btn-sm">
                        <i class="ri-download-cloud-2-line"></i> Download Template Excel
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i class="ri-upload-2-line"></i> Upload & Proses Import
                </button>
            </div>
        </form>
    </dialog>

    <script>
        function toggleSelectAll(master) {
            const items = document.querySelectorAll('.check-item');
            items.forEach(cb => cb.checked = master.checked);
            checkSelection();
        }

        function checkSelection() {
            const checked = document.querySelectorAll('.check-item:checked');
            const btn = document.getElementById('btnHapusTerpilih');
            if (checked.length > 0) {
                btn.style.display = 'inline-flex';
                btn.innerHTML = `<i class="ri-delete-bin-line"></i> Hapus (${checked.length}) Soal`;
            } else {
                btn.style.display = 'none';
            }
        }

        function submitBulkDelete() {
            if (confirm('Yakin ingin menghapus semua soal yang dicentang?')) {
                document.getElementById('formBulkDelete').submit();
            }
        }

        function updateImportFormat(val) {
            const pgPills = document.getElementById('formatPillsPG');
            const essayPills = document.getElementById('formatPillsEssay');
            const btnTpl = document.getElementById('btnDownloadTemplate');

            if (val === 'essay') {
                pgPills.style.display = 'none';
                essayPills.style.display = 'flex';
                btnTpl.href = "{{ route('admin.soal.template', 'essay') }}";
            } else {
                pgPills.style.display = 'flex';
                essayPills.style.display = 'none';
                btnTpl.href = "{{ route('admin.soal.template', 'pg') }}";
            }
        }

        function displayFilename(input, targetId) {
            if (input.files && input.files[0]) {
                document.getElementById(targetId).textContent = '📄 ' + input.files[0].name;
            }
        }
    </script>
@endsection
