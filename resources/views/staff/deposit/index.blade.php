@extends('layouts/app')

@section('content')

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

<div class="row mt-0">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRequestDeposit">
                    Request Deposit +
                </button>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $tanggal ?? now()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Cari Nama Supplier</label>
                        <input type="text" name="search_supplier" class="form-control form-control-sm" value="{{ $searchSupplier ?? '' }}" placeholder="Contoh: DIGIFLAZZ">
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        <a href="{{ route('deposit.request.index') }}" class="btn btn-outline-secondary btn-sm w-100">Hari Ini</a>
                    </div>
                </form>

                <div class="card border-0 bg-light mb-3">
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

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
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
                                    <td>
                                        @if (($item->status ?? 'pending') === 'approved')
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalInputReply-{{ $item->id }}">Input</button>

                                            <div class="modal fade" id="modalInputReply-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('deposit.request.reply.update', $item->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Input Reply Penambahan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <label class="form-label">Reply Penambahan</label>
                                                                <textarea name="reply_penambahan" class="form-control" rows="4" required>{{ $item->reply_penambahan }}</textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan</button>
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
            <form method="POST" action="{{ route('deposit.request.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Request Deposit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="form_id" value="{{ $activeForms->first()->id ?? '' }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Supplier</label>
                            <select name="nama_supplier" class="form-select" required>
                                <option value="">Pilih Supplier</option>
                                @foreach (($suppliers ?? collect()) as $supplier)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                @endforeach
                            </select>
                            @if (($suppliers ?? collect())->isEmpty())
                                <small class="text-danger">Belum ada supplier. Minta admin tambah supplier di menu Supplier Manajemen.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nominal</label>
                            <input type="text" name="nominal" class="form-control" inputmode="numeric" placeholder="Contoh: 1.250.000,-" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deposit / Hutang</label>
                            <select name="jenis_transaksi" class="form-select" required>
                                <option value="deposit">Deposit</option>
                                <option value="hutang">Hutang</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank</label>
                            <input type="text" name="bank" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Server</label>
                            <select name="server" class="form-select" required>
                                <option value="">Pilih Server</option>
                                @foreach (($servers ?? collect()) as $server)
                                    <option value="{{ $server }}">{{ $server }}</option>
                                @endforeach
                            </select>
                            @if (($servers ?? collect())->isEmpty())
                                <small class="text-danger">Belum ada server. Minta admin tambah server di menu Server Manajemen.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No Rekening</label>
                            <input type="text" name="no_rek" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Rekening</label>
                            <input type="text" name="nama_rekening" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reply Tiket</label>
                            <textarea name="reply_tiket" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
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
        const pollIntervalMs = 1000;

        const notifStatusEl = document.getElementById('browserNotifStatus');
        const enableNotifBtn = document.getElementById('btnEnableBrowserNotif');

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

                if (supplierCell) supplierCell.textContent = item.nama_supplier || '-';
                if (rekeningCell) rekeningCell.textContent = item.nama_rekening || '-';
                if (tiketCell) tiketCell.textContent = item.reply_tiket || '-';
                if (penambahanCell) penambahanCell.textContent = item.reply_penambahan || 'Menunggu Konfirmasi Admin';
                if (buktiCell) buktiCell.innerHTML = buktiTransferHtml(item);
                if (statusCell) statusCell.innerHTML = statusBadgeHtml(item.status || 'pending');
                if (jamCell) jamCell.textContent = item.jam || '-';

                row.classList.add('table-info');
                setTimeout(function () {
                    row.classList.remove('table-info');
                }, 2200);
            });

            if (hasMissingRow) {
                window.location.reload();
            }
        }

        if (enableNotifBtn) {
            enableNotifBtn.addEventListener('click', requestBrowserNotificationPermission);
        }

        updateNotifStatusText();

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                checkChanges();
            }
        });

        setInterval(checkChanges, pollIntervalMs);
    })();
</script>
@endpush

