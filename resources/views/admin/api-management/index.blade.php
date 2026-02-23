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
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Autentikasi API</h5>
                <p class="mb-2">Gunakan Sanctum token di header request:</p>
                <pre class="bg-light p-3 rounded mb-3">Authorization: Bearer your-token
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
