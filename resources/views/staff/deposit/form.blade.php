@extends('layouts/app')

@section('content')

<style>
    textarea.js-auto-resize-textarea {
        overflow-y: auto;
        resize: none;
        min-height: 70px;
        max-height: 260px;
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

                <form method="POST" action="{{ route('deposit.form.submit', $form->token) }}" id="depositRequestForm" autocomplete="off">
                    @csrf
                    <div class="alert alert-warning py-2 mb-3">
                        <strong>Note:</strong> Untuk <strong>Nama Supplier</strong>, <strong>Deposit/Hutang</strong>, <strong>Bank</strong>, dan <strong>Server</strong> gunakan data yang muncul. Jika data tidak ada, request tidak akan tersimpan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="nama_supplier" class="form-control" list="supplierPublicList" value="{{ old('nama_supplier') }}" placeholder="Ketik nama supplier..." autocomplete="off" required>
                        <datalist id="supplierPublicList">
                            @foreach (($suppliers ?? collect()) as $supplier)
                                <option value="{{ $supplier }}"></option>
                            @endforeach
                        </datalist>
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
                        <input type="text" name="jenis_transaksi" class="form-control" list="jenisPublicList" value="{{ old('jenis_transaksi', 'deposit') }}" autocomplete="off" required>
                        <datalist id="jenisPublicList">
                            <option value="deposit"></option>
                            <option value="hutang"></option>
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">BANK</label>
                        <input type="text" name="bank" class="form-control" list="bankPublicList" value="{{ old('bank') }}" placeholder="Ketik nama bank..." autocomplete="off" required>
                        <datalist id="bankPublicList">
                            @foreach (($banks ?? collect()) as $bank)
                                <option value="{{ $bank }}"></option>
                            @endforeach
                        </datalist>
                        @if (($banks ?? collect())->isEmpty())
                            <small class="text-danger">Belum ada bank. Minta admin tambah bank di menu Bank Manajemen.</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bank Tujuan</label>
                        <input type="text" name="bank_tujuan" class="form-control" list="bankTujuanPublicList" value="{{ old('bank_tujuan') }}" placeholder="Otomatis dari Reply Tiket" autocomplete="off">
                        <select id="bankTujuanParsedSelectPublic" class="form-select form-select-sm mt-2 d-none"></select>
                        <datalist id="bankTujuanPublicList"></datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Server</label>
                        <input type="text" name="server" class="form-control" list="serverPublicList" value="{{ old('server') }}" placeholder="Ketik server..." autocomplete="off" required>
                        <datalist id="serverPublicList">
                            @foreach (($servers ?? collect()) as $server)
                                <option value="{{ $server }}"></option>
                            @endforeach
                        </datalist>
                        @if (($servers ?? collect())->isEmpty())
                            <small class="text-danger">Belum ada server. Minta admin tambah server di menu Server Manajemen.</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No-Rek</label>
                        <input type="text" name="no_rek" class="form-control" list="noRekeningPublicList" autocomplete="off" required />
                        <select id="noRekParsedSelectPublic" class="form-select form-select-sm mt-2 d-none"></select>
                        <datalist id="noRekeningPublicList"></datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Rekening</label>
                        <input type="text" name="nama_rekening" class="form-control" list="namaRekeningPublicList" autocomplete="off" required />
                        <select id="namaRekParsedSelectPublic" class="form-select form-select-sm mt-2 d-none"></select>
                        <datalist id="namaRekeningPublicList"></datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reply Tiket</label>
                        <textarea name="reply_tiket" class="form-control js-auto-resize-textarea" rows="3"></textarea>
                    </div>
                    <div id="submitNoted" class="alert alert-info mt-2 d-none">
                        Noted: Anda telah melakukan pengajuan, jika anda ingin melakukan pengajuan ulang klik button "Pengajuan Ulang".
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="btnSubmit">Kirim Pengajuan</button>
                    <button type="button" class="btn btn-outline-secondary w-100 mt-2" id="btnResetAll">Reset</button>
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
        const resetBtn = document.getElementById('btnResetAll');
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

        if (!btn || !text) {
            // Keep going, auto parser runs later
        } else {
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
        }

        // Auto parse Reply Tiket
        const replyTiketInput = document.querySelector('textarea[name="reply_tiket"]');
        const nominalInput = document.querySelector('input[name="nominal"]');
        const bankTujuanInput = document.querySelector('input[name="bank_tujuan"]');
        const noRekInput = document.querySelector('input[name="no_rek"]');
        const namaRekeningInput = document.querySelector('input[name="nama_rekening"]');
        const bankTujuanList = document.getElementById('bankTujuanPublicList');
        const noRekList = document.getElementById('noRekeningPublicList');
        const namaRekList = document.getElementById('namaRekeningPublicList');
        const bankTujuanParsedSelect = document.getElementById('bankTujuanParsedSelectPublic');
        const noRekParsedSelect = document.getElementById('noRekParsedSelectPublic');
        const namaRekParsedSelect = document.getElementById('namaRekParsedSelectPublic');
        let parsedBankRekPairs = [];

        function resetPublicDepositFormFields() {
            if (!form) return;

            form.querySelectorAll('input, textarea, select').forEach(function (el) {
                if (el.name === '_token') return;
                if (el.id === 'btnSubmit' || el.id === 'btnResubmit' || el.id === 'btnResetAll') return;
                if (el.type === 'file') {
                    el.value = '';
                    return;
                }
                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                    return;
                }
                el.value = '';
            });

            const jenisInput = form.querySelector('input[name="jenis_transaksi"]');
            if (jenisInput) {
                jenisInput.value = 'deposit';
            }

            if (bankTujuanList) bankTujuanList.innerHTML = '';
            if (noRekList) noRekList.innerHTML = '';
            if (namaRekList) namaRekList.innerHTML = '';

            [bankTujuanParsedSelect, noRekParsedSelect, namaRekParsedSelect].forEach(function (selectEl) {
                if (!selectEl) return;
                selectEl.innerHTML = '';
                selectEl.classList.add('d-none');
            });

            parsedBankRekPairs = [];

            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Kirim Pengajuan';
            }
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', resetPublicDepositFormFields);
        }

        function bindParsedSelect(selectEl, inputEl, options, placeholderLabel) {
            if (!selectEl || !inputEl) return;

            if (!Array.isArray(options) || options.length <= 1) {
                selectEl.innerHTML = '';
                selectEl.classList.add('d-none');
                return;
            }

            selectEl.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = placeholderLabel;
            placeholder.selected = true;
            selectEl.appendChild(placeholder);

            options.forEach(function (value) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = value;
                selectEl.appendChild(option);
            });

            selectEl.onchange = function () {
                if (this.value) {
                    inputEl.value = this.value;
                }
            };

            selectEl.classList.remove('d-none');
        }

        function syncNoRekFromBank(selectedBank) {
            const normalizedBank = String(selectedBank || '').toUpperCase().trim();
            if (!normalizedBank) return;

            const filteredNoreks = parsedBankRekPairs
                .filter(pair => pair.bank === normalizedBank)
                .map(pair => pair.norek)
                .filter((value, index, arr) => arr.indexOf(value) === index);

            if (filteredNoreks.length === 0) return;

            if (noRekList) {
                noRekList.innerHTML = '';
                filteredNoreks.forEach(num => {
                    const opt = document.createElement('option');
                    opt.value = num;
                    noRekList.appendChild(opt);
                });
            }

            bindParsedSelect(noRekParsedSelect, noRekInput, filteredNoreks, 'Pilih No Rekening hasil parse...');

            if (noRekInput) {
                noRekInput.value = filteredNoreks[0];
            }
        }

        if (bankTujuanInput) {
            bankTujuanInput.addEventListener('change', function () {
                syncNoRekFromBank(this.value);
            });
            bankTujuanInput.addEventListener('input', function () {
                syncNoRekFromBank(this.value);
            });
        }

        if (replyTiketInput) {
            replyTiketInput.addEventListener('input', function() {
                const text = this.value;
                if (!text) return;

                // Parse Nominal
                const nominalMatch =
                    text.match(/(?:Rp\s*\.?|IDR)\s*([0-9][\d\.,]{2,})/i) ||
                    text.match(/(?:(?:Nominal|Total|Jumlah|Transfer|Sebesar|Senilai|Bayar|Total Pembayaran)[^\d]*?)\s*([\d\.\,]{4,})/i) ||
                    text.match(/\b(\d{1,3}(?:\.\d{3}){1,3})\b/);
                if (nominalMatch && nominalInput) {
                    let nom = nominalMatch[1].replace(/[^0-9]/g, '');
                    if (nom && !nominalInput.value) {
                        nominalInput.value = nom;
                    }
                }

                // Parse Nama Rekening Multiple (tangkap nama sesudah a.n. sampai tanda baca terdekat, nama bank, dsb.)
                // Ditambahkan batas pemberhentian jika ketemu nama Bank
                const namaRegex = /(?:a\/n|a\.n\.?|atas\s+nama\s*:?|\bAN\.?\b)\s*[:\-]?\s*(.*?)(?=\s*(?:BCA|BNI|BRI|MANDIRI|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN)\b|\s*no\s*rek\b|\s*rekening\b|\s*berita\b|\s*edc|\s*cntr|\s*\||\s*deposit\b|\s*harap\b|\s*konfirmasi\b|\s*batas\b|\s*transfer\b|\(|!+|\.\s*tiket|\.\s*$|,|\.\s[A-Z]|\n|\r|$)/gi;
                let parsedNamas = [];
                let nMatch;
                while ((nMatch = namaRegex.exec(text)) !== null) {
                    let extracted = nMatch[1]
                        .trim()
                        .replace(/^[\s:;,\-|()\[\]]+|[\s:;,\-|()\[\]]+$/g, '')
                        .replace(/\s{2,}/g, ' ');
                    if (extracted && extracted.length > 2 && !parsedNamas.includes(extracted)) {
                        parsedNamas.push(extracted);
                    }
                }

                if (parsedNamas.length > 0) {
                    if (namaRekeningInput && !namaRekeningInput.value) {
                        namaRekeningInput.value = parsedNamas[0];
                    }
                    if (namaRekList) {
                        namaRekList.innerHTML = '';
                        parsedNamas.forEach(ns => {
                            const opt = document.createElement('option');
                            opt.value = ns;
                            namaRekList.appendChild(opt);
                        });
                    }
                }
                bindParsedSelect(namaRekParsedSelect, namaRekeningInput, parsedNamas, 'Pilih Nama Rekening hasil parse...');

                // Parse Bank and Norek directly
                const bankRegexes = [
                    /\[(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|OVO|DANA|GOPAY|SHOPEEPAY|LINKAJA|MUAMALAT|BTN|PANIN)(?:\s+\d+)?[^\]]*\]\s*[-:=]?\s*([0-9][0-9\-\s]{4,30})/gi,
                    /\b(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|OVO|DANA|GOPAY|SHOPEEPAY|LINKAJA|MUAMALAT|BTN|PANIN)\b\s*(?:-|:|=)?\s*([0-9][0-9\-\s]{4,30})/gi,
                    /\b(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|OVO|DANA|GOPAY|SHOPEEPAY|LINKAJA|MUAMALAT|BTN|PANIN)\b\s*(?:-|:|=)\s*[A-Z]{2,20}\s*(?:-|:|=)\s*([0-9][0-9\-\s]{4,30})/gi,
                ];
                const banks = [];
                const noreks = [];
                const bankRekPairs = [];
                bankRegexes.forEach(function (bankRegex) {
                    let bMatch;
                    while ((bMatch = bankRegex.exec(text)) !== null) {
                        let b = bMatch[1].toUpperCase();
                        if (b === 'MNDR') b = 'MANDIRI';
                        let r = bMatch[2].replace(/[^0-9]/g, '');
                        if (r.length >= 5) {
                            if (!banks.includes(b)) banks.push(b);
                            if (!noreks.includes(r)) noreks.push(r);
                            const pairKey = b + '|' + r;
                            if (!bankRekPairs.some(pair => (pair.bank + '|' + pair.norek) === pairKey)) {
                                bankRekPairs.push({ bank: b, norek: r });
                            }
                        }
                    }
                });

                if (banks.length === 0) {
                    const bankOnlyRegex = /\b(BCA|BNI|BRI|MANDIRI|MNDR|BSI|CIMB|MAYBANK|PERMATA|JENIUS|SEABANK|NEO|BNC|BJB|DANAMON|MEGA|JAGO|ALADIN|MUAMALAT|BTN|PANIN)\b/gi;
                    let boMatch;
                    while ((boMatch = bankOnlyRegex.exec(text)) !== null) {
                        let b = boMatch[1].toUpperCase();
                        if (b === 'MNDR') b = 'MANDIRI';
                        if (!banks.includes(b)) banks.push(b);
                    }
                }

                if (noreks.length === 0) {
                    const noRekLabelMatch = text.match(/no\s*rek(?:ening)?\s*[:=\-]?\s*([0-9\-\s]{5,40})/i);
                    if (noRekLabelMatch) {
                        const noRekNumber = noRekLabelMatch[1].replace(/[^0-9]/g, '');
                        if (noRekNumber.length >= 5) {
                            noreks.push(noRekNumber);

                            const preferredPairBank = banks.includes('BCA')
                                ? 'BCA'
                                : (banks[0] || 'BCA');

                            if (!banks.includes(preferredPairBank)) {
                                banks.push(preferredPairBank);
                            }

                            bankRekPairs.push({
                                bank: preferredPairBank,
                                norek: noRekNumber,
                            });
                        }
                    }
                }

                if (noreks.length === 0) {
                    const vaMatch = text.match(/nomor\s*(?:virtual\s*account|va|rekening)\s*[:\-]?\s*([0-9]{8,40})/i);
                    if (vaMatch) {
                        const vaNumber = vaMatch[1].replace(/[^0-9]/g, '');
                        if (vaNumber.length >= 8) {
                            noreks.push(vaNumber);

                            const preferredPairBank = banks.includes('BCA')
                                ? 'BCA'
                                : (banks[0] || 'BCA');

                            if (!banks.includes(preferredPairBank)) {
                                banks.push(preferredPairBank);
                            }

                            bankRekPairs.push({
                                bank: preferredPairBank,
                                norek: vaNumber,
                            });
                        }
                    }
                }

                parsedBankRekPairs = bankRekPairs;

                if (banks.length > 0) {
                    if (bankTujuanList) {
                        bankTujuanList.innerHTML = '';
                        banks.forEach(bank => {
                            const opt = document.createElement('option');
                            opt.value = bank;
                            bankTujuanList.appendChild(opt);
                        });
                    }

                    if (noRekList) {
                        noRekList.innerHTML = '';
                        noreks.forEach(num => {
                            const opt = document.createElement('option');
                            opt.value = num;
                            noRekList.appendChild(opt);
                        });
                    }

                    const preferredBank = banks.includes('BCA') ? 'BCA' : banks[0];

                    if (bankTujuanInput) bankTujuanInput.value = preferredBank;
                    syncNoRekFromBank(preferredBank);
                }

                bindParsedSelect(bankTujuanParsedSelect, bankTujuanInput, banks, 'Pilih Bank Tujuan hasil parse...');

                if (bankTujuanParsedSelect && !bankTujuanParsedSelect.classList.contains('d-none')) {
                    const preferredBank = banks.includes('BCA') ? 'BCA' : banks[0];
                    bankTujuanParsedSelect.value = preferredBank || '';
                    bankTujuanParsedSelect.onchange = function () {
                        if (this.value) {
                            bankTujuanInput.value = this.value;
                            syncNoRekFromBank(this.value);
                        }
                    };
                }
            });
        }
    })();
</script>
@endpush
