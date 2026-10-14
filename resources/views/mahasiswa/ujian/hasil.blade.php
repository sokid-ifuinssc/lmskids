@extends('layouts.app')

@section('title', 'Hasil Ujian: ' . $ujian->judul)
@section('page-title')
    <i class="ri-award-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #10b981;"></i> Hasil Ujian Anda
@endsection
@section('page-subtitle', $ujian->judul . ' (' . $ujian->label . ')')

@section('content')
<div style="max-width: 680px; margin: 1.5rem auto;">
    <div class="card card-accent-green" style="box-shadow: 0 10px 30px rgba(0,0,0,0.06); border-radius: 20px;">
        <div class="card-body" style="padding: 2.2rem; text-align: center;">

            <!-- Trophy Icon & Status -->
            <div style="width: 76px; height: 76px; margin: 0 auto 1.25rem; border-radius: 50%; background: #ecfdf5; color: #10b981; display: grid; place-items: center; font-size: 2.5rem; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.25);">
                <i class="ri-checkbox-circle-fill"></i>
            </div>

            <h2 style="font-size: 1.45rem; font-weight: 700; color: #0f172a; margin: 0 0 0.35rem;">
                Ujian Berhasil Diselesaikan!
            </h2>
            <p style="font-size: 0.88rem; color: #64748b; margin: 0 0 1.75rem;">
                Seluruh jawaban Anda untuk <strong>{{ $ujian->judul }}</strong> telah tersimpan di sistem.
            </p>

            <!-- Score Badge Area -->
            @if($ujian->tampilkan_nilai && $attempt->nilai !== null)
                <div style="background: linear-gradient(135deg, #0f2e5a, #1e3a8a); color: #fff; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.75rem; box-shadow: 0 6px 20px rgba(15, 46, 90, 0.25);">
                    <div style="font-size: 0.82rem; color: #93c5fd; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; margin-bottom: 0.25rem;">
                        NILAI AKHIR ANDA
                    </div>
                    <div style="font-size: 3.2rem; font-weight: 800; line-height: 1.1; color: #38bdf8;">
                        {{ number_format($attempt->nilai, 1) }}
                    </div>
                    <div style="font-size: 0.82rem; color: #cbd5e1; margin-top: 0.35rem;">
                        Skala penilaian 0 sampai 100
                    </div>
                </div>
            @else
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 1.25rem; margin-bottom: 1.75rem;">
                    <div style="font-weight: 700; color: #334155; font-size: 1.05rem; margin-bottom: 0.25rem;">
                        <i class="ri-hourglass-2-line" style="color: #f59e0b;"></i> Nilai Sedang Ditinjau oleh Dosen
                    </div>
                    <div class="text-xs text-muted">
                        Dosen pengampu akan mengoreksi jawaban essay Anda. Nilai akhir akan ditampilkan setelah proses koreksi selesai.
                    </div>
                </div>
            @endif

            <!-- Summary Breakdown Grid -->
            <div class="grid grid-2" style="gap: 0.75rem; margin-bottom: 1.5rem; text-align: left;">
                <div style="background: #f8fafc; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div class="text-xs text-muted">Pilihan Ganda Benar</div>
                    <div style="font-weight: 700; color: #1e293b; font-size: 1rem;">
                        {{ $ringkas['pg_benar'] }} / {{ $ringkas['pg_total'] }} Soal Benar
                    </div>
                </div>

                <div style="background: #f8fafc; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div class="text-xs text-muted">Soal Essay Terjawab</div>
                    <div style="font-weight: 700; color: #1e293b; font-size: 1rem;">
                        {{ $ringkas['essay_total'] }} Soal Essay
                    </div>
                </div>

                <div style="background: #f8fafc; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div class="text-xs text-muted">Waktu Selesai</div>
                    <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">
                        {{ $attempt->selesai_at ? $attempt->selesai_at->format('d/m/Y, H:i') : '-' }} WIB
                    </div>
                </div>

                <div style="background: #f8fafc; padding: 0.85rem 1rem; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div class="text-xs text-muted">Catatan Pelanggaran</div>
                    <div style="font-weight: 700; font-size: 0.95rem;">
                        @if($attempt->pelanggaran > 0)
                            <span class="badge badge-amber">{{ $attempt->pelanggaran }}x Keluar Tab</span>
                        @else
                            <span class="badge badge-green">Tertib (0 Pelanggaran)</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($attempt->catatan)
                <div style="font-size: 0.82rem; color: #64748b; background: #fefce8; padding: 0.6rem 0.8rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fef08a;">
                    <strong>Keterangan Sistem:</strong> {{ $attempt->catatan }}
                </div>
            @endif

            <!-- Navigation Links -->
            <div style="display: flex; gap: 0.75rem; justify-content: center;">
                <a href="{{ route('mhs.dashboard') }}" class="btn btn-outline btn-lg" style="flex: 1;">
                    <i class="ri-home-4-line"></i> Dashboard Ujian
                </a>
                <a href="{{ route('mhs.hasil') }}" class="btn btn-primary btn-lg" style="flex: 1;">
                    <i class="ri-award-line"></i> Riwayat Nilai Saya
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
