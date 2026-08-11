@extends('layouts/app')

@section('content')

<!-- Page Header -->
<div class="page-header mb-3">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h4 class="mb-0 fw-bold text-dark"><i class="ti ti-trophy text-warning me-2"></i>Leaderboard Peringkat Karyawan & Rekap Fee</h4>
                    <p class="text-muted mb-0">Peringkat 90 karyawan diurutkan dari Poin Task terbanyak (1 Task Upload = 1 Poin)</p>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <a href="{{ route('admin.sosmed.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-2">
                    <i class="ti ti-dashboard me-1"></i> Dashboard Admin
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Search Filter -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-3">
        <form action="{{ route('admin.sosmed.user-fees') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama karyawan, username sosmed, atau divisi..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-search me-1"></i> Cari Data</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.sosmed.user-fees') }}" class="btn btn-light-secondary"><i class="ti ti-refresh"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px;" class="text-center">Rank</th>
                        <th>Nama Karyawan</th>
                        <th>Username Sosmed</th>
                        <th>Divisi</th>
                        <th class="text-center">Trafik Platform</th>
                        <th class="text-center">Status Task</th>
                        <th class="text-center">Total Poin (Task)</th>
                        <th class="text-end">Total Fee (ACC)</th>
                        <th class="text-center">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaderboardAll as $user)
                        <tr class="{{ $user['rank'] <= 5 ? 'table-warning bg-opacity-10' : '' }}">
                            <!-- Rank Column -->
                            <td class="text-center">
                                @if($user['rank'] === 1)
                                    <span class="badge bg-warning text-dark fs-6 px-2 py-1 shadow-sm">🥇 #1</span>
                                @elseif($user['rank'] === 2)
                                    <span class="badge bg-secondary text-white fs-6 px-2 py-1 shadow-sm">🥈 #2</span>
                                @elseif($user['rank'] === 3)
                                    <span class="badge bg-dark text-white fs-6 px-2 py-1 shadow-sm" style="background-color: #cd7f32 !important;">🥉 #3</span>
                                @elseif($user['rank'] <= 5)
                                    <span class="badge bg-light-primary text-primary fw-bold fs-7">⭐ #{{ $user['rank'] }}</span>
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
                            <td><span class="badge bg-light-secondary text-dark">{{ $user['divisi'] }}</span></td>

                            <!-- Platform Breakdown Column -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <span class="badge bg-danger" title="Instagram"><i class="ti ti-brand-instagram"></i> {{ $user['ig_count'] }}</span>
                                    <span class="badge bg-dark" title="TikTok"><i class="ti ti-brand-tiktok"></i> {{ $user['tiktok_count'] }}</span>
                                    <span class="badge bg-primary" title="Facebook"><i class="ti ti-brand-facebook"></i> {{ $user['fb_count'] }}</span>
                                </div>
                            </td>

                            <!-- Status Task Column -->
                            <td class="text-center">
                                <span class="badge bg-success" title="ACC">{{ $user['acc_count'] }} ACC</span>
                                @if($user['pending_count'] > 0)
                                    <span class="badge bg-warning text-dark">{{ $user['pending_count'] }} Pending</span>
                                @endif
                                @if($user['rejected_count'] > 0)
                                    <span class="badge bg-danger">{{ $user['rejected_count'] }} Ditolak</span>
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

                            <!-- Total Fee Column -->
                            <td class="text-end">
                                <strong class="text-success fs-5">
                                    Rp {{ number_format($user['total_fee'], 0, ',', '.') }}
                                </strong>
                            </td>

                            <!-- Detail Action Column -->
                            <td class="text-center">
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalUserDetail{{ Str::slug($user['nama']) }}">
                                    <i class="ti ti-eye me-1"></i> Rincian
                                </button>

                                <!-- Modal Detail Submission User -->
                                <div class="modal fade text-start" id="modalUserDetail{{ Str::slug($user['nama']) }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title text-white fw-bold">
                                                    <i class="ti ti-user me-1"></i> Rincian Task & Fee: {{ $user['nama'] }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3 bg-light p-3 rounded">
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Nama Karyawan & Divisi:</small>
                                                        <strong class="text-dark fs-6">{{ $user['nama'] }} ({{ $user['divisi'] }})</strong>
                                                        @if($user['username_sosmed'] !== '-')
                                                            <div class="text-info fs-7 fw-semibold"><i class="ti ti-at"></i> {{ $user['username_sosmed'] }}</div>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6 text-md-end">
                                                        <small class="text-muted d-block">Total Poin / Task Dikerjakan:</small>
                                                        <strong class="text-primary fs-5 me-2"><i class="ti ti-star"></i> {{ $user['total_points'] }} Poin</strong>
                                                        <small class="text-muted d-block mt-1">Total Fee Terkumpul (ACC):</small>
                                                        <strong class="text-success fs-4">Rp {{ number_format($user['total_fee'], 0, ',', '.') }}</strong>
                                                    </div>
                                                </div>

                                                <h6 class="fw-bold mb-2"><i class="ti ti-history me-1"></i>Riwayat Task & Bukti Upload (3 Foto):</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Tugas Sosmed</th>
                                                                <th>Platform</th>
                                                                <th>Tanggal</th>
                                                                <th>Status</th>
                                                                <th>Fee</th>
                                                                <th>Foto Bukti (Like, Komen, Share)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($user['submissions'] as $subIdx => $subItem)
                                                                <tr>
                                                                    <td>{{ $subIdx + 1 }}</td>
                                                                    <td>
                                                                        <strong class="d-block text-dark small">{{ $subItem->pilihan_tugas ?? 'Tugas Sosmed' }}</strong>
                                                                        @if($subItem->tugas_link)
                                                                            <a href="{{ $subItem->tugas_link }}" target="_blank" class="text-primary fs-7"><i class="ti ti-link"></i> Link</a>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($subItem->sosmed_platform === 'Instagram')
                                                                            <span class="badge bg-danger">IG</span>
                                                                        @elseif($subItem->sosmed_platform === 'TikTok')
                                                                            <span class="badge bg-dark">TikTok</span>
                                                                        @else
                                                                            <span class="badge bg-primary">FB</span>
                                                                        @endif
                                                                    </td>
                                                                    <td><small>{{ $subItem->created_at->format('d/m/Y H:i') }}</small></td>
                                                                    <td>
                                                                        @if($subItem->status === 'acc')
                                                                            <span class="badge bg-success">ACC</span>
                                                                        @elseif($subItem->status === 'ditolak')
                                                                            <span class="badge bg-danger">Ditolak</span>
                                                                        @else
                                                                            <span class="badge bg-warning text-dark">Pending</span>
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if($subItem->status === 'acc')
                                                                            Rp {{ number_format($subItem->fee_amount, 0, ',', '.') }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if(!empty($subItem->photos))
                                                                            <div class="d-flex gap-1">
                                                                                @php $labels = ['Like', 'Komen', 'Share']; @endphp
                                                                                @foreach($subItem->photos as $phIdx => $ph)
                                                                                    <a href="{{ asset($ph) }}" target="_blank" title="{{ $labels[$phIdx] ?? 'Foto '.($phIdx+1) }}">
                                                                                        <img src="{{ asset($ph) }}" style="width:32px; height:32px; object-fit:cover;" class="rounded border shadow-sm">
                                                                                    </a>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="7" class="text-center py-3 text-muted">Belum ada rincian task.</td>
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
                                <i class="ti ti-trophy-off fs-1 d-block mb-2 text-muted"></i>
                                Belum ada data leaderboard karyawan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
