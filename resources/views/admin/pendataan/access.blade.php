@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 fw-bold text-dark"><i class="ti ti-shield-lock text-primary me-2"></i>Pengaturan Akses Fitur Pendataan</h3>
        <p class="text-muted mb-0">Kelola dan tentukan akun staf yang diizinkan untuk melihat dan menggunakan fitur Pendataan di dashboard mereka.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ url('pendataan') }}" class="btn btn-outline-primary">
            <i class="ti ti-notes me-1"></i> Buka Fitur Pendataan
        </a>
    </div>
</div>

<!-- Quick Statistics -->
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="ti ti-users fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Akun Staff</h6>
                    <h3 class="mb-0 fw-bold">{{ $totalStaff }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="ti ti-user-check fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Staff Dengan Akses</h6>
                    <h3 class="mb-0 fw-bold text-success">{{ $staffWithAccess }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-secondary bg-opacity-10 p-3 text-secondary me-3">
                    <i class="ti ti-user-x fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Staff Tanpa Akses</h6>
                    <h3 class="mb-0 fw-bold text-secondary">{{ $staffWithoutAccess }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info border-0 shadow-sm d-flex align-items-start mb-4 rounded-3">
    <i class="ti ti-info-circle fs-3 me-3 text-info mt-1"></i>
    <div>
        <strong class="d-block mb-1">Informasi Hak Akses Fitur Pendataan:</strong>
        <span class="text-secondary">
            • <strong>Superadmin</strong> secara otomatis memiliki hak akses penuh ke fitur Pendataan.<br>
            • Untuk akun <strong>Staff</strong>, menu di sidebar hanya akan muncul jika saklar akses diaktifkan di bawah ini.<br>
            • Ketika staf membuka fitur Pendataan, staf harus memasukkan <strong>Username</strong> dan <strong>Password (5 huruf)</strong> miliknya terlebih dahulu. Nama penanggung jawab transaksi akan otomatis terkunci sesuai akun staf tersebut.
        </span>
    </div>
</div>

<!-- Card: Konfigurasi Bot Telegram Pendataan -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                <i class="ti ti-brand-telegram text-primary fs-3 me-2"></i>Konfigurasi Bot Telegram Pendataan
                <span class="badge bg-primary bg-opacity-10 text-primary ms-2 fs-6">@intel_awgbot</span>
            </h5>
            <small class="text-muted">Atur parameter scraping bot, URL webhook, dan jumlah pesan riwayat yang dicocokkan otomatis</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="https://t.me/{{ $botUsername }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-external-link me-1"></i>Buka Bot di Telegram
            </a>
        </div>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('admin.pendataan.bot-settings') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        <i class="ti ti-key text-warning me-1"></i>Telegram Bot Token
                    </label>
                    <div class="input-group">
                        <input type="text" name="bot_token" class="form-control font-monospace" value="{{ $botToken }}" placeholder="Token HTTP API dari BotFather" required>
                        <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText('{{ $botToken }}'); Swal.fire({icon:'success', title:'Tersalin!', timer:1500, showConfirmButton:false});" title="Salin Token">
                            <i class="ti ti-copy"></i>
                        </button>
                    </div>
                    <div class="form-text text-muted small">Token resmi bot <code>@intel_awgbot</code> untuk integrasi webhook.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        <i class="ti ti-list-check text-success me-1"></i>Jumlah Pesan Riwayat yang Dicek (Scraping Limit)
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="ti ti-history"></i></span>
                        <input type="number" name="check_limit" class="form-control fw-bold" value="{{ $checkLimit }}" min="1" max="50" required>
                        <span class="input-group-text bg-light">Pesan Terbaru</span>
                    </div>
                    <div class="form-text text-muted small">Berapa banyak data riwayat berstatus <code>pending</code> terbaru yang akan dicari kecocokannya saat SMS masuk (Default: 5).</div>
                </div>

                <div class="col-12">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                            <label class="form-label fw-semibold text-dark mb-0">
                                <i class="ti ti-link text-info me-1"></i>Endpoint URL Webhook Telegram
                            </label>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-xs btn-primary py-1 px-2" onclick="setWebhookTelegram()">
                                    <i class="ti ti-plug-connected me-1"></i>Daftarkan Webhook
                                </button>
                                <button type="button" class="btn btn-xs btn-outline-info py-1 px-2" onclick="checkWebhookTelegram()">
                                    <i class="ti ti-info-circle me-1"></i>Cek Status Webhook
                                </button>
                            </div>
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" id="webhookUrlInput" class="form-control font-monospace bg-white" value="{{ $webhookUrl }}" readonly>
                            <button type="button" class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(document.getElementById('webhookUrlInput').value); Swal.fire({icon:'success', title:'URL Webhook Tersalin!', timer:1500, showConfirmButton:false});">
                                <i class="ti ti-copy me-1"></i>Salin URL
                            </button>
                        </div>
                        <div class="mt-2 small text-muted">
                            <i class="ti ti-alert-triangle text-warning me-1"></i><strong>Penting untuk Bot Grup:</strong> Buka <strong>@BotFather</strong> &rarr; ketik <code>/mybots</code> &rarr; pilih <code>@intel_awgbot</code> &rarr; <strong>Bot Settings</strong> &rarr; <strong>Group Privacy</strong> &rarr; <strong>Turn OFF</strong> (agar bot bisa membaca SMS di dalam grup).
                        </div>
                    </div>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Pengaturan Bot
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function setWebhookTelegram() {
    Swal.fire({
        title: 'Mendaftarkan Webhook...',
        text: 'Mengirim perintah setWebhook ke server Telegram',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    fetch("{{ url('/api/telegram/pendataan/setup-webhook') }}", {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ url: document.getElementById('webhookUrlInput').value })
    })
    .then(r => r.json())
    .then(res => {
        if (res.status === 'success') {
            Swal.fire('Berhasil!', 'Webhook Telegram berhasil dihubungkan ke aplikasi Laravel!', 'success');
        } else {
            Swal.fire('Info Webhook', JSON.stringify(res.telegram_response || res), 'info');
        }
    })
    .catch(err => {
        Swal.fire('Gagal', 'Terjadi kesalahan saat menghubungi API: ' + err.message, 'error');
    });
}

function checkWebhookTelegram() {
    fetch("{{ url('/api/telegram/pendataan/webhook-info') }}")
    .then(r => r.json())
    .then(res => {
        Swal.fire({
            title: 'Status Webhook Telegram',
            html: '<pre class="text-start bg-light p-3 rounded" style="max-height: 250px; overflow-y:auto; font-size:12px;">' + JSON.stringify(res, null, 2) + '</pre>',
            confirmButtonText: 'Tutup'
        });
    })
    .catch(err => Swal.fire('Error', err.message, 'error'));
}
</script>

<!-- Card: Akun Staf Pendataan & Password (5 Huruf) -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                <i class="ti ti-key text-warning me-2"></i>Daftar Akun & Password Staf Pendataan
            </h5>
            <small class="text-muted">Kredensial username dan password untuk staf membuka kunci sesi shift fitur Pendataan</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border px-3 py-2 fw-semibold">
                <i class="ti ti-users me-1"></i>{{ $staffAccounts->count() }} Akun Staf
            </span>
            <button type="button" class="btn btn-sm btn-primary shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambahStaff">
                <i class="ti ti-user-plus me-1"></i> Tambah User Pendataan
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="p-3 bg-light border-bottom small text-muted">
            <i class="ti ti-info-circle me-1 text-primary"></i>
            Berikan Username dan Password 5 huruf berikut ke masing-masing staf. Saat staf membuka fitur Pendataan, nama transaksi akan otomatis terkunci sesuai akun staf yang sedang membuka.
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Nama Staf</th>
                        <th>Username</th>
                        <th>Password (5 Huruf)</th>
                        <th class="text-center" style="width: 120px;">Status</th>
                        <th class="text-center pe-4" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffAccounts as $idx => $st)
                    <tr>
                        <td class="ps-4 text-muted fw-semibold">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="text-dark">{{ $st->nama }}</strong>
                        </td>
                        <td>
                            <code class="bg-light px-2 py-1 rounded text-primary fw-bold">{{ $st->username }}</code>
                        </td>
                        <td>
                            <div class="d-inline-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark font-monospace border px-3 py-1 fs-6">
                                    {{ $st->password }}
                                </span>
                                <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2" title="Salin password" onclick="copyStaffPass('{{ $st->password }}', '{{ $st->nama }}')">
                                    <i class="ti ti-copy"></i>
                                </button>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-switch d-inline-block">
                                <input class="form-check-input"
                                       type="checkbox"
                                       role="switch"
                                       id="staff-switch-{{ $st->id }}"
                                       {{ $st->is_active ? 'checked' : '' }}
                                       onchange="toggleStaffActive({{ $st->id }}, this)"
                                       style="width: 2.5em; height: 1.3em; cursor: pointer;"
                                       title="Klik untuk aktifkan / nonaktifkan">
                            </div>
                        </td>
                        <td class="text-center pe-4 text-nowrap">
                            <button type="button" class="btn btn-sm btn-outline-warning" title="Edit Akun Staf" onclick="openEditStaffModal({{ $st->id }}, '{{ addslashes($st->nama) }}', '{{ addslashes($st->username) }}', '{{ addslashes($st->password) }}', {{ $st->is_active ? 1 : 0 }})">
                                <i class="ti ti-edit me-1"></i>Edit
                            </button>
                            <form method="POST" action="{{ route('admin.pendataan.staff.destroy', $st->id) }}" class="d-inline" onsubmit="return confirm('Hapus akun staf {{ addslashes($st->nama) }} dari login fitur Pendataan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger ms-1" title="Hapus Akun Staf">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Belum ada akun staf pendataan yang ditambahkan. Silakan klik tombol <strong>Tambah User Pendataan</strong>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= MODAL TAMBAH USER PENDATAAN ================= -->
<div class="modal fade" id="modalTambahStaff" tabindex="-1" aria-labelledby="modalTambahStaffLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.pendataan.staff.store') }}" id="formTambahStaff">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahStaffLabel">
                        <i class="ti ti-user-plus text-primary me-2"></i>Tambah User Login Fitur Pendataan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Quick Select from Users who have access -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">
                            Pilih dari Akun Pengguna Yang Diberikan Akses <span class="text-muted small fw-normal">(Opsional)</span>
                        </label>
                        <select class="form-select" id="selectUserForStaff" onchange="onSelectUserForStaff(this)">
                            <option value="">-- Ketik Nama Baru Secara Manual --</option>
                            @if(isset($usersWithAccess) && $usersWithAccess->count() > 0)
                                <optgroup label="Akun Yang Sudah Memiliki Akses Pendataan">
                                    @foreach($usersWithAccess->where('has_pendataan_access', true) as $uw)
                                        <option value="{{ $uw->id }}" data-nama="{{ $uw->nama }}" data-email="{{ $uw->email }}">
                                            {{ $uw->nama }} ({{ $uw->email }}) - Diberi Akses
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Akun Staf Lainnya">
                                    @foreach($usersWithAccess->where('has_pendataan_access', false) as $uo)
                                        <option value="{{ $uo->id }}" data-nama="{{ $uo->nama }}" data-email="{{ $uo->email }}">
                                            {{ $uo->nama }} ({{ $uo->email }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        <input type="hidden" name="user_id" id="tambah_staff_user_id" value="">
                        <div class="form-text text-muted small">
                            Pilih akun staf yang sudah ada untuk mengisi nama & username secara otomatis, atau pilih ketik manual.
                        </div>
                    </div>

                    <!-- Nama Staf -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Staf (Penanggung Jawab) <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="tambah_staff_nama" class="form-control" required placeholder="Contoh: Rudi" oninput="autoSuggestUsername(this.value)">
                        <div class="form-text text-muted small">Nama ini yang akan tercatat pada transaksi staf (misal: Rudi ( Shift 1 )).</div>
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Username Login <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="ti ti-at"></i></span>
                            <input type="text" name="username" id="tambah_staff_username" class="form-control font-monospace" required placeholder="Contoh: rudi">
                        </div>
                        <div class="form-text text-muted small">Digunakan staf saat memasukkan username pada layar unlock (huruf kecil & angka).</div>
                    </div>

                    <!-- Password 5 Huruf -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Password Login (5 Huruf) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="password" id="tambah_staff_password" class="form-control font-monospace form-control-lg" maxlength="20" required placeholder="Contoh: abcde">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPass('tambah_staff_password')" title="Buat password 5 huruf acak">
                                <i class="ti ti-dice me-1"></i>Acak 5 Huruf
                            </button>
                        </div>
                        <div class="form-text text-muted small">Kombinasi 5 huruf untuk verifikasi staf saat buka fitur.</div>
                    </div>

                    <!-- Status Aktif -->
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="tambah_staff_is_active" value="1" checked>
                        <label class="form-check-label fw-semibold text-dark" for="tambah_staff_is_active">
                            Aktifkan akun segera
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Akun Staf
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL EDIT USER PENDATAAN ================= -->
<div class="modal fade" id="modalEditStaff" tabindex="-1" aria-labelledby="modalEditStaffLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" id="formEditStaff" action="">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditStaffLabel">
                        <i class="ti ti-edit text-warning me-2"></i>Edit Akun Staf Pendataan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Staf <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="edit_staff_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Username <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="ti ti-at"></i></span>
                            <input type="text" name="username" id="edit_staff_username" class="form-control font-monospace" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Password (5 Huruf) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="password" id="edit_staff_password" class="form-control form-control-lg font-monospace" maxlength="20" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPass('edit_staff_password')" title="Acak 5 huruf">
                                <i class="ti ti-dice me-1"></i>Acak
                            </button>
                        </div>
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="edit_staff_is_active" value="1">
                        <label class="form-check-label fw-semibold text-dark" for="edit_staff_is_active">
                            Status Akun Aktif
                        </label>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                        <i class="ti ti-device-floppy me-1"></i> Perbarui Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
            <i class="ti ti-list-check me-2 text-primary"></i>Hak Akses Menu Dashboard Akun Pengguna
        </h5>
        <form method="GET" action="{{ route('admin.pendataan.access') }}" class="d-flex align-items-center gap-2">
            <select name="role" class="form-select form-select-sm" style="min-width: 140px;" onchange="this.form.submit()">
                <option value="Staff" {{ $currentRole === 'Staff' ? 'selected' : '' }}>Hanya Staff</option>
                <option value="all" {{ $currentRole === 'all' ? 'selected' : '' }}>Semua Pengguna</option>
            </select>
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control" placeholder="Cari nama/email..." value="{{ $currentSearch }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i></button>
            </div>
            @if($currentSearch || $currentRole !== 'Staff')
                <a href="{{ route('admin.pendataan.access') }}" class="btn btn-sm btn-light border text-muted" title="Reset filter">
                    <i class="ti ti-refresh"></i>
                </a>
            @endif
        </form>
    </div>

    <form method="POST" action="{{ route('admin.pendataan.access.batch') }}" id="batchAccessForm">
        @csrf
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 60px;">No</th>
                            <th>Nama Pengguna</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th class="text-center" style="width: 180px;">Status Akses</th>
                            <th class="text-center pe-4" style="width: 180px;">Aksi Saklar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        @php
                            $matchedStaff = $staffAccounts->first(function($st) use ($u) {
                                return strcasecmp(trim($st->nama), trim($u->nama)) === 0 || strcasecmp(trim($st->username), strtolower(explode(' ', trim($u->nama))[0])) === 0;
                            });
                        @endphp
                        <tr id="row-user-{{ $u->id }}">
                            <td class="ps-4 text-muted fw-semibold">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light text-primary fw-bold d-flex align-items-center justify-content-center me-3 border" style="width: 38px; height: 38px; font-size: 1rem;">
                                        {{ strtoupper(substr($u->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $u->nama }}</div>
                                        <small class="text-muted">{{ $u->no_hp ?: '-' }}</small>
                                        @if($matchedStaff)
                                            <div class="mt-1 d-flex align-items-center gap-1">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle font-monospace" style="font-size: 0.75rem;" title="Akun login staf pendataan">
                                                    <i class="ti ti-key me-1"></i>Login: <strong>{{ $matchedStaff->username }}</strong> ({{ $matchedStaff->password }})
                                                </span>
                                            </div>
                                        @elseif($u->has_pendataan_access)
                                            <div class="mt-1">
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size: 0.73rem;" onclick="quickCreateStaffFromUser('{{ $u->id }}', '{{ addslashes($u->nama) }}', '{{ addslashes($u->email) }}')">
                                                    <i class="ti ti-user-plus me-1"></i>+ Buat Akun Login Pendataan
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary">{{ $u->email }}</span>
                            </td>
                            <td>
                                @if($u->jabatan === 'Staff')
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">Staff</span>
                                @elseif($u->jabatan === 'Admin')
                                    <span class="badge bg-dark px-2 py-1">Admin</span>
                                @elseif($u->jabatan === 'Superadmin')
                                    <span class="badge bg-danger px-2 py-1">Superadmin</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">{{ $u->jabatan }}</span>
                                @endif
                            </td>
                            <td class="text-center" id="badge-container-{{ $u->id }}">
                                @if($u->has_pendataan_access)
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                        <i class="ti ti-check me-1"></i> Diberi Akses
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-muted border border-secondary-subtle px-3 py-2">
                                        <i class="ti ti-x me-1"></i> Tidak Ada Akses
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input js-access-toggle"
                                           type="checkbox"
                                           role="switch"
                                           id="switch-{{ $u->id }}"
                                           name="accessible_user_ids[]"
                                           value="{{ $u->id }}"
                                           data-user-id="{{ $u->id }}"
                                           data-user-name="{{ $u->nama }}"
                                           data-url="{{ route('admin.pendataan.access.toggle', $u->id) }}"
                                           {{ $u->has_pendataan_access ? 'checked' : '' }}
                                           style="width: 2.7em; height: 1.4em; cursor: pointer;">
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ti ti-user-off fs-1 d-block mb-2 text-secondary"></i>
                                Tidak ada data pengguna yang sesuai dengan filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="ti ti-info-circle me-1"></i> Klik saklar untuk langsung memperbarui akses staf secara instan.
            </span>
            <button type="submit" class="btn btn-primary btn-sm px-3">
                <i class="ti ti-device-floppy me-1"></i> Simpan Semua Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const switches = document.querySelectorAll('.js-access-toggle');

    switches.forEach(function(toggle) {
        toggle.addEventListener('change', function(e) {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            const url = this.getAttribute('data-url');
            const isChecked = this.checked;
            const badgeContainer = document.getElementById('badge-container-' + userId);

            // Temporarily disable to prevent spam clicks
            this.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ state: isChecked ? 1 : 0 })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    if (data.has_access) {
                        badgeContainer.innerHTML = `
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                <i class="ti ti-check me-1"></i> Diberi Akses
                            </span>`;
                    } else {
                        badgeContainer.innerHTML = `
                            <span class="badge rounded-pill bg-secondary-subtle text-muted border border-secondary-subtle px-3 py-2">
                                <i class="ti ti-x me-1"></i> Tidak Ada Akses
                            </span>`;
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || 'Status akses berhasil diperbarui!',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                } else {
                    // Revert state on error
                    this.checked = !isChecked;
                    alert('Gagal memperbarui status akses.');
                }
            })
            .catch(err => {
                this.disabled = false;
                this.checked = !isChecked;
                console.error(err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Terjadi kesalahan saat memperbarui akses.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
    });
});

function copyStaffPass(pass, nama) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(pass).then(() => {
            showCopySuccess(pass, nama);
        }).catch(() => {
            fallbackCopy(pass, nama);
        });
    } else {
        fallbackCopy(pass, nama);
    }
}

function fallbackCopy(pass, nama) {
    const tempInput = document.createElement('input');
    tempInput.value = pass;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    showCopySuccess(pass, nama);
}

function showCopySuccess(pass, nama) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Password ' + nama + ' (' + pass + ') berhasil disalin!',
            showConfirmButton: false,
            timer: 2000
        });
    } else {
        alert('Password ' + nama + ' (' + pass + ') berhasil disalin!');
    }
}

function generateRandomPass(targetInputId) {
    const letters = 'abcdefghijklmnopqrstuvwxyz';
    let res = '';
    for (let i = 0; i < 5; i++) {
        res += letters.charAt(Math.floor(Math.random() * letters.length));
    }
    const el = document.getElementById(targetInputId);
    if (el) {
        el.value = res;
    }
}

function autoSuggestUsername(nama) {
    if (!nama) return;
    const clean = nama.trim().split(' ')[0].toLowerCase().replace(/[^a-z0-9]/g, '');
    const userField = document.getElementById('tambah_staff_username');
    if (userField && (!userField.value || userField.dataset.autofilled === "true" || userField.value === clean)) {
        userField.value = clean;
        userField.dataset.autofilled = "true";
    }
}

function onSelectUserForStaff(selectElem) {
    const selectedOption = selectElem.options[selectElem.selectedIndex];
    if (!selectedOption || !selectElem.value) {
        document.getElementById('tambah_staff_user_id').value = '';
        return;
    }
    const nama = selectedOption.getAttribute('data-nama') || '';
    document.getElementById('tambah_staff_user_id').value = selectElem.value;
    document.getElementById('tambah_staff_nama').value = nama;
    autoSuggestUsername(nama);
    if (!document.getElementById('tambah_staff_password').value) {
        generateRandomPass('tambah_staff_password');
    }
}

function quickCreateStaffFromUser(id, nama, email) {
    const select = document.getElementById('selectUserForStaff');
    if (select) {
        select.value = id;
    }
    document.getElementById('tambah_staff_user_id').value = id;
    document.getElementById('tambah_staff_nama').value = nama;
    const clean = nama.trim().split(' ')[0].toLowerCase().replace(/[^a-z0-9]/g, '');
    document.getElementById('tambah_staff_username').value = clean;
    generateRandomPass('tambah_staff_password');
    const modal = new bootstrap.Modal(document.getElementById('modalTambahStaff'));
    modal.show();
}

function openEditStaffModal(id, nama, username, pass, isActive) {
    const form = document.getElementById('formEditStaff');
    form.action = "{{ url('superadmin/pendataan-access/staff') }}/" + id;
    document.getElementById('edit_staff_nama').value = nama;
    document.getElementById('edit_staff_username').value = username;
    document.getElementById('edit_staff_password').value = pass;
    document.getElementById('edit_staff_is_active').checked = (isActive == 1);
    const modal = new bootstrap.Modal(document.getElementById('modalEditStaff'));
    modal.show();
}

function toggleStaffActive(id, toggleElem) {
    const isChecked = toggleElem.checked;
    toggleElem.disabled = true;
    fetch("{{ url('superadmin/pendataan-access/staff') }}/" + id + "/toggle-status", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ is_active: isChecked ? 1 : 0 })
    })
    .then(res => res.json())
    .then(data => {
        toggleElem.disabled = false;
        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: data.message || 'Status akun staf berhasil diperbarui!',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } else {
            toggleElem.checked = !isChecked;
            alert(data.message || 'Gagal mengubah status staf.');
        }
    })
    .catch(err => {
        toggleElem.disabled = false;
        toggleElem.checked = !isChecked;
        console.error(err);
        alert('Terjadi kesalahan jaringan.');
    });
}
</script>
@endpush
