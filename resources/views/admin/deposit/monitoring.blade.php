@extends('layouts/app')

@section('content')

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
                <form method="GET" class="row g-2 align-items-end mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Search by Server</label>
                        <input type="text" name="server" class="form-control form-control-sm" value="{{ $server ?? '' }}" placeholder="Contoh: server-1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate ?? '' }}">
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
                        <a href="{{ route('admin.deposit.monitoring') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Supplier</th>
                                <th>Jenis</th>
                                <th class="text-end">Nominal</th>
                                <th>BANK</th>
                                <th>Server</th>
                                <th>No-Rek</th>
                                <th>Nama Rekening</th>
                                <th>Reply Tiket</th>
                                <th>Reply Penambahan</th>
                                <th>Status</th>
                                <th>Jam</th>
                                <th>Tanggal</th>
                                <th class="text-center">Aksi Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->nama_supplier }}</td>
                                    <td>{{ strtoupper($item->jenis_transaksi ?? 'deposit') }}</td>
                                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>{{ $item->bank }}</td>
                                    <td>{{ $item->server }}</td>
                                    <td>{{ $item->no_rek }}</td>
                                    <td>{{ $item->nama_rekening }}</td>
                                    <td>{{ $item->reply_tiket ?? '-' }}</td>
                                    <td>{{ $item->reply_penambahan ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'selesai' ? 'primary' : 'warning')) }}">
                                            {{ ucfirst($item->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>{{ $item->jam ? \Carbon\Carbon::parse($item->jam)->format('H:i') : '-' }}</td>
                                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
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
                                            <div class="modal-dialog modal-xl modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Request Deposit</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row g-4">
                                                            <div class="col-lg-8">
                                                                <h6 class="mb-3">Edit Detail</h6>
                                                                <form method="POST" action="{{ route('admin.deposit.update-details', $item->id) }}" class="row g-3">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Nama Supplier</label>
                                                                        <input type="text" name="nama_supplier" class="form-control" value="{{ $item->nama_supplier }}" required>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Nominal</label>
                                                                        <input type="number" name="nominal" class="form-control" value="{{ (int)$item->nominal }}" required>
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
                                                                        <textarea name="reply_tiket" class="form-control" rows="2">{{ $item->reply_tiket }}</textarea>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Reply Penambahan</label>
                                                                        <textarea name="reply_penambahan" class="form-control" rows="2" required>{{ $item->reply_penambahan }}</textarea>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label class="form-label">Jam</label>
                                                                        <input type="time" name="jam" class="form-control" value="{{ $item->jam ? \Carbon\Carbon::parse($item->jam)->format('H:i') : '' }}" required>
                                                                    </div>
                                                                    <div class="col-12 text-end">
                                                                        <button type="submit" class="btn btn-primary btn-sm">Simpan Edit</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <h6 class="mb-3">Update Status</h6>
                                                                <form method="POST" action="{{ route('admin.deposit.update-status', $item->id) }}" class="d-grid gap-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <select name="status" class="form-select" required>
                                                                        <option value="approved" {{ ($item->status ?? 'pending') === 'approved' ? 'selected' : '' }}>ACC</option>
                                                                        <option value="rejected" {{ ($item->status ?? 'pending') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                                        <option value="selesai" {{ ($item->status ?? 'pending') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                                    </select>
                                                                    <button type="submit" class="btn btn-success btn-sm">Simpan Status</button>
                                                                </form>
                                                            </div>
                                                        </div>
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

                <div class="mt-3">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
