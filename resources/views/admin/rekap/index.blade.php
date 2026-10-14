@extends('layouts.app')

@section('title', 'Laporan Nilai & Legger')
@section('page-title')
    <i class="ri-bar-chart-box-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #4f46e5;"></i> Laporan Nilai
@endsection
@section('page-subtitle', 'Rekapitulasi Nilai Ujian CPMK, UTS, dan UAS (Legger)')

@section('content')
    <div class="card card-accent">
        <div class="card-header">
            <div>
                <h2 style="font-size: 1.15rem; color: #1e293b; font-weight: 700; margin: 0 0 0.2rem;">
                    <i class="ri-survey-line" style="color: #4f46e5;"></i>
                    <span>Laporan Nilai (Legger)</span>
                </h2>
                <div class="sub">Nilai ujian terakumulasi per CPMK, UTS, dan UAS</div>
            </div>

            <!-- Filters Matching Reference Screenshot media_1791971043205.png -->
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <form action="{{ route('admin.rekap') }}" method="GET" style="display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap;">
                    <select name="mk" class="form-select" onchange="this.form.submit()" style="min-width: 170px; min-height: 36px; font-size: 0.85rem;">
                        @foreach($mkList as $m)
                            <option value="{{ $m->id }}" {{ $mkId == $m->id ? 'selected' : '' }}>
                                {{ $m->kode }} - {{ $m->nama }}
                            </option>
                        @endforeach
                    </select>

                    <select name="kelas" class="form-select" onchange="this.form.submit()" style="min-width: 140px; min-height: 36px; font-size: 0.85rem;">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $kelasId == $k->id ? 'selected' : '' }}>
                                Kelas: {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>

                    <a href="{{ route('admin.rekap', ['mk' => $mkId, 'kelas' => $kelasId]) }}" class="btn btn-outline btn-sm" title="Refresh Laporan">
                        <i class="ri-refresh-line"></i> Refresh
                    </a>
                </form>

                @if($mkId && $rows->count() > 0)
                    <a href="{{ route('admin.rekap.export', ['mk' => $mkId, 'kelas' => $kelasId]) }}" class="btn btn-primary btn-sm" title="Download Format Excel Legger">
                        <i class="ri-file-excel-2-line"></i> Export Legger Excel
                    </a>
                @endif
            </div>
        </div>

        <!-- Table of Legger Grades -->
        <div class="table-wrap">
            <table class="table table-legger">
                <thead>
                    <tr>
                        <th class="l" style="width: 45px;">NO</th>
                        <th class="l" style="width: 140px;">NIM</th>
                        <th class="l">NAMA MAHASISWA</th>
                        <th class="l" style="width: 120px;">KELAS</th>

                        <!-- Dynamic Exam Columns (CPMK 1..n, UTS, UAS) -->
                        @forelse($ujian as $u)
                            <th style="min-width: 85px;" title="{{ $u->judul }}">
                                {{ $u->label }}
                            </th>
                        @empty
                            <th style="min-width: 120px;">UJIAN</th>
                        @endforelse

                        <th style="width: 90px; background: #e0f2fe; color: #0369a1; font-weight: 700;">
                            RATA-RATA
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $idx => $r)
                        <tr>
                            <td class="l">{{ $idx + 1 }}</td>
                            <td class="l">
                                <strong class="mono" style="color: #1d4ed8;">{{ $r['user']->username }}</strong>
                            </td>
                            <td class="l">
                                <div style="font-weight: 600; color: #1e293b;">{{ $r['user']->name }}</div>
                            </td>
                            <td class="l">
                                <span class="badge" style="background: #e2e8f0;">{{ $r['user']->kelas?->nama ?? '-' }}</span>
                            </td>

                            <!-- Exam Columns -->
                            @forelse($ujian as $u)
                                @php
                                    $val = $r['nilai'][$u->id] ?? null;
                                @endphp
                                <td class="nilai">
                                    @if($val !== null)
                                        <span class="badge {{ $val >= 70 ? 'badge-green' : ($val >= 50 ? 'badge-amber' : 'badge-red') }}" style="font-size: 0.85rem; padding: 0.2rem 0.55rem; font-weight: 700;">
                                            {{ number_format($val, 1) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-muted">-</span>
                                    @endif
                                </td>
                            @empty
                                <td>-</td>
                            @endforelse

                            <td style="background: #f0f9ff; font-weight: 800; color: #0284c7; font-size: 0.95rem;">
                                {{ $r['rata'] !== null ? number_format($r['rata'], 1) : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + $ujian->count() }}" class="empty">
                                <i class="ri-inbox-line" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 0.5rem;"></i>
                                Belum ada data nilai pada mata kuliah dan kelas yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rows->count() > 0)
            <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; color: #64748b;">
                <div>
                    Menampilkan total <strong>{{ $rows->count() }}</strong> mahasiswa pada mata kuliah ini.
                </div>
                <div style="display: flex; gap: 0.75rem;">
                    <span><span class="badge badge-green">&ge; 70</span> Lulus Baik</span>
                    <span><span class="badge badge-amber">50 - 69</span> Cukup</span>
                    <span><span class="badge badge-red">&lt; 50</span> Kurang</span>
                </div>
            </div>
        @endif
    </div>
@endsection
