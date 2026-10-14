@extends('layouts.app')

@section('title', 'Mata Kuliah')
@section('page-title')
    <i class="ri-book-read-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #2563eb;"></i> Kelola Mata Kuliah
@endsection
@section('page-subtitle', 'Daftar Mata Kuliah & Kurikulum Ujian')

@section('content')
<div class="grid grid-3" style="align-items: start;">
    <!-- Left 2 cols: Daftar Mata Kuliah -->
    <div class="span-2 card card-accent">
        <div class="card-header">
            <h2>
                <i class="ri-book-2-line" style="color: #2563eb;"></i>
                <span>Daftar Mata Kuliah ({{ $matakuliah->count() }})</span>
            </h2>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th style="width: 120px;">KODE</th>
                        <th>NAMA MATA KULIAH</th>
                        <th style="width: 70px; text-align: center;">SKS</th>
                        <th style="width: 90px; text-align: center;">SEMESTER</th>
                        <th style="width: 110px; text-align: center;">BANK SOAL</th>
                        <th style="width: 90px; text-align: right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($matakuliah as $idx => $mk)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong class="mono" style="color: #1e3a8a;">{{ $mk->kode }}</strong>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $mk->nama }}</div>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f1f5f9; font-weight: 600;">{{ $mk->sks }} SKS</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="text-xs">{{ $mk->semester ?: '-' }}</span>
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.soal.index', ['mk' => $mk->id]) }}" class="badge badge-blue">
                                    <i class="ri-file-list-3-line"></i> {{ $mk->soal_count }} Soal
                                </a>
                            </td>
                            <td style="text-align: right;">
                                <div class="actions">
                                    <button type="button" class="btn-icon" title="Edit Mata Kuliah"
                                            onclick="openEditMkModal({{ $mk->id }}, '{{ addslashes($mk->kode) }}', '{{ addslashes($mk->nama) }}', {{ $mk->sks }}, '{{ addslashes($mk->semester ?? '') }}')">
                                        <i class="ri-edit-line" style="color: #ea580c;"></i>
                                    </button>
                                    <form action="{{ route('admin.matakuliah.destroy', $mk) }}" method="POST" onsubmit="return confirm('Hapus mata kuliah {{ $mk->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon danger" title="Hapus Mata Kuliah">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">Belum ada mata kuliah. Tambahkan melalui formulir di samping.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right 1 col: Form Tambah Mata Kuliah -->
    <div class="card card-accent-blue">
        <div class="card-header">
            <h2>
                <i class="ri-add-circle-fill" style="color: #2563eb;"></i>
                <span>Tambah Mata Kuliah</span>
            </h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.matakuliah.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="kode" class="form-label">Kode Mata Kuliah <span class="req">*</span></label>
                    <input type="text" name="kode" id="kode" class="form-control @error('kode') is-invalid @enderror" placeholder="Contoh: MK-SD01 atau SD001" required>
                    @error('kode') <div class="invalid">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="nama" class="form-label">Nama Mata Kuliah <span class="req">*</span></label>
                    <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: Struktur Data" required>
                    @error('nama') <div class="invalid">{{ $message }}</div> @enderror
                </div>

                <div class="grid grid-2" style="gap: 0.75rem;">
                    <div class="form-group mb-0">
                        <label for="sks" class="form-label">Bobot SKS <span class="req">*</span></label>
                        <input type="number" name="sks" id="sks" class="form-control" value="2" min="1" max="10" required>
                    </div>

                    <div class="form-group mb-0">
                        <label for="semester" class="form-label">Semester</label>
                        <input type="text" name="semester" id="semester" class="form-control" placeholder="Contoh: 3 atau Ganjil">
                    </div>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="ri-save-line"></i> Simpan Mata Kuliah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Mata Kuliah -->
<dialog class="modal" id="modalEditMk">
    <div class="modal-header">
        <h3><i class="ri-edit-line" style="color: #ea580c;"></i> Edit Mata Kuliah</h3>
        <button type="button" class="btn-icon" onclick="this.closest('dialog').close()">&times;</button>
    </div>
    <form id="formEditMk" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label for="edit_kode" class="form-label">Kode Mata Kuliah <span class="req">*</span></label>
                <input type="text" name="kode" id="edit_kode" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="edit_nama" class="form-label">Nama Mata Kuliah <span class="req">*</span></label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>
            <div class="grid grid-2" style="gap: 0.75rem;">
                <div class="form-group mb-0">
                    <label for="edit_sks" class="form-label">Bobot SKS <span class="req">*</span></label>
                    <input type="number" name="sks" id="edit_sks" class="form-control" min="1" max="10" required>
                </div>
                <div class="form-group mb-0">
                    <label for="edit_semester" class="form-label">Semester</label>
                    <input type="text" name="semester" id="edit_semester" class="form-control">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</dialog>

<script>
    function openEditMkModal(id, kode, nama, sks, semester) {
        const form = document.getElementById('formEditMk');
        form.action = `/admin/matakuliah/${id}`;
        document.getElementById('edit_kode').value = kode;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_sks').value = sks;
        document.getElementById('edit_semester').value = semester;
        document.getElementById('modalEditMk').showModal();
    }
</script>
@endsection
