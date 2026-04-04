@extends('layouts/app')

@section('content')

    <!-- [ breadcrumb ] start -->
    <div class="page-header" style="margin: 0; padding: 0;">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">{{ $title }}</h5>
                    </div>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item" aria-current="page">Dashboard</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- [ breadcrumb ] end -->

    <div class="row mt-0">
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Pengguna Aktif</p>
                    <h4 class="mb-0">{{ $totalUser }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Admin Action Hari Ini</p>
                    <h4 class="mb-0">{{ $adminActionsToday }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Data Matrix</p>
                    <h4 class="mb-0">{{ $totalDataMatrix }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Transaksi API</p>
                    <h4 class="mb-0">{{ $totalTransaksiApi }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Minusan (Transaksi)</p>
                    <h4 class="mb-0">{{ $totalTransaksiMinusan }}</h4>
                    <small class="text-muted">Rp {{ number_format($totalMinusan, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Deposit/Hutang</p>
                    <h4 class="mb-0">{{ $totalDeposit }}</h4>
                    <small class="text-muted">Hutang: {{ $totalHutang }} | Belum lunas: Rp {{ number_format($nominalHutangBelumLunas, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Reimburse</p>
                    <h4 class="mb-0">{{ $totalReimburse }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <p class="text-muted mb-1">Data Request / Loan / Persediaan</p>
                    <h4 class="mb-0">{{ $totalDataRequest + $totalLoanRequest + $totalPersediaan }}</h4>
                    <small class="text-muted">Data: {{ $totalDataRequest }} | Loan: {{ $totalLoanRequest }} | Persediaan: {{ $totalPersediaan }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-xl-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ti ti-chart-line me-2"></i>
                        <span>Grafik Total Minusan Per Bulan</span>
                    </h5>
                    <div class="d-flex gap-2">
                        <span class="badge bg-light-primary">Interaktif</span>
                        <span class="badge bg-light-info">
                            <i class="ti ti-info-circle me-1"></i>Hover untuk detail
                        </span>
                    </div>
                </div>
                <div class="card-body" style="height: 360px;">
                    <canvas id="chartMinusan"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Tren Semua Fitur (6 Bulan)</h5>
                </div>
                <div class="card-body" style="height: 360px;">
                    <canvas id="chartFeatureTrends"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Analisa Status per Fitur</h5>
                </div>
                <div class="card-body" style="height: 360px;">
                    <canvas id="chartStatusOverview"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Komparasi Nominal Utama</h5>
                </div>
                <div class="card-body" style="height: 360px;">
                    <canvas id="chartNominalFeature"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('sbadmin2/js/chart.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/chart/minusan')
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(item => item.bulan);
                    const totals = data.map(item => Number(item.total_bulanan || 0));

                    const ctxMinusan = document.getElementById('chartMinusan').getContext('2d');
                    const gradient = ctxMinusan.createLinearGradient(0, 0, 0, 360);
                    gradient.addColorStop(0, 'rgba(70, 128, 255, 0.30)');
                    gradient.addColorStop(0.5, 'rgba(70, 128, 255, 0.15)');
                    gradient.addColorStop(1, 'rgba(70, 128, 255, 0.05)');

                    new Chart(ctxMinusan, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Total Minusan',
                                data: totals,
                                backgroundColor: gradient,
                                borderColor: '#4680ff',
                                borderWidth: 3,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#4680ff',
                                pointBorderWidth: 3,
                                pointRadius: 6,
                                pointHoverRadius: 10,
                                pointHoverBackgroundColor: '#4680ff',
                                pointHoverBorderColor: '#fff',
                                pointHoverBorderWidth: 4,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            plugins: {
                                legend: { display: true, position: 'top', align: 'end' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            const value = context.parsed.y || 0;
                                            return 'Total: Rp ' + value.toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(() => {});

            const monthlyLabels = @json($chartMonthlyLabels);
            const monthlySeries = @json($chartMonthlySeries);
            const statusOverview = @json($chartStatusOverview);
            const nominalByFeature = @json($chartNominalByFeature);

            const featureColors = ['#4680ff', '#2ca87f', '#f39c12', '#e74c3c', '#8e44ad', '#16a085'];
            const trendDatasets = Object.keys(monthlySeries).map((key, index) => ({
                label: key,
                data: monthlySeries[key],
                borderColor: featureColors[index % featureColors.length],
                backgroundColor: featureColors[index % featureColors.length],
                pointRadius: 3,
                pointHoverRadius: 5,
                borderWidth: 2,
                tension: 0.35,
                fill: false
            }));

            new Chart(document.getElementById('chartFeatureTrends').getContext('2d'), {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: trendDatasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false }
                }
            });

            const statusLabels = Object.keys(statusOverview);
            const pending = statusLabels.map(label => statusOverview[label].pending || 0);
            const approved = statusLabels.map(label => statusOverview[label].approved || 0);
            const rejected = statusLabels.map(label => statusOverview[label].rejected || 0);
            const selesai = statusLabels.map(label => statusOverview[label].selesai || 0);
            const lunas = statusLabels.map(label => statusOverview[label].lunas || 0);

            new Chart(document.getElementById('chartStatusOverview').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: statusLabels,
                    datasets: [{ label: 'Pending', data: pending, backgroundColor: '#f39c12' },
                        { label: 'Approved', data: approved, backgroundColor: '#2ca87f' },
                        { label: 'Rejected', data: rejected, backgroundColor: '#e74c3c' },
                        { label: 'Selesai', data: selesai, backgroundColor: '#4680ff' },
                        { label: 'Lunas', data: lunas, backgroundColor: '#8e44ad' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true }
                    }
                }
            });

            const nominalLabels = Object.keys(nominalByFeature);
            const nominalValues = nominalLabels.map(label => nominalByFeature[label]);

            new Chart(document.getElementById('chartNominalFeature').getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: nominalLabels,
                    datasets: [{
                        data: nominalValues,
                        backgroundColor: ['#4680ff', '#2ca87f', '#f39c12', '#e74c3c', '#8e44ad'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed || 0;
                                    return context.label + ': Rp ' + value.toLocaleString('id-ID');
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
@endsection
