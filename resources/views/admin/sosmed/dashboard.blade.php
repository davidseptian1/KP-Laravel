@extends('layouts/app')

@section('content')

<!-- Page Header -->
<div class="page-header mb-3">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h4 class="mb-0 fw-bold text-dark"><i class="ti ti-brand-instagram text-primary me-2"></i>Dashboard Admin Sosmed</h4>
                    <p class="text-muted mb-0">Ringkasan grafik statistik trafik posting sosmed & aktivitas user</p>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <a href="{{ route('admin.sosmed.submissions') }}" class="btn btn-primary btn-sm rounded-2">
                    <i class="ti ti-list-check me-1"></i> Riwayat & Moderasi
                </a>
                <a href="{{ route('sosmed.form.show') }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
                    <i class="ti ti-external-link me-1"></i> Form Publik
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards Row -->
<div class="row">
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-4 border-primary">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 font-weight-medium">Total Posting</p>
                        <h3 class="mb-0 fw-bold">{{ number_format($totalSubmissions) }}</h3>
                    </div>
                    <div class="avatar-lg bg-light-primary text-primary rounded-circle p-2 text-center" style="width: 48px; height: 48px; display: grid; place-items: center;">
                        <i class="ti ti-cloud-upload fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-4 border-warning">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 font-weight-medium">Pending Moderasi</p>
                        <h3 class="mb-0 fw-bold text-warning">{{ number_format($pendingCount) }}</h3>
                    </div>
                    <div class="avatar-lg bg-light-warning text-warning rounded-circle p-2 text-center" style="width: 48px; height: 48px; display: grid; place-items: center;">
                        <i class="ti ti-clock fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-4 border-success">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 font-weight-medium">Posting Disetujui (ACC)</p>
                        <h3 class="mb-0 fw-bold text-success">{{ number_format($accCount) }}</h3>
                    </div>
                    <div class="avatar-lg bg-light-success text-success rounded-circle p-2 text-center" style="width: 48px; height: 48px; display: grid; place-items: center;">
                        <i class="ti ti-circle-check fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm border-0 border-start border-4 border-info">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 font-weight-medium">Total Fee Terkumpul</p>
                        <h3 class="mb-0 fw-bold text-info">Rp {{ number_format($totalFeeAccrued, 0, ',', '.') }}</h3>
                    </div>
                    <div class="avatar-lg bg-light-info text-info rounded-circle p-2 text-center" style="width: 48px; height: 48px; display: grid; place-items: center;">
                        <i class="ti ti-coin fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mt-2">
    <!-- Chart 1: Traffic Sosmed Platform -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-chart-pie text-primary me-2"></i>Grafik Trafik Simpan Sosmed (IG, TikTok, FB)</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="width: 100%; max-width: 360px; height: 300px;">
                    <canvas id="platformChart"></canvas>
                </div>
                <div class="d-flex justify-content-center gap-4 mt-3">
                    <span class="badge bg-danger p-2"><i class="ti ti-brand-instagram me-1"></i> Instagram: {{ $platformCounts['Instagram'] ?? 0 }}</span>
                    <span class="badge bg-dark p-2"><i class="ti ti-brand-tiktok me-1"></i> TikTok: {{ $platformCounts['TikTok'] ?? 0 }}</span>
                    <span class="badge bg-primary p-2"><i class="ti ti-brand-facebook me-1"></i> Facebook: {{ $platformCounts['Facebook'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: User Paling Rajin Simpan -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-chart-bar text-success me-2"></i>Grafik User Paling Rajin Simpan</h5>
                <small class="text-muted">Top Submitters</small>
            </div>
            <div class="card-body">
                <div style="width: 100%; height: 300px;">
                    <canvas id="topUsersChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Submissions Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-history me-2"></i>Posting Sosmed Terbaru</h5>
                <a href="{{ route('admin.sosmed.submissions') }}" class="btn btn-sm btn-light-primary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Nama (Panggilan)</th>
                                <th>Divisi</th>
                                <th>Platform</th>
                                <th>Bukti Foto</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSubmissions as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong class="text-dark">{{ $item->nama }}</strong><br>
                                        <small class="text-muted"><i class="ti ti-user me-1"></i>{{ $item->nama_first_word }}</small>
                                    </td>
                                    <td><span class="badge bg-light-secondary text-dark">{{ $item->divisi }}</span></td>
                                    <td>
                                        @if($item->sosmed_platform === 'Instagram')
                                            <span class="badge bg-danger"><i class="ti ti-brand-instagram me-1"></i>Instagram</span>
                                        @elseif($item->sosmed_platform === 'TikTok')
                                            <span class="badge bg-dark"><i class="ti ti-brand-tiktok me-1"></i>TikTok</span>
                                        @else
                                            <span class="badge bg-primary"><i class="ti ti-brand-facebook me-1"></i>Facebook</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!empty($item->photos) && is_array($item->photos))
                                            <div class="d-flex gap-1">
                                                @foreach($item->photos as $pIndex => $photo)
                                                    <a href="{{ asset($photo) }}" target="_blank">
                                                        <img src="{{ asset($photo) }}" alt="Foto" class="rounded border" style="width: 36px; height: 36px; object-fit: cover;">
                                                    </a>
                                                    @if($pIndex >= 2)
                                                        @break
                                                    @endif
                                                @endforeach
                                                @if(count($item->photos) > 3)
                                                    <span class="badge bg-secondary align-self-center">+{{ count($item->photos) - 3 }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $item->created_at->format('d/m/Y H:i') }}</small></td>
                                    <td>
                                        @if($item->status === 'acc')
                                            <span class="badge bg-success">ACC</span>
                                        @elseif($item->status === 'ditolak')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.sosmed.submissions', ['search' => $item->nama]) }}" class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data posting sosmed.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ChartJS CDN Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Platform Traffic Doughnut Chart
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: ['Instagram', 'TikTok', 'Facebook'],
                datasets: [{
                    data: [
                        {{ $platformCounts['Instagram'] ?? 0 }},
                        {{ $platformCounts['TikTok'] ?? 0 }},
                        {{ $platformCounts['Facebook'] ?? 0 }}
                    ],
                    backgroundColor: ['#e1306c', '#000000', '#1877f2'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Top Active Users Bar Chart
        const topUsersCtx = document.getElementById('topUsersChart').getContext('2d');
        new Chart(topUsersCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topUserLabels) !!},
                datasets: [{
                    label: 'Jumlah Post Bukti Sosmed',
                    data: {!! json_encode($topUserCounts) !!},
                    backgroundColor: '#1890ff',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>

@endsection
