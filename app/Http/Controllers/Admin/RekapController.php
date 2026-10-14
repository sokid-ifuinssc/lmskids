<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Services\ExcelService;
use App\Services\LeggerService;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request, LeggerService $legger)
    {
        $mkList = MataKuliah::orderBy('nama')->get();
        $kelasList = Kelas::orderBy('nama')->get();

        $mkId = (int) ($request->mk ?: $mkList->first()?->id);
        $kelasId = $request->kelas ? (int) $request->kelas : null;

        $data = $mkId ? $legger->build($mkId, $kelasId) : ['ujian' => collect(), 'rows' => collect()];

        return view('admin.rekap.index', array_merge($data, compact('mkList', 'kelasList', 'mkId', 'kelasId')));
    }

    public function export(Request $request, LeggerService $legger, ExcelService $excel)
    {
        $mk = MataKuliah::findOrFail($request->mk);
        $kelas = $request->kelas ? Kelas::find($request->kelas) : null;
        $data = $legger->build($mk->id, $kelas?->id);

        $headers = array_merge(['No', 'NIM', 'Nama', 'Kelas'], $data['ujian']->pluck('label')->all());
        $rows = $data['rows']->values()->map(function ($r, $i) {
            return array_merge(
                [$i + 1, $r['user']->username, $r['user']->name, $r['user']->kelas?->nama],
                array_map(fn ($v) => $v === null ? '-' : (float) $v, array_values($r['nilai']))
            );
        })->all();

        return $excel->download(
            'legger_' . $mk->kode . ($kelas ? '_' . $kelas->nama : '') . '.xlsx',
            $headers, $rows, 'Legger',
            ['LEGGER NILAI - ' . $mk->kode . ' ' . $mk->nama . ($kelas ? ' - Kelas ' . $kelas->nama : '')]
        );
    }
}
