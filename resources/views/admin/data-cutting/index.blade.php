@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-database-off me-2"></i>Potong Data Database
                </h2>
                <div class="text-muted mt-1">Kelola penghapusan data lama untuk optimasi performa</div>
            </div>
            <div class="col-auto">
                <a href="{{ route('data-cutting.guide') }}" class="btn btn-info me-2" title="Panduan lengkap">
                    <i class="ti ti-help me-2"></i>Panduan
                </a>
                <a href="{{ route('data-cutting.create') }}" class="btn btn-primary">
                    <i class="ti ti-scissors me-2"></i>Potong Data Baru
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-xl">
        @if ($message = Session::get('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="ti ti-check me-2"></i>
                        <strong>Berhasil!</strong> {{ $message }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex">
                    <div>
                        <i class="ti ti-alert-triangle me-2"></i>
                        <strong>Error!</strong> {{ $message }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter">
                            <thead>
                                <tr>
                                    <th>Tanggal Potong</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Total Record Dihapus</th>
                                    <th>Backup</th>
                                    <th>Status</th>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td><strong>{{ $log->cut_date->format('d M Y') }}</strong></td>
                                        <td>{{ $log->user->nama ?? 'Unknown' }}</td>
                                        <td>
                                            <span class="badge bg-blue">{{ $log->getTotalDeletedCount() }}</span>
                                        </td>
                                        <td>
                                            @if($log->backup_file)
                                                <span class="badge bg-green">{{ $log->backup_size }}</span>
                                            @else
                                                <span class="badge bg-secondary">Tidak Ada</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->status === 'completed')
                                                <span class="badge bg-success">Selesai</span>
                                            @elseif($log->status === 'pending')
                                                <span class="badge bg-warning">Menunggu</span>
                                            @elseif($log->status === 'deleting' || $log->status === 'backing_up')
                                                <span class="badge bg-info">Proses...</span>
                                            @elseif($log->status === 'failed')
                                                <span class="badge bg-danger">Gagal</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $log->created_at->format('d M Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-ghost-primary" data-bs-toggle="modal" 
                                                    data-bs-target="#detailModal{{ $log->id }}" title="Detail">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                                @if($log->backup_file)
                                                    <a href="{{ route('data-cutting.download', $log->id) }}" 
                                                        class="btn btn-sm btn-ghost-success" title="Download Backup">
                                                        <i class="ti ti-download"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Detail Modal -->
                                    <div class="modal modal-blur fade" id="detailModal{{ $log->id }}" tabindex="-1" role="dialog">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Potong Data</h5>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Tanggal Potong:</strong><br>
                                                            {{ $log->cut_date->format('d M Y') }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Dibuat Oleh:</strong><br>
                                                            {{ $log->user->nama ?? 'Unknown' }}
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <strong class="d-block mb-2">Data yang Dihapus:</strong>
                                                    <div class="row">
                                                        @if($log->transaksis_deleted > 0)
                                                            <div class="col-md-6 mb-2">
                                                                <i class="ti ti-receipt text-primary"></i>
                                                                Transaksi: <strong>{{ $log->transaksis_deleted }}</strong>
                                                            </div>
                                                        @endif
                                                        @if($log->deposits_deleted > 0)
                                                            <div class="col-md-6 mb-2">
                                                                <i class="ti ti-wallet text-success"></i>
                                                                Deposit: <strong>{{ $log->deposits_deleted }}</strong>
                                                            </div>
                                                        @endif
                                                        @if($log->reimburse_deleted > 0)
                                                            <div class="col-md-6 mb-2">
                                                                <i class="ti ti-cash-refund text-warning"></i>
                                                                Reimburse: <strong>{{ $log->reimburse_deleted }}</strong>
                                                            </div>
                                                        @endif
                                                        @if($log->minusans_deleted > 0)
                                                            <div class="col-md-6 mb-2">
                                                                <i class="ti ti-minus text-danger"></i>
                                                                Minusan: <strong>{{ $log->minusans_deleted }}</strong>
                                                            </div>
                                                        @endif
                                                        @if($log->imports_deleted > 0)
                                                            <div class="col-md-6 mb-2">
                                                                <i class="ti ti-upload text-info"></i>
                                                                Import: <strong>{{ $log->imports_deleted }}</strong>
                                                            </div>
                                                        @endif
                                                        @if($log->tag_nomor_pasca_bayars_deleted > 0)
                                                            <div class="col-md-6 mb-2">
                                                                <i class="ti ti-tag text-secondary"></i>
                                                                Tag Nomor: <strong>{{ $log->tag_nomor_pasca_bayars_deleted }}</strong>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    @if($log->notes)
                                                        <hr>
                                                        <strong>Catatan:</strong><br>
                                                        <p class="text-muted">{{ $log->notes }}</p>
                                                    @endif

                                                    @if($log->error_log)
                                                        <div class="alert alert-danger">
                                                            <strong>Error Log:</strong><br>
                                                            <small>{{ $log->error_log }}</small>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="ti ti-inbox fs-2 mb-2"></i>
                                            <p>Belum ada riwayat potong data</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        {{ $logs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
