@extends('layouts.app')

@section('content')
<style>
    .admin-po-page .card {
        border-radius: 12px;
        border: 1px solid var(--bs-border-color, #dee2e6);
    }
    .admin-po-page .table-wrap {
        border-radius: 10px;
        overflow-x: auto;
    }
    .admin-po-page .table th {
        font-weight: 600;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .admin-po-page .table td {
        vertical-align: middle;
        font-size: 0.88rem;
    }
</style>

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Monitoring Request PO</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0"><i class="ti ti-package me-2"></i>Monitoring Request PO (Persediaan Stok)</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 admin-po-page">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold"><i class="ti ti-list me-1"></i>Daftar Request PO</h5>
                <form method="GET" action="{{ route('admin.persediaan-stok.index') }}" class="d-flex gap-2">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari nama perusahaan / owner..." style="width: 240px;">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
                    @if(request('q'))
                        <a href="{{ route('admin.persediaan-stok.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </form>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        <i class="ti ti-check me-1"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-wrap border">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Perusahaan</th>
                                <th>Divisi</th>
                                <th>Pembayaran</th>
                                <th>Cicilan</th>
                                <th>Tgl PO</th>
                                <th>Tgl Penerimaan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Bukti Gambar</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($list as $row)
                                <tr>
                                    <td><strong>#{{ $row->id }}</strong></td>
                                    <td>
                                        <div class="fw-bold">{{ $row->company_name ?? $row->owner_name }}</div>
                                        @if($row->company_name && $row->owner_name && $row->company_name !== $row->owner_name)
                                            <small class="text-muted">Pemilik: {{ $row->owner_name }}</small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-secondary text-uppercase">{{ $row->division ?? '-' }}</span></td>
                                    <td><span class="badge bg-info text-uppercase">{{ $row->payment_method ?? '-' }}</span></td>
                                    <td>
                                        @if(($row->cicilan ?? 'Tanpa Cicilan') === 'Cicilan')
                                            <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Cicilan</span>
                                        @else
                                            <span class="badge bg-light text-dark">Tanpa Cicilan</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($row->po_date)->format('d/m/Y') ?? optional($row->created_at)->format('d/m/Y') }}</td>
                                    <td>
                                        @if($row->receive_date)
                                            <span class="text-success fw-semibold"><i class="ti ti-calendar-check me-1"></i>{{ optional($row->receive_date)->format('d/m/Y H:i') }}</span>
                                        @else
                                            <span class="text-muted opacity-75">- Belum Diterima -</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-dark">Rp {{ number_format($row->total_amount, 2, ',', '.') }}</td>
                                    <td>
                                        @if(($row->status ?? 'pending') === 'approved')
                                            <span class="badge bg-success"><i class="ti ti-circle-check me-1"></i>Approved (ACC)</span>
                                        @elseif(($row->status ?? 'pending') === 'rejected')
                                            <span class="badge bg-danger"><i class="ti ti-circle-x me-1"></i>Rejected</span>
                                        @elseif(($row->status ?? 'pending') === 'selesai')
                                            <span class="badge bg-primary"><i class="ti ti-checkall me-1"></i>Selesai</span>
                                        @else
                                            <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            @if($row->transfer_proof_path)
                                                <a href="{{ route('admin.persediaan.file', [$row->id, 'transfer']) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.72rem;">
                                                    <i class="ti ti-photo me-1"></i>Bukti Transfer
                                                </a>
                                            @endif
                                            @if($row->invoice_path)
                                                <a href="{{ route('admin.persediaan.file', [$row->id, 'invoice']) }}" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size: 0.72rem;">
                                                    <i class="ti ti-file-text me-1"></i>Faktur
                                                </a>
                                            @endif
                                            @if(!$row->transfer_proof_path && !$row->invoice_path)
                                                <span class="text-muted" style="font-size: 0.75rem;">- Tanpa Gambar -</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Button Detail -->
                                            <a href="{{ route('admin.persediaan.show', $row->id) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail">
                                                <i class="ti ti-eye"></i>
                                            </a>

                                            <!-- Button Edit Cicilan & Tgl Penerimaan -->
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#modalEditDetails{{ $row->id }}" title="Edit Cicilan & Tgl Penerimaan">
                                                <i class="ti ti-edit"></i>
                                            </button>

                                            <!-- Quick ACC / Reject Buttons -->
                                            <form method="POST" action="{{ route('admin.persediaan.update-status', $row->id) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-sm btn-success {{ ($row->status ?? 'pending') === 'approved' ? 'disabled' : '' }}" title="ACC (Approve) PO">
                                                    <i class="ti ti-check"></i> ACC
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.persediaan.update-status', $row->id) }}" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="btn btn-sm btn-danger {{ ($row->status ?? 'pending') === 'rejected' ? 'disabled' : '' }}" title="Tolak PO">
                                                    <i class="ti ti-x"></i> Tolak
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Edit Cicilan & Tanggal Penerimaan -->
                                <div class="modal fade" id="modalEditDetails{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('admin.persediaan.update-details', $row->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="ti ti-edit me-1"></i>Edit PO #{{ $row->id }} - {{ $row->company_name ?? $row->owner_name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Pilihan Cicilan</label>
                                                        <select name="cicilan" class="form-select" required>
                                                            <option value="Tanpa Cicilan" {{ ($row->cicilan ?? 'Tanpa Cicilan') === 'Tanpa Cicilan' ? 'selected' : '' }}>Tanpa Cicilan</option>
                                                            <option value="Cicilan" {{ ($row->cicilan ?? '') === 'Cicilan' ? 'selected' : '' }}>Cicilan</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Tanggal Penerimaan</label>
                                                        <input type="datetime-local" name="receive_date" class="form-control" value="{{ $row->receive_date ? \Carbon\Carbon::parse($row->receive_date)->format('Y-m-d\TH:i') : '' }}">
                                                        <small class="text-muted">Kosongkan jika barang belum diterima.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i>Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">Belum ada request PO / permintaan persediaan stok.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $list->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
