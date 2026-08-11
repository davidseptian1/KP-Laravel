@extends('layouts/app')

@section('content')

<!-- Page Header -->
<div class="page-header mb-3">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h4 class="mb-0 fw-bold text-dark"><i class="ti ti-brand-instagram text-primary me-2"></i>Dashboard Admin & Leaderboard Sosmed</h4>
                    <p class="text-muted mb-0">Deteksi pengerjaan tugas sosmed, grafik peringkat horizontal Top 1-5, dan urutan poin seluruh 90 karyawan</p>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <a href="{{ route('admin.sosmed.submissions') }}" class="btn btn-primary btn-sm rounded-2 me-1">
                    <i class="ti ti-list-check me-1"></i> Moderasi Posting
                </a>
                <a href="{{ route('admin.sosmed.user-fees') }}" class="btn btn-outline-primary btn-sm rounded-2 me-1">
                    <i class="ti ti-trophy me-1"></i> Rekap Leaderboard
                </a>
                <a href="{{ route('sosmed.form.show') }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-2">
                    <i class="ti ti-external-link me-1"></i> Form Public
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
                        <p class="text-muted mb-1 font-weight-medium">Total Task Dikerjakan</p>
                        <h3 class="mb-0 fw-bold text-primary">{{ number_format($totalSubmissions) }} <small class="fs-7 text-muted">Poin</small></h3>
                    </div>
                    <div class="avatar-lg bg-light-primary text-primary rounded-circle p-2 text-center" style="width: 48px; height: 48px; display: grid; place-items: center;">
                        <i class="ti ti-checkbox fs-2"></i>
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
                        <p class="text-muted mb-1 font-weight-medium">Task Disetujui (ACC)</p>
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

<!-- Section Charts -->
<div class="row mt-2">
    <!-- Chart 1: Horizontal Bar Chart Peringkat Top 1 - 5 User (Gaya Bar Race Ranking) -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="ti ti-chart-bar text-primary me-2"></i>Grafik Horizontal Peringkat Karyawan (Top Leaders)
                    </h5>
                    <small class="text-muted">Top 5 Karyawan dengan Poin Task Sosmed Terbanyak (1 Task = 1 Poin)</small>
                </div>
                <span class="badge bg-warning text-dark"><i class="ti ti-crown me-1"></i>Top 5 Leaders</span>
            </div>
            <div class="card-body">
                <div style="width: 100%; height: 320px;">
                    <canvas id="horizontalBarChartTop5"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 2: Traffic Sosmed Platform -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-chart-pie text-primary me-2"></i>Distribusi Platform Sosmed</h5>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div style="width: 100%; max-width: 280px; height: 240px;">
                    <canvas id="platformChart"></canvas>
                </div>
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                    <span class="badge bg-danger p-2"><i class="ti ti-brand-instagram me-1"></i> IG: {{ $platformCounts['Instagram'] ?? 0 }}</span>
                    <span class="badge bg-dark p-2"><i class="ti ti-brand-tiktok me-1"></i> TikTok: {{ $platformCounts['TikTok'] ?? 0 }}</span>
                    <span class="badge bg-primary p-2"><i class="ti ti-brand-facebook me-1"></i> FB: {{ $platformCounts['Facebook'] ?? 0 }}</span>
                    <span class="badge bg-danger p-2"><i class="ti ti-brand-youtube me-1"></i> YT: {{ $platformCounts['YouTube'] ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Section Leaderboard Tabel Peringkat 1 s/d 90 (Urut Terbesar ke Terkecil) -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="ti ti-trophy text-warning me-2"></i>Tabel Peringkat Karyawan (Rank 1 - 90)
                    </h5>
                    <p class="text-muted mb-0 small">Diurutkan berdasarkan perolehan Poin Task (1 Task Upload = 1 Poin) dari Terbesar ke Terkecil</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge bg-light-warning text-dark border align-self-center"><i class="ti ti-medal me-1"></i>Top 5 Grafik Bar</span>
                    <span class="badge bg-light-secondary text-dark border align-self-center"><i class="ti ti-users me-1"></i>Total {{ count($leaderboardAll) }} Karyawan</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;" class="text-center">Rank</th>
                                <th>Nama Karyawan</th>
                                <th>Username Sosmed</th>
                                <th>Divisi</th>
                                <th>Tugas Sosmed Dikerjakan</th>
                                <th class="text-center">Total Poin (Task)</th>
                                <th class="text-center">Status Task</th>
                                <th class="text-end">Total Fee (ACC)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaderboardAll as $user)
                                <tr class="{{ $user['rank'] <= 5 ? 'table-warning bg-opacity-10' : '' }}">
                                    <!-- Rank Column -->
                                    <td class="text-center">
                                        @if($user['rank'] === 1)
                                            <span class="badge bg-warning text-dark fs-6 px-2 py-1 shadow-sm" title="Peringkat 1 Gold">🥇 #1</span>
                                        @elseif($user['rank'] === 2)
                                            <span class="badge bg-secondary text-white fs-6 px-2 py-1 shadow-sm" title="Peringkat 2 Silver">🥈 #2</span>
                                        @elseif($user['rank'] === 3)
                                            <span class="badge bg-dark text-white fs-6 px-2 py-1 shadow-sm" style="background-color: #cd7f32 !important;" title="Peringkat 3 Bronze">🥉 #3</span>
                                        @elseif($user['rank'] <= 5)
                                            <span class="badge bg-light-primary text-primary fw-bold fs-7" title="Top 5 Leader">⭐ #{{ $user['rank'] }}</span>
                                        @else
                                            <span class="badge bg-light text-muted fw-normal fs-7">#{{ $user['rank'] }}</span>
                                        @endif
                                    </td>

                                    <!-- Nama Column -->
                                    <td>
                                        <strong class="text-dark d-block fs-6">{{ $user['nama'] }}</strong>
                                    </td>

                                    <!-- Username Sosmed Column -->
                                    <td>
                                        @if($user['username_sosmed'] !== '-')
                                            <span class="badge bg-light-info text-info font-monospace">
                                                <i class="ti ti-at me-1"></i>{{ $user['username_sosmed'] }}
                                            </span>
                                        @else
                                            <span class="text-muted fs-7">-</span>
                                        @endif
                                    </td>

                                    <!-- Divisi Column -->
                                    <td>
                                        <span class="badge bg-light-secondary text-dark">{{ $user['divisi'] }}</span>
                                    </td>

                                    <!-- Tugas Sosmed Dikerjakan Column -->
                                    <td>
                                        @if(!empty($user['tasks_completed']))
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($user['tasks_completed'] as $tugasName)
                                                    <span class="badge bg-light-primary text-primary text-wrap text-start" style="max-width: 250px;">
                                                        <i class="ti ti-check me-1"></i>{{ $tugasName }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @elseif($user['total_submissions'] > 0)
                                            <span class="badge bg-light-success text-success"><i class="ti ti-cloud-upload me-1"></i>Task Uploaded</span>
                                        @else
                                            <span class="text-muted fst-italic fs-7">Belum mengerjakan task</span>
                                        @endif
                                    </td>

                                    <!-- Total Poin Column -->
                                    <td class="text-center">
                                        @if($user['total_points'] > 0)
                                            <span class="badge bg-primary fs-6 px-3 py-2">
                                                <i class="ti ti-star me-1"></i>{{ $user['total_points'] }} Poin
                                            </span>
                                        @else
                                            <span class="badge bg-light text-muted fs-7">0 Poin</span>
                                        @endif
                                    </td>

                                    <!-- Status Task Column -->
                                    <td class="text-center">
                                        @if($user['total_submissions'] > 0)
                                            <small class="d-block text-success fw-semibold">{{ $user['acc_count'] }} ACC</small>
                                            @if($user['pending_count'] > 0)
                                                <small class="d-block text-warning fw-semibold">{{ $user['pending_count'] }} Pending</small>
                                            @endif
                                            @if($user['rejected_count'] > 0)
                                                <small class="d-block text-danger fw-semibold">{{ $user['rejected_count'] }} Ditolak</small>
                                            @endif
                                        @else
                                            <span class="text-muted fs-7">-</span>
                                        @endif
                                    </td>

                                    <!-- Total Fee Column -->
                                    <td class="text-end">
                                        @if($user['total_fee'] > 0)
                                            <strong class="text-success fs-6">Rp {{ number_format($user['total_fee'], 0, ',', '.') }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data karyawan.</td>
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
        // Horizontal Bar Chart Peringkat Top 1 - 5 User (Gaya Bar Chart Race)
        const barCtx = document.getElementById('horizontalBarChartTop5').getContext('2d');

        const top5Labels = {!! json_encode($top5Labels) !!};
        const top5Points = {!! json_encode($top5Points) !!};
        const top5Divisions = {!! json_encode($top5Divisions) !!};
        const top5Usernames = {!! json_encode($top5Usernames) !!};

        // Palet warna bervariasi persis seperti contoh gambar (Cyan, Bright Blue, Purple, Orange, Magenta)
        const barColors = [
            '#36cfc9', // Rank 1: Vibrant Cyan
            '#1890ff', // Rank 2: Bright Blue
            '#722ed1', // Rank 3: Purple / Violet
            '#fa8c16', // Rank 4: Orange Amber
            '#eb2f96'  // Rank 5: Pink Magenta
        ];

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: top5Labels,
                datasets: [{
                    label: 'Total Poin Task',
                    data: top5Points,
                    backgroundColor: barColors.slice(0, top5Labels.length),
                    borderRadius: 10,
                    borderSkipped: false,
                    barThickness: 26
                }]
            },
            options: {
                indexAxis: 'y', // Mengubah chart menjadi Horizontal Bar Chart
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { weight: 'bold', size: 12 },
                            color: '#262626'
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ` Poin: ${context.raw} Poin Task`;
                            },
                            afterLabel: function(context) {
                                const index = context.dataIndex;
                                const div = top5Divisions[index] || '-';
                                const uname = top5Usernames[index] || '-';
                                return `Divisi: ${div}\nUsername: ${uname}`;
                            }
                        }
                    }
                }
            }
        });

        // Platform Traffic Doughnut Chart
        const platformCtx = document.getElementById('platformChart').getContext('2d');
        new Chart(platformCtx, {
            type: 'doughnut',
            data: {
                labels: ['Instagram', 'TikTok', 'Facebook', 'YouTube'],
                datasets: [{
                    data: [
                        {{ $platformCounts['Instagram'] ?? 0 }},
                        {{ $platformCounts['TikTok'] ?? 0 }},
                        {{ $platformCounts['Facebook'] ?? 0 }},
                        {{ $platformCounts['YouTube'] ?? 0 }}
                    ],
                    backgroundColor: ['#e1306c', '#000000', '#1877f2', '#ff0000'],
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
    });
</script>

@endsection
