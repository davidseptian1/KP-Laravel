@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Reimburse</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Pengajuan Reimburse</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Form Reimburse</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('reimburse.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Divisi</label>
                        <select name="divisi" class="form-select" required>
                            <option value="">Pilih Divisi</option>
                            @foreach (['accounting','act','server','hrd','direksi','gudang','sosmed','host live','it'] as $divisi)
                                <option value="{{ $divisi }}">{{ strtoupper($divisi) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal</label>
                        <input type="number" name="nominal" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan (Deskripsi)</label>
                        <textarea name="keperluan" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti (jpg/png/pdf) - bisa lebih dari 1</label>
                        <input type="file" name="bukti[]" class="form-control" multiple required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Admin (Tujuan)</label>
                        <input type="text" class="form-control" value="{{ config('whatsapp.admin_numbers')[0] ?? '' }}" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Pengisi</label>
                        <input type="text" name="wa_pengisi" class="form-control" placeholder="628xxxxxxxxxx" required />
                    </div>
                    <button class="btn btn-primary w-100">Kirim Pengajuan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Status Pengajuan</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th>Divisi</th>
                                <th>Barang</th>
                                <th class="text-end">Nominal</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->kode_reimburse }}</td>
                                    <td>{{ $item->nama ?? '-' }}</td>
                                    <td>{{ $item->divisi ?? '-' }}</td>
                                    <td>{{ $item->nama_barang ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>{{ optional($item->tanggal_pengajuan)->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'revision' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->catatan_admin ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada pengajuan</td>
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
