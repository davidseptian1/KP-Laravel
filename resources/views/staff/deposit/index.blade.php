@extends('layouts/app')

@section('content')

@php
    $perPage = (int) ($perPage ?? request('per_page', 10));
@endphp

<style>
    .staff-deposit-page .section-card {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 10px;
        background: var(--bs-body-bg, #fff);
    }

    .staff-deposit-page .section-title {
        font-size: 0.92rem;
        font-weight: 600;
        color: var(--bs-heading-color, #212529);
    }

    .staff-deposit-page .filter-row .form-label {
        margin-bottom: 0.35rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--bs-secondary-color, #6c757d);
    }

    .staff-deposit-page .filter-row .form-control-sm,
    .staff-deposit-page .filter-row .form-select-sm {
        min-height: 36px;
    }

    .staff-deposit-page .table-wrap {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 10px;
        overflow-x: auto;
        overflow-y: hidden;
    }

    .staff-deposit-page .table-wrap table {
        min-width: 1500px;
    }

    .staff-deposit-page .table thead th {
        font-size: 0.78rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .staff-deposit-page .table thead th.js-col-draggable {
        cursor: move;
        user-select: none;
    }

    .staff-deposit-page .table thead th.js-col-dragging {
        opacity: 0.55;
    }

    .staff-deposit-page .column-settings-panel {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 10px;
        background: var(--bs-body-bg, #fff);
    }

    .staff-deposit-page .column-option-item {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 8px;
        padding: 4px 8px;
        background: var(--bs-tertiary-bg, #f8f9fa);
    }

    textarea.js-auto-resize-textarea {
        overflow-y: auto;
        resize: none;
        min-height: 70px;
        max-height: 260px;
    }

</style>

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Request Deposit</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Request Deposit</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0 staff-deposit-page">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="section-title mb-0"><i class="ti ti-adjustments-horizontal me-1"></i>Filter & Aktivitas</div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRequestDeposit">
                    <i class="ti ti-plus me-1"></i>Request Deposit
                </button>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger mb-3">
                        <div class="fw-semibold mb-1">Request deposit gagal disimpan:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="GET" class="row g-3 align-items-end mb-3 filter-row">
                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $tanggal ?? now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label class="form-label">Nama Server</label>
                        <select name="server" class="form-select form-select-sm">
                            <option value="">Semua Server</option>
                            @foreach (($servers ?? collect()) as $server)
                                <option value="{{ $server }}" {{ ($serverFilter ?? '') === $server ? 'selected' : '' }}>{{ $server }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="selesai" {{ ($status ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <label class="form-label">Cari Nama Supplier</label>
                        <input type="text" name="search_supplier" class="form-control form-control-sm" value="{{ $searchSupplier ?? '' }}" placeholder="Contoh: DIGIFLAZZ">
                    </div>
                    <div class="col-xl-2 col-lg-3 col-md-4">
                        <label class="form-label">Nominal</label>
                        <input type="text" name="nominal" class="form-control form-control-sm" value="{{ $nominalFilter ?? '' }}" placeholder="Contoh: 2.500.000">
                    </div>
                    <div class="col-xl-1 col-lg-3 col-md-4">
                        <label class="form-label">Tampilkan</label>
                        <select name="per_page" class="form-select form-select-sm">
                            @foreach ([10, 25, 50, 100] as $limit)
                                <option value="{{ $limit }}" {{ (int)($perPage ?? 10) === $limit ? 'selected' : '' }}>{{ $limit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4"><i class="ti ti-filter me-1"></i>Filter</button>
                        <a href="{{ route('deposit.request.index') }}" class="btn btn-outline-secondary btn-sm px-4">Hari Ini</a>
                    </div>
                </form>

                <div class="section-card mb-3">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-2">
                        <div>
                            <div class="fw-semibold mb-1"><i class="ti ti-download me-1"></i>Download Laporan Harian (00:00 - 23:59)</div>
                            <small class="text-muted">Laporan mengikuti filter aktif dan nama file akan memakai email staff.</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a
                                href="{{ route('deposit.request.export-excel', ['tanggal' => $tanggal, 'server' => $serverFilter, 'status' => $status, 'search_supplier' => $searchSupplier, 'nominal' => $nominalFilter]) }}"
                                class="btn btn-success btn-sm"
                            >
                                <i class="ti ti-file-spreadsheet me-1"></i>Download Excel
                            </a>
                            <a
                                href="{{ route('deposit.request.export-pdf', ['tanggal' => $tanggal, 'server' => $serverFilter, 'status' => $status, 'search_supplier' => $searchSupplier, 'nominal' => $nominalFilter]) }}"
                                class="btn btn-danger btn-sm"
                                target="_blank"
                            >
                                <i class="ti ti-file-type-pdf me-1"></i>Download PDF
                            </a>
                        </div>
                    </div>
                </div>

                <div class="section-card mb-3">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-2">
                        <div>
                            <div class="fw-semibold mb-1"><i class="ti ti-bell me-1"></i>Pengaturan Notifikasi</div>
                            <small class="text-muted" id="browserNotifStatus">Status browser notification: mengecek...</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnEnableBrowserNotif">
                                Aktifkan Notifikasi Browser
                            </button>
                        </div>
                    </div>
                </div>

                <div id="latestActivityCardContainer">
                    @include('staff.deposit.partials.latest-activity-card', ['latestActivityItem' => $latestActivityItem ?? null])
                </div>

                <div id="todayTotalCardContainer">
                    @include('staff.deposit.partials.today-total-card', ['todayDepositSummary' => $todayDepositSummary ?? null])
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <div class="card border-0 bg-success-subtle h-100">
                            <div class="card-body py-2">
                                <div class="fw-semibold text-success"><i class="ti ti-trending-up me-1"></i>Nominal Tinggi (≥ Rp 10.000.000)</div>
                                <small class="text-muted">Ditampilkan dengan card hijau dan teks tebal.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-primary-subtle h-100">
                            <div class="card-body py-2">
                                <div class="fw-semibold text-primary"><i class="ti ti-cash-banknote me-1"></i>Nominal Reguler (&lt; Rp 10.000.000)</div>
                                <small class="text-muted">Ditampilkan dengan card biru.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mb-2 gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnToggleColumnSettings">
                        Atur Kolom
                    </button>
                </div>

                <div id="columnSettingsPanel" class="column-settings-panel p-2 mb-2 d-none">
                    <div class="small text-muted mb-2">Centang kolom yang ingin ditampilkan.</div>
                    <div id="columnVisibilityOptions" class="d-flex flex-wrap gap-2"></div>
                    <div class="text-end mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetTableColumns">Reset Kolom</button>
                    </div>
                </div>

                <div class="table-responsive table-wrap">
                    <table class="table table-hover align-middle js-reorderable-table">
                        <thead class="table-light">
                            <tr>
                                <th><i class="ti ti-calendar-event me-1"></i>Tanggal</th>
                                <th><i class="ti ti-building-store me-1"></i>NAMA SUPPLIER</th>
                                <th><i class="ti ti-category me-1"></i>JENIS</th>
                                <th class="text-end"><i class="ti ti-currency-dollar me-1"></i>NOMINAL</th>
                                <th><i class="ti ti-building-bank me-1"></i>BANK</th>
                                <th><i class="ti ti-server me-1"></i>SERVER</th>
                                <th><i class="ti ti-credit-card me-1"></i>NOREK</th>
                                <th><i class="ti ti-user me-1"></i>NAMA REKENING</th>
                                <th><i class="ti ti-ticket me-1"></i>Reply Tiket</th>
                                <th><i class="ti ti-file-text me-1"></i>REPLY PENAMBAHAN</th>
                                <th><i class="ti ti-photo me-1"></i>Bukti Tranfers Admin</th>
                                <th><i class="ti ti-list-check me-1"></i>STATUS</th>
                                <th><i class="ti ti-clock me-1"></i>JAM</th>
                                <th><i class="ti ti-settings me-1"></i>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                @php
                                    $isHighNominal = (float) $item->nominal >= 10000000;
                                @endphp
                                <tr data-deposit-id="{{ $item->id }}">
                                    <td>{{ $item->created_at?->format('d/m/Y') }}</td>
                                    <td class="cell-nama-supplier">{{ $item->nama_supplier }}</td>
                                    <td>{{ strtoupper($item->jenis_transaksi ?? 'deposit') }}</td>
                                    <td class="text-end">
                                        <div class="d-inline-block rounded px-2 py-1 {{ $isHighNominal ? 'bg-success-subtle text-success fw-bold' : 'bg-primary-subtle text-primary fw-semibold' }}">
                                            <i class="ti {{ $isHighNominal ? 'ti-trending-up' : 'ti-cash-banknote' }} me-1"></i>
                                            Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td>{{ $item->bank }}</td>
                                    <td>{{ $item->server }}</td>
                                    <td>{{ $item->no_rek }}</td>
                                    <td class="cell-nama-rekening">{{ $item->nama_rekening }}</td>
                                    <td class="cell-reply-tiket">{{ $item->reply_tiket ?? '-' }}</td>
                                    <td class="cell-reply-penambahan">{{ $item->reply_penambahan ?? 'Menunggu Konfirmasi Admin' }}</td>
                                    <td class="cell-bukti-transfer">
                                        @if (($item->bukti_transfer_admin_type ?? 'text') === 'image')
                                            @if (!empty($item->bukti_transfer_admin_text))
                                                <div class="mb-1">{{ $item->bukti_transfer_admin_text }}</div>
                                            @endif
                                            @if (!empty($item->bukti_transfer_admin_image))
                                                <a href="{{ route('deposit.request.transfer-admin-image', $item->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Gambar</a>
                                            @else
                                                -
                                            @endif
                                        @else
                                            {{ $item->bukti_transfer_admin_text ?? '-' }}
                                        @endif
                                    </td>
                                    <td class="cell-status">
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'selesai' ? 'primary' : 'warning')) }}">
                                            <i class="ti {{ $item->status === 'approved' ? 'ti-circle-check' : ($item->status === 'rejected' ? 'ti-circle-x' : ($item->status === 'selesai' ? 'ti-checks' : 'ti-hourglass')) }} me-1"></i>
                                            {{ ucfirst($item->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td class="cell-jam">{{ $item->jam ? \Carbon\Carbon::parse($item->jam)->format('H:i') : '-' }}</td>
                                    <td class="cell-aksi">
                                        @if (($item->status ?? 'pending') === 'approved')
                                            <button type="button" class="btn btn-sm btn-primary js-action-input-reply" data-bs-toggle="modal" data-bs-target="#modalInputReply-{{ $item->id }}">Input</button>

                                            <div class="modal fade" id="modalInputReply-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('deposit.request.reply.update', $item->id) }}" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Input Reply Penambahan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Tipe Reply Penambahan</label>
                                                                    <select name="reply_penambahan_type" class="form-select js-staff-reply-type" data-target="{{ $item->id }}" required>
                                                                        <option value="text" {{ ($item->reply_penambahan_type ?? 'text') === 'text' ? 'selected' : '' }}>Text</option>
                                                                        <option value="image" {{ ($item->reply_penambahan_type ?? 'text') === 'image' ? 'selected' : '' }}>Image</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3 js-staff-reply-text-wrap" data-target="{{ $item->id }}" style="display: {{ ($item->reply_penambahan_type ?? 'text') === 'text' ? 'block' : 'none' }};">
                                                                    <label class="form-label">Reply Penambahan</label>
                                                                    <textarea name="reply_penambahan" class="form-control js-auto-resize-textarea" rows="4" placeholder="Masukkan reply penambahan">{{ $item->reply_penambahan }}</textarea>
                                                                </div>
                                                                <div class="mb-1 js-staff-reply-image-wrap" data-target="{{ $item->id }}" style="display: {{ ($item->reply_penambahan_type ?? 'text') === 'image' ? 'block' : 'none' }};">
                                                                    <label class="form-label">Upload / Paste Gambar</label>
                                                                    <input type="file" name="reply_penambahan_image" class="form-control js-staff-reply-image-input" data-target="{{ $item->id }}" accept="image/png,image/jpeg,image/jpg,image/webp">
                                                                    <small class="text-muted d-block mt-1">Bisa Ctrl+V dari clipboard saat fokus di area paste.</small>
                                                                    <div class="border rounded p-2 mt-2 js-staff-reply-paste-zone" data-target="{{ $item->id }}" tabindex="0" style="min-height:60px;">
                                                                        Paste gambar di sini (Ctrl+V)
                                                                    </div>
                                                                    <div class="mt-2 js-staff-reply-preview-wrap" data-target="{{ $item->id }}" style="display:none;">
                                                                        <img src="" alt="Preview Reply Penambahan" class="img-fluid rounded border js-staff-reply-preview" data-target="{{ $item->id }}" style="max-height:180px;">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif (($item->status ?? 'pending') === 'pending')
                                            <button type="button" class="btn btn-sm btn-outline-danger js-action-delete-pending" data-bs-toggle="modal" data-bs-target="#modalDeletePending-{{ $item->id }}">Hapus</button>

                                            <div class="modal fade" id="modalDeletePending-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('deposit.request.delete', $item->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Hapus Request Pending</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning py-2 mb-3">
                                                                    Request hanya dihapus dari daftar staff, data tetap tercatat dan terlihat di admin.
                                                                </div>
                                                                <label class="form-label">Alasan Penghapusan</label>
                                                                <textarea name="delete_note" class="form-control js-auto-resize-textarea" rows="3" placeholder="Contoh: Dihapus karena salah input nominal / rekening" required></textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-danger">Hapus Pending</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="text-center text-muted py-4">Belum ada request deposit pada tanggal ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRequestDeposit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('deposit.request.store') }}" enctype="multipart/form-data" id="staffRequestDepositForm" autocomplete="off">
                @csrf
                <input type="hidden" name="reply_penambahan" value="Menunggu Konfirmasi Admin">
                <div class="modal-header">
                    <h5 class="modal-title">Request Deposit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="form_id" value="{{ $activeForms->first()->id ?? '' }}">
                    <div class="alert alert-warning py-2 mb-3">
                        <strong>Note:</strong> Untuk <strong>Nama Supplier</strong>, <strong>Deposit/Hutang</strong>, <strong>Bank</strong>, dan <strong>Server</strong> gunakan data yang muncul. Jika data tidak ada, request tidak akan tersimpan.
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Supplier</label>
                            <input type="text" name="nama_supplier" class="form-control" list="supplierRequestList" value="{{ old('nama_supplier') }}" placeholder="Ketik nama supplier..." autocomplete="off" required>
                            <datalist id="supplierRequestList">
                                @foreach (($suppliers ?? collect()) as $supplier)
                                    <option value="{{ $supplier }}"></option>
                                @endforeach
                            </datalist>
                            @if (($suppliers ?? collect())->isEmpty())
                                <small class="text-danger">Belum ada supplier. Minta admin tambah supplier di menu Supplier Manajemen.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nominal</label>
                            <input type="text" name="nominal" class="form-control" inputmode="numeric" value="{{ old('nominal') }}" placeholder="Contoh: 1.250.000,-" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deposit / Hutang</label>
                            <input type="text" name="jenis_transaksi" class="form-control" list="jenisRequestList" value="{{ old('jenis_transaksi', 'deposit') }}" autocomplete="off" required>
                            <datalist id="jenisRequestList">
                                <option value="deposit"></option>
                                <option value="hutang"></option>
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank</label>
                            <input type="text" name="bank" class="form-control" list="bankRequestList" value="{{ old('bank') }}" placeholder="Ketik nama bank..." autocomplete="off" required>
                            <datalist id="bankRequestList">
                                @foreach (($banks ?? collect()) as $bank)
                                    <option value="{{ $bank }}"></option>
                                @endforeach
                            </datalist>
                            @if (($banks ?? collect())->isEmpty())
                                <small class="text-danger">Belum ada bank. Minta admin tambah bank di menu Bank Manajemen.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Tujuan</label>
                            <input type="text" name="bank_tujuan" class="form-control" list="bankTujuanRequestList" value="{{ old('bank_tujuan') }}" placeholder="Otomatis dari Reply Tiket" autocomplete="off">
                            <select id="bankTujuanParsedSelectRequest" class="form-select form-select-sm mt-2 d-none"></select>
                            <datalist id="bankTujuanRequestList"></datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Server</label>
                            <input type="text" name="server" class="form-control" list="serverRequestList" value="{{ old('server') }}" placeholder="Ketik server..." autocomplete="off" required>
                            <datalist id="serverRequestList">
                                @foreach (($servers ?? collect()) as $server)
                                    <option value="{{ $server }}"></option>
                                @endforeach
                            </datalist>
                            @if (($servers ?? collect())->isEmpty())
                                <small class="text-danger">Belum ada server. Minta admin tambah server di menu Server Manajemen.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No Rekening</label>
                            <input type="text" name="no_rek" class="form-control" list="noRekeningRequestList" value="{{ old('no_rek') }}" autocomplete="off" required>
                            <select id="noRekParsedSelectRequest" class="form-select form-select-sm mt-2 d-none"></select>
                            <datalist id="noRekeningRequestList"></datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Rekening</label>
                            <input type="text" name="nama_rekening" class="form-control" list="namaRekeningRequestList" value="{{ old('nama_rekening') }}" autocomplete="off" required>
                            <select id="namaRekParsedSelectRequest" class="form-select form-select-sm mt-2 d-none"></select>
                            <datalist id="namaRekeningRequestList"></datalist>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reply Tiket</label>
                            <textarea name="reply_tiket" class="form-control js-auto-resize-textarea" rows="2">{{ old('reply_tiket') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Upload / Paste Gambar Reply Tiket</label>
                            <input type="file" name="reply_tiket_image" id="replyTiketImageInput" class="form-control" accept="image/png,image/jpeg,image/jpg,image/webp">
                            <small class="text-muted d-block mt-1">Bisa Ctrl+V dari clipboard saat fokus di area paste.</small>
                            <div class="border rounded p-2 mt-2" id="replyTiketPasteZone" tabindex="0" style="min-height:60px;">
                                Paste gambar di sini (Ctrl+V)
                            </div>
                            <div class="mt-2" id="replyTiketPreviewWrap" style="display:none;">
                                <img src="" alt="Preview Reply Tiket" class="img-fluid rounded border" id="replyTiketPreview" style="max-height:180px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="staffRequestDepositResetBtn">Reset</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="staffRequestDepositSubmitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        let latestUpdatedAt = @json($latestUpdatedAt);
        const pollingUrl = @json(route('deposit.request.changes'));
        const transferImageBaseUrl = @json(url('deposit/request'));
        const tanggal = @json($tanggal ?? now()->format('Y-m-d'));
        const searchSupplier = @json($searchSupplier ?? '');
        const serverFilter = @json($serverFilter ?? '');
        const status = @json($status ?? '');
        const nominalFilter = @json($nominalFilter ?? '');
        const pollIntervalMs = 1000;
        const hasValidationErrors = @json($errors->any());

        const notifStatusEl = document.getElementById('browserNotifStatus');
        const enableNotifBtn = document.getElementById('btnEnableBrowserNotif');
        const staffRequestDepositForm = document.getElementById('staffRequestDepositForm');
        const staffRequestDepositResetBtn = document.getElementById('staffRequestDepositResetBtn');
        const staffRequestDepositSubmitBtn = document.getElementById('staffRequestDepositSubmitBtn');
        const replyTiketImageInput = document.getElementById('replyTiketImageInput');
        const replyTiketPasteZone = document.getElementById('replyTiketPasteZone');
        const replyTiketPreviewWrap = document.getElementById('replyTiketPreviewWrap');
        const replyTiketPreview = document.getElementById('replyTiketPreview');

        function setReplyTiketPreview(file) {
            if (!replyTiketPreview || !replyTiketPreviewWrap || !file) return;

            const reader = new FileReader();
            reader.onload = function (event) {
                replyTiketPreview.src = event.target.result;
                replyTiketPreviewWrap.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        function updateNotifStatusText() {
            if (!notifStatusEl) return;

            if (!('Notification' in window)) {
                notifStatusEl.textContent = 'Status browser notification: browser tidak mendukung notifikasi.';
                return;
            }

            if (Notification.permission === 'granted') {
                notifStatusEl.textContent = 'Status browser notification: diizinkan.';
            } else if (Notification.permission === 'denied') {
                notifStatusEl.textContent = 'Status browser notification: ditolak. Ubah dari pengaturan browser.';
            } else {
                notifStatusEl.textContent = 'Status browser notification: belum diizinkan.';
            }
        }

        async function requestBrowserNotificationPermission() {
            if (!('Notification' in window)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Browser tidak mendukung notifikasi',
                    timer: 1800,
                    showConfirmButton: false,
                });
                return;
            }

            const permission = await Notification.requestPermission();
            updateNotifStatusText();

            if (permission === 'granted') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Notifikasi browser berhasil diaktifkan',
                    showConfirmButton: false,
                    timer: 1800,
                });
            }
        }

        function sendBrowserNotification(title, description, changesCount) {
            if (!('Notification' in window)) return;
            if (Notification.permission !== 'granted') return;

            new Notification(title || 'Update Request Deposit', {
                body: description || ('Ada ' + changesCount + ' perubahan data deposit.'),
                tag: 'deposit-request-update',
            });
        }

        async function checkChanges() {
            try {
                const params = new URLSearchParams({
                    tanggal: tanggal,
                    search_supplier: searchSupplier
                });

                if (serverFilter) {
                    params.set('server', serverFilter);
                }

                if (status) {
                    params.set('status', status);
                }

                if (nominalFilter) {
                    params.set('nominal', nominalFilter);
                }

                if (latestUpdatedAt) {
                    params.set('since', latestUpdatedAt);
                }

                const response = await fetch(pollingUrl + '?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) return;

                const result = await response.json();

                if (result.latest_updated_at) {
                    latestUpdatedAt = result.latest_updated_at;
                }

                if (result.has_changes) {
                    const notifTitle = result.change_title || ('Ada perubahan data deposit (' + result.changes_count + ')');
                    const notifDescription = result.change_description || 'Ada perubahan data oleh admin.';

                    if (result.latest_card_html) {
                        const latestCardContainer = document.getElementById('latestActivityCardContainer');
                        if (latestCardContainer) {
                            latestCardContainer.innerHTML = result.latest_card_html;
                        }
                    }

                    if (result.today_total_card_html) {
                        const todayTotalCardContainer = document.getElementById('todayTotalCardContainer');
                        if (todayTotalCardContainer) {
                            todayTotalCardContainer.innerHTML = result.today_total_card_html;
                        }
                    }

                    applyChangedItems(result.changed_items || []);

                    sendBrowserNotification(notifTitle, notifDescription, result.changes_count);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'info',
                        title: notifTitle,
                        text: notifDescription,
                        showConfirmButton: false,
                        timer: 2400,
                        timerProgressBar: true,
                    });
                }
            } catch (error) {
            }
        }

        function statusBadgeHtml(status) {
            const normalized = (status || 'pending').toLowerCase();
            let badgeClass = 'warning';
            let iconClass = 'ti-hourglass';

            if (normalized === 'approved') {
                badgeClass = 'success';
                iconClass = 'ti-circle-check';
            } else if (normalized === 'rejected') {
                badgeClass = 'danger';
                iconClass = 'ti-circle-x';
            } else if (normalized === 'selesai') {
                badgeClass = 'primary';
                iconClass = 'ti-checks';
            }

            const label = normalized.charAt(0).toUpperCase() + normalized.slice(1);
            return '<span class="badge bg-' + badgeClass + '"><i class="ti ' + iconClass + ' me-1"></i>' + label + '</span>';
        }

        function buktiTransferHtml(item) {
            if ((item.bukti_transfer_admin_type || 'text') === 'image') {
                let html = '';

                if (item.bukti_transfer_admin_text) {
                    html += '<div class="mb-1">' + escapeHtml(item.bukti_transfer_admin_text) + '</div>';
                }

                if (item.has_bukti_transfer_admin_image) {
                    html += '<a href="' + transferImageBaseUrl + '/' + item.id + '/transfer-admin-image" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Gambar</a>';
                } else {
                    html += '-';
                }

                return html;
            }

            return escapeHtml(item.bukti_transfer_admin_text || '-');
        }

        function escapeHtml(text) {
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function applyChangedItems(items) {
            if (!Array.isArray(items) || items.length === 0) {
                return;
            }

            let hasMissingRow = false;
            let hasActionMismatch = false;

            items.forEach(function (item) {
                const row = document.querySelector('tr[data-deposit-id="' + item.id + '"]');
                if (!row) {
                    hasMissingRow = true;
                    return;
                }

                const supplierCell = row.querySelector('.cell-nama-supplier');
                const rekeningCell = row.querySelector('.cell-nama-rekening');
                const tiketCell = row.querySelector('.cell-reply-tiket');
                const penambahanCell = row.querySelector('.cell-reply-penambahan');
                const buktiCell = row.querySelector('.cell-bukti-transfer');
                const statusCell = row.querySelector('.cell-status');
                const jamCell = row.querySelector('.cell-jam');
                const aksiCell = row.querySelector('.cell-aksi');

                if (supplierCell) supplierCell.textContent = item.nama_supplier || '-';
                if (rekeningCell) rekeningCell.textContent = item.nama_rekening || '-';
                if (tiketCell) tiketCell.textContent = item.reply_tiket || '-';
                if (penambahanCell) penambahanCell.textContent = item.reply_penambahan || 'Menunggu Konfirmasi Admin';
                if (buktiCell) buktiCell.innerHTML = buktiTransferHtml(item);
                if (statusCell) statusCell.innerHTML = statusBadgeHtml(item.status || 'pending');
                if (jamCell) jamCell.textContent = item.jam || '-';

                if (aksiCell) {
                    const normalizedStatus = String(item.status || 'pending').toLowerCase();
                    if (normalizedStatus === 'approved' && !aksiCell.querySelector('.js-action-input-reply')) {
                        hasActionMismatch = true;
                    }
                    if (normalizedStatus === 'pending' && !aksiCell.querySelector('.js-action-delete-pending')) {
                        hasActionMismatch = true;
                    }
                }

                row.classList.add('table-info');
                setTimeout(function () {
                    row.classList.remove('table-info');
                }, 2200);
            });

            if (hasMissingRow || hasActionMismatch) {
                window.location.reload();
            }
        }

        function bindAutoResizeTextareas() {
            document.querySelectorAll('textarea.js-auto-resize-textarea').forEach(function (textarea) {
                if (textarea.dataset.boundAutosize === '1') return;
                textarea.dataset.boundAutosize = '1';

                const resize = function () {
                    const maxHeight = 260;
                    textarea.style.height = 'auto';
                    const nextHeight = Math.min(textarea.scrollHeight, maxHeight);
                    textarea.style.height = nextHeight + 'px';
                    textarea.style.overflowY = textarea.scrollHeight > maxHeight ? 'auto' : 'hidden';
                };

                textarea.addEventListener('input', resize);
                resize();
            });

            document.querySelectorAll('.modal').forEach(function (modalEl) {
                if (modalEl.dataset.boundModalAutosize === '1') return;
                modalEl.dataset.boundModalAutosize = '1';

                modalEl.addEventListener('shown.bs.modal', function () {
                    modalEl.querySelectorAll('textarea.js-auto-resize-textarea').forEach(function (textarea) {
                        const maxHeight = 260;
                        textarea.style.height = 'auto';
                        const nextHeight = Math.min(textarea.scrollHeight, maxHeight);
                        textarea.style.height = nextHeight + 'px';
                        textarea.style.overflowY = textarea.scrollHeight > maxHeight ? 'auto' : 'hidden';
                    });
                });
            });
        }

        function initReorderableColumns() {
            const table = document.querySelector('.js-reorderable-table');
            if (!table) return;

            const headerRow = table.querySelector('thead tr');
            if (!headerRow) return;

            const orderStorageKey = 'staff.deposit.table.column.order.v1';
            const visibilityStorageKey = 'staff.deposit.table.column.visibility.v1';
            const headerCells = Array.from(headerRow.cells || []);
            if (headerCells.length < 2) return;

            const toggleSettingsBtn = document.getElementById('btnToggleColumnSettings');
            const settingsPanel = document.getElementById('columnSettingsPanel');
            const visibilityOptions = document.getElementById('columnVisibilityOptions');
            const resetColumnsBtn = document.getElementById('btnResetTableColumns');

            headerCells.forEach(function (th, index) {
                th.classList.add('js-col-draggable');
                th.setAttribute('draggable', 'true');
                if (!th.dataset.colId) {
                    th.dataset.colId = String(index);
                }
                th.title = 'Geser untuk pindah kolom';
            });

            function moveColumn(fromIndex, toIndex) {
                if (fromIndex === toIndex || fromIndex < 0 || toIndex < 0) return;
                const rows = Array.from(table.rows || []);
                rows.forEach(function (row) {
                    const cells = row.cells;
                    if (!cells || cells.length <= Math.max(fromIndex, toIndex)) return;

                    const movingCell = cells[fromIndex];
                    const targetCell = cells[toIndex];
                    if (!movingCell || !targetCell) return;

                    if (fromIndex < toIndex) {
                        row.insertBefore(movingCell, targetCell.nextSibling);
                    } else {
                        row.insertBefore(movingCell, targetCell);
                    }
                });
            }

            function getCurrentOrder() {
                return Array.from(headerRow.cells || []).map(function (th) {
                    return String(th.dataset.colId || '');
                });
            }

            function applyOrder(desiredOrder) {
                if (!Array.isArray(desiredOrder) || desiredOrder.length !== headerCells.length) return;

                const currentOrder = getCurrentOrder();
                desiredOrder.forEach(function (desiredId, targetIndex) {
                    const currentIndex = currentOrder.indexOf(String(desiredId));
                    if (currentIndex === -1 || currentIndex === targetIndex) return;

                    moveColumn(currentIndex, targetIndex);
                    const moved = currentOrder.splice(currentIndex, 1)[0];
                    currentOrder.splice(targetIndex, 0, moved);
                });
            }

            function getColumnIndexById(colId) {
                const currentHeaders = Array.from(headerRow.cells || []);
                return currentHeaders.findIndex(function (th) {
                    return String(th.dataset.colId || '') === String(colId);
                });
            }

            function setColumnVisibilityById(colId, isVisible) {
                const index = getColumnIndexById(colId);
                if (index < 0) return;

                Array.from(table.rows || []).forEach(function (row) {
                    if (!row.cells || !row.cells[index]) return;
                    row.cells[index].style.display = isVisible ? '' : 'none';
                });
            }

            function getVisibilityState() {
                const state = {};
                Array.from(headerRow.cells || []).forEach(function (th) {
                    const id = String(th.dataset.colId || '');
                    if (!id) return;
                    state[id] = th.style.display !== 'none';
                });
                return state;
            }

            function saveVisibility() {
                try {
                    localStorage.setItem(visibilityStorageKey, JSON.stringify(getVisibilityState()));
                } catch (error) {
                }
            }

            function renderColumnVisibilityOptions() {
                if (!visibilityOptions) return;
                visibilityOptions.innerHTML = '';

                Array.from(headerRow.cells || []).forEach(function (th) {
                    const colId = String(th.dataset.colId || '');
                    const labelText = (th.textContent || '').trim();
                    const checked = th.style.display !== 'none';

                    const wrap = document.createElement('label');
                    wrap.className = 'column-option-item d-inline-flex align-items-center gap-1';

                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input m-0';
                    checkbox.checked = checked;
                    checkbox.dataset.colId = colId;

                    checkbox.addEventListener('change', function () {
                        setColumnVisibilityById(this.dataset.colId, this.checked);
                        saveVisibility();
                    });

                    const text = document.createElement('span');
                    text.className = 'small';
                    text.textContent = labelText;

                    wrap.appendChild(checkbox);
                    wrap.appendChild(text);
                    visibilityOptions.appendChild(wrap);
                });
            }

            function saveOrder() {
                try {
                    localStorage.setItem(orderStorageKey, JSON.stringify(getCurrentOrder()));
                } catch (error) {
                }
            }

            function applySavedOrder() {
                try {
                    const raw = localStorage.getItem(orderStorageKey);
                    if (!raw) return;
                    const savedOrder = JSON.parse(raw);
                    applyOrder(savedOrder);
                } catch (error) {
                }
            }

            function applySavedVisibility() {
                try {
                    const raw = localStorage.getItem(visibilityStorageKey);
                    if (!raw) return;
                    const savedState = JSON.parse(raw);
                    if (!savedState || typeof savedState !== 'object') return;

                    Object.keys(savedState).forEach(function (colId) {
                        setColumnVisibilityById(colId, savedState[colId] !== false);
                    });
                } catch (error) {
                }
            }

            applySavedOrder();
            applySavedVisibility();
            renderColumnVisibilityOptions();

            if (toggleSettingsBtn && settingsPanel) {
                toggleSettingsBtn.addEventListener('click', function () {
                    settingsPanel.classList.toggle('d-none');
                });
            }

            if (resetColumnsBtn) {
                resetColumnsBtn.addEventListener('click', function () {
                    try {
                        localStorage.removeItem(orderStorageKey);
                        localStorage.removeItem(visibilityStorageKey);
                    } catch (error) {
                    }

                    const defaultOrder = headerCells
                        .map(function (th) { return String(th.dataset.colId || ''); })
                        .sort(function (a, b) { return Number(a) - Number(b); });

                    applyOrder(defaultOrder);

                    defaultOrder.forEach(function (colId) {
                        setColumnVisibilityById(colId, true);
                    });

                    renderColumnVisibilityOptions();
                });
            }

            let dragFromIndex = null;

            headerCells.forEach(function (th) {
                th.addEventListener('dragstart', function (event) {
                    dragFromIndex = this.cellIndex;
                    this.classList.add('js-col-dragging');
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', String(dragFromIndex));
                    }
                });

                th.addEventListener('dragover', function (event) {
                    event.preventDefault();
                    if (event.dataTransfer) {
                        event.dataTransfer.dropEffect = 'move';
                    }
                });

                th.addEventListener('drop', function (event) {
                    event.preventDefault();
                    if (dragFromIndex === null) return;

                    const dropIndex = this.cellIndex;
                    moveColumn(dragFromIndex, dropIndex);
                    saveOrder();
                    renderColumnVisibilityOptions();
                });

                th.addEventListener('dragend', function () {
                    dragFromIndex = null;
                    headerRow.querySelectorAll('th.js-col-dragging').forEach(function (draggingTh) {
                        draggingTh.classList.remove('js-col-dragging');
                    });
                });
            });
        }

        function initStaffReplyPenambahanControls() {
            function setPreview(targetId, file) {
                const wrap = document.querySelector('.js-staff-reply-preview-wrap[data-target="' + targetId + '"]');
                const img = document.querySelector('.js-staff-reply-preview[data-target="' + targetId + '"]');

                if (!wrap || !img || !file) return;

                const reader = new FileReader();
                reader.onload = function (event) {
                    img.src = event.target.result;
                    wrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            document.querySelectorAll('.js-staff-reply-type').forEach(function (select) {
                if (select.dataset.bound === '1') return;
                select.dataset.bound = '1';

                select.addEventListener('change', function () {
                    const targetId = this.dataset.target;
                    const textWrap = document.querySelector('.js-staff-reply-text-wrap[data-target="' + targetId + '"]');
                    const imageWrap = document.querySelector('.js-staff-reply-image-wrap[data-target="' + targetId + '"]');

                    if (!textWrap || !imageWrap) return;

                    textWrap.style.display = this.value === 'text' ? 'block' : 'none';
                    imageWrap.style.display = this.value === 'image' ? 'block' : 'none';
                });

                select.dispatchEvent(new Event('change'));
            });

            document.querySelectorAll('.js-staff-reply-image-input').forEach(function (input) {
                if (input.dataset.bound === '1') return;
                input.dataset.bound = '1';

                input.addEventListener('change', function () {
                    const targetId = this.dataset.target;
                    const file = this.files && this.files[0] ? this.files[0] : null;
                    if (file) setPreview(targetId, file);
                });
            });

            document.querySelectorAll('.js-staff-reply-paste-zone').forEach(function (zone) {
                if (zone.dataset.bound === '1') return;
                zone.dataset.bound = '1';

                zone.addEventListener('paste', function (event) {
                    const targetId = this.dataset.target;
                    const input = document.querySelector('.js-staff-reply-image-input[data-target="' + targetId + '"]');
                    if (!input) return;

                    const items = (event.clipboardData || window.clipboardData).items;
                    if (!items) return;

                    for (let i = 0; i < items.length; i++) {
                        if (items[i].type.indexOf('image') !== -1) {
                            const file = items[i].getAsFile();
                            if (!file) continue;

                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(file);
                            input.files = dataTransfer.files;

                            const typeSelect = document.querySelector('.js-staff-reply-type[data-target="' + targetId + '"]');
                            if (typeSelect) {
                                typeSelect.value = 'image';
                                typeSelect.dispatchEvent(new Event('change'));
                            }

                            setPreview(targetId, file);
                            event.preventDefault();
                            break;
                        }
                    }
                });
            });
        }

        if (replyTiketImageInput) {
            replyTiketImageInput.addEventListener('change', function () {
                const file = this.files && this.files[0] ? this.files[0] : null;
                if (file) setReplyTiketPreview(file);
            });
        }

        if (replyTiketPasteZone && replyTiketImageInput) {
            replyTiketPasteZone.addEventListener('paste', function (event) {
                const items = (event.clipboardData || window.clipboardData).items;
                if (!items) return;

                for (let i = 0; i < items.length; i++) {
                    if (items[i].type.indexOf('image') !== -1) {
                        const file = items[i].getAsFile();
                        if (!file) continue;

                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        replyTiketImageInput.files = dataTransfer.files;

                        setReplyTiketPreview(file);
                        event.preventDefault();
                        break;
                    }
                }
            });
        }

        if (enableNotifBtn) {
            enableNotifBtn.addEventListener('click', requestBrowserNotificationPermission);
        }

        if (hasValidationErrors) {
            const requestModalEl = document.getElementById('modalRequestDeposit');
            if (requestModalEl && window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(requestModalEl).show();
            }
        }

        if (staffRequestDepositForm) {
            staffRequestDepositForm.addEventListener('submit', function (event) {
                if (staffRequestDepositForm.dataset.submitting === '1') {
                    event.preventDefault();
                    return;
                }

                staffRequestDepositForm.dataset.submitting = '1';

                if (staffRequestDepositSubmitBtn) {
                    staffRequestDepositSubmitBtn.disabled = true;
                    staffRequestDepositSubmitBtn.textContent = 'Mengirim...';
                }
            });
        }

        updateNotifStatusText();
        initReorderableColumns();
        bindAutoResizeTextareas();
        initStaffReplyPenambahanControls();

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                checkChanges();
            }
        });

        setInterval(checkChanges, pollIntervalMs);

        // Auto parse Reply Tiket
        const parserScope = staffRequestDepositForm || document;
        const replyTiketInput = parserScope.querySelector('textarea[name="reply_tiket"]');
        const nominalInput = parserScope.querySelector('input[name="nominal"]');
        const bankTujuanInput = parserScope.querySelector('input[name="bank_tujuan"]');
        const noRekInput = parserScope.querySelector('input[name="no_rek"]');
        const namaRekeningInput = parserScope.querySelector('input[name="nama_rekening"]');
        const bankTujuanList = document.getElementById('bankTujuanRequestList');
        const noRekList = document.getElementById('noRekeningRequestList');
        const namaRekList = document.getElementById('namaRekeningRequestList');
        const bankTujuanParsedSelect = document.getElementById('bankTujuanParsedSelectRequest');
        const noRekParsedSelect = document.getElementById('noRekParsedSelectRequest');
        const namaRekParsedSelect = document.getElementById('namaRekParsedSelectRequest');
        let parsedBankRekPairs = [];

        function resetStaffRequestDepositFormFields() {
            if (!staffRequestDepositForm) return;

            staffRequestDepositForm.querySelectorAll('input, textarea, select').forEach(function (el) {
                if (el.type === 'hidden') return;
                if (el.type === 'file') {
                    el.value = '';
                    return;
                }
                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                    return;
                }
                el.value = '';
            });

            const jenisInput = staffRequestDepositForm.querySelector('input[name="jenis_transaksi"]');
            if (jenisInput) {
                jenisInput.value = 'deposit';
            }

            if (bankTujuanList) bankTujuanList.innerHTML = '';
            if (noRekList) noRekList.innerHTML = '';
            if (namaRekList) namaRekList.innerHTML = '';

            [bankTujuanParsedSelect, noRekParsedSelect, namaRekParsedSelect].forEach(function (selectEl) {
                if (!selectEl) return;
                selectEl.innerHTML = '';
                selectEl.classList.add('d-none');
            });

            parsedBankRekPairs = [];

            if (replyTiketPreview) {
                replyTiketPreview.src = '';
            }
            if (replyTiketPreviewWrap) {
                replyTiketPreviewWrap.style.display = 'none';
            }

            if (staffRequestDepositForm.dataset.submitting) {
                delete staffRequestDepositForm.dataset.submitting;
            }
            if (staffRequestDepositSubmitBtn) {
                staffRequestDepositSubmitBtn.disabled = false;
                staffRequestDepositSubmitBtn.textContent = 'Submit';
            }
        }

        if (staffRequestDepositResetBtn) {
            staffRequestDepositResetBtn.addEventListener('click', resetStaffRequestDepositFormFields);
        }

        function bindParsedSelect(selectEl, inputEl, options, placeholderLabel) {
            if (!selectEl || !inputEl) return;

            if (!Array.isArray(options) || options.length <= 1) {
                selectEl.innerHTML = '';
                selectEl.classList.add('d-none');
                return;
            }

            selectEl.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = placeholderLabel;
            placeholder.selected = true;
            selectEl.appendChild(placeholder);

            options.forEach(function (value) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                selectEl.appendChild(option);
            });

            selectEl.onchange = function () {
                if (this.value) {
                    inputEl.value = this.value;
                }
            };

            selectEl.classList.remove('d-none');
        }

        function syncNoRekFromBank(selectedBank) {
            const normalizedBank = String(selectedBank || '').toUpperCase().trim();
            if (!normalizedBank) return;

            const filteredNoreks = parsedBankRekPairs
                .filter(pair => pair.bank === normalizedBank)
                .map(pair => pair.norek)
                .filter((value, index, arr) => arr.indexOf(value) === index);

            if (filteredNoreks.length === 0) return;

            if (noRekList) {
                noRekList.innerHTML = '';
                filteredNoreks.forEach(num => {
                    const opt = document.createElement('option');
                    opt.value = num;
                    noRekList.appendChild(opt);
                });
            }

            bindParsedSelect(noRekParsedSelect, noRekInput, filteredNoreks, 'Pilih No Rekening hasil parse...');

            if (noRekInput) {
                noRekInput.value = filteredNoreks[0];
            }
        }

        if (bankTujuanInput) {
            bankTujuanInput.addEventListener('change', function () {
                syncNoRekFromBank(this.value);
            });
            bankTujuanInput.addEventListener('input', function () {
                syncNoRekFromBank(this.value);
            });
        }

        if (replyTiketInput) {
            replyTiketInput.addEventListener('input', function() {
                const text = this.value;
                if (!text) return;

                // Parse Nominal
                const nominalMatch =
                    text.match(/(?:Rp\s*\.?|IDR)\s*([0-9][\d\.,]{2,})/i) ||
                    text.match(/(?:(?:Nominal|Total|Jumlah|Transfer|Sebesar|Senilai|Bayar|Total Pembayaran)[^\d]*?)\s*([\d\.\,]{4,})/i) ||
                    text.match(/\b(\d{1,3}(?:\.\d{3}){1,3})\b/);
                if (nominalMatch && nominalInput) {
                    let nom = nominalMatch[1].replace(/[^0-9]/g, '');
                    if (nom && !nominalInput.value) {
                        nominalInput.value = nom;
                    }
                }

                        // Parse Nama Rekening Multiple (tangkap nama sesudah a.n. sampai tanda baca terdekat, nama bank, dsb.)
                            const namaRegex = /(?:a\/n|a\.n\.?|atas\s+nama\s*:?|\bAN\.?\b)\s*[:\-]?\s*(.*?)(?=\s*(?:BCA|BNI|BRI|MANDIRI|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN)\b|\s*no\s*rek\b|\s*rekening\b|\s*berita\b|\s*edc|\s*cntr|\s*\||\s*deposit\b|\s*harap\b|\s*konfirmasi\b|\s*batas\b|\s*transfer\b|\s*tiket\s*berlaku\b|\s*open\s*jam\b|\*transaksi\s*normal\*|\(|!+|\.\s*tiket|\.\s*$|,|\n|\r|$)/gi;
                        let parsedNamas = [];
                        let nMatch;
                        while ((nMatch = namaRegex.exec(text)) !== null) {
                            let extracted = nMatch[1]
                                .trim()
                                .replace(/^[\s:;,\-|()\[\]]+|[\s:;,\-|()\[\]]+$/g, '')
                                .replace(/\s{2,}/g, ' ');
                            if (extracted && extracted.length > 2 && !parsedNamas.includes(extracted)) {
                                parsedNamas.push(extracted);
                            }
                        }

                const anLineMatch = text.match(/\bAn\.?\s*:\s*([A-Za-z0-9 .,&'\-]{3,120}?)(?=\s*(?:\.\s*Tiket\b|Tiket\s*Berlaku\b|Open\s*Jam\b|\*TRANSAKSI\s*NORMAL\*|HARUSSAMA\b|$))/i);
                if (anLineMatch) {
                    const anName = anLineMatch[1]
                        .replace(/\s{2,}/g, ' ')
                        .replace(/[\s:;,\-|()\[\]]+$/g, '')
                        .trim();
                    if (anName && anName.length > 2 && !parsedNamas.includes(anName)) {
                        parsedNamas.unshift(anName);
                    }
                }

                if (parsedNamas.length > 0) {
                    if (namaRekeningInput && !namaRekeningInput.value) {
                        namaRekeningInput.value = parsedNamas[0];
                    }
                    if (namaRekList) {
                        namaRekList.innerHTML = '';
                        parsedNamas.forEach(ns => {
                            const opt = document.createElement('option');
                            opt.value = ns;
                            namaRekList.appendChild(opt);
                        });
                    }
                }
                bindParsedSelect(namaRekParsedSelect, namaRekeningInput, parsedNamas, 'Pilih Nama Rekening hasil parse...');

                // Parse Bank and Norek directly without limiting to "Ke Rek:"
                const bankRegexes = [
                    /(?:^|[\/\s])(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|OVO|DANA|GOPAY|SHOPEEPAY|LINKAJA|MUAMALAT|BTN|PANIN)\s*[:=]\s*([0-9][0-9\-\s]{4,30})/gi,
                    /\[(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|OVO|DANA|GOPAY|SHOPEEPAY|LINKAJA|MUAMALAT|BTN|PANIN)(?:\s+\d+)?[^\]]*\]\s*[-:=]?\s*([0-9][0-9\-\s]{4,30})/gi,
                    /\b(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|OVO|DANA|GOPAY|SHOPEEPAY|LINKAJA|MUAMALAT|BTN|PANIN)\b\s*(?:-|:|=)?\s*([0-9][0-9\-\s]{4,30})/gi,
                    /\b(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|OVO|DANA|GOPAY|SHOPEEPAY|LINKAJA|MUAMALAT|BTN|PANIN)\b\s*(?:-|:|=)\s*[A-Z]{2,20}\s*(?:-|:|=)\s*([0-9][0-9\-\s]{4,30})/gi,
                ];
                const banks = [];
                const noreks = [];
                const bankRekPairs = [];
                bankRegexes.forEach(function (bankRegex) {
                    let bMatch;
                    while ((bMatch = bankRegex.exec(text)) !== null) {
                        let b = bMatch[1].toUpperCase();
                        if (b === 'MNDR') b = 'MANDIRI';
                        let r = bMatch[2].replace(/[^0-9]/g, '');
                        if (r.length >= 5) {
                            if (!banks.includes(b)) banks.push(b);
                            if (!noreks.includes(r)) noreks.push(r);
                            const pairKey = b + '|' + r;
                            if (!bankRekPairs.some(pair => (pair.bank + '|' + pair.norek) === pairKey)) {
                                bankRekPairs.push({ bank: b, norek: r });
                            }
                        }
                    }
                });

                if (banks.length === 0) {
                    const bankOnlyRegex = /\b(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|MUAMALAT|BTN|PANIN)\b/gi;
                    let boMatch;
                    while ((boMatch = bankOnlyRegex.exec(text)) !== null) {
                        let b = boMatch[1].toUpperCase();
                        if (b === 'MNDR') b = 'MANDIRI';
                        if (!banks.includes(b)) banks.push(b);
                    }
                }

                if (noreks.length === 0) {
                    const noRekLabelMatch = text.match(/no\s*rek(?:ening)?\s*[:=\-]?\s*([0-9\-\s]{5,40})/i);
                    if (noRekLabelMatch) {
                        const noRekNumber = noRekLabelMatch[1].replace(/[^0-9]/g, '');
                        if (noRekNumber.length >= 5) {
                            noreks.push(noRekNumber);

                            const preferredPairBank = banks.includes('BCA')
                                ? 'BCA'
                                : (banks[0] || 'BCA');

                            if (!banks.includes(preferredPairBank)) {
                                banks.push(preferredPairBank);
                            }

                            bankRekPairs.push({
                                bank: preferredPairBank,
                                norek: noRekNumber,
                            });
                        }
                    }
                }

                if (noreks.length === 0) {
                    const vaMatch = text.match(/nomor\s*(?:virtual\s*account|va|rekening)\s*[:\-]?\s*([0-9]{8,40})/i);
                    if (vaMatch) {
                        const vaNumber = vaMatch[1].replace(/[^0-9]/g, '');
                        if (vaNumber.length >= 8) {
                            noreks.push(vaNumber);

                            const preferredPairBank = banks.includes('BCA')
                                ? 'BCA'
                                : (banks[0] || 'BCA');

                            if (!banks.includes(preferredPairBank)) {
                                banks.push(preferredPairBank);
                            }

                            bankRekPairs.push({
                                bank: preferredPairBank,
                                norek: vaNumber,
                            });
                        }
                    }
                }

                parsedBankRekPairs = bankRekPairs;

                if (banks.length > 0) {
                    if (bankTujuanList) {
                        bankTujuanList.innerHTML = '';
                        banks.forEach(bank => {
                            const opt = document.createElement('option');
                            opt.value = bank;
                            bankTujuanList.appendChild(opt);
                        });
                    }

                    if (noRekList) {
                        noRekList.innerHTML = '';
                        noreks.forEach(num => {
                            const opt = document.createElement('option');
                            opt.value = num;
                            noRekList.appendChild(opt);
                        });
                    }

                    const preferredBank = banks.includes('BCA') ? 'BCA' : banks[0];

                    if (bankTujuanInput) bankTujuanInput.value = preferredBank;
                    syncNoRekFromBank(preferredBank);
                }

                bindParsedSelect(bankTujuanParsedSelect, bankTujuanInput, banks, 'Pilih Bank Tujuan hasil parse...');

                if (bankTujuanParsedSelect && !bankTujuanParsedSelect.classList.contains('d-none')) {
                    const preferredBank = banks.includes('BCA') ? 'BCA' : banks[0];
                    bankTujuanParsedSelect.value = preferredBank || '';
                    bankTujuanParsedSelect.onchange = function () {
                        if (this.value) {
                            bankTujuanInput.value = this.value;
                            syncNoRekFromBank(this.value);
                        }
                    };
                }
            });
        }
    })();
</script>
@endpush

