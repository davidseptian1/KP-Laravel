@extends('layouts/app')

@section('content')

<!-- Page Header -->
<div class="page-header mb-3">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="page-header-title">
                    <h4 class="mb-0 fw-bold text-dark"><i class="ti ti-settings text-primary me-2"></i>Pengaturan Fee & Kelola Penugasan Sosmed</h4>
                    <p class="text-muted mb-0">Atur nominal fee reward, status aktif form, dan kelola daftar tugas harian karyawan</p>
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

<form action="{{ route('admin.sosmed.settings.update') }}" method="POST">
    @csrf

    <div class="row">
        <!-- Form Settings Card (Fee & Akses) -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti ti-adjustments me-2 text-primary"></i>Konfigurasi Fee & Akses Form</h5>
                </div>
                <div class="card-body">
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
                    <div class="mb-3">
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
                </div>
            </div>
        </div>

        <!-- Link Sharing Card -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="ti ti-link me-2 text-info"></i>Link Form Publik User</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted fs-7 mb-3">
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

    <!-- Card Kelola Penugasan Sosmed (Judul, Link, Tanggal Start & Auto-Expire 00:00) -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="ti ti-checklist text-primary me-2"></i>Kelola Penugasan Sosmed (Judul, Link & Tanggal Start)
                        </h5>
                        <small class="text-muted">
                            Penugasan akan otomatis tampil di form user pada <strong>Tanggal Start</strong> dan <strong>otomatis HILANG pada jam 00:00 (hari berikutnya)</strong>.
                        </small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-2" onclick="addTaskRow()">
                        <i class="ti ti-plus me-1"></i> Tambah Penugasan Baru
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tasksTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">#</th>
                                    <th>Judul Penugasan <span class="text-danger">*</span></th>
                                    <th>Link Postingan (URL)</th>
                                    <th style="width: 170px;">Tanggal Start <span class="text-danger">*</span></th>
                                    <th style="width: 140px;" class="text-center">Status User</th>
                                    <th style="width: 60px;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="tasksContainer">
                                @forelse($taskItems as $tIdx => $taskItem)
                                    @php
                                        $todayStr = date('Y-m-d');
                                        $isToday = ($taskItem['tanggal_start'] === $todayStr);
                                        $isFuture = ($taskItem['tanggal_start'] > $todayStr);
                                    @endphp
                                    <tr class="task-row">
                                        <td class="text-center fw-bold row-number">{{ $tIdx + 1 }}</td>
                                        <td>
                                            <input type="text" 
                                                   name="tasks[{{ $tIdx }}][judul]" 
                                                   class="form-control form-control-sm fw-semibold" 
                                                   placeholder="Contoh: Tugas {{ $tIdx + 1 }}: Postingan Instagram Promo" 
                                                   value="{{ $taskItem['judul'] }}" 
                                                   required>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   name="tasks[{{ $tIdx }}][link]" 
                                                   class="form-control form-control-sm font-monospace fs-7" 
                                                   placeholder="https://www.instagram.com/p/..." 
                                                   value="{{ $taskItem['link'] }}">
                                        </td>
                                        <td>
                                            <input type="date" 
                                                   name="tasks[{{ $tIdx }}][tanggal_start]" 
                                                   class="form-control form-control-sm" 
                                                   value="{{ $taskItem['tanggal_start'] }}" 
                                                   required>
                                        </td>
                                        <td class="text-center">
                                            @if($isToday)
                                                <span class="badge bg-success" title="Aktif dan tampil di form user hari ini">
                                                    <i class="ti ti-eye me-1"></i>Aktif Today
                                                </span>
                                            @elseif($isFuture)
                                                <span class="badge bg-info text-white" title="Akan otomatis aktif di form user pada tanggal ini">
                                                    <i class="ti ti-clock me-1"></i>Mendatang
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border" title="Sudah lewat 00:00 (Otomatis hilang dari form user)">
                                                    <i class="ti ti-eye-off me-1"></i>Expired 00:00
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeTaskRow(this)" title="Hapus task ini">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="task-row">
                                        <td class="text-center fw-bold row-number">1</td>
                                        <td>
                                            <input type="text" name="tasks[0][judul]" class="form-control form-control-sm fw-semibold" placeholder="Contoh: Tugas 1: Postingan Sosmed Hari Ini" value="Tugas 1: Postingan Sosmed Hari Ini" required>
                                        </td>
                                        <td>
                                            <input type="text" name="tasks[0][link]" class="form-control form-control-sm font-monospace fs-7" placeholder="https://www.instagram.com/p/...">
                                        </td>
                                        <td>
                                            <input type="date" name="tasks[0][tanggal_start]" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><i class="ti ti-eye me-1"></i>Aktif Today</span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeTaskRow(this)">
                                                <i class="ti ti-trash fs-5"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="ti ti-device-floppy me-1"></i> Simpan Semua Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let taskRowIndex = {{ count($taskItems) > 0 ? count($taskItems) : 1 }};

    function addTaskRow() {
        const container = document.getElementById('tasksContainer');
        if (!container) return;

        const todayDate = '{{ date("Y-m-d") }}';
        const tr = document.createElement('tr');
        tr.className = 'task-row';
        
        tr.innerHTML = `
            <td class="text-center fw-bold row-number">${taskRowIndex + 1}</td>
            <td>
                <input type="text" name="tasks[${taskRowIndex}][judul]" class="form-control form-control-sm fw-semibold" placeholder="Contoh: Tugas ${taskRowIndex + 1}: Postingan Sosmed" value="Tugas ${taskRowIndex + 1}" required>
            </td>
            <td>
                <input type="text" name="tasks[${taskRowIndex}][link]" class="form-control form-control-sm font-monospace fs-7" placeholder="https://www.instagram.com/p/...">
            </td>
            <td>
                <input type="date" name="tasks[${taskRowIndex}][tanggal_start]" class="form-control form-control-sm" value="${todayDate}" required>
            </td>
            <td class="text-center">
                <span class="badge bg-success"><i class="ti ti-eye me-1"></i>Aktif Today</span>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="removeTaskRow(this)" title="Hapus task ini">
                    <i class="ti ti-trash fs-5"></i>
                </button>
            </td>
        `;
        container.appendChild(tr);
        taskRowIndex++;
        updateRowNumbers();
    }

    function removeTaskRow(btn) {
        const container = document.getElementById('tasksContainer');
        const rows = container.querySelectorAll('.task-row');
        if (rows.length <= 1) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Bisa Dihapus',
                text: 'Minimal harus ada 1 penugasan sosmed.',
                confirmColor: '#1890ff'
            });
            return;
        }

        const row = btn.closest('tr');
        if (row) {
            row.remove();
            updateRowNumbers();
        }
    }

    function updateRowNumbers() {
        const rows = document.querySelectorAll('.task-row');
        rows.forEach((r, idx) => {
            const numCell = r.querySelector('.row-number');
            if (numCell) numCell.innerText = idx + 1;
        });
    }

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
