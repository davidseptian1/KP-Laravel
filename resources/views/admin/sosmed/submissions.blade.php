@extends('layouts/app')

@section('content')

<!-- Page Header -->
<div class="page-header mb-3">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h4 class="mb-0 fw-bold text-dark"><i class="ti ti-history text-primary me-2"></i>Riwayat & Moderasi Posting Sosmed</h4>
                    <p class="text-muted mb-0">Kelola persetujuan (ACC/Tolak) dan beri catatan untuk setiap bukti simpan sosmed user</p>
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

<!-- Alert Messages -->
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Filters Card -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body py-3">
        <form action="{{ route('admin.sosmed.submissions') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="ti ti-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama user / divisi..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="acc" {{ request('status') == 'acc' ? 'selected' : '' }}>ACC (Disetujui)</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="platform" class="form-select">
                    <option value="">-- Semua Platform --</option>
                    <option value="Instagram" {{ request('platform') == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                    <option value="TikTok" {{ request('platform') == 'TikTok' ? 'selected' : '' }}>TikTok</option>
                    <option value="Facebook" {{ request('platform') == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100"><i class="ti ti-filter me-1"></i>Filter</button>
                @if(request()->hasAny(['search', 'status', 'platform']))
                    <a href="{{ route('admin.sosmed.submissions') }}" class="btn btn-light-secondary" title="Reset Filter"><i class="ti ti-refresh"></i></a>
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
                        <th>User (Nama & Kata 1)</th>
                        <th>Divisi</th>
                        <th>Platform</th>
                        <th>Foto Bukti</th>
                        <th>Tanggal Submit</th>
                        <th>Status</th>
                        <th>Fee Earned</th>
                        <th>Catatan Admin</th>
                        <th style="width: 150px;" class="text-center">Aksi Moderasi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $key => $item)
                        <tr>
                            <td>{{ $submissions->firstItem() + $key }}</td>
                            <td>
                                <strong class="text-dark d-block">{{ $item->nama }}</strong>
                                <span class="badge bg-light-primary text-primary font-monospace fs-7">
                                    <i class="ti ti-id me-1"></i>Kata 1: {{ $item->nama_first_word }}
                                </span>
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
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($item->photos as $pIndex => $photo)
                                            <a href="{{ asset($photo) }}" target="_blank" title="Klik untuk perbesar">
                                                <img src="{{ asset($photo) }}" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted fs-7">Tidak ada foto</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-dark d-block fw-semibold">{{ $item->created_at->format('d/m/Y') }}</small>
                                <small class="text-muted">{{ $item->created_at->format('H:i') }} WIB</small>
                            </td>
                            <td>
                                @if($item->status === 'acc')
                                    <span class="badge bg-success"><i class="ti ti-check me-1"></i>ACC</span>
                                @elseif($item->status === 'ditolak')
                                    <span class="badge bg-danger"><i class="ti ti-x me-1"></i>Ditolak</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($item->status === 'acc')
                                    <strong class="text-success fs-6">Rp {{ number_format($item->fee_amount, 0, ',', '.') }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($item->catatan)
                                    <small class="text-dark fst-italic"><i class="ti ti-notes text-muted me-1"></i>"{{ $item->catatan }}"</small>
                                @else
                                    <span class="text-muted fst-italic fs-7">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <!-- Button Modal Status -->
                                    <button type="button" 
                                            class="btn btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalStatus{{ $item->id }}"
                                            title="Ubah Status / Catatan">
                                        <i class="ti ti-edit"></i> Edit
                                    </button>

                                    <!-- Button Hapus -->
                                    <form action="{{ route('admin.sosmed.submissions.destroy', $item->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data postingan ini?')"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Data">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Modal Status & Catatan -->
                                <div class="modal fade text-start" id="modalStatus{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.sosmed.submissions.status', $item->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header bg-light">
                                                    <h5 class="modal-title fw-bold">
                                                        <i class="ti ti-edit me-1 text-primary"></i>Moderasi Submission #{{ $item->id }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label text-muted fs-7 mb-1">User & Platform</label>
                                                        <div class="fw-bold text-dark fs-6">{{ $item->nama }} ({{ $item->divisi }})</div>
                                                        <div class="text-muted fs-7">Platform: {{ $item->sosmed_platform }}</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="statusSelect{{ $item->id }}" class="form-label fw-semibold">Pilih Status Moderasi</label>
                                                        <select name="status" id="statusSelect{{ $item->id }}" class="form-select" required>
                                                            <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="acc" {{ $item->status == 'acc' ? 'selected' : '' }}>ACC (Disetujui)</option>
                                                            <option value="ditolak" {{ $item->status == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                                        </select>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="feeInput{{ $item->id }}" class="form-label fw-semibold">Nominal Fee (Rp)</label>
                                                        <input type="number" 
                                                               name="fee_amount" 
                                                               id="feeInput{{ $item->id }}" 
                                                               class="form-control" 
                                                               value="{{ $item->fee_amount > 0 ? (int)$item->fee_amount : (int)$defaultFee }}" 
                                                               min="0" 
                                                               step="500">
                                                        <small class="text-muted fs-7">Default fee per post: Rp {{ number_format($defaultFee, 0, ',', '.') }}</small>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="catatanInput{{ $item->id }}" class="form-label fw-semibold">Catatan Admin</label>
                                                        <textarea name="catatan" 
                                                                  id="catatanInput{{ $item->id }}" 
                                                                  class="form-control" 
                                                                  rows="3" 
                                                                  placeholder="Berikan alasan jika ditolak atau catatan tambahan...">{{ $item->catatan }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary btn-sm"><i class="ti ti-device-floppy me-1"></i>Simpan Perubahan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="ti ti-inbox fs-1 d-block mb-2 text-muted"></i>
                                Belum ada data postingan sosmed yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($submissions->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $submissions->links() }}
        </div>
    @endif
</div>

@endsection
