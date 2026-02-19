@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Form Deposit</li>
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
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Isi Form Deposit</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('deposit.form.submit', $form->token) }}" id="depositRequestForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="nama_supplier" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal</label>
                        <input type="number" name="nominal" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deposit / Hutang</label>
                        <select name="jenis_transaksi" class="form-select" required>
                            <option value="deposit">Deposit</option>
                            <option value="hutang">Hutang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">BANK</label>
                        <input type="text" name="bank" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Server</label>
                        <input type="text" name="server" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No-Rek</label>
                        <input type="text" name="no_rek" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Rekening</label>
                        <input type="text" name="nama_rekening" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reply Tiket</label>
                        <textarea name="reply_tiket" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam</label>
                        <input type="time" name="jam" class="form-control" required />
                    </div>

                    <div id="submitNoted" class="alert alert-info mt-2 d-none">
                        Noted: Anda telah melakukan pengajuan, jika anda ingin melakukan pengajuan ulang klik button "Pengajuan Ulang".
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="btnSubmit">Kirim Pengajuan</button>
                    <button type="button" class="btn btn-warning w-100 d-none mt-2" id="btnResubmit">Pengajuan Ulang</button>
                </form>

                @if (session('deposit_reply_text'))
                    <hr class="my-4">
                    <h6 class="mb-2">Reply Jawaban (siap copy)</h6>
                    <textarea id="depositReplyText" class="form-control" rows="9" readonly>{{ session('deposit_reply_text') }}</textarea>
                    <button type="button" class="btn btn-success w-100 mt-2" id="copyDepositReplyBtn">Copy Semua</button>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('depositRequestForm');
        const submitBtn = document.getElementById('btnSubmit');
        const resubmitBtn = document.getElementById('btnResubmit');
        const submitNoted = document.getElementById('submitNoted');
        const wasSubmitted = {{ session('deposit_submitted') ? 'true' : 'false' }};

        function toggleSubmittedState(submitted) {
            if (!form || !submitBtn || !resubmitBtn || !submitNoted) return;

            if (submitted) {
                submitBtn.classList.add('d-none');
                resubmitBtn.classList.remove('d-none');
                submitNoted.classList.remove('d-none');
            } else {
                submitBtn.classList.remove('d-none');
                resubmitBtn.classList.add('d-none');
                submitNoted.classList.add('d-none');
            }

            form.querySelectorAll('input, select, textarea').forEach((el) => {
                if (el.id === 'depositReplyText') return;
                if (el.name === '_token') return;
                if (el.id === 'btnSubmit' || el.id === 'btnResubmit') return;
                el.disabled = submitted;
            });
        }

        if (form) {
            form.addEventListener('submit', function () {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Mengirim...';
                }
            });
        }

        if (resubmitBtn) {
            resubmitBtn.addEventListener('click', function () {
                if (!form) return;
                form.reset();
                toggleSubmittedState(false);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Kirim Pengajuan';
                }
            });
        }

        toggleSubmittedState(wasSubmitted);

        const btn = document.getElementById('copyDepositReplyBtn');
        const text = document.getElementById('depositReplyText');
        if (!btn || !text) return;

        btn.addEventListener('click', async function () {
            try {
                await navigator.clipboard.writeText(text.value);
                btn.textContent = 'Berhasil di-copy';
                setTimeout(() => btn.textContent = 'Copy Semua', 1500);
            } catch (e) {
                text.select();
                document.execCommand('copy');
                btn.textContent = 'Berhasil di-copy';
                setTimeout(() => btn.textContent = 'Copy Semua', 1500);
            }
        });
    })();
</script>
@endpush
