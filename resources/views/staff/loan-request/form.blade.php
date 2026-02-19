@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Peminjaman Barang</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">{{ $form->title }}</h2>
                    @if ($form->description)
                        <p class="text-muted mb-0">{{ $form->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Form Peminjaman Barang</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div id="loanRequestNotedAlert" class="alert alert-info d-none">
                    <strong>Noted:</strong> Pengajuan Anda sudah tercatat. Jika ada revisi, klik <strong>Pengajuan Ulang</strong>.
                </div>

                <form id="loanRequestForm" method="POST" action="{{ route('loan-request.form.submit', $form->token) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Server</label>
                        <input type="text" name="nama_server" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan</label>
                        <textarea name="keperluan" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="nomor_hp" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barang yang Dipinjam</label>
                        <input type="text" name="barang_dipinjam" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pinjam</label>
                        <input type="datetime-local" name="tanggal_pinjam" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Balikin</label>
                        <input type="datetime-local" name="tanggal_kembali" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Admin (Tujuan)</label>
                        <input type="text" class="form-control" value="{{ config('whatsapp.admin_numbers')[0] ?? '' }}" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Pengisi</label>
                        <input type="text" name="wa_pengisi" class="form-control" placeholder="628xxxxxxxxxx" required />
                    </div>
                    <button type="submit" id="loanRequestSubmitBtn" class="btn btn-primary w-100">Kirim Pengajuan</button>
                    <button type="button" id="loanRequestResubmitBtn" class="btn btn-warning w-100 d-none mt-2">Pengajuan Ulang</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('loanRequestForm');
        if (!form) return;

        const submitBtn = document.getElementById('loanRequestSubmitBtn');
        const resubmitBtn = document.getElementById('loanRequestResubmitBtn');
        const notedAlert = document.getElementById('loanRequestNotedAlert');
        const wasSubmitted = @json((bool) session('loan_request_submitted'));

        function setFormEnabled(enabled) {
            form.querySelectorAll('input, select, textarea, button').forEach((el) => {
                if (el === resubmitBtn || el === submitBtn) return;
                el.disabled = !enabled;
            });
        }

        function applySubmittedState() {
            submitBtn.classList.add('d-none');
            resubmitBtn.classList.remove('d-none');
            notedAlert.classList.remove('d-none');
            setFormEnabled(false);
        }

        function applyInitialState() {
            submitBtn.classList.remove('d-none');
            resubmitBtn.classList.add('d-none');
            notedAlert.classList.add('d-none');
            setFormEnabled(true);
        }

        if (wasSubmitted) {
            applySubmittedState();
        }

        form.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mengirim...';
        });

        resubmitBtn.addEventListener('click', function () {
            form.reset();
            applyInitialState();
            submitBtn.disabled = false;
            submitBtn.textContent = 'Kirim Pengajuan';
        });
    })();
</script>
@endpush
