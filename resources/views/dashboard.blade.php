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
