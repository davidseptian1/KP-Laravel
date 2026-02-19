@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Request Deposit</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Request Deposit</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRequestDeposit">
                    Request Deposit +
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>NAMA SUPPLIER</th>
                                <th>JENIS</th>
                                <th class="text-end">NOMINAL</th>
                                <th>BANK</th>
                                <th>SERVER</th>
                                <th>NOREK</th>
                                <th>NAMA REKENING</th>
                                <th>Reply Tiket</th>
                                <th>REPLY PENAMBAHAN</th>
                                <th>STATUS</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->nama_supplier }}</td>
                                    <td>{{ strtoupper($item->jenis_transaksi ?? 'deposit') }}</td>
                                    <td class="text-end">{{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>{{ $item->bank }}</td>
                                    <td>{{ $item->server }}</td>
                                    <td>{{ $item->no_rek }}</td>
                                    <td>{{ $item->nama_rekening }}</td>
                                    <td>{{ $item->reply_tiket ?? '-' }}</td>
                                    <td>{{ $item->reply_penambahan ?? 'Menunggu Konfirmasi Admin' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $item->status === 'approved' ? 'success' : ($item->status === 'rejected' ? 'danger' : ($item->status === 'selesai' ? 'primary' : 'warning')) }}">
                                            {{ ucfirst($item->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if (($item->status ?? 'pending') === 'approved')
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalInputReply-{{ $item->id }}">Input</button>

                                            <div class="modal fade" id="modalInputReply-{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST" action="{{ route('deposit.request.reply.update', $item->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Input Reply Penambahan</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <label class="form-label">Reply Penambahan</label>
                                                                <textarea name="reply_penambahan" class="form-control" rows="4" required>{{ $item->reply_penambahan }}</textarea>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">Belum ada request deposit</td>
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

<div class="modal fade" id="modalRequestDeposit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('deposit.request.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Request Deposit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="form_id" value="{{ $activeForms->first()->id ?? '' }}">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nama Supplier</label>
                            <select name="nama_supplier" class="form-select" required>
                                <option value="">Pilih Supplier</option>
                                @foreach (($suppliers ?? collect()) as $supplier)
                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                @endforeach
                            </select>
                            @if (($suppliers ?? collect())->isEmpty())
                                <small class="text-danger">Belum ada supplier. Minta admin tambah supplier di menu Supplier Manajemen.</small>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nominal</label>
                            <input type="number" name="nominal" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Deposit / Hutang</label>
                            <select name="jenis_transaksi" class="form-select" required>
                                <option value="deposit">Deposit</option>
                                <option value="hutang">Hutang</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank</label>
                            <input type="text" name="bank" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Server</label>
                            <input type="text" name="server" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No Rekening</label>
                            <input type="text" name="no_rek" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Rekening</label>
                            <input type="text" name="nama_rekening" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reply Tiket</label>
                            <textarea name="reply_tiket" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jam</label>
                            <input type="time" name="jam" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

