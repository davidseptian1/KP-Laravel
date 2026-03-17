@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <h5>Monitoring Persediaan Stok</h5>
            <table class="table">
                <thead><tr><th>#</th><th>Owner</th><th>Total</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach($list as $row)
                        <tr>
                            <td>{{ $row->id }}</td>
                            <td>{{ $row->owner_name }}</td>
                            <td>{{ number_format($row->total_amount,2) }}</td>
                            <td>{{ $row->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $row->status }}</td>
                            <td>
                                <a href="{{ route('admin.persediaan.show', $row->id) }}" class="btn btn-sm btn-primary">Lihat</a>
                                @if($row->transfer_proof_path)
                                    <a href="{{ route('admin.persediaan.file', [$row->id, 'transfer']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Bukti Transfer</a>
                                @endif
                                @if($row->invoice_path)
                                    <a href="{{ route('admin.persediaan.file', [$row->id, 'invoice']) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Lihat Faktur</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $list->links() }}
        </div>
    </div>
</div>
@endsection
