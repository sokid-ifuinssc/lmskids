@extends('layouts.ujian')

@section('title', 'Mengerjakan Ujian: ' . $ujian->judul)

@section('content')
    <!-- Sticky Exam Header -->
    <header class="exam-header">
        <div class="exam-title-box">
            <h1>{{ $ujian->judul }}</h1>
            <div class="sub">
                {{ $ujian->mataKuliah->nama }} &bull; {{ $ujian->label }} &bull; {{ auth()->user()->name }} ({{ auth()->user()->username }})
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 1.25rem;">
            <!-- Autosave Status Indicator -->
            <div class="sync-badge" id="syncStatus">
                <i class="ri-checkbox-circle-fill"></i>
                <span>Tersimpan</span>
            </div>

            <!-- Countdown Timer -->
            <div class="timer-badge" id="timerBox">
                <i class="ri-time-line"></i>
                <span id="countdown">--:--:--</span>
            </div>

            <!-- Submit Button -->
            <button type="button" class="btn btn-sm" style="background: #10b981; color: #fff; font-weight: 700; border: 0; padding: 0.5rem 1rem;" onclick="openFinishModal()">
                <i class="ri-check-double-line"></i> Selesai Ujian
            </button>
        </div>
    </header>

    <!-- Main Workspace -->
    <main style="padding: 1.25rem; max-width: 1300px; margin: 0 auto; width: 100%; flex: 1;">
        <div class="exam-grid" style="display: grid; grid-template-columns: 1fr 320px; gap: 1.25rem; align-items: start;">

            <!-- Left: Question Area -->
            <div class="card card-accent" style="margin-bottom: 0;">
                <div class="card-body" style="padding: 1.75rem;">
                    @foreach($soalList as $idx => $s)
                        @php
                            $ans = $jawaban->get($s->id);
                        @endphp
                        <div class="soal-panel" id="panel-{{ $idx }}" style="{{ $idx === 0 ? '' : 'display: none;' }}">
                            <!-- Question Header Meta -->
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                                <div style="display: flex; align-items: center; gap: 0.6rem;">
                                    <span style="font-size: 1.1rem; font-weight: 800; color: #0f2e5a;">
                                        Soal No. {{ $idx + 1 }}
                                    </span>
                                    <span style="font-size: 0.85rem; color: #94a3b8;">/ {{ $soalList->count() }}</span>
                                    <span class="badge {{ $s->isPg() ? 'badge-blue' : 'badge-amber' }}">
                                        {{ $s->tipe_label }}
                                    </span>
                                </div>
                                <span class="badge" style="background: #f1f5f9; font-weight: 600;">
                                    Bobot: {{ $s->bobot }} Poin
                                </span>
                            </div>

                            <!-- Question Content -->
                            <div style="font-size: 1.05rem; line-height: 1.65; color: #1e293b; margin-bottom: 1.5rem; font-weight: 500;">
                                {!! nl2br(e($s->pertanyaan)) !!}
                            </div>

                            @if($s->isPg())
                                <!-- Pilihan Ganda -->
                                <div class="options-container">
                                    @foreach(['A' => $s->opsi_a, 'B' => $s->opsi_b, 'C' => $s->opsi_c, 'D' => $s->opsi_d] as $opt => $text)
                                        @php
                                            $isChecked = strtoupper((string)$ans) === $opt;
                                        @endphp
                                        <label class="opt-label {{ $isChecked ? 'selected' : '' }}" onclick="selectOption({{ $idx }}, {{ $s->id }}, '{{ $opt }}', this)">
                                            <input type="radio" name="jawaban_{{ $s->id }}" value="{{ $opt }}" {{ $isChecked ? 'checked' : '' }}>
                                            <div class="opt-circle">{{ $opt }}</div>
                                            <div style="flex: 1; font-size: 0.95rem; color: #334155; line-height: 1.4;">
                                                {{ $text }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <!-- Essay Textarea -->
                                <div>
                                    <label class="form-label" style="font-size: 0.86rem; color: #475569; margin-bottom: 0.5rem;">
                                        Tuliskan jawaban lengkap Anda di bawah ini:
                                    </label>
                                    <textarea id="essay_{{ $s->id }}"
                                              class="form-control"
                                              rows="7"
                                              placeholder="Ketikkan jawaban essay Anda di sini..."
                                              style="font-size: 0.95rem; line-height: 1.6; padding: 1rem;"
                                              oninput="onEssayInput({{ $idx }}, {{ $s->id }}, this)">{{ $ans }}</textarea>
                                    <div class="form-hint" style="display: flex; justify-content: space-between;">
                                        <span>Gunakan penjelasan yang jelas dan lengkap.</span>
                                        <span id="charCount_{{ $s->id }}">{{ strlen((string)$ans) }} Karakter</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Bottom Question Navigation Buttons -->
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid #f1f5f9;">
                                <div>
                                    @if($idx > 0)
                                        <button type="button" class="btn btn-outline" onclick="goToQuestion({{ $idx - 1 }})">
                                            <i class="ri-arrow-left-line"></i> Soal Sebelumnya
                                        </button>
                                    @endif
                                </div>

                                <div style="display: flex; gap: 0.5rem;">
                                    @if($idx < $soalList->count() - 1)
                                        <button type="button" class="btn btn-primary" onclick="goToQuestion({{ $idx + 1 }})">
                                            Soal Berikutnya <i class="ri-arrow-right-line"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-primary" style="background: #10b981; border-color: #10b981;" onclick="openFinishModal()">
                                            <i class="ri-check-line"></i> Selesai & Kirim Ujian
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Question Palette & Violation Status -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <!-- Number Palette Card -->
                <div class="card card-accent-blue" style="margin-bottom: 0;">
                    <div class="card-header" style="padding: 0.85rem 1rem;">
                        <h3 style="font-size: 0.95rem; margin: 0; display: flex; align-items: center; gap: 0.4rem; color: #1e293b;">
                            <i class="ri-grid-fill" style="color: #2563eb;"></i>
                            <span>Nomor Soal</span>
                        </h3>
                        <span class="text-xs text-muted" id="answeredCounter">
                            Terjawab: <strong id="answeredCount">0</strong> / {{ $soalList->count() }}
                        </span>
                    </div>
                    <div class="card-body" style="padding: 1rem;">
                        <div class="nav-palette">
                            @foreach($soalList as $idx => $s)
                                @php
                                    $isAnswered = trim((string)$jawaban->get($s->id)) !== '';
                                @endphp
                                <button type="button"
                                        class="btn-num {{ $idx === 0 ? 'active' : '' }} {{ $isAnswered ? 'answered' : '' }}"
                                        id="nav-btn-{{ $idx }}"
                                        onclick="goToQuestion({{ $idx }})">
                                    {{ $idx + 1 }}
                                </button>
                            @endforeach
                        </div>

                        <div style="margin-top: 1.25rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.78rem; color: #64748b;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 14px; height: 14px; border-radius: 4px; background: #10b981;"></div>
                                <span>Sudah Dijawab</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 14px; height: 14px; border-radius: 4px; background: #fff; border: 1px solid #cbd5e1;"></div>
                                <span>Belum Dijawab</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 14px; height: 14px; border-radius: 4px; border: 2px solid #2563eb;"></div>
                                <span>Soal Aktif Dibuka</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Anti-Cheat Status Card -->
                <div class="card" style="margin-bottom: 0; background: #fff; border-left: 4px solid #ef4444;">
                    <div class="card-body" style="padding: 0.9rem 1rem;">
                        <div style="font-weight: 700; font-size: 0.85rem; color: #991b1b; display: flex; align-items: center; gap: 0.35rem; margin-bottom: 0.25rem;">
                            <i class="ri-shield-alert-fill"></i> Pengawasan Anti-Kecurangan
                        </div>
                        <div class="text-xs text-muted" style="margin-bottom: 0.5rem;">
                            Jangan berpindah tab atau meminimalkan jendela ujian.
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; background: #fef2f2; padding: 0.45rem 0.75rem; border-radius: 8px; border: 1px solid #fee2e2;">
                            <span class="text-xs" style="color: #991b1b; font-weight: 600;">Pelanggaran Tab:</span>
                            <span class="badge badge-red" id="violationCountBadge">
                                {{ $attempt->pelanggaran }} / {{ $ujian->max_pelanggaran }}x
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Konfirmasi Selesai Ujian -->
    <dialog class="modal" id="modalFinish">
        <div class="modal-header">
            <h3><i class="ri-checkbox-circle-fill" style="color: #10b981;"></i> Konfirmasi Selesai Ujian</h3>
            <button type="button" class="btn-icon" onclick="this.closest('dialog').close()">&times;</button>
        </div>
        <form id="formFinishExam" action="{{ route('mhs.ujian.selesai', $ujian) }}" method="POST">
            @csrf
            <div class="modal-body">
                <p style="font-size: 0.95rem; color: #1e293b; margin-bottom: 1rem;">
                    Apakah Anda yakin ingin menyelesaikan dan mengirimkan seluruh jawaban ujian ini?
                </p>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; text-align: center;">
                    <div class="grid grid-2" style="gap: 0.5rem;">
                        <div>
                            <div class="text-xs text-muted">Soal Terjawab</div>
                            <div style="font-size: 1.4rem; font-weight: 800; color: #10b981;" id="modalAnsweredText">0</div>
                        </div>
                        <div>
                            <div class="text-xs text-muted">Belum Dijawab</div>
                            <div style="font-size: 1.4rem; font-weight: 800; color: #ef4444;" id="modalUnansweredText">0</div>
                        </div>
                    </div>
                </div>

                <div style="font-size: 0.8rem; color: #ef4444; background: #fef2f2; padding: 0.6rem 0.8rem; border-radius: 8px; border: 1px solid #fecaca;">
                    <i class="ri-information-line"></i> Setelah disubmit, Anda tidak dapat mengubah jawaban atau mengulang ujian ini kembali.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('dialog').close()">Lanjutkan Mengerjakan</button>
                <button type="submit" class="btn btn-primary" style="background: #10b981; border-color: #10b981;">
                    <i class="ri-check-double-line"></i> Ya, Selesai Ujian
                </button>
            </div>
        </form>
    </dialog>

    <!-- Modal Peringatan Pelanggaran Tab -->
    <dialog class="modal" id="modalViolation">
        <div class="modal-header" style="background: #fef2f2; border-bottom: 1px solid #fee2e2;">
            <h3 style="color: #991b1b;"><i class="ri-error-warning-fill"></i> Peringatan Pelanggaran!</h3>
        </div>
        <div class="modal-body" style="text-align: center; padding: 1.5rem 1rem;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: grid; place-items: center; font-size: 2rem; margin: 0 auto 1rem;">
                <i class="ri-alert-line"></i>
            </div>
            <h4 style="font-size: 1.15rem; margin: 0 0 0.5rem; color: #991b1b;">Anda Terdeteksi Meninggalkan Layar Ujian!</h4>
            <p style="font-size: 0.88rem; color: #475569; margin: 0 0 1rem;">
                Sistem mendeteksi bahwa Anda berpindah ke tab lain atau meminimalkan aplikasi. Pelanggaran ini telah dicatat ke sistem.
            </p>
            <div class="badge badge-red" id="violationWarningBadge" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">
                Pelanggaran: 1 / {{ $ujian->max_pelanggaran }}
            </div>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button type="button" class="btn btn-primary" onclick="this.closest('dialog').close()">
                Saya Mengerti, Kembali ke Ujian
            </button>
        </div>
    </dialog>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const totalSoal = {{ $soalList->count() }};
    let currentIdx = 0;
    let sisaDetik = {{ $attempt->sisaDetik() }};
    const maxPelanggaran = {{ $ujian->max_pelanggaran }};
    let pelanggaranCount = {{ $attempt->pelanggaran }};
    let essayDebounceTimer = null;

    // 1. Navigation Between Questions
    function goToQuestion(idx) {
        if (idx < 0 || idx >= totalSoal) return;

        // Hide current
        const curPanel = document.getElementById(`panel-${currentIdx}`);
        const curBtn = document.getElementById(`nav-btn-${currentIdx}`);
        if (curPanel) curPanel.style.display = 'none';
        if (curBtn) curBtn.classList.remove('active');

        // Show target
        currentIdx = idx;
        const nextPanel = document.getElementById(`panel-${currentIdx}`);
        const nextBtn = document.getElementById(`nav-btn-${currentIdx}`);
        if (nextPanel) nextPanel.style.display = 'block';
        if (nextBtn) nextBtn.classList.add('active');

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // 2. Option Selection (Multiple Choice)
    function selectOption(idx, soalId, val, labelEl) {
        // Toggle selected styles on label
        const container = labelEl.closest('.options-container');
        container.querySelectorAll('.opt-label').forEach(l => l.classList.remove('selected'));
        labelEl.classList.add('selected');
        labelEl.querySelector('input').checked = true;

        // Mark nav palette as answered
        const btn = document.getElementById(`nav-btn-${idx}`);
        if (btn) btn.classList.add('answered');
        updateAnsweredCount();

        // Autosave via AJAX
        saveAnswer(soalId, val);
    }

    // 3. Essay Input with Debounce
    function onEssayInput(idx, soalId, textarea) {
        const charEl = document.getElementById(`charCount_${soalId}`);
        if (charEl) charEl.textContent = `${textarea.value.length} Karakter`;

        const btn = document.getElementById(`nav-btn-${idx}`);
        if (textarea.value.trim().length > 0) {
            if (btn) btn.classList.add('answered');
        } else {
            if (btn) btn.classList.remove('answered');
        }
        updateAnsweredCount();

        clearTimeout(essayDebounceTimer);
        essayDebounceTimer = setTimeout(() => {
            saveAnswer(soalId, textarea.value);
        }, 800);
    }

    // 4. Autosave to Backend
    function saveAnswer(soalId, val) {
        const syncEl = document.getElementById('syncStatus');
        if (syncEl) {
            syncEl.className = 'sync-badge saving';
            syncEl.innerHTML = '<i class="ri-loader-4-line ri-spin"></i> <span>Menyimpan...</span>';
        }

        fetch("{{ route('mhs.ujian.jawab', $ujian) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ soal_id: soalId, jawaban: val })
        })
        .then(res => res.json())
        .then(data => {
            if (data.selesai && data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            if (syncEl) {
                syncEl.className = 'sync-badge';
                syncEl.innerHTML = '<i class="ri-checkbox-circle-fill"></i> <span>Tersimpan</span>';
            }
        })
        .catch(() => {
            if (syncEl) {
                syncEl.className = 'sync-badge saving';
                syncEl.innerHTML = '<i class="ri-wifi-off-line"></i> <span>Offline (Akan coba lagi)</span>';
            }
        });
    }

    // 5. Update Answered Count
    function updateAnsweredCount() {
        const answered = document.querySelectorAll('.btn-num.answered').length;
        const countEl = document.getElementById('answeredCount');
        if (countEl) countEl.textContent = answered;
    }
    updateAnsweredCount();

    // 6. Countdown Timer
    function startTimer() {
        const timerBox = document.getElementById('timerBox');
        const countdownEl = document.getElementById('countdown');

        function tick() {
            if (sisaDetik <= 0) {
                countdownEl.textContent = '00:00:00';
                autoSubmit('waktu');
                return;
            }

            const h = Math.floor(sisaDetik / 3600);
            const m = Math.floor((sisaDetik % 3600) / 60);
            const s = sisaDetik % 60;

            const str = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
            countdownEl.textContent = str;

            if (sisaDetik <= 300) { // under 5 minutes
                timerBox.classList.add('urgent');
            }

            sisaDetik--;
        }

        tick();
        setInterval(tick, 1000);
    }
    startTimer();

    // 7. Auto Submit on Time Expiry
    let isSubmitting = false;
    function autoSubmit(reason) {
        if (isSubmitting) return;
        isSubmitting = true;
        alert('Waktu ujian telah berakhir! Jawaban Anda akan otomatis dikirim ke sistem.');
        const form = document.getElementById('formFinishExam');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'alasan';
        input.value = reason;
        form.appendChild(input);
        form.submit();
    }

    // 8. Anti-Cheat: Tab Switch Detection
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && !isSubmitting) {
            reportViolation();
        }
    });

    function reportViolation() {
        fetch("{{ route('mhs.ujian.pelanggaran', $ujian) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            pelanggaranCount = data.pelanggaran;
            document.getElementById('violationCountBadge').textContent = `${pelanggaranCount} / ${maxPelanggaran}x`;

            if (data.selesai && data.redirect) {
                alert(`Anda telah melebihi batas toleransi keluar tab (${pelanggaranCount}/${maxPelanggaran}). Sesi ujian dihentikan otomatis.`);
                window.location.href = data.redirect;
                return;
            }

            const badge = document.getElementById('violationWarningBadge');
            if (badge) badge.textContent = `Pelanggaran: ${pelanggaranCount} / ${maxPelanggaran}x`;
            const modal = document.getElementById('modalViolation');
            if (modal) modal.showModal();
        })
        .catch(() => {});
    }

    // 9. Finish Exam Modal
    function openFinishModal() {
        const answered = document.querySelectorAll('.btn-num.answered').length;
        const unanswered = totalSoal - answered;
        document.getElementById('modalAnsweredText').textContent = answered;
        document.getElementById('modalUnansweredText').textContent = unanswered;
        document.getElementById('modalFinish').showModal();
    }
</script>
@endpush
