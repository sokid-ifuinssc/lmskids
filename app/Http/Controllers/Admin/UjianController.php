<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jawaban;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Soal;
use App\Models\Ujian;
use App\Models\UjianPeserta;
use App\Models\User;
use App\Services\ExcelService;
use App\Services\PenilaianService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UjianController extends Controller
{
    public function index(Request $request)
    {
        $ujian = Ujian::with(['mataKuliah', 'kelas'])
            ->withCount(['soal', 'peserta'])
            ->when($request->mk, fn ($q, $v) => $q->where('mata_kuliah_id', $v))
            ->when($request->jenis, fn ($q, $v) => $q->where('jenis', $v))
            ->latest('waktu_mulai')
            ->paginate(10)->withQueryString();

        return view('admin.ujian.index', [
            'ujian' => $ujian,
            'mkList' => MataKuliah::orderBy('nama')->get(),
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $ujian = Ujian::create($data);
        $ujian->kelas()->sync($request->input('kelas_ids', []));

        return redirect()->route('admin.ujian.soal', $ujian)
            ->with('success', 'Ujian dibuat. Silakan pilih / generate soal dari bank soal.');
    }

    public function edit(Ujian $ujian)
    {
        return view('admin.ujian.edit', [
            'ujian' => $ujian->load('kelas'),
            'mkList' => MataKuliah::orderBy('nama')->get(),
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Ujian $ujian)
    {
        $ujian->update($this->validated($request, $ujian->id));
        $ujian->kelas()->sync($request->input('kelas_ids', []));

        return redirect()->route('admin.ujian.index')->with('success', 'Ujian diperbarui.');
    }

    public function destroy(Ujian $ujian)
    {
        $ujian->delete();

        return back()->with('success', 'Ujian dihapus beserta hasilnya.');
    }

    public function toggle(Ujian $ujian)
    {
        if (! $ujian->is_active) {
            if ($ujian->soal()->count() === 0) {
                return back()->with('error', 'Ujian belum memiliki soal. Tambahkan soal sebelum mengaktifkan.');
            }
            if ($ujian->kelas()->count() === 0) {
                return back()->with('error', 'Ujian belum memiliki kelas tujuan.');
            }
        }
        $ujian->update(['is_active' => ! $ujian->is_active]);

        return back()->with('success', 'Ujian ' . ($ujian->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('ujian', 'kode')->ignore($ignoreId)],
            'judul' => 'required|string|max:255',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'jenis' => 'required|in:CPMK,UTS,UAS',
            'cpmk_ke' => 'nullable|required_if:jenis,CPMK|integer|min:1|max:20',
            'durasi' => 'required|integer|min:1|max:600',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'max_pelanggaran' => 'required|integer|min:1|max:10',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'keterangan' => 'nullable|string',
        ], [], [
            'kode' => 'Kode ujian', 'mata_kuliah_id' => 'Mata kuliah', 'cpmk_ke' => 'CPMK ke-',
            'waktu_mulai' => 'Waktu mulai', 'waktu_selesai' => 'Waktu selesai', 'kelas_ids' => 'Kelas tujuan',
            'max_pelanggaran' => 'Batas pelanggaran',
        ]);

        unset($data['kelas_ids']);
        $data['kode'] = strtoupper(trim($data['kode']));
        $data['cpmk_ke'] = $data['jenis'] === 'CPMK' ? $data['cpmk_ke'] : null;
        $data['acak_soal'] = $request->boolean('acak_soal');
        $data['tampilkan_nilai'] = $request->boolean('tampilkan_nilai');

        return $data;
    }

    // ================= Soal ujian =================

    public function soal(Request $request, Ujian $ujian)
    {
        $ujian->load(['mataKuliah', 'kelas', 'soal']);
        $terpasang = $ujian->soal->pluck('id');

        $bank = Soal::where('mata_kuliah_id', $ujian->mata_kuliah_id)
            ->whereNotIn('id', $terpasang)
            ->when($request->tipe, fn ($q, $v) => $q->where('tipe', $v))
            ->when($request->q, fn ($q, $v) => $q->where('pertanyaan', 'like', "%$v%"))
            ->latest()->get();

        $sisaPg = Soal::where('mata_kuliah_id', $ujian->mata_kuliah_id)->where('tipe', 'pg')->whereNotIn('id', $terpasang)->count();
        $sisaEssay = Soal::where('mata_kuliah_id', $ujian->mata_kuliah_id)->where('tipe', 'essay')->whereNotIn('id', $terpasang)->count();

        return view('admin.ujian.soal', compact('ujian', 'bank', 'sisaPg', 'sisaEssay'));
    }

    private function attach(Ujian $ujian, array $ids): int
    {
        $existing = $ujian->soal()->pluck('soal.id')->all();
        $ids = array_values(array_diff(array_map('intval', $ids), $existing));
        $start = (int) DB::table('ujian_soal')->where('ujian_id', $ujian->id)->max('urutan');
        $attach = [];
        foreach ($ids as $i => $id) {
            $attach[$id] = ['urutan' => $start + $i + 1];
        }
        $ujian->soal()->attach($attach);

        return count($attach);
    }

    public function attachSoal(Request $request, Ujian $ujian)
    {
        $ids = Soal::where('mata_kuliah_id', $ujian->mata_kuliah_id)
            ->whereIn('id', (array) $request->input('ids', []))->pluck('id')->all();
        $n = $this->attach($ujian, $ids);

        return back()->with('success', "$n soal ditambahkan ke ujian.");
    }

    public function generateSoal(Request $request, Ujian $ujian)
    {
        $data = $request->validate([
            'jumlah_pg' => 'nullable|integer|min:0|max:500',
            'jumlah_essay' => 'nullable|integer|min:0|max:100',
        ]);
        $terpasang = $ujian->soal()->pluck('soal.id');

        $pick = function ($tipe, $n) use ($ujian, $terpasang) {
            if ($n <= 0) {
                return [];
            }

            return Soal::where('mata_kuliah_id', $ujian->mata_kuliah_id)->where('tipe', $tipe)
                ->whereNotIn('id', $terpasang)->inRandomOrder()->limit($n)->pluck('id')->all();
        };

        $ids = array_merge($pick('pg', (int) ($data['jumlah_pg'] ?? 0)), $pick('essay', (int) ($data['jumlah_essay'] ?? 0)));
        $n = $this->attach($ujian, $ids);

        return back()->with('success', "Generate selesai: $n soal diambil acak dari bank soal.");
    }

    public function storeSoalBaru(Request $request, Ujian $ujian)
    {
        $request->merge(['mata_kuliah_id' => $ujian->mata_kuliah_id]);
        $data = $request->validate(SoalController::rules(), [], SoalController::attributes());
        if ($data['tipe'] === 'essay') {
            $data = array_merge($data, ['opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null, 'kunci' => null]);
        } else {
            $data['kata_kunci'] = null;
        }
        $soal = Soal::create($data);
        $this->attach($ujian, [$soal->id]);

        return back()->with('success', 'Soal baru disimpan ke bank soal dan ditambahkan ke ujian.');
    }

    public function detachSoal(Ujian $ujian, Soal $soal)
    {
        $ujian->soal()->detach($soal->id);

        return back()->with('success', 'Soal dilepas dari ujian (tetap ada di bank soal).');
    }

    public function detachAll(Ujian $ujian)
    {
        $ujian->soal()->detach();

        return back()->with('success', 'Semua soal dilepas dari ujian.');
    }

    // ================= Hasil =================

    public function hasil(Request $request, Ujian $ujian)
    {
        $ujian->load(['mataKuliah', 'kelas'])->loadCount('soal');
        $kelasIds = $request->kelas ? [(int) $request->kelas] : $ujian->kelas->pluck('id')->all();

        $mahasiswa = User::mahasiswa()->with('kelas')->whereIn('kelas_id', $kelasIds)
            ->orderBy('kelas_id')->orderBy('username')->get();
        $peserta = UjianPeserta::where('ujian_id', $ujian->id)->get()->keyBy('user_id');

        $ringkas = [
            'total' => $mahasiswa->count(),
            'selesai' => $peserta->where('status', 'selesai')->whereIn('user_id', $mahasiswa->pluck('id'))->count(),
            'berlangsung' => $peserta->where('status', 'berlangsung')->whereIn('user_id', $mahasiswa->pluck('id'))->count(),
            'rata' => round((float) $peserta->where('status', 'selesai')->whereIn('user_id', $mahasiswa->pluck('id'))->avg('nilai'), 2),
        ];

        return view('admin.ujian.hasil', compact('ujian', 'mahasiswa', 'peserta', 'ringkas'));
    }

    public function exportHasil(Ujian $ujian, ExcelService $excel)
    {
        $ujian->load(['mataKuliah', 'kelas']);
        $mahasiswa = User::mahasiswa()->with('kelas')->whereIn('kelas_id', $ujian->kelas->pluck('id'))
            ->orderBy('kelas_id')->orderBy('username')->get();
        $peserta = UjianPeserta::where('ujian_id', $ujian->id)->get()->keyBy('user_id');

        $rows = $mahasiswa->values()->map(function ($m, $i) use ($peserta) {
            $p = $peserta->get($m->id);

            return [
                $i + 1, $m->username, $m->name, $m->kelas?->nama,
                $p ? ($p->isSelesai() ? 'Selesai' : 'Berlangsung') : 'Belum ikut',
                $p?->nilai ?? '', $p?->pelanggaran ?? '', $p?->selesai_at?->format('d/m/Y H:i') ?? '', $p?->catatan ?? '',
            ];
        })->all();

        return $excel->download(
            'hasil_' . $ujian->kode . '.xlsx',
            ['No', 'NIM', 'Nama', 'Kelas', 'Status', 'Nilai', 'Pelanggaran', 'Selesai', 'Catatan'],
            $rows,
            'Hasil',
            [$ujian->judul . ' - ' . $ujian->mataKuliah->nama . ' (' . $ujian->label . ')']
        );
    }

    public function detailPeserta(Ujian $ujian, UjianPeserta $peserta, PenilaianService $penilaian)
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);
        $peserta->load(['user.kelas', 'jawaban']);
        $ujian->load('soal', 'mataKuliah');
        $jawaban = $peserta->jawaban->keyBy('soal_id');
        $urutan = $peserta->urutan_soal ?: $ujian->soal->pluck('id')->all();
        $soalList = $ujian->soal->sortBy(fn ($s) => array_search($s->id, $urutan))->values();

        return view('admin.ujian.peserta', compact('ujian', 'peserta', 'jawaban', 'soalList', 'penilaian'));
    }

    public function updateSkor(Request $request, Ujian $ujian, UjianPeserta $peserta, PenilaianService $penilaian)
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);
        $skor = (array) $request->input('skor', []);
        $soalMap = $ujian->soal->keyBy('id');

        foreach ($skor as $soalId => $nilai) {
            $soal = $soalMap->get((int) $soalId);
            if (! $soal || $soal->isPg() || $nilai === null || $nilai === '') {
                continue;
            }
            $nilai = max(0, min((float) $nilai, (float) $soal->bobot));
            Jawaban::updateOrCreate(
                ['ujian_peserta_id' => $peserta->id, 'soal_id' => $soal->id],
                ['skor' => $nilai, 'skor_manual' => true]
            );
        }
        $penilaian->hitungNilai($peserta);

        return back()->with('success', 'Skor essay diperbarui. Nilai akhir: ' . $peserta->nilai);
    }

    public function resetPeserta(Ujian $ujian, UjianPeserta $peserta)
    {
        abort_unless($peserta->ujian_id === $ujian->id, 404);
        $peserta->delete();

        return back()->with('success', 'Data ujian peserta direset. Mahasiswa dapat mengulang ujian.');
    }
}
