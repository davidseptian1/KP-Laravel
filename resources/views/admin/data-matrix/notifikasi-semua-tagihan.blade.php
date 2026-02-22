@extends('layouts/app')

@section('content')

@php($bulanMap = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'])

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item">Notifikasi Tagihan</li>
                    <li class="breadcrumb-item active">Semua Tagihan</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Semua Tagihan</h2>
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
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>No</th>
                                <th>Jenis Tagihan</th>
                                <th>Nama</th>
                                <th>Nomor Referensi</th>
                                <th>Bank</th>
                                <th>Periode</th>
                                <th>Tagihan</th>
                                <th>Tanggal Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($items as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $items->firstItem() + $index }}</td>
                                    <td>{{ $item->jenis_tagihan }}</td>
                                    <td>{{ $item->nama ?? '-' }}</td>
                                    <td>{{ $item->nomor_referensi ?? '-' }}</td>
                                    <td>{{ $item->bank ?? '-' }}</td>
                                    <td class="text-center">{{ $bulanMap[(int) $item->periode_bulan] ?? $item->periode_bulan }}/{{ $item->periode_tahun }}</td>
                                    <td>{{ $item->tagihan !== null ? 'Rp' . number_format((float)$item->tagihan, 0, ',', '.') : '-' }}</td>
                                    <td>{{ $item->tanggal_payment ? \Carbon\Carbon::parse($item->tanggal_payment)->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Belum ada data tagihan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $items->links() }}</div>
            </div>
        </div>
    </div>
</div>

@endsection
