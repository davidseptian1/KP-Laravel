@extends('layouts.app')

@section('content')
<style>
    .rekap-po-page .card {
        border-radius: 12px;
        border: 1px solid var(--bs-border-color, #dee2e6);
    }
    .rekap-po-page .metric-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .rekap-po-page .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.08) !important;
    }
    .rekap-po-page .table th {
        font-weight: 600;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .rekap-po-page .table td {
        vertical-align: middle;
        font-size: 0.88rem;
    }

    @media print {
        .pc-sidebar, .pc-header, .page-header, .no-print, .btn, form {
            display: none !important;
        }
        .pc-container {
            margin: 0 !important;
            padding: 0 !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="page-header no-print" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.persediaan-stok.index') }}">Monitoring PO</a></li>
                    <li class="breadcrumb-item active">Rekap Bulanan</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="page-header-title">
                        <h2 class="mb-0"><i class="ti ti-calendar-event me-2 text-primary"></i>Rekap Bulanan Request PO</h2>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.persediaan-stok.index') }}" class="btn btn-outline-secondary">
                            <i class="ti ti-arrow-left me-1"></i>Kembali ke Monitoring
                        </a>
                        <a href="{{ route('admin.persediaan.rekap.pdf', request()->all()) }}" target="_blank" class="btn btn-danger">
                            <i class="ti ti-file-text me-1"></i>Export PDF
                        </a>
                        <button onclick="window.print()" class="btn btn-success">
                            <i class="ti ti-printer me-1"></i>Cetak Rekap
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 rekap-po-page">
    <!-- Banner Notification Periode Perhitungan -->
    <div class="col-12 mb-3">
        <div class="d-flex align-items-center flex-wrap p-3 border-0 rounded-3 shadow-sm" style="background-color: #E0F7FA; border: 1px solid #B2EBF2 !important;">
            <div class="d-flex align-items-center justify-content-center me-2" style="color: #00838F;">
                <i class="ti ti-info-circle fs-4"></i>
            </div>
            <span class="fw-semibold me-2" style="color: #006064; font-size: 0.95rem;">Periode Perhitungan:</span>
            <span class="badge text-white fs-6 px-3 py-2 fw-bold" style="border-radius: 6px; background-color: #2196F3 !important;">{{ $startDate->format('d F Y') }}</span>
            <span class="mx-2 fw-bold" style="color: #00838F;">&rarr;</span>
            <span class="badge text-white fs-6 px-3 py-2 fw-bold" style="border-radius: 6px; background-color: #2196F3 !important;">{{ $endDate->format('d F Y') }}</span>
        </div>
    </div>

    <!-- Filter Periode Card -->
    <div class="col-12 mb-3 no-print">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-filter me-1"></i>Filter Periode & Status</h6>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('admin.persediaan.rekap') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-dark">Tanggal Mulai (Default Tgl 23)</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-dark">Tanggal Akhir (Default Tgl 24)</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-dark">Filter Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (ACC)</option>
                            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-dark">Cari Supplier</label>
                        <input type="text" name="supplier" class="form-control" value="{{ request('supplier') }}" placeholder="Nama supplier...">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
                        <a href="{{ route('admin.persediaan.rekap') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="ti ti-refresh"></i></a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Banner Info Periode untuk Print -->
    <div class="col-12 mb-3 d-none d-print-block">
        <div class="p-3 border rounded text-center bg-light">
            <h3 class="fw-bold mb-1">REKAP BULANAN REQUEST PO (PERSEDIAAN STOK)</h3>
            <h5 class="text-primary mb-0">Periode: {{ $startDate->translatedFormat('d F Y') }} s/d {{ $endDate->translatedFormat('d F Y') }}</h5>
        </div>
    </div>

    <!-- Statistic Metric Cards -->
    <div class="col-md-3 mb-3">
        <div class="card metric-card shadow-sm border-start border-primary border-4 bg-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Total Request PO</small>
                        <h3 class="fw-bold mb-0 text-primary mt-1">{{ number_format($totalPO) }} <span class="fs-6 text-muted fw-normal">Transaksi</span></h3>
                    </div>
                    <div class="p-3 bg-primary bg-opacity-10 rounded-circle text-primary">
                        <i class="ti ti-packages fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card metric-card shadow-sm border-start border-success border-4 bg-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Total Nominal PO</small>
                        <h3 class="fw-bold mb-0 text-success mt-1">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h3>
                    </div>
                    <div class="p-3 bg-success bg-opacity-10 rounded-circle text-success">
                        <i class="ti ti-cash fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card metric-card shadow-sm border-start border-info border-4 bg-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Disetujui / Selesai</small>
                        <h3 class="fw-bold mb-0 text-info mt-1">{{ number_format($totalApproved + $totalSelesai) }} <span class="fs-6 text-muted fw-normal">PO</span></h3>
                    </div>
                    <div class="p-3 bg-info bg-opacity-10 rounded-circle text-info">
                        <i class="ti ti-circle-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card metric-card shadow-sm border-start border-warning border-4 bg-white">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Pending / Tolak</small>
                        <h3 class="fw-bold mb-0 text-warning mt-1">{{ number_format($totalPending) }} / <span class="text-danger">{{ number_format($totalRejected) }}</span></h3>
                    </div>
                    <div class="p-3 bg-warning bg-opacity-10 rounded-circle text-warning">
                        <i class="ti ti-clock fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ringkasan per Supplier & per Server -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-building me-1"></i>Ringkasan Nominal per Supplier</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Supplier</th>
                                <th class="text-center">Jumlah PO</th>
                                <th class="text-end">Total Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supplierSummary as $sup)
                                <tr>
                                    <td class="fw-semibold">{{ $sup['name'] }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $sup['count'] }} PO</span></td>
                                    <td class="text-end fw-bold text-success">Rp {{ number_format($sup['total_amount'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Tidak ada data supplier pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-server me-1"></i>Ringkasan Nominal per Server</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Server</th>
                                <th class="text-center">Jumlah PO</th>
                                <th class="text-end">Total Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serverSummary as $srv)
                                <tr>
                                    <td class="fw-semibold">{{ $srv['name'] }}</td>
                                    <td class="text-center"><span class="badge bg-light text-dark border">{{ $srv['count'] }} PO</span></td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($srv['total_amount'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">Tidak ada data server pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Rincian Data Transaksi PO -->
    <div class="col-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="ti ti-list-check me-1"></i>Rincian Transaksi PO Periode (23 {{ $startDate->translatedFormat('M Y') }} - 24 {{ $endDate->translatedFormat('M Y') }})</h6>
                <span class="badge bg-secondary">{{ $list->count() }} Data Transaksi</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal PO</th>
                                <th>Nama Supplier</th>
                                <th>Server</th>
                                <th>Pembayaran</th>
                                <th>Cicilan</th>
                                <th class="text-end">Total Nominal</th>
                                <th>Status</th>
                                <th class="text-center no-print">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($list as $index => $row)
                                <tr>
                                    <td><strong>#{{ $row->id }}</strong></td>
                                    <td>{{ optional($row->po_date)->format('d/m/Y') ?? optional($row->created_at)->format('d/m/Y') }}</td>
                                    <td class="fw-semibold">{{ $row->company_name ?? $row->owner_name }}</td>
                                    <td><span class="badge bg-secondary text-uppercase">{{ $row->division ?? '-' }}</span></td>
                                    <td><span class="badge bg-info text-uppercase">{{ $row->payment_method ?? '-' }}</span></td>
                                    <td>
                                        @if(($row->cicilan ?? 'Tanpa Cicilan') !== 'Tanpa Cicilan')
                                            <span class="badge bg-warning text-dark">{{ $row->cicilan }}</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Tanpa Cicilan</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($row->total_amount, 2, ',', '.') }}</td>
                                    <td>
                                        @if(($row->status ?? 'pending') === 'approved')
                                            <span class="badge bg-success">Approved (ACC)</span>
                                        @elseif(($row->status ?? 'pending') === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @elseif(($row->status ?? 'pending') === 'selesai')
                                            <span class="badge bg-primary">Selesai</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center no-print">
                                        <a href="{{ route('admin.persediaan.show', $row->id) }}" class="btn btn-xs btn-outline-info py-1 px-2" title="Lihat Detail">
                                            <i class="ti ti-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada transaksi Request PO pada periode (23 {{ $startDate->translatedFormat('M Y') }} - 24 {{ $endDate->translatedFormat('M Y') }}).</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end fs-6">TOTAL KESELURUHAN NOMINAL PERIODE:</th>
                                <th class="text-end fs-5 text-success fw-bold">Rp {{ number_format($totalNominal, 2, ',', '.') }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
