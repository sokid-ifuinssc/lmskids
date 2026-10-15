<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\Soal;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SoalController extends Controller
{
    public function index(Request $request)
    {
        $mkList = MataKuliah::orderBy('nama')->get();

        $soal = Soal::with('mataKuliah')->withCount('ujian')
            ->when($request->mk, fn ($q, $v) => $q->where('mata_kuliah_id', $v))
            ->when($request->tipe, fn ($q, $v) => $q->where('tipe', $v))
            ->when($request->q, fn ($q, $v) => $q->where('pertanyaan', 'like', "%$v%"))
            ->latest()
            ->paginate(15)->withQueryString();

        return view('admin.soal.index', compact('soal', 'mkList'));
    }

    public function create(Request $request)
    {
        return view('admin.soal.form', [
            'soal' => new Soal(['tipe' => $request->get('tipe', 'pg'), 'bobot' => 1, 'mata_kuliah_id' => $request->mk]),
            'mkList' => MataKuliah::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Soal::create($this->validated($request));

        $redirect = $request->boolean('lagi')
            ? redirect()->route('admin.soal.create', ['tipe' => $request->tipe, 'mk' => $request->mata_kuliah_id])
            : redirect()->route('admin.soal.index', ['mk' => $request->mata_kuliah_id]);

        return $redirect->with('success', 'Soal berhasil disimpan ke bank soal.');
    }

    public function edit(Soal $soal)
    {
        return view('admin.soal.form', [
            'soal' => $soal,
            'mkList' => MataKuliah::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Soal $soal)
    {
        $soal->update($this->validated($request));

        return redirect()->route('admin.soal.index', ['mk' => $soal->mata_kuliah_id])->with('success', 'Soal diperbarui.');
    }

    public function destroy(Soal $soal)
    {
        $soal->delete();

        return back()->with('success', 'Soal dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        if ($request->boolean('all')) {
            $query = Soal::query();
            if ($request->filled('mk')) {
                $query->where('mata_kuliah_id', $request->mk);
            }
            if ($request->filled('tipe')) {
                $query->where('tipe', $request->tipe);
            }
            if ($request->filled('q')) {
                $query->where('pertanyaan', 'like', '%' . $request->q . '%');
            }
            $n = $query->delete();

            return back()->with('success', "$n soal berhasil dihapus.");
        }

        $ids = (array) $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Tidak ada soal yang dipilih.');
        }

        $n = Soal::whereIn('id', $ids)->delete();

        return back()->with('success', "$n soal berhasil dihapus.");
    }

    public static function rules(): array
    {
        return [
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'tipe' => 'required|in:pg,essay',
            'pertanyaan' => 'required|string',
            'opsi_a' => 'required_if:tipe,pg|nullable|string',
            'opsi_b' => 'required_if:tipe,pg|nullable|string',
            'opsi_c' => 'required_if:tipe,pg|nullable|string',
            'opsi_d' => 'required_if:tipe,pg|nullable|string',
            'kunci' => 'required_if:tipe,pg|nullable|in:A,B,C,D',
            'kata_kunci' => 'required_if:tipe,essay|nullable|string',
            'bobot' => 'required|integer|min:1|max:100',
        ];
    }

    public static function attributes(): array
    {
        return [
            'mata_kuliah_id' => 'Mata kuliah', 'opsi_a' => 'Pilihan A', 'opsi_b' => 'Pilihan B',
            'opsi_c' => 'Pilihan C', 'opsi_d' => 'Pilihan D', 'kunci' => 'Kunci jawaban',
            'kata_kunci' => 'Kata kunci jawaban',
        ];
    }

    private function validated(Request $request): array
    {
        $data = $request->validate(self::rules(), [], self::attributes());
        if ($data['tipe'] === 'essay') {
            $data = array_merge($data, ['opsi_a' => null, 'opsi_b' => null, 'opsi_c' => null, 'opsi_d' => null, 'kunci' => null]);
        } else {
            $data['kata_kunci'] = null;
        }

        return $data;
    }

    // ---------------- Excel ----------------

    public function template(string $tipe, ExcelService $excel)
    {
        if ($tipe === 'essay') {
            return $excel->download('template_soal_essay.xlsx', ['pertanyaan', 'kata_kunci', 'bobot'], [
                ['Jelaskan pengertian basis data!', 'kumpulan data, terorganisir, terstruktur, tabel', 5],
                ['Sebutkan fungsi DBMS!', 'mengelola, menyimpan, keamanan, akses data', 5],
            ], 'Soal Essay', ['Kata kunci dipisahkan dengan koma. Nilai essay = bobot x persentase kata kunci yang ditemukan pada jawaban.']);
        }

        return $excel->download('template_soal_pg.xlsx', ['pertanyaan', 'a', 'b', 'c', 'd', 'kunci', 'bobot'], [
            ['Ibukota Indonesia adalah ...', 'Bandung', 'Jakarta', 'Surabaya', 'Medan', 'B', 1],
            ['2 + 2 = ...', '3', '4', '5', '6', 'B', 1],
        ], 'Soal PG', ['Kolom kunci diisi huruf A/B/C/D. Bobot default 1.']);
    }

    public function export(Request $request, ExcelService $excel)
    {
        $tipe = $request->get('tipe', 'pg') === 'essay' ? 'essay' : 'pg';
        $soal = Soal::where('tipe', $tipe)
            ->when($request->mk, fn ($q, $v) => $q->where('mata_kuliah_id', $v))
            ->orderBy('id')->get();
        $mk = $request->mk ? MataKuliah::find($request->mk) : null;
        $suffix = $mk ? '_' . $mk->kode : '';

        if ($tipe === 'essay') {
            $rows = $soal->map(fn ($s) => [$s->pertanyaan, $s->kata_kunci, $s->bobot])->all();

            return $excel->download("bank_soal_essay{$suffix}.xlsx", ['pertanyaan', 'kata_kunci', 'bobot'], $rows, 'Soal Essay');
        }
        $rows = $soal->map(fn ($s) => [$s->pertanyaan, $s->opsi_a, $s->opsi_b, $s->opsi_c, $s->opsi_d, $s->kunci, $s->bobot])->all();

        return $excel->download("bank_soal_pg{$suffix}.xlsx", ['pertanyaan', 'a', 'b', 'c', 'd', 'kunci', 'bobot'], $rows, 'Soal PG');
    }

    public function import(Request $request, ExcelService $excel)
    {
        $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'tipe' => 'required|in:pg,essay',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'ujian_id' => 'nullable|exists:ujian,id',
        ], [], ['mata_kuliah_id' => 'Mata kuliah', 'file' => 'File Excel']);

        $tipe = $request->tipe;
        $required = $tipe === 'pg' ? ['pertanyaan', 'a', 'b', 'c', 'd', 'kunci'] : ['pertanyaan', 'kata_kunci'];

        try {
            $rows = $excel->read($request->file('file'), $required);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        $masuk = $lewati = 0;
        $ids = [];
        DB::transaction(function () use ($rows, $tipe, $request, &$masuk, &$lewati, &$ids) {
            foreach ($rows as $r) {
                $bobot = max(1, (int) ($r['bobot'] ?? 1) ?: 1);
                if ($tipe === 'pg') {
                    $kunci = strtoupper(trim($r['kunci'] ?? ''));
                    if (($r['pertanyaan'] ?? '') === '' || ! in_array($kunci, ['A', 'B', 'C', 'D'])
                        || ($r['a'] ?? '') === '' || ($r['b'] ?? '') === '' || ($r['c'] ?? '') === '' || ($r['d'] ?? '') === '') {
                        $lewati++;
                        continue;
                    }
                    $soal = Soal::create([
                        'mata_kuliah_id' => $request->mata_kuliah_id, 'tipe' => 'pg',
                        'pertanyaan' => $r['pertanyaan'], 'opsi_a' => $r['a'], 'opsi_b' => $r['b'],
                        'opsi_c' => $r['c'], 'opsi_d' => $r['d'], 'kunci' => $kunci, 'bobot' => $bobot,
                    ]);
                } else {
                    if (($r['pertanyaan'] ?? '') === '' || ($r['kata_kunci'] ?? '') === '') {
                        $lewati++;
                        continue;
                    }
                    $soal = Soal::create([
                        'mata_kuliah_id' => $request->mata_kuliah_id, 'tipe' => 'essay',
                        'pertanyaan' => $r['pertanyaan'], 'kata_kunci' => $r['kata_kunci'], 'bobot' => $bobot,
                    ]);
                }
                $ids[] = $soal->id;
                $masuk++;
            }

            // Jika import dilakukan dari halaman ujian, langsung pasang ke ujian tersebut
            if ($request->ujian_id && $ids) {
                $ujian = \App\Models\Ujian::find($request->ujian_id);
                $start = (int) $ujian->soal()->max('ujian_soal.urutan');
                $attach = [];
                foreach ($ids as $i => $id) {
                    $attach[$id] = ['urutan' => $start + $i + 1];
                }
                $ujian->soal()->syncWithoutDetaching($attach);
            }
        });

        return back()->with('success', "Import selesai: $masuk soal masuk ke bank soal" . ($request->ujian_id ? ' & ujian' : '') . ", $lewati baris dilewati.");
    }
}
