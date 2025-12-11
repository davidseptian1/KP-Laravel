@extends('layouts/app')

@section('content')
    <!-- [ breadcrumb ] start -->
    <div class="page-header" style="margin: 0; padding: 0;">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ul>
                </div>
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">{{ $title }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->

    <div class="row mt-0">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="row align-items-center g-3">
                        <!-- Filter Form -->
                        <div class="col-lg-6">
                            <form method="GET" action="{{ route('admin.rekap.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label mb-0 fw-semibold text-nowrap">
                                        <i class="ti ti-calendar me-1"></i>Periode:
                                    </label>
                                    <select name="bulan" class="form-select form-select-sm" style="min-width: 130px;">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ sprintf('%02d', $i) }}" {{ $bulan == sprintf('%02d', $i) ? 'selected' : '' }}>
                                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                    
                                    <select name="tahun" class="form-select form-select-sm" style="min-width: 100px;">
                                        @foreach ($tahunList as $t)
                                            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                                                {{ $t }}
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ti ti-eye me-1"></i>Tampilkan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Export Buttons -->
                        <div class="col-lg-6">
                            @if(auth()->user()->jabatan == 'Admin' || auth()->user()->jabatan == 'HRD')
                            <div class="d-flex gap-2 justify-content-lg-end">
                                <a href="{{ route('admin.rekap.excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="ti ti-file-spreadsheet me-1"></i>Excel
                                </a>
                                <a href="{{ route('admin.rekap.cetak', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                                    class="btn btn-danger btn-sm" target="_blank">
                                    <i class="ti ti-file-text me-1"></i>PDF
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Summary Stats -->
                    @if($minusan->count() > 0)
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light-primary border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-l bg-primary flex-shrink-0">
                                            <i class="ti ti-database text-white"></i>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <p class="mb-1 text-muted">Total Transaksi</p>
                                            <h4 class="mb-0">{{ number_format($minusan->count()) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-success border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-l bg-success flex-shrink-0">
                                            <i class="ti ti-currency-dollar text-white"></i>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <p class="mb-1 text-muted">Total Nilai</p>
                                            <h4 class="mb-0">Rp {{ number_format($minusan->sum('total'), 0, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-warning border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-l bg-warning flex-shrink-0">
                                            <i class="ti ti-package text-white"></i>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <p class="mb-1 text-muted">Total Qty</p>
                                            <h4 class="mb-0">{{ number_format($minusan->sum('qty')) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th>Tanggal</th>
                                    <th>Server</th>
                                    <th>Nama</th>
                                    <th>Supplier</th>
                                    <th>Produk</th>
                                    <th>Nomor</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Total/Org</th>
                                    <th class="text-center">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($minusan as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-light-primary">
                                                <i class="ti ti-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>{{ $item->server }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avtar avtar-s bg-light-primary flex-shrink-0 me-2">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <span class="fw-semibold">{{ $item->nama }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $item->spl }}</td>
                                        <td>{{ $item->produk }}</td>
                                        <td>{{ $item->nomor }}</td>
                                        <td class="text-end fw-semibold text-primary">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light-info">{{ $item->qty }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($item->total_per_orang, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light-{{ $item->keterangan == 'Dialihkan' ? 'warning' : 'success' }}">
                                                {{ $item->keterangan }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="ti ti-database-off" style="font-size: 48px; color: #ccc;"></i>
                                                <p class="text-muted mt-3 mb-0">Tidak ada data untuk bulan ini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
