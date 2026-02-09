@extends('layouts.app')

@section('content')
<h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <a href="{{ route('userCreate') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah User
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Jabatan</th>
                        <th><i class="fas fa-cog"></i></th>
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
                                <span class="badge bg-dark">{{ $item->jabatan }}</span>
                            @else
                                <span class="badge bg-success">{{ $item->jabatan }}</span>
                            @endif
                        </td>
                        <td class="text-nowrap">

                            <a href="{{ route('userEdit', $item->id) }}"
                               class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>

                            <!-- FORM DELETE LANGSUNG -->
                            <form action="{{ route('userDestroy', $item->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus user {{ $item->nama }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
