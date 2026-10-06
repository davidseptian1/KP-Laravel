@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h3 class="mb-1 fw-bold text-dark"><i class="ti ti-notes text-primary me-2"></i>Pendataan</h3>
        <p class="text-muted mb-0">Catat dan pantau transaksi produk dengan fitur pemilahan teks otomatis dan upload screenshot.</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end align-items-center flex-wrap gap-2">
        @php
            $shiftLoginTs = (float) ($shiftLoginAt ?? session('pendataan_staff_login_at', 0));
            $shiftExpiresTs = (float) ($shiftExpiresAt ?? session('pendataan_staff_expires_at', 0));
            $shiftLoginStr = $shiftLoginTs > 0 ? \Carbon\Carbon::createFromTimestamp($shiftLoginTs)->format('H:i') : '-';
            $shiftExpiresStr = $shiftExpiresTs > 0 ? \Carbon\Carbon::createFromTimestamp($shiftExpiresTs)->format('H:i') : '-';
        @endphp
        <span class="badge bg-light text-dark border px-3 py-2 fs-6 shadow-sm">
            <i class="ti ti-user-check text-primary me-1"></i>Staf: <strong class="text-primary">{{ $activeStaffNama }}</strong>
        </span>
        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle px-3 py-2 fs-6 shadow-sm d-inline-flex align-items-center" id="shiftTimerBadge" title="Sesi Shift 8 Jam (Login: {{ $shiftLoginStr }} • Berakhir: {{ $shiftExpiresStr }})" data-bs-toggle="tooltip">
            <i class="ti ti-clock-hour-4 text-warning me-1"></i>Sisa Shift: <strong class="text-danger ms-1" id="shiftCountdownText">08:00:00</strong>
        </span>
        <a href="{{ url('pendataan/lock') }}" class="btn btn-sm btn-outline-danger shadow-sm" 
           title="Kunci / Logout Fitur Pendataan (Hanya keluar dari fitur ini)"
           onclick="return confirm('Logout dari fitur Pendataan? (Akun login utama Anda akan tetap aktif)')">
            <i class="ti ti-lock me-1"></i>Logout Fitur
        </a>
        <form action="{{ route('pendataan.sync-telegram') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-primary shadow-sm" title="Sinkronkan data pending dengan SMS bot Telegram yang sudah masuk">
                <i class="ti ti-refresh me-1"></i> Sinkronkan Bot
            </button>
        </form>
        <button type="button" class="btn btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#modalManualSms" title="Tempel dan proses SMS voucher secara langsung">
            <i class="ti ti-message-dots me-1"></i> Tempel SMS
        </button>
        <button type="button" class="btn btn-primary shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#modalTambahPendataan">
            <i class="ti ti-plus me-1"></i> Tambah Pendataan
        </button>
        @if(auth()->user()->jabatan === 'Superadmin')
            <a href="{{ url('superadmin/pendataan-access') }}" class="btn btn-outline-secondary" title="Pengaturan Akses Staff">
                <i class="ti ti-settings me-1"></i> Akses
            </a>
        @endif
    </div>
</div>

<!-- Summary Statistic Cards -->
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="ti ti-file-text fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Pendataan</h6>
                    <h3 class="mb-0 fw-bold">{{ number_format($totalTransaksi, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info me-3">
                    <i class="ti ti-package fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Qty Terdata</h6>
                    <h3 class="mb-0 fw-bold text-info">{{ number_format($totalQty, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="ti ti-wallet fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Nominal / Harga</h6>
                    <h3 class="mb-0 fw-bold text-success">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
            <i class="ti ti-filter me-2 text-primary"></i>Filter Data Pendataan
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('pendataan.index') }}" class="row g-3" id="formFilterPendataan">
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Tanggal Mulai</label>
                <input type="date" name="start_date" id="filter_start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] ?? '' }}">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Tanggal Selesai</label>
                <input type="date" name="end_date" id="filter_end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] ?? '' }}">
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Pilihan Shift</label>
                <select name="shift" class="form-select form-select-sm fw-semibold">
                    <option value="All Shift" {{ ($filters['shift'] ?? 'All Shift') === 'All Shift' ? 'selected' : '' }}>All Shift (Semua)</option>
                    <option value="Shift 1" {{ ($filters['shift'] ?? '') === 'Shift 1' ? 'selected' : '' }}>Shift 1 (Pagi)</option>
                    <option value="Shift 2" {{ ($filters['shift'] ?? '') === 'Shift 2' ? 'selected' : '' }}>Shift 2 (Siang/Sore)</option>
                    <option value="Shift 3" {{ ($filters['shift'] ?? '') === 'Shift 3' ? 'selected' : '' }}>Shift 3 (Malam)</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Jenis Chip</label>
                <select name="jenis_chip" class="form-select form-select-sm fw-semibold">
                    <option value="All Chip" {{ ($filters['jenis_chip'] ?? 'All Chip') === 'All Chip' ? 'selected' : '' }}>Semua Chip</option>
                    <option value="KTTS" {{ ($filters['jenis_chip'] ?? '') === 'KTTS' ? 'selected' : '' }}>KTTS</option>
                    <option value="KBTG" {{ ($filters['jenis_chip'] ?? '') === 'KBTG' ? 'selected' : '' }}>KBTG</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Nama Staf</label>
                <select name="nama" class="form-select form-select-sm">
                    <option value="">-- Semua Staf --</option>
                    @foreach($daftarNama as $itemNama)
                        <option value="{{ $itemNama }}" {{ ($filters['nama'] ?? '') === $itemNama ? 'selected' : '' }}>{{ $itemNama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Nama Produk</label>
                <input type="text" name="nama_produk" class="form-control form-control-sm" placeholder="Cari produk..." value="{{ $filters['nama_produk'] ?? '' }}" list="listProdukOptions">
                <datalist id="listProdukOptions">
                    @foreach($uniqueProducts as $uProd)
                        <option value="{{ $uProd }}">
                    @endforeach
                </datalist>
            </div>
            <div class="col-md-2 col-sm-6">
                <label class="form-label small fw-semibold text-muted">Status Data</label>
                <select name="status" class="form-select form-select-sm fw-semibold">
                    <option value="All Status" {{ ($filters['status'] ?? 'All Status') === 'All Status' ? 'selected' : '' }}>Semua Status</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending ⏳ (Menunggu)</option>
                    <option value="sukses" {{ ($filters['status'] ?? '') === 'sukses' ? 'selected' : '' }}>Sukses ✅ (Sesuai)</option>
                    <option value="gagal" {{ ($filters['status'] ?? '') === 'gagal' ? 'selected' : '' }}>Gagal ❌ (> 24 Jam)</option>
                </select>
            </div>
            <div class="col-md-10 col-sm-12 d-flex align-items-center flex-wrap gap-1 pt-1">
                @php
                    $todayStr = now()->toDateString();
                    $yesterdayStr = now()->subDay()->toDateString();
                    $sevenDaysStr = now()->subDays(6)->toDateString();
                    $activeStartDate = $filters['start_date'] ?? '';
                    $activeEndDate = $filters['end_date'] ?? '';
                    $isAllDates = !empty($filters['is_all_dates']);

                    $isTodayActive = !$isAllDates && ($activeStartDate === $todayStr && $activeEndDate === $todayStr);
                    $isYesterdayActive = !$isAllDates && ($activeStartDate === $yesterdayStr && $activeEndDate === $yesterdayStr);
                    $is7DaysActive = !$isAllDates && ($activeStartDate === $sevenDaysStr && $activeEndDate === $todayStr);
                @endphp
                <span class="small text-muted me-1 fw-semibold"><i class="ti ti-calendar-time me-1"></i>Pilih Cepat:</span>
                <button type="button" class="btn btn-xs py-1 px-2 rounded-pill {{ $isTodayActive ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }}" onclick="applyDatePreset('today')" style="font-size: 0.75rem;">
                    <i class="ti ti-calendar-event me-1"></i>Hari Ini
                </button>
                <button type="button" class="btn btn-xs py-1 px-2 rounded-pill {{ $isYesterdayActive ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }}" onclick="applyDatePreset('yesterday')" style="font-size: 0.75rem;">
                    <i class="ti ti-history me-1"></i>Hari Kemarin
                </button>
                <button type="button" class="btn btn-xs py-1 px-2 rounded-pill {{ $is7DaysActive ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }}" onclick="applyDatePreset('7days')" style="font-size: 0.75rem;">
                    7 Hari Terakhir
                </button>
                <button type="button" class="btn btn-xs py-1 px-2 rounded-pill {{ $isAllDates ? 'btn-primary text-white shadow-sm' : 'btn-outline-secondary' }}" onclick="applyDatePreset('all')" style="font-size: 0.75rem;">
                    Semua Tanggal
                </button>
            </div>
            <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 border-top mt-2">
                <!-- Export / Download Buttons with active filters & Shift/Chip Dropdown -->
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="small fw-semibold text-muted"><i class="ti ti-download me-1"></i>Download Data:</span>

                    @php
                        $currentShift = $filters['shift'] ?? 'All Shift';
                        $currentChip = $filters['jenis_chip'] ?? 'All Chip';

                        $exportParams = request()->except(['page']);
                        if (!$isAllDates) {
                            if (!empty($activeStartDate)) $exportParams['start_date'] = $activeStartDate;
                            if (!empty($activeEndDate)) $exportParams['end_date'] = $activeEndDate;
                        } else {
                            $exportParams['date_preset'] = 'all';
                            unset($exportParams['start_date'], $exportParams['end_date']);
                        }
                        $exportQueryString = http_build_query($exportParams);

                        $dateBadgeLabel = $isAllDates ? 'Semua Tgl' : ($isTodayActive ? 'Hari Ini' : ($isYesterdayActive ? 'Kemarin' : ($activeStartDate ? date('d/m', strtotime($activeStartDate)) . ($activeStartDate !== $activeEndDate ? '-' . date('d/m', strtotime($activeEndDate)) : '') : '')));
                    @endphp

                    <!-- Excel Download Button with Shift and Chip Selector Dropdown -->
                    <div class="btn-group">
                        <a href="{{ url('pendataan/export-excel') }}?{{ $exportQueryString }}" class="btn btn-sm btn-success px-3 shadow-sm" title="Download data dalam format Excel">
                            <i class="ti ti-file-spreadsheet me-1"></i> Excel (.xlsx)
                            @if($dateBadgeLabel)
                                <span class="badge bg-white text-success ms-1 fw-bold">{{ $dateBadgeLabel }}</span>
                            @endif
                            @if($currentShift !== 'All Shift')
                                <span class="badge bg-white text-success ms-1 fw-bold">{{ $currentShift }}</span>
                            @endif
                            @if($currentChip !== 'All Chip')
                                <span class="badge bg-white text-dark ms-1 fw-bold">{{ $currentChip }}</span>
                            @endif
                        </a>
                        <button type="button" class="btn btn-sm btn-success dropdown-toggle dropdown-toggle-split shadow-sm" data-bs-toggle="dropdown" aria-expanded="false" title="Pilihan Download Excel">
                            <span class="visually-hidden">Pilihan Download</span>
                        </button>
                        <ul class="dropdown-menu shadow">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Download Berdasarkan Periode</h6></li>
                            <li><a class="dropdown-item" href="{{ url('pendataan/export-excel') }}?{{ $exportQueryString }}"><i class="ti ti-file-check me-2 text-success"></i>Sesuai Tampilan Filter Saat Ini</a></li>
                            <li><a class="dropdown-item" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['start_date' => $yesterdayStr, 'end_date' => $yesterdayStr, 'date_preset' => 'yesterday'])) }}"><i class="ti ti-history me-2 text-warning"></i>Khusus Hari Kemarin ({{ date('d/m/Y', strtotime($yesterdayStr)) }})</a></li>
                            <li><a class="dropdown-item" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['start_date' => $todayStr, 'end_date' => $todayStr, 'date_preset' => 'today'])) }}"><i class="ti ti-calendar-event me-2 text-primary"></i>Khusus Hari Ini ({{ date('d/m/Y', strtotime($todayStr)) }})</a></li>
                            <li><a class="dropdown-item" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['start_date' => '', 'end_date' => '', 'date_preset' => 'all'])) }}"><i class="ti ti-world me-2 text-info"></i>Semua Tanggal</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Download Berdasarkan Jenis Chip</h6></li>
                            <li><a class="dropdown-item {{ $currentChip === 'All Chip' ? 'active' : '' }}" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['jenis_chip' => 'All Chip'])) }}"><i class="ti ti-cpu me-2"></i>Semua Chip (All Chip)</a></li>
                            <li><a class="dropdown-item {{ $currentChip === 'KTTS' ? 'active' : '' }}" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['jenis_chip' => 'KTTS'])) }}"><i class="ti ti-cpu me-2 text-primary"></i>Khusus Chip KTTS</a></li>
                            <li><a class="dropdown-item {{ $currentChip === 'KBTG' ? 'active' : '' }}" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['jenis_chip' => 'KBTG'])) }}"><i class="ti ti-cpu-2 me-2" style="color: #6f42c1;"></i>Khusus Chip KBTG</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Download Berdasarkan Shift</h6></li>
                            <li><a class="dropdown-item {{ $currentShift === 'All Shift' ? 'active' : '' }}" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['shift' => 'All Shift'])) }}"><i class="ti ti-layers-subtract me-2"></i>All Shift (Semua)</a></li>
                            <li><a class="dropdown-item {{ $currentShift === 'Shift 1' ? 'active' : '' }}" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['shift' => 'Shift 1'])) }}"><i class="ti ti-sun me-2 text-warning"></i>Khusus Shift 1</a></li>
                            <li><a class="dropdown-item {{ $currentShift === 'Shift 2' ? 'active' : '' }}" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['shift' => 'Shift 2'])) }}"><i class="ti ti-sunset me-2 text-primary"></i>Khusus Shift 2</a></li>
                            <li><a class="dropdown-item {{ $currentShift === 'Shift 3' ? 'active' : '' }}" href="{{ url('pendataan/export-excel') }}?{{ http_build_query(array_merge($exportParams, ['shift' => 'Shift 3'])) }}"><i class="ti ti-moon-stars me-2 text-info"></i>Khusus Shift 3</a></li>
                        </ul>
                    </div>

                    <!-- PDF Download Button with Shift and Chip Selector Dropdown -->
                    <div class="btn-group">
                        <a href="{{ url('pendataan/export-pdf') }}?{{ $exportQueryString }}" class="btn btn-sm btn-danger px-3 shadow-sm" title="Download data dalam format PDF" target="_blank">
                            <i class="ti ti-file-type-pdf me-1"></i> PDF
                            @if($dateBadgeLabel)
                                <span class="badge bg-white text-danger ms-1 fw-bold">{{ $dateBadgeLabel }}</span>
                            @endif
                            @if($currentShift !== 'All Shift')
                                <span class="badge bg-white text-danger ms-1 fw-bold">{{ $currentShift }}</span>
                            @endif
                            @if($currentChip !== 'All Chip')
                                <span class="badge bg-white text-dark ms-1 fw-bold">{{ $currentChip }}</span>
                            @endif
                        </a>
                        <button type="button" class="btn btn-sm btn-danger dropdown-toggle dropdown-toggle-split shadow-sm" data-bs-toggle="dropdown" aria-expanded="false" title="Pilihan Download PDF">
                            <span class="visually-hidden">Pilihan Download</span>
                        </button>
                        <ul class="dropdown-menu shadow">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Download Berdasarkan Periode</h6></li>
                            <li><a class="dropdown-item" href="{{ url('pendataan/export-pdf') }}?{{ $exportQueryString }}" target="_blank"><i class="ti ti-file-check me-2 text-danger"></i>Sesuai Tampilan Filter Saat Ini</a></li>
                            <li><a class="dropdown-item" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['start_date' => $yesterdayStr, 'end_date' => $yesterdayStr, 'date_preset' => 'yesterday'])) }}" target="_blank"><i class="ti ti-history me-2 text-warning"></i>Khusus Hari Kemarin ({{ date('d/m/Y', strtotime($yesterdayStr)) }})</a></li>
                            <li><a class="dropdown-item" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['start_date' => $todayStr, 'end_date' => $todayStr, 'date_preset' => 'today'])) }}" target="_blank"><i class="ti ti-calendar-event me-2 text-primary"></i>Khusus Hari Ini ({{ date('d/m/Y', strtotime($todayStr)) }})</a></li>
                            <li><a class="dropdown-item" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['start_date' => '', 'end_date' => '', 'date_preset' => 'all'])) }}" target="_blank"><i class="ti ti-world me-2 text-info"></i>Semua Tanggal</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Download Berdasarkan Jenis Chip</h6></li>
                            <li><a class="dropdown-item {{ $currentChip === 'All Chip' ? 'active' : '' }}" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['jenis_chip' => 'All Chip'])) }}" target="_blank"><i class="ti ti-cpu me-2"></i>Semua Chip (All Chip)</a></li>
                            <li><a class="dropdown-item {{ $currentChip === 'KTTS' ? 'active' : '' }}" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['jenis_chip' => 'KTTS'])) }}" target="_blank"><i class="ti ti-cpu me-2 text-primary"></i>Khusus Chip KTTS</a></li>
                            <li><a class="dropdown-item {{ $currentChip === 'KBTG' ? 'active' : '' }}" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['jenis_chip' => 'KBTG'])) }}" target="_blank"><i class="ti ti-cpu-2 me-2" style="color: #6f42c1;"></i>Khusus Chip KBTG</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Download Berdasarkan Shift</h6></li>
                            <li><a class="dropdown-item {{ $currentShift === 'All Shift' ? 'active' : '' }}" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['shift' => 'All Shift'])) }}" target="_blank"><i class="ti ti-layers-subtract me-2"></i>All Shift (Semua)</a></li>
                            <li><a class="dropdown-item {{ $currentShift === 'Shift 1' ? 'active' : '' }}" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['shift' => 'Shift 1'])) }}" target="_blank"><i class="ti ti-sun me-2 text-warning"></i>Khusus Shift 1</a></li>
                            <li><a class="dropdown-item {{ $currentShift === 'Shift 2' ? 'active' : '' }}" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['shift' => 'Shift 2'])) }}" target="_blank"><i class="ti ti-sunset me-2 text-primary"></i>Khusus Shift 2</a></li>
                            <li><a class="dropdown-item {{ $currentShift === 'Shift 3' ? 'active' : '' }}" href="{{ url('pendataan/export-pdf') }}?{{ http_build_query(array_merge($exportParams, ['shift' => 'Shift 3'])) }}" target="_blank"><i class="ti ti-moon-stars me-2 text-info"></i>Khusus Shift 3</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Filter Action Buttons -->
                <div class="d-flex align-items-center gap-2">
                    @if(!empty($filters['start_date']) || !empty($filters['end_date']) || !empty($filters['nama']) || !empty($filters['nama_produk']) || (!empty($filters['shift']) && $filters['shift'] !== 'All Shift') || (!empty($filters['jenis_chip']) && $filters['jenis_chip'] !== 'All Chip'))
                        <a href="{{ route('pendataan.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-refresh me-1"></i> Reset Filter
                        </a>
                    @endif
                    <button type="submit" class="btn btn-sm btn-primary px-3">
                        <i class="ti ti-search me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
                <i class="ti ti-table me-2 text-primary"></i>Data Pendataan
            </h5>

            <!-- Quick Date, Shift & Chip Pill Navigations -->
            @php
                $activeShift = $filters['shift'] ?? 'All Shift';
                $activeChip = $filters['jenis_chip'] ?? 'All Chip';
                $pillQuery = request()->except(['page', 'shift']);
                $chipPillQuery = request()->except(['page', 'jenis_chip']);
                $datePillQuery = request()->except(['page', 'start_date', 'end_date', 'date_preset', 'all_dates']);
            @endphp
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <!-- Date Pills -->
                <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border" role="group" aria-label="Filter Tanggal Cepat">
                    <a href="{{ route('pendataan.index', array_merge($datePillQuery, ['start_date' => $todayStr, 'end_date' => $todayStr])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $isTodayActive ? 'btn-success text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Tampilkan data hari ini">
                        <i class="ti ti-calendar-event me-1"></i>Hari Ini
                    </a>
                    <a href="{{ route('pendataan.index', array_merge($datePillQuery, ['start_date' => $yesterdayStr, 'end_date' => $yesterdayStr])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $isYesterdayActive ? 'btn-success text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Tampilkan data hari kemarin">
                        <i class="ti ti-history me-1"></i>Kemarin
                    </a>
                    <a href="{{ route('pendataan.index', array_merge($datePillQuery, ['start_date' => $sevenDaysStr, 'end_date' => $todayStr])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $is7DaysActive ? 'btn-success text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Tampilkan data 7 hari terakhir">
                        7 Hari
                    </a>
                    <a href="{{ route('pendataan.index', array_merge($datePillQuery, ['date_preset' => 'all'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $isAllDates ? 'btn-success text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Tampilkan semua data tanpa batasan tanggal">
                        Semua
                    </a>
                </div>

                <!-- Shift Pills -->
                <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border" role="group" aria-label="Filter Shift Cepat">
                    <a href="{{ route('pendataan.index', array_merge($pillQuery, ['shift' => 'All Shift'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $activeShift === 'All Shift' || empty($activeShift) ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Lihat semua data shift">
                        <i class="ti ti-layers-subtract me-1"></i>All Shift
                    </a>
                    <a href="{{ route('pendataan.index', array_merge($pillQuery, ['shift' => 'Shift 1'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $activeShift === 'Shift 1' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Hanya tampilkan Shift 1">
                        <i class="ti ti-sun me-1"></i>Shift 1
                    </a>
                    <a href="{{ route('pendataan.index', array_merge($pillQuery, ['shift' => 'Shift 2'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $activeShift === 'Shift 2' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Hanya tampilkan Shift 2">
                        <i class="ti ti-sunset me-1"></i>Shift 2
                    </a>
                    <a href="{{ route('pendataan.index', array_merge($pillQuery, ['shift' => 'Shift 3'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $activeShift === 'Shift 3' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Hanya tampilkan Shift 3">
                        <i class="ti ti-moon-stars me-1"></i>Shift 3
                    </a>
                </div>

                <!-- Chip Pills -->
                <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border" role="group" aria-label="Filter Chip Cepat">
                    <a href="{{ route('pendataan.index', array_merge($chipPillQuery, ['jenis_chip' => 'All Chip'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $activeChip === 'All Chip' || empty($activeChip) ? 'btn-dark text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Lihat semua chip">
                        <i class="ti ti-cpu me-1"></i>All Chip
                    </a>
                    <a href="{{ route('pendataan.index', array_merge($chipPillQuery, ['jenis_chip' => 'KTTS'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $activeChip === 'KTTS' ? 'btn-primary text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       title="Hanya tampilkan KTTS">
                        <i class="ti ti-cpu me-1"></i>KTTS
                    </a>
                    <a href="{{ route('pendataan.index', array_merge($chipPillQuery, ['jenis_chip' => 'KBTG'])) }}" 
                       class="btn btn-sm rounded-pill px-3 {{ $activeChip === 'KBTG' ? 'text-white fw-bold shadow-sm' : 'btn-light text-muted' }}"
                       style="{{ $activeChip === 'KBTG' ? 'background-color: #6f42c1; border-color: #6f42c1;' : '' }}"
                       title="Hanya tampilkan KBTG">
                        <i class="ti ti-cpu-2 me-1"></i>KBTG
                    </a>
                </div>
            </div>
        </div>
        <span class="badge bg-light text-secondary border px-3 py-2">
            Menampilkan {{ $pendataans->count() }} dari {{ $pendataans->total() }} data
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablePendataan">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Nama Produk</th>
                        <th class="text-center" style="width: 85px;">Chip</th>
                        <th class="text-center" style="width: 130px;">Status</th>
                        <th class="text-end">Harga Qty</th>
                        <th class="text-end">Total Harga</th>
                        <th class="text-center" style="width: 80px;">Qty</th>
                        <th style="min-width: 140px;">Tanggal & Jam</th>
                        <th>Nama</th>
                        <th class="text-center" style="width: 90px;">Gambar</th>
                        <th class="text-center pe-4" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendataans as $item)
                    <tr>
                        <td class="ps-4 text-muted fw-semibold">
                            {{ ($pendataans->currentPage() - 1) * $pendataans->perPage() + $loop->iteration }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item->nama_produk }}</div>
                            @if($item->deskripsi)
                                <small class="text-muted d-block text-truncate" style="max-width: 250px;" title="{{ $item->deskripsi }}">
                                    {{ Str::limit(str_replace(["\r", "\n"], ' ', $item->deskripsi), 45) }}
                                </small>
                            @endif
                            @if($item->alasan_edit)
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle mt-1" title="Alasan Edit: {{ $item->alasan_edit }}">
                                    <i class="ti ti-history me-1"></i>Pernah Diedit
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(($item->jenis_chip ?? 'KTTS') === 'KBTG')
                                <span class="badge border px-2 py-1 fw-bold text-white shadow-sm" style="background-color: #6f42c1; font-size: 0.75rem;">
                                    <i class="ti ti-cpu-2 me-1"></i>KBTG
                                </span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                    <i class="ti ti-cpu me-1"></i>KTTS
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            {!! $item->status_badge_html !!}
                        </td>
                        <td class="text-end fw-semibold text-secondary">
                            {{ $item->formatted_harga_qty }}
                        </td>
                        <td class="text-end fw-bold text-success">
                            {{ $item->formatted_total_harga }}
                        </td>
                        <td class="text-center">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-1 fw-bold">
                                {{ $item->qty }}
                            </span>
                        </td>
                        <td>
                            <div class="text-dark small fw-medium">{{ $item->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted"><i class="ti ti-clock me-1"></i>{{ $item->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border px-2 py-1 fw-semibold">
                                <i class="ti ti-user me-1 text-primary"></i>{{ $item->nama }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($item->gambar)
                                <a href="javascript:void(0)" onclick="previewLightboxImage('{{ $item->gambar_url }}', '{{ addslashes($item->nama_produk) }}')" title="Lihat gambar">
                                    <img src="{{ $item->gambar_url }}" alt="Thumbnail" loading="lazy" decoding="async" class="rounded border shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                </a>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center pe-4 text-nowrap">
                            <!-- Detail Button -->
                            <button type="button" class="btn btn-sm btn-icon btn-outline-info" title="Lihat Detail" onclick="openDetailModal({{ $item->id }})">
                                <i class="ti ti-eye"></i>
                            </button>

                            <!-- Edit Button -->
                            <button type="button" class="btn btn-sm btn-icon btn-outline-warning ms-1" title="Edit Data" onclick="openEditModal({{ json_encode($item) }})">
                                <i class="ti ti-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="ti ti-inbox fs-1 d-block mb-2 text-secondary"></i>
                            Belum ada data pendataan yang tersimpan. Silakan klik tombol <strong>Tambah Pendataan</strong> untuk mulai mencatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pendataans->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
        <span class="text-muted small">
            Halaman {{ $pendataans->currentPage() }} dari {{ $pendataans->lastPage() }}
        </span>
        <div>
            {{ $pendataans->links() }}
        </div>
    </div>
    @endif
</div>

<!-- ================= MODAL TAMBAH PENDATAAN ================= -->
<div class="modal fade" id="modalTambahPendataan" tabindex="-1" aria-labelledby="modalTambahPendataanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('pendataan.store') }}" method="POST" enctype="multipart/form-data" id="formTambahPendataan">
                @csrf
                {{-- Idempotency token: refreshed every time modal opens (see JS) to prevent double-submit --}}
                <input type="hidden" name="_idempotency_token" id="tambah_idempotency_token" value="">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahPendataanLabel">
                        <i class="ti ti-plus-circle text-primary me-2"></i>Tambah Pendataan Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Field: Nama Penanggung Jawab (Terkunci Sesuai Akun Staf Aktif) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Penanggung Jawab</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-primary"><i class="ti ti-user-check"></i></span>
                            <input type="text" class="form-control bg-light fw-bold text-dark" value="{{ $activeStaffNama }}" readonly disabled>
                        </div>
                        <div class="form-text text-muted small">
                            <i class="ti ti-lock me-1 text-primary"></i>Tercatat otomatis atas nama <strong>{{ $activeStaffNama }}</strong> (sesuai akun staf yang sedang membuka fitur ini).
                        </div>
                    </div>

                    <!-- Field: Pilihan Jenis Chip (KTTS / KBTG) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark mb-1">
                            Jenis Chip <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="border rounded-3 p-2 d-flex align-items-center bg-white shadow-sm h-100" for="tambah_chip_ktts" style="cursor: pointer;">
                                    <input class="form-check-input me-2 mt-0" type="radio" name="jenis_chip" id="tambah_chip_ktts" value="KTTS" checked required>
                                    <div>
                                        <div class="fw-bold text-primary"><i class="ti ti-cpu me-1"></i>KTTS</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Kartu / Chip KTTS</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="border rounded-3 p-2 d-flex align-items-center bg-white shadow-sm h-100" for="tambah_chip_kbtg" style="cursor: pointer;">
                                    <input class="form-check-input me-2 mt-0" type="radio" name="jenis_chip" id="tambah_chip_kbtg" value="KBTG" required>
                                    <div>
                                        <div class="fw-bold" style="color: #6f42c1;"><i class="ti ti-cpu-2 me-1"></i>KBTG</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Kartu / Chip KBTG</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Field: Deskripsi (Auto-Pilah) -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold text-dark mb-0">Deskripsi / Rincian Transaksi</label>
                            <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" id="btnPilahDeskripsiTambah" title="Pilah teks secara manual">
                                <i class="ti ti-wand me-1"></i> Pilah Teks
                            </button>
                        </div>
                        <textarea name="deskripsi" id="tambah_deskripsi" rows="5" class="form-control" placeholder="Contoh:&#10;AIGO Bronet 7GB 28hr&#10;Rp 34.500&#10;-&#10;1&#10;+&#10;Metode Pembayaran&#10;...&#10;Total Tagihan&#10;Rp 34.500&#10;Total Qty&#10;1"></textarea>
                        <div class="form-text">
                            <i class="ti ti-sparkles text-primary me-1"></i> Anda dapat copy & paste teks transaksi dari aplikasi di sini. Sistem akan otomatis memilah nama produk, harga qty, total harga, dan qty!
                        </div>
                    </div>

                    <!-- Live Parsed Notification Badge -->
                    <div id="parseBadgeTambah" class="alert alert-success border-0 py-2 px-3 small d-none mb-3 d-flex align-items-center">
                        <i class="ti ti-check-circle fs-5 me-2 text-success"></i>
                        <span id="parseMessageTambah">Teks berhasil dipilah secara otomatis!</span>
                    </div>

                    <!-- Parsed Result Fields Card -->
                    <div class="card bg-light border p-3 rounded-3 mb-3">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                            <i class="ti ti-clipboard-data text-primary me-2"></i>Hasil Pemilahan (Dapat Diedit Sesuai Kebutuhan)
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="nama_produk" id="tambah_nama_produk" class="form-control" placeholder="Contoh: AIGO Bronet 7GB 28hr" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Harga Qty (Satuan)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Rp</span>
                                    <input type="text" name="harga_qty" id="tambah_harga_qty" class="form-control js-currency-input" placeholder="0" oninput="calculateTotalTambah()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Qty</label>
                                <input type="number" name="qty" id="tambah_qty" class="form-control text-center" value="1" min="1" oninput="onQtyChangeTambah()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Total Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Rp</span>
                                    <input type="text" name="total_harga" id="tambah_total_harga" class="form-control js-currency-input" placeholder="0" oninput="calculateUnitTambah()">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Field: Upload Gambar with Ctrl+V Paste Area -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark">Upload Gambar (Opsional)</label>
                        <div class="upload-paste-zone border border-2 border-dashed rounded-3 p-3 text-center position-relative" id="dropzoneTambah" style="cursor: pointer; background: #fafbfc;">
                            <input type="file" name="gambar" id="tambah_gambar_input" class="d-none" accept="image/*">
                            <input type="hidden" name="gambar_base64" id="tambah_gambar_base64">

                            <!-- Placeholder inside dropzone -->
                            <div id="dropzonePlaceholderTambah" class="py-2">
                                <i class="ti ti-photo-plus fs-1 text-primary opacity-75 d-block mb-1"></i>
                                <span class="fw-semibold text-dark d-block">Klik untuk pilih gambar atau tekan <kbd class="bg-primary text-white px-2 py-1 rounded">Ctrl + V</kbd> untuk Paste Screenshot</span>
                                <span class="text-muted small">Mendukung format JPG, PNG, WEBP (Maksimal 10MB)</span>
                            </div>

                            <!-- Preview container inside dropzone -->
                            <div id="dropzonePreviewTambah" class="d-none text-center">
                                <div class="position-relative d-inline-block">
                                    <img id="previewImgTambah" src="" alt="Preview Gambar" class="rounded border shadow-sm" style="max-height: 180px; max-width: 100%; object-fit: contain;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1" style="width: 26px; height: 26px; line-height: 1;" onclick="removeImageTambah(event)" title="Hapus gambar">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <div class="mt-2 text-muted small" id="previewFilenameTambah">Gambar siap diunggah</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Pendataan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL EDIT PENDATAAN ================= -->
<div class="modal fade" id="modalEditPendataan" tabindex="-1" aria-labelledby="modalEditPendataanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="" method="POST" enctype="multipart/form-data" id="formEditPendataan">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditPendataanLabel">
                        <i class="ti ti-edit text-warning me-2"></i>Edit Data Pendataan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Field: Nama Penanggung Jawab (Terkunci) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Nama Penanggung Jawab</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="ti ti-user-lock"></i></span>
                            <input type="text" id="edit_nama" class="form-control bg-light fw-bold text-dark" readonly disabled>
                        </div>
                        <div class="form-text text-muted small">
                            <i class="ti ti-lock me-1"></i>Nama penanggung jawab transaksi terkunci dan tidak dapat diubah.
                        </div>
                    </div>

                    <!-- Field: Pilihan Jenis Chip (KTTS / KBTG) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark mb-1">
                            Jenis Chip <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="border rounded-3 p-2 d-flex align-items-center bg-white shadow-sm h-100" for="edit_chip_ktts" style="cursor: pointer;">
                                    <input class="form-check-input me-2 mt-0" type="radio" name="jenis_chip" id="edit_chip_ktts" value="KTTS" required>
                                    <div>
                                        <div class="fw-bold text-primary"><i class="ti ti-cpu me-1"></i>KTTS</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Kartu / Chip KTTS</small>
                                    </div>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="border rounded-3 p-2 d-flex align-items-center bg-white shadow-sm h-100" for="edit_chip_kbtg" style="cursor: pointer;">
                                    <input class="form-check-input me-2 mt-0" type="radio" name="jenis_chip" id="edit_chip_kbtg" value="KBTG" required>
                                    <div>
                                        <div class="fw-bold" style="color: #6f42c1;"><i class="ti ti-cpu-2 me-1"></i>KBTG</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Kartu / Chip KBTG</small>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Field: Deskripsi -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold text-dark mb-0">Deskripsi / Rincian Transaksi</label>
                            <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" id="btnPilahDeskripsiEdit" title="Pilah teks secara manual">
                                <i class="ti ti-wand me-1"></i> Pilah Ulang Teks
                            </button>
                        </div>
                        <textarea name="deskripsi" id="edit_deskripsi" rows="4" class="form-control"></textarea>
                    </div>

                    <!-- Live Parsed Notification Badge -->
                    <div id="parseBadgeEdit" class="alert alert-success border-0 py-2 px-3 small d-none mb-3 d-flex align-items-center">
                        <i class="ti ti-check-circle fs-5 me-2 text-success"></i>
                        <span>Teks berhasil dipilah ulang!</span>
                    </div>

                    <!-- Parsed Result Fields Card -->
                    <div class="card bg-light border p-3 rounded-3 mb-3">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                            <i class="ti ti-clipboard-data text-primary me-2"></i>Rincian Produk & Harga
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-semibold text-muted">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="nama_produk" id="edit_nama_produk" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Harga Qty (Satuan)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Rp</span>
                                    <input type="text" name="harga_qty" id="edit_harga_qty" class="form-control js-currency-input" oninput="calculateTotalEdit()">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Qty</label>
                                <input type="number" name="qty" id="edit_qty" class="form-control text-center" min="1" oninput="onQtyChangeEdit()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted">Total Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white">Rp</span>
                                    <input type="text" name="total_harga" id="edit_total_harga" class="form-control js-currency-input" oninput="calculateUnitEdit()">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Field: Upload / Replace Gambar -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark">Gambar</label>
                        <div class="upload-paste-zone border border-2 border-dashed rounded-3 p-3 text-center position-relative" id="dropzoneEdit" style="cursor: pointer; background: #fafbfc;">
                            <input type="file" name="gambar" id="edit_gambar_input" class="d-none" accept="image/*">
                            <input type="hidden" name="gambar_base64" id="edit_gambar_base64">
                            <input type="hidden" name="hapus_gambar" id="edit_hapus_gambar" value="0">

                            <!-- Placeholder inside dropzone -->
                            <div id="dropzonePlaceholderEdit" class="py-2">
                                <i class="ti ti-photo-plus fs-1 text-primary opacity-75 d-block mb-1"></i>
                                <span class="fw-semibold text-dark d-block">Klik untuk ganti gambar atau tekan <kbd class="bg-primary text-white px-2 py-1 rounded">Ctrl + V</kbd> untuk Paste Screenshot baru</span>
                                <span class="text-muted small">Mendukung format JPG, PNG, WEBP (Maksimal 10MB)</span>
                            </div>

                            <!-- Preview container inside dropzone -->
                            <div id="dropzonePreviewEdit" class="d-none text-center">
                                <div class="position-relative d-inline-block">
                                    <img id="previewImgEdit" src="" alt="Preview Gambar" class="rounded border shadow-sm" style="max-height: 180px; max-width: 100%; object-fit: contain;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 rounded-circle p-1" style="width: 26px; height: 26px; line-height: 1;" onclick="removeImageEdit(event)" title="Hapus gambar">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                                <div class="mt-2 text-muted small" id="previewFilenameEdit">Gambar saat ini</div>
                            </div>
                        </div>
                    </div>

                    <!-- Field: Alasan Edit (Wajib) -->
                    <div class="mt-3 pt-3 border-top">
                        <label class="form-label fw-semibold text-danger">
                            <i class="ti ti-message-exclamation me-1"></i>Alasan Edit <span class="text-danger">*</span>
                        </label>
                        <textarea name="alasan_edit" id="edit_alasan" rows="2" class="form-control border-warning" required placeholder="Wajib sertakan alasan perubahan data (contoh: Koreksi salah ketik nominal / revisi qty)..."></textarea>
                        <div class="form-text text-muted small">
                            <i class="ti ti-info-circle me-1"></i>Setiap perubahan data wajib disertai alasan yang jelas untuk pencatatan riwayat (audit trail).
                        </div>
                        <div id="riwayatAlasanSection" class="mt-2 d-none">
                            <small class="text-muted fw-semibold d-block mb-1">Riwayat Edit Sebelumnya:</small>
                            <pre id="riwayatAlasanText" class="bg-light p-2 rounded border small text-muted mb-0" style="white-space: pre-wrap; word-break: break-word; max-height: 100px; overflow-y: auto; font-size: 11px;"></pre>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-semibold">
                        <i class="ti ti-device-floppy me-1"></i> Perbarui Pendataan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL DETAIL PENDATAAN ================= -->
<div class="modal fade" id="modalDetailPendataan" tabindex="-1" aria-labelledby="modalDetailPendataanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="modalDetailPendataanLabel">
                    <i class="ti ti-file-description text-primary me-2"></i>Detail Pendataan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3 text-center pb-3 border-bottom">
                    <h5 class="fw-bold text-dark mb-1" id="detail_nama_produk">-</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 fw-bold fs-6" id="detail_total_harga">Rp 0</span>
                    <div id="detail_status_badge" class="mt-2"></div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <small class="text-muted d-block">Harga Qty:</small>
                        <span class="fw-semibold text-dark" id="detail_harga_qty">Rp 0</span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Total Qty:</small>
                        <span class="fw-semibold text-dark" id="detail_qty">0</span>
                    </div>
                    <div class="col-4">
                        <small class="text-muted d-block">Jenis Chip:</small>
                        <span class="badge bg-primary px-2 py-1 fw-bold" id="detail_chip">KTTS</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Nama:</small>
                        <span class="fw-semibold text-dark" id="detail_nama">-</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Tanggal & Jam:</small>
                        <span class="fw-semibold text-dark" id="detail_tanggal">-</span>
                    </div>
                </div>

                <div class="mb-3" id="detail_deskripsi_section">
                    <small class="text-muted fw-semibold d-block mb-1">Rincian Deskripsi Mentah:</small>
                    <pre class="bg-light p-3 rounded border text-secondary small mb-0" id="detail_deskripsi" style="white-space: pre-wrap; word-break: break-word; max-height: 200px; overflow-y: auto;"></pre>
                </div>

                <div class="mb-3 d-none" id="detail_alasan_section">
                    <small class="text-danger fw-semibold d-block mb-1">
                        <i class="ti ti-history me-1"></i>Riwayat Alasan Perubahan Data:
                    </small>
                    <pre class="bg-warning bg-opacity-10 border border-warning-subtle text-dark p-3 rounded small mb-0" id="detail_alasan" style="white-space: pre-wrap; word-break: break-word; max-height: 150px; overflow-y: auto; font-family: inherit; font-size: 12px;"></pre>
                </div>

                <div id="detail_gambar_section" class="text-center mt-3 pt-3 border-top d-none">
                    <small class="text-muted fw-semibold d-block mb-2 text-start">Lampiran Gambar:</small>
                    <a href="javascript:void(0)" id="detail_gambar_link" target="_blank">
                        <img id="detail_gambar" src="" alt="Gambar Pendataan" class="rounded border shadow-sm img-fluid" style="max-height: 240px; cursor: pointer;">
                    </a>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL LIGHTBOX GAMBAR ================= -->
<div class="modal fade" id="modalLightboxGambar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent shadow-none">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close" style="z-index: 1055;"></button>
                <img id="lightboxImg" src="" alt="Preview Besar" class="rounded shadow-lg img-fluid" style="max-height: 85vh; background: #fff;">
                <div class="text-white mt-2 fw-semibold" id="lightboxCaption"></div>
            </div>
        </div>
    </div>
</div>

<!-- ================= MODAL TEMPEL / PROSES SMS MANUAL ================= -->
<div class="modal fade" id="modalManualSms" tabindex="-1" aria-labelledby="modalManualSmsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center" id="modalManualSmsLabel">
                    <i class="ti ti-message-dots me-2 fs-4"></i>Tempel & Proses SMS Voucher
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formManualSms" onsubmit="submitManualSms(event)">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small border-0 mb-3 d-flex align-items-center gap-2">
                        <i class="ti ti-info-circle fs-4 text-info flex-shrink-0"></i>
                        <div>
                            Tempelkan teks SMS transaksi dari Telegram atau HP di sini. Sistem akan memilah produk, mencocokkan ke transaksi <strong>Pending</strong>, mengubahnya ke <strong>Sukses</strong>, dan bot <code>@intel_awgbot</code> akan otomatis membalas ke grup <strong>AWG KBTG</strong>!
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Isi Pesan SMS:</label>
                        <textarea class="form-control font-monospace" id="inputManualSmsText" rows="4" placeholder="Contoh: 150 AIGO Mini 5GB + Kuota di Kotamu 14hr senilai Rp3525000 expired sd 04-04-2027..." required></textarea>
                        <div class="form-text small text-muted">Bisa langsung ditempel meskipun ada awalan <code>App: ... Title: ... Message: ...</code></div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm px-4 fw-bold" id="btnSubmitManualSms">
                        <i class="ti ti-send me-1"></i> Proses & Cocokkan SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// =========================================================================
// TEXT PARSER LOGIC (CLIENT SIDE)
// =========================================================================
function cleanPriceText(raw) {
    if (!raw) return 0;
    raw = String(raw).trim().replace(/[^\d.,]/g, '');
    if (!raw) return 0;

    if (raw.includes('.') && raw.includes(',')) {
        raw = raw.replace(/\./g, '').replace(',', '.');
    } else if (raw.includes('.')) {
        const parts = raw.split('.');
        if (parts.length > 1 && parts[parts.length - 1].length === 3) {
            raw = parts.join('');
        }
    } else if (raw.includes(',')) {
        const parts = raw.split(',');
        if (parts.length > 1 && parts[parts.length - 1].length === 3) {
            raw = parts.join('');
        } else {
            raw = raw.replace(',', '.');
        }
    }
    return parseFloat(raw) || 0;
}

function formatRupiahNumber(num) {
    if (!num || isNaN(num)) return '0';
    return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function parseTransactionText(text) {
    const res = {
        nama_produk: '',
        harga_qty: 0,
        total_harga: 0,
        qty: 1
    };

    if (!text || !text.trim()) return res;

    const clean = text.replace(/\r\n|\r/g, '\n').trim();

    // 0. Check SMS voucher pattern (e.g. from AXIS / Telegram / Forwarder)
    const mSms = clean.match(/(?:Message:\s*)?(.+?)\s+senilai\s+(?:Rp\.?\s*)?([\d.,]+)/is);
    if (mSms) {
        let prodName = mSms[1].trim();
        prodName = prodName.replace(/^(?:App:.*?\n)?(?:Title:.*?\n)?(?:Message:\s*)?/is, '').trim();
        const nom = cleanPriceText(mSms[2]);
        if (nom > 0 && prodName) {
            res.nama_produk = prodName;
            res.total_harga = nom;
            res.harga_qty = nom;
            res.qty = 1;
            return res;
        }
    }

    // 1. Check labeled format
    const mProduk = clean.match(/(?:Nama\s*Produk|Produk)\s*[:=]\s*(.+)/i);
    if (mProduk) res.nama_produk = mProduk[1].trim();

    const mHargaQty = clean.match(/(?:Harga\s*Qty|Harga\s*Satuan|Harga\s*Per\s*Qty)\s*[:=]\s*(?:Rp\.?\s*)?([\d.,]+)/i);
    if (mHargaQty) res.harga_qty = cleanPriceText(mHargaQty[1]);

    const mTotal = clean.match(/(?:Total\s*Tagihan|Total\s*Harga|Total\s*Pembayaran|Total\s*Bayar)\s*[:=]?\s*\n?\s*(?:Rp\.?\s*)?([\d.,]+)/i);
    if (mTotal) res.total_harga = cleanPriceText(mTotal[1]);

    const mQty = clean.match(/(?:Total\s*Qty|Qty|Jumlah)\s*[:=]?\s*\n?\s*(\d+)/i);
    if (mQty) res.qty = parseInt(mQty[1], 10) || 1;

    // 2. Lines breakdown and stepper detection
    const lines = clean.split('\n').map(l => l.trim()).filter(l => l !== '');

    let stepperStartIndex = null;
    let stepperEndIndex = null;
    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        // Symbol stepper: "-" \n QTY [\n "+"]
        if (['-', '–', '—'].includes(line) && lines[i + 1] && /^\d+$/.test(lines[i + 1])) {
            stepperStartIndex = i;
            stepperEndIndex = (lines[i + 2] && ['+', '＋'].includes(lines[i + 2])) ? i + 2 : i + 1;
            if (res.qty <= 1) {
                res.qty = parseInt(lines[i + 1], 10) || 1;
            }
            break;
        }
        // Text stepper: "kurangi [jumlah]" \n QTY [\n "tambah [jumlah]"]
        if (/^kurangi(?:\s+jumlah)?$/i.test(line) && lines[i + 1] && /^\d+$/.test(lines[i + 1])) {
            stepperStartIndex = i;
            stepperEndIndex = (lines[i + 2] && /^tambah(?:\s+jumlah)?$/i.test(lines[i + 2])) ? i + 2 : i + 1;
            if (res.qty <= 1) {
                res.qty = parseInt(lines[i + 1], 10) || 1;
            }
            break;
        }
    }

    // Product name fallback
    let productIndex = 0;
    if (!res.nama_produk) {
        const ignoreKeywords = [
            'keranjang belanja', 'keranjang', 'paket', 'jumlah', 'item', 'produk',
            'konfirmasi', 'detail transaksi', 'rincian transaksi', 'metode pembayaran',
            'saldo dompul', 'pin dompul', 'pin keuangan', 'masukkan pin',
            'total tagihan', 'total qty', 'total bayar', 'total harga', 'pembayaran', 'ringkasan',
            'kurangi jumlah', 'tambah jumlah', 'kurangi', 'tambah', 'hapus item',
            'pesanan', 'rincian pesanan', 'daftar pesanan', 'detail pesanan', 'informasi pesanan',
            'checkout', 'beli', 'pembelian'
        ];

        const isIgnoredLine = function(rawLine) {
            const trimmed = rawLine.trim();
            const lower = trimmed.toLowerCase();

            if (['-', '+', '–', '—', '＋'].includes(trimmed)) return true;
            if (/^\d+$/.test(trimmed)) return true;
            if (/^(?:rp\.?|idr)\s*[\d.,]+/i.test(trimmed)) return true;
            if (/^[\d.,]+$/.test(trimmed) && /\d/.test(trimmed)) return true;
            if (/^[•\*\.\-\_\s]+$/.test(trimmed)) return true;

            if (/^\(?\d+\)?\s*paket$/i.test(trimmed)) return true;
            if (/^(?:paket|jumlah|item|keranjang)$/i.test(trimmed)) return true;

            for (let kw of ignoreKeywords) {
                if (lower === kw || lower.startsWith(kw + ' ') || lower.startsWith(kw + ':') || lower.startsWith(kw + ' -')) {
                    return true;
                }
                if (kw === 'keranjang belanja' && lower.includes('keranjang belanja')) {
                    return true;
                }
            }
            return false;
        };

        // Priority 1: If stepper exists, look backwards right before the stepper or unit price
        if (stepperStartIndex !== null) {
            for (let k = stepperStartIndex - 1; k >= 0; k--) {
                if (!isIgnoredLine(lines[k])) {
                    res.nama_produk = lines[k];
                    productIndex = k;
                    break;
                }
            }
        }

        // Priority 2: Normal forward scan if not found via backward stepper search
        if (!res.nama_produk) {
            for (let idx = 0; idx < lines.length; idx++) {
                if (!isIgnoredLine(lines[idx])) {
                    res.nama_produk = lines[idx];
                    productIndex = idx;
                    break;
                }
            }
        }
    }

    // Fallback Total Tagihan
    if (res.total_harga <= 0) {
        for (let i = 0; i < lines.length; i++) {
            if (/Total\s*(?:Tagihan|Bayar|Pembayaran|Harga)/i.test(lines[i])) {
                const matchCurr = lines[i].match(/(?:Rp\.?\s*)?([\d.,]+)/i);
                if (matchCurr && cleanPriceText(matchCurr[1]) > 0) {
                    res.total_harga = cleanPriceText(matchCurr[1]);
                    break;
                }
                if (lines[i + 1] && cleanPriceText(lines[i + 1]) > 0) {
                    res.total_harga = cleanPriceText(lines[i + 1]);
                    break;
                }
            }
        }
    }

    // Fallback Total Qty
    if (res.qty <= 1) {
        for (let i = 0; i < lines.length; i++) {
            if (/Total\s*Qty/i.test(lines[i])) {
                const m = lines[i].match(/(\d+)/);
                if (m) {
                    res.qty = parseInt(m[1], 10) || 1;
                    break;
                }
                if (lines[i + 1] && /^\d+$/.test(lines[i + 1])) {
                    res.qty = parseInt(lines[i + 1], 10) || 1;
                    break;
                }
            }
        }
    }

    // Collect all price candidates outside of the stepper lines
    const pricesBeforeStepper = [];
    const pricesAfterStepper = [];
    const allPriceCandidates = [];

    for (let idx = 0; idx < lines.length; idx++) {
        if (idx === productIndex) continue;
        if (stepperStartIndex !== null && idx >= stepperStartIndex && idx <= stepperEndIndex) {
            continue;
        }

        const line = lines[idx];
        const isRpLine = /^(?:Rp\.?|IDR)\s*[\d.,]+\s*$/i.test(line) || /(?:Rp\.?|IDR)\s*[\d.,]+/i.test(line);
        const isBareFormatted = /^[\d.,]+$/.test(line) && /[.,]/.test(line);
        if ((isRpLine || isBareFormatted) && /\d/.test(line)) {
            const p = cleanPriceText(line);
            if (p > 0) {
                allPriceCandidates.push({ index: idx, price: p });
                if (stepperStartIndex !== null) {
                    if (idx < stepperStartIndex) {
                        pricesBeforeStepper.push(p);
                    } else if (idx > stepperEndIndex) {
                        pricesAfterStepper.push(p);
                    }
                }
            }
        }
    }

    const qty = res.qty > 0 ? res.qty : 1;

    // Resolve prices based on stepper position
    if (stepperStartIndex !== null) {
        if (pricesBeforeStepper.length > 0) {
            // Case 1: Price BEFORE stepper is the unit price (e.g. Flex Mini)
            if (res.harga_qty <= 0) {
                res.harga_qty = pricesBeforeStepper[0];
            }
            if (res.total_harga <= 0) {
                if (pricesAfterStepper.length > 0) {
                    res.total_harga = pricesAfterStepper[pricesAfterStepper.length - 1];
                } else {
                    res.total_harga = Math.round(res.harga_qty * qty);
                }
            }
        } else if (pricesAfterStepper.length > 0) {
            // Case 2: NO price before stepper; price(s) appear AFTER stepper (e.g. Kuota Nonstop)
            const uniquePrices = [...new Set(pricesAfterStepper)];
            if (uniquePrices.length >= 2) {
                uniquePrices.sort((a, b) => a - b);
                const pSmall = uniquePrices[0];
                const pLarge = uniquePrices[uniquePrices.length - 1];
                if (Math.abs((pSmall * qty) - pLarge) < 2) {
                    if (res.harga_qty <= 0) res.harga_qty = pSmall;
                    if (res.total_harga <= 0) res.total_harga = pLarge;
                } else {
                    if (res.total_harga <= 0) res.total_harga = pLarge;
                    if (res.harga_qty <= 0) res.harga_qty = Math.round(pLarge / qty);
                }
            } else {
                // All prices after stepper are identical (or only 1 price exists).
                // Because there was NO price before stepper, this price is TOTAL HARGA!
                const totalCandidate = pricesAfterStepper[0];
                if (res.total_harga <= 0) {
                    res.total_harga = totalCandidate;
                }
                if (res.harga_qty <= 0) {
                    res.harga_qty = Math.round(res.total_harga / qty);
                }
            }
        }
    } else {
        // No stepper detected
        if (res.harga_qty <= 0 && allPriceCandidates.length > 0) {
            const uniquePrices = [...new Set(allPriceCandidates.map(c => c.price))];
            if (qty > 1 && uniquePrices.length === 1) {
                if (res.total_harga <= 0) {
                    res.total_harga = uniquePrices[0];
                }
                res.harga_qty = Math.round(res.total_harga / qty);
            } else {
                res.harga_qty = allPriceCandidates[0].price;
            }
        }
    }

    // Safety checks & cross calculations
    if (res.total_harga <= 0 && res.harga_qty > 0 && qty > 0) {
        res.total_harga = Math.round(res.harga_qty * qty);
    }
    if (res.harga_qty <= 0 && res.total_harga > 0 && qty > 0) {
        res.harga_qty = Math.round(res.total_harga / qty);
    }

    // If qty > 1 and harga_qty was accidentally set equal to total_harga
    if (qty > 1 && res.total_harga > 0 && res.harga_qty === res.total_harga) {
        res.harga_qty = Math.round(res.total_harga / qty);
    }

    return res;
}

// Auto-calculate total in modal Tambah
function calculateTotalTambah() {
    const hargaQty = cleanPriceText(document.getElementById('tambah_harga_qty').value);
    const qty = parseInt(document.getElementById('tambah_qty').value, 10) || 1;
    if (hargaQty > 0) {
        document.getElementById('tambah_total_harga').value = formatRupiahNumber(hargaQty * qty);
    }
}

// Auto-calculate unit in modal Tambah
function calculateUnitTambah() {
    const totalHarga = cleanPriceText(document.getElementById('tambah_total_harga').value);
    const qty = parseInt(document.getElementById('tambah_qty').value, 10) || 1;
    if (totalHarga > 0 && qty > 0) {
        document.getElementById('tambah_harga_qty').value = formatRupiahNumber(totalHarga / qty);
    }
}

function onQtyChangeTambah() {
    const hargaQty = cleanPriceText(document.getElementById('tambah_harga_qty').value);
    const totalHarga = cleanPriceText(document.getElementById('tambah_total_harga').value);
    const qty = parseInt(document.getElementById('tambah_qty').value, 10) || 1;
    if (hargaQty > 0) {
        document.getElementById('tambah_total_harga').value = formatRupiahNumber(hargaQty * qty);
    } else if (totalHarga > 0 && qty > 0) {
        document.getElementById('tambah_harga_qty').value = formatRupiahNumber(totalHarga / qty);
    }
}

// Auto-calculate total in modal Edit
function calculateTotalEdit() {
    const hargaQty = cleanPriceText(document.getElementById('edit_harga_qty').value);
    const qty = parseInt(document.getElementById('edit_qty').value, 10) || 1;
    if (hargaQty > 0) {
        document.getElementById('edit_total_harga').value = formatRupiahNumber(hargaQty * qty);
    }
}

// Auto-calculate unit in modal Edit
function calculateUnitEdit() {
    const totalHarga = cleanPriceText(document.getElementById('edit_total_harga').value);
    const qty = parseInt(document.getElementById('edit_qty').value, 10) || 1;
    if (totalHarga > 0 && qty > 0) {
        document.getElementById('edit_harga_qty').value = formatRupiahNumber(totalHarga / qty);
    }
}

function onQtyChangeEdit() {
    const hargaQty = cleanPriceText(document.getElementById('edit_harga_qty').value);
    const totalHarga = cleanPriceText(document.getElementById('edit_total_harga').value);
    const qty = parseInt(document.getElementById('edit_qty').value, 10) || 1;
    if (hargaQty > 0) {
        document.getElementById('edit_total_harga').value = formatRupiahNumber(hargaQty * qty);
    } else if (totalHarga > 0 && qty > 0) {
        document.getElementById('edit_harga_qty').value = formatRupiahNumber(totalHarga / qty);
    }
}

// =========================================================================
// CLIPBOARD IMAGE PASTE (CTRL + V) & DROPZONE HANDLING
// =========================================================================
function handleImageFile(file, isEdit) {
    if (!file || !file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const base64 = e.target.result;
        if (isEdit) {
            document.getElementById('previewImgEdit').src = base64;
            document.getElementById('dropzonePreviewEdit').classList.remove('d-none');
            document.getElementById('dropzonePlaceholderEdit').classList.add('d-none');
            document.getElementById('edit_gambar_base64').value = base64;
            document.getElementById('edit_hapus_gambar').value = '0';
            document.getElementById('previewFilenameEdit').textContent = file.name || 'Screenshot Clipboard (' + Math.round(file.size / 1024) + ' KB)';
        } else {
            document.getElementById('previewImgTambah').src = base64;
            document.getElementById('dropzonePreviewTambah').classList.remove('d-none');
            document.getElementById('dropzonePlaceholderTambah').classList.add('d-none');
            document.getElementById('tambah_gambar_base64').value = base64;
            document.getElementById('previewFilenameTambah').textContent = file.name || 'Screenshot Clipboard (' + Math.round(file.size / 1024) + ' KB)';
        }
    };
    reader.readAsDataURL(file);

    // Also populate standard file input via DataTransfer
    try {
        const dt = new DataTransfer();
        dt.items.add(file);
        const inputId = isEdit ? 'edit_gambar_input' : 'tambah_gambar_input';
        document.getElementById(inputId).files = dt.files;
    } catch (err) {
        // Fallback uses base64
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Gambar berhasil ditempel dari Clipboard!',
            showConfirmButton: false,
            timer: 2000
        });
    }
}

function removeImageTambah(e) {
    e.stopPropagation();
    document.getElementById('tambah_gambar_input').value = '';
    document.getElementById('tambah_gambar_base64').value = '';
    document.getElementById('previewImgTambah').src = '';
    document.getElementById('dropzonePreviewTambah').classList.add('d-none');
    document.getElementById('dropzonePlaceholderTambah').classList.remove('d-none');
}

function removeImageEdit(e) {
    e.stopPropagation();
    document.getElementById('edit_gambar_input').value = '';
    document.getElementById('edit_gambar_base64').value = '';
    document.getElementById('edit_hapus_gambar').value = '1';
    document.getElementById('previewImgEdit').src = '';
    document.getElementById('dropzonePreviewEdit').classList.add('d-none');
    document.getElementById('dropzonePlaceholderEdit').classList.remove('d-none');
}

// Global Ctrl+V listener
window.addEventListener('paste', function(e) {
    const modalTambah = document.getElementById('modalTambahPendataan');
    const modalEdit = document.getElementById('modalEditPendataan');

    const isTambahOpen = modalTambah && modalTambah.classList.contains('show');
    const isEditOpen = modalEdit && modalEdit.classList.contains('show');

    if (!isTambahOpen && !isEditOpen) return;

    const items = (e.clipboardData || e.originalEvent.clipboardData).items;
    for (let item of items) {
        if (item.type.indexOf('image') !== -1) {
            e.preventDefault();
            const blob = item.getAsFile();
            handleImageFile(blob, isEditOpen);
            break;
        }
    }
});

// Setup click and drag-drop on dropzones
function setupDropzone(zoneId, inputId, isEdit) {
    const zone = document.getElementById(zoneId);
    const input = document.getElementById(inputId);
    if (!zone || !input) return;

    zone.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        input.click();
    });

    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            handleImageFile(this.files[0], isEdit);
        }
    });

    zone.addEventListener('dragover', function(e) {
        e.preventDefault();
        zone.classList.add('border-primary', 'bg-light');
    });

    zone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        zone.classList.remove('border-primary', 'bg-light');
    });

    zone.addEventListener('drop', function(e) {
        e.preventDefault();
        zone.classList.remove('border-primary', 'bg-light');
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleImageFile(e.dataTransfer.files[0], isEdit);
        }
    });
}

// =========================================================================
// MODAL & ACTION HANDLERS
// =========================================================================
function openEditModal(item) {
    const form = document.getElementById('formEditPendataan');
    form.action = "{{ url('pendataan') }}/" + item.id;

    document.getElementById('edit_nama').value = item.nama || '';
    document.getElementById('edit_deskripsi').value = item.deskripsi || '';
    document.getElementById('edit_nama_produk').value = item.nama_produk || '';
    document.getElementById('edit_harga_qty').value = formatRupiahNumber(item.harga_qty);
    document.getElementById('edit_qty').value = item.qty || 1;
    document.getElementById('edit_total_harga').value = formatRupiahNumber(item.total_harga);
    document.getElementById('edit_hapus_gambar').value = '0';
    document.getElementById('edit_gambar_base64').value = '';
    document.getElementById('edit_gambar_input').value = '';

    // Set Jenis Chip (KTTS / KBTG)
    if ((item.jenis_chip || 'KTTS').toUpperCase() === 'KBTG') {
        const kbtgRadio = document.getElementById('edit_chip_kbtg');
        if (kbtgRadio) kbtgRadio.checked = true;
    } else {
        const kttsRadio = document.getElementById('edit_chip_ktts');
        if (kttsRadio) kttsRadio.checked = true;
    }

    // Reset and prepare Alasan Edit
    document.getElementById('edit_alasan').value = '';
    const riwayatSec = document.getElementById('riwayatAlasanSection');
    const riwayatText = document.getElementById('riwayatAlasanText');
    if (item.alasan_edit && item.alasan_edit.trim()) {
        riwayatText.textContent = item.alasan_edit;
        riwayatSec.classList.remove('d-none');
    } else {
        riwayatText.textContent = '';
        riwayatSec.classList.add('d-none');
    }

    const preview = document.getElementById('dropzonePreviewEdit');
    const placeholder = document.getElementById('dropzonePlaceholderEdit');
    const previewImg = document.getElementById('previewImgEdit');

    if (item.gambar_url) {
        previewImg.src = item.gambar_url;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
        document.getElementById('previewFilenameEdit').textContent = 'Gambar yang tersimpan';
    } else {
        previewImg.src = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
    }

    const modal = new bootstrap.Modal(document.getElementById('modalEditPendataan'));
    modal.show();
}

function openDetailModal(id) {
    fetch("{{ url('pendataan') }}/" + id, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => {
        if (res.status === 401) {
            window.location.replace("{{ route('pendataan.unlock') }}");
            return null;
        }
        return res.json();
    })
    .then(payload => {
        if (!payload) return;
        if (payload.expired && payload.redirect) {
            window.location.replace(payload.redirect);
            return;
        }
        if (payload.success) {
            const data = payload.data;
            document.getElementById('detail_nama_produk').textContent = data.nama_produk;
            document.getElementById('detail_total_harga').textContent = data.formatted_total_harga;
            document.getElementById('detail_harga_qty').textContent = data.formatted_harga_qty;
            document.getElementById('detail_qty').textContent = data.qty;
            document.getElementById('detail_nama').textContent = data.nama;
            document.getElementById('detail_tanggal').textContent = data.created_at;

            const statusBadgeElem = document.getElementById('detail_status_badge');
            if (statusBadgeElem) {
                statusBadgeElem.innerHTML = data.status_badge_html || '';
            }

            // Jenis Chip badge in Detail Modal
            const chipElem = document.getElementById('detail_chip');
            if (chipElem) {
                const chipVal = (data.jenis_chip || 'KTTS').toUpperCase();
                chipElem.textContent = chipVal;
                if (chipVal === 'KBTG') {
                    chipElem.className = 'badge text-white px-2 py-1 fw-bold';
                    chipElem.style.backgroundColor = '#6f42c1';
                } else {
                    chipElem.className = 'badge bg-primary text-white px-2 py-1 fw-bold';
                    chipElem.style.backgroundColor = '';
                }
            }

            const deskripsiSec = document.getElementById('detail_deskripsi_section');
            if (data.deskripsi && data.deskripsi.trim()) {
                document.getElementById('detail_deskripsi').textContent = data.deskripsi;
                deskripsiSec.classList.remove('d-none');
            } else {
                deskripsiSec.classList.add('d-none');
            }

            const gambarSec = document.getElementById('detail_gambar_section');
            if (data.gambar_url) {
                document.getElementById('detail_gambar').src = data.gambar_url;
                document.getElementById('detail_gambar_link').href = data.gambar_url;
                gambarSec.classList.remove('d-none');
            } else {
                gambarSec.classList.add('d-none');
            }

            // Alasan Edit history in Detail modal
            const alasanSec = document.getElementById('detail_alasan_section');
            if (data.alasan_edit && data.alasan_edit.trim()) {
                document.getElementById('detail_alasan').textContent = data.alasan_edit;
                alasanSec.classList.remove('d-none');
            } else {
                alasanSec.classList.add('d-none');
            }

            const modal = new bootstrap.Modal(document.getElementById('modalDetailPendataan'));
            modal.show();
        }
    })
    .catch(err => console.error(err));
}

function previewLightboxImage(url, caption) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('lightboxCaption').textContent = caption || '';
    const modal = new bootstrap.Modal(document.getElementById('modalLightboxGambar'));
    modal.show();
}



// =========================================================================
// DOM INITIALIZATION
// =========================================================================
document.addEventListener('DOMContentLoaded', function() {
    setupDropzone('dropzoneTambah', 'tambah_gambar_input', false);
    setupDropzone('dropzoneEdit', 'edit_gambar_input', true);

    // Refresh idempotency token every time the Tambah modal opens
    const modalTambahEl = document.getElementById('modalTambahPendataan');
    if (modalTambahEl) {
        modalTambahEl.addEventListener('show.bs.modal', function() {
            const tokenField = document.getElementById('tambah_idempotency_token');
            if (tokenField) {
                tokenField.value = 'tok_' + Date.now() + '_' + Math.random().toString(36).slice(2, 10);
            }
            // Re-enable submit button in case it was disabled from a previous attempt
            const btn = document.querySelector('#formTambahPendataan button[type="submit"]');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i> Simpan Pendataan';
            }
        });
    }

    // Format currency inputs on blur / input
    document.querySelectorAll('.js-currency-input').forEach(input => {
        input.addEventListener('blur', function() {
            const val = cleanPriceText(this.value);
            if (val > 0) {
                this.value = formatRupiahNumber(val);
            }
        });
    });

    // Auto-parse on typing or pasting in Tambah textarea
    const deskripsiTambah = document.getElementById('tambah_deskripsi');
    if (deskripsiTambah) {
        const triggerParseTambah = function() {
            const text = deskripsiTambah.value;
            if (!text || !text.trim()) return;

            const parsed = parseTransactionText(text);
            if (parsed.nama_produk) {
                document.getElementById('tambah_nama_produk').value = parsed.nama_produk;
            }
            if (parsed.harga_qty > 0) {
                document.getElementById('tambah_harga_qty').value = formatRupiahNumber(parsed.harga_qty);
            }
            if (parsed.qty > 0) {
                document.getElementById('tambah_qty').value = parsed.qty;
            }
            if (parsed.total_harga > 0) {
                document.getElementById('tambah_total_harga').value = formatRupiahNumber(parsed.total_harga);
            }

            const badge = document.getElementById('parseBadgeTambah');
            if (badge) {
                badge.classList.remove('d-none');
                document.getElementById('parseMessageTambah').textContent = 
                    '✓ Dipilah: ' + parsed.nama_produk + ' (Qty: ' + parsed.qty + ' | Total: Rp ' + formatRupiahNumber(parsed.total_harga) + ')';
            }
        };

        deskripsiTambah.addEventListener('paste', function() {
            setTimeout(triggerParseTambah, 50);
        });

        deskripsiTambah.addEventListener('input', function() {
            triggerParseTambah();
        });

        document.getElementById('btnPilahDeskripsiTambah').addEventListener('click', triggerParseTambah);

        const formTambah = document.getElementById('formTambahPendataan');
        if (formTambah) {
            let _tambahSubmitting = false;
            formTambah.addEventListener('submit', function(e) {
                // Auto-parse if product name is empty
                if (!document.getElementById('tambah_nama_produk').value.trim()) {
                    triggerParseTambah();
                }
                // Prevent double-submit
                if (_tambahSubmitting) {
                    e.preventDefault();
                    return false;
                }
                _tambahSubmitting = true;
                const btn = formTambah.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
                }
            });
        }
    }

    // Auto-parse on Edit textarea
    const deskripsiEdit = document.getElementById('edit_deskripsi');
    if (deskripsiEdit) {
        const triggerParseEdit = function() {
            const text = deskripsiEdit.value;
            if (!text || !text.trim()) return;

            const parsed = parseTransactionText(text);
            if (parsed.nama_produk) {
                document.getElementById('edit_nama_produk').value = parsed.nama_produk;
            }
            if (parsed.harga_qty > 0) {
                document.getElementById('edit_harga_qty').value = formatRupiahNumber(parsed.harga_qty);
            }
            if (parsed.qty > 0) {
                document.getElementById('edit_qty').value = parsed.qty;
            }
            if (parsed.total_harga > 0) {
                document.getElementById('edit_total_harga').value = formatRupiahNumber(parsed.total_harga);
            }

            const badge = document.getElementById('parseBadgeEdit');
            if (badge) {
                badge.classList.remove('d-none');
            }
        };

        deskripsiEdit.addEventListener('paste', function() {
            setTimeout(triggerParseEdit, 50);
        });

        deskripsiEdit.addEventListener('input', function() {
            triggerParseEdit();
        });

        document.getElementById('btnPilahDeskripsiEdit').addEventListener('click', triggerParseEdit);

        const formEdit = document.getElementById('formEditPendataan');
        if (formEdit) {
            let _editSubmitting = false;
            formEdit.addEventListener('submit', function(e) {
                // Auto-parse if product name is empty
                if (!document.getElementById('edit_nama_produk').value.trim()) {
                    triggerParseEdit();
                }
                // Prevent double-submit
                if (_editSubmitting) {
                    e.preventDefault();
                    return false;
                }
                _editSubmitting = true;
                const btn = formEdit.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...';
                }
            });
        }
    }

    // =========================================================================
    // STRICT 8-HOUR SHIFT AUTO-LOGOUT TIMER (Real-time sub-second precision)
    // =========================================================================
    (function initShiftTimer() {
        const shiftExpiresAtMs = {{ (float) ($shiftExpiresAt ?? session('pendataan_staff_expires_at', 0)) * 1000 }};
        if (!shiftExpiresAtMs || shiftExpiresAtMs <= 0) return;

        const timerBadge = document.getElementById('shiftTimerBadge');
        const countdownText = document.getElementById('shiftCountdownText');
        const lockUrl = "{{ route('pendataan.lock', ['expired' => 1]) }}";
        let hasLoggedOut = false;

        function checkShiftExpiry() {
            if (hasLoggedOut) return;

            const now = Date.now();
            const remainingMs = shiftExpiresAtMs - now;

            if (remainingMs <= 0) {
                hasLoggedOut = true;
                if (countdownText) countdownText.textContent = "00:00:00 (Habis)";
                if (timerBadge) {
                    timerBadge.className = "badge bg-danger text-white border border-danger px-3 py-2 fs-6 shadow-sm d-inline-flex align-items-center";
                }

                // Instant auto-logout even if 0.1s past 8 hours!
                window.location.replace(lockUrl);
                return;
            }

            const totalSeconds = Math.floor(remainingMs / 1000);
            const hours = Math.floor(totalSeconds / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;

            const formatted = 
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');

            if (countdownText && countdownText.textContent !== formatted) {
                countdownText.textContent = formatted;
            }

            if (timerBadge) {
                if (totalSeconds < 900) { // < 15 minutes left
                    timerBadge.className = "badge bg-danger text-white border border-danger px-3 py-2 fs-6 shadow-sm d-inline-flex align-items-center";
                } else if (totalSeconds < 3600) { // < 1 hour left
                    timerBadge.className = "badge bg-warning text-dark border border-warning px-3 py-2 fs-6 shadow-sm d-inline-flex align-items-center";
                }
            }
        }

        // Run immediately
        checkShiftExpiry();

        // Check every 250ms for sub-second real-time precision
        setInterval(checkShiftExpiry, 250);
    })();

    // =========================================================================
    // QUICK DATE PRESET SELECTOR (Hari Ini, Kemarin, 7 Hari, Semua)
    // =========================================================================
    window.applyDatePreset = function(preset) {
        const today = '{{ now()->toDateString() }}';
        const yesterday = '{{ now()->subDay()->toDateString() }}';
        const sevenDaysAgo = '{{ now()->subDays(6)->toDateString() }}';
        const form = document.getElementById('formFilterPendataan');
        const startInput = document.getElementById('filter_start_date');
        const endInput = document.getElementById('filter_end_date');

        if (!form || !startInput || !endInput) return;

        if (preset === 'today') {
            startInput.value = today;
            endInput.value = today;
        } else if (preset === 'yesterday') {
            startInput.value = yesterday;
            endInput.value = yesterday;
        } else if (preset === '7days') {
            startInput.value = sevenDaysAgo;
            endInput.value = today;
        } else if (preset === 'all') {
            startInput.value = '';
            endInput.value = '';
        }
        form.submit();
    };
});

function submitManualSms(e) {
    e.preventDefault();
    const text = document.getElementById('inputManualSmsText').value.trim();
    if (!text) return;

    const btn = document.getElementById('btnSubmitManualSms');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memproses...';

    fetch("{{ route('pendataan.process-manual-sms') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ sms_text: text })
    })
    .then(r => r.json())
    .then(res => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-send me-1"></i> Proses & Cocokkan SMS';
        if (res.success) {
            const modalEl = document.getElementById('modalManualSms');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
            document.getElementById('inputManualSmsText').value = '';

            Swal.fire({
                icon: res.matched ? 'success' : 'info',
                title: res.matched ? 'Berhasil Cocok!' : 'SMS Tercatat',
                text: res.message,
                confirmButtonText: 'OK'
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Perhatian', res.message || 'Gagal memproses SMS.', 'warning');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-send me-1"></i> Proses & Cocokkan SMS';
        Swal.fire('Error', 'Terjadi kesalahan: ' + err.message, 'error');
    });
}
</script>
@endpush
