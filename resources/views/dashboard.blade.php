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

    <!-- [ Main Content ] start -->
    <div class="row mt-0">
        <!-- Statistics Cards -->
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 f-w-500">Total Transaksi</p>
                            <h4 class="mb-0 f-w-600">{{ $totalTransaksi }}</h4>
                        </div>
                        <div class="avtar avtar-l bg-light-primary">
                            <i class="ti ti-calendar-event f-24"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <span class="badge bg-light-primary border border-primary me-2">
                            <i class="ti ti-trending-up"></i>
                        </span>
                        <p class="mb-0 text-muted text-sm">Transaksi tercatat</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 f-w-500">Total Minusan</p>
                            <h4 class="mb-0 f-w-600">Rp {{ number_format($totalMinusan, 0, ',', '.') }}</h4>
                        </div>
                        <div class="avtar avtar-l bg-light-warning">
                            <i class="ti ti-currency-dollar f-24"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <span class="badge bg-light-warning border border-warning me-2">
                            <i class="ti ti-cash"></i>
                        </span>
                        <p class="mb-0 text-muted text-sm">Total nilai transaksi</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="text-muted mb-1 f-w-500">Pengguna Aktif</p>
                            <h4 class="mb-0 f-w-600">{{ $totalUser }}</h4>
                        </div>
                        <div class="avtar avtar-l bg-light-success">
                            <i class="ti ti-users f-24"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mt-3">
                        <span class="badge bg-light-success border border-success me-2">
                            <i class="ti ti-user-check"></i>
                        </span>
                        <p class="mb-0 text-muted text-sm">User terdaftar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Area -->
    <div class="row mt-3">
        <div class="col-12">
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
                <div class="card-body">
                    <div style="height: 400px; position: relative;">
                        <canvas id="chartMinusan"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Minusan-->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm">
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Tanggal</th>
                                    <th>Server</th>
                                    <th>Nama</th>
                                    <th>Supplier</th>
                                    <th>Produk</th>
                                    <th>Nomor</th>
                                    <th>Total</th>
                                    <th>Qty</th>
                                    <th>Total/Org</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">
                                        <i class="ti ti-settings"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($minusan as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $item->tanggal }}</td>
                                        <td>{{ $item->server }}</td>
                                        <td>{{ $item->nama }}</td>
                                        <td>{{ $item->spl }}</td>
                                        <td>{{ $item->produk }}</td>
                                        <td>{{ $item->nomor }}</td>
                                        <td>{{ number_format($item->total, 0, ',', '.') }}</td>
                                        <td>{{ $item->qty }}</td>
                                        <td>{{ number_format($item->total_per_orang, 0, ',', '.') }}</td>
                                        <td><span class="badge bg-light-{{ $item->keterangan == 'Dialihkan' ? 'warning' : 'success' }}">{{ $item->keterangan }}</span></td>
                                        <td class="text-center">
                                            @if(auth()->user()->jabatan == 'Admin')
                                            <a href="{{ route('minusanEdit', $item->id) }}" class="avtar avtar-xs btn-link-warning">
                                                <i class="ti ti-edit f-18"></i>
                                            </a>
                                            <form action="{{ route('minusanDestroy', $item->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="avtar avtar-xs btn-link-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                                                    <i class="ti ti-trash f-18"></i>
                                                </button>
                                            </form>
                                            @else
                                            <span class="badge bg-secondary">
                                                <i class="ti ti-lock"></i> Terkunci
                                            </span>
                                            @endif
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

    <script src="{{ asset('sbadmin2/js/chart.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            fetch("/chart/minusan")
                .then(res => res.json())
                .then(data => {
                    const labels = data.map(item => item.bulan);
                    const totals = data.map(item => item.total_bulanan);

                    const ctx = document.getElementById("chartMinusan").getContext("2d");
                    
                    // Create gradient
                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, 'rgba(70, 128, 255, 0.3)');
                    gradient.addColorStop(0.5, 'rgba(70, 128, 255, 0.15)');
                    gradient.addColorStop(1, 'rgba(70, 128, 255, 0.05)');

                    new Chart(ctx, {
                        type: "line",
                        data: {
                            labels: labels,
                            datasets: [{
                                label: "Total Minusan",
                                data: totals,
                                backgroundColor: gradient,
                                borderColor: "#4680ff",
                                borderWidth: 3,
                                pointBackgroundColor: "#fff",
                                pointBorderColor: "#4680ff",
                                pointBorderWidth: 3,
                                pointRadius: 6,
                                pointHoverRadius: 10,
                                pointHoverBackgroundColor: "#4680ff",
                                pointHoverBorderColor: "#fff",
                                pointHoverBorderWidth: 4,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                    align: 'end',
                                    labels: {
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 15,
                                        font: {
                                            size: 13,
                                            weight: '600'
                                        }
                                    }
                                },
                                tooltip: {
                                    enabled: true,
                                    backgroundColor: 'rgba(255, 255, 255, 0.98)',
                                    titleColor: '#1e293b',
                                    bodyColor: '#475569',
                                    borderColor: '#4680ff',
                                    borderWidth: 2,
                                    padding: 16,
                                    boxPadding: 8,
                                    usePointStyle: true,
                                    cornerRadius: 12,
                                    titleFont: {
                                        size: 14,
                                        weight: '700'
                                    },
                                    bodyFont: {
                                        size: 13,
                                        weight: '500'
                                    },
                                    callbacks: {
                                        title: function(context) {
                                            return '📅 ' + context[0].label;
                                        },
                                        label: function(context) {
                                            const value = context.parsed.y;
                                            const formatted = new Intl.NumberFormat('id-ID', {
                                                style: 'currency',
                                                currency: 'IDR',
                                                minimumFractionDigits: 0,
                                                maximumFractionDigits: 0
                                            }).format(value);
                                            return '💰 Total: ' + formatted;
                                        },
                                        afterLabel: function(context) {
                                            const dataIndex = context.dataIndex;
                                            if (dataIndex > 0) {
                                                const currentValue = context.parsed.y;
                                                const previousValue = context.chart.data.datasets[0].data[dataIndex - 1];
                                                const diff = currentValue - previousValue;
                                                const percentage = ((diff / previousValue) * 100).toFixed(1);
                                                const arrow = diff > 0 ? '↑' : diff < 0 ? '↓' : '→';
                                                const emoji = diff > 0 ? '📈' : diff < 0 ? '📉' : '➡️';
                                                return emoji + ' ' + arrow + ' ' + Math.abs(percentage) + '% vs bulan lalu';
                                            }
                                            return '🎯 Data bulan pertama';
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false,
                                        drawBorder: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        },
                                        color: '#64748b',
                                        padding: 10
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(203, 213, 225, 0.4)',
                                        drawBorder: false
                                    },
                                    ticks: {
                                        font: {
                                            size: 12,
                                            weight: '500'
                                        },
                                        color: '#64748b',
                                        padding: 10,
                                        callback: function(value) {
                                            if (value >= 1000000) {
                                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                                            } else if (value >= 1000) {
                                                return 'Rp ' + (value / 1000).toFixed(0) + 'rb';
                                            }
                                            return 'Rp ' + value.toLocaleString('id-ID');
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(err => console.error("Error loading chart:", err));
        });
    </script>
@endsection
