@extends('layouts/app')

@section('content')

<style>
    #monitoringTableContainer tr.latest-row-highlight > td {
        background-color: var(--bs-primary-bg-subtle, #cfe2ff) !important;
        color: var(--bs-body-color, #212529);
    }
</style>

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Monitoring Deposit</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Monitoring Deposit</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalUploadManualDeposit">
                        Upload Manual Excel
                    </button>
                </div>

                <form method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Search by Server</label>
                        <input type="text" name="server" class="form-control form-control-sm" value="{{ $server ?? '' }}" placeholder="Contoh: server-1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ ($status ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ ($status ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="selesai" {{ ($status ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="rejected" {{ ($status ?? '') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        <a href="{{ route('admin.deposit.monitoring') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                </form>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <small class="text-muted">
                        Default monitoring menampilkan data hari ini (jam 00:00 - 23:59).
                    </small>
                    <div class="d-flex gap-2">
                        <a
                            href="{{ route('admin.deposit.monitoring.export-excel', ['server' => $server, 'status' => $status, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="btn btn-success btn-sm"
                        >
                            Download Excel
                        </a>
                        <a
                            href="{{ route('admin.deposit.monitoring.export-pdf', ['server' => $server, 'status' => $status, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="btn btn-danger btn-sm"
                            target="_blank"
                        >
                            Download PDF
                        </a>
                    </div>
                </div>

                <div class="card border-0 bg-light mb-3">
                    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-2">
                        <div>
                            <div class="fw-semibold mb-1"><i class="ti ti-bell me-1"></i>Pengaturan Notifikasi Monitoring</div>
                            <small class="text-muted" id="adminBrowserNotifStatus">Status browser notification: mengecek...</small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnEnableAdminBrowserNotif">Notif Off</button>
                        </div>
                    </div>
                </div>

                <div id="latestIncomingCardContainer">
                    @include('admin.deposit.partials.latest-incoming-card', ['latestIncomingAt' => $latestIncomingAt ?? null])
                </div>

                <div id="monitoringTableContainer">
                    @include('admin.deposit.partials.table', ['items' => $items, 'latestIncomingId' => $latestIncomingId ?? null])
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUploadManualDeposit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.deposit.import-manual') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Upload Manual Deposit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Input</label>
                        <input type="date" name="manual_date" class="form-control" value="{{ old('manual_date', now()->format('Y-m-d')) }}" required>
                        <small class="text-muted">Semua data dari file akan masuk ke tanggal ini dan status otomatis selesai.</small>
                    </div>
                    <div>
                        <label class="form-label">File Excel</label>
                        <input type="file" name="manual_file" class="form-control" accept=".xlsx,.xls,.csv,.txt" required>
                        <small class="text-muted">Kolom minimal: Nama Supplier, Nominal, Bank, Server, No Rek, Nama Rekening, Jam.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const changesUrl = @json(route('admin.deposit.monitoring.changes'));
        let latestUpdatedAt = @json($latestUpdatedAt);
        let latestIncomingId = @json($latestIncomingId ?? null);
        const filters = {
            server: @json($server ?? ''),
            status: @json($status ?? ''),
            start_date: @json($startDate ?? ''),
            end_date: @json($endDate ?? ''),
            page: @json($items->currentPage()),
        };
        const pollIntervalMs = 1000;

        const notifStatusEl = document.getElementById('adminBrowserNotifStatus');
        const enableNotifBtn = document.getElementById('btnEnableAdminBrowserNotif');

        function updateNotifToggleButton() {
            if (!enableNotifBtn) return;

            if (!('Notification' in window)) {
                enableNotifBtn.textContent = 'Notif Off';
                enableNotifBtn.disabled = true;
                return;
            }

            enableNotifBtn.disabled = false;
            enableNotifBtn.textContent = Notification.permission === 'granted' ? 'Notif On' : 'Notif Off';
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

            updateNotifToggleButton();
        }

        async function requestBrowserNotificationPermission() {
            if (!('Notification' in window)) return;

            if (Notification.permission === 'granted') {
                updateNotifStatusText();
                return;
            }

            await Notification.requestPermission();
            updateNotifStatusText();
        }

        function sendBrowserNotification(title, description) {
            if (!('Notification' in window)) return;
            if (Notification.permission !== 'granted') return;

            new Notification(title || 'Update Monitoring Deposit', {
                body: description || 'Ada perubahan data deposit oleh staff/admin.',
                tag: 'admin-monitoring-deposit-update',
            });
        }

        function initTransferFormControls() {
            function setPreview(targetId, file) {
                const wrap = document.querySelector('.js-image-preview-wrap[data-target="' + targetId + '"]');
                const img = document.querySelector('.js-image-preview[data-target="' + targetId + '"]');

                if (!wrap || !img || !file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    img.src = event.target.result;
                    wrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }

            document.querySelectorAll('.js-reply-type').forEach(function (select) {
                if (select.dataset.bound === '1') return;
                select.dataset.bound = '1';

                select.addEventListener('change', function () {
                    const targetId = this.dataset.target;
                    const imageWrap = document.querySelector('.js-reply-image-wrap[data-target="' + targetId + '"]');
                    const transferTextWrap = document.querySelector('.js-transfer-text-wrap[data-target="' + targetId + '"]');
                    if (!imageWrap || !transferTextWrap) return;

                    imageWrap.style.display = this.value === 'image' ? 'block' : 'none';
                    transferTextWrap.style.display = this.value === 'text' ? 'block' : 'none';
                });

                select.dispatchEvent(new Event('change'));
            });

            document.querySelectorAll('.js-reply-image-input').forEach(function (input) {
                if (input.dataset.bound === '1') return;
                input.dataset.bound = '1';

                input.addEventListener('change', function () {
                    const targetId = this.dataset.target;
                    const file = this.files && this.files[0] ? this.files[0] : null;
                    if (file) setPreview(targetId, file);
                });
            });

            document.querySelectorAll('.js-paste-zone').forEach(function (zone) {
                if (zone.dataset.bound === '1') return;
                zone.dataset.bound = '1';

                zone.addEventListener('paste', function (event) {
                    const targetId = this.dataset.target;
                    const input = document.querySelector('.js-reply-image-input[data-target="' + targetId + '"]');
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

                            const typeSelect = document.querySelector('.js-reply-type[data-target="' + targetId + '"]');
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

        function applyLatestRowHighlight(targetId) {
            if (!targetId) return;

            document.querySelectorAll('#monitoringTableContainer tr[data-deposit-id]').forEach(function (row) {
                row.classList.remove('latest-row-highlight');
            });

            const targetRow = document.querySelector('#monitoringTableContainer tr[data-deposit-id="' + targetId + '"]');
            if (!targetRow) return;

            targetRow.classList.add('latest-row-highlight');
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        async function checkChanges() {
            try {
                const params = new URLSearchParams();
                if (filters.server) params.set('server', filters.server);
                if (filters.status) params.set('status', filters.status);
                if (filters.start_date) params.set('start_date', filters.start_date);
                if (filters.end_date) params.set('end_date', filters.end_date);
                if (filters.page) params.set('page', filters.page);
                if (latestUpdatedAt) params.set('since', latestUpdatedAt);

                const response = await fetch(changesUrl + '?' + params.toString(), {
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

                if (result.latest_incoming_id) {
                    latestIncomingId = result.latest_incoming_id;
                }

                if (!result.has_changes) return;

                if (result.latest_card_html) {
                    const latestCardContainer = document.getElementById('latestIncomingCardContainer');
                    if (latestCardContainer) {
                        latestCardContainer.innerHTML = result.latest_card_html;
                    }
                }

                if (result.table_html) {
                    const tableContainer = document.getElementById('monitoringTableContainer');
                    if (tableContainer) {
                        tableContainer.innerHTML = result.table_html;
                        initTransferFormControls();
                        applyLatestRowHighlight(latestIncomingId);
                    }
                }

                const notifTitle = result.change_title || ('Ada perubahan data deposit (' + result.changes_count + ')');
                const notifDescription = result.change_description || 'Ada perubahan data oleh staff/admin.';

                sendBrowserNotification(notifTitle, notifDescription);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'info',
                    title: notifTitle,
                    text: notifDescription,
                    showConfirmButton: false,
                    timer: 2200,
                    timerProgressBar: true,
                });
            } catch (error) {
            }
        }

        if (enableNotifBtn) {
            enableNotifBtn.addEventListener('click', requestBrowserNotificationPermission);
        }

        updateNotifStatusText();
        initTransferFormControls();
        applyLatestRowHighlight(latestIncomingId);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                checkChanges();
            }
        });

        setInterval(checkChanges, pollIntervalMs);
    })();
</script>
@endpush
