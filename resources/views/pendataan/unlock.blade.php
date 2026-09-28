@extends('layouts.app')

@section('content')
<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-primary text-white text-center py-4">
                <div class="d-inline-flex p-3 rounded-circle bg-white bg-opacity-20 mb-2">
                    <i class="ti ti-lock-access fs-1"></i>
                </div>
                <h4 class="fw-bold mb-1 text-white">Buka Fitur Pendataan</h4>
                <p class="mb-0 text-white-50 small">Masukkan username dan password staf (5 huruf) Anda</p>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('error'))
                    <div class="alert alert-danger border-0 d-flex align-items-center mb-4" role="alert">
                        <i class="ti ti-alert-circle fs-4 me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success border-0 d-flex align-items-center mb-4" role="alert">
                        <i class="ti ti-check-circle fs-4 me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('pendataan.unlock.post') }}">
                    @csrf
                    <!-- Field Username -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">Username Staf</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="ti ti-user"></i></span>
                            <input type="text" name="username" class="form-control form-control-lg @error('username') is-invalid @enderror" placeholder="Contoh: ginta atau Ginta" value="{{ old('username') }}" required autofocus autocomplete="off">
                        </div>
                        @error('username')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field Password (5 Huruf) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">Password (5 Huruf)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="ti ti-key"></i></span>
                            <input type="password" name="password" id="inputStaffPassword" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="Masukkan 5 huruf password..." maxlength="10" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                                <i class="ti ti-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted small">
                            <i class="ti ti-info-circle me-1"></i>Setiap staf memiliki password unik 5 huruf.
                        </div>
                    </div>

                    <!-- Field Pilihan Shift 1/2/3 -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark d-flex align-items-center justify-content-between mb-2">
                            <span><i class="ti ti-clock-play text-primary me-1"></i>Pilih Shift</span>
                            <span class="badge bg-light text-primary border border-primary-subtle fw-medium">Wajib Dipilih</span>
                        </label>
                        <div class="row g-2">
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="shift" id="shift1" value="Shift 1" {{ old('shift', 'Shift 1') === 'Shift 1' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center rounded-3 shadow-none shift-select-card" for="shift1">
                                    <i class="ti ti-sun fs-3 mb-1"></i>
                                    <span class="fw-bold fs-6">Shift 1</span>
                                    <span class="shift-subtext small opacity-75">Pagi</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="shift" id="shift2" value="Shift 2" {{ old('shift') === 'Shift 2' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center rounded-3 shadow-none shift-select-card" for="shift2">
                                    <i class="ti ti-sunset fs-3 mb-1"></i>
                                    <span class="fw-bold fs-6">Shift 2</span>
                                    <span class="shift-subtext small opacity-75">Siang / Sore</span>
                                </label>
                            </div>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="shift" id="shift3" value="Shift 3" {{ old('shift') === 'Shift 3' ? 'checked' : '' }} required>
                                <label class="btn btn-outline-primary w-100 py-2 d-flex flex-column align-items-center justify-content-center rounded-3 shadow-none shift-select-card" for="shift3">
                                    <i class="ti ti-moon-stars fs-3 mb-1"></i>
                                    <span class="fw-bold fs-6">Shift 3</span>
                                    <span class="shift-subtext small opacity-75">Malam</span>
                                </label>
                            </div>
                        </div>
                        @error('shift')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <div class="form-text text-muted small mt-2">
                            <i class="ti ti-info-circle me-1"></i>Nama akan otomatis tercatat: <strong class="text-primary" id="shiftPreviewText">Nuni ( Shift 1 )</strong>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm fw-semibold">
                        <i class="ti ti-unlock me-2"></i>Buka Fitur Pendataan
                    </button>
                </form>

                @if(auth()->user()->jabatan === 'Superadmin')
                    <div class="text-center mt-4 pt-3 border-top">
                        <small class="text-muted d-block mb-2">Anda login sebagai Superadmin:</small>
                        <form method="POST" action="{{ route('pendataan.unlock.post') }}">
                            @csrf
                            <input type="hidden" name="superadmin_bypass" value="1">
                            <div class="d-inline-flex align-items-center gap-2 mb-2 p-1 bg-light rounded-pill border">
                                <div class="form-check form-check-inline m-0 ps-3">
                                    <input class="form-check-input" type="radio" name="shift" id="saShift1" value="Shift 1" checked>
                                    <label class="form-check-label small fw-semibold" for="saShift1">Shift 1</label>
                                </div>
                                <div class="form-check form-check-inline m-0">
                                    <input class="form-check-input" type="radio" name="shift" id="saShift2" value="Shift 2">
                                    <label class="form-check-label small fw-semibold" for="saShift2">Shift 2</label>
                                </div>
                                <div class="form-check form-check-inline m-0 pe-3">
                                    <input class="form-check-input" type="radio" name="shift" id="saShift3" value="Shift 3">
                                    <label class="form-check-label small fw-semibold" for="saShift3">Shift 3</label>
                                </div>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-shield-check me-1"></i>Masuk Langsung sebagai Superadmin
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
            <div class="card-footer bg-light text-center py-3 border-0">
                <small class="text-muted">
                    Lupa password staf? Hubungi Superadmin untuk melihat password akun Anda.
                </small>
            </div>
        </div>
    </div>
</div>

<style>
.shift-select-card {
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    border-width: 2px;
}
.shift-select-card:hover {
    transform: translateY(-2px);
}
.btn-check:checked + .shift-select-card {
    background-color: var(--bs-primary, #0d6efd) !important;
    border-color: var(--bs-primary, #0d6efd) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25) !important;
}
.btn-check:checked + .shift-select-card i,
.btn-check:checked + .shift-select-card span,
.btn-check:checked + .shift-select-card .shift-subtext {
    color: #ffffff !important;
}
</style>

@push('scripts')
<script>
function togglePasswordVisibility() {
    const input = document.getElementById('inputStaffPassword');
    const icon = document.getElementById('togglePasswordIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('ti-eye');
        icon.classList.add('ti-eye-off');
    } else {
        input.type = 'password';
        icon.classList.remove('ti-eye-off');
        icon.classList.add('ti-eye');
    }
}

function updateShiftPreview() {
    const usernameInput = document.querySelector('input[name="username"]');
    const checkedShift = document.querySelector('input[name="shift"]:checked');
    const previewEl = document.getElementById('shiftPreviewText');
    if (!previewEl) return;

    let rawName = usernameInput && usernameInput.value.trim() ? usernameInput.value.trim() : 'Nuni';
    let displayName = rawName.charAt(0).toUpperCase() + rawName.slice(1);
    let shiftVal = checkedShift ? checkedShift.value : 'Shift 1';

    previewEl.textContent = `${displayName} ( ${shiftVal} )`;
}

document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.querySelector('input[name="username"]');
    const shiftRadios = document.querySelectorAll('input[name="shift"]');

    if (usernameInput) {
        usernameInput.addEventListener('input', updateShiftPreview);
    }
    shiftRadios.forEach(function(radio) {
        radio.addEventListener('change', updateShiftPreview);
    });
    updateShiftPreview();
});
</script>
@endpush
@endsection
