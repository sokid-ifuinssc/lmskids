<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\User;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    public function index(Request $request)
    {
        $kelasList = Kelas::withCount('mahasiswa')->orderBy('nama')->get();

        $mahasiswa = User::mahasiswa()->with('kelas')
            ->when($request->kelas, fn ($q, $k) => $q->where('kelas_id', $k))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('username', 'like', "%$s%")->orWhere('name', 'like', "%$s%")))
            ->orderBy('kelas_id')->orderBy('username')
            ->paginate(15)->withQueryString();

        return view('admin.mahasiswa.index', compact('mahasiswa', 'kelasList'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:4',
            'kelas' => 'required|string|max:50',
        ], [], ['username' => 'NIM', 'name' => 'Nama', 'kelas' => 'Kelas']);

        User::create([
            'username' => trim($data['username']),
            'name' => $data['name'],
            'password' => $data['password'],
            'role' => 'mahasiswa',
            'kelas_id' => Kelas::findOrCreateByName($data['kelas'])->id,
        ]);

        return back()->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function edit(User $mahasiswa)
    {
        abort_if($mahasiswa->isAdmin(), 404);

        return view('admin.mahasiswa.edit', [
            'mhs' => $mahasiswa,
            'kelasList' => Kelas::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, User $mahasiswa)
    {
        abort_if($mahasiswa->isAdmin(), 404);
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($mahasiswa->id)],
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:4',
            'kelas' => 'required|string|max:50',
        ], [], ['username' => 'NIM', 'name' => 'Nama', 'kelas' => 'Kelas']);

        $mahasiswa->username = trim($data['username']);
        $mahasiswa->name = $data['name'];
        $mahasiswa->kelas_id = Kelas::findOrCreateByName($data['kelas'])->id;
        if (! empty($data['password'])) {
            $mahasiswa->password = $data['password'];
        }
        $mahasiswa->save();

        return redirect()->route('admin.mahasiswa.index')->with('success', 'Data mahasiswa diperbarui.');
    }

    public function destroy(User $mahasiswa)
    {
        abort_if($mahasiswa->isAdmin(), 404);
        $mahasiswa->delete();

        return back()->with('success', 'Mahasiswa dihapus.');
    }

    public function template(ExcelService $excel)
    {
        return $excel->download('template_mahasiswa.xlsx', ['nim', 'nama', 'password', 'kelas'], [
            ['2530801001', 'Contoh Mahasiswa Satu', '123456', '2A'],
            ['2530801002', 'Contoh Mahasiswa Dua', '123456', '2B'],
        ], 'Mahasiswa');
    }

    public function export(Request $request, ExcelService $excel)
    {
        $rows = User::mahasiswa()->with('kelas')
            ->when($request->kelas, fn ($q, $k) => $q->where('kelas_id', $k))
            ->orderBy('kelas_id')->orderBy('username')->get()
            ->values()
            ->map(fn ($m, $i) => [$i + 1, $m->username, $m->name, $m->kelas?->nama])
            ->all();

        return $excel->download('data_mahasiswa.xlsx', ['No', 'NIM', 'Nama', 'Kelas'], $rows, 'Mahasiswa');
    }

    public function import(Request $request, ExcelService $excel)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        try {
            $rows = $excel->read($request->file('file'), ['nim', 'nama', 'kelas']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file: ' . $e->getMessage());
        }

        $baru = $update = $lewati = 0;
        DB::transaction(function () use ($rows, &$baru, &$update, &$lewati) {
            foreach ($rows as $r) {
                $nim = trim($r['nim'] ?? '');
                if ($nim === '' || trim($r['nama'] ?? '') === '' || trim($r['kelas'] ?? '') === '') {
                    $lewati++;
                    continue;
                }
                $kelas = Kelas::findOrCreateByName($r['kelas']);
                $user = User::where('username', $nim)->first();
                if ($user && $user->isAdmin()) {
                    $lewati++;
                    continue;
                }
                if ($user) {
                    $user->fill(['name' => $r['nama'], 'kelas_id' => $kelas->id]);
                    if (! empty($r['password'])) {
                        $user->password = $r['password'];
                    }
                    $user->save();
                    $update++;
                } else {
                    User::create([
                        'username' => $nim,
                        'name' => $r['nama'],
                        'password' => ($r['password'] ?? '') !== '' ? $r['password'] : $nim,
                        'role' => 'mahasiswa',
                        'kelas_id' => $kelas->id,
                    ]);
                    $baru++;
                }
            }
        });

        return back()->with('success', "Import selesai: $baru baru, $update diperbarui, $lewati dilewati.");
    }
}
