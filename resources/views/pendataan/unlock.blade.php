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
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="ti ti-shield-check me-1"></i>Masuk Langsung sebagai Superadmin
                            </button>
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
</script>
@endpush
@endsection
