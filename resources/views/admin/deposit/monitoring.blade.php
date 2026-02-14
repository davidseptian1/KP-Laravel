@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Monitoring Deposit</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Monitoring Deposit</h2>
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
                                <th>Nama Supplier</th>
                                <th class="text-end">Nominal</th>
                                <th>BANK</th>
                                <th>Server</th>
                                <th>No-Rek</th>
                                <th>Nama Rekening</th>
                                <th>Reply Penambahan</th>
                                <th>Jam</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $item)
                                <tr>
                                    <td>{{ $item->nama_supplier }}</td>
                                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                                    <td>{{ $item->bank }}</td>
                                    <td>{{ $item->server }}</td>
                                    <td>{{ $item->no_rek }}</td>
                                    <td>{{ $item->nama_rekening }}</td>
                                    <td>{{ $item->reply_penambahan ?? '-' }}</td>
                                    <td>{{ $item->jam ? \Carbon\Carbon::parse($item->jam)->format('H:i') : '-' }}</td>
                                    <td>{{ $item->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Belum ada deposit</td>
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
