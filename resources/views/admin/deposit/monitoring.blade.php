@extends('layouts/app')

@section('content')

<style>
    #monitoringTableContainer .modal-super-xl {
        max-width: min(1400px, 96vw);
    }

    #monitoringTableContainer textarea.js-auto-resize-textarea {
        overflow-y: auto;
        resize: none;
        min-height: 70px;
        max-height: 260px;
    }

    #adminMonitoringColumnSettingsPanel {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 10px;
        background: var(--bs-body-bg, #fff);
    }

    #adminMonitoringColumnVisibilityOptions .column-option-item {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 8px;
        padding: 4px 8px;
        background: var(--bs-tertiary-bg, #f8f9fa);
    }

    #monitoringTableContainer table.js-admin-reorderable-table thead th {
        position: relative;
        border-right: 1px solid var(--bs-border-color, #dee2e6);
        user-select: none;
    }

    #monitoringTableContainer table.js-admin-reorderable-table thead th:last-child,
    #monitoringTableContainer table.js-admin-reorderable-table tbody td:last-child {
        border-right: none;
    }

    #monitoringTableContainer table.js-admin-reorderable-table tbody td {
        border-right: 1px solid var(--bs-border-color, #dee2e6);
    }

    #monitoringTableContainer table.js-admin-reorderable-table thead th.js-col-dragging {
        opacity: 0.55;
    }

    #monitoringTableContainer table.js-admin-reorderable-table thead th .js-col-resize-handle {
        position: absolute;
        top: 0;
        right: -5px;
        width: 12px;
        height: 100%;
        cursor: ew-resize;
        z-index: 3;
    }

    #monitoringTableContainer table.js-admin-reorderable-table thead th .js-col-resize-handle::before {
        content: '';
        position: absolute;
        top: 8%;
        bottom: 8%;
        left: 50%;
        transform: translateX(-50%);
        width: 2px;
        border-radius: 2px;
        background: var(--bs-border-color, #dee2e6);
        opacity: 0.9;
    }

    #monitoringTableContainer table.js-admin-reorderable-table thead th:hover .js-col-resize-handle::before,
    #monitoringTableContainer table.js-admin-reorderable-table thead th .js-col-resize-handle:hover::before {
        background: var(--bs-primary, #0d6efd);
        opacity: 1;
    }

    #monitoringTableContainer table.js-admin-reorderable-table.js-col-resizing,
    #monitoringTableContainer table.js-admin-reorderable-table.js-col-resizing * {
        cursor: ew-resize !important;
        user-select: none;
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

                <form method="GET" class="row g-2 align-items-end mb-3" autocomplete="off">
                    <div class="col-md-2">
                        <label class="form-label">Nama Server</label>
                        <input type="text" name="server" class="form-control form-control-sm" value="{{ $server ?? '' }}" list="filterServerList" placeholder="Semua Server" autocomplete="off">
                        <datalist id="filterServerList">
                            @foreach (($serverOptions ?? collect()) as $serverOption)
                                <option value="{{ $serverOption }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nama Bank</label>
                        <input type="text" name="bank" class="form-control form-control-sm" value="{{ $bank ?? '' }}" list="filterBankList" placeholder="Semua Bank" autocomplete="off">
                        <datalist id="filterBankList">
                            @foreach (($bankOptions ?? collect()) as $bankOption)
                                <option value="{{ $bankOption }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Nama SPL</label>
                        <input type="text" name="nama_supplier" class="form-control form-control-sm" value="{{ $namaSupplier ?? '' }}" list="filterSupplierList" placeholder="Semua SPL" autocomplete="off">
                        <datalist id="filterSupplierList">
                            @foreach (($supplierOptions ?? collect()) as $supplierOption)
                                <option value="{{ $supplierOption }}"></option>
                            @endforeach
                        </datalist>
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
                        <label class="form-label">Dihapus Staff</label>
                        <select name="staff_deleted" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="yes" {{ ($staffDeleted ?? '') === 'yes' ? 'selected' : '' }}>Ya</option>
                            <option value="no" {{ ($staffDeleted ?? '') === 'no' ? 'selected' : '' }}>Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tampil</label>
                        <select name="per_page" class="form-select form-select-sm">
                            <option value="10" {{ ($perPage ?? 50) == 10 ? 'selected' : '' }}>10 baris</option>
                            <option value="50" {{ ($perPage ?? 50) == 50 ? 'selected' : '' }}>50 baris</option>
                            <option value="100" {{ ($perPage ?? 50) == 100 ? 'selected' : '' }}>100 baris</option>
                            <option value="200" {{ ($perPage ?? 50) == 200 ? 'selected' : '' }}>200 baris</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Cari Data</label>
                        <input type="text" name="global_search" class="form-control form-control-sm" value="{{ $globalSearch ?? '' }}" placeholder="Cari nama, nominal...">
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
                            href="{{ route('admin.deposit.monitoring.export-excel', ['server' => $server, 'bank' => $bank, 'nama_supplier' => $namaSupplier, 'status' => $status, 'staff_deleted' => $staffDeleted, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
                            class="btn btn-success btn-sm"
                        >
                            Download Excel
                        </a>
                        <a
                            href="{{ route('admin.deposit.monitoring.export-pdf', ['server' => $server, 'bank' => $bank, 'nama_supplier' => $namaSupplier, 'status' => $status, 'staff_deleted' => $staffDeleted, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
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
                    @include('admin.deposit.partials.latest-incoming-card', [
                        'latestIncomingAt' => $latestIncomingAt ?? null,
                        'latestIncomingServer' => $latestIncomingServer ?? null,
                        'latestIncomingServerColor' => $latestIncomingServerColor ?? 'primary',
                    ])
                </div>

                <div id="monitoringSummaryCardContainer">
                    @include('admin.deposit.partials.summary-card', [
                        'monitoringSummary' => $monitoringSummary ?? null,
                    ])
                </div>

                <div class="d-flex justify-content-end mb-2 gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnToggleAdminColumnSettings">Atur Kolom</button>
                </div>

                <div id="adminMonitoringColumnSettingsPanel" class="p-2 mb-2 d-none">
                    <div class="small text-muted mb-2">Centang kolom yang ingin ditampilkan.</div>
                    <div id="adminMonitoringColumnVisibilityOptions" class="d-flex flex-wrap gap-2"></div>
                    <div class="text-end mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetAdminTableColumns">Reset Kolom</button>
                    </div>
                </div>

                <div id="monitoringTableContainer">
                    @include('admin.deposit.partials.table', [
                        'items' => $items,
                        'latestIncomingId' => $latestIncomingId ?? null,
                        'serverOptions' => $serverOptions ?? collect(),
                        'bankOptions' => $bankOptions ?? collect(),
                        'supplierOptions' => $supplierOptions ?? collect(),
                    ])
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalUploadManualDeposit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ url('admin/deposit/import-manual') }}" enctype="multipart/form-data">
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
            bank: @json($bank ?? ''),
            nama_supplier: @json($namaSupplier ?? ''),
            status: @json($status ?? ''),
            staff_deleted: @json($staffDeleted ?? ''),
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

        function initAutoResizeTextareas() {
            document.querySelectorAll('#monitoringTableContainer textarea.js-auto-resize-textarea').forEach(function (textarea) {
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
        }

        function bindModalTextareaResize() {
            const container = document.getElementById('monitoringTableContainer');
            if (!container || container.dataset.boundModalAutosize === '1') return;

            container.dataset.boundModalAutosize = '1';
            container.addEventListener('shown.bs.modal', function (event) {
                const modalEl = event.target;
                if (!modalEl) return;

                modalEl.querySelectorAll('textarea.js-auto-resize-textarea').forEach(function (textarea) {
                    textarea.style.height = 'auto';
                    const maxHeight = 260;
                    const nextHeight = Math.min(textarea.scrollHeight, maxHeight);
                    textarea.style.height = nextHeight + 'px';
                    textarea.style.overflowY = textarea.scrollHeight > maxHeight ? 'auto' : 'hidden';
                });
            });
        }

        function initAdminTableColumnControls() {
            const table = document.querySelector('#monitoringTableContainer .js-admin-reorderable-table');
            if (!table) return;

            const headerRow = table.querySelector('thead tr');
            if (!headerRow) return;

            const orderStorageKey = 'admin.deposit.table.column.order.v1';
            const visibilityStorageKey = 'admin.deposit.table.column.visibility.v1';
            const widthStorageKey = 'admin.deposit.table.column.width.v1';

            const toggleSettingsBtn = document.getElementById('btnToggleAdminColumnSettings');
            const settingsPanel = document.getElementById('adminMonitoringColumnSettingsPanel');
            const visibilityOptions = document.getElementById('adminMonitoringColumnVisibilityOptions');
            const resetColumnsBtn = document.getElementById('btnResetAdminTableColumns');

            const headerCells = Array.from(headerRow.cells || []);
            if (headerCells.length < 2) return;

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

            function applyColumnWidthById(colId, widthPx) {
                const index = getColumnIndexById(colId);
                if (index < 0) return;

                const safeWidth = Math.max(60, Number(widthPx) || 0);
                if (!safeWidth) return;

                Array.from(table.rows || []).forEach(function (row) {
                    if (!row.cells || !row.cells[index]) return;
                    row.cells[index].style.width = safeWidth + 'px';
                    row.cells[index].style.minWidth = safeWidth + 'px';
                    row.cells[index].style.maxWidth = safeWidth + 'px';
                });
            }

            function clearColumnWidthById(colId) {
                const index = getColumnIndexById(colId);
                if (index < 0) return;

                Array.from(table.rows || []).forEach(function (row) {
                    if (!row.cells || !row.cells[index]) return;
                    row.cells[index].style.removeProperty('width');
                    row.cells[index].style.removeProperty('min-width');
                    row.cells[index].style.removeProperty('max-width');
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

            function getWidthState() {
                const state = {};
                Array.from(headerRow.cells || []).forEach(function (th) {
                    const id = String(th.dataset.colId || '');
                    if (!id) return;
                    state[id] = Math.round(th.getBoundingClientRect().width || th.offsetWidth || 0);
                });
                return state;
            }

            function lockCurrentColumnWidths() {
                const widthState = getWidthState();
                Object.keys(widthState).forEach(function (colId) {
                    applyColumnWidthById(colId, widthState[colId]);
                });
            }

            function saveOrder() {
                try {
                    localStorage.setItem(orderStorageKey, JSON.stringify(getCurrentOrder()));
                } catch (error) {
                }
            }

            function saveVisibility() {
                try {
                    localStorage.setItem(visibilityStorageKey, JSON.stringify(getVisibilityState()));
                } catch (error) {
                }
            }

            function saveWidths() {
                try {
                    localStorage.setItem(widthStorageKey, JSON.stringify(getWidthState()));
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

            function applySavedWidths() {
                try {
                    const raw = localStorage.getItem(widthStorageKey);
                    if (!raw) return;
                    const savedState = JSON.parse(raw);
                    if (!savedState || typeof savedState !== 'object') return;

                    Object.keys(savedState).forEach(function (colId) {
                        applyColumnWidthById(colId, savedState[colId]);
                    });
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

            applySavedOrder();
            applySavedVisibility();
            applySavedWidths();
            lockCurrentColumnWidths();
            renderColumnVisibilityOptions();

            if (toggleSettingsBtn && settingsPanel && !toggleSettingsBtn.dataset.boundColumnSetting) {
                toggleSettingsBtn.dataset.boundColumnSetting = '1';
                toggleSettingsBtn.addEventListener('click', function () {
                    settingsPanel.classList.toggle('d-none');
                });
            }

            if (resetColumnsBtn && !resetColumnsBtn.dataset.boundColumnReset) {
                resetColumnsBtn.dataset.boundColumnReset = '1';
                resetColumnsBtn.addEventListener('click', function () {
                    try {
                        localStorage.removeItem(orderStorageKey);
                        localStorage.removeItem(visibilityStorageKey);
                        localStorage.removeItem(widthStorageKey);
                    } catch (error) {
                    }

                    const defaultOrder = headerCells
                        .map(function (th) { return String(th.dataset.colId || ''); })
                        .sort(function (a, b) { return Number(a) - Number(b); });

                    applyOrder(defaultOrder);

                    defaultOrder.forEach(function (colId) {
                        setColumnVisibilityById(colId, true);
                        clearColumnWidthById(colId);
                    });

                    renderColumnVisibilityOptions();
                });
            }

            let dragFromIndex = null;
            let isResizingColumn = false;

            Array.from(headerRow.cells || []).forEach(function (th) {
                if (th.querySelector('.js-col-resize-handle')) return;

                const colId = String(th.dataset.colId || '');
                if (!colId) return;

                const handle = document.createElement('span');
                handle.className = 'js-col-resize-handle';
                handle.title = 'Tarik untuk ubah lebar kolom';

                handle.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    isResizingColumn = true;
                    table.classList.add('js-col-resizing');

                    const startX = event.clientX;
                    const startWidth = th.getBoundingClientRect().width || th.offsetWidth || 0;

                    function onMouseMove(moveEvent) {
                        const diffX = moveEvent.clientX - startX;
                        const nextWidth = Math.max(60, Math.round(startWidth + diffX));
                        applyColumnWidthById(colId, nextWidth);
                    }

                    function onMouseUp() {
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);
                        isResizingColumn = false;
                        table.classList.remove('js-col-resizing');
                        saveWidths();
                    }

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });

                handle.addEventListener('dragstart', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                });

                th.appendChild(handle);
            });

            Array.from(headerRow.cells || []).forEach(function (th) {
                th.addEventListener('dragstart', function (event) {
                    if (isResizingColumn) {
                        event.preventDefault();
                        return;
                    }

                    const target = event.target;
                    if (target && target.closest && target.closest('.js-col-resize-handle')) {
                        event.preventDefault();
                        return;
                    }

                    const dragEdgeThreshold = 14;
                    const pointerX = typeof event.offsetX === 'number' ? event.offsetX : 0;
                    if ((th.clientWidth - pointerX) <= dragEdgeThreshold) {
                        event.preventDefault();
                        return;
                    }

                    dragFromIndex = this.cellIndex;
                    this.classList.add('js-col-dragging');
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', String(dragFromIndex));
                    }
                });

                th.addEventListener('dragover', function (event) {
                    if (isResizingColumn) return;
                    event.preventDefault();
                    if (event.dataTransfer) {
                        event.dataTransfer.dropEffect = 'move';
                    }
                });

                th.addEventListener('drop', function (event) {
                    if (isResizingColumn) return;
                    event.preventDefault();
                    if (dragFromIndex === null) return;

                    const dropIndex = this.cellIndex;
                    moveColumn(dragFromIndex, dropIndex);
                    lockCurrentColumnWidths();
                    saveOrder();
                    saveWidths();
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
                if (filters.bank) params.set('bank', filters.bank);
                if (filters.nama_supplier) params.set('nama_supplier', filters.nama_supplier);
                if (filters.status) params.set('status', filters.status);
                if (filters.staff_deleted) params.set('staff_deleted', filters.staff_deleted);
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

                if (result.summary_card_html) {
                    const summaryCardContainer = document.getElementById('monitoringSummaryCardContainer');
                    if (summaryCardContainer) {
                        summaryCardContainer.innerHTML = result.summary_card_html;
                    }
                }

                if (result.table_html) {
                    const hasOpenModal = document.querySelector('.modal.show') !== null;
                    
                    if (!hasOpenModal) {
                        const tableContainer = document.getElementById('monitoringTableContainer');
                        if (tableContainer) {
                            tableContainer.innerHTML = result.table_html;
                            initTransferFormControls();
                            initAutoResizeTextareas();
                            bindModalTextareaResize();
                            initAdminTableColumnControls();
                            applyLatestRowHighlight(latestIncomingId);
                        }
                    } else {
                        // Kalo ada modal terbuka, tunda update-nya sampai modal ditutup
                        const openModalEl = document.querySelector('.modal.show');
                        const handleModalHidden = function () {
                            const container = document.getElementById('monitoringTableContainer');
                            if (container) {
                                container.innerHTML = result.table_html;
                                initTransferFormControls();
                                initAutoResizeTextareas();
                                bindModalTextareaResize();
                                initAdminTableColumnControls();
                                applyLatestRowHighlight(latestIncomingId);
                            }
                            openModalEl.removeEventListener('hidden.bs.modal', handleModalHidden);
                        };
                        openModalEl.addEventListener('hidden.bs.modal', handleModalHidden);
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
        initAutoResizeTextareas();
        bindModalTextareaResize();
        initAdminTableColumnControls();
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
