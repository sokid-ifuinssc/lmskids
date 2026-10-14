@extends('layouts.app')

@section('title', 'Portal Ujian Mahasiswa')
@section('page-title')
    <i class="ri-dashboard-3-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #10b981;"></i> Daftar Ujian Anda
@endsection
@section('page-subtitle', 'Pilih Ujian Aktif Sesuai Jadwal Kelas Anda')

@section('content')
    <!-- Student Greeting Banner -->
    <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #0f2e5a, #164e63); color: #fff; border: 0;">
        <div class="card-body" style="padding: 1.5rem 1.75rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div>
                <div style="font-size: 0.85rem; color: #6ee7b7; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                    Portal Ujian Mahasiswa &bull; Kelas: {{ $user->kelas?->nama ?? 'Umum' }}
                </div>
                <h2 style="color: #fff; margin: 0 0 0.35rem; font-size: 1.35rem;">Halo, {{ $user->name }}!</h2>
                <p style="color: #cbd5e1; margin: 0; font-size: 0.88rem;">
                    NIM: <strong style="color: #fff;">{{ $user->username }}</strong> &bull; Pastikan koneksi internet stabil dan gunakan peramban terkini saat mengikuti ujian.
                </p>
            </div>
            <div>
                <a href="{{ route('mhs.hasil') }}" class="btn" style="background: #10b981; color: #fff; font-weight: 600; border: 0;">
                    <i class="ri-award-line"></i> Lihat Rekap Nilai Saya
                </a>
            </div>
        </div>
    </div>

    <!-- Active Exams Grid -->
    <div style="margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between;">
        <h2 style="font-size: 1.15rem; color: #1e293b; font-weight: 700; margin: 0;">
            <i class="ri-calendar-event-line" style="color: #2563eb;"></i> Ujian yang Tersedia untuk Kelas Anda
        </h2>
        <span class="badge badge-blue">{{ $ujian->count() }} Ujian Ditemukan</span>
    </div>

    <div class="grid grid-2" style="gap: 1.25rem; margin-bottom: 2rem;">
        @forelse($ujian as $u)
            @php
                $att = $attempts->get($u->id);
                $isSelesai = $att && $att->isSelesai();
                $isBerlangsung = $att && ! $att->isSelesai();
                $isExpired = now()->isAfter($u->waktu_selesai);
                $isUpcoming = now()->isBefore($u->waktu_mulai);
                $canTake = ! $isSelesai && ! $isExpired && ! $isUpcoming;
            @endphp

            <div class="card card-accent" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem; gap: 0.5rem;">
                        <div>
                            <span class="badge badge-blue" style="font-size: 0.75rem; margin-bottom: 0.35rem;">
                                {{ $u->label }}
                            </span>
                            <h3 style="font-size: 1.15rem; color: #0f172a; margin: 0; font-weight: 700;">
                                {{ $u->judul }}
                            </h3>
                            <div class="text-xs text-muted" style="margin-top: 2px;">
                                Mata Kuliah: <strong>{{ $u->mataKuliah->nama }}</strong> (<span class="mono">{{ $u->kode }}</span>)
                            </div>
                        </div>

                        <div>
                            @if($isSelesai)
                                <span class="badge badge-green" style="font-size: 0.78rem;"><i class="ri-check-line"></i> Selesai</span>
                            @elseif($isBerlangsung)
                                <span class="badge badge-amber" style="font-size: 0.78rem;"><i class="ri-loader-2-line"></i> Berlangsung</span>
                            @elseif($isUpcoming)
                                <span class="badge" style="background: #f1f5f9; color: #64748b;">Belum Dibuka</span>
                            @elseif($isExpired)
                                <span class="badge badge-red">Berakhir</span>
                            @else
                                <span class="badge badge-green"><span class="dot"></span> Siap Dikerjakan</span>
                            @endif
                        </div>
                    </div>

                    @if($u->keterangan)
                        <div style="font-size: 0.82rem; color: #64748b; margin-bottom: 1rem; background: #f8fafc; padding: 0.5rem 0.75rem; border-radius: 8px;">
                            {{ $u->keterangan }}
                        </div>
                    @endif

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; background: #f8fafc; padding: 0.75rem; border-radius: 10px; margin-bottom: 1rem; text-align: center;">
                        <div>
                            <div class="text-xs text-muted">Durasi</div>
                            <div style="font-weight: 700; color: #1e293b;">{{ $u->durasi }} Menit</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Jumlah Soal</div>
                            <div style="font-weight: 700; color: #1e293b;">{{ $u->soal_count }} Soal</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Toleransi Tab</div>
                            <div style="font-weight: 700; color: #ef4444;">{{ $u->max_pelanggaran }}x Keluar</div>
                        </div>
                    </div>

                    <div class="text-xs text-muted" style="display: flex; flex-direction: column; gap: 0.2rem; margin-bottom: 1rem;">
                        <div><i class="ri-calendar-line"></i> Dibuka: {{ $u->waktu_mulai->format('d M Y, H:i') }} WIB</div>
                        <div><i class="ri-time-line"></i> Batas: {{ $u->waktu_selesai->format('d M Y, H:i') }} WIB</div>
                    </div>
                </div>

                <div class="card-footer" style="background: #fff; border-top: 1px solid #f1f5f9; padding: 0.85rem 1.25rem;">
                    @if($isSelesai)
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                @if($u->tampilkan_nilai && $att->nilai !== null)
                                    <div class="text-xs text-muted">Nilai Anda:</div>
                                    <div style="font-size: 1.35rem; font-weight: 800; color: #10b981;">
                                        {{ number_format($att->nilai, 1) }}
                                    </div>
                                @else
                                    <span class="text-xs text-muted">Nilai sedang diproses oleh dosen.</span>
                                @endif
                            </div>
                            <a href="{{ route('mhs.ujian.hasil', $u) }}" class="btn btn-outline btn-sm">
                                <i class="ri-file-chart-line"></i> Lihat Hasil
                            </a>
                        </div>
                    @elseif($isBerlangsung)
                        <a href="{{ route('mhs.ujian.kerjakan', $u) }}" class="btn btn-primary btn-block" style="background: #f59e0b; border: 0; color: #1e293b; font-weight: 700;">
                            <i class="ri-play-circle-line"></i> Lanjutkan Pengerjaan Ujian &rarr;
                        </a>
                    @elseif($canTake)
                        <a href="{{ route('mhs.ujian.konfirmasi', $u) }}" class="btn btn-primary btn-block">
                            <i class="ri-pencil-line"></i> Kerjakan Ujian Sekarang &rarr;
                        </a>
                    @elseif($isUpcoming)
                        <button class="btn btn-block" disabled style="background: #f1f5f9; color: #94a3b8;">
                            <i class="ri-time-line"></i> Belum Masuk Waktu Ujian
                        </button>
                    @else
                        <button class="btn btn-block" disabled style="background: #fee2e2; color: #ef4444;">
                            <i class="ri-close-circle-line"></i> Waktu Ujian Telah Berakhir
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="span-2 card">
                <div class="card-body empty" style="padding: 3rem 1rem;">
                    <i class="ri-inbox-line" style="font-size: 3rem; color: #cbd5e1; display: block; margin-bottom: 0.5rem;"></i>
                    <h3 style="color: #64748b; margin-bottom: 0.25rem;">Tidak Ada Jadwal Ujian Aktif</h3>
                    <p class="text-xs text-muted" style="margin: 0;">
                        Saat ini belum ada ujian yang dijadwalkan untuk kelas Anda. Silakan periksa kembali nanti.
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Riwayat Ujian Selesai -->
    @if($riwayat->count() > 0)
        <div class="card card-accent-green">
            <div class="card-header">
                <h2>
                    <i class="ri-history-line" style="color: #10b981;"></i>
                    <span>Riwayat Ujian Terakhir yang Telah Anda Selesaikan</span>
                </h2>
                <a href="{{ route('mhs.hasil') }}" class="btn btn-sm btn-outline">Lihat Rekap Lengkap &rarr;</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>UJIAN / MATA KULIAH</th>
                            <th>KATEGORI</th>
                            <th>SELESAI PADA</th>
                            <th>PELANGGARAN</th>
                            <th>NILAI ANDA</th>
                            <th style="text-align: right;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayat as $r)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b;">{{ $r->ujian->judul }}</div>
                                    <div class="text-xs text-muted">{{ $r->ujian->mataKuliah->nama }}</div>
                                </td>
                                <td><span class="badge badge-blue">{{ $r->ujian->label }}</span></td>
                                <td><div class="text-xs">{{ $r->selesai_at ? $r->selesai_at->format('d/m/Y H:i') : '-' }} WIB</div></td>
                                <td>
                                    @if($r->pelanggaran > 0)
                                        <span class="badge badge-amber">{{ $r->pelanggaran }}x Keluar Tab</span>
                                    @else
                                        <span class="text-xs text-muted">0 (Tertib)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($r->ujian->tampilkan_nilai && $r->nilai !== null)
                                        <span class="badge badge-green" style="font-size: 0.95rem; font-weight: 800;">
                                            {{ number_format($r->nilai, 1) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-muted">Diproses</span>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ route('mhs.ujian.hasil', $r->ujian) }}" class="btn btn-sm btn-outline">
                                        Rincian &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
