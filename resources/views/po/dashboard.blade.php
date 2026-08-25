@extends('layouts.app')

@section('content')
<style>
    .po-dashboard-page .card {
        border-radius: 12px;
        border: 1px solid var(--bs-border-color, #dee2e6);
    }
    .po-dashboard-page .table-wrap {
        border-radius: 10px;
        overflow-x: auto;
    }
    .po-dashboard-page .table th {
        font-weight: 600;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .po-dashboard-page .table td {
        vertical-align: middle;
        font-size: 0.88rem;
    }
    .goods-paste-zone {
        border: 2px dashed #0d6efd;
        border-radius: 8px;
        min-height: 120px;
        cursor: pointer;
        background: #f8f9fa;
        position: relative;
        overflow: hidden;
    }
    .goods-paste-zone:hover {
        background: #eef5ff;
    }
</style>

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard PO</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0"><i class="ti ti-package-import me-2"></i>Dashboard PO - Konfirmasi Penerimaan Barang</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3 po-dashboard-page">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                <ul class="nav nav-pills card-header-pills">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'pending_confirmation' ? 'active fw-bold' : '' }}" href="{{ route('po.dashboard', ['tab' => 'pending_confirmation', 'q' => request('q')]) }}">
                            <i class="ti ti-clock me-1"></i>Perlu Konfirmasi
                            <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'completed' ? 'active fw-bold' : '' }}" href="{{ route('po.dashboard', ['tab' => 'completed', 'q' => request('q')]) }}">
                            <i class="ti ti-circle-check me-1"></i>Selesai Diterima
                            <span class="badge bg-success ms-1">{{ $completedCount }}</span>
                        </a>
                    </li>
                </ul>

                <form method="GET" action="{{ route('po.dashboard') }}" class="d-flex gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Cari supplier / server..." style="width: 220px;">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="ti ti-search me-1"></i>Cari</button>
                    @if(request('q'))
                        <a href="{{ route('po.dashboard', ['tab' => $tab]) }}" class="btn btn-sm btn-outline-secondary">Reset</a>
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
                                <th>#PO</th>
                                <th>Supplier</th>
                                <th>Server</th>
                                <th>Pembayaran</th>
                                <th>Cicilan</th>
                                <th>Tgl PO</th>
                                <th>Status Admin</th>
                                <th>Bukti Transfer Admin</th>
                                <th>Foto Barang</th>
                                <th class="text-center">Aksi PO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($list as $row)
                                <tr>
                                    <td><strong>#{{ $row->id }}</strong></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $row->company_name ?? $row->owner_name }}</div>
                                    </td>
                                    <td><span class="badge bg-secondary text-uppercase">{{ $row->division ?? '-' }}</span></td>
                                    <td><span class="badge bg-info text-uppercase">{{ $row->payment_method ?? '-' }}</span></td>
                                    <td>
                                        @if(($row->cicilan ?? 'Tanpa Cicilan') !== 'Tanpa Cicilan')
                                            <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>{{ $row->cicilan }}</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Tanpa Cicilan</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($row->po_date)->format('d/m/Y') ?? optional($row->created_at)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-success"><i class="ti ti-check me-1"></i>ACC Admin</span>
                                    </td>
                                    <td>
                                        @if($row->transfer_proof_path)
                                            <a href="{{ route('po.file', [$row->id, 'transfer']) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.75rem;">
                                                <i class="ti ti-photo me-1"></i>Bukti TF
                                            </a>
                                        @else
                                            <span class="text-muted" style="font-size: 0.75rem;">- Belum Ada TF -</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->goods_photo_path)
                                            <a href="{{ route('po.file', [$row->id, 'goods']) }}" target="_blank" class="btn btn-xs btn-outline-success py-0 px-2" style="font-size: 0.75rem;">
                                                <i class="ti ti-camera me-1"></i>Foto Barang
                                            </a>
                                        @else
                                            <span class="text-muted" style="font-size: 0.75rem;">- Belum Ada Foto -</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- Detail Button -->
                                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#poDetailModal{{ $row->id }}" title="Lihat Detail PO">
                                                <i class="ti ti-eye"></i> Detail
                                            </button>

                                            <!-- Confirm Receive Button -->
                                            @if($row->status !== 'selesai')
                                                <button type="button" class="btn btn-sm btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#poConfirmModal{{ $row->id }}">
                                                    <i class="ti ti-truck-delivery me-1"></i>Konfirmasi Terima
                                                </button>
                                            @else
                                                <span class="badge bg-primary px-2 py-1"><i class="ti ti-circle-check me-1"></i>Selesai</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal Detail PO -->
                                <div class="modal fade" id="poDetailModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header bg-light">
                                                <h5 class="modal-title fw-bold text-dark"><i class="ti ti-file-text me-1"></i>Detail Request PO #{{ $row->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <div class="p-3 border rounded bg-light">
                                                            <small class="text-muted d-block mb-1">Nama Supplier</small>
                                                            <h6 class="fw-bold mb-0 text-dark">{{ $row->company_name ?? $row->owner_name }}</h6>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="p-3 border rounded bg-light">
                                                            <small class="text-muted d-block mb-1">Nama Server & Pembayaran</small>
                                                            <div class="d-flex gap-2">
                                                                <span class="badge bg-secondary text-uppercase">{{ $row->division ?? '-' }}</span>
                                                                <span class="badge bg-info text-uppercase">{{ $row->payment_method ?? '-' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="p-3 border rounded bg-light">
                                                            <small class="text-muted d-block mb-1">Pilihan Cicilan</small>
                                                            <h6 class="fw-bold mb-0 text-dark">{{ $row->cicilan }}</h6>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="p-3 border rounded bg-light">
                                                            <small class="text-muted d-block mb-1">Tanggal PO</small>
                                                            <h6 class="fw-bold mb-0 text-dark">{{ optional($row->po_date)->format('d F Y') ?? optional($row->created_at)->format('d F Y') }}</h6>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="p-3 border rounded bg-light">
                                                            <small class="text-muted d-block mb-1">Tgl Penerimaan</small>
                                                            <h6 class="fw-bold mb-0 text-dark">
                                                                @if($row->receive_date)
                                                                    <span class="text-success">{{ optional($row->receive_date)->format('d F Y H:i') }}</span>
                                                                @else
                                                                    <span class="text-muted opacity-75">- Belum Diterima -</span>
                                                                @endif
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </div>

                                                @if(!empty($row->items))
                                                    <h6 class="fw-bold mt-4 mb-2"><i class="ti ti-list me-1"></i>Rincian Barang PO</h6>
                                                    <div class="table-responsive border rounded">
                                                        <table class="table table-sm mb-0">
                                                            <thead class="table-light"><tr><th>Nama Barang</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
                                                            <tbody>
                                                                @foreach($row->items as $it)
                                                                    <tr>
                                                                        <td>{{ $it['name'] ?? '' }}</td>
                                                                        <td>{{ $it['qty'] ?? 0 }}</td>
                                                                        <td>Rp {{ number_format($it['price'] ?? 0, 2, ',', '.') }}</td>
                                                                        <td>Rp {{ number_format((($it['qty'] ?? 0) * ($it['price'] ?? 0)), 2, ',', '.') }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif

                                                @if(!empty($row->installment_payments))
                                                    <div class="mt-3 mb-2 border rounded p-2 bg-light">
                                                        <h6 class="fw-bold mb-2 text-dark"><i class="ti ti-history me-1"></i>Riwayat Pembayaran Cicilan</h6>
                                                        <div class="row g-2">
                                                            @foreach($row->installment_payments as $idx => $pmt)
                                                                <div class="col-md-4">
                                                                    <div class="p-2 border rounded bg-white shadow-sm">
                                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                                            <span class="badge bg-success">{{ $pmt['stage'] ?? 'Cicilan' }}</span>
                                                                            <span class="text-success fw-semibold" style="font-size:0.78rem;"><i class="ti ti-check me-1"></i>Sudah Dibayarkan</span>
                                                                        </div>
                                                                        <small class="text-muted d-block mb-1"><i class="ti ti-clock me-1"></i>{{ \Carbon\Carbon::parse($pmt['paid_at'] ?? now())->format('d/m/Y H:i') }} WIB</small>
                                                                        @if(!empty($pmt['proof_path']))
                                                                            <div class="text-center mt-1 border rounded bg-light p-1">
                                                                                <a href="{{ route('po.file', [$row->id, 'cicilan_'.$idx]) }}" target="_blank">
                                                                                    <img src="{{ route('po.file', [$row->id, 'cicilan_'.$idx]) }}" style="max-height: 90px;" class="img-fluid rounded" alt="Bukti {{ $pmt['stage'] }}" />
                                                                                </a>
                                                                                <div class="mt-1">
                                                                                    <a href="{{ route('po.file', [$row->id, 'cicilan_'.$idx]) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-1" style="font-size:0.7rem;">Bukti TF {{ $pmt['stage'] }}</a>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="row g-3 mt-2">
                                                    <div class="col-md-4">
                                                        <h6 class="fw-bold mb-2">Bukti Transfer Terakhir (Admin)</h6>
                                                        @if($row->transfer_proof_path)
                                                            <div class="p-2 border rounded text-center bg-light">
                                                                <a href="{{ route('po.file', [$row->id, 'transfer']) }}" target="_blank">
                                                                    <img src="{{ route('po.file', [$row->id, 'transfer']) }}" style="max-height: 160px;" class="img-fluid rounded" alt="Bukti Transfer">
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="p-3 border rounded text-muted text-center bg-light">Belum ada bukti transfer</div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="fw-bold mb-2">Bukti Faktur (User)</h6>
                                                        @if($row->invoice_path)
                                                            <div class="p-2 border rounded text-center bg-light">
                                                                <a href="{{ route('po.file', [$row->id, 'invoice']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka Faktur</a>
                                                            </div>
                                                        @elseif($row->invoice_text)
                                                            <pre class="p-2 border rounded bg-light small mb-0" style="white-space: pre-wrap; max-height: 160px;">{{ $row->invoice_text }}</pre>
                                                        @else
                                                            <div class="p-3 border rounded text-muted text-center bg-light">Tidak ada faktur</div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="fw-bold mb-2">Foto Barang (PO)</h6>
                                                        @if($row->goods_photo_path)
                                                            <div class="p-2 border rounded text-center bg-light">
                                                                <a href="{{ route('po.file', [$row->id, 'goods']) }}" target="_blank">
                                                                    <img src="{{ route('po.file', [$row->id, 'goods']) }}" style="max-height: 160px;" class="img-fluid rounded" alt="Foto Barang">
                                                                </a>
                                                            </div>
                                                        @else
                                                            <div class="p-3 border rounded text-muted text-center bg-light">Belum ada foto barang</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Konfirmasi Penerimaan Barang -->
                                <div class="modal fade" id="poConfirmModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('po.confirm-receive', $row->id) }}" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"><i class="ti ti-truck-delivery me-1"></i>Konfirmasi Penerimaan PO #{{ $row->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Supplier</label>
                                                        <input class="form-control" value="{{ $row->company_name ?? $row->owner_name }}" readonly>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Tanggal Penerimaan Barang <span class="text-danger">*</span></label>
                                                        <input type="datetime-local" name="receive_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Foto Barang / Bukti Fisik <span class="text-danger">*</span></label>
                                                        <div class="goods-paste-zone" id="goods-paste-zone-{{ $row->id }}" onclick="document.getElementById('goods-paste-input-{{ $row->id }}').focus()">
                                                            <div id="goods-hint-{{ $row->id }}" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#6c757d;pointer-events:none;padding:10px;text-align:center;">
                                                                <i class="ti ti-camera fs-3 mb-1"></i>
                                                                <span>Klik area ini, lalu <strong>Ctrl+V</strong> untuk paste gambar</span>
                                                                <small class="text-muted">atau pilih file foto barang di bawah</small>
                                                            </div>
                                                            <div id="goods-preview-{{ $row->id }}" style="display:none;padding:10px;text-align:center;"></div>
                                                            <textarea id="goods-paste-input-{{ $row->id }}"
                                                                style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;resize:none;border:none;background:transparent;cursor:pointer;z-index:2;"
                                                                data-id="{{ $row->id }}"
                                                                class="js-goods-paste-input"
                                                                spellcheck="false"
                                                            ></textarea>
                                                        </div>
                                                        <div class="mt-2">
                                                            <input type="file" name="goods_photo" accept="image/*" class="form-control" id="goods-file-{{ $row->id }}" onchange="handleGoodsFileChange(this, {{ $row->id }})">
                                                        </div>
                                                        <input type="hidden" name="goods_photo_base64" id="goods_photo_base64_{{ $row->id }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success fw-bold"><i class="ti ti-check me-1"></i>Konfirmasi & Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">Tidak ada data PO pada kategori ini.</td>
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

@push('scripts')
<script>
(function() {
    'use strict';

    function setGoodsPreview(id, src) {
        var hint = document.getElementById('goods-hint-' + id);
        var prev = document.getElementById('goods-preview-' + id);
        var zone = document.getElementById('goods-paste-zone-' + id);

        if (hint) hint.style.display = 'none';
        if (zone) zone.style.borderColor = '#28a745';
        if (prev) {
            prev.style.display = 'block';
            prev.innerHTML = '<div style="position:relative;display:inline-block;z-index:10;">' +
                '<img src="' + src + '" style="max-width:100%;max-height:180px;border-radius:6px;border:2px solid #28a745;" />' +
                '<span class="badge bg-success" style="position:absolute;bottom:4px;left:4px;">✓ Foto Barang Terpasang</span>' +
                '</div>';
        }
    }

    window.handleGoodsFileChange = function(input, id) {
        var file = input.files && input.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            setGoodsPreview(id, e.target.result);
            var hidden = document.getElementById('goods_photo_base64_' + id);
            if (hidden) hidden.value = e.target.result;
        };
        reader.readAsDataURL(file);
    };

    document.addEventListener('paste', function(e) {
        var active = document.activeElement;
        if (!active || !active.classList.contains('js-goods-paste-input')) return;

        var id = active.getAttribute('data-id');
        if (!id) return;

        var clipboardData = e.clipboardData || window.clipboardData;
        if (!clipboardData) return;

        var items = clipboardData.items || [];
        for (var i = 0; i < items.length; i++) {
            if (items[i].type && items[i].type.indexOf('image') === 0) {
                var blob = items[i].getAsFile();
                if (blob) {
                    e.preventDefault();
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        setGoodsPreview(id, ev.target.result);
                        var hidden = document.getElementById('goods_photo_base64_' + id);
                        if (hidden) hidden.value = ev.target.result;
                    };
                    reader.readAsDataURL(blob);
                    break;
                }
            }
        }
    });
})();
</script>
@endpush
