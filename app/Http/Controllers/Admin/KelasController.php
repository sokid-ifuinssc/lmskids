<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::withCount(['mahasiswa', 'ujian'])->orderBy('nama')->get();

        return view('admin.kelas.index', compact('kelas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:50|unique:kelas,nama',
            'keterangan' => 'nullable|string|max:255',
        ]);
        $data['nama'] = strtoupper(trim($data['nama']));
        Kelas::create($data);

        return back()->with('success', 'Kelas ditambahkan.');
    }

    public function update(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama')->ignore($kelas->id)],
            'keterangan' => 'nullable|string|max:255',
        ]);
        $data['nama'] = strtoupper(trim($data['nama']));
        $kelas->update($data);

        return back()->with('success', 'Kelas diperbarui.');
    }

    public function destroy(Kelas $kelas)
    {
        if ($kelas->mahasiswa()->exists()) {
            return back()->with('error', 'Kelas masih memiliki mahasiswa. Pindahkan/hapus mahasiswa terlebih dahulu.');
        }
        $kelas->delete();

        return back()->with('success', 'Kelas dihapus.');
    }
}
