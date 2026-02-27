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
                <form method="GET" class="row g-2 align-items-end mb-4">
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Server</label>
                        <select name="server" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach (($filterOptions['servers'] ?? collect()) as $serverOption)
                                <option value="{{ $serverOption }}" {{ ($filters['server'] ?? '') === $serverOption ? 'selected' : '' }}>{{ $serverOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Bank</label>
                        <select name="bank" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach (($filterOptions['banks'] ?? collect()) as $bankOption)
                                <option value="{{ $bankOption }}" {{ ($filters['bank'] ?? '') === $bankOption ? 'selected' : '' }}>{{ $bankOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="selesai" {{ ($filters['status'] ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Jenis</label>
                        <select name="jenis_transaksi" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="deposit" {{ ($filters['jenis_transaksi'] ?? '') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                            <option value="hutang" {{ ($filters['jenis_transaksi'] ?? '') === 'hutang' ? 'selected' : '' }}>Hutang</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Supplier</label>
                        <select name="nama_supplier" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach (($filterOptions['suppliers'] ?? collect()) as $supplierOption)
                                <option value="{{ $supplierOption }}" {{ ($filters['nama_supplier'] ?? '') === $supplierOption ? 'selected' : '' }}>{{ $supplierOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        <a href="{{ route('admin.deposit.analysis') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                </form>

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
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Rata-rata per Hari Aktif</div>
                                <div class="fs-5 fw-semibold">Rp {{ number_format($avgPerDay ?? 0, 0, ',', '.') }}</div>
                                <small class="text-muted">{{ $activeDays ?? 0 }} hari aktif</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Periode Data</div>
                                <div class="fw-semibold">{{ !empty($period?->min_date) ? \Carbon\Carbon::parse($period->min_date)->format('d/m/Y') : '-' }}</div>
                                <div class="fw-semibold">{{ !empty($period?->max_date) ? \Carbon\Carbon::parse($period->max_date)->format('d/m/Y') : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3 g-3">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Pending</div>
                                <div class="fs-5 fw-semibold">{{ $summary->total_pending ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Approved Rate</div>
                                <div class="fs-5 fw-semibold">{{ number_format($approvalRate ?? 0, 2, ',', '.') }}%</div>
                                <small class="text-muted">{{ $summary->total_approved ?? 0 }} transaksi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Selesai Rate</div>
                                <div class="fs-5 fw-semibold">{{ number_format($completionRate ?? 0, 2, ',', '.') }}%</div>
                                <small class="text-muted">{{ $summary->total_selesai ?? 0 }} transaksi</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Rejected Rate</div>
                                <div class="fs-5 fw-semibold">{{ number_format($rejectionRate ?? 0, 2, ',', '.') }}%</div>
                                <small class="text-muted">{{ $summary->total_rejected ?? 0 }} transaksi</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Top Bank</div>
                                <div class="fw-semibold">{{ $topBank->bank ?? '-' }}</div>
                                <small class="text-muted">{{ $topBank->jumlah ?? 0 }} trx • Rp {{ number_format($topBank->total ?? 0, 0, ',', '.') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Top Server</div>
                                <div class="fw-semibold">{{ $topServer->server ?? '-' }}</div>
                                <small class="text-muted">{{ $topServer->jumlah ?? 0 }} trx • Rp {{ number_format($topServer->total ?? 0, 0, ',', '.') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-body">
                                <div class="text-muted">Top Supplier</div>
                                <div class="fw-semibold">{{ $topSupplier->nama_supplier ?? '-' }}</div>
                                <small class="text-muted">{{ $topSupplier->jumlah ?? 0 }} trx • Rp {{ number_format($topSupplier->total ?? 0, 0, ',', '.') }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-lg-6">
                        <h5>Tren Harian (31 Hari Terakhir Sesuai Filter)</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($trendDaily as $row)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
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
                        <h5>Ringkasan per Status</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($byStatus as $row)
                                        <tr>
                                            <td>{{ ucfirst($row->status) }}</td>
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
                        <h5>Top 10 Nama Supplier</h5>
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

                <div class="row mt-4">
                    <div class="col-lg-8">
                        <h5>Top 10 No Rek & Nama Rekening</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>No Rek</th>
                                        <th>Nama Rekening</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($byAccount as $row)
                                        <tr>
                                            <td>{{ $row->no_rek }}</td>
                                            <td>{{ $row->nama_rekening }}</td>
                                            <td class="text-end">{{ $row->jumlah }}</td>
                                            <td class="text-end">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada data</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <h5>Ringkasan per Jam</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jam</th>
                                        <th class="text-end">Jumlah</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($byHour as $row)
                                        <tr>
                                            <td>{{ str_pad($row->jam, 2, '0', STR_PAD_LEFT) }}:00</td>
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
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="text-muted">Total Reply Penambahan Terisi</div>
                                <div class="fs-4 fw-semibold">{{ $replyCount ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
