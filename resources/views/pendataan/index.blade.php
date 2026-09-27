@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h3 class="mb-1 fw-bold text-dark"><i class="ti ti-notes text-primary me-2"></i>Pendataan</h3>
        <p class="text-muted mb-0">Catat dan pantau transaksi produk dengan fitur pemilahan teks otomatis dan upload screenshot.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center flex-wrap gap-2">
        <span class="badge bg-light text-dark border px-3 py-2 fs-6 shadow-sm">
            <i class="ti ti-user-check text-primary me-1"></i>Staf: <strong class="text-primary">{{ $activeStaffNama }}</strong>
        </span>
        <a href="{{ url('pendataan/lock') }}" class="btn btn-sm btn-outline-danger shadow-sm" 
           title="Kunci / Logout Fitur Pendataan (Hanya keluar dari fitur ini)"
           onclick="return confirm('Logout dari fitur Pendataan? (Akun login utama Anda akan tetap aktif)')">
            <i class="ti ti-lock me-1"></i>Logout Fitur
        </a>
        <button type="button" class="btn btn-primary shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambahPendataan">
            <i class="ti ti-plus me-1"></i> Tambah Pendataan
        </button>
        @if(auth()->user()->jabatan === 'Superadmin')
            <a href="{{ url('superadmin/pendataan-access') }}" class="btn btn-outline-secondary" title="Pengaturan Akses Staff">
                <i class="ti ti-settings me-1"></i> Akses
            </a>
        @endif
    </div>
</div>

<!-- Summary Statistic Cards -->
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="ti ti-file-text fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Pendataan</h6>
                    <h3 class="mb-0 fw-bold">{{ number_format($totalTransaksi, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="ti ti-package fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Qty Terdata</h6>
                    <h3 class="mb-0 fw-bold text-info">{{ number_format($totalQty, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="ti ti-wallet fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Nominal / Harga</h6>
                    <h3 class="mb-0 fw-bold text-success">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
            <i class="ti ti-filter me-2 text-primary"></i>Filter Data Pendataan
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('pendataan.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Nama</label>
                <select name="nama" class="form-select form-select-sm">
                    <option value="">-- Semua Nama --</option>
                    @foreach($daftarNama as $itemNama)
                        <option value="{{ $itemNama }}" {{ ($filters['nama'] ?? '') === $itemNama ? 'selected' : '' }}>{{ $itemNama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control form-control-sm" placeholder="Cari produk..." value="{{ $filters['nama_produk'] ?? '' }}" list="listProdukOptions">
                <datalist id="listProdukOptions">
                    @foreach($uniqueProducts as $uProd)
                        <option value="{{ $uProd }}">
                    @endforeach
                </datalist>
            </div>
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top mt-2">
                <!-- Export / Download Buttons with active filters -->
                <div class="d-flex align-items-center gap-2">
                    <span class="small fw-semibold text-muted"><i class="ti ti-download me-1"></i>Download Data:</span>
                    <a href="{{ url('pendataan/export-excel') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-success px-3 shadow-sm" title="Download data dalam format Excel">
                        <i class="ti ti-file-spreadsheet me-1"></i> Excel (.xlsx)
                    </a>
                    <a href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(request()->query()) }}" class="btn btn-sm btn-danger px-3 shadow-sm" title="Download data dalam format PDF" target="_blank">
                        <i class="ti ti-file-type-pdf me-1"></i> PDF
                    </a>
                </div>

                <!-- Filter Action Buttons -->
                <div class="d-flex align-items-center gap-2">
                    @if(!empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filters['nama']) || !empty($filters['nama_produk']))
                        <a href="{{ route('pendataan.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-refresh me-1"></i> Reset Filter
                        </a>
                    @endif
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <i class="ti ti-search me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
            <i class="ti ti-table me-2 text-primary"></i>Data Pendataan
        </h5>
        <span class="badge bg-light text-secondary border px-3 py-2">
            Menampilkan {{ $pendataans->count() }} dari {{ $pendataans->total() }} data
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablePendataan">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Nama Produk</th>
                        <th class="text-end">Harga Qty</th>
                        <th class="text-end">Total Harga</th>
                        <th class="text-center" style="width: 80px;">Qty</th>
                        <th style="min-width: 140px;">Tanggal & Jam</th>
                        <th>Nama</th>
                        <th class="text-center" style="width: 90px;">Gambar</th>
                        <th class="text-center pe-4" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendataans as $item)
                    <tr>
                        <td class="ps-4 text-muted fw-semibold">
                            {{ ($pendataans->currentPage() - 1) * $pendataans->perPage() + $loop->iteration }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->nama_produk }}</div>
                            @if($item->deskripsi)
                                <small class="text-muted d-block text-truncate" style="max-width: 250px;" title="{{ $item->deskripsi }}">
                                    {{ Str::limit(str_replace(["\r", "\n"], ' ', $item->deskripsi), 45) }}
                                </small>
                            @endif
                        </td>
                        <td class="text-end fw-semibold text-secondary">
                            {{ $item->formatted_harga_qty }}
                        </td>
                        <td class="text-end fw-bold text-success">
                            {{ $item->formatted_total_harga }}
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-1 fw-bold">
                                {{ $item->qty }}
                            </span>
                        </td>
                        <td>
                            <div class="text-dark small fw-medium">{{ $item->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted"><i class="ti ti-clock me-1"></i>{{ $item->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1 fw-semibold">
                                <i class="ti ti-user me-1 text-primary"></i>{{ $item->nama }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($item->gambar)
                                <a href="javascript:void(0)" onclick="previewLightboxImage('{{ $item->gambar_url }}', '{{ addslashes($item->nama_produk) }}')" title="Lihat gambar">
                                    <img src="{{ $item->gambar_url }}" alt="Thumbnail" class="rounded border shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center pe-4 text-nowrap">
                            <!-- Detail Button -->
                            <button type="button" class="btn btn-sm btn-icon btn-outline-info" title="Lihat Detail" onclick="openDetailModal({{ $item->id }})">
                                <i class="ti ti-eye"></i>
                            </button>

                            <!-- Edit Button -->
                            <button type="button" class="btn btn-sm btn-icon btn-outline-warning ms-1" title="Edit Data" onclick="openEditModal({{ json_encode($item) }})">
                                <i class="ti ti-edit"></i>
                            </button>


                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="ti ti-inbox fs-1 d-block mb-2 text-secondary"></i>
                            Belum ada data pendataan yang tersimpan. Silakan klik tombol <strong>Tambah Pendataan</strong> untuk mulai mencatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pendataans->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            Halaman {{ $pendataans->currentPage() }} dari {{ $pendataans->lastPage() }}
        </span>
        <div>
            {{ $pendataans->links() }}
        </div>
    </div>
    @endif
</div>

<!-- ================= MODAL TAMBAH PENDATAAN ================= -->
<div class="modal fade" id="modalTambahPendataan" tabindex="-1" aria-labelledby="modalTambahPendataanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('pendataan.store') }}" method="POST" enctype="multipart/form-data" id="formTambahPendataan">
                @csrf
                {{-- Idempotency token: refreshed every time modal opens (see JS) to prevent double-submit --}}
                <input type="hidden" name="_idempotency_token" id="tambah_idempotency_token" value="">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahPendataanLabel">
                        <i class="ti ti-plus-circle text-primary me-2"></i>Tambah Pendataan Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Field: Nama Penanggung Jawab (Terkunci Sesuai Akun Staf Aktif) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Penanggung Jawab</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-primary"><i class="ti ti-user-check"></i></span>
                            <input type="text" class="form-control bg-light fw-bold text-dark" value="{{ $activeStaffNama }}" readonly disabled>
                        </div>
                        <div class="form-text text-muted small">
                            <i class="ti ti-lock me-1 text-primary"></i>Tercatat otomatis atas nama <strong>{{ $activeStaffNama }}</strong> (sesuai akun staf yang sedang membuka fitur ini).
                        </div>
                    </div>

                    <!-- Field: Deskripsi (Auto-Pilah) -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold text-dark mb-0">Deskripsi / Rincian Transaksi</label>
                            <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" id="btnPilahDeskripsiTambah" title="Pilah teks secara manual">
                                <i class="ti ti-wand me-1"></i> Pilah Teks
                            </button>
                        </div>
                        <textarea name="deskripsi" id="tambah_deskripsi" rows="5" class="form-control" placeholder="Contoh:&#10;AIGO Bronet 7GB 28hr&#10;Rp 34.500&#10;-&#10;1&#10;+&#10;Metode Pembayaran&#10;...&#10;Total Tagihan&#10;Rp 34.500&#10;Total Qty&#10;1"></textarea>
                        <div class="form-text">
                            <i class="ti ti-sparkles text-primary me-1"></i> Anda dapat copy & paste teks transaksi dari aplikasi di sini. Sistem akan otomatis memilah nama produk, harga qty, total harga, dan qty!
                        </div>
                    </div>

                    <!-- Live Parsed Notification Badge -->
                    <div id="parseBadgeTambah" class="alert alert-success border-0 py-2 px-3 small d-none mb-3 d-flex align-items-center">
                        <i class="ti ti-check-circle fs-5 me-2 text-success"></i>
                        <span id="parseMessageTambah">Teks berhasil dipilah secara otomatis!</span>
                    </div>

                    <!-- Parsed Result Fields Card -->
                    <div class="card bg-light border p-3 rounded-3 mb-3">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                            <i class="ti ti-clipboard-data text-primary me-2"></i>Hasil Pemilahan (Dapat Diedit Sesuai Kebutuhan)
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="nama_produk" id="tambah_nama_produk" class="form-control" placeholder="Contoh: AIGO Bronet 7GB 28hr" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Harga Qty (Satuan)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Rp</span>
                                    <input type="text" name="harga_qty" id="tambah_harga_qty" class="form-control js-currency-input" placeholder="0" oninput="calculateTotalTambah()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Qty</label>
                                <input type="number" name="qty" id="tambah_qty" class="form-control text-center" value="1" min="1" oninput="calculateTotalTambah()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Total Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Rp</span>
                                    <input type="text" name="total_harga" id="tambah_total_harga" class="form-control js-currency-input" placeholder="0">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Field: Upload Gambar with Ctrl+V Paste Area -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark">Upload Gambar (Opsional)</label>
                        <div class="upload-paste-zone border border-2 border-dashed rounded-3 p-3 text-center position-relative" id="dropzoneTambah" style="cursor: pointer; background: #fafbfc;">
                            <input type="file" name="gambar" id="tambah_gambar_input" class="d-none" accept="image/*">
                            <input type="hidden" name="gambar_base64" id="tambah_gambar_base64">

                            <!-- Placeholder inside dropzone -->
                            <div id="dropzonePlaceholderTambah" class="py-2">
                                <i class="ti ti-photo-plus fs-1 text-primary opacity-75 d-block mb-1"></i>
                                <span class="fw-semibold text-dark d-block">Klik untuk pilih gambar atau tekan <kbd class="bg-primary text-white px-2 py-1 rounded">Ctrl + V</kbd> untuk Paste Screenshot</span>
                                <span class="text-muted small">Mendukung format JPG, PNG, WEBP (Maksimal 10MB)</span>
                            </div>

                            <!-- Preview container inside dropzone -->
                            <div id="dropzonePreviewTambah" class="d-none text-center">
                                <div class="position-relative d-inline-block">
                                    <img id="previewImgTambah" src="" alt="Preview Gambar" class="rounded border shadow-sm" style="max-height: 180px; max-width: 100%; object-fit: contain;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1" style="width: 26px; height: 26px; line-height: 1;" onclick="removeImageTambah(event)" title="Hapus gambar">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <div class="mt-2 text-muted small" id="previewFilenameTambah">Gambar siap diunggah</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Pendataan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL EDIT PENDATAAN ================= -->
<div class="modal fade" id="modalEditPendataan" tabindex="-1" aria-labelledby="modalEditPendataanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="" method="POST" enctype="multipart/form-data" id="formEditPendataan">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditPendataanLabel">
                        <i class="ti ti-edit text-warning me-2"></i>Edit Data Pendataan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Field: Nama Penanggung Jawab (Terkunci) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Penanggung Jawab</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="ti ti-user-lock"></i></span>
                            <input type="text" id="edit_nama" class="form-control bg-light fw-bold text-dark" readonly disabled>
                        </div>
                        <div class="form-text text-muted small">
                            <i class="ti ti-lock me-1"></i>Nama penanggung jawab transaksi terkunci dan tidak dapat diubah.
                        </div>
                    </div>

                    <!-- Field: Deskripsi -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold text-dark mb-0">Deskripsi / Rincian Transaksi</label>
                            <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" id="btnPilahDeskripsiEdit" title="Pilah teks secara manual">
                                <i class="ti ti-wand me-1"></i> Pilah Ulang Teks
                            </button>
                        </div>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="4" class="form-control"></textarea>
                    </div>

                    <!-- Live Parsed Notification Badge -->
                    <div id="parseBadgeEdit" class="alert alert-success border-0 py-2 px-3 small d-none mb-3 d-flex align-items-center">
                        <i class="ti ti-check-circle fs-5 me-2 text-success"></i>
                        <span>Teks berhasil dipilah ulang!</span>
                    </div>

                    <!-- Parsed Result Fields Card -->
                    <div class="card bg-light border p-3 rounded-3 mb-3">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                            <i class="ti ti-clipboard-data text-primary me-2"></i>Rincian Produk & Harga
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="nama_produk" id="edit_nama_produk" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Harga Qty (Satuan)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Rp</span>
                                    <input type="text" name="harga_qty" id="edit_harga_qty" class="form-control js-currency-input" oninput="calculateTotalEdit()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Qty</label>
                                <input type="number" name="qty" id="edit_qty" class="form-control text-center" min="1" oninput="calculateTotalEdit()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Total Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Rp</span>
                                    <input type="text" name="total_harga" id="edit_total_harga" class="form-control js-currency-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Field: Upload / Replace Gambar -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark">Gambar</label>
                        <div class="upload-paste-zone border border-2 border-dashed rounded-3 p-3 text-center position-relative" id="dropzoneEdit" style="cursor: pointer; background: #fafbfc;">
                            <input type="file" name="gambar" id="edit_gambar_input" class="d-none" accept="image/*">
                            <input type="hidden" name="gambar_base64" id="edit_gambar_base64">
                            <input type="hidden" name="hapus_gambar" id="edit_hapus_gambar" value="0">

                            <!-- Placeholder inside dropzone -->
                            <div id="dropzonePlaceholderEdit" class="py-2">
                                <i class="ti ti-photo-plus fs-1 text-primary opacity-75 d-block mb-1"></i>
                                <span class="fw-semibold text-dark d-block">Klik untuk ganti gambar atau tekan <kbd class="bg-primary text-white px-2 py-1 rounded">Ctrl + V</kbd> untuk Paste Screenshot baru</span>
                                <span class="text-muted small">Mendukung format JPG, PNG, WEBP (Maksimal 10MB)</span>
                            </div>

                            <!-- Preview container inside dropzone -->
                            <div id="dropzonePreviewEdit" class="d-none text-center">
                                <div class="position-relative d-inline-block">
                                    <img id="previewImgEdit" src="" alt="Preview Gambar" class="rounded border shadow-sm" style="max-height: 180px; max-width: 100%; object-fit: contain;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1" style="width: 26px; height: 26px; line-height: 1;" onclick="removeImageEdit(event)" title="Hapus gambar">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <div class="mt-2 text-muted small" id="previewFilenameEdit">Gambar saat ini</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                        <i class="ti ti-device-floppy me-1"></i> Perbarui Pendataan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL DETAIL PENDATAAN ================= -->
<div class="modal fade" id="modalDetailPendataan" tabindex="-1" aria-labelledby="modalDetailPendataanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="modalDetailPendataanLabel">
                    <i class="ti ti-file-description text-primary me-2"></i>Detail Pendataan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 text-center pb-3 border-bottom">
                    <h5 class="fw-bold text-dark mb-1" id="detail_nama_produk">-</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 fw-bold fs-6" id="detail_total_harga">Rp 0</span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Harga Qty (Satuan):</small>
                        <span class="fw-semibold text-dark" id="detail_harga_qty">Rp 0</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Total Qty:</small>
                        <span class="fw-semibold text-dark" id="detail_qty">0</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Nama:</small>
                        <span class="fw-semibold text-dark" id="detail_nama">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Tanggal & Jam:</small>
                        <span class="fw-semibold text-dark" id="detail_tanggal">-</span>
                    </div>
                </div>

                <div class="mb-3" id="detail_deskripsi_section">
                    <small class="text-muted fw-semibold d-block mb-1">Rincian Deskripsi Mentah:</small>
                    <pre class="bg-light p-3 rounded border text-secondary small mb-0" id="detail_deskripsi" style="white-space: pre-wrap; word-break: break-word; max-height: 200px; overflow-y: auto;"></pre>
                </div>

                <div id="detail_gambar_section" class="text-center mt-3 pt-3 border-top d-none">
                    <small class="text-muted fw-semibold d-block mb-2 text-start">Lampiran Gambar:</small>
                    <a href="javascript:void(0)" id="detail_gambar_link" target="_blank">
                        <img id="detail_gambar" src="" alt="Gambar Pendataan" class="rounded border shadow-sm img-fluid" style="max-height: 240px; cursor: pointer;">
                    </a>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL LIGHTBOX GAMBAR ================= -->
<div class="modal fade" id="modalLightboxGambar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1055;"></button>
                <img id="lightboxImg" src="" alt="Preview Besar" class="rounded shadow-lg img-fluid" style="max-height: 85vh; background: #fff;">
                <div class="text-white mt-2 fw-semibold" id="lightboxCaption"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// =========================================================================
// TEXT PARSER LOGIC (CLIENT SIDE)
// =========================================================================
function cleanPriceText(raw) {
    if (!raw) return 0;
    raw = String(raw).trim().replace(/[^\d.,]/g, '');
    if (!raw) return 0;

    if (raw.includes('.') && raw.includes(',')) {
        raw = raw.replace(/\./g, '').replace(',', '.');
    } else if (raw.includes('.')) {
        const parts = raw.split('.');
        if (parts.length > 1 && parts[parts.length - 1].length === 3) {
            raw = parts.join('');
        }
    } else if (raw.includes(',')) {
        const parts = raw.split(',');
        if (parts.length > 1 && parts[parts.length - 1].length === 3) {
            raw = parts.join('');
        } else {
            raw = raw.replace(',', '.');
        }
    }
    return parseFloat(raw) || 0;
}

function formatRupiahNumber(num) {
    if (!num || isNaN(num)) return '0';
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function parseTransactionText(text) {
    const res = {
        nama_produk: '',
        harga_qty: 0,
        total_harga: 0,
        qty: 1
    };

    if (!text || !text.trim()) return res;

    const clean = text.replace(/\r\n|\r/g, '\n').trim();

    // 1. Check labeled format
    const mProduk = clean.match(/(?:Nama\s*Produk|Produk)\s*[:=]\s*(.+)/i);
    if (mProduk) res.nama_produk = mProduk[1].trim();

    const mHargaQty = clean.match(/(?:Harga\s*Qty|Harga\s*Satuan|Harga\s*Per\s*Qty)\s*[:=]\s*(?:Rp\.?\s*)?([\d.,]+)/i);
    if (mHargaQty) res.harga_qty = cleanPriceText(mHargaQty[1]);

    const mTotal = clean.match(/(?:Total\s*Tagihan|Total\s*Harga|Total\s*Pembayaran|Total\s*Bayar)\s*[:=]?\s*\n?\s*(?:Rp\.?\s*)?([\d.,]+)/i);
    if (mTotal) res.total_harga = cleanPriceText(mTotal[1]);

    const mQty = clean.match(/(?:Total\s*Qty|Qty|Jumlah)\s*[:=]?\s*\n?\s*(\d+)/i);
    if (mQty) res.qty = parseInt(mQty[1], 10) || 1;

    // 2. Check +/- counter block
    // Require the "-" at the start of a line so it doesn't match "5hr - 15.000".
    // Make the trailing "+" optional in case user didn't copy it.
    if (res.qty <= 1) {
        const mCounter = clean.match(/(?:^|\n)[ \t]*[-\u2013\u2014][ \t]*\n[ \t]*(\d+)[ \t]*(?:\n[ \t]*[+\uff0b])?/m);
        if (mCounter) res.qty = parseInt(mCounter[1], 10) || 1;
    }

    // 3. Fallback per-line inspection
    const lines = clean.split('\n').map(l => l.trim()).filter(l => l !== '');

    // Product name fallback
    if (!res.nama_produk) {
        const ignoreKeywords = [
            'konfirmasi', 'detail transaksi', 'rincian transaksi', 'metode pembayaran',
            'saldo dompul', 'pin dompul', 'pin keuangan', 'masukkan pin',
            'total tagihan', 'total qty', 'total bayar', 'pembayaran', 'ringkasan'
        ];

        for (let line of lines) {
            const lower = line.toLowerCase();
            if (['-', '+', '–', '—', '＋'].includes(line)) continue;
            if (/^\d+$/.test(line)) continue;
            if (/^(?:rp\.?|idr)\s*[\d.,]+/i.test(line)) continue;

            let ignored = false;
            for (let kw of ignoreKeywords) {
                if (lower.includes(kw)) {
                    ignored = true;
                    break;
                }
            }

            if (!ignored) {
                res.nama_produk = line;
                break;
            }
        }
    }

    // Fallback Total Tagihan
    if (res.total_harga <= 0) {
        for (let i = 0; i < lines.length; i++) {
            if (/Total\s*(?:Tagihan|Bayar|Pembayaran|Harga)/i.test(lines[i])) {
                const matchCurr = lines[i].match(/(?:Rp\.?\s*)?([\d.,]+)/i);
                if (matchCurr && cleanPriceText(matchCurr[1]) > 0) {
                    res.total_harga = cleanPriceText(matchCurr[1]);
                    break;
                }
                if (lines[i + 1] && cleanPriceText(lines[i + 1]) > 0) {
                    res.total_harga = cleanPriceText(lines[i + 1]);
                    break;
                }
            }
        }
    }

    // Fallback Total Qty
    if (res.qty <= 1) {
        for (let i = 0; i < lines.length; i++) {
            if (/Total\s*Qty/i.test(lines[i])) {
                const m = lines[i].match(/(\d+)/);
                if (m) {
                    res.qty = parseInt(m[1], 10) || 1;
                    break;
                }
                if (lines[i + 1] && /^\d+$/.test(lines[i + 1])) {
                    res.qty = parseInt(lines[i + 1], 10) || 1;
                    break;
                }
            }
        }
    }

    // Fallback Harga Qty
    if (res.harga_qty <= 0) {
        let foundProduct = !res.nama_produk;
        for (let i = 0; i < lines.length; i++) {
            if (!foundProduct) {
                if (lines[i] === res.nama_produk) foundProduct = true;
                continue;
            }
            if (/(?:Metode\s*Pembayaran|Total\s*(?:Tagihan|Bayar|Pembayaran))/i.test(lines[i])) break;

            if (/^(?:Rp\.?|IDR)?\s*[\d.,]+\s*$/i.test(lines[i]) && /\d/.test(lines[i])) {
                const p = cleanPriceText(lines[i]);
                if (p > 0) {
                    res.harga_qty = p;
                    break;
                }
            }
        }
    }

    // Cross calculations
    if (res.harga_qty <= 0 && res.total_harga > 0 && res.qty > 0) {
        res.harga_qty = Math.round(res.total_harga / res.qty);
    }
    if (res.total_harga <= 0 && res.harga_qty > 0 && res.qty > 0) {
        res.total_harga = Math.round(res.harga_qty * res.qty);
    }

    return res;
}

// Auto-calculate total in modal Tambah
function calculateTotalTambah() {
    const hargaQty = cleanPriceText(document.getElementById('tambah_harga_qty').value);
    const qty = parseInt(document.getElementById('tambah_qty').value, 10) || 1;
    if (hargaQty > 0) {
        document.getElementById('tambah_total_harga').value = formatRupiahNumber(hargaQty * qty);
    }
}

// Auto-calculate total in modal Edit
function calculateTotalEdit() {
    const hargaQty = cleanPriceText(document.getElementById('edit_harga_qty').value);
    const qty = parseInt(document.getElementById('edit_qty').value, 10) || 1;
    if (hargaQty > 0) {
        document.getElementById('edit_total_harga').value = formatRupiahNumber(hargaQty * qty);
    }
}

// =========================================================================
// CLIPBOARD IMAGE PASTE (CTRL + V) & DROPZONE HANDLING
// =========================================================================
function handleImageFile(file, isEdit) {
    if (!file || !file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const base64 = e.target.result;
        if (isEdit) {
            document.getElementById('previewImgEdit').src = base64;
            document.getElementById('dropzonePreviewEdit').classList.remove('d-none');
            document.getElementById('dropzonePlaceholderEdit').classList.add('d-none');
            document.getElementById('edit_gambar_base64').value = base64;
            document.getElementById('edit_hapus_gambar').value = '0';
            document.getElementById('previewFilenameEdit').textContent = file.name || 'Screenshot Clipboard (' + Math.round(file.size / 1024) + ' KB)';
        } else {
            document.getElementById('previewImgTambah').src = base64;
            document.getElementById('dropzonePreviewTambah').classList.remove('d-none');
            document.getElementById('dropzonePlaceholderTambah').classList.add('d-none');
            document.getElementById('tambah_gambar_base64').value = base64;
            document.getElementById('previewFilenameTambah').textContent = file.name || 'Screenshot Clipboard (' + Math.round(file.size / 1024) + ' KB)';
        }
    };
    reader.readAsDataURL(file);

    // Also populate standard file input via DataTransfer
    try {
        const dt = new DataTransfer();
        dt.items.add(file);
        const inputId = isEdit ? 'edit_gambar_input' : 'tambah_gambar_input';
        document.getElementById(inputId).files = dt.files;
    } catch (err) {
        // Fallback uses base64
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Gambar berhasil ditempel dari Clipboard!',
            showConfirmButton: false,
            timer: 2000
        });
    }
}

function removeImageTambah(e) {
    e.stopPropagation();
    document.getElementById('tambah_gambar_input').value = '';
    document.getElementById('tambah_gambar_base64').value = '';
    document.getElementById('previewImgTambah').src = '';
    document.getElementById('dropzonePreviewTambah').classList.add('d-none');
    document.getElementById('dropzonePlaceholderTambah').classList.remove('d-none');
}

function removeImageEdit(e) {
    e.stopPropagation();
    document.getElementById('edit_gambar_input').value = '';
    document.getElementById('edit_gambar_base64').value = '';
    document.getElementById('edit_hapus_gambar').value = '1';
    document.getElementById('previewImgEdit').src = '';
    document.getElementById('dropzonePreviewEdit').classList.add('d-none');
    document.getElementById('dropzonePlaceholderEdit').classList.remove('d-none');
}

// Global Ctrl+V listener
window.addEventListener('paste', function(e) {
    const modalTambah = document.getElementById('modalTambahPendataan');
    const modalEdit = document.getElementById('modalEditPendataan');

    const isTambahOpen = modalTambah && modalTambah.classList.contains('show');
    const isEditOpen = modalEdit && modalEdit.classList.contains('show');

    if (!isTambahOpen && !isEditOpen) return;

    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
    for (let item of items) {
        if (item.type.indexOf('image') !== -1) {
            e.preventDefault();
            const blob = item.getAsFile();
            handleImageFile(blob, isEditOpen);
            break;
        }
    }
});

// Setup click and drag-drop on dropzones
function setupDropzone(zoneId, inputId, isEdit) {
    const zone = document.getElementById(zoneId);
    const input = document.getElementById(inputId);
    if (!zone || !input) return;

    zone.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        input.click();
    });

    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleImageFile(this.files[0], isEdit);
        }
    });

    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        zone.classList.add('border-primary', 'bg-light');
    });

    zone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        zone.classList.remove('border-primary', 'bg-light');
    });

    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('border-primary', 'bg-light');
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleImageFile(e.dataTransfer.files[0], isEdit);
        }
    });
}

// =========================================================================
// MODAL & ACTION HANDLERS
// =========================================================================
function openEditModal(item) {
    const form = document.getElementById('formEditPendataan');
    form.action = "{{ url('pendataan') }}/" + item.id;

    document.getElementById('edit_nama').value = item.nama || '';
    document.getElementById('edit_deskripsi').value = item.deskripsi || '';
    document.getElementById('edit_nama_produk').value = item.nama_produk || '';
    document.getElementById('edit_harga_qty').value = formatRupiahNumber(item.harga_qty);
    document.getElementById('edit_qty').value = item.qty || 1;
    document.getElementById('edit_total_harga').value = formatRupiahNumber(item.total_harga);
    document.getElementById('edit_hapus_gambar').value = '0';
    document.getElementById('edit_gambar_base64').value = '';
    document.getElementById('edit_gambar_input').value = '';

    const preview = document.getElementById('dropzonePreviewEdit');
    const placeholder = document.getElementById('dropzonePlaceholderEdit');
    const previewImg = document.getElementById('previewImgEdit');

    if (item.gambar_url) {
        previewImg.src = item.gambar_url;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
        document.getElementById('previewFilenameEdit').textContent = 'Gambar yang tersimpan';
    } else {
        previewImg.src = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
    }

    const modal = new bootstrap.Modal(document.getElementById('modalEditPendataan'));
    modal.show();
}

function openDetailModal(id) {
    fetch("{{ url('pendataan') }}/" + id, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(payload => {
        if (payload.success) {
            const data = payload.data;
            document.getElementById('detail_nama_produk').textContent = data.nama_produk;
            document.getElementById('detail_total_harga').textContent = data.formatted_total_harga;
            document.getElementById('detail_harga_qty').textContent = data.formatted_harga_qty;
            document.getElementById('detail_qty').textContent = data.qty;
            document.getElementById('detail_nama').textContent = data.nama;
            document.getElementById('detail_tanggal').textContent = data.created_at;

            const deskripsiSec = document.getElementById('detail_deskripsi_section');
            if (data.deskripsi && data.deskripsi.trim()) {
                document.getElementById('detail_deskripsi').textContent = data.deskripsi;
                deskripsiSec.classList.remove('d-none');
            } else {
                deskripsiSec.classList.add('d-none');
            }

            const gambarSec = document.getElementById('detail_gambar_section');
            if (data.gambar_url) {
                document.getElementById('detail_gambar').src = data.gambar_url;
                document.getElementById('detail_gambar_link').href = data.gambar_url;
                gambarSec.classList.remove('d-none');
            } else {
                gambarSec.classList.add('d-none');
            }

            const modal = new bootstrap.Modal(document.getElementById('modalDetailPendataan'));
            modal.show();
        }
    })
    .catch(err => console.error(err));
}

function previewLightboxImage(url, caption) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('lightboxCaption').textContent = caption || '';
    const modal = new bootstrap.Modal(document.getElementById('modalLightboxGambar'));
    modal.show();
}



// =========================================================================
// DOM INITIALIZATION
// =========================================================================
document.addEventListener('DOMContentLoaded', function() {
    setupDropzone('dropzoneTambah', 'tambah_gambar_input', false);
    setupDropzone('dropzoneEdit', 'edit_gambar_input', true);

    // Refresh idempotency token every time the Tambah modal opens
    const modalTambahEl = document.getElementById('modalTambahPendataan');
    if (modalTambahEl) {
        modalTambahEl.addEventListener('show.bs.modal', function() {
            const tokenField = document.getElementById('tambah_idempotency_token');
            if (tokenField) {
                tokenField.value = 'tok_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
            }
            // Re-enable submit button in case it was disabled from a previous attempt
            const btn = document.querySelector('#formTambahPendataan button[type="submit"]');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Simpan Pendataan';
            }
        });
    }

    // Format currency inputs on blur / input
    document.querySelectorAll('.js-currency-input').forEach(input => {
        input.addEventListener('blur', function() {
            const val = cleanPriceText(this.value);
            if (val > 0) {
                this.value = formatRupiahNumber(val);
            }
        });
    });

    // Auto-parse on typing or pasting in Tambah textarea
    const deskripsiTambah = document.getElementById('tambah_deskripsi');
    if (deskripsiTambah) {
        const triggerParseTambah = function() {
            const text = deskripsiTambah.value;
            if (!text || !text.trim()) return;

            const parsed = parseTransactionText(text);
            if (parsed.nama_produk) {
                document.getElementById('tambah_nama_produk').value = parsed.nama_produk;
            }
            if (parsed.harga_qty > 0) {
                document.getElementById('tambah_harga_qty').value = formatRupiahNumber(parsed.harga_qty);
            }
            if (parsed.qty > 0) {
                document.getElementById('tambah_qty').value = parsed.qty;
            }
            if (parsed.total_harga > 0) {
                document.getElementById('tambah_total_harga').value = formatRupiahNumber(parsed.total_harga);
            }

            const badge = document.getElementById('parseBadgeTambah');
            if (badge) {
                badge.classList.remove('d-none');
                document.getElementById('parseMessageTambah').textContent = 
                    '✓ Dipilah: ' + parsed.nama_produk + ' (Qty: ' + parsed.qty + ' | Total: Rp ' + formatRupiahNumber(parsed.total_harga) + ')';
            }
        };

        deskripsiTambah.addEventListener('paste', function() {
            setTimeout(triggerParseTambah, 50);
        });

        deskripsiTambah.addEventListener('input', function() {
            triggerParseTambah();
        });

        document.getElementById('btnPilahDeskripsiTambah').addEventListener('click', triggerParseTambah);

        const formTambah = document.getElementById('formTambahPendataan');
        if (formTambah) {
            let _tambahSubmitting = false;
            formTambah.addEventListener('submit', function(e) {
                // Auto-parse if product name is empty
                if (!document.getElementById('tambah_nama_produk').value.trim()) {
                    triggerParseTambah();
                }
                // Prevent double-submit
                if (_tambahSubmitting) {
                    e.preventDefault();
                    return false;
                }
                _tambahSubmitting = true;
                const btn = formTambah.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
                }
            });
        }
    }

    // Auto-parse on Edit textarea
    const deskripsiEdit = document.getElementById('edit_deskripsi');
    if (deskripsiEdit) {
        const triggerParseEdit = function() {
            const text = deskripsiEdit.value;
            if (!text || !text.trim()) return;

            const parsed = parseTransactionText(text);
            if (parsed.nama_produk) {
                document.getElementById('edit_nama_produk').value = parsed.nama_produk;
            }
            if (parsed.harga_qty > 0) {
                document.getElementById('edit_harga_qty').value = formatRupiahNumber(parsed.harga_qty);
            }
            if (parsed.qty > 0) {
                document.getElementById('edit_qty').value = parsed.qty;
            }
            if (parsed.total_harga > 0) {
                document.getElementById('edit_total_harga').value = formatRupiahNumber(parsed.total_harga);
            }

            const badge = document.getElementById('parseBadgeEdit');
            if (badge) {
                badge.classList.remove('d-none');
            }
        };

        deskripsiEdit.addEventListener('paste', function() {
            setTimeout(triggerParseEdit, 50);
        });

        deskripsiEdit.addEventListener('input', function() {
            triggerParseEdit();
        });

        document.getElementById('btnPilahDeskripsiEdit').addEventListener('click', triggerParseEdit);

        const formEdit = document.getElementById('formEditPendataan');
        if (formEdit) {
            let _editSubmitting = false;
            formEdit.addEventListener('submit', function(e) {
                // Auto-parse if product name is empty
                if (!document.getElementById('edit_nama_produk').value.trim()) {
                    triggerParseEdit();
                }
                // Prevent double-submit
                if (_editSubmitting) {
                    e.preventDefault();
                    return false;
                }
                _editSubmitting = true;
                const btn = formEdit.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
                }
            });
        }
    }
});
</script>
@endpush
