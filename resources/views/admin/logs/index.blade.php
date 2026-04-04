@extends('layouts.app')

@section('content')
<h1 class="h3 mb-4 text-gray-800">{{ $title }}</h1>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('superadmin.logs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Cari</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Nama, route, path">
            </div>
            <div class="col-md-2">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="">Semua</option>
                    <option value="Admin" {{ request('role') === 'Admin' ? 'selected' : '' }}>Admin</option>
                    <option value="Superadmin" {{ request('role') === 'Superadmin' ? 'selected' : '' }}>Superadmin</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Method</label>
                <select name="method" class="form-select">
                    <option value="">Semua</option>
                    @foreach ($methods as $method)
                        <option value="{{ $method }}" {{ request('method') === $method ? 'selected' : '' }}>{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Status</label>
                <input type="number" name="status_code" class="form-control" value="{{ request('status_code') }}" placeholder="200">
            </div>
            <div class="col-md-2">
                <label class="form-label">Dari</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Sampai</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-12 mt-2">
                <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                <a href="{{ route('superadmin.logs.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th style="width: 170px;">Waktu</th>
                        <th>Admin</th>
                        <th style="width: 80px;">Method</th>
                        <th>Path</th>
                        <th>Route</th>
                        <th style="width: 80px;">Status</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ optional($log->created_at)->format('d-m-Y H:i:s') }}</td>
                            <td>
                                <div>{{ $log->actor_name ?? '-' }}</div>
                                <small class="text-muted">{{ $log->actor_role ?? '-' }}</small>
                            </td>
                            <td><span class="badge bg-dark">{{ $log->method }}</span></td>
                            <td>{{ $log->path }}</td>
                            <td>{{ $log->route_name ?? '-' }}</td>
                            <td>{{ $log->status_code }}</td>
                            <td style="min-width: 220px;">
                                @if (!empty($log->request_data))
                                    <details>
                                        <summary>Lihat data</summary>
                                        <pre class="mb-0 mt-2" style="white-space: pre-wrap; max-height: 180px; overflow: auto;">{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Belum ada data logs.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
