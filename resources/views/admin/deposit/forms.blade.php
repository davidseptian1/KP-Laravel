@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Form Deposit</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Form Deposit (Share Link)</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Form</h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreate">
                    <i class="ti ti-plus me-1"></i>Buat Form
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Judul</th>
                                <th>Link</th>
                                <th>Expired</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->kode_form }}</td>
                                    <td>{{ $item->title }}</td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" value="{{ route('deposit.form.show', $item->token) }}" readonly>
                                    </td>
                                    <td>{{ $item->expires_at ? $item->expires_at->format('d M Y') : '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">
                                            {{ $item->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('admin.deposit.forms.toggle', $item->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-sm btn-outline-{{ $item->is_active ? 'danger' : 'success' }}">
                                                {{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada form deposit</td>
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

<div class="modal fade" id="modalCreate" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.deposit.forms.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buat Form Deposit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Judul</label>
                            <input type="text" name="title" class="form-control" required />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Expired (opsional)</label>
                            <input type="date" name="expires_at" class="form-control" />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Buat Form</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
