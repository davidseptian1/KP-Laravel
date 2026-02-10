@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Pengajuan Data</li>
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
                <h5 class="mb-0">Form Pengajuan Data</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('data-request.form.submit', $form->token) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Aplikasi</label>
                        <select name="aplikasi" class="form-select" required>
                            <option value="">Pilih Aplikasi</option>
                            @foreach (['belanja kuota','238','crm payment','aira'] as $item)
                                <option value="{{ $item }}">{{ strtoupper($item) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username Pemilik Akun</label>
                        <input type="text" name="username_akun" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor HP Terdaftar</label>
                        <input type="text" name="nomor_hp" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Lama</label>
                        <input type="email" name="email_lama" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Baru (Opsional)</label>
                        <input type="email" name="email_baru" class="form-control" />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap Pemohon</label>
                        <input type="text" name="nama_pemohon" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Riwayat Transaksi (3-10 transaksi)</label>
                        <textarea name="riwayat_transaksi" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Saldo Terakhir Saat Ini</label>
                        <input type="number" name="saldo_terakhir" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Perubahan</label>
                        <select name="jenis_perubahan" class="form-select" required>
                            <option value="">Pilih Jenis Perubahan</option>
                            @foreach (['perubahan email','nomor hp','password'] as $item)
                                <option value="{{ $item }}">{{ strtoupper($item) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Perubahan</label>
                        <textarea name="alasan_perubahan" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Verifikasi Identitas (Foto KTP)</label>
                        <input type="file" name="foto_ktp" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Verifikasi Identitas (Selfie Pegang KTP)</label>
                        <input type="file" name="foto_selfie" class="form-control" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Admin (Tujuan)</label>
                        <input type="text" class="form-control" value="{{ config('whatsapp.admin_numbers')[0] ?? '' }}" readonly />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp Pengisi</label>
                        <input type="text" name="wa_pengisi" class="form-control" placeholder="628xxxxxxxxxx" required />
                    </div>
                    <button class="btn btn-primary w-100">Kirim Pengajuan</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
