@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-scissors me-2"></i>Buat Potong Data Baru
                </h2>
                <div class="text-muted mt-1">Backup dan hapus data yang lebih lama dari tanggal tertentu</div>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-xl">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="ti ti-alert-triangle me-2"></i>
                        <strong>Validasi Gagal!</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <!-- Info Card -->
                <div class="alert alert-info" role="alert">
                    <div class="d-flex">
                        <div>
                            <i class="ti ti-info-circle me-2"></i>
                            <strong>Informasi Penting:</strong>
                        </div>
                    </div>
                    <ul class="mb-0 mt-2">
                        <li>Data yang lebih lama dari tanggal yang dipilih akan <strong>DIHAPUS PERMANEN</strong></li>
                        <li>Disarankan untuk membuat <strong>backup database</strong> sebelum menghapus data</li>
                        <li>Proses ini tidak bisa dibatalkan setelah dikonfirmasi</li>
                        <li>Hanya tersimpan data <strong>1-2 bulan terakhir</strong> setelah proses selesai</li>
                    </ul>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('data-cutting.store') }}" method="POST" id="cuttingForm">
                            @csrf

                            <!-- Tanggal Potong -->
                            <div class="mb-4">
                                <label class="form-label" for="cut_date">Tanggal Potong Data <span class="text-danger">*</span></label>
                                <div class="input-icon">
                                    <input type="date" class="form-control @error('cut_date') is-invalid @enderror" 
                                        id="cut_date" name="cut_date" value="{{ old('cut_date', now()->subMonths(2)->format('Y-m-d')) }}" required>
                                    <span class="input-icon-addon">
                                        <i class="ti ti-calendar"></i>
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Data sebelum tanggal ini akan dihapus. Rekomendasi: 2 bulan yang lalu
                                </small>
                                @error('cut_date')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Data Preview -->
                            <div class="mb-4">
                                <label class="form-label">Preview Data yang Akan Dihapus</label>
                                <div class="card bg-light">
                                    <div class="card-body" id="previewContainer">
                                        <p class="text-muted text-center mb-0">
                                            <i class="ti ti-loader-3 animate-spin me-2"></i>Loading...
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Backup Checkbox -->
                            <div class="mb-4">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" id="backup_database" name="backup_database" value="1" checked>
                                    <span class="form-check-label">
                                        <strong>Buat Backup Database Sebelum Menghapus</strong>
                                        <i class="ti ti-help-circle" data-bs-toggle="tooltip" title="Sangat disarankan untuk membuat backup terlebih dahulu"></i>
                                    </span>
                                </label>
                                <small class="text-muted d-block mt-2">
                                    Backup akan disimpan dan bisa diunduh kapan saja jika diperlukan restore
                                </small>
                            </div>

                            <!-- Catatan -->
                            <div class="mb-4">
                                <label class="form-label" for="notes">Catatan (Opsional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                    id="notes" name="notes" rows="3" placeholder="Tuliskan alasan atau catatan penting...">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Konfirmasi -->
                            <div class="mb-4">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" id="confirm" required>
                                    <span class="form-check-label">
                                        Saya memahami bahwa data akan <strong>DIHAPUS PERMANEN</strong> dan tidak bisa dikembalikan
                                    </span>
                                </label>
                            </div>

                            <!-- Actions -->
                            <div class="form-footer">
                                <a href="{{ route('data-cutting.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-danger" id="submitBtn" disabled>
                                    <i class="ti ti-trash me-2"></i>Proses Potong Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cutDateInput = document.getElementById('cut_date');
    const previewContainer = document.getElementById('previewContainer');
    const confirmCheckbox = document.getElementById('confirm');
    const submitBtn = document.getElementById('submitBtn');

    // Load preview saat halaman dimuat
    loadPreview();

    // Update preview saat tanggal berubah
    cutDateInput.addEventListener('change', loadPreview);

    // Enable submit button jika checkbox diklik
    confirmCheckbox.addEventListener('change', function() {
        submitBtn.disabled = !this.checked;
    });

    function loadPreview() {
        const cutDate = cutDateInput.value;
        
        if (!cutDate) {
            previewContainer.innerHTML = '<p class="text-muted text-center mb-0">Pilih tanggal terlebih dahulu</p>';
            return;
        }

        previewContainer.innerHTML = '<p class="text-muted text-center mb-0"><i class="ti ti-loader-3 animate-spin me-2"></i>Loading...</p>';

        fetch('{{ route("data-cutting.preview") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
            },
            body: JSON.stringify({ cut_date: cutDate })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <div class="row">
                        <div class="col-12 mb-3">
                            <p class="mb-2"><strong>Tanggal Cutoff:</strong> <span class="badge bg-warning">${formatDate(data.cut_date)}</span></p>
                            <p class="mb-0"><strong>Total Record yang Akan Dihapus:</strong> <span class="badge bg-danger fs-5">${data.total_records}</span></p>
                        </div>
                    </div>
                    <hr>
                    <strong class="d-block mb-3">Breakdown per Tabel:</strong>
                    <div class="row">
                `;

                Object.entries(data.stats).forEach(([key, value]) => {
                    if (value > 0) {
                        const labels = {
                            'transaksis': 'Transaksi',
                            'deposits': 'Deposit',
                            'reimburse': 'Reimburse',
                            'minusans': 'Minusan',
                            'imports': 'Import',
                            'tag_nomor_pasca_bayars': 'Tag Nomor Pasca Bayar',
                            'tag_pln_internets': 'Tag PLN Internet',
                            'tag_lainnyas': 'Tag Lainnya'
                        };

                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card bg-white border">
                                    <div class="card-body p-2">
                                        <small class="text-muted">${labels[key] || key}</small><br>
                                        <strong class="text-danger">${value} record</strong>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });

                html += '</div>';
                previewContainer.innerHTML = html;
            }
        })
        .catch(error => {
            previewContainer.innerHTML = '<p class="text-danger text-center mb-0">Error loading preview</p>';
            console.error('Error:', error);
        });
    }

    function formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }
});
</script>
@endsection
