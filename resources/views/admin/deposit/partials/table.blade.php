<div class="table-responsive">
    <style>
        .table-hover > tbody > tr.table-primary:hover > td { background-color: var(--bs-primary-bg-subtle) !important; color: var(--bs-primary-text-emphasis) !important; }
        .table-hover > tbody > tr.table-success:hover > td { background-color: var(--bs-success-bg-subtle) !important; color: var(--bs-success-text-emphasis) !important; }
        .table-hover > tbody > tr.table-info:hover > td { background-color: var(--bs-info-bg-subtle) !important; color: var(--bs-info-text-emphasis) !important; }
        .table-hover > tbody > tr.table-warning:hover > td { background-color: var(--bs-warning-bg-subtle) !important; color: var(--bs-warning-text-emphasis) !important; }
        .table-hover > tbody > tr.table-danger:hover > td { background-color: var(--bs-danger-bg-subtle) !important; color: var(--bs-danger-text-emphasis) !important; }
        .table-hover > tbody > tr.table-secondary:hover > td { background-color: var(--bs-secondary-bg-subtle) !important; color: var(--bs-secondary-text-emphasis) !important; }
    </style>
    <table class="table table-hover align-middle js-admin-reorderable-table">
        <thead class="table-light">
            <tr>
                <th>Tanggal</th>
                <th>Nama SPL</th>
                <th>Nama Rekening</th>
                <th>Nama Bank</th>
                <th>Bank Tujuan</th>
                <th>Nama Server</th>
                <th>Bukti Tiket</th>
                <th>Bukti Penambahan</th>
                <th>Bukti Transfers Admin</th>
                <th>Info Staff</th>
                <th>Status</th>
                <th>Jam</th>
                <th class="text-center">Aksi Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                @php
                    $rowColorClass = '';
                    if (strtolower($item->status) === 'pending') {
                        $itemServer = trim((string)($item->server ?? ''));
                        $defaultColor = 'primary';
                        if ($itemServer !== '' && isset($serverColorsMap[$itemServer]) && !empty($serverColorsMap[$itemServer])) {
                            $defaultColor = $serverColorsMap[$itemServer];
                        }
                        $rowColorClass = 'table-' . $defaultColor;
                    }
                @endphp
                <tr data-deposit-id="{{ $item->id }}" class="{{ $rowColorClass }}">
                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->nama_supplier ?? '-' }}</td>
                    <td>{{ $item->nama_rekening }}</td>
                    <td>{{ $item->bank ?? '-' }}</td>
                    <td>{{ $item->bank_tujuan ?? '-' }}</td>
                    <td>{{ $item->server ?? '-' }}</td>
                    <td>
                        @if (!empty($item->reply_tiket))
                            <div class="mb-1">{{ $item->reply_tiket }}</div>
                        @endif
                        @if (!empty($item->reply_tiket_image))
                            <a href="{{ route('admin.deposit.reply-tiket-image', $item->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Gambar</a>
                        @elseif (empty($item->reply_tiket))
                            -
                        @endif
                    </td>
                    <td>
                        @if (!empty($item->reply_penambahan))
                            <div class="mb-1">{{ $item->reply_penambahan }}</div>
                        @endif
                        @if (!empty($item->reply_penambahan_image))
                            <a href="{{ route('admin.deposit.reply-image', $item->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Gambar</a>
                        @elseif (empty($item->reply_penambahan))
                            -
                        @endif
                    </td>
                    <td>
                        @if (($item->bukti_transfer_admin_type ?? 'text') === 'image')
                            @if (!empty($item->bukti_transfer_admin_text))
                                <div class="mb-1">{{ $item->bukti_transfer_admin_text }}</div>
                            @endif
                            <a href="{{ url('admin/deposit/' . $item->id . '/transfer-admin-image') }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Gambar</a>
                        @else
                            {{ $item->bukti_transfer_admin_text ?? '-' }}
                        @endif
                    </td>
                    <td>
                        @if ($item->is_deleted_by_staff)
                            <span class="badge bg-danger">Dihapus Staff</span>
                            <div class="small text-muted mt-1">
                                Alasan: {{ $item->staff_deleted_note ?? '-' }}
                            </div>
                            <div class="small text-muted">
                                {{ optional($item->staff_deleted_at)->format('d/m/Y H:i') ?? '' }}
                            </div>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'selesai' ? 'primary' : ($item->status === 'lunas' ? 'info' : 'warning'))) }}">
                            {{ ucfirst($item->status ?? 'pending') }}
                        </span>
                    </td>
                    <td>{{ $item->jam ? \Carbon\Carbon::parse($item->jam)->format('H:i') : '-' }}</td>
                    <td class="text-center" style="min-width:180px;">
                        <div class="d-flex gap-2 justify-content-center align-items-center flex-wrap">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailDeposit-{{ $item->id }}">Lihat</button>
                            <form method="POST" action="{{ route('admin.deposit.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus request deposit ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>

                        <div class="modal fade" id="detailDeposit-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl modal-dialog-centered modal-super-xl">
                                <div class="modal-content text-start">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Detail Request Deposit</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="{{ route('admin.deposit.update-details', $item->id) }}" class="row g-4" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="col-lg-9">
                                                <h6 class="mb-3">Edit Detail</h6>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nama Supplier</label>
                                                        <input type="text" name="nama_supplier" class="form-control" value="{{ $item->nama_supplier }}" list="adminSupplierList" placeholder="Ketik nama supplier..." required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nominal</label>
                                                        <input type="text" name="nominal" class="form-control" value="{{ (int)$item->nominal }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Deposit / Hutang</label>
                                                        <input type="text" name="jenis_transaksi" class="form-control" value="{{ $item->jenis_transaksi ?? 'deposit' }}" list="adminJenisTransaksiList" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">BANK</label>
                                                        <input type="text" name="bank" class="form-control" value="{{ $item->bank }}" list="adminBankList" placeholder="Ketik nama bank..." required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Server</label>
                                                        <input type="text" name="server" class="form-control" value="{{ $item->server }}" list="adminServerList" placeholder="Ketik server..." required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">No-Rek</label>
                                                        <input type="text" name="no_rek" class="form-control" value="{{ $item->no_rek }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nama Rekening</label>
                                                        <input type="text" name="nama_rekening" class="form-control" value="{{ $item->nama_rekening }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Reply Tiket</label>
                                                        <textarea name="reply_tiket" class="form-control js-auto-resize-textarea" rows="2">{{ $item->reply_tiket }}</textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Bukti Penambahan</label>
                                                        <textarea name="reply_penambahan" class="form-control js-auto-resize-textarea" rows="2" placeholder="Masukkan bukti penambahan">{{ ($item->reply_penambahan ?? '') === 'Menunggu Konfirmasi Admin' ? '' : ($item->reply_penambahan ?? '') }}</textarea>
                                                    </div>
                                                    <div class="col-md-6 js-transfer-text-wrap" data-target="{{ $item->id }}" style="display: {{ ($item->bukti_transfer_admin_type ?? 'text') === 'text' ? 'block' : 'none' }};">
                                                        <label class="form-label">Input Bukti Transfer Admin</label>
                                                        <textarea name="bukti_transfer_admin_text" class="form-control js-auto-resize-textarea" rows="2" placeholder="Masukkan bukti transfer admin dalam bentuk teks">{{ $item->bukti_transfer_admin_text ?? '' }}</textarea>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Tipe Bukti Transfer Admin</label>
                                                        <select name="bukti_transfer_admin_type" class="form-select js-reply-type" data-target="{{ $item->id }}" required>
                                                            <option value="text" {{ ($item->bukti_transfer_admin_type ?? 'text') === 'text' ? 'selected' : '' }}>Text</option>
                                                            <option value="image" {{ ($item->bukti_transfer_admin_type ?? 'text') === 'image' ? 'selected' : '' }}>Image</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 js-reply-image-wrap" data-target="{{ $item->id }}" style="display: {{ ($item->bukti_transfer_admin_type ?? 'text') === 'image' ? 'block' : 'none' }};">
                                                        <label class="form-label">Upload / Paste Gambar</label>
                                                        <input type="file" name="bukti_transfer_admin_image" class="form-control js-reply-image-input" data-target="{{ $item->id }}" accept="image/png,image/jpeg,image/jpg,image/webp">
                                                        <small class="text-muted d-block mt-1">Bisa Ctrl+V dari clipboard saat fokus di area paste.</small>
                                                        <div class="border rounded p-2 mt-2 js-paste-zone" data-target="{{ $item->id }}" tabindex="0" style="min-height:60px;">
                                                            Paste gambar di sini (Ctrl+V)
                                                        </div>
                                                        <div class="mt-2 js-image-preview-wrap" data-target="{{ $item->id }}" style="display:none;">
                                                            <img src="" alt="Preview" class="img-fluid rounded border js-image-preview" data-target="{{ $item->id }}" style="max-height:180px;">
                                                        </div>
                                                        @if (!empty($item->bukti_transfer_admin_image))
                                                            <a href="{{ url('admin/deposit/' . $item->id . '/transfer-admin-image') }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2">Lihat Gambar Tersimpan</a>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Jam</label>
                                                        <input type="time" name="jam" class="form-control" value="{{ $item->jam ? \Carbon\Carbon::parse($item->jam)->format('H:i') : '' }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-3">
                                                <h6 class="mb-3">Update Status</h6>
                                                <div class="d-grid gap-2">
                                                    <select name="status" class="form-select" required>
                                                        <option value="pending" {{ ($item->status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="approved" {{ ($item->status ?? 'pending') === 'approved' ? 'selected' : '' }}>ACC</option>
                                                        <option value="rejected" {{ ($item->status ?? 'pending') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                        <option value="selesai" {{ ($item->status ?? 'pending') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                        <option value="lunas" {{ ($item->status ?? 'pending') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-success btn-sm">Simpan Data & Status</button>
                                                    <div class="small mt-1 p-2 rounded bg-warning-subtle border border-warning-subtle text-warning-emphasis">
                                                        <strong>Note :</strong> jika mau merubah status menjadi ACC, input bukti tranfers nya dengan memilih tipe bukti text ataupun gambar.<br>
                                                        Jika mau merubah status menjadi Selesai, pastikan semua data terisi.
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center text-muted py-4">Belum ada deposit</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<datalist id="adminSupplierList">
    @foreach (($supplierOptions ?? collect()) as $supplierOption)
        <option value="{{ $supplierOption }}"></option>
    @endforeach
</datalist>

<datalist id="adminBankList">
    @foreach (($bankOptions ?? collect()) as $bankOption)
        <option value="{{ $bankOption }}"></option>
    @endforeach
</datalist>

<datalist id="adminServerList">
    @foreach (($serverOptions ?? collect()) as $serverOption)
        <option value="{{ $serverOption }}"></option>
    @endforeach
</datalist>

<datalist id="adminJenisTransaksiList">
    <option value="deposit"></option>
    <option value="hutang"></option>
</datalist>

<div class="mt-3">
    {{ $items->links() }}
</div>
