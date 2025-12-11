@extends('layouts/app')

@section('content')
    <h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-center justify-content-xl-between">
            <div class="mb-1 mr-2">
                <a href="{{ route('userCreate') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus mr-2"></i>Tambah User</a>
            </div>
            <!-- <div>
                <a href="{{ route('userExcel')}}" class="btn btn-sm btn-success">
                    <i class="fas fa-file-excel mr-2"></i>Excel</a>

                <a href="{{ route('userPdf')}}" class="btn btn-sm btn-danger" target='__blank'>
                    <i class="fas fa-file-pdf mr-2"></i>PDF</a>
            </div> -->
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Jabatan</th>
                            <th>
                                <i class="fas fa-cog"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->nama }}</td>
                                <td>{{ $item->email }}</td>
                                <td>
                                    @if ($item->jabatan == 'Admin')
                                        <span class="badge badge-dark">
                                            {{ $item->jabatan }}
                                        </span>
                                    @else
                                        <span class="badge badge-success">
                                            {{ $item->jabatan }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('userEdit', $item->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" data-toggle="modal"
                                        data-target="#exampleModal{{ $item->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    @include('admin/user/modal', ['item' => $item])
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
