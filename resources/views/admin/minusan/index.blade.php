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
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex gap-2 flex-wrap">
                        @if(auth()->user()->jabatan == 'Admin')
                        <a href="{{ route('minusanCreate') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>Tambah Data
                        </a>
                        @endif
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if(auth()->user()->jabatan == 'Admin')
                        <a href="{{ route('minusanExcel') }}" class="btn btn-success">
                            <i class="ti ti-file-spreadsheet me-1"></i>Excel
                        </a>
                        <a href="{{ route('minusanPdf') }}" class="btn btn-danger" target="_blank">
                            <i class="ti ti-file-text me-1"></i>PDF
                        </a>
                        @endif
                    </div>
                </div>
                
                <!-- Filter Section -->
                <!-- <div class="card-body border-bottom">
                    <div class="row g-3">
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
                                <i class="ti ti-user me-1"></i>Filter Nama
                            </label>
                            <input type="text" class="form-control" id="filterNama" placeholder="Cari nama...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-block">&nbsp;</label>
                            <button class="btn btn-secondary w-100" id="btnResetFilter">
                                <i class="ti ti-refresh me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div> -->

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No.</th>
                                    <th>Tanggal</th>
                                    <th>Server</th>
                                    <th>Nama</th>
                                    <th>Supplier</th>
                                    <th>Produk</th>
                                    <th>Nomor</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Total/Org</th>
                                    <th class="text-center">Keterangan</th>
                                    <th class="text-center">
                                        <i class="ti ti-settings"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($minusan as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-light-primary">
                                                <i class="ti ti-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                                            </span>
                                        </td>
                                        <td>{{ $item->server }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avtar avtar-s bg-light-primary flex-shrink-0 me-2">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <span class="fw-semibold">{{ $item->nama }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $item->spl }}</td>
                                        <td>{{ $item->produk }}</td>
                                        <td>{{ $item->nomor }}</td>
                                        <td class="text-end fw-semibold text-primary">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light-info">{{ $item->qty }}</span>
                                        </td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($item->total_per_orang, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-light-{{ $item->keterangan == 'Dialihkan' ? 'warning' : 'success' }}">
                                                {{ $item->keterangan }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                @if(auth()->user()->jabatan == 'Admin')
                                                <a href="{{ route('minusanEdit', $item->id) }}" 
                                                   class="avtar avtar-xs btn-link-warning" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Edit">
                                                    <i class="ti ti-edit f-18"></i>
                                                </a>
                                                <form action="{{ route('minusanDestroy', $item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="avtar avtar-xs btn-link-danger" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                        <i class="ti ti-trash f-18"></i>
                                                    </button>
                                                </form>
                                                @else
                                                <span class="badge bg-secondary" title="Hanya Admin yang bisa mengedit">
                                                    <i class="ti ti-lock"></i> Terkunci
                                                </span>
                                                @endif
                                            </div>
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
                "order": [[1, 'desc']], // Sort by date descending
                "columnDefs": [
                    { "orderable": false, "targets": 11 } // Disable sorting on action column
                ],
                "fnDrawCallback": function(oSettings) {
                    initDeleteButtons();
                    initTooltips();
                }
            });

            // Custom filter function for date range
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var tanggalMulai = $('#filterTanggalMulai').val();
                    var tanggalAkhir = $('#filterTanggalAkhir').val();
                    var nama = $('#filterNama').val().toLowerCase();
                    
                    // Get date from column (parse badge text)
                    var tanggalText = $(data[1]).text().trim();
                    var tanggalParts = tanggalText.split(' ');
                    var bulanMap = {
                        'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
                        'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
                        'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
                    };
                    var tanggalRow = tanggalParts[2] + '-' + bulanMap[tanggalParts[1]] + '-' + tanggalParts[0].padStart(2, '0');
                    
                    // Get nama from column
                    var namaRow = data[3].toLowerCase();
                    
                    // Date filter
                    var dateMatch = true;
                    if (tanggalMulai && tanggalRow < tanggalMulai) {
                        dateMatch = false;
                    }
                    if (tanggalAkhir && tanggalRow > tanggalAkhir) {
                        dateMatch = false;
                    }
                    
                    // Name filter
                    var nameMatch = true;
                    if (nama && !namaRow.includes(nama)) {
                        nameMatch = false;
                    }
                    
                    return dateMatch && nameMatch;
                }
            );

            // Event listeners for filters
            $('#filterTanggalMulai, #filterTanggalAkhir, #filterNama').on('keyup change', function() {
                table.draw();
            });

            // Reset filters
            $('#btnResetFilter').on('click', function() {
                $('#filterTanggalMulai').val('');
                $('#filterTanggalAkhir').val('');
                $('#filterNama').val('');
                table.draw();
            });

            // Initialize tooltips
            function initTooltips() {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }

            // Initial call
            initTooltips();
        });
    </script>
@endsection
