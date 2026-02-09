@extends('layouts/app')

@section('content')

<!-- [ breadcrumb ] start -->
<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active">
                        Report Khusus
                    </li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Report Khusus</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row mt-0">
    <div class="col-12">
        <div class="card shadow-sm">

            {{-- HEADER --}}
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <form method="GET" class="d-flex gap-2">
                        <select name="bulan" class="form-select form-select-sm">
                            <option value="">Semua Bulan</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>

                        <select name="tahun" class="form-select form-select-sm">
                            <option value="">Semua Tahun</option>
                            @for ($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ request('tahun') == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>

                        <button class="btn btn-primary btn-sm">
                            <i class="ti ti-eye me-1"></i>Tampilkan
                        </button>
                    </form>

                    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                        <i class="ti ti-plus me-1"></i>Tambah Data
                    </button>
                </div>
            </div>

            <!-- Modal Tambah Report -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('admin.report.khusus.store') }}" method="POST">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Report Khusus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Server</label>
                            <input type="text" name="server" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Nama</label>
                            <input type="text" name="nama" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <input type="text" name="supplier" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Produk</label>
                            <input type="text" name="produk" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nomor Tujuan</label>
                            <input type="text" name="nomor_tujuan" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Total</label>
                            <input type="number" name="total" class="form-control" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Catatan</label>
                            <textarea name="note" class="form-control" rows="3"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">
                        Simpan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

            {{-- TABLE --}}
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Tanggal</th>
                                <th>Server</th>
                                <th>Nama</th>
                                <th>Supplier</th>
                                <th>Produk</th>
                                <th>Nomor Tujuan</th>
                                <th class="text-end">Total</th>
                                <th>Note</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reports as $r)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}</td>
                                    <td>{{ $r->server }}</td>
                                    <td>{{ $r->nama }}</td>
                                    <td>{{ $r->supplier }}</td>
                                    <td>{{ $r->produk }}</td>
                                    <td>{{ $r->nomor_tujuan }}</td>
                                    <td class="text-end fw-semibold">
                                        Rp {{ number_format($r->total, 0, ',', '.') }}
                                    </td>
                                    <td>{{ $r->note }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-warning btn-sm btn-edit" 
                                                data-id="{{ $r->id }}"
                                                data-tanggal="{{ $r->tanggal }}"
                                                data-server="{{ $r->server }}"
                                                data-nama="{{ $r->nama }}"
                                                data-supplier="{{ $r->supplier }}"
                                                data-produk="{{ $r->produk }}"
                                                data-nomor="{{ $r->nomor_tujuan }}"
                                                data-total="{{ $r->total }}"
                                                data-note="{{ $r->note }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalEdit">
                                            <i class="ti ti-edit"></i>
                                        </button>

                                        <form action="{{ route('admin.report.khusus.destroy', $r->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Hapus data ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        Tidak ada data report khusus
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Edit Report (Generic) -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Report Khusus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>
                            <input type="date" id="editTanggal" name="tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Server</label>
                            <input type="text" id="editServer" name="server" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nama</label>
                            <input type="text" id="editNama" name="nama" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Supplier</label>
                            <input type="text" id="editSupplier" name="supplier" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Produk</label>
                            <input type="text" id="editProduk" name="produk" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Tujuan</label>
                            <input type="text" id="editNomor" name="nomor_tujuan" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total</label>
                            <input type="number" id="editTotal" name="total" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Catatan</label>
                            <textarea id="editNote" name="note" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="submit">Update Data</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('formEdit').action = `/report-khusus/${id}`;
        document.getElementById('editTanggal').value = this.dataset.tanggal;
        document.getElementById('editServer').value = this.dataset.server;
        document.getElementById('editNama').value = this.dataset.nama;
        document.getElementById('editSupplier').value = this.dataset.supplier;
        document.getElementById('editProduk').value = this.dataset.produk;
        document.getElementById('editNomor').value = this.dataset.nomor;
        document.getElementById('editTotal').value = this.dataset.total;
        document.getElementById('editNote').value = this.dataset.note || '';
    });
});
</script>

@endsection
