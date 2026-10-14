<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jawaban;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Services\PenilaianService;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    public function __construct(private PenilaianService $penilaian) {}

    /** Pastikan ujian ditujukan untuk kelas mahasiswa & sedang aktif. */
    private function cekAkses(Ujian $ujian): void
    {
        $user = auth()->user();
        $boleh = $ujian->is_active && $ujian->kelas()->where('kelas.id', $user->kelas_id)->exists();
        abort_unless($boleh, 403, 'Ujian ini tidak tersedia untuk kelas Anda.');
    }

    private function attempt(Ujian $ujian): ?UjianPeserta
    {
        return UjianPeserta::where('ujian_id', $ujian->id)->where('user_id', auth()->id())->first();
    }

    /** Selesaikan otomatis bila waktu habis. Mengembalikan true jika sudah selesai. */
    private function cekWaktu(UjianPeserta $p): bool
    {
        if ($p->isSelesai()) {
            return true;
        }
        if (now()->gt($p->batas_at->copy()->addSeconds(15))) {
            $this->penilaian->selesaikan($p, 'Waktu habis');

            return true;
        }

        return false;
    }

    public function konfirmasi(Ujian $ujian)
    {
        $this->cekAkses($ujian);
        $ujian->load('mataKuliah')->loadCount('soal');
        $attempt = $this->attempt($ujian);

        if ($attempt && $this->cekWaktu($attempt)) {
            return redirect()->route('mhs.ujian.hasil', $ujian);
        }

        return view('mahasiswa.ujian.konfirmasi', compact('ujian', 'attempt'));
    }

    public function mulai(Ujian $ujian)
    {
        $this->cekAkses($ujian);
        $attempt = $this->attempt($ujian);

        if ($attempt) {
            return $this->cekWaktu($attempt)
                ? redirect()->route('mhs.ujian.hasil', $ujian)
                : redirect()->route('mhs.ujian.kerjakan', $ujian);
        }

        if (! $ujian->bisaDikerjakan()) {
            return back()->with('error', 'Ujian belum dimulai atau sudah berakhir.');
        }

        $soalIds = $ujian->soal()->pluck('soal.id')->all();
        if (empty($soalIds)) {
            return back()->with('error', 'Ujian belum memiliki soal. Hubungi dosen.');
        }
        if ($ujian->acak_soal) {
            shuffle($soalIds);
        }

        $batas = now()->addMinutes($ujian->durasi);
        if ($batas->gt($ujian->waktu_selesai)) {
            $batas = $ujian->waktu_selesai->copy();
        }

        UjianPeserta::create([
            'ujian_id' => $ujian->id,
            'user_id' => auth()->id(),
            'mulai_at' => now(),
            'batas_at' => $batas,
            'status' => 'berlangsung',
            'urutan_soal' => $soalIds,
        ]);

        return redirect()->route('mhs.ujian.kerjakan', $ujian);
    }

    public function kerjakan(Ujian $ujian)
    {
        $this->cekAkses($ujian);
        $attempt = $this->attempt($ujian);
        if (! $attempt) {
            return redirect()->route('mhs.ujian.konfirmasi', $ujian);
        }
        if ($this->cekWaktu($attempt)) {
            return redirect()->route('mhs.ujian.hasil', $ujian);
        }

        $ujian->load('mataKuliah', 'soal');
        $urutan = $attempt->urutan_soal ?: $ujian->soal->pluck('id')->all();
        $soalList = $ujian->soal->whereIn('id', $urutan)
            ->sortBy(fn ($s) => array_search($s->id, $urutan))->values();
        $jawaban = $attempt->jawaban()->pluck('jawaban', 'soal_id');

        return response()
            ->view('mahasiswa.ujian.kerjakan', compact('ujian', 'attempt', 'soalList', 'jawaban'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function jawab(Request $request, Ujian $ujian)
    {
        $attempt = $this->attempt($ujian);
        if (! $attempt || $this->cekWaktu($attempt)) {
            return response()->json(['ok' => false, 'selesai' => true, 'redirect' => route('mhs.ujian.hasil', $ujian)]);
        }

        $data = $request->validate([
            'soal_id' => 'required|integer',
            'jawaban' => 'nullable|string|max:20000',
        ]);
        abort_unless(in_array((int) $data['soal_id'], $attempt->urutan_soal ?? []), 422);

        Jawaban::updateOrCreate(
            ['ujian_peserta_id' => $attempt->id, 'soal_id' => $data['soal_id']],
            ['jawaban' => $data['jawaban']]
        );

        return response()->json(['ok' => true, 'sisa' => $attempt->sisaDetik()]);
    }

    public function pelanggaran(Request $request, Ujian $ujian)
    {
        $attempt = $this->attempt($ujian);
        if (! $attempt || $this->cekWaktu($attempt)) {
            return response()->json(['selesai' => true, 'redirect' => route('mhs.ujian.hasil', $ujian)]);
        }

        $attempt->increment('pelanggaran');
        $max = $ujian->max_pelanggaran;

        if ($attempt->pelanggaran >= $max) {
            $this->penilaian->selesaikan($attempt, "Dihentikan otomatis: {$attempt->pelanggaran}x keluar dari halaman ujian");

            return response()->json([
                'selesai' => true,
                'pelanggaran' => $attempt->pelanggaran,
                'max' => $max,
                'redirect' => route('mhs.ujian.hasil', $ujian),
            ]);
        }

        return response()->json(['selesai' => false, 'pelanggaran' => $attempt->pelanggaran, 'max' => $max]);
    }

    public function selesai(Request $request, Ujian $ujian)
    {
        $attempt = $this->attempt($ujian);
        if ($attempt && ! $attempt->isSelesai()) {
            // simpan jawaban terakhir yang ikut terkirim (fallback jika autosave gagal)
            foreach ((array) $request->input('jawaban', []) as $soalId => $isi) {
                if (in_array((int) $soalId, $attempt->urutan_soal ?? []) && $isi !== null && $isi !== '') {
                    Jawaban::updateOrCreate(
                        ['ujian_peserta_id' => $attempt->id, 'soal_id' => (int) $soalId],
                        ['jawaban' => $isi]
                    );
                }
            }
            $this->penilaian->selesaikan($attempt, $request->input('alasan') === 'waktu' ? 'Waktu habis' : null);
        }

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('mhs.ujian.hasil', $ujian)]);
        }

        return redirect()->route('mhs.ujian.hasil', $ujian)->with('success', 'Ujian selesai. Jawaban Anda telah tersimpan.');
    }

    public function hasil(Ujian $ujian)
    {
        $attempt = $this->attempt($ujian);
        if (! $attempt) {
            return redirect()->route('mhs.dashboard');
        }
        if (! $this->cekWaktu($attempt)) {
            return redirect()->route('mhs.ujian.kerjakan', $ujian);
        }
        $ujian->load('mataKuliah', 'soal');
        $jawaban = $attempt->jawaban()->get();

        $ringkas = [
            'pg_total' => $ujian->soal->where('tipe', 'pg')->count(),
            'pg_benar' => $jawaban->where('is_benar', true)->count(),
            'essay_total' => $ujian->soal->where('tipe', 'essay')->count(),
            'terjawab' => $jawaban->filter(fn ($j) => trim((string) $j->jawaban) !== '')->count(),
        ];

        return view('mahasiswa.ujian.hasil', compact('ujian', 'attempt', 'ringkas'));
    }
}
