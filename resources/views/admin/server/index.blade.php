@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Server Manajemen</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="mb-0">Server Manajemen</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahServer">
                        Tambah Server
                    </button>
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
                                <th style="width:80px;">No</th>
                                <th>Nama Server</th>
                                <th>Dibuat</th>
                                <th class="text-center" style="width:220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->nama_server }}</td>
                                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditServer-{{ $item->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.server.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus server ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada riwayat server</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @foreach ($items as $item)
                    <div class="modal fade" id="modalEditServer-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.server.update', $item->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Server</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Nama Server</label>
                                        <input type="text" name="nama_server" class="form-control" value="{{ $item->nama_server }}" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambahServer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.server.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Server</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Nama Server</label>
                    <input type="text" name="nama_server" class="form-control" placeholder="Masukkan nama server" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        document.querySelectorAll('[id^="modalEditServer-"]').forEach(function (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
        });

        const tambahModal = document.getElementById('modalTambahServer');
        if (tambahModal && tambahModal.parentElement !== document.body) {
            document.body.appendChild(tambahModal);
        }
    })();
</script>
@endpush
