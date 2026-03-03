<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>Tanggal</th>
                <th>Nama Rekening</th>
                <th>Nama Server</th>
                <th>Bukti Tiket</th>
                <th>Bukti Penambahan</th>
                <th>Bukti Transfers Admin</th>
                <th>Status</th>
                <th>Jam</th>
                <th class="text-center">Aksi Admin</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr data-deposit-id="{{ $item->id }}" class="{{ (int)($latestIncomingId ?? 0) === (int)$item->id ? 'latest-row-highlight' : '' }}">
                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                    <td>{{ $item->nama_rekening }}</td>
                    <td>{{ $item->server ?? '-' }}</td>
                    <td>{{ $item->reply_tiket ?? '-' }}</td>
                    <td>{{ $item->reply_penambahan ?? '-' }}</td>
                    <td>
                        @if (($item->bukti_transfer_admin_type ?? 'text') === 'image')
                            @if (!empty($item->bukti_transfer_admin_text))
                                <div class="mb-1">{{ $item->bukti_transfer_admin_text }}</div>
                            @endif
                            <a href="{{ route('admin.deposit.transfer-admin-image', $item->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">Lihat Gambar</a>
                        @else
                            {{ $item->bukti_transfer_admin_text ?? '-' }}
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'selesai' ? 'primary' : 'warning')) }}">
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
                                                        <input type="text" name="nama_supplier" class="form-control" value="{{ $item->nama_supplier }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Nominal</label>
                                                        <input type="text" name="nominal" class="form-control" value="{{ (int)$item->nominal }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Deposit / Hutang</label>
                                                        <select name="jenis_transaksi" class="form-select" required>
                                                            <option value="deposit" {{ ($item->jenis_transaksi ?? 'deposit') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                                                            <option value="hutang" {{ ($item->jenis_transaksi ?? 'deposit') === 'hutang' ? 'selected' : '' }}>Hutang</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">BANK</label>
                                                        <input type="text" name="bank" class="form-control" value="{{ $item->bank }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Server</label>
                                                        <input type="text" name="server" class="form-control" value="{{ $item->server }}" required>
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
                                                            <a href="{{ route('admin.deposit.transfer-admin-image', $item->id) }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2">Lihat Gambar Tersimpan</a>
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
                                                    </select>
                                                    <button type="submit" class="btn btn-success btn-sm">Simpan Data & Status</button>
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
                    <td colspan="9" class="text-center text-muted py-4">Belum ada deposit</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $items->links() }}
</div>
