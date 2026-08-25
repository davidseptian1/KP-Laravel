@extends('layouts/app')

@section('content')

<style>
    .persediaan-page .section-card {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 10px;
        background: var(--bs-body-bg, #fff);
    }
    .persediaan-page .section-title { font-weight:600 }
    .persediaan-page .table-wrap { border:1px solid var(--bs-border-color,#dee2e6); border-radius:10px; overflow:auto }
</style>

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Permintaan Persediaan</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Permintaan Persediaan Stok</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0 persediaan-page">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="section-title mb-0"><i class="ti ti-box me-1"></i>Permintaan Persediaan</div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPersediaan">
                    <i class="ti ti-plus me-1"></i>Ajukan Persediaan
                </button>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <p class="mb-3">Gunakan tombol di kanan atas untuk membuat permintaan persediaan baru. Lampiran dapat ditempel dari clipboard atau di-upload.</p>

                <div class="table-wrap">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Tgl PO</th>
                                <th>Supplier</th>
                                <th>Server</th>
                                <th>Pembayaran</th>
                                <th>Cicilan</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($records ?? collect()) as $r)
                                <tr class="js-persediaan-row" data-items='@json($r->items ?? [])' data-transfer-path="{{ $r->transfer_proof_path }}" data-invoice-path="{{ $r->invoice_path }}" data-id="{{ $r->id }}">
                                    <td>{{ optional($r->po_date)->format('d/m/Y') ?? optional($r->created_at)->format('d/m/Y') }}</td>
                                    <td><strong>{{ $r->company_name ?? $r->owner_name }}</strong></td>
                                    <td><span class="badge bg-secondary text-uppercase">{{ $r->division ?? '-' }}</span></td>
                                    <td><span class="badge bg-info text-uppercase">{{ $r->payment_method ?? '-' }}</span></td>
                                    <td>
                                        @if(($r->cicilan ?? 'Tanpa Cicilan') !== 'Tanpa Cicilan')
                                            <span class="badge bg-warning text-dark">{{ $r->cicilan }}</span>
                                        @else
                                            <span class="badge bg-light text-dark border">Tanpa Cicilan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($r->status ?? 'pending') === 'approved')
                                            <span class="badge bg-success">Approved (ACC)</span>
                                        @elseif(($r->status ?? 'pending') === 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @elseif(($r->status ?? 'pending') === 'selesai')
                                            <span class="badge bg-primary">Selesai</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#persediaanDetail{{ $r->id }}">Lihat</button>
                                    </td>
                                </tr>
                            @endforeach
                            @if(empty($records) || $records->isEmpty())
                                <tr><td colspan="7" class="text-center text-muted">Belum ada permintaan persediaan / PO.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- Detail Modals --}}
                @foreach(($records ?? collect()) as $r)
                    <div class="modal fade" id="persediaanDetail{{ $r->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title fw-bold text-dark"><i class="ti ti-file-text me-1"></i>Detail Request PO #{{ $r->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">Nama Supplier</small>
                                                <h6 class="fw-bold mb-0 text-dark">{{ $r->company_name ?? $r->owner_name }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">Server & Pembayaran</small>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-secondary text-uppercase">{{ $r->division ?? '-' }}</span>
                                                    <span class="badge bg-info text-uppercase">{{ $r->payment_method ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">Pilihan Cicilan</small>
                                                <h6 class="fw-bold mb-0 text-dark">{{ $r->cicilan }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">Tanggal PO</small>
                                                <h6 class="fw-bold mb-0 text-dark">{{ optional($r->po_date)->format('d F Y') ?? optional($r->created_at)->format('d F Y') }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">Status</small>
                                                @if(($r->status ?? 'pending') === 'approved')
                                                    <span class="badge bg-success">Approved (ACC)</span>
                                                @elseif(($r->status ?? 'pending') === 'rejected')
                                                    <span class="badge bg-danger">Rejected</span>
                                                @elseif(($r->status ?? 'pending') === 'selesai')
                                                    <span class="badge bg-primary">Selesai</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">No. Rekening & Bank</small>
                                                <h6 class="fw-bold mb-0 text-dark">{{ $r->account_number ?: '-' }} ({{ $r->account_name ?: '-' }})</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">Tanggal Penerimaan</small>
                                                <h6 class="fw-bold mb-0 text-dark">
                                                    @if($r->receive_date)
                                                        <span class="text-success">{{ optional($r->receive_date)->format('d F Y H:i') }}</span>
                                                    @else
                                                        <span class="text-muted opacity-75">- Belum Diterima -</span>
                                                    @endif
                                                </h6>
                                            </div>
                                        </div>
                                    </div>

                                    @if(!empty($r->items))
                                        <h6 class="fw-bold mt-4 mb-2">Barang yang Dibeli</h6>
                                        <table class="table table-sm border">
                                            <thead><tr><th>Nama</th><th>Qty</th><th>Harga</th><th>Subtotal</th></tr></thead>
                                            <tbody>
                                                @foreach($r->items as $it)
                                                    <tr>
                                                        <td>{{ $it['name'] ?? '' }}</td>
                                                        <td>{{ $it['qty'] ?? 0 }}</td>
                                                        <td>Rp {{ number_format($it['price'] ?? 0, 2, ',', '.') }}</td>
                                                        <td>Rp {{ number_format((($it['qty'] ?? 0) * ($it['price'] ?? 0)), 2, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif

                                    @if(!empty($r->installment_payments))
                                        <div class="mt-3 mb-3 border rounded p-2 bg-light">
                                            <h6 class="fw-bold mb-2 text-dark"><i class="ti ti-history me-1"></i>Riwayat Pembayaran Cicilan</h6>
                                            <div class="row g-2">
                                                @foreach($r->installment_payments as $idx => $pmt)
                                                    <div class="col-md-6">
                                                        <div class="p-2 border rounded bg-white shadow-sm">
                                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                                <span class="badge bg-success">{{ $pmt['stage'] ?? 'Cicilan' }}</span>
                                                                <span class="text-success fw-semibold" style="font-size:0.8rem;"><i class="ti ti-check me-1"></i>Sudah Dibayarkan</span>
                                                            </div>
                                                            <small class="text-muted d-block mb-1"><i class="ti ti-clock me-1"></i>{{ \Carbon\Carbon::parse($pmt['paid_at'] ?? now())->format('d/m/Y H:i') }} WIB</small>
                                                            @if(!empty($pmt['proof_path']))
                                                                <div class="text-center mt-1 border rounded bg-light p-1">
                                                                    <a href="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'cicilan_'.$idx]) }}" target="_blank">
                                                                        <img src="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'cicilan_'.$idx]) }}" style="max-height: 100px;" class="img-fluid rounded" alt="Bukti {{ $pmt['stage'] }}" />
                                                                    </a>
                                                                    <div class="mt-1">
                                                                        <a href="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'cicilan_'.$idx]) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-1" style="font-size:0.7rem;">Bukti TF {{ $pmt['stage'] }}</a>
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
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-2">Bukti Transfer Terakhir (Gambar)</h6>
                                            @if($r->transfer_proof_path)
                                                <div class="p-2 border rounded text-center bg-light">
                                                    <a href="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'transfer']) }}" target="_blank">
                                                        <img src="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'transfer']) }}" style="max-height: 220px;" class="img-fluid rounded" alt="Bukti Transfer" />
                                                    </a>
                                                    <div class="mt-2">
                                                        <a href="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'transfer']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka Fullscreen</a>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="p-3 border rounded text-muted text-center bg-light">Tidak ada gambar bukti transfer</div>
                                            @endif
                                        </div>

                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-2">Faktur / Lampiran</h6>
                                            @if($r->invoice_path)
                                                <div class="p-2 border rounded text-center bg-light">
                                                    <a href="{{ route('persediaan.file', ['id' => $r->id, 'field' => 'invoice']) }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka File Faktur</a>
                                                </div>
                                            @elseif($r->invoice_text)
                                                <pre class="p-2 border rounded bg-light small mb-0" style="white-space: pre-wrap;">{{ $r->invoice_text }}</pre>
                                            @else
                                                <div class="p-3 border rounded text-muted text-center bg-light">Tidak ada faktur</div>
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
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Modal with the existing form --}}
<div class="modal fade" id="modalPersediaan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-dark">Form Permintaan Persediaan Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="persediaan-form" method="post" action="{{ route('persediaan.store') }}" enctype="multipart/form-data">
                    @csrf
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Nama Supplier <span class="text-danger">*</span></label>
                        <input name="company_name" class="form-control" placeholder="Contoh: PT Belanja Kuota / Supplier Utama" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Nama Server <span class="text-danger">*</span></label>
                            <select name="division" class="form-select" required>
                                <option value="">-- Pilih Server --</option>
                                @foreach(($servers ?? []) as $srv)
                                    <option value="{{ $srv->nama_server }}">{{ $srv->nama_server }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">-- Pilih Pembayaran --</option>
                                <option value="bank">Bank</option>
                                <option value="va">VA (Virtual Account)</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-dark">Pilihan Bank (Bank/VA)</label>
                            <select name="bank_id" class="form-select">
                                <option value="">-- Pilih Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->nama_bank ?? $bank->nama ?? 'Bank '.$bank->id }} - {{ $bank->nomor_rekening ?? $bank->nomor_rek ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-dark">Pilihan Cicilan / Tidak <span class="text-danger">*</span></label>
                            <select name="cicilan" class="form-select" required>
                                <option value="Tanpa Cicilan">Tanpa Cicilan</option>
                                <option value="Cicilan 1">Cicilan 1</option>
                                <option value="Cicilan 2">Cicilan 2</option>
                                <option value="Cicilan 3">Cicilan 3</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-dark">Tanggal PO <span class="text-danger">*</span></label>
                            <input type="date" name="po_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">No. Rekening</label>
                            <input name="account_number" class="form-control" placeholder="Contoh: 1234567890">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">A.N. Rekening</label>
                            <input name="account_name" class="form-control" placeholder="Contoh: PT Belanja Kuota">
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label fw-bold text-dark">Bukti Transfer (Gambar) <span class="text-danger">*</span></label>
                        {{-- Paste zone: klik untuk fokus ke textarea tersembunyi, lalu Ctrl+V --}}
                        <div id="transfer-paste-zone" style="border:2px dashed #0d6efd;border-radius:8px;min-height:100px;cursor:pointer;background:#f8f9fa;position:relative;overflow:hidden;" onclick="document.getElementById('transfer-paste-input').focus()">
                            <div id="transfer-paste-hint" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#6c757d;pointer-events:none;padding:12px;text-align:center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" style="margin-bottom:6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <span>Klik area ini, lalu tekan <strong>Ctrl+V</strong> untuk paste gambar</span>
                                <small class="text-muted mt-1">atau pilih file di bawah ini</small>
                            </div>
                            <div id="transfer-paste-preview" style="display:none;padding:10px;text-align:center;"></div>
                            {{-- Textarea tersembunyi yang menerima paste event --}}
                            <textarea id="transfer-paste-input"
                                style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;resize:none;border:none;background:transparent;cursor:pointer;z-index:2;color:transparent;caret-color:transparent;"
                                placeholder=""
                                autocomplete="off"
                                tabindex="0"
                                spellcheck="false"
                                onfocus="document.getElementById('transfer-paste-zone').style.borderColor='#0a58ca'"
                                onblur="var h=document.getElementById('transfer_proof_base64');document.getElementById('transfer-paste-zone').style.borderColor=h&&h.value?'#28a745':'#0d6efd'"
                            ></textarea>
                        </div>
                        <div style="margin-top:.5rem;">
                            <input type="file" name="transfer_proof" accept="image/*" class="form-control" id="transfer-proof-file">
                        </div>
                        <input type="hidden" name="transfer_proof_base64" id="transfer_proof_base64">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Bukti Faktur (Upload / Paste Ctrl+V Gambar)</label>
                        <div id="invoice-paste-zone" style="border:2px dashed #0d6efd;border-radius:8px;min-height:100px;cursor:pointer;background:#f8f9fa;position:relative;overflow:hidden;" onclick="document.getElementById('invoice-paste-input').focus()">
                            <div id="invoice-paste-hint" style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#6c757d;pointer-events:none;padding:12px;text-align:center;">
                                <i class="ti ti-file-text fs-3 mb-1"></i>
                                <span>Klik area ini, lalu tekan <strong>Ctrl+V</strong> untuk paste gambar Faktur</span>
                                <small class="text-muted mt-1">atau pilih file faktur di bawah</small>
                            </div>
                            <div id="invoice-file-preview" style="display:none;padding:10px;text-align:center;"></div>
                            <textarea id="invoice-paste-input"
                                style="position:absolute;top:0;left:0;width:100%;height:100%;opacity:0;resize:none;border:none;background:transparent;cursor:pointer;z-index:2;"
                                placeholder=""
                                autocomplete="off"
                                tabindex="0"
                                spellcheck="false"
                            ></textarea>
                        </div>
                        <div class="mt-2">
                            <input type="file" name="invoice_file" id="invoice-file" class="form-control"/>
                        </div>
                        <div class="mt-2">
                            <textarea name="invoice_text" id="invoice-text" class="form-control" rows="2" placeholder="Catatan / Teks Faktur opsional..."></textarea>
                        </div>
                        <input type="hidden" name="invoice_file_base64" id="invoice_file_base64">
                    </div>

                    <input type="hidden" name="items_json" id="items-json">

                    <div class="mt-3 text-end">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
                        <button class="btn btn-primary">Kirim Permintaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // Simpan blob asli — BUKAN base64 — untuk preview cepat (ObjectURL)
    var _blob = null;
    var _objectUrl = null;

    // -------------------------------------------------------
    //  Tampilkan preview dari blob/file (tanpa konversi base64)
    // -------------------------------------------------------
    function showPreviewFromBlob(blob) {
        _blob = blob;
        if (_objectUrl) URL.revokeObjectURL(_objectUrl); // bebaskan memori lama
        _objectUrl = URL.createObjectURL(blob);

        // Salin blob ke file input agar backend bisa pakai multipart upload
        try {
            var dt = new DataTransfer();
            dt.items.add(new File([blob], 'bukti_transfer_' + Date.now() + '.' + (blob.type.split('/')[1] || 'png'), { type: blob.type }));
            var fi = document.getElementById('transfer-proof-file');
            if (fi) fi.files = dt.files;
        } catch (ex) { /* fallback ke base64 saat submit */ }

        // Reset hidden field (akan diisi saat submit jika file input gagal)
        var hidden = document.getElementById('transfer_proof_base64');
        if (hidden) hidden.value = '';

        renderPreview(_objectUrl);
    }

    function renderPreview(src) {
        var hint = document.getElementById('transfer-paste-hint');
        var preview = document.getElementById('transfer-paste-preview');
        var zone = document.getElementById('transfer-paste-zone');

        if (hint) hint.style.display = 'none';
        if (zone) zone.style.borderColor = '#28a745';

        if (preview) {
            preview.style.display = 'block';
            preview.innerHTML =
                '<div style="position:relative;display:inline-block;z-index:10;">' +
                '<img src="' + src + '" style="max-width:100%;max-height:200px;border-radius:6px;border:2px solid #28a745;display:block;" />' +
                '<span class="badge bg-success" style="position:absolute;bottom:4px;left:4px;">✓ Gambar Terpasang</span>' +
                '<button type="button" id="remove-transfer-preview" class="btn btn-sm btn-danger" ' +
                'style="position:absolute;top:4px;right:4px;padding:2px 8px;z-index:20;" title="Hapus gambar">×</button>' +
                '</div>';

            var rm = document.getElementById('remove-transfer-preview');
            if (rm) {
                rm.addEventListener('click', function (ev) {
                    ev.stopPropagation(); ev.preventDefault();
                    clearTransfer();
                });
            }
        }
    }

    function clearTransfer() {
        _blob = null;
        if (_objectUrl) { URL.revokeObjectURL(_objectUrl); _objectUrl = null; }
        var hidden = document.getElementById('transfer_proof_base64');
        if (hidden) hidden.value = '';
        var fi = document.getElementById('transfer-proof-file');
        if (fi) fi.value = '';
        var preview = document.getElementById('transfer-paste-preview');
        if (preview) { preview.style.display = 'none'; preview.innerHTML = ''; }
        var hint = document.getElementById('transfer-paste-hint');
        if (hint) hint.style.display = 'flex';
        var zone = document.getElementById('transfer-paste-zone');
        if (zone) zone.style.borderColor = '#0d6efd';
        var input = document.getElementById('transfer-paste-input');
        if (input) input.value = '';
    }

    // -------------------------------------------------------
    //  Extract gambar dari clipboard event
    // -------------------------------------------------------
    function getBlobFromClipboard(clipboardData) {
        if (!clipboardData) return null;
        // 1. files[]
        if (clipboardData.files && clipboardData.files.length > 0) {
            for (var i = 0; i < clipboardData.files.length; i++) {
                if (clipboardData.files[i].type.startsWith('image/')) return clipboardData.files[i];
            }
        }
        // 2. items[]
        if (clipboardData.items && clipboardData.items.length > 0) {
            for (var j = 0; j < clipboardData.items.length; j++) {
                var it = clipboardData.items[j];
                if (it.type && it.type.startsWith('image/') && it.getAsFile) {
                    var b = it.getAsFile();
                    if (b) return b;
                }
            }
        }
        return null;
    }

    // -------------------------------------------------------
    //  Handle paste event
    // -------------------------------------------------------
    function handlePaste(e) {
        var blob = getBlobFromClipboard(e.clipboardData || window.clipboardData);
        if (!blob) {
            // Feedback border merah sebentar
            var zone = document.getElementById('transfer-paste-zone');
            if (zone) {
                zone.style.borderColor = '#dc3545';
                setTimeout(function () {
                    zone.style.borderColor = _blob ? '#28a745' : '#0d6efd';
                }, 1200);
            }
            return;
        }
        e.preventDefault();
        showPreviewFromBlob(blob);
        // Bersihkan textarea agar tidak ada teks aneh
        setTimeout(function () {
            var inp = document.getElementById('transfer-paste-input');
            if (inp) inp.value = '';
        }, 0);
    }

    // -------------------------------------------------------
    //  Saat form submit: konversi blob → base64 HANYA jika
    //  file input tidak berhasil (sebagai fallback)
    // -------------------------------------------------------
    function ensureBase64BeforeSubmit(form) {
        form.addEventListener('submit', function (e) {
            var fi = document.getElementById('transfer-proof-file');
            var fiHasFile = fi && fi.files && fi.files.length > 0;
            var hidden = document.getElementById('transfer_proof_base64');
            var hiddenHasValue = hidden && hidden.value.length > 0;

            // Jika file input sudah punya file, backend pakai multipart → tidak perlu base64
            if (fiHasFile || hiddenHasValue || !_blob) return;

            // Fallback: konversi blob ke base64 secara sinkron saat submit
            // (hanya terjadi jika DataTransfer gagal)
            try {
                var reader = new FileReader();
                reader.readAsDataURL(_blob);
                // Ini async, jadi kita tunda submit
                e.preventDefault();
                reader.onload = function (ev) {
                    if (hidden) hidden.value = ev.target.result;
                    form.submit();
                };
            } catch (ex) {
                // Biarkan submit tanpa gambar
            }
        });
    }

    // -------------------------------------------------------
    //  Init setelah DOM siap
    // -------------------------------------------------------
    function init() {
        // Paste dari textarea tersembunyi
        var pasteInput = document.getElementById('transfer-paste-input');
        if (pasteInput) {
            pasteInput.addEventListener('paste', handlePaste);
        }

        // Global fallback: paste di mana saja dalam modal
        document.addEventListener('paste', function (e) {
            var active = document.activeElement;
            if (active && (active.id === 'invoice-text' || active.id === 'transfer-paste-input')) return;
            var modal = document.getElementById('modalPersediaan');
            if (!modal || !modal.classList.contains('show')) return;
            var blob = getBlobFromClipboard(e.clipboardData || window.clipboardData);
            if (blob) {
                e.preventDefault();
                showPreviewFromBlob(blob);
                if (pasteInput) pasteInput.focus();
            }
        });

        // File input biasa
        var fileInput = document.getElementById('transfer-proof-file');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                var f = fileInput.files && fileInput.files[0];
                if (!f) { clearTransfer(); return; }
                _blob = f;
                if (_objectUrl) URL.revokeObjectURL(_objectUrl);
                _objectUrl = URL.createObjectURL(f);
                renderPreview(_objectUrl);
                // hidden tidak diperlukan karena file sudah ada di input
                var hidden = document.getElementById('transfer_proof_base64');
                if (hidden) hidden.value = '';
            });
        }

        // Form submit fallback
        var form = document.getElementById('persediaan-form');
        if (form) ensureBase64BeforeSubmit(form);

        // Invoice paste (gambar)
        var invoiceText = document.getElementById('invoice-text');
        if (invoiceText) {
            invoiceText.addEventListener('paste', function (e) {
                var blob = getBlobFromClipboard(e.clipboardData || window.clipboardData);
                if (!blob) return;
                e.preventDefault();
                var reader = new FileReader();
                reader.onload = function (ev) {
                    var h = document.getElementById('invoice_file_base64');
                    if (h) h.value = ev.target.result;
                    var prev = document.getElementById('invoice-file-preview');
                    if (prev) prev.innerHTML = '<img src="' + ev.target.result + '" style="max-width:200px;max-height:140px;border-radius:4px;margin-top:6px;border:1px solid #ccc;" />';
                };
                reader.readAsDataURL(blob);
            });
        }

        // Invoice file input
        var invoiceFile = document.getElementById('invoice-file');
        if (invoiceFile) {
            invoiceFile.addEventListener('change', function () {
                var f = invoiceFile.files && invoiceFile.files[0];
                if (!f) return;
                var reader = new FileReader();
                reader.onload = function (ev) {
                    var h = document.getElementById('invoice_file_base64');
                    if (h) h.value = ev.target.result;
                    var prev = document.getElementById('invoice-file-preview');
                    if (prev) prev.innerHTML = '<img src="' + ev.target.result + '" style="max-width:200px;max-height:140px;border-radius:4px;margin-top:6px;border:1px solid #ccc;" />';
                };
                reader.readAsDataURL(f);
            });
        }
    }

    // Jalankan setelah DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush

@push('scripts')
<script>
    // Inline detail toggle for persediaan rows
    (function(){
        function closeOpenDetail() {
            const open = document.querySelector('.persediaan-detail-row');
            if (open) {
                const prev = open.previousElementSibling;
                if (prev) {
                    const prevBtn = prev.querySelector('.js-persediaan-open-detail');
                    if (prevBtn) prevBtn.textContent = 'Lihat';
                }
                open.remove();
            }
        }

        function buildDetailHtml(items, transferPath, invoicePath, id){
            let html = '<td colspan="5">';
            html += '<table class="table table-sm mb-2"><thead><tr><th>NAMA</th><th>QTY</th><th>HARGA</th><th>SUBTOTAL</th></tr></thead><tbody>';
            items.forEach(it => {
                const name = it.name || '';
                const qty = parseFloat(it.qty || 0);
                const price = parseFloat(it.price || 0);
                const subtotal = (qty*price).toFixed(2);
                html += `<tr><td>${name}</td><td>${qty}</td><td>${price.toFixed(2)}</td><td>${subtotal}</td></tr>`;
            });
            html += '</tbody></table>';

            html += '<div><strong>Bukti Transfer:</strong><div style="margin-top:.5rem;">';
            if (transferPath) {
                html += `<img src="/persediaan-stok/`+id+`/file/transfer" style="max-width:240px;max-height:240px;display:block;" onerror="this.style.display='none'"/>`;
            } else {
                html += '<div class="text-muted">Tidak ada bukti transfer</div>';
            }
            html += '</div></div>';

            html += '<div class="mt-3"><strong>Faktur / Lampiran:</strong> ';
            if (invoicePath) {
                html += `<a href="/persediaan-stok/`+id+`/file/invoice" target="_blank" class="btn btn-sm btn-outline-primary">Buka Faktur</a>`;
            } else {
                html += '<div class="text-muted">Tidak ada faktur</div>';
            }
            html += '</div>';

            html += '</td>';
            return html;
        }

        // Use event delegation to reliably handle clicks on rows and buttons
        document.addEventListener('click', function(e){
            const btn = e.target.closest('.js-persediaan-open-detail');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                const row = document.querySelector(`.js-persediaan-row[data-id="${id}"]`);
                if (row) toggleRow(row);
                return;
            }
            const row = e.target.closest('.js-persediaan-row');
            if (row) {
                if (e.target.closest('button') || e.target.closest('a')) return;
                toggleRow(row);
            }
        });

        function toggleRow(row) {
            const next = row.nextElementSibling;
            if (next && next.classList && next.classList.contains('persediaan-detail-row')) {
                const btnHere = row.querySelector('.js-persediaan-open-detail');
                if (btnHere) btnHere.textContent = 'Lihat';
                next.remove();
                return;
            }
            closeOpenDetail();
            const items = JSON.parse(row.getAttribute('data-items') || '[]');
            const transferPath = row.getAttribute('data-transfer-path');
            const invoicePath = row.getAttribute('data-invoice-path');
            const id = row.getAttribute('data-id');
            const tr = document.createElement('tr');
            tr.className = 'persediaan-detail-row';
            tr.innerHTML = buildDetailHtml(items, transferPath, invoicePath, id);
            row.parentNode.insertBefore(tr, row.nextSibling);
            const btnHere = row.querySelector('.js-persediaan-open-detail');
            if (btnHere) btnHere.textContent = 'Tutup';
            tr.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    })();
</script>
@endpush
