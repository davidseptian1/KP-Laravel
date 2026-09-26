@extends('layouts.app')

@section('content')
<h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a href="{{ route('userCreate') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah User
            </a>
            @if(auth()->user()->jabatan === 'Superadmin')
                <a href="{{ route('admin.pendataan.access') }}" class="btn btn-sm btn-outline-primary ms-2">
                    <i class="fas fa-shield-alt me-1"></i> Kelola Akses Pendataan
                </a>
            @endif
        </div>
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
                        <th class="text-center">Akses Pendataan</th>
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
                            @elseif ($item->jabatan == 'Superadmin')
                                <span class="badge bg-danger">{{ $item->jabatan }}</span>
                            @else
                                <span class="badge bg-success">{{ $item->jabatan }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if ($item->jabatan === 'Superadmin')
                                <span class="badge bg-secondary-subtle text-secondary border">Superadmin</span>
                            @elseif ($item->has_pendataan_access)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                    <i class="fas fa-check me-1"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-light text-muted border px-2 py-1">Nonaktif</span>
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
