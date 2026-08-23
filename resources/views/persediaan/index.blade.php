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
                                <th>Perusahaan</th>
                                <th>Divisi</th>
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
                                        @if(($r->cicilan ?? 'Tanpa Cicilan') === 'Cicilan')
                                            <span class="badge bg-warning text-dark">Cicilan</span>
                                        @else
                                            <span class="badge bg-light text-dark">Tanpa Cicilan</span>
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
                                                <small class="text-muted d-block mb-1">Nama Perusahaan</small>
                                                <h6 class="fw-bold mb-0 text-dark">{{ $r->company_name ?? $r->owner_name }}</h6>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">Divisi & Pembayaran</small>
                                                <div class="d-flex gap-2">
                                                    <span class="badge bg-secondary text-uppercase">{{ $r->division ?? '-' }}</span>
                                                    <span class="badge bg-info text-uppercase">{{ $r->payment_method ?? '-' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="p-3 border rounded bg-light">
                                                <small class="text-muted d-block mb-1">Pilihan Cicilan</small>
                                                @if(($r->cicilan ?? 'Tanpa Cicilan') === 'Cicilan')
                                                    <span class="badge bg-warning text-dark">Cicilan</span>
                                                @else
                                                    <span class="badge bg-light text-dark border">Tanpa Cicilan</span>
                                                @endif
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

                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold mb-2">Bukti Transfer (Gambar)</h6>
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
                        <label class="form-label fw-bold text-dark">Nama Perusahaan <span class="text-danger">*</span></label>
                        <input name="company_name" class="form-control" placeholder="Contoh: PT Belanja Kuota" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-dark">Nama Divisi <span class="text-danger">*</span></label>
                            <select name="division" class="form-select" required>
                                <option value="">-- Pilih Divisi --</option>
                                <option value="server">Server</option>
                                <option value="gudang">Gudang</option>
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
                                <option value="Cicilan">Cicilan</option>
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
                        <div id="transfer-paste-zone" tabindex="0" style="border:2px dashed #0d6efd;border-radius:8px;padding:20px;min-height:90px;cursor:pointer;background:#f8f9fa;text-align:center;outline:none;" onclick="this.focus()">
                            <div id="transfer-paste-hint" style="color:#6c757d;pointer-events:none;">
                                <i class="ti ti-clipboard" style="font-size:1.5rem;"></i><br>
                                <span>Klik area ini, lalu tekan <strong>Ctrl+V</strong> untuk paste gambar</span><br>
                                <small class="text-muted">atau pilih file di bawah ini</small>
                            </div>
                            <div id="transfer-paste-preview" style="display:none;"></div>
                        </div>
                        <div style="margin-top:.5rem;">
                            <input type="file" name="transfer_proof" accept="image/*" class="form-control" id="transfer-proof-file">
                        </div>
                        <input type="hidden" name="transfer_proof_base64" id="transfer_proof_base64">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Bukti Faktur (opsional copy/paste gambar atau teks)</label>
                        <textarea name="invoice_text" id="invoice-text" class="form-control" rows="3" placeholder="Anda bisa paste teks atau gambar di sini (gambar akan disimpan sebagai lampiran)"></textarea>
                        <div class="mt-2">atau upload file: <input type="file" name="invoice_file" id="invoice-file" class="form-control"/></div>
                        <div id="invoice-file-preview" style="margin-top:.5rem;"></div>
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

@section('scripts')
<script>
    // ============================================================
    //  TRANSFER PROOF — paste zone & file input handler
    // ============================================================
    (function () {
        var _pastedDataUrl = null; // store the captured data URL

        function showTransferPreview(dataUrl) {
            _pastedDataUrl = dataUrl;

            // Update hidden input immediately
            var hidden = document.getElementById('transfer_proof_base64');
            if (hidden) hidden.value = dataUrl;

            // Also push to the file input via DataTransfer so backend receives multipart file
            try {
                var arr = dataUrl.split(',');
                var mimeMatch = arr[0].match(/:(.*?);/);
                var mime = mimeMatch ? mimeMatch[1] : 'image/png';
                var bstr = atob(arr[1].replace(/\s/g, ''));
                var n = bstr.length;
                var u8 = new Uint8Array(n);
                while (n--) u8[n] = bstr.charCodeAt(n);
                var file = new File([u8], 'bukti_transfer_' + Date.now() + '.png', { type: mime });
                var dt = new DataTransfer();
                dt.items.add(file);
                var fi = document.getElementById('transfer-proof-file');
                if (fi) fi.files = dt.files;
            } catch (ex) {
                console.warn('DataTransfer error (will use base64 fallback):', ex);
            }

            // Show preview inside the paste zone
            var hint = document.getElementById('transfer-paste-hint');
            var preview = document.getElementById('transfer-paste-preview');
            if (hint) hint.style.display = 'none';
            if (preview) {
                preview.style.display = 'block';
                preview.innerHTML =
                    '<div style="position:relative;display:inline-block;">' +
                    '<img src="' + dataUrl + '" style="max-width:100%;max-height:220px;border-radius:6px;border:2px solid #28a745;" />' +
                    '<span class="badge bg-success" style="position:absolute;bottom:4px;left:4px;">Gambar Terpasang ✓</span>' +
                    '<button type="button" id="remove-transfer-preview" class="btn btn-sm btn-danger" style="position:absolute;top:4px;right:4px;padding:2px 8px;">×</button>' +
                    '</div>';

                var rmBtn = document.getElementById('remove-transfer-preview');
                if (rmBtn) {
                    rmBtn.addEventListener('click', function (ev) {
                        ev.stopPropagation();
                        _pastedDataUrl = null;
                        var h = document.getElementById('transfer_proof_base64');
                        if (h) h.value = '';
                        var fi2 = document.getElementById('transfer-proof-file');
                        if (fi2) fi2.value = '';
                        preview.style.display = 'none';
                        preview.innerHTML = '';
                        if (hint) hint.style.display = '';
                        // Also reset zone border
                        var zone = document.getElementById('transfer-paste-zone');
                        if (zone) zone.style.borderColor = '#0d6efd';
                    });
                }
            }

            // Turn zone border green as feedback
            var zone = document.getElementById('transfer-paste-zone');
            if (zone) zone.style.borderColor = '#28a745';
        }

        function extractImageFromClipboard(clipboardData) {
            if (!clipboardData) return null;

            // 1. Try files[]
            if (clipboardData.files && clipboardData.files.length > 0) {
                for (var i = 0; i < clipboardData.files.length; i++) {
                    if (clipboardData.files[i].type.startsWith('image/')) {
                        return clipboardData.files[i];
                    }
                }
            }

            // 2. Try items[]
            if (clipboardData.items && clipboardData.items.length > 0) {
                for (var j = 0; j < clipboardData.items.length; j++) {
                    var item = clipboardData.items[j];
                    if (item.type && item.type.startsWith('image/') && item.getAsFile) {
                        var blob = item.getAsFile();
                        if (blob) return blob;
                    }
                }
            }

            return null;
        }

        // Listen for paste on the zone element itself
        var zone = document.getElementById('transfer-paste-zone');
        if (zone) {
            zone.addEventListener('paste', function (e) {
                var blob = extractImageFromClipboard(e.clipboardData || window.clipboardData);
                if (blob) {
                    e.preventDefault();
                    var reader = new FileReader();
                    reader.onload = function (ev) { showTransferPreview(ev.target.result); };
                    reader.readAsDataURL(blob);
                } else {
                    // Show hint that no image was found
                    zone.style.borderColor = '#dc3545';
                    setTimeout(function () { zone.style.borderColor = '#0d6efd'; }, 1500);
                }
            });

            // Global paste: capture anywhere in modal
            document.addEventListener('paste', function (e) {
                var active = document.activeElement;

                // Skip if focused on invoice-text textarea
                if (active && active.id === 'invoice-text') return;

                // Only handle when modal is open
                var modal = document.getElementById('modalPersediaan');
                if (!modal || !modal.classList.contains('show')) return;

                // Skip if already handled by zone's own paste listener
                if (active && active.id === 'transfer-paste-zone') return;

                var blob = extractImageFromClipboard(e.clipboardData || window.clipboardData);
                if (blob) {
                    e.preventDefault();
                    var reader = new FileReader();
                    reader.onload = function (ev) { showTransferPreview(ev.target.result); };
                    reader.readAsDataURL(blob);
                    // Auto-focus zone so user sees it
                    zone.focus();
                }
            });
        }

        // File input change → show preview
        var fileInput = document.getElementById('transfer-proof-file');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                var f = fileInput.files && fileInput.files[0];
                if (!f) return;
                var reader = new FileReader();
                reader.onload = function (ev) { showTransferPreview(ev.target.result); };
                reader.readAsDataURL(f);
            });
        }

        // On form submit: ensure base64 is set
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('persediaan-form');
            if (form) {
                form.addEventListener('submit', function () {
                    if (_pastedDataUrl) {
                        var h = document.getElementById('transfer_proof_base64');
                        if (h && !h.value) h.value = _pastedDataUrl;
                    }
                });
            }
        });

        // Invoice-text paste: image → base64 field
        var invoiceText = document.getElementById('invoice-text');
        if (invoiceText) {
            invoiceText.addEventListener('paste', function (e) {
                var blob = extractImageFromClipboard(e.clipboardData || window.clipboardData);
                if (blob) {
                    e.preventDefault();
                    var reader = new FileReader();
                    reader.onload = function (ev) {
                        var h = document.getElementById('invoice_file_base64');
                        if (h) h.value = ev.target.result;
                        var prev = document.getElementById('invoice-file-preview');
                        if (prev) prev.innerHTML = '<img src="' + ev.target.result + '" style="max-width:200px;max-height:200px;border-radius:4px;margin-top:6px;" />';
                    };
                    reader.readAsDataURL(blob);
                }
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
                    if (prev) prev.innerHTML = '<img src="' + ev.target.result + '" style="max-width:200px;max-height:200px;border-radius:4px;margin-top:6px;" />';
                };
                reader.readAsDataURL(f);
            });
        }
    })();

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('persediaan-form');
        if (form) {
            form.addEventListener('submit', function () {
                const itemsTable = document.querySelector('#items-table tbody');
                let items = [];
                if (itemsTable) {
                    items = Array.from(itemsTable.querySelectorAll('tr')).map(r => ({
                        name: (r.querySelector('.item-name') && r.querySelector('.item-name').value) || '',
                        qty: (r.querySelector('.item-qty') && r.querySelector('.item-qty').value) || 0,
                        price: (r.querySelector('.item-price') && r.querySelector('.item-price').value) || 0,
                    }));
                }
                const itemsJsonInput = document.getElementById('items-json');
                if (itemsJsonInput) itemsJsonInput.value = JSON.stringify(items);
            });
        }
    });

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
            // button click - toggle corresponding row
            const btn = e.target.closest('.js-persediaan-open-detail');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                const id = btn.getAttribute('data-id');
                const row = document.querySelector(`.js-persediaan-row[data-id="${id}"]`);
                if (row) toggleRow(row);
                return;
            }

            // row click (but not clicks on buttons/links)
            const row = e.target.closest('.js-persediaan-row');
            if (row) {
                if (e.target.closest('button') || e.target.closest('a')) return;
                toggleRow(row);
            }
        });

        function toggleRow(row) {
            const next = row.nextElementSibling;
            // if detail open for this row, close it
            if (next && next.classList && next.classList.contains('persediaan-detail-row')) {
                const btnHere = row.querySelector('.js-persediaan-open-detail');
                if (btnHere) btnHere.textContent = 'Lihat';
                next.remove();
                return;
            }

            // otherwise close any other open detail and open this one
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
@endsection
