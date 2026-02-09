@extends('layouts.app')
@section('content')

<h4>Edit Report Khusus</h4>

<form method="POST" action="{{ route('admin.report.khusus.update',$data->id) }}">
@csrf

<input type="date" name="tanggal" value="{{ $data->tanggal }}" class="form-control mb-2">
<input type="text" name="nama" value="{{ $data->nama }}" class="form-control mb-2">
<input type="text" name="produk" value="{{ $data->produk }}" class="form-control mb-2">
<input type="number" name="total" value="{{ $data->total }}" class="form-control mb-2">
<textarea name="note" class="form-control mb-2">{{ $data->note }}</textarea>

<button class="btn btn-primary">Update</button>
</form>

@endsection
