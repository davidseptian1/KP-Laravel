@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Rek Manual Manajement</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="mb-0">Rek Manual Manajement</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahRekManual">
                        Tambah Rek Manual
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
                                <th>Bank Tujuan</th>
                                <th>No Rekening</th>
                                <th>Nama Rekening</th>
                                <th>Keterangan</th>
                                <th>Dibuat</th>
                                <th class="text-center" style="width:220px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->bank_tujuan }}</td>
                                    <td>{{ $item->no_rek }}</td>
                                    <td>{{ $item->nama_rekening }}</td>
                                    <td>{{ $item->keterangan ?: '-' }}</td>
                                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditRekManual-{{ $item->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.rek-manual.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada data rek manual</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @foreach ($items as $item)
                    <div class="modal fade" id="modalEditRekManual-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.rek-manual.update', $item->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Rek Manual</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label">Bank Tujuan</label>
                                        <input type="text" name="bank_tujuan" class="form-control" value="{{ $item->bank_tujuan }}" required>

                                        <label class="form-label mt-3">No Rekening</label>
                                        <input type="text" name="no_rek" class="form-control" value="{{ $item->no_rek }}" required>

                                        <label class="form-label mt-3">Nama Rekening</label>
                                        <input type="text" name="nama_rekening" class="form-control" value="{{ $item->nama_rekening }}" required>

                                        <label class="form-label mt-3">Keterangan</label>
                                        <textarea name="keterangan" class="form-control" rows="2">{{ $item->keterangan }}</textarea>
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

<div class="modal fade" id="modalTambahRekManual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.rek-manual.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Rek Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Bank Tujuan</label>
                    <input type="text" name="bank_tujuan" class="form-control" placeholder="Contoh: BCA" required>

                    <label class="form-label mt-3">No Rekening</label>
                    <input type="text" name="no_rek" class="form-control" placeholder="Masukkan nomor rekening" required>

                    <label class="form-label mt-3">Nama Rekening</label>
                    <input type="text" name="nama_rekening" class="form-control" placeholder="Masukkan nama rekening" required>

                    <label class="form-label mt-3">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Opsional"></textarea>
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
        document.querySelectorAll('[id^="modalEditRekManual-"]').forEach(function (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
        });

        const tambahModal = document.getElementById('modalTambahRekManual');
        if (tambahModal && tambahModal.parentElement !== document.body) {
            document.body.appendChild(tambahModal);
        }
    })();
</script>
@endpush
