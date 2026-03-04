@extends('layouts/app')

@section('content')

<style>
    textarea.js-auto-resize-textarea {
        overflow-y: auto;
        resize: none;
        min-height: 70px;
        max-height: 260px;
    }

    .searchable-select-wrap .js-select-search {
        margin-bottom: 6px;
    }
</style>

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
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('deposit_reply_text'))
                    <div class="alert alert-warning">
                        Salin data berikut dan kirimkan ke WhatsApp admin jika ingin diproses.
                    </div>
                    <textarea id="depositReplyText" class="form-control" rows="9" readonly>{{ session('deposit_reply_text') }}</textarea>
                    <button type="button" class="btn btn-success w-100 mt-2" id="copyDepositReplyBtn">Copy Semua</button>
                    <hr class="my-3">
                @endif

                <form method="POST" action="{{ route('deposit.form.submit', $form->token) }}" id="depositRequestForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <div class="searchable-select-wrap">
                            <input type="text" class="form-control js-select-search" data-target="supplierPublicSelect" placeholder="Cari supplier...">
                            <select name="nama_supplier" id="supplierPublicSelect" class="form-select js-searchable-select" required>
                                <option value="">Pilih Supplier</option>
                                @foreach (($suppliers ?? collect()) as $supplier)
                                    <option value="{{ $supplier }}" {{ old('nama_supplier') === $supplier ? 'selected' : '' }}>{{ $supplier }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (($suppliers ?? collect())->isEmpty())
                            <small class="text-danger">Belum ada supplier. Minta admin tambah supplier di menu Supplier Manajemen.</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal</label>
                        <input type="text" name="nominal" class="form-control" inputmode="numeric" placeholder="Contoh: 1.250.000,-" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deposit / Hutang</label>
                        <div class="searchable-select-wrap">
                            <input type="text" class="form-control js-select-search" data-target="jenisPublicSelect" placeholder="Cari jenis...">
                            <select name="jenis_transaksi" id="jenisPublicSelect" class="form-select js-searchable-select" required>
                                <option value="deposit">Deposit</option>
                                <option value="hutang">Hutang</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">BANK</label>
                        <div class="searchable-select-wrap">
                            <input type="text" class="form-control js-select-search" data-target="bankPublicSelect" placeholder="Cari bank...">
                            <select name="bank" id="bankPublicSelect" class="form-select js-searchable-select" required>
                                <option value="">Pilih Bank</option>
                                @foreach (($banks ?? collect()) as $bank)
                                    <option value="{{ $bank }}" {{ old('bank') === $bank ? 'selected' : '' }}>{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Server</label>
                        <div class="searchable-select-wrap">
                            <input type="text" class="form-control js-select-search" data-target="serverPublicSelect" placeholder="Cari server...">
                            <select name="server" id="serverPublicSelect" class="form-select js-searchable-select" required>
                                <option value="">Pilih Server</option>
                                @foreach (($servers ?? collect()) as $server)
                                    <option value="{{ $server }}" {{ old('server') === $server ? 'selected' : '' }}>{{ $server }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if (($servers ?? collect())->isEmpty())
                            <small class="text-danger">Belum ada server. Minta admin tambah server di menu Server Manajemen.</small>
                        @endif
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
                        <textarea name="reply_tiket" class="form-control js-auto-resize-textarea" rows="3"></textarea>
                    </div>
                    <div id="submitNoted" class="alert alert-info mt-2 d-none">
                        Noted: Anda telah melakukan pengajuan, jika anda ingin melakukan pengajuan ulang klik button "Pengajuan Ulang".
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="btnSubmit">Kirim Pengajuan</button>
                    <button type="button" class="btn btn-warning w-100 d-none mt-2" id="btnResubmit">Pengajuan Ulang</button>
                </form>

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

        document.querySelectorAll('textarea.js-auto-resize-textarea').forEach(function (textarea) {
            const resize = function () {
                const maxHeight = 260;
                textarea.style.height = 'auto';
                const nextHeight = Math.min(textarea.scrollHeight, maxHeight);
                textarea.style.height = nextHeight + 'px';
                textarea.style.overflowY = textarea.scrollHeight > maxHeight ? 'auto' : 'hidden';
            };

            textarea.addEventListener('input', resize);
            resize();
        });

        document.querySelectorAll('.js-searchable-select').forEach(function (select) {
            const originalOptions = Array.from(select.options).map(function (opt) {
                return { value: opt.value, text: opt.text };
            });

            const searchInput = document.querySelector('.js-select-search[data-target="' + select.id + '"]');
            if (!searchInput) return;

            const renderOptions = function (query) {
                const q = String(query || '').toLowerCase().trim();
                const currentValue = select.value;

                const filtered = originalOptions.filter(function (opt, idx) {
                    if (idx === 0) return true;
                    return opt.text.toLowerCase().includes(q);
                });

                select.innerHTML = '';
                filtered.forEach(function (opt) {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.text;
                    if (opt.value === currentValue) option.selected = true;
                    select.appendChild(option);
                });

                if (!Array.from(select.options).some(function (opt) { return opt.value === currentValue; })) {
                    select.selectedIndex = 0;
                }
            };

            searchInput.addEventListener('input', function () {
                renderOptions(this.value);
            });
        });

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
