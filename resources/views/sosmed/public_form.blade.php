<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Form Bukti Posting Sosmed | e-SMT</title>
    <link rel="icon" href="{{ asset('mantis/images/favicon.svg') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('mantis/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('mantis/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('sweetalert2/sweetalert2.min.css') }}">
    <style>
        body {
            background: linear-gradient(135deg, #eef2f5 0%, #e4e8eb 100%);
            min-height: 100vh;
            font-family: 'Public Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 12px;
        }
        .form-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
            width: 100%;
            max-width: 780px;
            margin: 0 auto;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
            color: #ffffff;
            padding: 30px 32px;
            text-align: center;
        }
        .card-header-custom h3 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 1.6rem;
        }
        .card-header-custom p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin-bottom: 0;
        }
        .card-body-custom {
            padding: 32px;
        }
        .info-fee-box {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
            border-left: 4px solid #1890ff;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 24px;
        }
        .info-fee-box p {
            color: #003a8c;
            font-size: 0.88rem;
            margin: 0;
            line-height: 1.5;
        }
        .form-label {
            font-weight: 600;
            color: #262626;
            margin-bottom: 6px;
            font-size: 0.92rem;
        }
        .required-star {
            color: #ff4d4f;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 11px 14px;
            border: 1px solid #d9d9d9;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #40a9ff;
            box-shadow: 0 0 0 3px rgba(24, 144, 255, 0.15);
        }
        .auto-divisi-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8rem;
            padding: 4px 10px;
            border-radius: 20px;
            background-color: #e6f7ff;
            color: #1890ff;
            border: 1px solid #91d5ff;
            margin-top: 6px;
        }
        .task-link-box {
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f3ff 100%);
            border: 1.5px dashed #40a9ff;
            border-radius: 10px;
            padding: 16px;
            margin-top: 10px;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-start-task {
            background: linear-gradient(135deg, #52c41a 0%, #389e0d 100%);
            color: #ffffff !important;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(82, 196, 26, 0.3);
            transition: all 0.25s ease;
        }
        .btn-start-task:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(82, 196, 26, 0.4);
            color: #ffffff !important;
        }
        .photo-note-box {
            background-color: #fffbe6;
            border: 1px solid #ffe58f;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 12px;
        }
        .photo-note-box .note-title {
            color: #d46b08;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 6px;
        }
        .photo-note-box ul {
            margin-bottom: 0;
            padding-left: 20px;
            font-size: 0.88rem;
            color: #595959;
        }
        .photo-note-box ul li {
            margin-bottom: 3px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 13px 24px;
            border-radius: 8px;
            font-size: 1.05rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 14px rgba(24, 144, 255, 0.35);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(24, 144, 255, 0.45);
            color: #ffffff;
        }
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 14px;
        }
        .preview-card {
            position: relative;
            width: 110px;
            height: 120px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #d9d9d9;
            box-shadow: 0 3px 8px rgba(0,0,0,0.08);
            background: #fafafa;
            display: flex;
            flex-direction: column;
        }
        .preview-card img {
            width: 100%;
            height: 85px;
            object-fit: cover;
        }
        .preview-card .badge-note {
            background: #1890ff;
            color: white;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 4px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }
        .task-completed-badge {
            background-color: #f6ffed;
            border: 1px solid #b7eb8f;
            border-radius: 8px;
            padding: 10px 14px;
            margin-top: 8px;
            color: #389e0d;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .public-leaderboard-section {
            margin-top: 40px;
            padding-top: 28px;
            border-top: 2px dashed #e8e8e8;
        }
        .public-leaderboard-title {
            color: #1890ff;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .footer-credit {
            text-align: center;
            margin-top: 24px;
            font-size: 0.82rem;
            color: #8c8c8c;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">
        <div class="card-header-custom">
            <div class="mb-2">
                <i class="ti ti-brand-instagram fs-1 me-2"></i>
                <i class="ti ti-brand-tiktok fs-1 me-2"></i>
                <i class="ti ti-brand-facebook fs-1 me-2"></i>
                <i class="ti ti-brand-youtube fs-1"></i>
            </div>
            <h3>Form Upload Bukti Posting Sosmed</h3>
            <p>Silakan isi data postingan Anda untuk pencatatan fee reward karyawan</p>
        </div>

        <div class="card-body-custom">
            @if ($isFormActive === '0' || $isFormActive === 0 || $isFormActive === false)
                <div class="alert alert-warning text-center p-4">
                    <i class="ti ti-alert-triangle fs-1 d-block mb-2 text-warning"></i>
                    <h5 class="fw-bold">Form Sedang Dinonaktifkan</h5>
                    <p class="mb-0 text-muted">Mohon maaf, pengisian form bukti posting sosmed saat ini ditutup sementara oleh admin.</p>
                </div>
            @else
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-circle-check fs-4 me-2 align-middle"></i>
                        <strong>Berhasil!</strong> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle fs-4 me-2 align-middle"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <div class="fw-semibold mb-1"><i class="ti ti-alert-circle me-1"></i> Mohon periksa inputan Anda:</div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Info Note Ketentuan Pengisian Nama -->
                <div class="info-fee-box">
                    <div class="d-flex align-items-start">
                        <i class="ti ti-info-circle fs-3 me-2 text-primary flex-shrink-0 mt-1"></i>
                        <div>
                            <strong class="d-block text-primary mb-1">PETUNJUK PENGISIAN FORM & ATURAN TASK HARIAN:</strong>
                            <p class="mb-1">
                                1. Ketik nama Anda di kolom <strong>Nama Karyawan</strong>, divisi akan <strong>terisi otomatis</strong>.
                            </p>
                            <p class="mb-0">
                                2. Task yang <strong>sudah Anda selesaikan hari ini</strong> akan terkunci otomatis (tidak bisa diklik lagi hari ini). Silakan selesaikan task lainnya.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('sosmed.form.submit') }}" method="POST" enctype="multipart/form-data" id="sosmedForm">
                    @csrf

                    <!-- Field 1: Nama Karyawan (Autocomplete) -->
                    <div class="mb-3">
                        <label for="nama" class="form-label">
                            Nama Karyawan <span class="required-star">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" 
                               name="nama" 
                               value="{{ old('nama') }}" 
                               list="karyawanDatalist"
                               placeholder="Ketik nama Anda (contoh: Defit, Haikal, Ninda...)" 
                               autocomplete="off"
                               required>
                        <datalist id="karyawanDatalist">
                            @foreach($karyawanList as $karyawan)
                                <option value="{{ $karyawan['nama'] }}" data-divisi="{{ $karyawan['divisi'] }}">
                                    {{ $karyawan['nama'] }} ({{ $karyawan['divisi'] }})
                                </option>
                            @endforeach
                        </datalist>
                        <div id="autoDivisiNotice" class="auto-divisi-badge d-none">
                            <i class="ti ti-circle-check"></i> <span id="autoDivisiText">Divisi terisi otomatis</span>
                        </div>
                        <div class="form-text text-muted">
                            <i class="ti ti-bulb me-1"></i>Ketik nama Anda, sistem akan mencarikan nama dan menyesuaikan divisinya secara otomatis.
                        </div>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field 2: Username Sosmed (Di bawah Field Nama) -->
                    <div class="mb-3">
                        <label for="username_sosmed" class="form-label">
                            Username Sosmed <span class="required-star">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('username_sosmed') is-invalid @enderror" 
                               id="username_sosmed" 
                               name="username_sosmed" 
                               value="{{ old('username_sosmed') }}" 
                               placeholder="Contoh: @defi_firmansyah atau defi123" 
                               required>
                        <div class="form-text text-muted">
                            <i class="ti ti-at me-1"></i>Masukkan username akun sosmed (Instagram / TikTok / Facebook) yang Anda pakai untuk posting.
                        </div>
                        @error('username_sosmed')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field 3: Divisi (Otomatis Disesuaikan / Dapat Disesuaikan) -->
                    <div class="mb-3">
                        <label for="divisi" class="form-label">
                            Divisi <span class="required-star">*</span>
                        </label>
                        <select class="form-select @error('divisi') is-invalid @enderror" id="divisi" name="divisi" required>
                            <option value="" disabled selected>-- Pilih / Otomatis Terisi --</option>
                            @foreach ($divisiList as $div)
                                <option value="{{ $div }}" {{ old('divisi') == $div ? 'selected' : '' }}>
                                    {{ $div }}
                                </option>
                            @endforeach
                        </select>
                        @error('divisi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field 4: Pilih Sosmed Platform -->
                    <div class="mb-3">
                        <label for="sosmed_platform" class="form-label">
                            Pilih Sosmed <span class="required-star">*</span>
                        </label>
                        <select class="form-select @error('sosmed_platform') is-invalid @enderror" id="sosmed_platform" name="sosmed_platform" required>
                            <option value="" disabled selected>-- Pilih Platform Sosmed --</option>
                            @foreach ($sosmedPlatforms as $platform)
                                <option value="{{ $platform }}" {{ old('sosmed_platform') == $platform ? 'selected' : '' }}>
                                    {{ $platform }}
                                </option>
                            @endforeach
                        </select>
                        @error('sosmed_platform')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field 5: Dropdown Pilihan Tugas & Dynamic Link Button -->
                    <div class="mb-3">
                        <label for="pilihan_tugas" class="form-label">
                            Pilihan Tugas Sosmed Hari Ini <span class="required-star">*</span>
                        </label>
                        <select class="form-select @error('pilihan_tugas') is-invalid @enderror" id="pilihan_tugas" name="pilihan_tugas" onchange="handleTaskChange()" required>
                            <option value="" selected disabled>-- Pilih Tugas Yang Dikerjakan --</option>
                            @if(!empty($formattedTasks))
                                @foreach ($formattedTasks as $task)
                                    <option value="{{ $task['title'] }}" data-original-text="{{ $task['title'] }}" data-link="{{ $task['link'] }}" {{ old('pilihan_tugas') == $task['title'] ? 'selected' : '' }}>
                                        {{ $task['title'] }}
                                    </option>
                                @endforeach
                            @else
                                <option value="Tugas 1: Postingan Sosmed Hari Ini" data-original-text="Tugas 1: Postingan Sosmed Hari Ini" data-link="" {{ old('pilihan_tugas') == 'Tugas 1: Postingan Sosmed Hari Ini' ? 'selected' : '' }}>
                                    Tugas 1: Postingan Sosmed Hari Ini
                                </option>
                                <option value="Tugas 2: Video Content & Story" data-original-text="Tugas 2: Video Content & Story" data-link="" {{ old('pilihan_tugas') == 'Tugas 2: Video Content & Story' ? 'selected' : '' }}>
                                    Tugas 2: Video Content & Story
                                </option>
                            @endif
                        </select>
                        <input type="hidden" id="tugas_link" name="tugas_link" value="{{ old('tugas_link') }}">

                        <!-- Dynamic Link Box -->
                        <div id="taskLinkBox" class="task-link-box d-none">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <span class="fw-bold text-dark d-block">
                                        <i class="ti ti-link text-primary me-1"></i>Link Tugas Siap Digunakan
                                    </span>
                                    <small class="text-muted" id="taskLinkPreviewText">-</small>
                                </div>
                                <a id="btnStartTask" href="#" target="_blank" class="btn-start-task">
                                    <i class="ti ti-external-link fs-4"></i> Klik link ini untuk memulai tugas
                                </a>
                            </div>
                        </div>

                        <div id="allTasksCompletedBox" class="task-completed-badge d-none">
                            <i class="ti ti-circle-check fs-4 text-success"></i>
                            <div>
                                <strong>Hebat! Anda sudah menyelesaikan semua tugas sosmed untuk hari ini.</strong>
                                <span class="d-block small text-muted">Terima kasih atas kontribusi Anda! Tugas baru akan muncul otomatis esok hari.</span>
                            </div>
                        </div>

                        @error('pilihan_tugas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field 6: Multi-Upload Foto (3 Foto: Like, Komen, Share) -->
                    <div class="mb-4">
                        <label for="photos" class="form-label">
                            Upload Foto Bukti Posting (Minimal 1 Foto, Disarankan 3 Foto) <span class="required-star">*</span>
                        </label>
                        
                        <!-- Note Foto Like, Komen, Share -->
                        <div class="photo-note-box">
                            <div class="note-title">
                                <i class="ti ti-camera me-1"></i> Catatan Upload Bukti (3 Foto):
                            </div>
                            <ul>
                                <li><strong>Foto 1:</strong> Screenshot Bukti <strong>Like</strong></li>
                                <li><strong>Foto 2:</strong> Screenshot Bukti <strong>Komen</strong></li>
                                <li><strong>Foto 3:</strong> Screenshot Bukti <strong>Share / Repost</strong></li>
                            </ul>
                        </div>

                        <input type="file" 
                               class="form-control @error('photos') is-invalid @enderror" 
                               id="photos" 
                               name="photos[]" 
                               accept="image/*" 
                               multiple 
                               required
                               onchange="previewImages(event)">
                        <div class="form-text text-muted">
                            <i class="ti ti-paperclip me-1"></i>Anda dapat memilih 3 foto sekaligus dari galeri Anda. Format: JPG, PNG, WEBP (Max 10MB per foto).
                        </div>
                        @error('photos')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <!-- Preview Container dengan Label Note -->
                        <div class="preview-container" id="previewContainer"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-submit" id="btnSubmit">
                        <i class="ti ti-send me-2"></i>Simpan & Kirim Bukti Posting
                    </button>
                </form>

                <!-- SECTION PALING BAWAH: CEK POIN & KLASEMEN REALTIME (Sensored *** Name + Pagination) -->
                <div class="public-leaderboard-section" id="cekPoinSection">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <div>
                            <div class="public-leaderboard-title">
                                <i class="ti ti-trophy text-warning fs-2"></i>Cek Poin & Klasemen Realtime Karyawan
                            </div>
                            <p class="text-muted small mb-0">
                                Memantau poin & peringkat 90 karyawan secara realtime (Nama disamarkan <code>***</code> demi privasi).
                            </p>
                        </div>
                        <span class="badge bg-light-primary text-primary border px-3 py-2 fw-bold">
                            <i class="ti ti-users me-1"></i>90 Karyawan
                        </span>
                    </div>

                    <!-- Controls Row: Search Input + List Page Size Select (10, 20, 50, Semua) -->
                    <div class="row g-2 align-items-center mb-3">
                        <div class="col-md-7 col-sm-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
                                <input type="text" 
                                       class="form-control" 
                                       id="searchPublicLeaderboard" 
                                       placeholder="Ketik nama Anda untuk mencari poin & peringkat kamu..." 
                                       onkeyup="filterPublicLeaderboard()">
                            </div>
                        </div>
                        <div class="col-md-5 col-sm-6 text-sm-end d-flex align-items-center justify-content-sm-end gap-2">
                            <label for="pageSizeSelect" class="form-label text-muted mb-0 small text-nowrap">Tampilkan:</label>
                            <select id="pageSizeSelect" class="form-select form-select-sm w-auto d-inline-block fw-semibold" onchange="changePageSize()">
                                <option value="10" selected>10 Baris</option>
                                <option value="20">20 Baris</option>
                                <option value="50">50 Baris</option>
                                <option value="-1">Semua (All)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tabel Klasemen Poin Sensored *** -->
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0" id="tablePublicLeaderboard">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 65px;" class="text-center">Rank</th>
                                    <th>Nama Karyawan</th>
                                    <th>Username</th>
                                    <th>Divisi</th>
                                    <th class="text-center">Total Poin (ACC)</th>
                                    <th class="text-center">Hari Ini</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($publicLeaderboard as $userItem)
                                    <tr data-nama-raw="{{ strtolower($userItem['nama_raw']) }}">
                                        <td class="text-center">
                                            @if($userItem['rank'] === 1)
                                                <span class="badge bg-warning text-dark font-monospace">🥇 #1</span>
                                            @elseif($userItem['rank'] === 2)
                                                <span class="badge bg-secondary text-white font-monospace">🥈 #2</span>
                                            @elseif($userItem['rank'] === 3)
                                                <span class="badge bg-dark text-white font-monospace" style="background-color: #cd7f32 !important;">🥉 #3</span>
                                            @elseif($userItem['rank'] <= 5)
                                                <span class="badge bg-light-primary text-primary font-monospace fw-bold">⭐ #{{ $userItem['rank'] }}</span>
                                            @else
                                                <span class="badge bg-light text-muted font-monospace fw-normal">#{{ $userItem['rank'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-dark font-monospace fs-6">{{ $userItem['nama_masked'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-secondary text-muted font-monospace fs-7">{{ $userItem['username_masked'] }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light-info text-info fs-7">{{ $userItem['divisi'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($userItem['total_points'] > 0)
                                                <span class="badge bg-primary fs-7 px-2 py-1">
                                                    <i class="ti ti-star me-1"></i>{{ $userItem['total_points'] }} Poin
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted fs-7">0 Poin</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($userItem['submitted_today'])
                                                <span class="badge bg-success"><i class="ti ti-check me-1"></i>Kirim Hari Ini</span>
                                            @else
                                                <span class="badge bg-light text-muted border"><i class="ti ti-clock me-1"></i>Belum Kirim</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data klasemen poin.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls & Next/Prev Navigation -->
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 pt-2">
                        <div class="small text-muted" id="paginationInfoText">
                            Menampilkan 1 - 10 dari 90 karyawan
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnPrevPage" onclick="prevPage()">
                                <i class="ti ti-chevron-left me-1"></i>Prev
                            </button>
                            <div id="pageNumbersContainer" class="d-inline-flex gap-1"></div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnNextPage" onclick="nextPage()">
                                Next<i class="ti ti-chevron-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="footer-credit">
        &copy; {{ date('Y') }} e-Sistem Monitoring Transaksi — Form Sosmed Karyawan
    </div>
</div>

<script src="{{ asset('sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    // Master data karyawan & Map Task Hari Ini dari server
    const masterKaryawan = @json($karyawanList);
    const todayTaskMap = @json($todayTaskMap);

    const namaInput = document.getElementById('nama');
    const divisiSelect = document.getElementById('divisi');
    const autoDivisiNotice = document.getElementById('autoDivisiNotice');
    const autoDivisiText = document.getElementById('autoDivisiText');
    const pilihanTugasSelect = document.getElementById('pilihan_tugas');
    const allTasksCompletedBox = document.getElementById('allTasksCompletedBox');

    // State Pagination Tabel Publik
    let currentPage = 1;
    let pageSize = 10;
    let filteredRows = [];

    // Auto Fill Divisi & Lock Completed Tasks Today
    if (namaInput) {
        namaInput.addEventListener('input', function() {
            const val = this.value.trim().toLowerCase();
            if (!val) {
                if (autoDivisiNotice) autoDivisiNotice.classList.add('d-none');
                resetTaskDropdownOptions();
                return;
            }

            // Cari match persis atau match pertama
            const match = masterKaryawan.find(k => k.nama.toLowerCase() === val) 
                       || masterKaryawan.find(k => k.nama.toLowerCase().includes(val));

            if (match && divisiSelect) {
                // Set divisi select
                for (let i = 0; i < divisiSelect.options.length; i++) {
                    if (divisiSelect.options[i].value.toLowerCase() === match.divisi.toLowerCase()) {
                        divisiSelect.selectedIndex = i;
                        break;
                    }
                }
                if (autoDivisiNotice && autoDivisiText) {
                    autoDivisiText.innerText = `Divisi disesuaikan otomatis: ${match.divisi}`;
                    autoDivisiNotice.classList.remove('d-none');
                }
            } else {
                if (autoDivisiNotice) autoDivisiNotice.classList.add('d-none');
            }

            // Cek task yang sudah dikerjakan karyawan ini HARI INI
            checkEmployeeTodayTasks(val);

            // Filter tabel klasemen publik ke nama ini jika dicari
            const searchInput = document.getElementById('searchPublicLeaderboard');
            if (searchInput) {
                searchInput.value = val;
                filterPublicLeaderboard();
            }
        });

        // Trigger jika ada old value
        if (namaInput.value) {
            namaInput.dispatchEvent(new Event('input'));
        }
    }

    function checkEmployeeTodayTasks(inputNamaVal) {
        if (!pilihanTugasSelect) return;

        const valLower = inputNamaVal.trim().toLowerCase();
        const firstWordLower = valLower.split(/\s+/)[0] || '';

        const completedTasksToday = todayTaskMap[valLower] || todayTaskMap[firstWordLower] || [];

        let enabledOptionsCount = 0;
        let totalTaskOptionsCount = 0;

        for (let i = 0; i < pilihanTugasSelect.options.length; i++) {
            const opt = pilihanTugasSelect.options[i];
            if (!opt.value) continue;

            totalTaskOptionsCount++;
            const originalText = opt.getAttribute('data-original-text') || opt.value;
            const isCompleted = completedTasksToday.some(t => t.toLowerCase() === opt.value.toLowerCase() || t.toLowerCase() === originalText.toLowerCase());

            if (isCompleted) {
                opt.disabled = true;
                opt.innerText = `${originalText} — (✓ Kamu sudah menyelesaikan task ini, selesaikan yang lain)`;
                opt.style.color = '#bfbfbf';
                opt.style.backgroundColor = '#f5f5f5';

                if (opt.selected) {
                    pilihanTugasSelect.selectedIndex = 0;
                    handleTaskChange();
                }
            } else {
                opt.disabled = false;
                opt.innerText = originalText;
                opt.style.color = '#262626';
                opt.style.backgroundColor = '#ffffff';
                enabledOptionsCount++;
            }
        }

        if (allTasksCompletedBox) {
            if (completedTasksToday.length > 0 && enabledOptionsCount === 0 && totalTaskOptionsCount > 0) {
                allTasksCompletedBox.classList.remove('d-none');
            } else {
                allTasksCompletedBox.classList.add('d-none');
            }
        }
    }

    function resetTaskDropdownOptions() {
        if (!pilihanTugasSelect) return;
        for (let i = 0; i < pilihanTugasSelect.options.length; i++) {
            const opt = pilihanTugasSelect.options[i];
            if (!opt.value) continue;
            const originalText = opt.getAttribute('data-original-text') || opt.value;
            opt.disabled = false;
            opt.innerText = originalText;
            opt.style.color = '#262626';
            opt.style.backgroundColor = '#ffffff';
        }
        if (allTasksCompletedBox) allTasksCompletedBox.classList.add('d-none');
    }

    function handleTaskChange() {
        const select = document.getElementById('pilihan_tugas');
        const linkBox = document.getElementById('taskLinkBox');
        const linkBtn = document.getElementById('btnStartTask');
        const linkPreview = document.getElementById('taskLinkPreviewText');
        const hiddenLinkInput = document.getElementById('tugas_link');

        if (!select || !linkBox || !linkBtn) return;

        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption && selectedOption.disabled) {
            Swal.fire({
                icon: 'warning',
                title: 'Tugas Sudah Selesai',
                text: 'Kamu sudah menyelesaikan tugas ini untuk hari ini, silakan selesaikan tugas lain yang belum dikerjakan!',
                confirmColor: '#1890ff'
            });
            select.selectedIndex = 0;
            linkBox.classList.add('d-none');
            return;
        }

        const linkUrl = selectedOption ? selectedOption.getAttribute('data-link') : '';

        if (linkUrl && linkUrl.trim() !== '') {
            const cleanUrl = linkUrl.trim();
            linkBtn.href = cleanUrl;
            if (hiddenLinkInput) hiddenLinkInput.value = cleanUrl;
            if (linkPreview) linkPreview.innerText = cleanUrl;
            linkBox.classList.remove('d-none');
        } else {
            if (hiddenLinkInput) hiddenLinkInput.value = '';
            linkBox.classList.add('d-none');
        }
    }

    function previewImages(event) {
        const container = document.getElementById('previewContainer');
        container.innerHTML = '';
        const files = event.target.files;

        const noteLabels = [
            '1. Bukti Like',
            '2. Bukti Komen',
            '3. Bukti Share',
        ];

        if (files) {
            Array.from(files).forEach((file, idx) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const card = document.createElement('div');
                        card.className = 'preview-card';
                        
                        const labelText = noteLabels[idx] ? noteLabels[idx] : `Foto ${idx + 1}`;

                        card.innerHTML = `
                            <img src="${e.target.result}" alt="Preview Foto ${idx + 1}">
                            <div class="badge-note" title="${labelText}">${labelText}</div>
                        `;
                        container.appendChild(card);
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    // JS Pagination Logic untuk Klasemen Publik
    function initPagination() {
        const table = document.getElementById('tablePublicLeaderboard');
        if (!table) return;
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const allRows = Array.from(tbody.querySelectorAll('tr'));
        const searchInput = document.getElementById('searchPublicLeaderboard');
        const filterText = searchInput ? searchInput.value.trim().toLowerCase() : '';

        filteredRows = allRows.filter(row => {
            const rawNama = row.getAttribute('data-nama-raw') || '';
            const rowText = row.innerText.toLowerCase();
            return filterText === '' || rawNama.includes(filterText) || rowText.includes(filterText);
        });

        const totalItems = filteredRows.length;
        const effectivePageSize = pageSize < 0 ? totalItems : pageSize;
        const totalPages = effectivePageSize > 0 ? Math.ceil(totalItems / effectivePageSize) : 1;

        if (currentPage > totalPages) currentPage = totalPages || 1;

        const startIdx = (currentPage - 1) * effectivePageSize;
        const endIdx = pageSize < 0 ? totalItems : Math.min(startIdx + effectivePageSize, totalItems);

        // Hide all rows first
        allRows.forEach(row => row.style.display = 'none');

        // Show active page rows
        for (let i = startIdx; i < endIdx; i++) {
            if (filteredRows[i]) {
                filteredRows[i].style.display = '';
            }
        }

        // Update Page Info
        const pageInfo = document.getElementById('paginationInfoText');
        if (pageInfo) {
            if (totalItems === 0) {
                pageInfo.innerText = 'Menampilkan 0 data karyawan';
            } else {
                pageInfo.innerText = `Menampilkan ${startIdx + 1} - ${endIdx} dari ${totalItems} karyawan (Halaman ${currentPage} dari ${totalPages})`;
            }
        }

        // Update Prev / Next Buttons
        const btnPrev = document.getElementById('btnPrevPage');
        const btnNext = document.getElementById('btnNextPage');
        if (btnPrev) btnPrev.disabled = (currentPage <= 1);
        if (btnNext) btnNext.disabled = (currentPage >= totalPages || totalItems === 0);

        renderPageNumbers(totalPages);
    }

    function renderPageNumbers(totalPages) {
        const container = document.getElementById('pageNumbersContainer');
        if (!container) return;
        container.innerHTML = '';

        if (totalPages <= 1) return;

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn btn-sm ${i === currentPage ? 'btn-primary fw-bold' : 'btn-outline-secondary'}`;
            btn.innerText = i;
            btn.onclick = function() {
                currentPage = i;
                initPagination();
            };
            container.appendChild(btn);
        }
    }

    function changePageSize() {
        const select = document.getElementById('pageSizeSelect');
        if (!select) return;
        pageSize = parseInt(select.value);
        currentPage = 1;
        initPagination();
    }

    function prevPage() {
        if (currentPage > 1) {
            currentPage--;
            initPagination();
        }
    }

    function nextPage() {
        const effectivePageSize = pageSize < 0 ? filteredRows.length : pageSize;
        const totalPages = Math.ceil(filteredRows.length / effectivePageSize);
        if (currentPage < totalPages) {
            currentPage++;
            initPagination();
        }
    }

    function filterPublicLeaderboard() {
        currentPage = 1;
        initPagination();
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleTaskChange();
        initPagination();
    });
</script>

</body>
</html>
