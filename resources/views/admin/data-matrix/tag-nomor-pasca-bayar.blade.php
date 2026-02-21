@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Data Matrix</li>
                    <li class="breadcrumb-item active">Tag Nomor Pasca Bayar</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="mb-0">Tag Nomor Pasca Bayar</h2>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.data-matrix.tag-pasca-bayar.import') }}" enctype="multipart/form-data" class="d-flex gap-2 flex-wrap align-items-center">
                            @csrf
                            <input type="file" name="file_excel" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required style="max-width:260px;">
                            <button type="submit" class="btn btn-success btn-sm">Upload Excel</button>
                        </form>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahPascaBayar">Tambah Data</button>
                    </div>
                </div>
                <small class="text-muted">Format Excel mengikuti urutan kolom tabel (No, Nomor, Atas Nama, Chip, Keterangan, Bank, Status, Tagihan, Bank, Tanggal Payment, Tagihan).</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-success text-center">
                            <tr>
                                <th rowspan="2">No.</th>
                                <th rowspan="2">Nomor</th>
                                <th rowspan="2">Atas Nama</th>
                                <th rowspan="2">Chip</th>
                                <th rowspan="2">Keterangan</th>
                                <th rowspan="2">Bank</th>
                                <th rowspan="2">Status</th>
                                <th colspan="2">Periode Des 2025</th>
                                <th colspan="2">Periode Febru 2026</th>
                                <th rowspan="2" class="text-center">Aksi</th>
                            </tr>
                            <tr>
                                <th>Tagihan</th>
                                <th>Bank</th>
                                <th>Tanggal Payment</th>
                                <th>Tagihan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $items->firstItem() + $index }}</td>
                                    <td>{{ $item->nomor }}</td>
                                    <td>{{ $item->atas_nama }}</td>
                                    <td>{{ $item->chip ?? '-' }}</td>
                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                    <td>{{ $item->bank ?? '-' }}</td>
                                    <td class="text-center">{{ $item->status }}</td>
                                    <td>{{ $item->periode_des_2025_tagihan !== null ? 'Rp' . number_format((float)$item->periode_des_2025_tagihan, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $item->periode_des_2025_bank ?? '-' }}</td>
                                    <td>{{ $item->periode_feb_2026_tanggal_payment ? $item->periode_feb_2026_tanggal_payment->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->periode_feb_2026_tagihan !== null ? 'Rp' . number_format((float)$item->periode_feb_2026_tagihan, 0, ',', '.') : '-' }}</td>
                                    <td class="text-center" style="min-width: 160px;">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditPascaBayar-{{ $item->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.data-matrix.tag-pasca-bayar.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center text-muted py-4">Belum ada data tag nomor pasca bayar</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $items->links() }}</div>

                @foreach ($items as $item)
                    <div class="modal fade" id="modalEditPascaBayar-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.data-matrix.tag-pasca-bayar.update', $item->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Tag Nomor Pasca Bayar</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @include('admin.data-matrix.partials.tag-pasca-bayar-fields', ['item' => $item])
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

<div class="modal fade" id="modalTambahPascaBayar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.data-matrix.tag-pasca-bayar.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tag Nomor Pasca Bayar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.data-matrix.partials.tag-pasca-bayar-fields', ['item' => null])
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
        const allModals = [
            ...document.querySelectorAll('[id^="modalEditPascaBayar-"]'),
            document.getElementById('modalTambahPascaBayar')
        ].filter(Boolean);

        allModals.forEach(function (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
        });
    })();
</script>
@endpush
