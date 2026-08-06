@extends('layouts/app')

@section('content')

<!-- Page Header -->
<div class="page-header mb-3">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h4 class="mb-0 fw-bold text-dark"><i class="ti ti-settings text-primary me-2"></i>Pengaturan Fee & Link Form Sosmed</h4>
                    <p class="text-muted mb-0">Atur nominal fee reward per postingan dan status aktif form publik</p>
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

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <!-- Form Settings Card -->
    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-adjustments me-2 text-primary"></i>Konfigurasi Fee & Akses Form</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.sosmed.settings.update') }}" method="POST">
                    @csrf

                    <!-- Fee Per Submission -->
                    <div class="mb-4">
                        <label for="fee_per_submission" class="form-label fw-bold">
                            Nominal Fee Default per Posting ACC (Rp) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold">Rp</span>
                            <input type="number" 
                                   name="fee_per_submission" 
                                   id="fee_per_submission" 
                                   class="form-control @error('fee_per_submission') is-invalid @enderror" 
                                   value="{{ old('fee_per_submission', (int)$feePerSubmission) }}" 
                                   min="0" 
                                   step="500" 
                                   required>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            <i class="ti ti-info-circle me-1"></i>Nominal ini akan menjadi acuan fee default saat admin menyetujui (ACC) postingan sosmed user.
                        </small>
                        @error('fee_per_submission')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Status Form Toggle -->
                    <div class="mb-4">
                        <label for="form_is_active" class="form-label fw-bold">
                            Status Akses Form Publik <span class="text-danger">*</span>
                        </label>
                        <select name="form_is_active" id="form_is_active" class="form-select @error('form_is_active') is-invalid @enderror" required>
                            <option value="1" {{ $isFormActive == '1' || $isFormActive == 1 ? 'selected' : '' }}>Aktif (Form Publik Bisa Diisi User)</option>
                            <option value="0" {{ $isFormActive == '0' || $isFormActive == 0 ? 'selected' : '' }}>Nonaktif (Form Ditutup Sementara)</option>
                        </select>
                        <small class="text-muted mt-1 d-block">
                            <i class="ti ti-lock me-1"></i>Jika nonaktif, user tidak akan dapat mengirimkan pengisian form baru.
                        </small>
                        @error('form_is_active')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Link Sharing Card -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0 fw-bold"><i class="ti ti-link me-2 text-info"></i>Link Form Publik User</h5>
            </div>
            <div class="card-body">
                <p class="text-muted fs-7">
                    Bagikan URL berikut kepada user/karyawan untuk melakukan pengisian bukti posting sosmed (dapat diakses langsung tanpa login).
                </p>

                <div class="mb-3">
                    <label class="form-label fw-semibold">URL Form Upload Sosmed:</label>
                    <div class="input-group">
                        <input type="text" id="publicUrlInput" class="form-control bg-light font-monospace fs-7" value="{{ $publicFormUrl }}" readonly>
                        <button type="button" class="btn btn-outline-primary" onclick="copyLink()">
                            <i class="ti ti-copy me-1"></i> Salin
                        </button>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ $publicFormUrl }}" target="_blank" class="btn btn-light-primary">
                        <i class="ti ti-external-link me-1"></i> Buka Form di Tab Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copyLink() {
        const input = document.getElementById('publicUrlInput');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Link Form berhasil disalin!',
                showConfirmButton: false,
                timer: 2000
            });
        });
    }
</script>

@endsection
