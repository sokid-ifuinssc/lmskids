@extends('layouts.app')

@section('title', 'Dashboard Dosen')
@section('page-title')
    <i class="ri-dashboard-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #10b981;"></i> Dashboard Dosen
@endsection
@section('page-subtitle', 'Ringkasan & Pengawasan Ujian')

@section('content')
    <!-- Stat Cards Grid -->
    <div class="grid grid-4" style="margin-bottom: 1.5rem;">
        <div class="stat">
            <div class="stat-icon" style="background: #eef2ff; color: #4f46e5;">
                <i class="ri-team-line"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['mahasiswa']) }}</div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
        </div>

        <div class="stat">
            <div class="stat-icon" style="background: #ecfdf5; color: #059669;">
                <i class="ri-radar-line"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['ujian_aktif']) }} / {{ number_format($stats['ujian']) }}</div>
                <div class="stat-label">Ujian Aktif / Total</div>
            </div>
        </div>

        <div class="stat">
            <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                <i class="ri-file-list-3-line"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['soal']) }}</div>
                <div class="stat-label">Bank Soal Tersedia</div>
            </div>
        </div>

        <div class="stat">
            <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
                <i class="ri-book-read-line"></i>
            </div>
            <div>
                <div class="stat-value">{{ number_format($stats['matakuliah']) }} MK / {{ number_format($stats['kelas']) }} Kelas</div>
                <div class="stat-label">Mata Kuliah & Kelas</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Banner -->
    <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #0f2e5a, #1a4975); color: #fff; border: 0;">
        <div class="card-body" style="padding: 1.5rem 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="color: #fff; margin: 0 0 0.35rem; font-size: 1.2rem;">Panel Kelola Ujian & Penilaian</h3>
                <p style="color: #cbd5e1; margin: 0; font-size: 0.88rem;">
                    Buat ujian baru, kelola bank soal, atau periksa hasil jawaban mahasiswa dengan penilaian otomatis.
                </p>
            </div>
            <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                <a href="{{ route('admin.ujian.index') }}" class="btn" style="background: #f59e0b; color: #1e293b; font-weight: 700; border: 0;">
                    <i class="ri-add-circle-line"></i> Kelola Ujian
                </a>
                <a href="{{ route('admin.soal.create') }}" class="btn btn-outline" style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.3);">
                    <i class="ri-file-add-line"></i> Tambah Soal
                </a>
                <a href="{{ route('admin.rekap') }}" class="btn btn-outline" style="background: rgba(255,255,255,0.15); color: #fff; border-color: rgba(255,255,255,0.3);">
                    <i class="ri-file-chart-line"></i> Laporan Nilai
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-2" style="align-items: start;">
        <!-- Card Ujian Terdekat & Aktif -->
        <div class="card card-accent">
            <div class="card-header">
                <h2>
                    <i class="ri-time-line" style="color: #f59e0b;"></i>
                    <span>Jadwal Ujian Terdekat</span>
                </h2>
                <a href="{{ route('admin.ujian.index') }}" class="btn btn-sm btn-outline">Lihat Semua &rarr;</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Ujian / MK</th>
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ujianTerdekat as $u)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b;">{{ $u->judul }}</div>
                                    <div class="text-xs text-muted">
                                        {{ $u->mataKuliah->nama ?? '-' }} &bull;
                                        <span class="mono">{{ $u->kode }}</span>
                                    </div>
                                    <div class="text-xs text-muted" style="margin-top: 2px;">
                                        Kelas:
                                        @foreach($u->kelas as $k)
                                            <span class="badge" style="font-size: 0.65rem;">{{ $k->nama }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div class="text-xs" style="font-weight: 500;">
                                        {{ $u->waktu_mulai ? $u->waktu_mulai->format('d/m/Y H:i') : '-' }}
                                    </div>
                                    <div class="text-xs text-muted">{{ $u->durasi }} Menit</div>
                                </td>
                                <td>
                                    @if($u->is_active)
                                        <span class="badge badge-green"><span class="dot"></span> Aktif</span>
                                    @else
                                        <span class="badge badge-red"><span class="dot"></span> Non-Aktif</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('admin.ujian.soal', $u) }}" class="btn btn-sm btn-outline" title="Kelola Soal">
                                        <i class="ri-list-check"></i> {{ $u->soal_count }} Soal
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty">Belum ada ujian yang dijadwalkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Card Aktivitas Pengerjaan Terbaru -->
        <div class="card card-accent-green">
            <div class="card-header">
                <h2>
                    <i class="ri-history-line" style="color: #10b981;"></i>
                    <span>Aktivitas Mahasiswa Terbaru</span>
                </h2>
                <a href="{{ route('admin.rekap') }}" class="btn btn-sm btn-outline">Rekap Nilai &rarr;</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Ujian</th>
                            <th>Status</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aktivitas as $act)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b;">{{ $act->user->name ?? '-' }}</div>
                                    <div class="text-xs text-muted">
                                        {{ $act->user->username ?? '-' }}
                                        @if($act->user?->kelas)
                                            &bull; {{ $act->user->kelas->nama }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="text-xs" style="font-weight: 500;">{{ $act->ujian->judul ?? '-' }}</div>
                                    @if($act->pelanggaran > 0)
                                        <span class="badge badge-amber" style="font-size: 0.65rem;" title="Pelanggaran keluar tab">
                                            <i class="ri-alert-line"></i> {{ $act->pelanggaran }}x Tab
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($act->status === 'selesai')
                                        <span class="badge badge-green">Selesai</span>
                                    @else
                                        <span class="badge badge-blue">Berlangsung</span>
                                    @endif
                                </td>
                                <td>
                                    @if($act->nilai !== null)
                                        <span class="badge badge-green" style="font-size: 0.8rem; font-weight: 700;">
                                            {{ number_format($act->nilai, 1) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="empty">Belum ada aktivitas pengerjaan ujian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
