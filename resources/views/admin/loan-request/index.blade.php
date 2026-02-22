@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Peminjaman Barang</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Monitoring Peminjaman Barang</h2>
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
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm" placeholder="Kode / Server / Barang" style="max-width: 240px;" />
                    <button class="btn btn-primary btn-sm">Filter</button>
                </form>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Form</th>
                                <th>Nama Server</th>
                                <th>Nomor HP</th>
                                <th>Barang</th>
                                <th>Tgl Pinjam</th>
                                <th>Tgl Balik</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->kode_pengajuan }}</td>
                                    <td>{{ $item->form?->kode_form ?? '-' }}</td>
                                    <td>{{ $item->nama_server }}</td>
                                    <td>{{ $item->nomor_hp }}</td>
                                    <td>{{ $item->barang_dipinjam }}</td>
                                    <td>{{ optional($item->tanggal_pinjam)->format('d/m/Y H:i') }}</td>
                                    <td>{{ $item->tanggal_kembali ? $item->tanggal_kembali->format('d/m/Y H:i') : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'revision' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="min-width:180px;">
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailLoanRequest-{{ $item->id }}">Lihat</button>
                                            <form method="POST" action="{{ route('admin.loan-request.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus peminjaman barang ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>

                                        <div class="modal fade" id="detailLoanRequest-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Peminjaman - {{ $item->kode_pengajuan }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Nama Server</label>
                                                                <div class="fw-semibold">{{ $item->nama_server }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Nomor HP</label>
                                                                <div class="fw-semibold">{{ $item->nomor_hp }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Barang Dipinjam</label>
                                                                <div class="fw-semibold">{{ $item->barang_dipinjam }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Tanggal Pinjam</label>
                                                                <div class="fw-semibold">{{ optional($item->tanggal_pinjam)->format('d/m/Y H:i') }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Tanggal Kembali</label>
                                                                <div class="fw-semibold">{{ $item->tanggal_kembali ? $item->tanggal_kembali->format('d/m/Y H:i') : '-' }}</div>
                                                            </div>
                                                            <div class="col-12"><hr class="my-1"></div>
                                                            <div class="col-12">
                                                                <form method="POST" action="{{ route('admin.loan-request.update', $item->id) }}" class="row g-2">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Status</label>
                                                                        <select name="status" class="form-select form-select-sm" required>
                                                                            @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'revision' => 'Revision'] as $key => $label)
                                                                                <option value="{{ $key }}" {{ $item->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <label class="form-label">Catatan Admin</label>
                                                                        <input type="text" name="catatan_admin" value="{{ $item->catatan_admin }}" class="form-control form-control-sm" placeholder="Catatan admin" />
                                                                    </div>
                                                                    <div class="col-12 text-end mt-2">
                                                                        <button class="btn btn-success btn-sm">Update + Kirim WA</button>
                                                                    </div>
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
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada peminjaman barang</td>
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
