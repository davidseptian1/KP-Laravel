@extends('layouts.app')
@section('content')

<h4>Tambah Report Khusus</h4>

<form method="POST" action="{{ route('admin.report.khusus.store') }}">
@csrf

<input type="date" name="tanggal" class="form-control mb-2" required>
<input type="text" name="nama" class="form-control mb-2" placeholder="Nama">
<input type="text" name="produk" class="form-control mb-2" placeholder="Produk">
<input type="number" name="total" class="form-control mb-2" placeholder="Total">
<textarea name="note" class="form-control mb-2" placeholder="Catatan Khusus"></textarea>

<button class="btn btn-success">Simpan</button>
</form>

@endsection
