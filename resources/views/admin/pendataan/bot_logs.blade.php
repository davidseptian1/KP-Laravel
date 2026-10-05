@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="mb-1 fw-bold text-dark d-flex align-items-center">
            <i class="ti ti-activity-heartbeat text-primary me-2"></i>Riwayat Logs Aktivitas Bot Telegram
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2 fs-6">@intel_awgbot</span>
        </h3>
        <p class="text-muted mb-0">Pantau aktivitas bot Telegram secara langsung: pesan masuk, hasil ekstraksi SMS, status pencocokan dengan data pendataan, dan balasan grup.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
        <form action="{{ route('admin.pendataan.bot-logs.sync') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-primary btn-sm shadow-sm" title="Sinkronkan ulang SMS yang belum cocok dengan data transaksi pending">
                <i class="ti ti-refresh me-1"></i> Sinkronkan Sekarang
            </button>
        </form>
        <a href="{{ url('superadmin/pendataan-access?tab=bot#cardBotTelegram') }}" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-settings me-1"></i> Pengaturan Bot
        </a>
        <a href="{{ url('pendataan') }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-notes me-1"></i> Data Pendataan
        </a>
        @if($totalLogs > 0)
            <form action="{{ route('admin.pendataan.bot-logs.clear') }}" method="POST" id="formClearLogs" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmClearLogs()">
                    <i class="ti ti-trash me-1"></i> Bersihkan Log
                </button>
            </form>
        @endif
    </div>
</div>

<!-- Tab Navigasi Utama -->
<ul class="nav nav-pills mb-4 p-1 bg-light rounded-3 d-inline-flex border">
    <li class="nav-item">
        <a class="nav-link py-2 px-4 fw-semibold {{ $activeTab !== 'webhook' ? 'active shadow-sm' : 'text-muted' }}" href="{{ route('admin.pendataan.bot-logs', ['tab' => 'transactions']) }}">
            <i class="ti ti-brand-telegram me-1"></i> Log Transaksi SMS Bot ({{ $totalLogs }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link py-2 px-4 fw-semibold {{ $activeTab === 'webhook' ? 'active shadow-sm' : 'text-muted' }}" href="{{ route('admin.pendataan.bot-logs', ['tab' => 'webhook']) }}">
            <i class="ti ti-server me-1"></i> Raw Webhook Logs ({{ $totalRawLogs }})
            <span class="badge {{ $totalRawLogs > 0 ? 'bg-success' : 'bg-secondary' }} bg-opacity-25 text-dark ms-1">{{ $totalRawLogs }}</span>
        </a>
    </li>
</ul>

@if($activeTab === 'webhook')
    <!-- Diagnostic Warning / Education Banner -->
    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
        <div class="d-flex align-items-start">
            <i class="ti ti-info-circle fs-2 text-info me-3 mt-1"></i>
            <div>
                <h6 class="fw-bold mb-1 text-dark">Diagnosa Masalah Webhook: Mengapa Pesan SMS di Grup Belum Tertangkap?</h6>
                <p class="mb-2 small text-dark">Halaman ini mencatat <strong>setiap panggilan HTTP / Webhook yang masuk ke server</strong> secara langsung dari Telegram maupun aplikasi SMS Forwarder HP.</p>
                <div class="row g-2 small">
                    <div class="col-md-6">
                        <div class="p-2 bg-white rounded border h-100">
                            <strong class="text-danger d-block mb-1"><i class="ti ti-alert-triangle me-1"></i>1. Pesan dikirim oleh BOT lain di dalam Grup</strong>
                            Telegram Bot API <u>secara resmi memblokir bot dari membaca pesan bot lain di dalam Grup</u> (untuk mencegah infinite loop antar bot). Server Telegram sama sekali tidak akan meneruskan pesan tersebut ke bot webhook kita.
                            <div class="mt-1 text-muted"><strong>Solusi:</strong> Gunakan Saluran (Channel) Telegram, atau gunakan opsi Direct Webhook di bawah.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-2 bg-white rounded border h-100">
                            <strong class="text-warning d-block mb-1"><i class="ti ti-shield me-1"></i>2. Bot Belum Jadi Admin / Sesi Grup Belum Ter-refresh</strong>
                            Meskipun <em>Group Privacy</em> sudah <code>Disabled</code> di @BotFather, Telegram membutuhkan bot <u>dijadikan ADMIN</u> di grup tersebut, atau di-kick lalu di-invite kembali agar izin membaca semua pesan aktif.
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-2 bg-success bg-opacity-10 text-success rounded border border-success-subtle">
                            <strong><i class="ti ti-bulb me-1"></i>Solusi Paling Cepat & 100% Pasti Masuk (Direct SMS Forwarder):</strong>
                            Di aplikasi SMS Forwarder di Android Anda, pilih metode kirim <strong>HTTP / Webhook POST</strong> langsung ke URL:
                            <code class="fw-bold text-dark px-2 py-1 bg-white rounded border ms-1">{{ url('/api/sms/pendataan-webhook') }}</code>
                            <span class="d-block small text-muted mt-1">Sistem akan langsung menerima SMS secara instan tanpa tergantung kendala bot Telegram!</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Webhook Raw Logs Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="ti ti-server me-2 text-primary"></i>Daftar Panggilan Webhook Masuk (Raw HTTP Payloads)
                </h6>
                <small class="text-muted">Total {{ $totalRawLogs }} rekaman panggilan diterima oleh server</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <form action="{{ route('admin.pendataan.bot-logs.test-ping') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary shadow-sm" title="Kirim simulasi ping untuk memastikan logger aktif">
                        <i class="ti ti-flame me-1"></i>Tes Simulasi Webhook
                    </button>
                </form>
                @if($totalRawLogs > 0)
                    <form action="{{ route('admin.pendataan.bot-logs.clear-raw') }}" method="POST" id="formClearRawLogs" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger shadow-sm" onclick="confirmClearRawLogs()">
                            <i class="ti ti-trash me-1"></i>Bersihkan Raw Log
                        </button>
                    </form>
                @endif
                <a href="{{ route('admin.pendataan.bot-logs', ['tab' => 'webhook']) }}" class="btn btn-sm btn-outline-secondary" title="Segarkan Halaman">
                    <i class="ti ti-refresh"></i>
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @include('admin.pendataan.partials.raw_webhook_logs_table', ['rawLogs' => $rawLogs])
        </div>
    </div>
@else
    <!-- Quick Statistics -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                        <i class="ti ti-brand-telegram fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Pesan Diterima</h6>
                        <h3 class="mb-0 fw-bold">{{ $totalLogs }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                        <i class="ti ti-circle-check fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Berhasil Cocok ✅</h6>
                        <h3 class="mb-0 fw-bold text-success">{{ $matchedLogs }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning me-3">
                        <i class="ti ti-clock fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Belum Ada Cocok ⏳</h6>
                        <h3 class="mb-0 fw-bold text-warning">{{ $unmatchedLogs }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 text-secondary me-3">
                        <i class="ti ti-message-dots fs-2"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Bukan SMS Valid ⚠️</h6>
                        <h3 class="mb-0 fw-bold text-secondary">{{ $invalidFormatLogs }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <!-- Left: Filter Tabs -->
                <ul class="nav nav-pills flex-nowrap" id="statusFilterTabs" style="overflow-x: auto;">
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ $currentStatus === 'all' ? 'active' : '' }}" href="{{ route('admin.pendataan.bot-logs', ['status' => 'all', 'search' => $currentSearch, 'tab' => 'transactions']) }}">
                            Semua Status ({{ $totalLogs }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ $currentStatus === 'matched' ? 'active' : '' }}" href="{{ route('admin.pendataan.bot-logs', ['status' => 'matched', 'search' => $currentSearch, 'tab' => 'transactions']) }}">
                            <i class="ti ti-check me-1"></i>Cocok ({{ $matchedLogs }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ $currentStatus === 'unmatched' ? 'active' : '' }}" href="{{ route('admin.pendataan.bot-logs', ['status' => 'unmatched', 'search' => $currentSearch, 'tab' => 'transactions']) }}">
                            <i class="ti ti-hourglass me-1"></i>Belum Cocok ({{ $unmatchedLogs }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3 {{ $currentStatus === 'invalid_format' ? 'active' : '' }}" href="{{ route('admin.pendataan.bot-logs', ['status' => 'invalid_format', 'search' => $currentSearch, 'tab' => 'transactions']) }}">
                            <i class="ti ti-info-circle me-1"></i>Bukan SMS ({{ $invalidFormatLogs }})
                        </a>
                    </li>
                </ul>

                <!-- Right: Live refresh controls & search -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="form-check form-switch mb-0" title="Muat ulang tabel otomatis setiap 5 detik">
                        <input class="form-check-input" type="checkbox" id="autoRefreshSwitch">
                        <label class="form-check-label small text-muted" for="autoRefreshSwitch">
                            <span id="liveBadge" class="badge bg-success bg-opacity-10 text-success d-none me-1"><i class="ti ti-point-filled"></i> LIVE</span> Auto Refresh (5s)
                        </label>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshLogsTable()" id="btnManualRefresh" title="Segarkan Data Sekarang">
                        <i class="ti ti-refresh" id="refreshIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mt-3">
                <form method="GET" action="{{ route('admin.pendataan.bot-logs') }}" class="d-flex gap-2">
                    <input type="hidden" name="tab" value="transactions">
                    <input type="hidden" name="status" value="{{ $currentStatus }}">
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="ti ti-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari isi pesan SMS, nama produk, grup Telegram, pengirim, atau catatan bot..." value="{{ $currentSearch }}">
                        @if(!empty($currentSearch))
                            <a href="{{ route('admin.pendataan.bot-logs', ['status' => $currentStatus, 'tab' => 'transactions']) }}" class="btn btn-outline-secondary" title="Hapus Pencarian">
                                <i class="ti ti-x"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3">Cari</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Container -->
        <div id="logsTableContainer">
            @include('admin.pendataan.partials.bot_logs_table', ['logs' => $logs])
        </div>
    </div>
@endif

<!-- Modal: Detail Log Bot Telegram -->
<div class="modal fade" id="modalDetailBotLog" tabindex="-1" aria-labelledby="modalDetailBotLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="modalDetailBotLogLabel">
                    <i class="ti ti-receipt-2 text-primary me-2 fs-4"></i>Rincian Log Aktivitas Bot Telegram #<span id="modalLogId">-</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Status & Timing -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold mb-1">STATUS PEMROSESAN</label>
                        <div id="modalStatusBadge">-</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-semibold mb-1">WAKTU DITERIMA</label>
                        <div class="fw-bold text-dark" id="modalCreatedAt">-</div>
                    </div>
                </div>

                <!-- Chat & Sender Info -->
                <div class="row g-3 mb-3 p-3 bg-light rounded-3 border">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Grup / Obrolan Telegram:</small>
                        <strong class="text-dark" id="modalChatTitle">-</strong>
                        <div class="small text-muted font-monospace" id="modalChatId">-</div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Pengirim Pesan:</small>
                        <strong class="text-dark" id="modalSenderName">-</strong>
                        <span class="badge bg-info bg-opacity-10 text-info font-monospace ms-1" id="modalSenderUsername">-</span>
                    </div>
                </div>

                <!-- Extracted Product & Nominal -->
                <div class="p-3 mb-3 rounded-3 border border-success-subtle bg-success bg-opacity-10" id="sectionExtractedData">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <small class="text-success fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Hasil Ekstraksi Nama Produk:</small>
                            <h5 class="fw-bold text-dark mb-0" id="modalParsedProduct">-</h5>
                        </div>
                        <div class="text-end">
                            <small class="text-success fw-bold text-uppercase d-block" style="font-size: 0.72rem;">Nominal Terdeteksi:</small>
                            <h4 class="fw-bold text-success mb-0" id="modalParsedNominal">-</h4>
                        </div>
                    </div>
                </div>

                <!-- Matched Pendataan Record -->
                <div class="p-3 mb-3 rounded-3 border border-primary-subtle bg-primary bg-opacity-10 d-none" id="sectionMatchedRecord">
                    <small class="text-primary fw-bold text-uppercase d-block mb-1" style="font-size: 0.72rem;">
                        <i class="ti ti-link me-1"></i>Data Pendataan yang Berhasil Dicocokkan:
                    </small>
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="badge bg-primary text-white me-2">ID #<span id="modalPendataanId">-</span></span>
                            <strong class="text-dark" id="modalPendataanProduct">-</strong>
                            <small class="text-muted d-block mt-1">Petugas Staf: <span id="modalPendataanNama" class="fw-semibold">-</span></small>
                        </div>
                        <div class="text-end">
                            <span class="fw-bold text-primary" id="modalPendataanNominal">-</span>
                            <span class="badge bg-success ms-2">Sukses ✅</span>
                        </div>
                    </div>
                </div>

                <!-- Action Note -->
                <div class="mb-3">
                    <label class="form-label text-muted small fw-semibold mb-1">CATATAN & DETAIL AKTIVITAS BOT</label>
                    <div class="p-3 bg-light rounded-3 border text-secondary" id="modalActionNote" style="line-height: 1.5;">-</div>
                </div>

                <!-- Bot Reply -->
                <div class="mb-3 d-none" id="sectionBotReply">
                    <label class="form-label text-muted small fw-semibold mb-1">BALASAN DI GRUP TELEGRAM</label>
                    <pre class="bg-dark text-white p-3 rounded-3 small mb-0 font-monospace" id="modalBotReply" style="white-space: pre-wrap; word-break: break-word;"></pre>
                </div>

                <!-- Raw Message -->
                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label text-muted small fw-semibold mb-0">TEKS ASLI PESAN TELEGRAM (RAW)</label>
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2" onclick="copyRawMessage()">
                            <i class="ti ti-copy me-1"></i>Salin Teks
                        </button>
                    </div>
                    <pre class="bg-light p-3 rounded-3 border small text-dark mb-0 font-monospace" id="modalRawMessage" style="white-space: pre-wrap; word-break: break-word; max-height: 180px; overflow-y: auto;"></pre>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Detail Raw Webhook Payload -->
<div class="modal fade" id="modalDetailRawWebhook" tabindex="-1" aria-labelledby="modalDetailRawWebhookLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center text-white" id="modalDetailRawWebhookLabel">
                    <i class="ti ti-code text-warning me-2 fs-4"></i>Raw Webhook Payload #<span id="modalRawId">-</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Meta Info -->
                <div class="row g-2 mb-3 p-3 bg-light rounded-3 border small">
                    <div class="col-md-3"><strong>Sumber:</strong> <span id="modalRawSource" class="text-primary">-</span></div>
                    <div class="col-md-3"><strong>IP Address:</strong> <span id="modalRawIp" class="font-monospace">-</span></div>
                    <div class="col-md-3"><strong>Update Type:</strong> <span id="modalRawType" class="badge bg-secondary">-</span></div>
                    <div class="col-md-3"><strong>Status:</strong> <span id="modalRawStatusBadge">-</span></div>
                    <div class="col-md-6 mt-2"><strong>Chat/Grup:</strong> <span id="modalRawChat">-</span></div>
                    <div class="col-md-6 mt-2"><strong>Waktu:</strong> <span id="modalRawTime">-</span></div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-muted text-uppercase mb-1">Catatan / Analisa Sistem:</label>
                    <div class="alert alert-secondary py-2 px-3 small mb-0" id="modalRawNotes">-</div>
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label small fw-semibold text-muted text-uppercase mb-0">Payload JSON Lengkap:</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="copyRawJsonPayload()">
                            <i class="ti ti-copy me-1"></i>Salin JSON
                        </button>
                    </div>
                    <pre class="bg-dark text-success p-3 rounded-3 font-monospace small mb-0" style="max-height: 380px; overflow-y: auto; white-space: pre-wrap; word-break: break-all;" id="modalRawPayloadContent"></pre>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let autoRefreshTimer = null;

function refreshLogsTable() {
    const icon = document.getElementById('refreshIcon');
    if (icon) icon.classList.add('rotate-spin');

    const currentUrl = new URL(window.location.href);

    fetch(currentUrl.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(r => r.text())
    .then(html => {
        document.getElementById('logsTableContainer').innerHTML = html;
        if (icon) icon.classList.remove('rotate-spin');
    })
    .catch(err => {
        console.error('Error refreshing logs:', err);
        if (icon) icon.classList.remove('rotate-spin');
    });
}

// Auto-refresh switcher
document.getElementById('autoRefreshSwitch').addEventListener('change', function() {
    const badge = document.getElementById('liveBadge');
    if (this.checked) {
        badge.classList.remove('d-none');
        refreshLogsTable();
        autoRefreshTimer = setInterval(refreshLogsTable, 5000);
    } else {
        badge.classList.add('d-none');
        if (autoRefreshTimer) clearInterval(autoRefreshTimer);
    }
});

// Show Log Detail Modal
function showLogDetail(logId) {
    Swal.fire({
        title: 'Memuat Rincian Log...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch("{{ url('superadmin/pendataan-bot-logs') }}/" + logId)
    .then(r => r.json())
    .then(res => {
        Swal.close();
        if (!res.success) {
            Swal.fire('Gagal', 'Data log tidak ditemukan.', 'error');
            return;
        }

        const d = res.data;
        document.getElementById('modalLogId').textContent = d.id;
        document.getElementById('modalStatusBadge').innerHTML = d.status_badge_html;
        document.getElementById('modalCreatedAt').textContent = d.created_at;
        document.getElementById('modalChatTitle').textContent = d.chat_title;
        document.getElementById('modalChatId').textContent = 'Chat ID: ' + (d.chat_id || '-');
        document.getElementById('modalSenderName').textContent = d.sender_name;
        document.getElementById('modalSenderUsername').textContent = d.sender_username;
        document.getElementById('modalRawMessage').textContent = d.raw_message || '-';
        document.getElementById('modalActionNote').textContent = d.action_note || '-';

        // Extracted data
        if (d.parsed_product !== '-' || d.parsed_nominal !== '-') {
            document.getElementById('modalParsedProduct').textContent = d.parsed_product;
            document.getElementById('modalParsedNominal').textContent = d.parsed_nominal;
            document.getElementById('sectionExtractedData').classList.remove('d-none');
        } else {
            document.getElementById('sectionExtractedData').classList.add('d-none');
        }

        // Matched record
        if (d.pendataan_id) {
            document.getElementById('modalPendataanId').textContent = d.pendataan_id;
            document.getElementById('modalPendataanProduct').textContent = d.pendataan_product || '-';
            document.getElementById('modalPendataanNominal').textContent = d.pendataan_nominal || '-';
            document.getElementById('modalPendataanNama').textContent = d.pendataan_nama || '-';
            document.getElementById('sectionMatchedRecord').classList.remove('d-none');
        } else {
            document.getElementById('sectionMatchedRecord').classList.add('d-none');
        }

        // Bot reply
        if (d.bot_replied && d.bot_reply_text) {
            document.getElementById('modalBotReply').textContent = d.bot_reply_text;
            document.getElementById('sectionBotReply').classList.remove('d-none');
        } else {
            document.getElementById('sectionBotReply').classList.add('d-none');
        }

        const modal = new bootstrap.Modal(document.getElementById('modalDetailBotLog'));
        modal.show();
    })
    .catch(err => {
        Swal.fire('Error', 'Gagal memuat rincian log: ' + err.message, 'error');
    });
}

function copyRawMessage() {
    const text = document.getElementById('modalRawMessage').textContent;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Tersalin!',
            text: 'Teks asli pesan berhasil disalin ke clipboard.',
            timer: 1500,
            showConfirmButton: false
        });
    });
}

function confirmClearLogs() {
    Swal.fire({
        title: 'Hapus Semua Log Bot?',
        text: 'Riwayat aktivitas bot Telegram akan dibersihkan secara permanen. Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Bersihkan Log!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formClearLogs').submit();
        }
    });
}

function showRawWebhookDetail(id) {
    Swal.fire({
        title: 'Memuat Payload Webhook...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch("{{ url('superadmin/pendataan-bot-logs/raw') }}/" + id)
    .then(r => r.json())
    .then(res => {
        Swal.close();
        if (!res.success) {
            Swal.fire('Gagal', res.message || 'Data raw log tidak ditemukan.', 'error');
            return;
        }

        const d = res.data;
        document.getElementById('modalRawId').textContent = d.id;
        document.getElementById('modalRawSource').textContent = d.source;
        document.getElementById('modalRawIp').textContent = d.ip_address;
        document.getElementById('modalRawType').textContent = d.update_type;
        document.getElementById('modalRawChat').textContent = d.chat_title + (d.chat_id !== '-' ? ' (' + d.chat_id + ')' : '');
        document.getElementById('modalRawSender').textContent = d.sender_name;
        document.getElementById('modalRawTime').textContent = d.created_at;
        document.getElementById('modalRawStatusBadge').innerHTML = d.status_badge_html;
        document.getElementById('modalRawNotes').textContent = d.notes;
        document.getElementById('modalRawPayloadContent').textContent = d.raw_payload;

        const modal = new bootstrap.Modal(document.getElementById('modalDetailRawWebhook'));
        modal.show();
    })
    .catch(err => {
        Swal.fire('Error', 'Gagal memuat rincian raw webhook: ' + err.message, 'error');
    });
}

function copyRawJsonPayload() {
    const text = document.getElementById('modalRawPayloadContent').textContent;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire({
            icon: 'success',
            title: 'Tersalin!',
            text: 'Payload JSON mentah berhasil disalin ke clipboard.',
            timer: 1500,
            showConfirmButton: false
        });
    });
}

function confirmClearRawLogs() {
    Swal.fire({
        title: 'Bersihkan Raw Webhook Logs?',
        text: 'Semua rekaman raw HTTP payloads webhook akan dihapus secara permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Bersihkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('formClearRawLogs').submit();
        }
    });
}
</script>
<style>
.rotate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
</style>
@endpush
@endsection
