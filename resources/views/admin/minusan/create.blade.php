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
        <form action="{{ route('minusanStore') }}" method="post">
            @csrf

            <!-- Baris 1 -->
            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> Tanggal :
                    </label>
                    <input type="date" name="tanggal"
                        class="form-control @error('tanggal') is-invalid @enderror"
                        value="{{ old('tanggal') }}">
                    @error('tanggal')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> Server :
                    </label>
                    <select name="server" class="form-control @error('server') is-invalid @enderror">
                        <option selected disabled>Pilih Server</option>
                        <option value="CMP">CMP</option>
                        <option value="CCN">CCN</option>
                        <option value="AIRA">AIRA</option>
                        <option value="238">238</option>
                        <option value="Belanja Kuota">Belanja Kuota</option>
                    </select>
                    @error('server')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Baris 2 -->
            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> Nama :
                    </label>
                    <input type="text" name="nama"
                        class="form-control @error('nama') is-invalid @enderror"
                        value="{{ old('nama') }}">
                    @error('nama')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> Supplier :
                    </label>
                    <input type="text" name="spl"
                        class="form-control @error('spl') is-invalid @enderror"
                        value="{{ old('spl') }}">
                    @error('spl')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Baris 3 -->
            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> Kode Produk :
                    </label>
                    <input type="text" name="produk"
                        class="form-control @error('produk') is-invalid @enderror"
                        value="{{ old('produk') }}">
                    @error('produk')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> Nomor :
                    </label>
                    <input type="number" name="nomor"
                        class="form-control @error('nomor') is-invalid @enderror"
                        value="{{ old('nomor') }}">
                    @error('nomor')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Baris 4 -->
            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> Total :
                    </label>
                    <input type="number" name="total" id="total"
                        class="form-control @error('total') is-invalid @enderror"
                        value="{{ old('total') }}">
                    @error('total')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> QTY :
                    </label>
                    <input type="number" name="qty" id="qty"
                        class="form-control @error('qty') is-invalid @enderror"
                        value="{{ old('qty') }}">
                    @error('qty')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Baris 5 -->
            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">Total Per Orang (otomatis):</label>
                    <input type="number" id="total_per_orang" name="total_per_orang"
                        class="form-control" readonly>
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span> Keterangan :
                    </label>
                    <select name="keterangan" class="form-control @error('keterangan') is-invalid @enderror">
                        <option selected disabled>Pilih Keterangan</option>
                        <option value="Dialihkan">Menunggu Jawaban -> Alihkan</option>
                        <option value="Digagalkan">Menunggu Jawaban -> Digagalkan</option>
                        <option value="Gagal">Sukses -> Gagal</option>
                    </select>
                    @error('keterangan')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

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
