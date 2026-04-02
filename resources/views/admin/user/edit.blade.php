@extends('layouts/app')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

    <div class="card">
        <!-- <div class="card-header bg-warning d-flex flex-wrap justify-content-center justify-content-xl-between"> -->
        <div class="card-header d-flex flex-wrap justify-content-center justify-content-xl-between">
            <div class="mb-1 mr-2">
                <a href="{{ route('user') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali</a>
            </div>
        </div>
        <div class="card-body">

        <form action="{{ route('userUpdate', $user->id) }}" method="post">
        @csrf

            <div class="row mb-2">
                <div class="col-6">
                    <label class="form-label">
                        <span class="text-danger">*</span>
                        Nama :
                    </label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ $user->nama }}">
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
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $user->email }}">
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
                        <option disabled>-- Pilih Jabatan --</option>
                        @php
                            $allowed = $allowedRoles ?? [];
                        @endphp
                        @if(!empty($allowed))
                            @foreach($allowed as $role)
                                <option value="{{ $role }}" {{ $user->jabatan == $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                            @if(!in_array($user->jabatan, $allowed))
                                <option value="{{ $user->jabatan }}" selected disabled>{{ $user->jabatan }} (saat ini)</option>
                            @endif
                        @else
                            <option value="{{ $user->jabatan }}" selected disabled>{{ $user->jabatan }}</option>
                        @endif
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
                    <i class="fas fa-edit mr-1"></i> Edit
                </button>
            </div>
        </form>
        </div>
    </div>
@endsection
