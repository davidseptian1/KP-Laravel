@extends('layouts.app')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="mb-1 fw-bold text-dark"><i class="ti ti-shield-lock text-primary me-2"></i>Pengaturan Akses Fitur Pendataan</h3>
        <p class="text-muted mb-0">Kelola dan tentukan akun staf yang diizinkan untuk melihat dan menggunakan fitur Pendataan di dashboard mereka.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('pendataan.index') }}" class="btn btn-outline-primary">
            <i class="ti ti-notes me-1"></i> Buka Fitur Pendataan
        </a>
    </div>
</div>

<!-- Quick Statistics -->
<div class="row mb-4 g-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="ti ti-users fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Akun Staff</h6>
                    <h3 class="mb-0 fw-bold">{{ $totalStaff }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="ti ti-user-check fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Staff Dengan Akses</h6>
                    <h3 class="mb-0 fw-bold text-success">{{ $staffWithAccess }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-body d-flex align-items-center">
                <div class="rounded-circle bg-secondary bg-opacity-10 p-3 text-secondary me-3">
                    <i class="ti ti-user-x fs-2"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">Staff Tanpa Akses</h6>
                    <h3 class="mb-0 fw-bold text-secondary">{{ $staffWithoutAccess }}</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info border-0 shadow-sm d-flex align-items-start mb-4 rounded-3">
    <i class="ti ti-info-circle fs-3 me-3 text-info mt-1"></i>
    <div>
        <strong class="d-block mb-1">Informasi Hak Akses Fitur Pendataan:</strong>
        <span class="text-secondary">
            • <strong>Superadmin</strong> secara otomatis memiliki hak akses penuh ke fitur Pendataan.<br>
            • Untuk akun <strong>Staff</strong>, menu dan halaman Pendataan hanya akan muncul jika saklar akses diaktifkan di bawah ini.
        </span>
    </div>
</div>

<!-- Main Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-bold text-dark d-flex align-items-center">
            <i class="ti ti-list-check me-2 text-primary"></i>Daftar Pengguna & Status Akses
        </h5>
        <form method="GET" action="{{ route('admin.pendataan.access') }}" class="d-flex align-items-center gap-2">
            <select name="role" class="form-select form-select-sm" style="min-width: 140px;" onchange="this.form.submit()">
                <option value="Staff" {{ $currentRole === 'Staff' ? 'selected' : '' }}>Hanya Staff</option>
                <option value="all" {{ $currentRole === 'all' ? 'selected' : '' }}>Semua Pengguna</option>
            </select>
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control" placeholder="Cari nama/email..." value="{{ $currentSearch }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="ti ti-search"></i></button>
            </div>
            @if($currentSearch || $currentRole !== 'Staff')
                <a href="{{ route('admin.pendataan.access') }}" class="btn btn-sm btn-light border text-muted" title="Reset filter">
                    <i class="ti ti-refresh"></i>
                </a>
            @endif
        </form>
    </div>

    <form method="POST" action="{{ route('admin.pendataan.access.batch') }}" id="batchAccessForm">
        @csrf
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 60px;">No</th>
                            <th>Nama Pengguna</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th class="text-center" style="width: 180px;">Status Akses</th>
                            <th class="text-center pe-4" style="width: 180px;">Aksi Saklar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $u)
                        <tr id="row-user-{{ $u->id }}">
                            <td class="ps-4 text-muted fw-semibold">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light text-primary fw-bold d-flex align-items-center justify-content-center me-3 border" style="width: 38px; height: 38px; font-size: 1rem;">
                                        {{ strtoupper(substr($u->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $u->nama }}</div>
                                        <small class="text-muted">{{ $u->no_hp ?: '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-secondary">{{ $u->email }}</span>
                            </td>
                            <td>
                                @if($u->jabatan === 'Staff')
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">Staff</span>
                                @elseif($u->jabatan === 'Admin')
                                    <span class="badge bg-dark px-2 py-1">Admin</span>
                                @elseif($u->jabatan === 'Superadmin')
                                    <span class="badge bg-danger px-2 py-1">Superadmin</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">{{ $u->jabatan }}</span>
                                @endif
                            </td>
                            <td class="text-center" id="badge-container-{{ $u->id }}">
                                @if($u->has_pendataan_access)
                                    <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                        <i class="ti ti-check me-1"></i> Diberi Akses
                                    </span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-muted border border-secondary-subtle px-3 py-2">
                                        <i class="ti ti-x me-1"></i> Tidak Ada Akses
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input js-access-toggle"
                                           type="checkbox"
                                           role="switch"
                                           id="switch-{{ $u->id }}"
                                           name="accessible_user_ids[]"
                                           value="{{ $u->id }}"
                                           data-user-id="{{ $u->id }}"
                                           data-user-name="{{ $u->nama }}"
                                           data-url="{{ route('admin.pendataan.access.toggle', $u->id) }}"
                                           {{ $u->has_pendataan_access ? 'checked' : '' }}
                                           style="width: 2.7em; height: 1.4em; cursor: pointer;">
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="ti ti-user-off fs-1 d-block mb-2 text-secondary"></i>
                                Tidak ada data pengguna yang sesuai dengan filter.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">
                <i class="ti ti-info-circle me-1"></i> Klik saklar untuk langsung memperbarui akses staf secara instan.
            </span>
            <button type="submit" class="btn btn-primary btn-sm px-3">
                <i class="ti ti-device-floppy me-1"></i> Simpan Semua Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const switches = document.querySelectorAll('.js-access-toggle');

    switches.forEach(function(toggle) {
        toggle.addEventListener('change', function(e) {
            const userId = this.getAttribute('data-user-id');
            const userName = this.getAttribute('data-user-name');
            const url = this.getAttribute('data-url');
            const isChecked = this.checked;
            const badgeContainer = document.getElementById('badge-container-' + userId);

            // Temporarily disable to prevent spam clicks
            this.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    if (data.has_access) {
                        badgeContainer.innerHTML = `
                            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                <i class="ti ti-check me-1"></i> Diberi Akses
                            </span>`;
                    } else {
                        badgeContainer.innerHTML = `
                            <span class="badge rounded-pill bg-secondary-subtle text-muted border border-secondary-subtle px-3 py-2">
                                <i class="ti ti-x me-1"></i> Tidak Ada Akses
                            </span>`;
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: data.message || 'Status akses berhasil diperbarui!',
                            showConfirmButton: false,
                            timer: 2500,
                            timerProgressBar: true
                        });
                    }
                } else {
                    // Revert state on error
                    this.checked = !isChecked;
                    alert('Gagal memperbarui status akses.');
                }
            })
            .catch(err => {
                this.disabled = false;
                this.checked = !isChecked;
                console.error(err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Terjadi kesalahan saat memperbarui akses.',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        });
    });
});
</script>
@endpush
