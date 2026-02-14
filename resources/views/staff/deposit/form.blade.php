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
                <form method="POST" action="{{ route('deposit.form.submit', $form->token) }}">
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
                        <label class="form-label">Reply Penambahan</label>
                        <textarea name="reply_penambahan" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jam</label>
                        <input type="time" name="jam" class="form-control" required />
                    </div>
                    <button class="btn btn-primary w-100">Kirim Deposit</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
