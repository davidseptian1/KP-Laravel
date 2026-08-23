@extends('layouts.app')

@section('content')
<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.persediaan-stok.index') }}">Monitoring Request PO</a></li>
                    <li class="breadcrumb-item active">Detail PO #{{ $item->id }}</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0"><i class="ti ti-box me-2"></i>Detail Request PO #{{ $item->id }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-semibold"><i class="ti ti-file-description me-1"></i>Informasi PO</h5>
                <div>
                    @if(($item->status ?? 'pending') === 'approved')
                        <span class="badge bg-success fs-6"><i class="ti ti-check me-1"></i>Approved (ACC)</span>
                    @elseif(($item->status ?? 'pending') === 'rejected')
                        <span class="badge bg-danger fs-6"><i class="ti ti-x me-1"></i>Rejected</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6"><i class="ti ti-clock me-1"></i>Pending</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3">
                        <i class="ti ti-check me-1"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block mb-1">Nama Perusahaan</small>
                            <h6 class="fw-bold mb-0 text-dark">{{ $item->company_name ?? $item->owner_name }}</h6>
                            @if($item->owner_name && $item->company_name !== $item->owner_name)
                                <small class="text-muted">Pemilik: {{ $item->owner_name }}</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block mb-1">Divisi & Pembayaran</small>
                            <div class="d-flex gap-2">
                                <span class="badge bg-secondary text-uppercase">{{ $item->division ?? '-' }}</span>
                                <span class="badge bg-info text-uppercase">{{ $item->payment_method ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block mb-1">Pilihan Cicilan</small>
                            @if(($item->cicilan ?? 'Tanpa Cicilan') === 'Cicilan')
                                <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Cicilan</span>
                            @else
                                <span class="badge bg-light text-dark border">Tanpa Cicilan</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block mb-1">Tanggal PO</small>
                            <h6 class="fw-bold mb-0 text-dark">{{ optional($item->po_date)->format('d F Y') ?? optional($item->created_at)->format('d F Y') }}</h6>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block mb-1">Tanggal Penerimaan</small>
                            <h6 class="fw-bold mb-0 text-dark">
                                @if($item->receive_date)
                                    <span class="text-success"><i class="ti ti-calendar-check me-1"></i>{{ optional($item->receive_date)->format('d F Y H:i') }}</span>
                                @else
                                    <span class="text-muted opacity-75">- Belum Diterima -</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded bg-light">
                            <small class="text-muted d-block mb-1">Rekening Tujuan / Pemohon</small>
                            <h6 class="fw-bold mb-0 text-dark">{{ $item->account_number ?: '-' }} - {{ $item->account_name ?: '-' }}</h6>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="fw-bold mb-3"><i class="ti ti-list-check me-1"></i>Barang yang Dibeli</h6>
                <div class="table-responsive border rounded mb-3">
                    <table class="table table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Barang</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($item->items ?? [] as $it)
                                <tr>
                                    <td>{{ $it['name'] ?? '-' }}</td>
                                    <td class="text-center">{{ $it['qty'] ?? 0 }}</td>
                                    <td class="text-end">Rp {{ number_format($it['price'] ?? 0, 2, ',', '.') }}</td>
                                    <td class="text-end fw-semibold">Rp {{ number_format((($it['qty'] ?? 0) * ($it['price'] ?? 0)), 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end fs-6">TOTAL AMOUNT:</th>
                                <th class="text-end fs-5 text-primary">Rp {{ number_format($item->total_amount, 2, ',', '.') }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2"><i class="ti ti-photo me-1"></i>Bukti Transfer / Gambar</h6>
                        @if($item->transfer_proof_path)
                            <div class="border rounded p-2 text-center bg-light">
                                <a href="{{ route('admin.persediaan.file', [$item->id, 'transfer']) }}" target="_blank">
                                    <img src="{{ route('admin.persediaan.file', [$item->id, 'transfer']) }}" class="img-fluid rounded" style="max-height: 260px;" alt="Bukti Transfer">
                                </a>
                                <div class="mt-2">
                                    <a href="{{ route('admin.persediaan.file', [$item->id, 'transfer']) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                        <i class="ti ti-external-link me-1"></i>Buka Fullscreen
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="p-3 border rounded text-muted text-center bg-light">Tidak ada gambar bukti transfer</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-2"><i class="ti ti-file-invoice me-1"></i>Bukti Faktur / Lampiran</h6>
                        @if($item->invoice_text)
                            <pre class="p-2 border rounded bg-light mb-2" style="white-space: pre-wrap; max-height: 150px; overflow-y: auto;">{{ $item->invoice_text }}</pre>
                        @endif
                        @if($item->invoice_path)
                            <div class="border rounded p-2 text-center bg-light">
                                <a href="{{ route('admin.persediaan.file', [$item->id, 'invoice']) }}" target="_blank">
                                    <img src="{{ route('admin.persediaan.file', [$item->id, 'invoice']) }}" class="img-fluid rounded" style="max-height: 200px;" alt="Faktur">
                                </a>
                                <div class="mt-2">
                                    <a href="{{ route('admin.persediaan.file', [$item->id, 'invoice']) }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">Buka Faktur</a>
                                    <a href="{{ route('admin.persediaan.invoice.pdf', $item->id) }}" class="btn btn-sm btn-primary">Unduh PDF</a>
                                </div>
                            </div>
                        @elseif(!$item->invoice_text)
                            <div class="p-3 border rounded text-muted text-center bg-light">Tidak ada faktur</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Card Aksi Admin -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-semibold"><i class="ti ti-adjustments me-1"></i>Aksi Admin</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.persediaan.update-status', $item->id) }}" class="mb-3">
                    @csrf
                    @method('PUT')
                    <label class="form-label fw-bold">Ubah Status Request PO</label>
                    <div class="d-grid gap-2">
                        <button type="submit" name="status" value="approved" class="btn btn-success {{ ($item->status ?? 'pending') === 'approved' ? 'disabled' : '' }}">
                            <i class="ti ti-check me-1"></i>ACC (Approve PO)
                        </button>
                        <button type="submit" name="status" value="rejected" class="btn btn-danger {{ ($item->status ?? 'pending') === 'rejected' ? 'disabled' : '' }}">
                            <i class="ti ti-x me-1"></i>Tolak (Reject PO)
                        </button>
                    </div>
                </form>

                <hr>

                <form method="POST" action="{{ route('admin.persediaan.update-details', $item->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-bold">Edit Pilihan Cicilan</label>
                        <select name="cicilan" class="form-select" required>
                            <option value="Tanpa Cicilan" {{ ($item->cicilan ?? 'Tanpa Cicilan') === 'Tanpa Cicilan' ? 'selected' : '' }}>Tanpa Cicilan</option>
                            <option value="Cicilan" {{ ($item->cicilan ?? '') === 'Cicilan' ? 'selected' : '' }}>Cicilan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal Penerimaan</label>
                        <input type="datetime-local" name="receive_date" class="form-control" value="{{ $item->receive_date ? \Carbon\Carbon::parse($item->receive_date)->format('Y-m-d\TH:i') : '' }}">
                        <small class="text-muted">Set tanggal penerimaan barang saat diterima.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="ti ti-device-floppy me-1"></i>Simpan Perubahan</button>
                </form>
            </div>
            <div class="card-footer bg-white">
                <a href="{{ route('admin.persediaan-stok.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="ti ti-arrow-left me-1"></i>Kembali ke Daftar PO
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
