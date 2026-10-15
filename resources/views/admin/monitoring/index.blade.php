@extends('layouts.app')

@section('title', 'Monitoring Ujian')
@section('page-title')
    <i class="ri-radar-fill" style="vertical-align: middle; margin-right: 0.35rem; color: #0284c7;"></i> Live Monitoring Ujian
@endsection
@section('page-subtitle', 'Pantau status pengerjaan mahasiswa secara realtime per kelas & mata kuliah')

@push('styles')
<style>
    /* Styling khusus monitoring & screenshot */
    .monitoring-card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.08);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    .monitoring-header {
        background: linear-gradient(135deg, #0f2e5a 0%, #1e3a8a 50%, #2563eb 100%);
        color: #ffffff;
        padding: 1.5rem 1.75rem;
        position: relative;
    }

    .stat-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .stat-badge.selesai { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .stat-badge.sedang { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .stat-badge.belum { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

    .chart-container {
        position: relative;
        width: 220px;
        height: 220px;
        margin: 0 auto;
    }

    .chart-center-label {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        pointer-events: none;
    }

    .chart-center-label .percentage {
        font-size: 1.85rem;
        font-weight: 800;
        line-height: 1;
        color: #0f2e5a;
    }

    .chart-center-label .desc {
        font-size: 0.72rem;
        font-weight: 600;
        color: #64748b;
        margin-top: 0.2rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .tab-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid transparent;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .tab-btn:hover { background: #e2e8f0; }
    .tab-btn.active {
        background: #0f2e5a;
        color: #ffffff;
    }

    /* Focus Mode untuk Screenshot */
    body.screenshot-mode .sidebar,
    body.screenshot-mode .app-header,
    body.screenshot-mode .filter-controls,
    body.screenshot-mode .no-screenshot {
        display: none !important;
    }
    body.screenshot-mode .main-content {
        margin: 0 !important;
        padding: 1rem !important;
    }
    body.screenshot-mode .app {
        display: block !important;
    }
</style>
@endpush

@section('content')
    <!-- Control Bar (Filter & Aksi Cepat) -->
    <div class="card filter-controls" style="margin-bottom: 1.25rem;">
        <div class="card-body" style="padding: 1rem 1.25rem;">
            <form action="{{ route('admin.monitoring.index') }}" method="GET" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; flex: 1;">
                    <!-- Pilih Ujian -->
                    <div style="min-width: 260px;">
                        <label class="form-label" style="margin-bottom: 0.25rem; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                            <i class="ri-file-list-3-line"></i> Pilih Ujian (Mata Kuliah)
                        </label>
                        <select name="ujian_id" class="form-select" onchange="this.form.submit()" style="font-weight: 600; color: #1e293b;">
                            @forelse($ujianList as $u)
                                <option value="{{ $u->id }}" {{ $selectedUjian && $selectedUjian->id == $u->id ? 'selected' : '' }}>
                                    {{ $u->judul }} [{{ $u->mataKuliah->nama }}] ({{ $u->label }}) {{ $u->is_active ? '🟢 Aktif' : '⚪' }}
                                </option>
                            @empty
                                <option value="">-- Belum Ada Ujian Dibuat --</option>
                            @endforelse
                        </select>
                    </div>

                    <!-- Pilih Kelas -->
                    @if($selectedUjian && $kelasList->count() > 0)
                        <div style="min-width: 170px;">
                            <label class="form-label" style="margin-bottom: 0.25rem; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">
                                <i class="ri-community-line"></i> Pilih Kelas Peserta
                            </label>
                            <select name="kelas_id" class="form-select" onchange="this.form.submit()" style="font-weight: 600; color: #1e293b;">
                                @if($kelasList->count() > 1)
                                    <option value="" {{ !$selectedKelas ? 'selected' : '' }}>-- Semua Kelas ({{ $kelasList->pluck('nama')->implode(', ') }}) --</option>
                                @endif
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ $selectedKelas && $selectedKelas->id == $k->id ? 'selected' : '' }}>
                                        Kelas {{ $k->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div style="align-self: flex-end;">
                        <button type="submit" class="btn btn-outline btn-sm">
                            <i class="ri-filter-3-line"></i> Terapkan
                        </button>
                    </div>
                </div>

                <!-- Aksi Khusus: Salin WA & Mode Screenshot -->
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm btn-outline" id="btnAutoRefresh" onclick="toggleAutoRefresh()" title="Auto refresh data tiap 30 detik">
                        <i class="ri-refresh-line" id="iconRefresh"></i> <span id="labelRefresh">Auto-Refresh: OFF</span>
                    </button>

                    <button type="button" class="btn btn-sm btn-outline" style="color: #16a34a; border-color: #86efac; background: #f0fdf4;" onclick="salinPesanWhatsApp()" title="Salin ringkasan teks untuk dikirim ke WhatsApp Ketua Kelas">
                        <i class="ri-whatsapp-line" style="font-size: 1.05rem;"></i> Salin Pesan WA
                    </button>

                    <button type="button" class="btn btn-sm btn-primary" onclick="toggleScreenshotMode()" title="Sembunyikan menu samping untuk screenshot bersih">
                        <i class="ri-camera-lens-line"></i> Mode Screenshot
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tombol Keluar dari Mode Screenshot (Hanya muncul saat Mode Screenshot aktif) -->
    <div id="barExitScreenshot" style="display: none; position: fixed; top: 12px; right: 12px; z-index: 9999; background: #0f172a; padding: 0.5rem 1rem; border-radius: 9999px; box-shadow: 0 4px 14px rgba(0,0,0,0.4);">
        <button type="button" class="btn btn-sm" style="color: #ffffff; background: #ef4444; border: 0; font-weight: 600;" onclick="toggleScreenshotMode()">
            <i class="ri-close-circle-line"></i> Keluar Mode Screenshot
        </button>
    </div>

    @if(!$selectedUjian)
        <!-- Empty State Belum Ada Ujian -->
        <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
            <i class="ri-file-list-3-line" style="font-size: 3.5rem; color: #cbd5e1; display: block; margin-bottom: 0.75rem;"></i>
            <h3 style="color: #1e293b; font-size: 1.2rem; margin-bottom: 0.35rem;">Belum Ada Ujian yang Dapat Dipantau</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.25rem;">Buat ujian baru terlebih dahulu dan kaitkan dengan kelas tujuan.</p>
            <div>
                <a href="{{ route('admin.ujian.index') }}" class="btn btn-primary">
                    <i class="ri-add-line"></i> Buat Ujian Baru
                </a>
            </div>
        </div>
    @elseif($kelasList->count() === 0)
        <!-- Empty State Ujian Belum Dihubungkan ke Kelas -->
        <div class="card" style="text-align: center; padding: 3rem 1.5rem;">
            <i class="ri-community-line" style="font-size: 3.5rem; color: #f59e0b; display: block; margin-bottom: 0.75rem;"></i>
            <h3 style="color: #1e293b; font-size: 1.2rem; margin-bottom: 0.35rem;">Ujian Belum Dihubungkan ke Kelas Manapun</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.25rem;">
                Ujian <strong>"{{ $selectedUjian->judul }}"</strong> belum memiliki kelas peserta. Silakan pilih kelas peserta di menu Kelola Ujian.
            </p>
            <div>
                <a href="{{ route('admin.ujian.index') }}" class="btn btn-outline">
                    <i class="ri-settings-4-line"></i> Atur Kelas Ujian
                </a>
            </div>
        </div>
    @else
        <!-- ================= KARTU UTAMA SIAP SCREENSHOT ================= -->
        <div class="monitoring-card" id="areaScreenshot">
            <!-- Header Kartu: Identitas Ujian & Kelas -->
            <div class="monitoring-header">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <span style="background: rgba(255,255,255,0.2); font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 4px; letter-spacing: 0.05em;">
                                LMS UIN SIBER CIREBON
                            </span>
                            <span style="background: {{ $selectedUjian->is_active ? '#22c55e' : '#64748b' }}; color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 0.2rem 0.55rem; border-radius: 4px;">
                                {{ $selectedUjian->is_active ? 'LIVE AKTIF' : 'TIDAK AKTIF' }}
                            </span>
                            <span style="background: rgba(255,255,255,0.2); font-size: 0.75rem; padding: 0.2rem 0.6rem; border-radius: 4px;">
                                {{ $selectedUjian->label }}
                            </span>
                        </div>
                        <h1 style="font-size: 1.45rem; font-weight: 800; margin: 0 0 0.35rem; color: #ffffff;">
                            {{ $selectedUjian->judul }}
                        </h1>
                        <div style="font-size: 0.9rem; opacity: 0.9; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            <span><i class="ri-book-read-line"></i> {{ $selectedUjian->mataKuliah->nama }} ({{ $selectedUjian->mataKuliah->kode }})</span>
                            <span><i class="ri-community-line"></i> Kelas: <strong>{{ $selectedKelas ? $selectedKelas->nama : 'Semua Kelas (' . $kelasList->pluck('nama')->implode(', ') . ')' }}</strong></span>
                            <span><i class="ri-time-line"></i> Durasi: {{ $selectedUjian->durasi }} Menit</span>
                        </div>
                    </div>

                    <!-- Waktu & Info Update Realtime -->
                    <div style="text-align: right; background: rgba(0,0,0,0.25); padding: 0.65rem 1rem; border-radius: 10px; border: 1px solid rgba(255,255,255,0.15);">
                        <div style="font-size: 0.72rem; text-transform: uppercase; opacity: 0.8; letter-spacing: 0.05em;">Status Jadwal Ujian</div>
                        <div style="font-size: 0.95rem; font-weight: 700;">
                            {{ $selectedUjian->waktu_mulai->format('d M Y, H:i') }} — {{ $selectedUjian->waktu_selesai->format('d M Y, H:i') }} WIB
                        </div>
                        <div style="font-size: 0.72rem; opacity: 0.75; margin-top: 0.25rem;">
                            Data diperbarui: <strong>{{ now()->translatedFormat('l, d F Y - H:i:s') }} WIB</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Konten Tengah: Visual Donut Chart & 4 Kartu Metrik -->
            <div style="padding: 1.5rem 1.75rem; background: #ffffff;">
                <div class="grid grid-3" style="align-items: center; gap: 1.5rem;">
                    <!-- Kolom Kiri: Visual Donut Ring Progress (1 Col) -->
                    <div style="text-align: center; border-right: 1px solid #e2e8f0; padding-right: 1rem;">
                        <div class="chart-container">
                            <canvas id="chartMonitoringProgress"></canvas>
                            <div class="chart-center-label">
                                <div class="percentage">{{ $stats['persen_selesai'] }}%</div>
                                <div class="desc">Selesai Ujian</div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: center; gap: 0.75rem; margin-top: 1rem; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; font-weight: 600; color: #15803d;">
                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #10b981;"></span>
                                Selesai: {{ $stats['selesai'] }} ({{ $stats['persen_selesai'] }}%)
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; font-weight: 600; color: #b45309;">
                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #f59e0b;"></span>
                                Sedang: {{ $stats['sedang'] }} ({{ $stats['persen_sedang'] }}%)
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.78rem; font-weight: 600; color: #b91c1c;">
                                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></span>
                                Belum: {{ $stats['belum'] }} ({{ $stats['persen_belum'] }}%)
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: 4 Kartu Metrik Modern (2 Cols) -->
                    <div class="span-2" style="padding-left: 0.5rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;">
                            <!-- Total Mahasiswa -->
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.15rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <span style="font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Total Mahasiswa</span>
                                    <i class="ri-team-line" style="font-size: 1.25rem; color: #475569;"></i>
                                </div>
                                <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a;">{{ $stats['total'] }}</div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                    Peserta terdaftar di kelas ini
                                </div>
                            </div>

                            <!-- Sudah Selesai -->
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1.15rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <span style="font-size: 0.8rem; font-weight: 700; color: #15803d; text-transform: uppercase;">Sudah Selesai</span>
                                    <i class="ri-checkbox-circle-fill" style="font-size: 1.25rem; color: #16a34a;"></i>
                                </div>
                                <div style="font-size: 1.85rem; font-weight: 800; color: #166534;">
                                    {{ $stats['selesai'] }} <span style="font-size: 1rem; font-weight: 600;">({{ $stats['persen_selesai'] }}%)</span>
                                </div>
                                <div style="font-size: 0.75rem; color: #15803d; margin-top: 0.25rem;">
                                    Rata-rata Nilai: <strong>{{ $stats['rata_nilai'] }}</strong>
                                </div>
                            </div>

                            <!-- Sedang Mengerjakan -->
                            <div style="background: #fefce8; border: 1px solid #fef08a; border-radius: 12px; padding: 1.15rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <span style="font-size: 0.8rem; font-weight: 700; color: #a16207; text-transform: uppercase;">Sedang Mengerjakan</span>
                                    <i class="ri-loader-4-line" style="font-size: 1.25rem; color: #ca8a04;"></i>
                                </div>
                                <div style="font-size: 1.85rem; font-weight: 800; color: #854d0e;">
                                    {{ $stats['sedang'] }} <span style="font-size: 1rem; font-weight: 600;">({{ $stats['persen_sedang'] }}%)</span>
                                </div>
                                <div style="font-size: 0.75rem; color: #a16207; margin-top: 0.25rem;">
                                    Sesi ujian aktif berlangsung
                                </div>
                            </div>

                            <!-- Belum Mengerjakan (Prioritas Screenshot) -->
                            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 1.15rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <span style="font-size: 0.8rem; font-weight: 700; color: #b91c1c; text-transform: uppercase;">Belum Mengerjakan</span>
                                    <i class="ri-alarm-warning-fill" style="font-size: 1.25rem; color: #dc2626;"></i>
                                </div>
                                <div style="font-size: 1.85rem; font-weight: 800; color: #991b1b;">
                                    {{ $stats['belum'] }} <span style="font-size: 1rem; font-weight: 600;">({{ $stats['persen_belum'] }}%)</span>
                                </div>
                                <div style="font-size: 0.75rem; color: #b91c1c; margin-top: 0.25rem; font-weight: 600;">
                                    ⚠️ Perlu dihubungi ketua kelas
                                </div>
                            </div>
                        </div>

                        <!-- Progress Bar Visual Linier -->
                        <div style="margin-top: 1.25rem;">
                            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; font-weight: 600; margin-bottom: 0.4rem;">
                                <span style="color: #475569;">Progress Partisipasi Keseluruhan (Selesai + Sedang)</span>
                                <span style="color: #0f2e5a; font-weight: 800;">{{ $stats['persen_partisipasi'] }}% ({{ $stats['selesai'] + $stats['sedang'] }} / {{ $stats['total'] }} Mahasiswa)</span>
                            </div>
                            <div style="height: 12px; background: #e2e8f0; border-radius: 9999px; overflow: hidden; display: flex;">
                                <div style="width: {{ $stats['persen_selesai'] }}%; background: #10b981;" title="Selesai: {{ $stats['selesai'] }} ({{ $stats['persen_selesai'] }}%)"></div>
                                <div style="width: {{ $stats['persen_sedang'] }}%; background: #f59e0b;" title="Sedang: {{ $stats['sedang'] }} ({{ $stats['persen_sedang'] }}%)"></div>
                                <div style="width: {{ $stats['persen_belum'] }}%; background: #ef4444;" title="Belum: {{ $stats['belum'] }} ({{ $stats['persen_belum'] }}%)"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bagian Khusus: Daftar Mahasiswa Belum Mengerjakan (Siap Copy/Screenshot untuk Ketua Kelas) -->
                @if($stats['belum'] > 0)
                    <div style="margin-top: 1.5rem; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 12px; padding: 1.25rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; color: #9f1239; font-size: 0.95rem;">
                                <i class="ri-error-warning-fill" style="font-size: 1.2rem; color: #e11d48;"></i>
                                <span>Pemberitahuan Ketua Kelas: Daftar Mahasiswa Belum Mengerjakan ({{ $stats['belum'] }} Orang)</span>
                            </div>
                            <span class="text-xs" style="color: #be123c; font-weight: 600;">
                                Mohon segera mengabari mahasiswa bersangkutan
                            </span>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            @foreach($listBelum as $idx => $item)
                                <div style="background: #ffffff; border: 1px solid #fda4af; border-radius: 8px; padding: 0.35rem 0.65rem; font-size: 0.8rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span style="color: #e11d48; font-weight: 700;">{{ $idx + 1 }}.</span>
                                    <strong style="color: #1e293b;">{{ $item['user']->name }}</strong>
                                    <span class="mono" style="color: #64748b; font-size: 0.75rem;">({{ $item['user']->username }})</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div style="margin-top: 1.5rem; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1rem 1.25rem; display: flex; align-items: center; gap: 0.75rem;">
                        <i class="ri-checkbox-circle-fill" style="font-size: 1.75rem; color: #16a34a;"></i>
                        <div>
                            <div style="font-weight: 700; color: #166534; font-size: 0.95rem;">Luar Biasa! Seluruh Mahasiswa Sudah Berpartisipasi</div>
                            <div style="font-size: 0.8rem; color: #15803d;">Semua mahasiswa pada kelas ini telah selesai atau sedang mengerjakan ujian.</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tab Filter & Tabel Rincian Semua Peserta -->
            <div style="border-top: 1px solid #e2e8f0; background: #f8fafc; padding: 1rem 1.75rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
                    <!-- Filter Tabs -->
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <button type="button" class="tab-btn active" onclick="filterTableStatus('all', this)">
                            Semua Peserta ({{ $stats['total'] }})
                        </button>
                        <button type="button" class="tab-btn" onclick="filterTableStatus('belum', this)">
                            ⚠️ Belum Mengerjakan ({{ $stats['belum'] }})
                        </button>
                        <button type="button" class="tab-btn" onclick="filterTableStatus('sedang', this)">
                            ⏳ Sedang Mengerjakan ({{ $stats['sedang'] }})
                        </button>
                        <button type="button" class="tab-btn" onclick="filterTableStatus('selesai', this)">
                            ✅ Selesai ({{ $stats['selesai'] }})
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div style="position: relative; width: 220px;">
                        <input type="text" id="inputCariPeserta" class="form-control" placeholder="Cari nama atau NIM..." onkeyup="searchTablePeserta(this.value)" style="font-size: 0.85rem; padding-left: 2rem; min-height: 36px;">
                        <i class="ri-search-line" style="position: absolute; left: 0.65rem; top: 50%; transform: translateY(-50%); color: #94a3b8;"></i>
                    </div>
                </div>

                <!-- Table Peserta -->
                <div class="table-wrap" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
                    <table class="table" id="tablePesertaMonitoring">
                        <thead>
                            <tr>
                                <th style="width: 45px; text-align: center;">NO</th>
                                <th style="width: 140px;">NIM</th>
                                <th>NAMA LENGKAP</th>
                                <th style="width: 110px;">KELAS</th>
                                <th style="width: 150px; text-align: center;">STATUS UJIAN</th>
                                <th style="width: 170px;">WAKTU AKTIVITAS</th>
                                <th style="width: 100px; text-align: center;">NILAI AKHIR</th>
                                <th style="width: 90px; text-align: center;">PELANGGARAN</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mahasiswa as $idx => $m)
                                @php
                                    $p = $pesertaMap->get($m->id);
                                    $statusKey = $p ? $p->status : 'belum';
                                @endphp
                                <tr class="row-peserta" data-status="{{ $statusKey }}" data-search="{{ strtolower($m->username . ' ' . $m->name) }}">
                                    <td style="text-align: center;" class="row-number">{{ $idx + 1 }}</td>
                                    <td>
                                        <strong class="mono" style="color: #1d4ed8; font-size: 0.88rem;">{{ $m->username }}</strong>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #1e293b;">{{ $m->name }}</div>
                                    </td>
                                    <td>
                                        <span class="badge" style="background: #f1f5f9; color: #334155; font-weight: 600;">
                                            {{ $m->kelas?->nama ?: '-' }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        @if(!$p)
                                            <span class="stat-badge belum">
                                                <i class="ri-close-circle-line"></i> Belum Mengerjakan
                                            </span>
                                        @elseif($p->status === 'berlangsung')
                                            <span class="stat-badge sedang">
                                                <i class="ri-loader-4-line"></i> Sedang Mengerjakan
                                            </span>
                                        @elseif($p->status === 'selesai')
                                            <span class="stat-badge selesai">
                                                <i class="ri-checkbox-circle-line"></i> Selesai
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$p)
                                            <span class="text-xs text-muted">Belum ada sesi</span>
                                        @elseif($p->status === 'berlangsung')
                                            <div style="font-size: 0.8rem; color: #1e293b;">
                                                Mulai: {{ $p->mulai_at?->format('H:i:s') }}
                                            </div>
                                            <div class="text-xs" style="color: #b45309; font-weight: 600;">
                                                Sisa: {{ gmdate('H:i:s', $p->sisaDetik()) }}
                                            </div>
                                        @elseif($p->status === 'selesai')
                                            <div style="font-size: 0.8rem; color: #1e293b;">
                                                Selesai: {{ $p->selesai_at?->format('H:i:s') }}
                                            </div>
                                            <div class="text-xs text-muted">
                                                Durasi: {{ $p->mulai_at && $p->selesai_at ? $p->mulai_at->diffInMinutes($p->selesai_at) . ' mnt' : '-' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @if($p && $p->status === 'selesai' && $p->nilai !== null)
                                            <strong style="font-size: 1.05rem; color: {{ (float)$p->nilai >= 70 ? '#16a34a' : '#ea580c' }};">
                                                {{ (float) $p->nilai }}
                                            </strong>
                                        @elseif($p && $p->status === 'berlangsung')
                                            <span class="text-xs" style="color: #f59e0b; font-weight: 600;">Sedang ujian</span>
                                        @else
                                            <span class="text-muted text-xs">-</span>
                                        @endif
                                    </td>
                                    <td style="text-align: center;">
                                        @if($p && $p->pelanggaran > 0)
                                            <span class="badge" style="background: #fee2e2; color: #dc2626; font-weight: 700;">
                                                {{ $p->pelanggaran }}x
                                            </span>
                                        @else
                                            <span style="color: #94a3b8; font-size: 0.85rem;">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="empty">Tidak ada mahasiswa yang ditemukan untuk kelas ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // 1. Inisialisasi Donut Chart
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('chartMonitoringProgress');
        if (ctx) {
            const dataSelesai = {{ (int) $stats['selesai'] }};
            const dataSedang = {{ (int) $stats['sedang'] }};
            const dataBelum = {{ (int) $stats['belum'] }};
            const total = {{ (int) $stats['total'] }};

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Selesai', 'Sedang Mengerjakan', 'Belum Mengerjakan'],
                    datasets: [{
                        data: total > 0 ? [dataSelesai, dataSedang, dataBelum] : [0, 0, 1],
                        backgroundColor: total > 0 ? ['#10b981', '#f59e0b', '#ef4444'] : ['#e2e8f0', '#e2e8f0', '#e2e8f0'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '76%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = context.parsed;
                                    const pct = total > 0 ? Math.round((val / total) * 100) : 0;
                                    return ` ${context.label}: ${val} orang (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    // 2. Filter Tabel Mahasiswa
    function filterTableStatus(status, btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        const rows = document.querySelectorAll('#tablePesertaMonitoring tbody .row-peserta');
        let counter = 1;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const matchStatus = (status === 'all' || rowStatus === status);
            const searchVal = document.getElementById('inputCariPeserta').value.toLowerCase().trim();
            const matchSearch = !searchVal || row.getAttribute('data-search').includes(searchVal);

            if (matchStatus && matchSearch) {
                row.style.display = '';
                const numCell = row.querySelector('.row-number');
                if (numCell) numCell.textContent = counter++;
            } else {
                row.style.display = 'none';
            }
        });
    }

    // 3. Search Pencarian Cepat
    function searchTablePeserta(query) {
        const q = query.toLowerCase().trim();
        const activeTab = document.querySelector('.tab-btn.active');
        let currentStatus = 'all';
        if (activeTab) {
            const txt = activeTab.textContent;
            if (txt.includes('Belum')) currentStatus = 'belum';
            else if (txt.includes('Sedang')) currentStatus = 'sedang';
            else if (txt.includes('Selesai')) currentStatus = 'selesai';
        }

        const rows = document.querySelectorAll('#tablePesertaMonitoring tbody .row-peserta');
        let counter = 1;

        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const matchStatus = (currentStatus === 'all' || rowStatus === currentStatus);
            const matchSearch = !q || row.getAttribute('data-search').includes(q);

            if (matchStatus && matchSearch) {
                row.style.display = '';
                const numCell = row.querySelector('.row-number');
                if (numCell) numCell.textContent = counter++;
            } else {
                row.style.display = 'none';
            }
        });
    }

    // 4. Salin Ringkasan untuk WhatsApp Ketua Kelas
    function salinPesanWhatsApp() {
        @if($selectedUjian)
            let teks = `📢 *MONITORING PROGRESS UJIAN REALTIME*\n`;
            teks += `🏛️ *LMS UIN Siber Cirebon*\n`;
            teks += `━━━━━━━━━━━━━━━━━━━━━\n`;
            teks += `📝 *Ujian*: {{ $selectedUjian->judul }} ({{ $selectedUjian->label }})\n`;
            teks += `📚 *Mata Kuliah*: {{ $selectedUjian->mataKuliah->nama }}\n`;
            teks += `👥 *Kelas*: {{ $selectedKelas ? $selectedKelas->nama : 'Semua Kelas' }}\n`;
            teks += `⏰ *Batas Ujian*: {{ $selectedUjian->waktu_selesai->format('d/m/Y H:i') }} WIB\n`;
            teks += `━━━━━━━━━━━━━━━━━━━━━\n`;
            teks += `📊 *RINGKASAN STATUS PESERTA:*\n`;
            teks += `• Total Mahasiswa : {{ $stats['total'] }} Orang\n`;
            teks += `• ✅ Selesai       : {{ $stats['selesai'] }} Orang ({{ $stats['persen_selesai'] }}%)\n`;
            teks += `• ⏳ Sedang Ujian  : {{ $stats['sedang'] }} Orang ({{ $stats['persen_sedang'] }}%)\n`;
            teks += `• ⚠️ Belum Mulai   : {{ $stats['belum'] }} Orang ({{ $stats['persen_belum'] }}%)\n\n`;

            @if($stats['belum'] > 0)
                teks += `⚠️ *DAFTAR MAHASISWA BELUM UJIAN ({{ $stats['belum'] }}):*\n`;
                @foreach($listBelum as $idx => $item)
                    teks += `{{ $idx + 1 }}. {{ $item['user']->name }} ({{ $item['user']->username }})\n`;
                @endforeach
                teks += `\n*Mohon kepada Ketua Kelas agar mengingatkan rekan-rekan di atas untuk segera menyelesaikan ujian sebelum batas waktu berakhir.* 🙏 Terima kasih.`;
            @else
                teks += `🎉 *Alhamdulillah, semua mahasiswa kelas ini sudah berpartisipasi dalam ujian!*`;
            @endif

            navigator.clipboard.writeText(teks).then(() => {
                alert('✅ Pesan ringkasan WhatsApp berhasil disalin ke clipboard!\nSilakan buka WhatsApp dan Paste (Ctrl + V) ke Ketua Kelas atau grup kelas.');
            }).catch(err => {
                prompt('Salin teks berikut:', teks);
            });
        @else
            alert('Pilih ujian terlebih dahulu.');
        @endif
    }

    // 5. Toggle Mode Screenshot (Sembunyikan Navigasi & Sidebar)
    function toggleScreenshotMode() {
        const isScreenshot = document.body.classList.toggle('screenshot-mode');
        const barExit = document.getElementById('barExitScreenshot');
        if (barExit) {
            barExit.style.display = isScreenshot ? 'block' : 'none';
        }
        if (isScreenshot) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // 6. Auto-Refresh Tiap 30 Detik
    let refreshTimer = null;
    function toggleAutoRefresh() {
        const label = document.getElementById('labelRefresh');
        const icon = document.getElementById('iconRefresh');
        const btn = document.getElementById('btnAutoRefresh');

        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
            label.textContent = 'Auto-Refresh: OFF';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline');
            icon.classList.remove('ri-loader-4-line');
            icon.classList.add('ri-refresh-line');
        } else {
            let detik = 30;
            label.textContent = `Auto-Refresh: ${detik}s`;
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-primary');
            icon.classList.remove('ri-refresh-line');
            icon.classList.add('ri-loader-4-line');

            refreshTimer = setInterval(() => {
                detik--;
                if (detik <= 0) {
                    window.location.reload();
                } else {
                    label.textContent = `Auto-Refresh: ${detik}s`;
                }
            }, 1000);
        }
    }
</script>
@endpush
@endsection
