@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Pengajuan Data</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Monitoring Pengajuan Data</h2>
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
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm" placeholder="Kode / Nama / Username" style="max-width: 240px;" />
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
                                <th>Aplikasi</th>
                                <th>Username</th>
                                <th>Nomor HP</th>
                                <th>Nama Pemohon</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->kode_pengajuan }}</td>
                                    <td>{{ $item->form?->kode_form ?? '-' }}</td>
                                    <td>{{ strtoupper($item->aplikasi) }}</td>
                                    <td>{{ $item->username_akun }}</td>
                                    <td>{{ $item->nomor_hp }}</td>
                                    <td>{{ $item->nama_pemohon }}</td>
                                    <td>{{ $item->jenis_perubahan }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'revision' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="min-width:180px;">
                                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detailDataRequest-{{ $item->id }}">Lihat</button>
                                            <form method="POST" action="{{ route('admin.data-request.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus pengajuan data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                            </form>
                                        </div>

                                        <div class="modal fade" id="detailDataRequest-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Detail Pengajuan Data - {{ $item->kode_pengajuan }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Aplikasi</label>
                                                                <div class="fw-semibold">{{ strtoupper($item->aplikasi) }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Jenis Perubahan</label>
                                                                <div class="fw-semibold">{{ $item->jenis_perubahan }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Username</label>
                                                                <div class="fw-semibold">{{ $item->username_akun }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Nama Pemohon</label>
                                                                <div class="fw-semibold">{{ $item->nama_pemohon }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Nomor HP</label>
                                                                <div class="fw-semibold">{{ $item->nomor_hp }}</div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label mb-1">Lampiran</label>
                                                                <div class="d-flex gap-2 flex-wrap">
                                                                    <a class="btn btn-outline-secondary btn-sm" target="_blank" href="{{ route('admin.data-request.view', [$item->id, 'ktp']) }}">Lihat KTP</a>
                                                                    <a class="btn btn-outline-secondary btn-sm" target="_blank" href="{{ route('admin.data-request.view', [$item->id, 'selfie']) }}">Lihat Selfie</a>
                                                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.data-request.download', [$item->id, 'ktp']) }}">Download KTP</a>
                                                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.data-request.download', [$item->id, 'selfie']) }}">Download Selfie</a>
                                                                </div>
                                                            </div>
                                                            <div class="col-12"><hr class="my-1"></div>
                                                            <div class="col-12">
                                                                <form method="POST" action="{{ route('admin.data-request.update', $item->id) }}" class="row g-2">
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
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada pengajuan data</td>
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
