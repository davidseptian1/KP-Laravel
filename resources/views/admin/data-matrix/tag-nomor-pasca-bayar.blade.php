@extends('layouts/app')

@section('content')

@php($bulanMap = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'])

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
                                <th>No.</th>
                                <th>Nomor</th>
                                <th>Atas Nama</th>
                                <th>Chip</th>
                                <th>Keterangan</th>
                                <th>Bank</th>
                                <th>Status</th>
                                <th>Periode Terbaru</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                @php($latestPeriod = $item->periods->first())
                                <tr>
                                    <td class="text-center">{{ $items->firstItem() + $index }}</td>
                                    <td>{{ $item->nomor }}</td>
                                    <td>{{ $item->atas_nama }}</td>
                                    <td>{{ $item->chip ?? '-' }}</td>
                                    <td>{{ $item->keterangan ?? '-' }}</td>
                                    <td>{{ $item->bank ?? '-' }}</td>
                                    <td class="text-center">{{ $item->status }}</td>
                                    <td>
                                        @if($latestPeriod)
                                            {{ $bulanMap[$latestPeriod->periode_bulan] ?? $latestPeriod->periode_bulan }}/{{ $latestPeriod->periode_tahun }}
                                            - {{ $latestPeriod->tagihan !== null ? 'Rp' . number_format((float)$latestPeriod->tagihan, 0, ',', '.') : '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center" style="min-width: 250px;">
                                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle-expand="pasca-period-{{ $item->id }}">Periode</button>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalTambahPeriodePasca-{{ $item->id }}">+ Periode</button>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEditPascaBayar-{{ $item->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.data-matrix.tag-pasca-bayar.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr id="pasca-period-{{ $item->id }}" style="display:none;">
                                    <td colspan="9" class="bg-light">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-light text-center">
                                                    <tr>
                                                        <th style="width:120px;">Periode</th>
                                                        <th>Tagihan</th>
                                                        <th>Bank</th>
                                                        <th>Tanggal Payment</th>
                                                        <th style="width:100px;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($item->periods as $period)
                                                        <tr>
                                                            <td class="text-center">{{ $bulanMap[$period->periode_bulan] ?? $period->periode_bulan }}/{{ $period->periode_tahun }}</td>
                                                            <td>{{ $period->tagihan !== null ? 'Rp' . number_format((float)$period->tagihan, 0, ',', '.') : '-' }}</td>
                                                            <td>{{ $period->bank ?? '-' }}</td>
                                                            <td>{{ $period->tanggal_payment ? $period->tanggal_payment->format('d/m/Y') : '-' }}</td>
                                                            <td class="text-center">
                                                                <form method="POST" action="{{ route('admin.data-matrix.tag-pasca-bayar.periods.destroy', [$item->id, $period->id]) }}" onsubmit="return confirm('Hapus periode ini?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">Belum ada periode</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada data tag nomor pasca bayar</td>
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

                @foreach ($items as $item)
                    <div class="modal fade" id="modalTambahPeriodePasca-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form method="POST" action="{{ route('admin.data-matrix.tag-pasca-bayar.periods.store', $item->id) }}">
                                    @csrf
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tambah Periode - {{ $item->nomor }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label">Bulan</label>
                                                <select name="periode_bulan" class="form-select" required>
                                                    @foreach($bulanMap as $num => $nama)
                                                        <option value="{{ $num }}">{{ $nama }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label">Tahun</label>
                                                <input type="number" name="periode_tahun" class="form-control" min="2000" max="2100" value="{{ now()->year }}" required>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Tagihan</label>
                                                <input type="number" step="0.01" min="0" name="tagihan" class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Bank</label>
                                                <input type="text" name="bank" class="form-control">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label">Tanggal Payment</label>
                                                <input type="date" name="tanggal_payment" class="form-control">
                                            </div>
                                        </div>
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
            ...document.querySelectorAll('[id^="modalTambahPeriodePasca-"]'),
            document.getElementById('modalTambahPascaBayar')
        ].filter(Boolean);

        allModals.forEach(function (modalEl) {
            if (modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
        });

        document.querySelectorAll('[data-toggle-expand]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-toggle-expand');
                const row = document.getElementById(targetId);
                if (!row) return;
                row.style.display = row.style.display === 'none' ? '' : 'none';
            });
        });
    })();
</script>
@endpush
