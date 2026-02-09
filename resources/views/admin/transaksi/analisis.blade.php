@extends('layouts.app')

@section('content')
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ul>
                </div>
                <div class="col-md-12">
                    <div class="page-header-title d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">{{ $title }}</h2>
                        <div>
                            <a href="{{ route('transaksi.upload') }}" class="btn btn-secondary me-2">
                                <i class="ti ti-upload me-1"></i> Upload Data
                            </a>
                            <a href="{{ route('transaksi.export') }}" class="btn btn-success">
                                <i class="ti ti-download me-1"></i> Export
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->

    <!-- Filter Section -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('transaksi.analisis') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="start_date" class="form-label fw-semibold">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                   value="{{ $filters['start_date'] }}">
                        </div>
                        <div class="col-md-3">
                            <label for="end_date" class="form-label fw-semibold">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                   value="{{ $filters['end_date'] }}">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_produk" class="form-label fw-semibold">Produk</label>
                            <select class="form-select" id="filter_produk" name="filter_produk">
                                <option value="">-- Semua Produk --</option>
                                @foreach ($produkList as $produk)
                                    <option value="{{ $produk }}" 
                                        {{ $filters['filter_produk'] == $produk ? 'selected' : '' }}>
                                        {{ $produk }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_status" class="form-label fw-semibold">Status</label>
                            <select class="form-select" id="filter_status" name="filter_status">
                                <option value="">-- Semua Status --</option>
                                @foreach ($statusList as $status)
                                    <option value="{{ $status }}" 
                                        {{ $filters['filter_status'] == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-filter-alt me-1"></i> Filter
                            </button>
                            <a href="{{ route('transaksi.analisis') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Utama -->
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Transaksi</p>
                            <h4 class="mb-0">{{ $statistik['total_transaksi'] }}</h4>
                        </div>
                        <div class="avatar bg-light-primary">
                            <i class="ti ti-receipt text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Success Rate</p>
                            <h4 class="mb-0">{{ $statistik['success_rate'] }}%</h4>
                        </div>
                        <div class="avatar bg-light-success">
                            <i class="ti ti-check-circle text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Laba</p>
                            <h4 class="mb-0">@acct($statistik['total_laba'])</h4>
                        </div>
                        <div class="avatar bg-light-warning">
                            <i class="ti ti-coin text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Profit Margin</p>
                            <h4 class="mb-0">{{ $statistik['profit_margin'] }}%</h4>
                        </div>
                        <div class="avatar bg-light-info">
                            <i class="ti ti-trending-up text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ANALISIS 1: KECEPATAN TRANSAKSI -->
    <div class="row mb-3 mt-3">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="ti ti-clock me-2"></i>Analisis Kecepatan Transaksi</h5>
                </div>
                <div class="card-body">
                    @if ($kecepatanAnalisis['total_berhasil'] > 0)
                        <div class="row mb-3">
                            <div class="col-6">
                                <p class="text-muted small mb-1">Rata-rata Kecepatan</p>
                                <h5 class="mb-0">{{ $kecepatanAnalisis['rata_rata_detik'] }} detik</h5>
                            </div>
                            <div class="col-6">
                                <p class="text-muted small mb-1">Total Berhasil</p>
                                <h5 class="mb-0">{{ $kecepatanAnalisis['total_berhasil'] }}</h5>
                            </div>
                        </div>

                        <hr class="my-3">

                        <p class="text-muted small mb-2">Distribusi Kecepatan:</p>
                        
                        <!-- Cepat -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold">{{ $kecepatanAnalisis['distribusi_kecepatan']['cepat']['label'] }}</small>
                                <small class="text-muted">{{ $kecepatanAnalisis['distribusi_kecepatan']['cepat']['count'] }} ({{ $kecepatanAnalisis['distribusi_kecepatan']['cepat']['persen'] }}%)</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $kecepatanAnalisis['distribusi_kecepatan']['cepat']['persen'] }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Normal -->
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold">{{ $kecepatanAnalisis['distribusi_kecepatan']['normal']['label'] }}</small>
                                <small class="text-muted">{{ $kecepatanAnalisis['distribusi_kecepatan']['normal']['count'] }} ({{ $kecepatanAnalisis['distribusi_kecepatan']['normal']['persen'] }}%)</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" role="progressbar" 
                                     style="width: {{ $kecepatanAnalisis['distribusi_kecepatan']['normal']['persen'] }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Lambat -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="fw-semibold">{{ $kecepatanAnalisis['distribusi_kecepatan']['lambat']['label'] }}</small>
                                <small class="text-muted">{{ $kecepatanAnalisis['distribusi_kecepatan']['lambat']['count'] }} ({{ $kecepatanAnalisis['distribusi_kecepatan']['lambat']['persen'] }}%)</small>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" role="progressbar" 
                                     style="width: {{ $kecepatanAnalisis['distribusi_kecepatan']['lambat']['persen'] }}%">
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info small mb-0">
                            <strong>Min:</strong> {{ $kecepatanAnalisis['tercepat'] }}s | 
                            <strong>Max:</strong> {{ $kecepatanAnalisis['terlambat'] }}s
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            Tidak ada data transaksi yang berhasil untuk analisis kecepatan.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0"><i class="ti ti-bar-chart me-2"></i>Kecepatan per Produk</h5>
                </div>
                <div class="card-body">
                    @if (count($kecepatanAnalisis['kecepatan_per_produk']) > 0)
                        <div style="max-height: 300px; overflow-y: auto;">
                            @foreach ($kecepatanAnalisis['kecepatan_per_produk'] as $produk)
                                <div class="mb-3 pb-2 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong class="small">{{ $produk['produk'] }}</strong>
                                        <span class="badge bg-light-success text-success">{{ $produk['rata_rata'] }}s</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">{{ $produk['tercepat'] }}-{{ $produk['terlambat'] }}s</small>
                                        <small class="text-muted">{{ $produk['count'] }} transaksi</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            Tidak ada data untuk analisis kecepatan per produk.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ANALISIS 2: REKOMENDASI PRODUK -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0"><i class="ti ti-star me-2"></i>Rekomendasi Produk untuk Dijual</h5>
                    <small class="text-muted">Skor: Margin (40%) + Success Rate (30%) + Volume (30%)</small>
                </div>
                <div class="card-body">
                    @if (count($rekomendasiProduk) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 50px;"><i class="ti ti-star"></i></th>
                                        <th>Produk</th>
                                        <th class="text-center">Transaksi</th>
                                        <th class="text-end">Total Penjualan</th>
                                        <th class="text-end">Total Laba</th>
                                        <th class="text-center">Profit Margin</th>
                                        <th class="text-center">Success Rate</th>
                                        <th class="text-center">Skor</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rekomendasiProduk as $index => $produk)
                                        @php
                                            $skor = $produk['skor_rekomendasi'];
                                            $status = '';
                                            $badgeClass = '';
                                            
                                            if ($skor >= 80) {
                                                $status = 'Sangat Rekomendasi';
                                                $badgeClass = 'bg-light-success text-success';
                                            } elseif ($skor >= 60) {
                                                $status = 'Rekomendasi';
                                                $badgeClass = 'bg-light-info text-info';
                                            } elseif ($skor >= 40) {
                                                $status = 'Cukup';
                                                $badgeClass = 'bg-light-warning text-warning';
                                            } else {
                                                $status = 'Kurang Rekomendasi';
                                                $badgeClass = 'bg-light-danger text-danger';
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge bg-light-primary text-primary">{{ $index + 1 }}</span>
                                            </td>
                                            <td class="fw-semibold">{{ $produk['kode_produk'] }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-light-primary text-primary fw-bold">{{ $produk['jumlah_transaksi'] }}</span>
                                            </td>
                                            <td class="text-end">
                                                @acct($produk['total_penjualan'] ?? 0)
                                            </td>
                                            <td class="text-end fw-semibold text-success">
                                                @acct($produk['total_laba'] ?? 0)
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light-info text-info">{{ $produk['profit_margin'] }}%</span>
                                            </td>
                                            <td class="text-center">{{ $produk['success_rate'] }}%</td>
                                            <td class="text-center">
                                                <strong class="text-primary">{{ $skor }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            Tidak ada data untuk rekomendasi produk. Silakan upload data transaksi terlebih dahulu.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- ANALISIS 3: PERFORMA RESELLER -->
    @if (count($performaReseller) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0"><i class="ti ti-users me-2"></i>Top 10 Reseller Terbaik</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Kode Reseller</th>
                                        <th>Nama Reseller</th>
                                        <th class="text-center">Transaksi</th>
                                        <th class="text-end">Total Penjualan</th>
                                        <th class="text-end">Total Laba</th>
                                        <th class="text-end">Rata-rata Laba</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($performaReseller as $index => $reseller)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td class="fw-semibold">{{ $reseller['kode_reseller'] }}</td>
                                            <td>{{ $reseller['nama_reseller'] }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-light-primary text-primary fw-bold">{{ $reseller['jumlah_transaksi'] }}</span>
                                            </td>
                                            <td class="text-end">
                                                @acct($reseller['total_penjualan'] ?? 0)
                                            </td>
                                            <td class="text-end fw-semibold text-success">
                                                @acct($reseller['total_laba'] ?? 0)
                                            </td>
                                            <td class="text-end">
                                                @acct($reseller['rata_laba'] ?? 0)
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (function(){
        const importFile = "{{ session('import_file') ?? '' }}";
        if (!importFile) return;

        let attempts = 0;
        const maxAttempts = 180; // ~6 minutes

        const checkStatus = async () => {
            attempts++;
            try {
                const res = await fetch(`{{ url('imports/status') }}?file=${encodeURIComponent(importFile)}`);
                if (res.status === 200) {
                    const json = await res.json();
                    if (json.status === 'completed') {
                        // show simple alert and reload to display new data
                        alert('Import selesai. Memuat data terbaru...');
                        location.reload();
                        return;
                    }
                    if (json.status === 'failed') {
                        alert('Import gagal: ' + (json.message || 'lihat log server'));
                        return;
                    }
                }
            } catch (e) {
                // ignore network errors and retry
            }

            if (attempts < maxAttempts) {
                setTimeout(checkStatus, 2000);
            } else {
                console.warn('Import polling timed out for', importFile);
            }
        };

        // start polling after small delay to allow job to create import record
        setTimeout(checkStatus, 1000);

        // If Laravel Echo is available, also subscribe to broadcast channel for real-time updates
        if (window.Echo && importFile) {
            try {
                window.Echo.channel('imports')
                    .listen('.ImportStatusUpdated', (e) => {
                        if (e.file === importFile) {
                            if (e.status === 'completed') {
                                alert('Import selesai (real-time). Memuat data...');
                                location.reload();
                            } else if (e.status === 'failed') {
                                alert('Import gagal (real-time): ' + (e.message || 'lihat log'));
                            }
                        }
                    });
            } catch (err) {
                console.warn('Echo subscription failed', err);
            }
        }
    })();
</script>
@endpush
