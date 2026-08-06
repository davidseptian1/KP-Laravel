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
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
            min-height: 100vh;
            font-family: 'Public Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 10px;
        }
        .form-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: none;
            overflow: hidden;
            width: 100%;
            max-width: 680px;
            margin: 0 auto;
        }
        .card-header-custom {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
            color: #ffffff;
            padding: 28px 30px;
            text-align: center;
        }
        .card-header-custom h3 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 6px;
            font-size: 1.5rem;
        }
        .card-header-custom p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 0.95rem;
            margin-bottom: 0;
        }
        .card-body-custom {
            padding: 30px;
        }
        .info-fee-box {
            background-color: #e6f7ff;
            border: 1px solid #91d5ff;
            border-left: 4px solid #1890ff;
            border-radius: 8px;
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
            font-size: 0.9rem;
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
        .btn-submit {
            background: linear-gradient(135deg, #1890ff 0%, #096dd9 100%);
            border: none;
            color: #ffffff;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(24, 144, 255, 0.3);
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(24, 144, 255, 0.4);
            color: #ffffff;
        }
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 14px;
        }
        .preview-item {
            position: relative;
            width: 90px;
            height: 90px;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #d9d9d9;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .preview-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .platform-option-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .footer-credit {
            text-align: center;
            margin-top: 20px;
            font-size: 0.8rem;
            color: #8c8c8c;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">
        <div class="card-header-custom">
            <div class="mb-2">
                <i class="ti ti-brand-instagram fs-1 me-1"></i>
                <i class="ti ti-brand-tiktok fs-1 me-1"></i>
                <i class="ti ti-brand-facebook fs-1"></i>
            </div>
            <h3>Form Upload Bukti Posting Sosmed</h3>
            <p>Silakan isi data postingan Anda untuk pencatatan fee reward</p>
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

                <!-- Info Note khusus fee & nama -->
                <div class="info-fee-box">
                    <div class="d-flex align-items-start">
                        <i class="ti ti-info-circle fs-3 me-2 text-primary flex-shrink-0 mt-1"></i>
                        <div>
                            <strong class="d-block text-primary mb-1">PENTING - Ketentuan Pengisian Nama / Username Sosmed:</strong>
                            <p>
                                <strong>Nama Panggilan / Username Sosmed WAJIB SAMA</strong> di setiap pengisian form dan tidak boleh berbeda-beda!
                                Hal ini sangat penting untuk akurasi pencatatan akumulasi fee yang akan didapatkan Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('sosmed.form.submit') }}" method="POST" enctype="multipart/form-data" id="sosmedForm">
                    @csrf

                    <!-- Field Nama -->
                    <div class="mb-3">
                        <label for="nama" class="form-label">
                            Nama Panggilan / Username Sosmed <span class="required-star">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('nama') is-invalid @enderror" 
                               id="nama" 
                               name="nama" 
                               value="{{ old('nama') }}" 
                               placeholder="Masukkan nama panggilan / username sosmed Anda (misal: Budi / Budi_ig)" 
                               required>
                        <div class="form-text text-muted">
                            <i class="ti ti-bulb me-1"></i>Gunakan kata pertama atau username sosmed yang konsisten pada penginputan selanjutnya.
                        </div>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Field Divisi -->
                    <div class="mb-3">
                        <label for="divisi" class="form-label">
                            Divisi <span class="required-star">*</span>
                        </label>
                        <select class="form-select @error('divisi') is-invalid @enderror" id="divisi" name="divisi" required>
                            <option value="" disabled selected>-- Pilih Divisi --</option>
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

                    <!-- Field Sosmed Platform -->
                    <div class="mb-3">
                        <label for="sosmed_platform" class="form-label">
                            Platform Sosmed <span class="required-star">*</span>
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

                    <!-- Field Multi-Upload Foto -->
                    <div class="mb-4">
                        <label for="photos" class="form-label">
                            Upload Foto Bukti Posting (Bisa > 1 Foto) <span class="required-star">*</span>
                        </label>
                        <input type="file" 
                               class="form-control @error('photos') is-invalid @enderror" 
                               id="photos" 
                               name="photos[]" 
                               accept="image/*" 
                               multiple 
                               required
                               onchange="previewImages(event)">
                        <div class="form-text text-muted">
                            <i class="ti ti-paperclip me-1"></i>Anda dapat memilih lebih dari satu foto sekaligus. Format: JPG, PNG, WEBP (Max 10MB/foto).
                        </div>
                        @error('photos')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <!-- Preview Container -->
                        <div class="preview-container" id="previewContainer"></div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-submit" id="btnSubmit">
                        <i class="ti ti-send me-2"></i>Simpan & Kirim Bukti Posting
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="footer-credit">
        &copy; {{ date('Y') }} e-Sistem Monitoring Transaksi — Form Sosmed User
    </div>
</div>

<script src="{{ asset('sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    function previewImages(event) {
        const container = document.getElementById('previewContainer');
        container.innerHTML = '';
        const files = event.target.files;

        if (files) {
            Array.from(files).forEach(file => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const item = document.createElement('div');
                        item.className = 'preview-item';
                        item.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                        container.appendChild(item);
                    }
                    reader.readAsDataURL(file);
                }
            });
        }
    }

    document.getElementById('sosmedForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('btnSubmit');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Mengirim Data...';
        }
    });
</script>

</body>
</html>
