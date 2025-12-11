@extends('layouts/app')

@section('content')
<h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-center justify-content-xl-between">
        <div class="mb-1 mr-2">
            <a href="{{ route('minusan') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="card-body">

        <div class="alert alert-danger">
            <strong>Perhatian!</strong> Data berikut akan dihapus secara permanen.<br>
            Apakah Anda yakin ingin melanjutkan?
        </div>

        <form action="{{ route('minusanDelete', $minusan->id) }}" method="post">
            @csrf

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">Tanggal :</label>
                    <input type="date" class="form-control" value="{{ $minusan->tanggal }}" readonly>
                </div>

                <div class="col-6">
                    <label class="form-label">Server :</label>
                    <input type="text" class="form-control" value="{{ $minusan->server }}" readonly>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">Nama :</label>
                    <input type="text" class="form-control" value="{{ $minusan->nama }}" readonly>
                </div>

                <div class="col-6">
                    <label class="form-label">Supplier :</label>
                    <input type="text" class="form-control" value="{{ $minusan->spl }}" readonly>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">Produk :</label>
                    <input type="text" class="form-control" value="{{ $minusan->produk }}" readonly>
                </div>

                <div class="col-6">
                    <label class="form-label">Nomor :</label>
                    <input type="text" class="form-control" value="{{ $minusan->nomor }}" readonly>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">Total :</label>
                    <input type="text" class="form-control" value="{{ number_format($minusan->total) }}" readonly>
                </div>

                <div class="col-6">
                    <label class="form-label">QTY :</label>
                    <input type="text" class="form-control" value="{{ $minusan->qty }}" readonly>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">Total Per Orang :</label>
                    <input type="text" class="form-control" value="{{ $minusan->total_per_orang }}" readonly>
                </div>

                <div class="col-6">
                    <label class="form-label">Keterangan :</label>
                    <input type="text" class="form-control" value="{{ $minusan->keterangan }}" readonly>
                </div>
            </div>

            <div class="form-group mt-3">
                <button type="submit" onclick="return confirm('Yakin ingin menghapus data ini?')"
                    class="btn btn-sm btn-danger">
                    <i class="fas fa-trash mr-1"></i> Hapus Data
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
