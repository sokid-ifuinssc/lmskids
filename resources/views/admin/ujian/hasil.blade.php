@extends('layouts.app')

@section('title', 'Hasil Ujian: ' . $ujian->judul)
@section('page-title')
    <i class="ri-bar-chart-2-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #10b981;"></i> Hasil Ujian Mahasiswa
@endsection
@section('page-subtitle', $ujian->judul . ' (' . $ujian->kode . ')')

@section('content')
    <!-- Exam Header & Stat Summary -->
    <div class="card" style="margin-bottom: 1.5rem; background: #ffffff; border-left: 4px solid #f59e0b;">
        <div class="card-body" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2 style="font-size: 1.2rem; margin: 0 0 0.35rem; color: #1e293b;">{{ $ujian->judul }}</h2>
                <div class="text-xs text-muted">
                    Mata Kuliah: <strong>{{ $ujian->mataKuliah->nama }}</strong> &bull;
                    Kategori: <span class="badge badge-blue">{{ $ujian->label }}</span> &bull;
                    Total Soal: <strong>{{ $ujian->soal_count }} Soal</strong>
                </div>
            </div>
            <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                <a href="{{ route('admin.ujian.hasil.export', $ujian) }}" class="btn btn-outline btn-sm" style="color: #10b981; border-color: #a7f3d0; background: #ecfdf5;">
                    <i class="ri-file-excel-2-line"></i> Download Rekap Nilai Excel
                </a>
                <a href="{{ route('admin.ujian.index') }}" class="btn btn-outline btn-sm">
                    <i class="ri-arrow-left-line"></i> Kembali ke Daftar Ujian
                </a>
            </div>
        </div>
    </div>

    <!-- Mini Stats -->
    <div class="grid grid-4" style="margin-bottom: 1.5rem;">
        <div class="stat">
            <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
                <i class="ri-user-star-line"></i>
            </div>
            <div>
                <div class="stat-value">{{ $ringkas['total'] }}</div>
                <div class="stat-label">Total Peserta Kelas</div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-icon" style="background: #ecfdf5; color: #059669;">
                <i class="ri-checkbox-circle-line"></i>
            </div>
            <div>
                <div class="stat-value">{{ $ringkas['selesai'] }}</div>
                <div class="stat-label">Sudah Selesai</div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="ri-timer-line"></i>
            </div>
            <div>
                <div class="stat-value">{{ $ringkas['berlangsung'] }}</div>
                <div class="stat-label">Sedang Mengerjakan</div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-icon" style="background: #faf5ff; color: #9333ea;">
                <i class="ri-calculator-line"></i>
            </div>
            <div>
                <div class="stat-value">{{ $ringkas['rata'] ?? 0 }}</div>
                <div class="stat-label">Rata-Rata Nilai</div>
            </div>
        </div>
    </div>

    <!-- Table of Results -->
    <div class="card card-accent">
        <div class="card-header">
            <h2>
                <i class="ri-group-line" style="color: #4f46e5;"></i>
                <span>Daftar Nilai Peserta Ujian</span>
            </h2>

            <form action="{{ route('admin.ujian.hasil', $ujian) }}" method="GET" class="filters">
                <select name="kelas" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Kelas Target --</option>
                    @foreach($ujian->kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas') == $k->id ? 'selected' : '' }}>
                            Kelas: {{ $k->nama }}
                        </option>
                    @endforeach
                </select>
                <a href="{{ route('admin.ujian.hasil', $ujian) }}" class="btn btn-sm btn-outline">
                    <i class="ri-refresh-line"></i> Reset
                </a>
            </form>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px;">NO</th>
                        <th>NIM</th>
                        <th>NAMA MAHASISWA</th>
                        <th>KELAS</th>
                        <th>STATUS</th>
                        <th>PELANGGARAN</th>
                        <th>NILAI AKHIR</th>
                        <th>WAKTU SELESAI</th>
                        <th style="text-align: right;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswa as $idx => $m)
                        @php
                            $p = $peserta->get($m->id);
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong class="mono" style="color: #1e3a8a;">{{ $m->username }}</strong></td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $m->name }}</div>
                            </td>
                            <td>
                                <span class="badge" style="background: #e2e8f0;">{{ $m->kelas?->nama ?? '-' }}</span>
                            </td>
                            <td>
                                @if(! $p)
                                    <span class="badge badge-amber">Belum Ikut</span>
                                @elseif($p->isSelesai())
                                    <span class="badge badge-green"><i class="ri-check-line"></i> Selesai</span>
                                @else
                                    <span class="badge badge-blue"><i class="ri-loader-4-line"></i> Berlangsung</span>
                                @endif
                            </td>
                            <td>
                                @if($p && $p->pelanggaran > 0)
                                    <span class="badge badge-red" title="Mahasiswa berpindah tab atau keluar layar penuh">
                                        <i class="ri-alert-fill"></i> {{ $p->pelanggaran }}x Keluar Tab
                                    </span>
                                @else
                                    <span class="text-xs text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                @if($p && $p->nilai !== null)
                                    <span class="badge badge-green" style="font-size: 0.95rem; font-weight: 700; padding: 0.25rem 0.65rem;">
                                        {{ number_format($p->nilai, 1) }}
                                    </span>
                                @else
                                    <span class="text-xs text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-xs">
                                    {{ $p?->selesai_at ? $p->selesai_at->format('d/m/Y H:i') : '-' }}
                                </div>
                            </td>
                            <td style="text-align: right;">
                                @if($p)
                                    <div class="actions">
                                        <a href="{{ route('admin.ujian.peserta', [$ujian, $p]) }}" class="btn btn-sm btn-outline" title="Lihat Lembar Jawaban & Koreksi Essay">
                                            <i class="ri-file-search-line"></i> Koreksi / Lembar
                                        </a>
                                        <form action="{{ route('admin.ujian.peserta.reset', [$ujian, $p]) }}" method="POST" onsubmit="return confirm('Reset ujian untuk {{ $m->name }}? Mahasiswa akan dapat mengulang ujian kembali dari awal.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-icon danger" title="Reset Ujian Siswa">
                                                <i class="ri-restart-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty">Tidak ada mahasiswa yang terdaftar di kelas target ujian ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
