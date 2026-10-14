@extends('layouts.app')

@section('title', 'Rekap Nilai Saya')
@section('page-title')
    <i class="ri-award-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #10b981;"></i> Nilai & Riwayat Ujian
@endsection
@section('page-subtitle', 'Rekapitulasi Nilai CPMK, UTS, dan UAS Anda')

@section('content')
    <!-- User Card -->
    <div class="card card-accent" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="avatar" style="width: 50px; height: 50px; font-size: 1.4rem;">
                    <i class="ri-user-smile-line"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.2rem; margin: 0 0 0.25rem; color: #1e293b;">{{ $user->name }}</h2>
                    <div class="text-xs text-muted">
                        NIM: <strong class="mono" style="color: #1e3a8a;">{{ $user->username }}</strong> &bull;
                        Kelas: <strong>{{ $user->kelas?->nama ?? '-' }}</strong>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('mhs.dashboard') }}" class="btn btn-outline btn-sm">
                    <i class="ri-arrow-left-line"></i> Kembali ke Daftar Ujian
                </a>
            </div>
        </div>
    </div>

    <!-- Rekap Nilai per Mata Kuliah (Legger Mahasiswa) -->
    <div style="margin-bottom: 1rem;">
        <h3 style="font-size: 1.15rem; color: #1e293b; font-weight: 700; margin: 0;">
            <i class="ri-file-chart-line" style="color: #2563eb;"></i> Nilai per Mata Kuliah (CPMK, UTS, UAS)
        </h3>
    </div>

    <div style="display: flex; flex-direction: column; gap: 1.25rem; margin-bottom: 2rem;">
        @forelse($rekap as $item)
            @php
                $mk = $item['mk'];
                $ujianList = $item['ujian'];
                $nilai = $item['nilai'];
            @endphp
            <div class="card card-accent-blue" style="margin-bottom: 0;">
                <div class="card-header" style="background: #ffffff; padding: 1rem 1.25rem;">
                    <div>
                        <span class="badge" style="background: #e2e8f0; font-weight: 700; color: #1e293b;">
                            {{ $mk->kode }}
                        </span>
                        <h4 style="font-size: 1.05rem; margin: 0.25rem 0 0; color: #0f172a; font-weight: 700;">
                            {{ $mk->nama }}
                        </h4>
                    </div>
                    <span class="text-xs text-muted">{{ $mk->sks }} SKS</span>
                </div>

                <div class="table-wrap">
                    <table class="table table-legger">
                        <thead>
                            <tr>
                                @forelse($ujianList as $u)
                                    <th>{{ $u->label }}</th>
                                @empty
                                    <th>Ujian</th>
                                @endforelse
                                <th style="background: #e0f2fe; color: #0369a1; font-weight: 700;">RATA-RATA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                @php
                                    $scores = [];
                                @endphp
                                @forelse($ujianList as $u)
                                    @php
                                        $val = $nilai[$u->id] ?? null;
                                        if ($val !== null) $scores[] = (float)$val;
                                    @endphp
                                    <td class="nilai" style="padding: 1rem;">
                                        @if($val !== null)
                                            <span class="badge {{ $val >= 70 ? 'badge-green' : ($val >= 50 ? 'badge-amber' : 'badge-red') }}" style="font-size: 1rem; font-weight: 800; padding: 0.35rem 0.75rem;">
                                                {{ number_format($val, 1) }}
                                            </span>
                                        @else
                                            <span class="text-xs text-muted">Belum ada nilai</span>
                                        @endif
                                    </td>
                                @empty
                                    <td class="empty">Belum ada ujian untuk mata kuliah ini.</td>
                                @endforelse

                                <td style="background: #f0f9ff; font-weight: 800; color: #0284c7; font-size: 1.15rem; padding: 1rem;">
                                    @if(count($scores) > 0)
                                        {{ number_format(array_sum($scores) / count($scores), 1) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body empty" style="padding: 2.5rem 1rem;">
                    <p style="color: #64748b; margin: 0;">Belum ada mata kuliah yang terdaftar untuk kelas Anda.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Riwayat Seluruh Ujian yang Diikuti -->
    <div class="card card-accent-green">
        <div class="card-header">
            <h3 style="font-size: 1.05rem; margin: 0; color: #1e293b; font-weight: 700;">
                <i class="ri-history-line" style="color: #10b981;"></i>
                <span>Riwayat Keseluruhan Ujian yang Telah Anda Selesaikan</span>
            </h3>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 45px;">NO</th>
                        <th>NAMA UJIAN / MATA KULIAH</th>
                        <th>KATEGORI</th>
                        <th>WAKTU SELESAI</th>
                        <th>PELANGGARAN</th>
                        <th>NILAI ANDA</th>
                        <th style="text-align: right;">RINCIAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat as $idx => $r)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <div style="font-weight: 600; color: #1e293b;">{{ $r->ujian->judul }}</div>
                                <div class="text-xs text-muted">{{ $r->ujian->mataKuliah->nama }} &bull; <span class="mono">{{ $r->ujian->kode }}</span></div>
                            </td>
                            <td><span class="badge badge-blue">{{ $r->ujian->label }}</span></td>
                            <td><div class="text-xs">{{ $r->selesai_at ? $r->selesai_at->format('d/m/Y, H:i') : '-' }} WIB</div></td>
                            <td>
                                @if($r->pelanggaran > 0)
                                    <span class="badge badge-amber">{{ $r->pelanggaran }}x Keluar Tab</span>
                                @else
                                    <span class="badge badge-green">0 (Tertib)</span>
                                @endif
                            </td>
                            <td>
                                @if($r->ujian->tampilkan_nilai && $r->nilai !== null)
                                    <span class="badge badge-green" style="font-size: 1rem; font-weight: 800; padding: 0.25rem 0.65rem;">
                                        {{ number_format($r->nilai, 1) }}
                                    </span>
                                @else
                                    <span class="text-xs text-muted">Ditinjau</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('mhs.ujian.hasil', $r->ujian) }}" class="btn btn-sm btn-outline">
                                    Lihat Hasil &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">Anda belum menyelesaikan ujian apapun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
