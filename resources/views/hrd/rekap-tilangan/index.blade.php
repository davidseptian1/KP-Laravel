@extends('layouts/app')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <form method="GET" action="{{ route('admin.rekap.tilangan') }}"
                class="d-flex align-items-center flex-wrap gap-2 mb-2 mb-xl-0">

                <select name="bulan" class="form-control form-control-sm w-auto">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ sprintf('%02d', $i) }}" {{ $bulan == sprintf('%02d', $i) ? 'selected' : '' }}>
                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                        </option>
                    @endfor
                </select>

                <select name="tahun" class="form-control form-control-sm w-auto ml-2">
                    @foreach ($tahunList as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                            {{ $t }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-sm btn-primary ml-2">
                    <i class="fas fa-eye mr-2"></i>Tampilkan
                </button>
            </form>


            <div class="d-flex align-items-center">
                <a href="{{ route('admin.rekap.tilangan.excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </a>
                <a href="{{ route('admin.rekap.tilangan.cetak', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="btn btn-sm btn-danger" target="_blank">
                    <i class="fas fa-file-pdf mr-2"></i>PDF
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead class="text-center">
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Total Tilangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($minusan as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->total_tilangan ? number_format($item->total_tilangan, 0, ',', '.') : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Tidak ada data untuk periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-2 text-muted small">Periode: <strong>{{ $start->format('d F Y') }} - {{ $end->format('d F Y') }}</strong></div>
            </div>
        </div>
    </div>
@endsection
