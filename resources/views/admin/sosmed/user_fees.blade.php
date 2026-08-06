@extends('layouts/app')

@section('content')

<!-- Page Header -->
<div class="page-header mb-3">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h4 class="mb-0 fw-bold text-dark"><i class="ti ti-coin text-warning me-2"></i>Rekap Fee Terkumpul Setiap User</h4>
                    <p class="text-muted mb-0">Akumulasi pendapatan fee per user berdasarkan <strong>kata/kalimat pertama</strong> dari input nama user</p>
                </div>
            </div>
            <div class="col-md-4 text-md-end mt-2 mt-md-0">
                <a href="{{ route('admin.sosmed.dashboard') }}" class="btn btn-outline-secondary btn-sm rounded-2">
                    <i class="ti ti-dashboard me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Info Alert explaining the first word grouping -->
<div class="alert alert-info border-0 shadow-sm mb-3">
    <div class="d-flex align-items-center">
        <i class="ti ti-info-circle fs-3 me-3 text-info"></i>
        <div>
            <strong class="d-block text-dark">Informasi Pengelompokan Fee:</strong>
            <span class="text-muted fs-7">
                Data fee di bawah diakumulasikan secara otomatis dengan mengambil <strong>kata/kalimat pertama</strong> dari input nama panggilan/lengkap user (contoh: "Budi Santoso" & "Budi Pratama" akan dikelompokkan pada kata pertama "Budi").
            </span>
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
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama panggilan / kata pertama user..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-search me-1"></i> Cari User</button>
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
                        <th style="width: 50px;">No</th>
                        <th>Nama Panggilan (Kata Pertama)</th>
                        <th>Variasi Nama Lengkap Inputan</th>
                        <th>Divisi</th>
                        <th class="text-center">Trafik Platform</th>
                        <th class="text-center">Status Submission</th>
                        <th class="text-end">Total Fee Terkumpul</th>
                        <th class="text-center">Aksi Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowNo = 1; @endphp
                    @forelse($userFeesGrouped as $firstWord => $data)
                        <tr>
                            <td>{{ $rowNo++ }}</td>
                            <td>
                                <strong class="text-primary fs-6 d-block">
                                    <i class="ti ti-user-check me-1"></i>{{ $data['first_word'] }}
                                </strong>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($data['variasi_nama'] as $vNama)
                                        <span class="badge bg-light-secondary text-dark border fs-7">{{ $vNama }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td><span class="badge bg-light-info text-info">{{ $data['latest_divisi'] }}</span></td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <span class="badge bg-danger" title="Instagram"><i class="ti ti-brand-instagram"></i> {{ $data['ig_count'] }}</span>
                                    <span class="badge bg-dark" title="TikTok"><i class="ti ti-brand-tiktok"></i> {{ $data['tiktok_count'] }}</span>
                                    <span class="badge bg-primary" title="Facebook"><i class="ti ti-brand-facebook"></i> {{ $data['fb_count'] }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success" title="ACC">{{ $data['acc_submissions'] }} ACC</span>
                                @if($data['pending_submissions'] > 0)
                                    <span class="badge bg-warning text-dark">{{ $data['pending_submissions'] }} Pending</span>
                                @endif
                                @if($data['rejected_submissions'] > 0)
                                    <span class="badge bg-danger">{{ $data['rejected_submissions'] }} Ditolak</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <strong class="text-success fs-5">
                                    Rp {{ number_format($data['total_fee'], 0, ',', '.') }}
                                </strong>
                            </td>
                            <td class="text-center">
                                <!-- Modal Trigger Button -->
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalUserDetail{{ Str::slug($firstWord) }}">
                                    <i class="ti ti-eye me-1"></i> Detail
                                </button>

                                <!-- Modal Detail Submission User -->
                                <div class="modal fade text-start" id="modalUserDetail{{ Str::slug($firstWord) }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title text-white fw-bold">
                                                    <i class="ti ti-user me-1"></i> Rincian Post & Fee: {{ $data['first_word'] }}
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-3 bg-light p-3 rounded">
                                                    <div class="col-md-6">
                                                        <small class="text-muted d-block">Nama Panggilan (Kata 1):</small>
                                                        <strong class="text-dark fs-6">{{ $data['first_word'] }}</strong>
                                                    </div>
                                                    <div class="col-md-6 text-md-end">
                                                        <small class="text-muted d-block">Total Fee Terkumpul (ACC):</small>
                                                        <strong class="text-success fs-4">Rp {{ number_format($data['total_fee'], 0, ',', '.') }}</strong>
                                                    </div>
                                                </div>

                                                <h6 class="fw-bold mb-2">Riwayat Bukti Posting:</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>No</th>
                                                                <th>Nama Input</th>
                                                                <th>Platform</th>
                                                                <th>Tanggal</th>
                                                                <th>Status</th>
                                                                <th>Fee</th>
                                                                <th>Foto</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($data['items'] as $subIdx => $subItem)
                                                                <tr>
                                                                    <td>{{ $subIdx + 1 }}</td>
                                                                    <td>{{ $subItem->nama }}</td>
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
                                                                            @foreach($subItem->photos as $ph)
                                                                                <a href="{{ asset($ph) }}" target="_blank">
                                                                                    <img src="{{ asset($ph) }}" style="width:28px; height:28px; object-fit:cover;" class="rounded border">
                                                                                </a>
                                                                            @endforeach
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
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
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="ti ti-coin-off fs-1 d-block mb-2 text-muted"></i>
                                Belum ada akumulasi data fee user.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
