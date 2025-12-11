@extends('layouts/app')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <form method="GET" action="{{ route('admin.rekap.index') }}"
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
                <a href="{{ route('admin.rekap.excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                    class="btn btn-sm btn-success mr-2">
                    <i class="fas fa-file-excel mr-2"></i>Excel
                </a>
                <a href="{{ route('admin.rekap.cetak', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
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
                            <th>Tanggal</th>
                            <th>Server</th>
                            <th>Nama</th>
                            <th>Supplier</th>
                            <th>Produk</th>
                            <th>Nomor</th>
                            <th>Total</th>
                            <th>Qty</th>
                            <th>Total/Org</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($minusan as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $item->server }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->spl }}</td>
                                <td>{{ $item->produk }}</td>
                                <td>{{ $item->nomor }}</td>
                                <td>{{ number_format($item->total, 0, ',', '.') }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ number_format($item->total_per_orang, 0, ',', '.') }}</td>
                                <td>{{ $item->keterangan }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">Tidak ada data untuk bulan ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
