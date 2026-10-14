@extends('layouts.app')

@section('title', 'Koreksi Lembar Jawaban: ' . $peserta->user->name)
@section('page-title')
    <i class="ri-file-edit-line" style="vertical-align: middle; margin-right: 0.35rem; color: #8b5cf6;"></i> Koreksi Lembar Jawaban & Essay
@endsection
@section('page-subtitle', $peserta->user->name . ' (' . $peserta->user->username . ') — ' . $ujian->judul)

@section('content')
    <!-- Student & Exam Header Info -->
    <div class="card card-accent" style="margin-bottom: 1.5rem;">
        <div class="card-body" style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="avatar" style="width: 52px; height: 52px; font-size: 1.4rem;">
                    <i class="ri-user-smile-line"></i>
                </div>
                <div>
                    <h2 style="font-size: 1.25rem; margin: 0 0 0.25rem; color: #1e293b;">{{ $peserta->user->name }}</h2>
                    <div class="text-xs text-muted">
                        NIM: <strong class="mono">{{ $peserta->user->username }}</strong> &bull;
                        Kelas: <strong>{{ $peserta->user->kelas?->nama ?? '-' }}</strong> &bull;
                        Waktu Mulai: {{ $peserta->mulai_at ? $peserta->mulai_at->format('d/m/Y H:i') : '-' }} &bull;
                        Waktu Selesai: {{ $peserta->selesai_at ? $peserta->selesai_at->format('d/m/Y H:i') : 'Masih Berlangsung' }}
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center;">
                @if($peserta->pelanggaran > 0)
                    <div style="text-align: right;">
                        <span class="badge badge-red" style="font-size: 0.82rem; padding: 0.3rem 0.65rem;">
                            <i class="ri-alarm-warning-fill"></i> {{ $peserta->pelanggaran }}x Pelanggaran Tab
                        </span>
                        <div class="text-xs text-muted" style="margin-top: 2px;">Terdeteksi keluar jendela ujian</div>
                    </div>
                @endif

                <div style="text-align: center; background: #f8fafc; padding: 0.6rem 1.2rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div class="text-xs text-muted">NILAI AKHIR</div>
                    <div style="font-size: 1.7rem; font-weight: 800; color: #10b981; line-height: 1.1;">
                        {{ $peserta->nilai !== null ? number_format($peserta->nilai, 1) : '-' }}
                    </div>
                    <div class="text-xs text-muted">Skala 0 - 100</div>
                </div>

                <a href="{{ route('admin.ujian.hasil', $ujian) }}" class="btn btn-outline btn-sm">
                    <i class="ri-arrow-left-line"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Koreksi Nilai Manual Essay -->
    <form action="{{ route('admin.ujian.peserta.skor', [$ujian, $peserta]) }}" method="POST">
        @csrf
        @method('PUT')

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @foreach($soalList as $idx => $s)
                @php
                    $j = $jawaban->get($s->id);
                    $ansText = $j?->jawaban;
                @endphp

                <div class="card" style="margin-bottom: 0.5rem; border-left: 4px solid {{ $s->isPg() ? ($j?->is_benar ? '#10b981' : '#ef4444') : '#f59e0b' }};">
                    <div class="card-header" style="background: #fafafa; padding: 0.75rem 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="font-weight: 700; color: #475569; font-size: 0.95rem;">Nomor {{ $idx + 1 }}</span>
                            <span class="badge {{ $s->isPg() ? 'badge-blue' : 'badge-amber' }}">
                                {{ $s->tipe_label }}
                            </span>
                            <span class="badge" style="background: #e2e8f0;">Bobot Poin: {{ $s->bobot }}</span>
                        </div>

                        <div>
                            @if($s->isPg())
                                @if($j?->is_benar)
                                    <span class="badge badge-green" style="font-size: 0.8rem;"><i class="ri-checkbox-circle-fill"></i> Jawaban Benar (+{{ $s->bobot }})</span>
                                @else
                                    <span class="badge badge-red" style="font-size: 0.8rem;"><i class="ri-close-circle-fill"></i> Jawaban Salah (0)</span>
                                @endif
                            @else
                                <span class="badge badge-amber" style="font-size: 0.8rem;">
                                    Skor Saat Ini: <strong>{{ number_format($j?->skor ?? 0, 1) }} / {{ $s->bobot }}</strong>
                                    {{ $j?->skor_manual ? '(Koreksi Dosen)' : '(Otomatis Kata Kunci)' }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Pertanyaan -->
                        <div style="font-size: 0.95rem; font-weight: 600; color: #1e293b; margin-bottom: 1rem; line-height: 1.5;">
                            {!! nl2br(e($s->pertanyaan)) !!}
                        </div>

                        @if($s->isPg())
                            <!-- Jawaban Pilihan Ganda -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 0.75rem;">
                                @foreach(['A' => $s->opsi_a, 'B' => $s->opsi_b, 'C' => $s->opsi_c, 'D' => $s->opsi_d] as $opt => $txt)
                                    @php
                                        $isKey = strtoupper($s->kunci) === $opt;
                                        $isChosen = strtoupper((string) $ansText) === $opt;
                                        $bg = '#fff';
                                        $border = '#e2e8f0';
                                        $icon = '';
                                        if ($isKey && $isChosen) {
                                            $bg = '#dcfce7'; $border = '#86efac'; $icon = '✅ Kunci & Dipilih Mahasiswa';
                                        } elseif ($isKey) {
                                            $bg = '#dcfce7'; $border = '#86efac'; $icon = '🔑 Kunci Jawaban Benar';
                                        } elseif ($isChosen) {
                                            $bg = '#fee2e2'; $border = '#fca5a5'; $icon = '❌ Pilihan Mahasiswa (Salah)';
                                        }
                                    @endphp
                                    <div style="padding: 0.6rem 0.8rem; border-radius: 8px; border: 1px solid {{ $border }}; background: {{ $bg }}; font-size: 0.86rem;">
                                        <div style="font-weight: 600; margin-bottom: 0.2rem;">
                                            {{ $opt }}. {{ $txt }}
                                        </div>
                                        @if($icon)
                                            <div class="text-xs" style="font-weight: 600;">{{ $icon }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-xs text-muted">
                                Mahasiswa memilih: <strong>{{ $ansText ?: 'Tidak dijawab' }}</strong> | Kunci jawaban: <strong>{{ strtoupper($s->kunci) }}</strong>
                            </div>

                        @else
                            <!-- Jawaban Essay & Analisis Kata Kunci -->
                            <div style="background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0; padding: 1rem; margin-bottom: 1rem;">
                                <div class="text-xs text-muted" style="margin-bottom: 0.35rem; font-weight: 600;">
                                    LEMBAR JAWABAN MAHASISWA:
                                </div>
                                <div style="font-size: 0.92rem; color: #1e293b; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0.85rem; line-height: 1.6; white-space: pre-wrap;">
                                    {{ $ansText ?: '(Mahasiswa tidak mengisi jawaban)' }}
                                </div>
                            </div>

                            <!-- Detail Kata Kunci & Persentase Sesuai Instruksi User -->
                            @php
                                $daftarKunci = $s->daftarKataKunci();
                                $matched = [];
                                $textLower = mb_strtolower((string) $ansText);
                                foreach($daftarKunci as $k) {
                                    if (str_contains($textLower, $k)) {
                                        $matched[] = $k;
                                    }
                                }
                                $persenKecocokan = count($daftarKunci) > 0 ? round((count($matched) / count($daftarKunci)) * 100, 1) : 0;
                            @endphp

                            <div style="padding: 0.85rem; border-radius: 10px; background: #fefce8; border: 1px solid #fef08a; margin-bottom: 1rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                                    <div style="font-size: 0.84rem; font-weight: 700; color: #854d0e; display: flex; align-items: center; gap: 0.35rem;">
                                        <i class="ri-key-2-line"></i> Analisis Kata Kunci (Koreksi Otomatis):
                                    </div>
                                    <div class="badge badge-amber" style="font-size: 0.82rem; font-weight: 700;">
                                        Kecocokan: {{ count($matched) }} dari {{ count($daftarKunci) }} kata kunci ({{ $persenKecocokan }}%)
                                    </div>
                                </div>

                                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                                    @foreach($daftarKunci as $k)
                                        @if(in_array($k, $matched))
                                            <span class="badge badge-green" style="font-size: 0.76rem;">
                                                <i class="ri-check-line"></i> {{ $k }}
                                            </span>
                                        @else
                                            <span class="badge" style="background: #f1f5f9; color: #94a3b8; font-size: 0.76rem;">
                                                &bull; {{ $k }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Input Penilaian / Koreksi Dosen -->
                            <div style="display: flex; align-items: center; gap: 1rem; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 0.75rem 1rem; border-radius: 8px;">
                                <div style="font-size: 0.85rem; color: #166534; font-weight: 600;">
                                    <i class="ri-edit-2-line"></i> Poin Essay Mahasiswa:
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <input type="number"
                                           name="skor[{{ $s->id }}]"
                                           value="{{ old('skor.'.$s->id, $j?->skor ?? 0) }}"
                                           min="0"
                                           max="{{ $s->bobot }}"
                                           step="0.5"
                                           class="form-control"
                                           style="width: 100px; min-height: 36px; font-weight: 700; color: #15803d; text-align: center;">
                                    <span style="font-size: 0.85rem; color: #166534; font-weight: 600;">/ {{ $s->bobot }} Poin Maksimal</span>
                                </div>
                                <div class="text-xs text-muted" style="margin-left: auto;">
                                    Dosen dapat mengubah skor di atas jika jawaban mahasiswa layak mendapatkan nilai berbeda.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div style="position: sticky; bottom: 1rem; background: rgba(255,255,255,0.95); backdrop-filter: blur(8px); padding: 1rem; border-radius: 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem;">
            <div style="font-size: 0.86rem; color: #475569;">
                Pastikan seluruh skor essay sudah sesuai sebelum menyimpan perubahan. Nilai total (0 - 100) akan dihitung ulang secara otomatis.
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <a href="{{ route('admin.ujian.hasil', $ujian) }}" class="btn btn-outline">Batal</a>
                <button type="submit" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 12px rgba(15,46,90,0.3);">
                    <i class="ri-save-line"></i> Simpan Penilaian Essay
                </button>
            </div>
        </div>
    </form>
@endsection
