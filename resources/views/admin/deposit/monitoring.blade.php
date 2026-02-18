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
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Supplier</th>
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
                                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>{{ $item->bank }}</td>
                                    <td>{{ $item->server }}</td>
                                    <td>{{ $item->no_rek }}</td>
                                    <td>{{ $item->nama_rekening }}</td>
                                    <td>{{ $item->reply_tiket ?? '-' }}</td>
                                    <td>{{ $item->reply_penambahan ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($item->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>{{ $item->jam ? \Carbon\Carbon::parse($item->jam)->format('H:i') : '-' }}</td>
                                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="text-center" style="min-width:280px;">
                                        <form method="POST" action="{{ route('admin.deposit.update', $item->id) }}" class="d-flex flex-column gap-2">
                                            @csrf
                                            @method('PUT')
                                            <input type="text" name="reply_penambahan" class="form-control form-control-sm" value="{{ $item->reply_penambahan ?? 'Menunggu Konfirmasi Admin' }}" required>
                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="pending" {{ ($item->status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="approved" {{ ($item->status ?? 'pending') === 'approved' ? 'selected' : '' }}>ACC</option>
                                                <option value="rejected" {{ ($item->status ?? 'pending') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                            </select>
                                            <button class="btn btn-sm btn-success">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">Belum ada deposit</td>
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
