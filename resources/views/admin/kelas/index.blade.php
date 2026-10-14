@extends('layouts.app')

@section('title', 'Kelola Kelas')
@section('page-title')
    <i class="ri-community-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #10b981;"></i> Kelola Kelas
@endsection
@section('page-subtitle', 'Daftar Kelas & Semester Mahasiswa')

@section('content')
<div class="grid grid-3" style="align-items: start;">
    <!-- Left 2 cols: Daftar Kelas -->
    <div class="span-2 card card-accent">
        <div class="card-header">
            <h2>
                <i class="ri-building-4-line" style="color: #4f46e5;"></i>
                <span>Daftar Kelas Terdaftar ({{ $kelas->count() }})</span>
            </h2>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">NO</th>
                        <th>NAMA KELAS</th>
                        <th>KETERANGAN</th>
                        <th style="width: 120px; text-align: center;">MAHASISWA</th>
                        <th style="width: 100px; text-align: center;">TOTAL UJIAN</th>
                        <th style="width: 90px; text-align: right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas as $idx => $k)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong style="color: #1e293b; font-size: 0.95rem;">{{ $k->nama }}</strong>
                            </td>
                            <td>
                                <span class="text-xs text-muted">{{ $k->keterangan ?: '-' }}</span>
                            </td>
                            <td style="text-align: center;">
                                <a href="{{ route('admin.mahasiswa.index', ['kelas' => $k->id]) }}" class="badge badge-blue" style="font-weight: 600;">
                                    <i class="ri-team-line"></i> {{ $k->mahasiswa_count }} Mahasiswa
                                </a>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background: #f1f5f9;">{{ $k->ujian_count }} Ujian</span>
                            </td>
                            <td style="text-align: right;">
                                <div class="actions">
                                    <button type="button" class="btn-icon" title="Edit Kelas"
                                            onclick="openEditKelasModal({{ $k->id }}, '{{ addslashes($k->nama) }}', '{{ addslashes($k->keterangan ?? '') }}')">
                                        <i class="ri-edit-line" style="color: #ea580c;"></i>
                                    </button>
                                    <form action="{{ route('admin.kelas.destroy', $k) }}" method="POST" onsubmit="return confirm('Hapus kelas {{ $k->nama }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon danger" title="Hapus Kelas">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">Belum ada kelas. Tambahkan melalui formulir di samping.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right 1 col: Form Tambah Kelas Baru -->
    <div class="card card-accent-green">
        <div class="card-header">
            <h2>
                <i class="ri-add-circle-fill" style="color: #10b981;"></i>
                <span>Tambah Kelas Baru</span>
            </h2>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.kelas.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="nama" class="form-label">Nama Kelas / Semester <span class="req">*</span></label>
                    <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" placeholder="Contoh: 2A, 2B, 3ABC" required>
                    <div class="form-hint">Format bebas, misal nama ruang atau semester tujuan.</div>
                    @error('nama') <div class="invalid">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3" placeholder="Contoh: Kelas Pagi Semester Genap"></textarea>
                </div>

                <div style="margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="ri-save-line"></i> Simpan Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Kelas -->
<dialog class="modal" id="modalEditKelas">
    <div class="modal-header">
        <h3><i class="ri-edit-line" style="color: #ea580c;"></i> Edit Data Kelas</h3>
        <button type="button" class="btn-icon" onclick="this.closest('dialog').close()">&times;</button>
    </div>
    <form id="formEditKelas" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-group">
                <label for="edit_nama" class="form-label">Nama Kelas <span class="req">*</span></label>
                <input type="text" name="nama" id="edit_nama" class="form-control" required>
            </div>
            <div class="form-group mb-0">
                <label for="edit_keterangan" class="form-label">Keterangan</label>
                <textarea name="keterangan" id="edit_keterangan" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
</dialog>

<script>
    function openEditKelasModal(id, nama, keterangan) {
        const form = document.getElementById('formEditKelas');
        form.action = `/admin/kelas/${id}`;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_keterangan').value = keterangan;
        document.getElementById('modalEditKelas').showModal();
    }
</script>
@endsection
