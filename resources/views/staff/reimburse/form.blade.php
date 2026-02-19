@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Form Reimburse</li>
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
                <h5 class="mb-0">Isi Form Reimburse</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div id="reimburseNotedAlert" class="alert alert-info d-none">
                    <strong>Noted:</strong> Pengajuan Anda sudah tercatat. Jika ada revisi, klik <strong>Pengajuan Ulang</strong>.
                </div>

                <form id="reimburseForm" method="POST" action="{{ route('reimburse.form.submit', $form->token) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Pilih Metode</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="bank">Bank</option>
                        </select>
                    </div>

                    <div class="mb-3" id="ewallet_fields" style="display: none;">
                        <label class="form-label">E-Wallet</label>
                        <select name="ewallet_provider" id="ewallet_provider" class="form-select mb-2">
                            <option value="">Pilih E-Wallet</option>
                            <option value="gopay">Gopay</option>
                            <option value="shopee">Shopee</option>
                            <option value="dana">Dana</option>
                        </select>
                        <input type="text" name="ewallet_number" id="ewallet_number" class="form-control mb-2" placeholder="Nomor pengguna" />
                        <input type="text" name="ewallet_name" id="ewallet_name" class="form-control" placeholder="Atas nama" />
                    </div>

                    <div class="mb-3" id="bank_fields" style="display: none;">
                        <label class="form-label">Bank</label>
                        <select name="bank_provider" id="bank_provider" class="form-select mb-2">
                            <option value="">Pilih Bank</option>
                            <option value="bri">BRI</option>
                            <option value="bca">BCA</option>
                            <option value="mandiri">Mandiri</option>
                            <option value="seabank">Seabank</option>
                            <option value="bca digital">BCA Digital</option>
                        </select>
                        <input type="text" name="bank_account_number" id="bank_account_number" class="form-control mb-2" placeholder="No rekening" />
                        <input type="text" name="bank_account_name" id="bank_account_name" class="form-control" placeholder="Atas nama" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Divisi</label>
                        <select name="divisi" class="form-select" required>
                            <option value="">Pilih Divisi</option>
                            @foreach (['accounting','act','server','hrd','direksi','gudang','sosmed','host live','it'] as $divisi)
                                <option value="{{ $divisi }}">{{ strtoupper($divisi) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal</label>
                        <input type="number" name="nominal" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan (Deskripsi)</label>
                        <textarea name="keperluan" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bukti (jpg/png/pdf) - bisa lebih dari 1</label>
                        <input type="file" name="bukti[]" class="form-control" multiple required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Admin (Tujuan)</label>
                        <input type="text" class="form-control" value="{{ config('whatsapp.admin_numbers')[0] ?? '' }}" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Pengisi</label>
                        <input type="text" name="wa_pengisi" class="form-control" placeholder="628xxxxxxxxxx" required />
                    </div>
                    <button type="submit" id="reimburseSubmitBtn" class="btn btn-primary w-100">Kirim Pengajuan</button>
                    <button type="button" id="reimburseResubmitBtn" class="btn btn-warning w-100 d-none mt-2">Pengajuan Ulang</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    (function () {
        const form = document.getElementById('reimburseForm');
        const submitBtn = document.getElementById('reimburseSubmitBtn');
        const resubmitBtn = document.getElementById('reimburseResubmitBtn');
        const notedAlert = document.getElementById('reimburseNotedAlert');
        const wasSubmitted = @json((bool) session('reimburse_submitted'));

        const paymentMethod = document.getElementById('payment_method');
        const ewalletFields = document.getElementById('ewallet_fields');
        const bankFields = document.getElementById('bank_fields');
        const ewalletProvider = document.getElementById('ewallet_provider');
        const ewalletNumber = document.getElementById('ewallet_number');
        const ewalletName = document.getElementById('ewallet_name');
        const bankProvider = document.getElementById('bank_provider');
        const bankAccountNumber = document.getElementById('bank_account_number');
        const bankAccountName = document.getElementById('bank_account_name');

        function toggleFields() {
            const value = paymentMethod.value;
            if (value === 'ewallet') {
                ewalletFields.style.display = 'block';
                bankFields.style.display = 'none';
                ewalletProvider.required = true;
                ewalletNumber.required = true;
                ewalletName.required = true;
                bankProvider.required = false;
                bankAccountNumber.required = false;
                bankAccountName.required = false;
            } else if (value === 'bank') {
                ewalletFields.style.display = 'none';
                bankFields.style.display = 'block';
                ewalletProvider.required = false;
                ewalletNumber.required = false;
                ewalletName.required = false;
                bankProvider.required = true;
                bankAccountNumber.required = true;
                bankAccountName.required = true;
            } else {
                ewalletFields.style.display = 'none';
                bankFields.style.display = 'none';
                ewalletProvider.required = false;
                ewalletNumber.required = false;
                ewalletName.required = false;
                bankProvider.required = false;
                bankAccountNumber.required = false;
                bankAccountName.required = false;
            }
        }

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
            toggleFields();
        }

        paymentMethod.addEventListener('change', toggleFields);
        toggleFields();

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
