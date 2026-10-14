@extends('layouts.app')

@section('title', 'Konfirmasi Ujian: ' . $ujian->judul)
@section('page-title')
    <i class="ri-file-shield-2-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #2563eb;"></i> Konfirmasi & Tata Tertib Ujian
@endsection
@section('page-subtitle', $ujian->judul . ' (' . $ujian->kode . ')')

@section('content')
<div style="max-width: 760px; margin: 1rem auto;">
    <div class="card card-accent" style="box-shadow: 0 10px 30px rgba(0,0,0,0.06); border-radius: 20px;">
        <div class="card-header" style="background: #ffffff; padding: 1.25rem 1.75rem;">
            <div>
                <span class="badge badge-blue" style="font-size: 0.78rem; margin-bottom: 0.35rem;">
                    {{ $ujian->label }}
                </span>
                <h2 style="font-size: 1.35rem; color: #0f172a; margin: 0; font-weight: 700;">
                    {{ $ujian->judul }}
                </h2>
                <div class="text-xs text-muted" style="margin-top: 3px;">
                    Mata Kuliah: <strong>{{ $ujian->mataKuliah->nama }}</strong> &bull; Kode Ujian: <span class="mono">{{ $ujian->kode }}</span>
                </div>
            </div>
            <a href="{{ route('mhs.dashboard') }}" class="btn btn-sm btn-outline">
                <i class="ri-arrow-left-line"></i> Kembali
            </a>
        </div>

        <div class="card-body" style="padding: 1.75rem;">
            <!-- Informasi Penting Grid -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; text-align: center;">
                <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 1.75rem; color: #2563eb; margin-bottom: 0.25rem;">
                        <i class="ri-time-line"></i>
                    </div>
                    <div class="text-xs text-muted">Durasi Ujian</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #1e293b;">{{ $ujian->durasi }} Menit</div>
                </div>

                <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 1.75rem; color: #10b981; margin-bottom: 0.25rem;">
                        <i class="ri-list-check-2"></i>
                    </div>
                    <div class="text-xs text-muted">Jumlah Soal</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #1e293b;">{{ $ujian->soal_count }} Butir</div>
                </div>

                <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 1.75rem; color: #ef4444; margin-bottom: 0.25rem;">
                        <i class="ri-alarm-warning-line"></i>
                    </div>
                    <div class="text-xs text-muted">Batas Pelanggaran</div>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #ef4444;">{{ $ujian->max_pelanggaran }}x Keluar Tab</div>
                </div>
            </div>

            <!-- Identitas Peserta -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <div class="text-xs" style="color: #166534; font-weight: 600;">TERDAFTAR SEBAGAI:</div>
                    <div style="font-size: 1.1rem; font-weight: 700; color: #14532d;">{{ auth()->user()->name }}</div>
                </div>
                <div>
                    <div class="text-xs" style="color: #166534;">NIM / KELAS:</div>
                    <div style="font-weight: 700; color: #14532d;">{{ auth()->user()->username }} &bull; {{ auth()->user()->kelas?->nama ?? '-' }}</div>
                </div>
            </div>

            <!-- Petunjuk & Tata Tertib Ujian -->
            <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.75rem;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #854d0e; margin: 0 0 0.5rem; display: flex; align-items: center; gap: 0.4rem;">
                    <i class="ri-shield-check-fill" style="color: #eab308; font-size: 1.15rem;"></i>
                    Tata Tertib & Ketentuan Ujian
                </h4>
                <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.85rem; color: #713f12; line-height: 1.7;">
                    <li>Waktu ujian akan <strong>langsung berjalan mundur</strong> segera setelah Anda menekan tombol <em>"Mulai Mengerjakan Ujian"</em> di bawah.</li>
                    <li>Jawaban Anda akan <strong>disimpan secara otomatis (Auto-save)</strong> setiap kali Anda memilih atau mengetikkan jawaban.</li>
                    <li><strong>Dilarang membuka tab lain</strong>, berpindah jendela aplikasi, atau meminimalkan browser selama ujian berlangsung.</li>
                    <li>Sistem dilengkapi dengan <strong>deteksi pelanggaran otomatis</strong>. Jika Anda berpindah tab sebanyak <strong>{{ $ujian->max_pelanggaran }} kali</strong>, sesi ujian akan langsung dihentikan dan jawaban otomatis disubmit.</li>
                    <li>Ujian akan otomatis terkirim ketika waktu hitung mundur habis.</li>
                </ul>
            </div>

            <!-- Form Mulai Ujian -->
            <form action="{{ route('mhs.ujian.mulai', $ujian) }}" method="POST">
                @csrf

                <div style="margin-bottom: 1.5rem;">
                    <label class="check" style="cursor: pointer;">
                        <input type="checkbox" id="agreeCheck" required onchange="document.getElementById('btnStartExam').disabled = !this.checked">
                        <span style="font-size: 0.88rem; color: #1e293b; font-weight: 600;">
                            Saya telah membaca, memahami, dan menyetujui seluruh tata tertib ujian di atas.
                        </span>
                    </label>
                </div>

                <div style="display: flex; gap: 0.75rem;">
                    <a href="{{ route('mhs.dashboard') }}" class="btn btn-outline btn-lg" style="flex: 1;">
                        Batal
                    </a>
                    <button type="submit" id="btnStartExam" class="btn btn-primary btn-lg" style="flex: 2; box-shadow: 0 4px 14px rgba(15, 46, 90, 0.35);" disabled>
                        <i class="ri-pencil-fill"></i> Mulai Mengerjakan Ujian &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
