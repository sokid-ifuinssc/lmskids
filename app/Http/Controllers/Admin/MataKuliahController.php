<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MataKuliahController extends Controller
{
    public function index()
    {
        $matakuliah = MataKuliah::withCount(['soal', 'ujian'])->orderBy('nama')->get();

        return view('admin.matakuliah.index', compact('matakuliah'));
    }

    public function store(Request $request)
    {
        MataKuliah::create($this->validated($request));

        return back()->with('success', 'Mata kuliah ditambahkan.');
    }

    public function update(Request $request, MataKuliah $matakuliah)
    {
        $matakuliah->update($this->validated($request, $matakuliah->id));

        return back()->with('success', 'Mata kuliah diperbarui.');
    }

    public function destroy(MataKuliah $matakuliah)
    {
        if ($matakuliah->ujian()->exists()) {
            return back()->with('error', 'Mata kuliah sudah dipakai pada ujian dan tidak dapat dihapus.');
        }
        $matakuliah->delete();

        return back()->with('success', 'Mata kuliah dihapus.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:30', Rule::unique('mata_kuliah', 'kode')->ignore($ignoreId)],
            'nama' => 'required|string|max:255',
            'sks' => 'required|integer|min:1|max:10',
            'semester' => 'nullable|string|max:20',
        ]);
        $data['kode'] = strtoupper(trim($data['kode']));

        return $data;
    }
}
