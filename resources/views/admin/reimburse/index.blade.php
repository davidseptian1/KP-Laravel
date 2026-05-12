@extends('layouts/app')

@section('content')

<style>
    #reimburseTableContainer .modal-super-xl {
        max-width: min(1400px, 96vw);
    }

    #adminReimburseColumnSettingsPanel {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 10px;
        background: var(--bs-body-bg, #fff);
    }

    #adminReimburseColumnVisibilityOptions .column-option-item {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 8px;
        padding: 4px 8px;
        background: var(--bs-tertiary-bg, #f8f9fa);
    }

    #reimburseTableContainer table.js-admin-reorderable-table thead th {
        position: relative;
        border-right: 1px solid var(--bs-border-color, #dee2e6);
        user-select: none;
    }

    #reimburseTableContainer table.js-admin-reorderable-table thead th:last-child,
    #reimburseTableContainer table.js-admin-reorderable-table tbody td:last-child {
        border-right: none;
    }

    #reimburseTableContainer table.js-admin-reorderable-table tbody td {
        border-right: 1px solid var(--bs-border-color, #dee2e6);
    }

    #reimburseTableContainer table.js-admin-reorderable-table thead th.js-col-dragging {
        opacity: 0.55;
    }

    #reimburseTableContainer table.js-admin-reorderable-table thead th .js-col-resize-handle {
        position: absolute;
        top: 0;
        right: -5px;
        width: 12px;
        height: 100%;
        cursor: ew-resize;
        z-index: 3;
    }

    #reimburseTableContainer table.js-admin-reorderable-table thead th .js-col-resize-handle::before {
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

    #reimburseTableContainer table.js-admin-reorderable-table thead th:hover .js-col-resize-handle::before,
    #reimburseTableContainer table.js-admin-reorderable-table thead th .js-col-resize-handle:hover::before {
        background: var(--bs-primary, #0d6efd);
        opacity: 1;
    }

    #reimburseTableContainer table.js-admin-reorderable-table.js-col-resizing,
    #reimburseTableContainer table.js-admin-reorderable-table.js-col-resizing * {
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
                    <li class="breadcrumb-item active">Reimburse</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Monitoring Reimburse</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                    <select name="status" class="form-select form-select-sm" style="max-width: 200px;">
                        <option value="">Semua Status</option>
                        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'revision' => 'Revision'] as $key => $label)
                            <option value="{{ $key }}" {{ ($status ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm" placeholder="Kode / Nama User" style="max-width: 240px;" />
                    <button class="btn btn-primary btn-sm">Filter</button>
                </form>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-end mb-2 gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnToggleAdminColumnSettings">Atur Kolom</button>
                </div>

                <div id="adminReimburseColumnSettingsPanel" class="p-2 mb-2 d-none">
                    <div class="small text-muted mb-2">Centang kolom yang ingin ditampilkan.</div>
                    <div id="adminReimburseColumnVisibilityOptions" class="d-flex flex-wrap gap-2"></div>
                    <div class="text-end mt-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnResetAdminTableColumns">Reset Kolom</button>
                    </div>
                </div>

                <div class="table-responsive" id="reimburseTableContainer">
                    <table class="table table-hover align-middle js-admin-reorderable-table">
                        <thead class="table-light">
                            <tr>
                                <th data-col-id="0">Kode</th>
                                <th data-col-id="1">Form</th>
                                <th data-col-id="2">Nama</th>
                                <th data-col-id="3">Divisi</th>
                                <th data-col-id="4">No Rekening</th>
                                <th data-col-id="5">Metode</th>
                                <th data-col-id="6">No/ID</th>
                                <th data-col-id="7">Atas Nama</th>
                                <th data-col-id="8">Barang</th>
                                <th data-col-id="9">Deskripsi</th>
                                <th data-col-id="10" class="text-end">Nominal</th>
                                <th data-col-id="11">WA Penerima</th>
                                <th data-col-id="12">WA Pengisi</th>
                                <th data-col-id="13">Bukti Pembayaran</th>
                                <th data-col-id="14">Tanggal</th>
                                <th data-col-id="15">Status</th>
                                <th data-col-id="16">Catatan</th>
                                <th data-col-id="17" class="text-center" style="position: sticky; right: 0; background: #f8f9fa; z-index: 2; min-width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->kode_reimburse }}</td>
                                    <td>{{ $item->form?->kode_form ?? '-' }}</td>
                                    <td>{{ $item->nama ?? '-' }}</td>
                                    <td>{{ $item->divisi ?? '-' }}</td>
                                    <td>{{ $item->no_rekening ?? '-' }}</td>
                                    <td>
                                        @if ($item->payment_method)
                                            {{ $item->payment_method === 'ewallet' ? 'E-Wallet' : 'Bank' }}
                                            @if ($item->payment_provider)
                                                ({{ strtoupper($item->payment_provider) }})
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->payment_account_number ?? '-' }}</td>
                                    <td>{{ $item->payment_account_name ?? '-' }}</td>
                                    <td>{{ $item->nama_barang ?? '-' }}</td>
                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>{{ $item->wa_penerima ?? '-' }}</td>
                                    <td>{{ $item->wa_pengisi ?? '-' }}</td>
                                    <td>
                                        @if ($item->payment_proof_type === 'text')
                                            {{ $item->payment_proof_text ?? '-' }}
                                        @elseif ($item->payment_proof_type === 'image' && $item->payment_proof_image)
                                            <a target="_blank" href="{{ route('admin.reimburse.payment-proof', $item->id) }}">Lihat</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ optional($item->tanggal_pengajuan)->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'revision' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->catatan_admin ?? '-' }}</td>
                                    <td class="text-center" style="position: sticky; right: 0; background: #fff; z-index: 1; min-width: 170px;">
                                        <div class="d-flex gap-2 justify-content-center">
                                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#aksiModal-{{ $item->id }}">Lihat</button>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteReimburse-{{ $item->id }}">Hapus</button>
                                        </div>

                                        <div class="modal fade" id="deleteReimburse-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <form method="POST" action="{{ route('admin.reimburse.destroy', $item->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Hapus Data Reimburse</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p class="mb-2">Tuliskan alasan penghapusan agar tercatat di log.</p>
                                                            <textarea name="delete_reason" class="form-control" rows="3" placeholder="Contoh: Data duplikat / salah upload" required></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="aksiModal-{{ $item->id }}" tabindex="-1" aria-labelledby="aksiModalLabel-{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="aksiModalLabel-{{ $item->id }}">Aksi Reimburse - {{ $item->kode_reimburse }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex flex-column gap-3">
                                                            <div class="border rounded p-3 bg-light">
                                                                <div class="fw-semibold">No Rekening</div>
                                                                <div>{{ $item->no_rekening ?? '-' }}</div>
                                                                <div class="mt-2">
                                                                    <div><span class="fw-semibold">Metode:</span> {{ $item->payment_method ? ($item->payment_method === 'ewallet' ? 'E-Wallet' : 'Bank') : '-' }}</div>
                                                                    <div><span class="fw-semibold">Provider:</span> {{ $item->payment_provider ? strtoupper($item->payment_provider) : '-' }}</div>
                                                                    <div><span class="fw-semibold">No/ID:</span> {{ $item->payment_account_number ?? '-' }}</div>
                                                                    <div><span class="fw-semibold">Atas Nama:</span> {{ $item->payment_account_name ?? '-' }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <a class="btn btn-outline-secondary" target="_blank" href="{{ route('admin.reimburse.view', [$item->id, 0]) }}">Lihat Bukti</a>
                                                                <a class="btn btn-outline-primary" href="{{ route('admin.reimburse.download', $item->id) }}">Download Bukti</a>
                                                            </div>

                                                            @if (!empty($item->bukti_files) && count($item->bukti_files) > 1)
                                                                <div class="small text-muted">
                                                                    Bukti lain:
                                                                    @foreach ($item->bukti_files as $idx => $file)
                                                                        <a target="_blank" href="{{ route('admin.reimburse.view', [$item->id, $idx]) }}">#{{ $idx + 1 }}</a>
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                            <form method="POST" action="{{ route('admin.reimburse.update', $item->id) }}" class="d-flex flex-column gap-3" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PUT')
                                                                <select name="status" class="form-select" required>
                                                                    @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'revision' => 'Revision'] as $key => $label)
                                                                        <option value="{{ $key }}" {{ $item->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <label class="form-label text-start mb-0">Bukti Pembayaran</label>
                                                                <select name="payment_proof_type" class="form-select">
                                                                    <option value="">Bukti Pembayaran (opsional)</option>
                                                                    <option value="text" {{ $item->payment_proof_type === 'text' ? 'selected' : '' }}>Teks</option>
                                                                    <option value="image" {{ $item->payment_proof_type === 'image' ? 'selected' : '' }}>Gambar</option>
                                                                </select>
                                                                <input type="text" name="payment_proof_text" value="{{ $item->payment_proof_text }}" class="form-control" placeholder="Isi bukti pembayaran (teks)" />
                                                                <input type="file" name="payment_proof_image" class="form-control" accept="image/*" />
                                                                <label class="form-label text-start mb-0">Catatan Admin</label>
                                                                <input type="text" name="catatan_admin" value="{{ $item->catatan_admin }}" class="form-control" placeholder="Catatan admin" />
                                                                <button class="btn btn-success">Update + Kirim WA</button>
                                                            </form>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data reimburse</td>
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

@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[id^="aksiModal-"], [id^="deleteReimburse-"]').forEach(function (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }

            modalEl.addEventListener('hidden.bs.modal', function () {
                const hasOpenModal = document.querySelectorAll('.modal.show').length > 0;
                if (!hasOpenModal) {
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('padding-right');
                    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
                        backdrop.remove();
                    });
                }
            });
        });

        function initAdminTableColumnControls() {
            const table = document.querySelector('#reimburseTableContainer .js-admin-reorderable-table');
            if (!table) return;

            const headerRow = table.querySelector('thead tr');
            if (!headerRow) return;

            const orderStorageKey = 'admin.reimburse.table.column.order.v1';
            const visibilityStorageKey = 'admin.reimburse.table.column.visibility.v1';
            const widthStorageKey = 'admin.reimburse.table.column.width.v1';

            const toggleSettingsBtn = document.getElementById('btnToggleAdminColumnSettings');
            const settingsPanel = document.getElementById('adminReimburseColumnSettingsPanel');
            const visibilityOptions = document.getElementById('adminReimburseColumnVisibilityOptions');
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

                th.appendChild(handle);

                th.addEventListener('dragstart', function (event) {
                    if (isResizingColumn) {
                        event.preventDefault();
                        return;
                    }
                    dragFromIndex = getColumnIndexById(this.dataset.colId);
                    this.classList.add('js-col-dragging');
                });

                th.addEventListener('dragend', function () {
                    this.classList.remove('js-col-dragging');
                    dragFromIndex = null;
                });

                th.addEventListener('dragover', function (event) {
                    event.preventDefault();
                });

                th.addEventListener('drop', function (event) {
                    event.preventDefault();
                    if (dragFromIndex === null) return;

                    const dragToIndex = getColumnIndexById(this.dataset.colId);
                    if (dragFromIndex !== dragToIndex) {
                        moveColumn(dragFromIndex, dragToIndex);
                        saveOrder();
                        saveWidths();
                        renderColumnVisibilityOptions();
                    }
                });
            });
        }

        initAdminTableColumnControls();
    })();
</script>
@endpush
