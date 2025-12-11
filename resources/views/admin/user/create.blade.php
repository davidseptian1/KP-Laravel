@extends('layouts/app')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-center justify-content-xl-between">
            <div class="mb-1 mr-2">
                <a href="{{ route('user') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali</a>
            </div>
        </div>
        <div class="card-body">

        <form action="{{ route('userStore') }}" method="post">
        @csrf

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Nama :
                    </label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{old('nama')}}">
                    @error('nama')
                    <small class="text-danger">
                        {{ $message }}
                    </small> 
                    @enderror
                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Email :
                    </label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{old('email')}}">
                    @error('email')
                    <small class="text-danger">
                        {{ $message }}
                    </small> 
                    @enderror
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-12">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Jabatan :
                    </label>
                    <select name="jabatan" class="form-control @error('jabatan') is-invalid @enderror">
                        <option selected disabled>-- Pilih Jabatan --</option>
                        <option value="Admin">Admin</option>
                        <option value="HRD">HRD</option>
                        <option value="Staff">Staff</option>
                    </select>
                    @error('jabatan')
                    <small class="text-danger">
                        {{ $message }}
                    </small> 
                    @enderror
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Password :
                    </label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                    <small class="text-danger">
                        {{ $message }}
                    </small> 
                    @enderror

                </div>

                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Password Konfirmasi :
                    </label>
                    <input type="password" name="password_confirmation" class="form-control @error('password') is-invalid @enderror">
                </div>
            </div>

            <div class="form-group mt-3">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fas fa-save mr-1"></i> Simpan
                </button>
            </div>
        </form>
        </div>
    </div>
@endsection
