<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase fs-7 text-muted">
            <tr>
                <th style="width: 50px;" class="text-center">No</th>
                <th style="width: 150px;">Waktu</th>
                <th style="width: 170px;">Grup / Pengirim</th>
                <th style="width: 140px;">Status Bot</th>
                <th>Hasil Ekstraksi & Aktivitas Bot</th>
                <th style="width: 160px;">Balasan Bot</th>
                <th style="width: 80px;" class="text-center">Detail</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $index => $log)
                <tr>
                    <td class="text-center text-muted fw-semibold">
                        {{ $logs->firstItem() + $index }}
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-dark">{{ $log->created_at ? $log->created_at->format('d/m/Y') : '-' }}</span>
                            <small class="text-muted"><i class="ti ti-clock me-1"></i>{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }}</small>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark text-truncate" style="max-width: 160px;" title="{{ $log->chat_title }}">
                                <i class="ti ti-messages me-1 text-primary"></i>{{ $log->chat_title ?: 'Grup / Chat' }}
                            </span>
                            @if($log->sender_name)
                                <small class="text-muted text-truncate" style="max-width: 160px;" title="{{ $log->sender_name }}">
                                    <i class="ti ti-user me-1"></i>{{ $log->sender_name }}
                                </small>
                            @endif
                            @if($log->sender_username)
                                <small class="text-info font-monospace" style="font-size: 0.72rem;">
                                    {{ '@' . $log->sender_username }}
                                </small>
                            @endif
                        </div>
                    </td>
                    <td>
                        {!! $log->status_badge_html !!}
                        @if($log->pendataan_id)
                            <div class="mt-1">
                                <span class="badge bg-light text-primary border border-primary-subtle" style="font-size: 0.72rem;">
                                    <i class="ti ti-link me-1"></i>ID #{{ $log->pendataan_id }}
                                </span>
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($log->parsed_product || $log->parsed_nominal)
                            <div class="p-2 mb-1 bg-light rounded-2 border border-light-subtle">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                                    <span class="fw-bold text-dark text-truncate" style="max-width: 280px;" title="{{ $log->parsed_product }}">
                                        <i class="ti ti-package me-1 text-success"></i>{{ $log->parsed_product ?: '-' }}
                                    </span>
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                                        {{ $log->formatted_nominal }}
                                    </span>
                                </div>
                            </div>
                        @endif
                        <div class="small text-secondary" style="line-height: 1.4;">
                            <i class="ti ti-info-circle me-1 text-muted"></i>{{ $log->action_note ?: '-' }}
                        </div>
                    </td>
                    <td>
                        @if($log->bot_replied)
                            <div class="p-1 px-2 bg-success bg-opacity-10 border border-success-subtle rounded small text-success">
                                <i class="ti ti-check me-1"></i><strong>Dibalas:</strong><br>
                                <span style="font-size: 0.75rem;">Sudah sesuai ✅</span>
                            </div>
                        @else
                            <span class="text-muted small">
                                <i class="ti ti-minus me-1"></i>Tidak membalas
                            </span>
                        @endif
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-primary" onclick="showLogDetail({{ $log->id }})" title="Lihat Rincian Log">
                            <i class="ti ti-eye"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <div class="text-center my-4">
                            <div class="mb-3">
                                <i class="ti ti-history-off text-muted" style="font-size: 48px;"></i>
                            </div>
                            <h6 class="text-dark fw-bold">Belum Ada Riwayat Log Bot</h6>
                            <p class="text-muted small mb-0">Belum ada pesan webhook yang diterima dari bot Telegram atau sesuai filter saat ini.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($logs->hasPages())
    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">Menampilkan {{ $logs->firstItem() }} - {{ $logs->lastItem() }} dari {{ $logs->total() }} log</small>
        <div>
            {{ $logs->links() }}
        </div>
    </div>
@endif
