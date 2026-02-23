@extends('layouts/app')

@section('content')

<div class="page-header" style="margin: 0; padding: 0;">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">API Manajement</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">API Manajement</h2>
                    <p class="text-muted mb-0">Endpoint API untuk semua menu Monitoring.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-0">
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">Buat API Key Baru</h5>
                <form method="POST" action="{{ route('admin.api-management.keys.store') }}" class="d-grid gap-2">
                    @csrf
                    <div>
                        <label class="form-label">Nama API Key</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Partner Monitoring" required>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="never_expires" value="1" id="neverExpires" checked>
                        <label class="form-check-label" for="neverExpires">Tidak Kedaluwarsa</label>
                    </div>
                    <div>
                        <label class="form-label">Kadaluarsa (opsional)</label>
                        <input type="datetime-local" name="expires_at" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-danger">Generate API Key</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @if(session('generated_api_key'))
            <div class="alert alert-warning">
                <div class="fw-bold mb-1">API key baru (simpan sekarang, hanya tampil 1x):</div>
                <div class="small"><code>{{ session('generated_api_key') }}</code></div>
            </div>
        @endif

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="mb-3">Daftar API Keys</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th>Nama</th>
                                <th>Status</th>
                                <th>Kadaluarsa</th>
                                <th>Terakhir Dipakai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($apiKeys as $key)
                                <tr>
                                    <td>{{ $key->name }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $key->is_active ? 'success' : 'secondary' }}">
                                            {{ $key->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                        </span>
                                    </td>
                                    <td>{{ $key->expires_at ? $key->expires_at->format('d/m/Y H:i') : 'Permanen' }}</td>
                                    <td>{{ $key->last_used_at ? $key->last_used_at->format('d/m/Y H:i') : '-' }}</td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('admin.api-management.keys.destroy', $key->id) }}" onsubmit="return confirm('Hapus API key ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada API key</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Autentikasi API</h5>
                <p class="mb-2">Gunakan API key di header request:</p>
                <pre class="bg-light p-3 rounded mb-3">X-API-KEY: your-api-key
Accept: application/json</pre>

                <h5 class="mb-3">Daftar Endpoint Monitoring</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light text-center">
                            <tr>
                                <th style="width: 100px;">Method</th>
                                <th>Endpoint</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($endpoints as $ep)
                                <tr>
                                    <td class="text-center"><span class="badge bg-primary">{{ $ep['method'] }}</span></td>
                                    <td><code>{{ $ep['endpoint'] }}</code></td>
                                    <td>{{ $ep['desc'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
