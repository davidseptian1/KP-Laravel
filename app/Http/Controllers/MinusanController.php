<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Minusan;
use App\Exports\MinusanExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Exports\RekapBulananExport;
use App\Exports\RekapTilanganExport;
use Carbon\Carbon;

class MinusanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $minusan = Minusan::get()->map(function ($item) {
            $item->total_per_orang = $item->qty > 0 ? $item->total / $item->qty : 0;
            return $item;
        });

        $data = [
            'title' => 'Data Minusan',
            'menuAdminMinusan' => 'active',
            'minusan' => $minusan,
        ];

        if ($user->jabatan == 'Admin') {
            return view('admin/minusan/index', $data);
        } elseif ($user->jabatan == 'HRD') {
            return view('hrd/minusan/index', $data);
        } else {
            return view('staff/minusan/index', $data);
        }
    }

    public function create()
    {
        if (Auth::user()->jabatan == 'HRD') {
            abort(403, 'Akses ditolak. HRD hanya dapat melihat laporan.');
        }

        $data = [
            'title' => 'Tambah Data Minusan',
            'menuAdminMinusan' => 'active',
        ];
        return view('admin/minusan/create', $data);
    }

    public function store(Request $request)
    {
        if (Auth::user()->jabatan == 'HRD') {
            abort(403, 'Akses ditolak. HRD hanya dapat melihat laporan.');
        }

        $request->validate(
            [
                'tanggal' => 'required|date',
                'server' => 'required|string',
                'nama' => [
                    'required',
                    'string',
                    'regex:/^[\pL\s,]+$/u'
                ],
                'spl' => 'required|string',
                'produk' => 'required|string',
                'nomor' => 'required|string',
                'total' => 'required|numeric',
                'qty' => 'required|integer|min:1',
                'keterangan' => 'required|string',
            ],
            [
                'tanggal.required' => 'Tanggal Tidak Boleh Kosong',
                'server.required' => 'Server Tidak Boleh Kosong',
                'nama.required' => 'Nama Tidak Boleh Kosong',
                'nama.regex' => 'Nama hanya boleh berisi huruf koma dan spasi.',
                'spl.required' => 'SPL Harus di Pilih',
                'produk.required' => 'Produk Harus di Pilih',
                'nomor.required' => 'Nomor Tidak Boleh Kosong',
                'total.required' => 'Total Tidak Boleh Kosong',
                'qty.required' => 'Qty Tidak Boleh Kosong',
                'keterangan.required' => 'Keterangan Harus di Pilih',
            ],
        );

        $minusan = new Minusan();
        $minusan->tanggal = $request->input('tanggal');
        $minusan->server = $request->input('server');
        $minusan->nama = $request->input('nama');
        $minusan->spl = $request->input('spl');
        $minusan->produk = $request->input('produk');
        $minusan->nomor = $request->input('nomor');
        $minusan->total = $request->input('total');
        $minusan->qty = $request->input('qty');
        $minusan->total_per_orang = $request->input('qty') > 0 ? $request->input('total') / $request->input('qty') : 0;
        $minusan->keterangan = $request->input('keterangan');

        $minusan->save();

        return redirect()->route('minusan')->with('success', 'Data Berhasil di Tambahkan');
    }

    public function edit($id)
    {
        if (Auth::user()->jabatan == 'HRD') {
            abort(403, 'Akses ditolak. HRD hanya dapat melihat laporan.');
        }

        $data = [
            'title' => 'Edit Data Minusan',
            'menuAdminMinusan' => 'active',
            'minusan' => Minusan::findOrFail($id),
        ];
        return view('admin/minusan/update', $data);
    }

    public function update(Request $request, $id)
    {
        if (Auth::user()->jabatan == 'HRD') {
            abort(403, 'Akses ditolak. HRD hanya dapat melihat laporan.');
        }

        $request->validate(
            [
                'tanggal' => 'required|date',
                'server' => 'required|string',
                'nama' => [
                    'required',
                    'string',
                    'regex:/^[\pL\s,]+$/u'
                ],
                'spl' => 'required|string',
                'produk' => 'required|string',
                'nomor' => 'required|string',
                'total' => 'required|numeric',
                'qty' => 'required|integer|min:1',
                'keterangan' => 'required|string',
            ],
            [
                'tanggal.required' => 'Tanggal Tidak Boleh Kosong',
                'server.required' => 'Server Tidak Boleh Kosong',
                'nama.required' => 'Nama Tidak Boleh Kosong',
                'nama.regex' => 'Nama hanya boleh berisi huruf koma dan spasi.',
                'spl.required' => 'SPL Harus di Pilih',
                'produk.required' => 'Produk Harus di Pilih',
                'nomor.required' => 'Nomor Tidak Boleh Kosong',
                'total.required' => 'Total Tidak Boleh Kosong',
                'qty.required' => 'Qty Tidak Boleh Kosong',
                'keterangan.required' => 'Keterangan Harus di Pilih',
            ],
        );

        $minusan = Minusan::findOrFail($id);
        $minusan->tanggal = $request->input('tanggal');
        $minusan->server = $request->input('server');
        $minusan->nama = $request->input('nama');
        $minusan->spl = $request->input('spl');
        $minusan->produk = $request->input('produk');
        $minusan->nomor = $request->input('nomor');
        $minusan->total = $request->input('total');
        $minusan->qty = $request->input('qty');
        $minusan->total_per_orang = $request->input('qty') > 0 ? $request->input('total') / $request->input('qty') : 0;
        $minusan->keterangan = $request->input('keterangan');

        $minusan->save();

        return redirect()->route('minusan')->with('success', 'Data Berhasil di Update');
    }


    public function delete($id)
    {
        $minusan = Minusan::findOrFail($id);

        return view('minusan.delete', [
            'title' => 'Hapus Data Minusan',
            'minusan' => $minusan,
        ]);
    }

    public function destroy($id)
    {
        if (Auth::user()->jabatan == 'HRD') {
            abort(403, 'Akses ditolak. HRD hanya dapat melihat laporan.');
        }

        $minusan = Minusan::findOrFail($id);
        $minusan->delete();

        return redirect()->route('minusan')->with('success', 'Data Berhasil di Hapus');
    }


    public function excel()
    {
        if (Auth::user()->jabatan == 'HRD') {
            abort(403, 'Akses ditolak. HRD hanya dapat melihat laporan.');
        }
        $filename = now()->format('d-m-Y');
        return Excel::download(new MinusanExport(), 'DataMinusan_' . $filename . '.xlsx');
    }

    public function pdf()
    {
        if (Auth::user()->jabatan == 'HRD') {
            abort(403, 'Akses ditolak. HRD hanya dapat melihat laporan.');
        }
        $filename = now()->format('d-m-Y');
        $data = [
            'minusan' => Minusan::get(),
        ];

        $pdf = Pdf::loadView('admin/minusan/pdf', $data);
        return $pdf->stream('DataMinusan_' . $filename . '.pdf');
    }

    public function chartMinusan()
    {
        $data = DB::table('minusans')
            ->selectRaw('
            DATE_FORMAT(tanggal, "%Y-%m") as bulan,
            SUM(total) as total_bulanan
            ')
            ->groupBy('bulan')
            ->orderBy('bulan', 'asc')
            ->get();

        return response()->json($data);
    }

    public function rekapBulanan(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $tahunList = DB::table('minusans')->selectRaw('YEAR(tanggal) as tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        if (!$tahunList->contains(date('Y'))) {
            $tahunList->prepend(date('Y'));
        }

        $minusan = DB::table('minusans')->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        return view('admin.rekap-bulanan.index', [
            'title' => 'Rekap Bulanan',
            'menuAdminRekap' => 'active',
            'minusan' => $minusan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tahunList' => $tahunList,
        ]);
    }

    public function rekapTilangan(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $tahunList = DB::table('minusans')->selectRaw('YEAR(tanggal) as tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        if (!$tahunList->contains(date('Y'))) {
            $tahunList->prepend(date('Y'));
        }

        $start = Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-24', $tahun, $bulan));
        $end = (clone $start)->addMonth()->setDay(23);

        $startDate = $start->format('Y-m-d');
        $endDate = $end->format('Y-m-d');

        $rows = DB::table('minusans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->select('nama', 'total', 'qty', 'total_per_orang')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $names = preg_split('/\s*,\s*/u', trim($row->nama));
            $share = null;
            if (!is_null($row->total_per_orang) && is_numeric($row->total_per_orang) && floatval($row->total_per_orang) > 0) {
                $share = floatval($row->total_per_orang);
            } else {
                if (!is_null($row->qty) && intval($row->qty) > 0) {
                    $share = floatval($row->total) / intval($row->qty);
                } else {
                    $count = max(1, count($names));
                    $share = floatval($row->total) / $count;
                }
            }

            foreach ($names as $name) {
                $key = mb_strtolower(trim($name));
                if ($key === '') continue;
                if (!isset($map[$key])) {
                    $map[$key] = [
                        'display' => mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8'),
                        'total' => 0.0,
                    ];
                }
                $map[$key]['total'] += $share;
            }
        }

        $minusan = collect($map)
            ->map(function ($item, $key) {
                return (object) [
                    'nama' => $item['display'],
                    'total_tilangan' => round($item['total'], 2),
                ];
            })
            ->sortByDesc('total_tilangan')
            ->values();

        return view('admin.rekap-tilangan.index', [
            'title' => 'Rekap Tilangan',
            'menuAdminTilangan' => 'active',
            'minusan' => $minusan,
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tahunList' => $tahunList,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function rekapTilanganPdf($bulan, $tahun)
    {
        $start = Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-24', $tahun, $bulan));
        $end = (clone $start)->addMonth()->setDay(23);

        $startDate = $start->format('Y-m-d');
        $endDate = $end->format('Y-m-d');

        $rows = DB::table('minusans')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->select('nama', 'total', 'qty', 'total_per_orang')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $names = preg_split('/\s*,\s*/u', trim($row->nama));
            $share = null;
            if (!is_null($row->total_per_orang) && is_numeric($row->total_per_orang) && floatval($row->total_per_orang) > 0) {
                $share = floatval($row->total_per_orang);
            } else {
                if (!is_null($row->qty) && intval($row->qty) > 0) {
                    $share = floatval($row->total) / intval($row->qty);
                } else {
                    $count = max(1, count($names));
                    $share = floatval($row->total) / $count;
                }
            }

            foreach ($names as $name) {
                $key = mb_strtolower(trim($name));
                if ($key === '') continue;
                if (!isset($map[$key])) {
                    $map[$key] = [
                        'display' => mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8'),
                        'total' => 0.0,
                    ];
                }
                $map[$key]['total'] += $share;
            }
        }

        $minusan = collect($map)
            ->map(function ($item, $key) {
                return (object) [
                    'nama' => $item['display'],
                    'total_tilangan' => round($item['total'], 2),
                ];
            })
            ->sortByDesc('total_tilangan')
            ->values();

        $pdf = PDF::loadView('admin.rekap-tilangan.rekap-pdf', compact('minusan', 'start', 'end'));
        return $pdf->stream("Rekap-Tilangan-{$bulan}-{$tahun}.pdf");
    }

    public function rekapTilanganExcel($bulan, $tahun)
    {
        $start = Carbon::createFromFormat('Y-m-d', sprintf('%04d-%02d-24', $tahun, $bulan));
        $end = (clone $start)->addMonth()->setDay(23);

        return Excel::download(new RekapTilanganExport($start->format('Y-m-d'), $end->format('Y-m-d')), "Rekap-Tilangan-{$bulan}-{$tahun}.xlsx");
    }

    public function rekapBulananPdf($bulan, $tahun)
    {
        $minusan = DB::table('minusans')->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();

        $pdf = PDF::loadView('admin.rekap-bulanan.rekap-pdf', compact('minusan', 'bulan', 'tahun'));
        return $pdf->stream("Rekap-Bulanan-{$bulan}-{$tahun}.pdf");
    }

    public function rekapBulananExcel($bulan, $tahun)
    {
        return Excel::download(new RekapBulananExport($bulan, $tahun), "Rekap-Bulanan-{$bulan}-{$tahun}.xlsx");
    }
}
