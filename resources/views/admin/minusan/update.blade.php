@extends('layouts/app')

@section('content')
<h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

<div class="card">
    <div class="card-header d-flex flex-wrap justify-content-center justify-content-xl-between">
        <div class="mb-1 mr-2">
            <a href="{{ route('minusan') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-arrow-left mr-2"></i>Kembali</a>
        </div>
    </div>
    <div class="card-body">

        <form action="{{ route('minusanUpdate', $minusan->id) }}" method="post">
            @csrf

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Tanggal :
                    </label>
                    <input type="date" name="tanggal" class="form-control @error('tanggal') is-invalid @enderror" value="{{ $minusan->tanggal }}">
                    @error('tanggal')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Server :
                    </label>
                    <select name="server" class="form-control @error('server') is-invalid @enderror">
                        <option disabled {{ old('server', $minusan->server) ? '' : 'selected' }}>Pilih Server</option>
                        <option value="CMP" {{ old('server', $minusan->server) == 'CMP' ? 'selected' : '' }}>CMP</option>
                        <option value="CCN" {{ old('server', $minusan->server) == 'CCN' ? 'selected' : '' }}>CCN</option>
                        <option value="238" {{ old('server', $minusan->server) == '238' ? 'selected' : '' }}>238</option>
                        <option value="AIRA" {{ old('server', $minusan->server) == 'AIRA' ? 'selected' : '' }}>AIRA</option>
                        <option value="BELANJA KUOTA" {{ old('server', $minusan->server) == 'BELANJA KUOTA' ? 'selected' : '' }}>Belanja Kuota</option>
                    </select>
                    @error('server')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                    @enderror
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Nama :
                    </label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ $minusan->nama }}">
                    @error('nama')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        SPL :
                    </label>
                    <input type="spl" name="spl" class="form-control @error('spl') is-invalid @enderror" value="{{ $minusan->spl }}">
                    @error('spl')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                    @enderror
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Produk :
                    </label>
                    <input type="produk" name="produk" class="form-control @error('produk') is-invalid @enderror" value="{{ $minusan->produk }}">
                    @error('produk')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Nomor :
                    </label>
                    <input type="number" name="nomor" class="form-control @error('nomor') is-invalid @enderror" value="{{ $minusan->nomor }}">
                    @error('nomor')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                    @enderror
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Total :
                    </label>
                    <input type="number" name="total" class="form-control @error('total') is-invalid @enderror" value="{{ $minusan->total }}">
                    @error('total')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        QTY :
                    </label>
                    <input type="number" name="qty" class="form-control @error('qty') is-invalid @enderror" value="{{ $minusan->qty }}">
                    @error('qty')
                    <small class="text-danger">
                        {{ $message }}
                    </small>
                    @enderror
                </div>

            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Total Per Orang :
                    </label>
                    <input type="number" id="total_per_orang" name="total_per_orang"
                        class="form-control @error('total_per_orang') is-invalid @enderror"
                        value="{{ $minusan->total_per_orang }}" readonly>
                    @error('total_per_orang')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Keterangan :
                    </label>
                    <select name="keterangan" class="form-control @error('keterangan') is-invalid @enderror">
                        <option disabled {{ old('keterangan', $minusan->keterangan) ? '' : 'selected' }}>Pilih Keterangan</option>
                        <option value="Menunggu Jawaban -> Alihkan" {{ old('keterangan', $minusan->keterangan) == 'Dialihkan' ? 'selected' : '' }}>Menunggu Jawaban -> Alihkan</option>
                        <option value="Menunggu Jawaban -> Digagalkan" {{ old('keterangan', $minusan->keterangan) == 'Digagalkan' ? 'selected' : '' }}>Menunggu Jawaban -> Digagalkan</option>
                        <option value="Sukses -> Gagal" {{ old('keterangan', $minusan->keterangan) == 'Sukses -> Gagal' ? 'selected' : '' }}>Sukses -> Gagal</option>
                    </select>
                    @error('keterangan')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>


            <div class="form-group mt-3">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script hitung otomatis total per orang --}}

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const totalInput = document.getElementById('total');
        const qtyInput = document.getElementById('qty');
        const perOrangInput = document.getElementById('total_per_orang');

        function updatePerOrang() {
            const total = parseFloat(totalInput.value) || 0;
            const qty = parseInt(qtyInput.value) || 0;
            perOrangInput.value = qty > 0 ? (total / qty).toFixed(0) : 0;
        }

        totalInput.addEventListener('input', updatePerOrang);
        qtyInput.addEventListener('input', updatePerOrang);
    });
</script>

@endsection