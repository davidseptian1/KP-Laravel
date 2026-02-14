@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Analisis Deposit</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Analisis Deposit</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Total Data</div>
                                <div class="fs-4 fw-semibold">{{ $summary->total ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Total Nominal</div>
                                <div class="fs-4 fw-semibold">Rp {{ number_format($summary->total_nominal ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Rata-rata Nominal</div>
                                <div class="fs-4 fw-semibold">Rp {{ number_format($summary->avg_nominal ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Min / Max</div>
                                <div class="fw-semibold">Rp {{ number_format($summary->min_nominal ?? 0, 0, ',', '.') }}</div>
                                <div class="fw-semibold">Rp {{ number_format($summary->max_nominal ?? 0, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-6">
                        <h5>Ringkasan per Bank</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Bank</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($byBank as $row)
                                        <tr>
                                            <td>{{ $row->bank }}</td>
                                            <td class="text-end">{{ $row->jumlah }}</td>
                                            <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h5>Ringkasan per Server</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Server</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($byServer as $row)
                                        <tr>
                                            <td>{{ $row->server }}</td>
                                            <td class="text-end">{{ $row->jumlah }}</td>
                                            <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Ringkasan per Nama Supplier</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nama Supplier</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bySupplier as $row)
                                        <tr>
                                            <td>{{ $row->nama_supplier }}</td>
                                            <td class="text-end">{{ $row->jumlah }}</td>
                                            <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
