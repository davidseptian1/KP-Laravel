@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Data Matrix</li>
                    <li class="breadcrumb-item active">Tag PLN & Internet</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h2 class="mb-0">Tag PLN & Internet</h2>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.data-matrix.tag-pln-internet.import') }}" enctype="multipart/form-data" class="d-flex gap-2 flex-wrap align-items-center">
                            @csrf
                            <input type="file" name="file_excel" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required style="max-width:260px;">
                            <button type="submit" class="btn btn-success btn-sm">Upload Excel</button>
                        </form>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahTagPlnInternet">Tambah Data</button>
                    </div>
                </div>
                <small class="text-muted">Format Excel: No, Nama, Nomor PLN & Internet, Atas Nama, Bank, Keterangan, Tagihan Jan, Tanggal Payment Jan, Tagihan Feb, Tanggal Payment Feb.</small>
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
                                <th rowspan="2">No</th>
                                <th rowspan="2">Nama</th>
                                <th rowspan="2">Nomor PLN & Internet</th>
                                <th rowspan="2">Atas Nama</th>
                                <th rowspan="2">Bank</th>
                                <th rowspan="2">Keterangan</th>
                                <th colspan="2">Periode JANUARI 2026</th>
                                <th colspan="2">Periode FEBRUARI 2026</th>
                                <th rowspan="2">Aksi</th>
                            </tr>
                            <tr>
                                <th>Tagihan</th>
                                <th>Tanggal Payment</th>
                                <th>Tagihan</th>
                                <th>Tanggal Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $items->firstItem() + $index }}</td>
                                    <td>{{ $item->nama }}</td>
                                    <td>{{ $item->nomor_pln_internet }}</td>
                                    <td>{{ $item->atas_nama }}</td>
                                    <td>{{ $item->bank ?? '-' }}</td>
                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                    <td>{{ $item->periode_januari_2026_tagihan !== null ? 'Rp' . number_format((float)$item->periode_januari_2026_tagihan, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $item->periode_januari_2026_tanggal_payment ? $item->periode_januari_2026_tanggal_payment->format('d/m/Y') : '-' }}</td>
                                    <td>{{ $item->periode_februari_2026_tagihan !== null ? 'Rp' . number_format((float)$item->periode_februari_2026_tagihan, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $item->periode_februari_2026_tanggal_payment ? $item->periode_februari_2026_tanggal_payment->format('d/m/Y') : '-' }}</td>
                                    <td class="text-center" style="min-width:160px;">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditTagPlnInternet-{{ $item->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.data-matrix.tag-pln-internet.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">Belum ada data tag PLN & Internet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $items->links() }}</div>

                @foreach ($items as $item)
                    <div class="modal fade" id="modalEditTagPlnInternet-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-xl modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.data-matrix.tag-pln-internet.update', $item->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Tag PLN & Internet</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        @include('admin.data-matrix.partials.tag-pln-internet-fields', ['item' => $item])
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

<div class="modal fade" id="modalTambahTagPlnInternet" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.data-matrix.tag-pln-internet.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Tag PLN & Internet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('admin.data-matrix.partials.tag-pln-internet-fields', ['item' => null])
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
            ...document.querySelectorAll('[id^="modalEditTagPlnInternet-"]'),
            document.getElementById('modalTambahTagPlnInternet')
        ].filter(Boolean);

        allModals.forEach(function (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
        });
    })();
</script>
@endpush
