@extends('layouts/app')

@section('content')

<!-- Page Header -->
<div class="page-header mb-3">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h4 class="mb-0 fw-bold text-dark"><i class="ti ti-chart-dots text-primary me-2"></i>Dashboard Analisa Rincian User & Keaktifan Harian</h4>
                    <p class="text-muted mb-0">Analisa kegemaran platform sosmed, kelengkapan 3 foto bukti, dan konsistensi keaktifan harian karyawan</p>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <a href="{{ route('admin.sosmed.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-2 me-1">
                    <i class="ti ti-dashboard me-1"></i> Dashboard Utama
                </a>
                <a href="{{ route('admin.sosmed.user-fees') }}" class="btn btn-outline-primary btn-sm rounded-2">
                    <i class="ti ti-trophy me-1"></i> Rekap Fee
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Search Filter -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-3">
        <form action="{{ route('admin.sosmed.user-analysis') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama karyawan, username, divisi, atau platform favorit..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-search me-1"></i> Cari User</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.sosmed.user-analysis') }}" class="btn btn-light-secondary"><i class="ti ti-refresh"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Navigation Tabs for 2 Leaderboards -->
<ul class="nav nav-pills mb-3 gap-2" id="leaderboardTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold px-3 py-2 shadow-sm" id="tab-aktivitas-tab" data-bs-toggle="pill" data-bs-target="#tab-aktivitas" type="button" role="tab" aria-controls="tab-aktivitas" aria-selected="true">
            <i class="ti ti-activity me-1"></i> Leaderboard Aktivitas & Kelengkapan User
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold px-3 py-2 shadow-sm" id="tab-poin-tab" data-bs-toggle="pill" data-bs-target="#tab-poin" type="button" role="tab" aria-controls="tab-poin" aria-selected="false">
            <i class="ti ti-star me-1"></i> Leaderboard Total Poin Task
        </button>
    </li>
</ul>

<div class="tab-content" id="leaderboardTabsContent">

    <!-- Tab 1: Leaderboard Aktivitas, Kegemaran & Kelengkapan Foto -->
    <div class="tab-pane fade show active" id="tab-aktivitas" role="tabpanel" aria-labelledby="tab-aktivitas-tab">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="ti ti-report-analytics text-primary me-2"></i>Analisa Kegemaran Sosmed, Foto 3 Gambar, & Konsistensi Harian
                    </h5>
                    <p class="text-muted mb-0 small">Memantau apakah user rajin tiap hari / ada hari bolos, serta kelengkapan 3 foto bukti yang diunggah</p>
                </div>
                <span class="badge bg-light-primary text-primary border"><i class="ti ti-users me-1"></i>Total {{ count($analysisData) }} Karyawan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">Rank</th>
                                <th>Nama Karyawan</th>
                                <th>Divisi</th>
                                <th>Platform Digemari</th>
                                <th class="text-center">Kelengkapan 3 Foto</th>
                                <th class="text-center">Keaktifan Harian</th>
                                <th class="text-center">Hari Aktif vs Bolos</th>
                                <th class="text-center">Hari Ini</th>
                                <th class="text-center">Aksi Rincian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analysisData as $user)
                                <tr>
                                    <!-- Rank Column -->
                                    <td class="text-center">
                                        @if($user['rank'] === 1)
                                            <span class="badge bg-warning text-dark font-monospace fs-7">🥇 #1</span>
                                        @elseif($user['rank'] === 2)
                                            <span class="badge bg-secondary text-white font-monospace fs-7">🥈 #2</span>
                                        @elseif($user['rank'] === 3)
                                            <span class="badge bg-dark text-white font-monospace fs-7" style="background-color: #cd7f32 !important;">🥉 #3</span>
                                        @else
                                            <span class="badge bg-light text-muted font-monospace fs-7">#{{ $user['rank'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Nama Column -->
                                    <td>
                                        <strong class="text-dark d-block fs-6">{{ $user['nama'] }}</strong>
                                        @if($user['username_sosmed'] !== '-')
                                            <span class="badge bg-light-info text-info font-monospace fs-7"><i class="ti ti-at"></i>{{ $user['username_sosmed'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Divisi Column -->
                                    <td><span class="badge bg-light-secondary text-dark">{{ $user['divisi'] }}</span></td>

                                    <!-- Platform Digemari Column -->
                                    <td>
                                        @if($user['favorite_platform'] === 'Instagram')
                                            <span class="badge bg-danger"><i class="ti ti-brand-instagram me-1"></i>Instagram (Favorit)</span>
                                        @elseif($user['favorite_platform'] === 'TikTok')
                                            <span class="badge bg-dark"><i class="ti ti-brand-tiktok me-1"></i>TikTok (Favorit)</span>
                                        @elseif($user['favorite_platform'] === 'Facebook')
                                            <span class="badge bg-primary"><i class="ti ti-brand-facebook me-1"></i>Facebook (Favorit)</span>
                                        @elseif($user['favorite_platform'] === 'YouTube')
                                            <span class="badge bg-danger"><i class="ti ti-brand-youtube me-1"></i>YouTube (Favorit)</span>
                                        @else
                                            <span class="text-muted fs-7">Belum Ada</span>
                                        @endif
                                    </td>

                                    <!-- Kelengkapan 3 Foto Column -->
                                    <td class="text-center">
                                        @if($user['total_submissions'] > 0)
                                            @if($user['photo_completeness_rate'] >= 100)
                                                <span class="badge bg-success" title="Selalu upload 3 foto bukti (Like, Komen, Share)">
                                                    <i class="ti ti-circle-check me-1"></i>100% Lengkap (3 Foto)
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark" title="Rata-rata foto ter-upload {{ $user['avg_photos'] }} foto">
                                                    <i class="ti ti-alert-triangle me-1"></i>{{ $user['photo_completeness_rate'] }}% Lengkap (Rata-rata {{ $user['avg_photos'] }} foto)
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted fs-7">-</span>
                                        @endif
                                    </td>

                                    <!-- Keaktifan Harian Status Column -->
                                    <td class="text-center">
                                        <span class="badge {{ $user['activity_badge'] }} px-2 py-1 fs-7">
                                            {{ $user['activity_status'] }}
                                        </span>
                                    </td>

                                    <!-- Hari Aktif vs Bolos Column -->
                                    <td class="text-center">
                                        @if($user['total_submissions'] > 0)
                                            <small class="d-block text-success fw-bold"><i class="ti ti-calendar-check me-1"></i>{{ $user['active_days_count'] }} Hari Aktif</small>
                                            @if($user['inactive_days_count'] > 0)
                                                <small class="d-block text-danger"><i class="ti ti-calendar-x me-1"></i>{{ $user['inactive_days_count'] }} Hari Kosong/Bolos</small>
                                            @endif
                                        @else
                                            <span class="text-muted fs-7">0 Hari</span>
                                        @endif
                                    </td>

                                    <!-- Hari Ini Status Column -->
                                    <td class="text-center">
                                        @if($user['submitted_today'])
                                            <span class="badge bg-success"><i class="ti ti-check me-1"></i>Sudah Kirim</span>
                                        @else
                                            <span class="badge bg-light text-muted border"><i class="ti ti-clock me-1"></i>Belum Kirim</span>
                                        @endif
                                    </td>

                                    <!-- Aksi Detail Column -->
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalAnalisaUser{{ Str::slug($user['nama']) }}">
                                            <i class="ti ti-chart-bar me-1"></i> Rincian
                                        </button>

                                        <!-- Modal Rincian Analisa Individu User -->
                                        <div class="modal fade text-start" id="modalAnalisaUser{{ Str::slug($user['nama']) }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title text-white fw-bold">
                                                            <i class="ti ti-user me-1"></i> Rincian Analisa Perilaku: {{ $user['nama'] }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <!-- Card Profil Analisa -->
                                                        <div class="row g-3 mb-4">
                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light rounded border h-100">
                                                                    <small class="text-muted d-block">Nama Karyawan & Divisi:</small>
                                                                    <strong class="text-dark fs-6 d-block">{{ $user['nama'] }}</strong>
                                                                    <span class="badge bg-light-secondary text-dark mb-2">{{ $user['divisi'] }}</span>
                                                                    @if($user['username_sosmed'] !== '-')
                                                                        <div class="text-info fs-7 fw-semibold"><i class="ti ti-at me-1"></i>{{ $user['username_sosmed'] }}</div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light rounded border h-100">
                                                                    <small class="text-muted d-block">Status Keaktifan & Konsistensi:</small>
                                                                    <span class="badge {{ $user['activity_badge'] }} fs-6 mb-2">{{ $user['activity_status'] }}</span>
                                                                    <div class="small text-muted">
                                                                        Total Hari Aktif: <strong class="text-dark">{{ $user['active_days_count'] }} Hari</strong><br>
                                                                        Total Hari Kosong: <strong class="text-danger">{{ $user['inactive_days_count'] }} Hari</strong>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Ringkasan Kelengkapan Foto & Platform -->
                                                        <div class="row g-3 mb-4">
                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light-primary rounded border border-primary h-100">
                                                                    <h6 class="fw-bold text-primary mb-1"><i class="ti ti-camera me-1"></i>Kelengkapan 3 Foto Bukti</h6>
                                                                    <div class="fs-4 fw-bold text-dark">{{ $user['photo_completeness_rate'] }}% <small class="fs-7 text-muted">Lengkap</small></div>
                                                                    <small class="text-muted d-block mt-1">
                                                                        Upload 3 Foto Lengkap: <strong>{{ $user['three_photo_count'] }}x dari {{ $user['total_submissions'] }} submit</strong>
                                                                    </small>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light-info rounded border border-info h-100">
                                                                    <h6 class="fw-bold text-info mb-1"><i class="ti ti-flame me-1"></i>Platform Paling Digemari</h6>
                                                                    <div class="fs-5 fw-bold text-dark mb-1">{{ $user['favorite_platform'] }}</div>
                                                                    <div class="d-flex flex-wrap gap-1">
                                                                        <span class="badge bg-danger">IG: {{ $user['ig_count'] }}</span>
                                                                        <span class="badge bg-dark">TikTok: {{ $user['tiktok_count'] }}</span>
                                                                        <span class="badge bg-primary">FB: {{ $user['fb_count'] }}</span>
                                                                        <span class="badge bg-danger">YT: {{ $user['yt_count'] }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <h6 class="fw-bold mb-2"><i class="ti ti-history me-1"></i>Riwayat Pengiriman Task:</h6>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered align-middle">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>No</th>
                                                                        <th>Tugas</th>
                                                                        <th>Platform</th>
                                                                        <th>Tanggal & Jam</th>
                                                                        <th>Foto Bukti (Like, Komen, Share)</th>
                                                                        <th>Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($user['submissions'] as $sIdx => $subItem)
                                                                        <tr>
                                                                            <td>{{ $sIdx + 1 }}</td>
                                                                            <td>
                                                                                <strong class="d-block small">{{ $subItem->pilihan_tugas }}</strong>
                                                                            </td>
                                                                            <td><span class="badge bg-secondary fs-7">{{ $subItem->sosmed_platform }}</span></td>
                                                                            <td><small>{{ $subItem->created_at->format('d/m/Y H:i') }}</small></td>
                                                                            <td>
                                                                                @if(!empty($subItem->photos))
                                                                                    <div class="d-flex gap-1">
                                                                                        @php $labels = ['Like', 'Komen', 'Share']; @endphp
                                                                                        @foreach($subItem->photos as $pIdx => $ph)
                                                                                            <a href="{{ asset($ph) }}" target="_blank" title="{{ $labels[$pIdx] ?? 'Foto '.($pIdx+1) }}">
                                                                                                <img src="{{ asset($ph) }}" style="width: 32px; height: 32px; object-fit: cover;" class="rounded border">
                                                                                            </a>
                                                                                        @endforeach
                                                                                    </div>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if($subItem->status === 'acc')
                                                                                    <span class="badge bg-success">ACC</span>
                                                                                @elseif($subItem->status === 'ditolak')
                                                                                    <span class="badge bg-danger">Ditolak</span>
                                                                                @else
                                                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="6" class="text-center py-3 text-muted">Belum pernah mengirim task sosmed.</td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="ti ti-user-x fs-1 d-block mb-2 text-muted"></i>
                                        Belum ada data analisa user.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Leaderboard Total Poin Task -->
    <div class="tab-pane fade" id="tab-poin" role="tabpanel" aria-labelledby="tab-poin-tab">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-star text-warning me-2"></i>Klasemen Peringkat Total Poin Karyawan</h5>
                <span class="badge bg-warning text-dark">1 Task = 1 Poin</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;" class="text-center">Rank</th>
                                <th>Nama Karyawan</th>
                                <th>Divisi</th>
                                <th class="text-center">Total Task Dikerjakan</th>
                                <th class="text-center">Total Poin</th>
                                <th class="text-end">Total Fee ACC</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($analysisData as $user)
                                <tr class="{{ $user['rank'] <= 5 ? 'table-warning bg-opacity-10' : '' }}">
                                    <td class="text-center fw-bold">#{{ $user['rank'] }}</td>
                                    <td><strong class="text-dark">{{ $user['nama'] }}</strong></td>
                                    <td><span class="badge bg-light-secondary text-dark">{{ $user['divisi'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-info">{{ $user['total_submissions'] }} Task</span></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6 px-3 py-2"><i class="ti ti-star me-1"></i>{{ $user['total_points'] }} Poin</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">Rp {{ number_format($user['total_fee'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
