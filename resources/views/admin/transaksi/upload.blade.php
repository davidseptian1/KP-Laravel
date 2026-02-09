@extends('layouts.app')

@section('content')
    <!-- [ breadcrumb ] start -->
    <div class="page-header" style="margin: 0; padding: 0;">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ul>
                </div>
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h2 class="mb-0">{{ $title }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->

    <!-- Alert Messages -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Form Upload -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="ti ti-upload me-2"></i>Upload File CSV</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('transaksi.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="csv_file" class="form-label fw-semibold">Pilih File CSV <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="file" class="form-control @error('csv_file') is-invalid @enderror" 
                                       id="csv_file" name="csv_file" accept=".csv,.txt" required>
                                <button class="btn btn-primary" type="submit">
                                    <i class="ti ti-upload me-1"></i> Upload
                                </button>
                            </div>
                            @error('csv_file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                Format: CSV atau TXT | Maksimal 10MB
                            </small>
                        </div>
                        <div class="alert alert-info mt-3" id="upload-estimation" style="display: none;">
                            <strong>Estimasi Waktu:</strong> <span id="estimated-time"></span> detik.
                        </div>
                    </form>

                    <script>
                        document.getElementById('csv_file').addEventListener('change', function(event) {
                            const file = event.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    const lines = e.target.result.split('\n').length - 1; // Kurangi header
                                    const timePerRecord = 0.02; // 0.02 detik per record
                                    const estimatedTime = (lines * timePerRecord).toFixed(2);
                                    document.getElementById('upload-estimation').style.display = 'block';
                                    document.getElementById('estimated-time').textContent = estimatedTime;
                                };
                                reader.readAsText(file);
                            }
                        });
                    </script>
                </div>
            </div>

            <!-- Template Info -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="ti ti-info-circle me-2"></i>Format File CSV</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">File CSV Anda harus memiliki kolom-kolom berikut:</p>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless">
                            <thead class="table-light">
                                <tr>
                                    <th>Kolom</th>
                                    <th>Tipe</th>
                                    <th>Contoh</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>trx_id</code></td>
                                    <td>Text (Unique)</td>
                                    <td>117625695</td>
                                </tr>
                                <tr>
                                    <td><code>tgl_entri</code></td>
                                    <td>DD/MM/YYYY HH:mm:ss</td>
                                    <td>28/01/2026 23:42:15</td>
                                </tr>
                                <tr>
                                    <td><code>kode_produk</code></td>
                                    <td>Text</td>
                                    <td>XDP1, MOBA5</td>
                                </tr>
                                <tr>
                                    <td><code>nomor_tujuan</code></td>
                                    <td>Text</td>
                                    <td>087750646966</td>
                                </tr>
                                <tr>
                                    <td><code>status</code></td>
                                    <td>Gagal/Sukses</td>
                                    <td>Sukses</td>
                                </tr>
                                <tr>
                                    <td><code>harga_beli</code></td>
                                    <td>Decimal</td>
                                    <td>6.905</td>
                                </tr>
                                <tr>
                                    <td><code>harga_jual</code></td>
                                    <td>Decimal</td>
                                    <td>6.985</td>
                                </tr>
                                <tr>
                                    <td><code>laba</code></td>
                                    <td>Decimal</td>
                                    <td>0.08</td>
                                </tr>
                                <tr>
                                    <td><code>durasi_detik</code></td>
                                    <td>Integer</td>
                                    <td>5</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <small>
                            <strong>Catatan:</strong> Kolom yang opsional: sn, kode_reseller, nama_reseller, modul
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Summary -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="ti ti-bar-chart-alt-2 me-2"></i>Ringkasan Data</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3 bg-light-primary">
                                    <i class="ti ti-receipt text-primary fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 small text-muted">Total Transaksi</p>
                                    <h5 class="mb-0">{{ \App\Models\Transaksi::count() }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3 bg-light-success">
                                    <i class="ti ti-check-circle text-success fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 small text-muted">Transaksi Sukses</p>
                                    <h5 class="mb-0">{{ \App\Models\Transaksi::where('status', 'Sukses')->count() }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3 bg-light-danger">
                                    <i class="ti ti-x-circle text-danger fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 small text-muted">Transaksi Gagal</p>
                                    <h5 class="mb-0">{{ \App\Models\Transaksi::where('status', 'Gagal')->count() }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3 bg-light-warning">
                                    <i class="ti ti-coin text-warning fs-4"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 small text-muted">Total Laba</p>
                                    <h5 class="mb-0">Rp {{ number_format(\App\Models\Transaksi::sum('laba'), 0, ',', '.') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fitur Tambahan -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="ti ti-cog me-2"></i>Opsi Lainnya</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Kelola data transaksi:</p>
                    <form action="{{ route('transaksi.clear') }}" method="POST" class="d-inline" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua data transaksi?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="ti ti-trash me-1"></i> Hapus Semua Data
                        </button>
                    </form>
                    <hr>
                    <a href="{{ route('transaksi.analisis') }}" class="btn btn-primary w-100">
                        <i class="ti ti-bar-chart me-1"></i> Lihat Analisis
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
