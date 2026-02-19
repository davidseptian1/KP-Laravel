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
                    <h2 class="mb-0">Monitoring Reimburse</h2>
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
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control form-control-sm" placeholder="Kode / Nama User" style="max-width: 240px;" />
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
                                <th>Nama</th>
                                <th>Divisi</th>
                                <th>No Rekening</th>
                                <th>Metode</th>
                                <th>No/ID</th>
                                <th>Atas Nama</th>
                                <th>Barang</th>
                                <th class="text-end">Nominal</th>
                                <th>WA Penerima</th>
                                <th>WA Pengisi</th>
                                <th>Bukti Pembayaran</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th class="text-center" style="position: sticky; right: 0; background: #f8f9fa; z-index: 2; min-width: 120px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->kode_reimburse }}</td>
                                    <td>{{ $item->form?->kode_form ?? '-' }}</td>
                                    <td>{{ $item->nama ?? '-' }}</td>
                                    <td>{{ $item->divisi ?? '-' }}</td>
                                    <td>{{ $item->no_rekening ?? '-' }}</td>
                                    <td>
                                        @if ($item->payment_method)
                                            {{ $item->payment_method === 'ewallet' ? 'E-Wallet' : 'Bank' }}
                                            @if ($item->payment_provider)
                                                ({{ strtoupper($item->payment_provider) }})
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $item->payment_account_number ?? '-' }}</td>
                                    <td>{{ $item->payment_account_name ?? '-' }}</td>
                                    <td>{{ $item->nama_barang ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>{{ $item->wa_penerima ?? '-' }}</td>
                                    <td>{{ $item->wa_pengisi ?? '-' }}</td>
                                    <td>
                                        @if ($item->payment_proof_type === 'text')
                                            {{ $item->payment_proof_text ?? '-' }}
                                        @elseif ($item->payment_proof_type === 'image' && $item->payment_proof_image)
                                            <a target="_blank" href="{{ route('admin.reimburse.payment-proof', $item->id) }}">Lihat</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ optional($item->tanggal_pengajuan)->format('d M Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'revision' ? 'warning' : 'secondary')) }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->catatan_admin ?? '-' }}</td>
                                    <td class="text-center" style="position: sticky; right: 0; background: #fff; z-index: 1; min-width: 120px;">
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#aksiModal-{{ $item->id }}">Lihat</button>

                                        <div class="modal fade" id="aksiModal-{{ $item->id }}" tabindex="-1" aria-labelledby="aksiModalLabel-{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="aksiModalLabel-{{ $item->id }}">Aksi Reimburse - {{ $item->kode_reimburse }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex flex-column gap-3">
                                                            <div class="border rounded p-3 bg-light">
                                                                <div class="fw-semibold">No Rekening</div>
                                                                <div>{{ $item->no_rekening ?? '-' }}</div>
                                                                <div class="mt-2">
                                                                    <div><span class="fw-semibold">Metode:</span> {{ $item->payment_method ? ($item->payment_method === 'ewallet' ? 'E-Wallet' : 'Bank') : '-' }}</div>
                                                                    <div><span class="fw-semibold">Provider:</span> {{ $item->payment_provider ? strtoupper($item->payment_provider) : '-' }}</div>
                                                                    <div><span class="fw-semibold">No/ID:</span> {{ $item->payment_account_number ?? '-' }}</div>
                                                                    <div><span class="fw-semibold">Atas Nama:</span> {{ $item->payment_account_name ?? '-' }}</div>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <a class="btn btn-outline-secondary" target="_blank" href="{{ route('admin.reimburse.view', [$item->id, 0]) }}">Lihat Bukti</a>
                                                                <a class="btn btn-outline-primary" href="{{ route('admin.reimburse.download', $item->id) }}">Download Bukti</a>
                                                            </div>

                                                            @if (!empty($item->bukti_files) && count($item->bukti_files) > 1)
                                                                <div class="small text-muted">
                                                                    Bukti lain:
                                                                    @foreach ($item->bukti_files as $idx => $file)
                                                                        <a target="_blank" href="{{ route('admin.reimburse.view', [$item->id, $idx]) }}">#{{ $idx + 1 }}</a>
                                                                    @endforeach
                                                                </div>
                                                            @endif

                                                            <form method="POST" action="{{ route('admin.reimburse.update', $item->id) }}" class="d-flex flex-column gap-3" enctype="multipart/form-data">
                                                                @csrf
                                                                @method('PUT')
                                                                <select name="status" class="form-select" required>
                                                                    @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'revision' => 'Revision'] as $key => $label)
                                                                        <option value="{{ $key }}" {{ $item->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <label class="form-label text-start mb-0">Bukti Pembayaran</label>
                                                                <select name="payment_proof_type" class="form-select">
                                                                    <option value="">Bukti Pembayaran (opsional)</option>
                                                                    <option value="text" {{ $item->payment_proof_type === 'text' ? 'selected' : '' }}>Teks</option>
                                                                    <option value="image" {{ $item->payment_proof_type === 'image' ? 'selected' : '' }}>Gambar</option>
                                                                </select>
                                                                <input type="text" name="payment_proof_text" value="{{ $item->payment_proof_text }}" class="form-control" placeholder="Isi bukti pembayaran (teks)" />
                                                                <input type="file" name="payment_proof_image" class="form-control" accept="image/*" />
                                                                <label class="form-label text-start mb-0">Catatan Admin</label>
                                                                <input type="text" name="catatan_admin" value="{{ $item->catatan_admin }}" class="form-control" placeholder="Catatan admin" />
                                                                <button class="btn btn-success">Update + Kirim WA</button>
                                                            </form>

                                                            <form method="POST" action="{{ route('admin.reimburse.destroy', $item->id) }}" onsubmit="return confirm('Yakin ingin menghapus data reimburse ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Hapus</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data reimburse</td>
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
