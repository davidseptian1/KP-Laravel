@extends('layouts/app')

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

    <div class="row mt-0">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="row align-items-center g-3">
                        <!-- Filter Form -->
                        <div class="col-lg-6">
                            <form method="GET" action="{{ route('admin.rekap.tilangan') }}" class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="form-label mb-0 fw-semibold text-nowrap">
                                        <i class="ti ti-calendar me-1"></i>Periode:
                                    </label>
                                    <select name="bulan" class="form-select form-select-sm" style="min-width: 130px;">
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ sprintf('%02d', $i) }}" {{ $bulan == sprintf('%02d', $i) ? 'selected' : '' }}>
                                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                    
                                    <select name="tahun" class="form-select form-select-sm" style="min-width: 100px;">
                                        @foreach ($tahunList as $t)
                                            <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>
                                                {{ $t }}
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ti ti-eye me-1"></i>Tampilkan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Export Buttons -->
                        <div class="col-lg-6">
                            @if(auth()->user()->jabatan == 'Admin' || auth()->user()->jabatan == 'HRD')
                            <div class="d-flex gap-2 justify-content-lg-end">
                                <a href="{{ route('admin.rekap.tilangan.excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                                    class="btn btn-success btn-sm">
                                    <i class="ti ti-file-spreadsheet me-1"></i>Excel
                                </a>
                                <a href="{{ route('admin.rekap.tilangan.cetak', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                                    class="btn btn-danger btn-sm" target="_blank">
                                    <i class="ti ti-file-text me-1"></i>PDF
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="card-body border-bottom">
                        <!-- <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="ti ti-calendar me-1"></i>Tanggal Mulai
                                </label>
                                <input type="date" class="form-control" id="filterTanggalMulai">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="ti ti-calendar me-1"></i>Tanggal Akhir
                                </label>
                                <input type="date" class="form-control" id="filterTanggalAkhir">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="ti ti-search me-1"></i>Cari Nama
                                </label>
                                <input type="text" class="form-control" id="filterNama" placeholder="Ketik nama untuk mencari...">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-secondary w-100" id="btnResetFilter">
                                    <i class="ti ti-refresh me-1"></i>Reset
                                </button>
                            </div>
                        </div> -->
                    
                    <div class="alert alert-info mt-3 mb-0 d-flex align-items-center">
                        <i class="ti ti-info-circle me-2" style="font-size: 20px;"></i>
                        <div>
                            <strong>Periode Perhitungan:</strong> 
                            <span class="badge bg-primary ms-2">{{ $start->format('d F Y') }}</span>
                            <i class="ti ti-arrow-right mx-2"></i>
                            <span class="badge bg-primary">{{ $end->format('d F Y') }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Summary Stats -->
                    @if(count($minusan) > 0)
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light-primary border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-l bg-primary flex-shrink-0">
                                            <i class="ti ti-users text-white"></i>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <p class="mb-1 text-muted">Total Orang</p>
                                            <h4 class="mb-0">{{ number_format(count($minusan)) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-success border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-l bg-success flex-shrink-0">
                                            <i class="ti ti-currency-dollar text-white"></i>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <p class="mb-1 text-muted">Total Tilangan</p>
                                            <h4 class="mb-0">Rp {{ number_format(collect($minusan)->sum('total_tilangan'), 0, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light-warning border-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="avtar avtar-l bg-warning flex-shrink-0">
                                            <i class="ti ti-calculator text-white"></i>
                                        </div>
                                        <div class="ms-3 flex-grow-1">
                                            <p class="mb-1 text-muted">Rata-rata</p>
                                            <h4 class="mb-0">Rp {{ number_format(collect($minusan)->avg('total_tilangan'), 0, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 80px;">No.</th>
                                    <th>Nama</th>
                                    <th class="text-end" style="width: 200px;">Total Tilangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($minusan as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avtar avtar-s bg-light-primary flex-shrink-0 me-2">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold d-block">{{ $item->nama }}</span>
                                                    <small class="text-muted">ID: {{ str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if($item->total_tilangan)
                                                <span class="badge bg-success" style="font-size: 14px; padding: 8px 12px;">
                                                    Rp {{ number_format($item->total_tilangan, 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="badge bg-light-secondary">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="empty-state">
                                                <i class="ti ti-file-off" style="font-size: 48px; color: #ccc;"></i>
                                                <p class="text-muted mt-3 mb-0">Tidak ada data untuk periode ini</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#dataTable').DataTable({
                "language": {
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                    "infoEmpty": "Tidak ada data tersedia",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "search": "Cari:",
                    "paginate": {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Selanjutnya",
                        "previous": "Sebelumnya"
                    }
                },
                "pageLength": 10,
                "order": [[2, 'desc']], // Sort by total tilangan descending
                "columnDefs": [
                    { "orderable": false, "targets": 0 } // Disable sorting on No column
                ]
            });

            // Custom filter function
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var nama = $('#filterNama').val().toLowerCase();
                    var namaRow = data[1].toLowerCase();
                    
                    // Name filter
                    if (nama && !namaRow.includes(nama)) {
                        return false;
                    }
                    
                    return true;
                }
            );

            // Event listeners for filters
            $('#filterNama').on('keyup', function() {
                table.draw();
            });

            // Note: Date filters are informational for this report
            // The data is already filtered by period (24th to 23rd)
            $('#filterTanggalMulai, #filterTanggalAkhir').on('change', function() {
                // Show alert about date filtering
                if ($('#filterTanggalMulai').val() || $('#filterTanggalAkhir').val()) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Informasi',
                        text: 'Filter tanggal untuk laporan ini sudah ditentukan dari tanggal 24 bulan dipilih hingga 23 bulan berikutnya. Gunakan filter Periode di atas untuk mengubah periode laporan.',
                        confirmButtonText: 'Mengerti'
                    });
                }
            });

            // Reset filters
            $('#btnResetFilter').on('click', function() {
                $('#filterTanggalMulai').val('');
                $('#filterTanggalAkhir').val('');
                $('#filterNama').val('');
                table.draw();
            });
        });
    </script>
@endsection
