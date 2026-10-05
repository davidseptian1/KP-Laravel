<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light text-uppercase fs-7 text-muted">
            <tr>
                <th style="width: 50px;" class="text-center">No</th>
                <th style="width: 150px;">Waktu Masuk</th>
                <th style="width: 150px;">Sumber & IP</th>
                <th style="width: 130px;">Tipe Update</th>
                <th style="width: 160px;">Chat / Grup</th>
                <th style="width: 130px;">Pengirim</th>
                <th>Ringkasan Pesan / Payload</th>
                <th style="width: 130px;">Status</th>
                <th style="width: 90px;" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rawLogs as $index => $raw)
                <tr>
                    <td class="text-center text-muted fw-semibold">
                        {{ $rawLogs->firstItem() + $index }}
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-semibold text-dark">{{ $raw->created_at ? $raw->created_at->format('d/m/Y') : '-' }}</span>
                            <small class="text-muted"><i class="ti ti-clock me-1"></i>{{ $raw->created_at ? $raw->created_at->format('H:i:s') : '-' }}</small>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ $raw->created_at ? $raw->created_at->diffForHumans() : '' }}</small>
                        </div>
                    </td>
                    <td>
                        <div>
                            @if($raw->source === 'direct_sms')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle mb-1">
                                    <i class="ti ti-device-mobile me-1"></i>Direct SMS
                                </span>
                            @elseif($raw->source === 'simulated_test')
                                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle mb-1">
                                    <i class="ti ti-flame me-1"></i>Simulasi Tes
                                </span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle mb-1">
                                    <i class="ti ti-brand-telegram me-1"></i>Telegram
                                </span>
                            @endif
                            <div class="small text-muted font-monospace" style="font-size: 0.75rem;">
                                {{ $raw->ip_address ?: '-' }}
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-dark bg-opacity-10 text-dark border font-monospace" style="font-size: 0.75rem;">
                            {{ $raw->update_type ?: 'unknown' }}
                        </span>
                        @if($raw->update_id)
                            <div class="small text-muted font-monospace" style="font-size: 0.7rem;">
                                ID: {{ $raw->update_id }}
                            </div>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark text-truncate" style="max-width: 150px;" title="{{ $raw->chat_title }}">
                                {{ $raw->chat_title ?: '-' }}
                            </span>
                            @if($raw->chat_id)
                                <small class="text-muted font-monospace" style="font-size: 0.7rem;">
                                    {{ $raw->chat_id }}
                                </small>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="text-truncate d-inline-block" style="max-width: 120px;" title="{{ $raw->sender_name }}">
                            {{ $raw->sender_name ?: '-' }}
                        </span>
                    </td>
                    <td>
                        <div class="p-2 bg-light rounded-2 border border-light-subtle small font-monospace text-truncate" style="max-width: 380px;" title="{{ $raw->summary }}">
                            {{ $raw->summary ?: '-' }}
                        </div>
                        @if($raw->notes)
                            <small class="text-muted d-block mt-1 fst-italic" style="font-size: 0.75rem;">
                                <i class="ti ti-info-circle me-1"></i>{{ $raw->notes }}
                            </small>
                        @endif
                    </td>
                    <td>
                        {!! $raw->status_badge_html !!}
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-dark shadow-sm px-2 py-1" onclick="showRawWebhookDetail({{ $raw->id }})" title="Lihat Payload JSON Mentah">
                            <i class="ti ti-code me-1"></i>JSON
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <div class="rounded-circle bg-light p-3 mb-3 text-muted">
                                <i class="ti ti-inbox-off fs-1"></i>
                            </div>
                            <h6 class="fw-bold text-muted mb-1">Belum Ada Panggilan Webhook Masuk</h6>
                            <p class="text-muted small mb-3" style="max-width: 480px;">
                                Belum ada request HTTP yang diterima oleh endpoint webhook. Klik tombol <strong>"Tes Webhook"</strong> di atas untuk memvalidasi pencatatan logger.
                            </p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($rawLogs->hasPages())
    <div class="d-flex justify-content-between align-items-center flex-wrap px-4 py-3 border-top bg-light">
        <small class="text-muted">
            Menampilkan {{ $rawLogs->firstItem() ?? 0 }} - {{ $rawLogs->lastItem() ?? 0 }} dari {{ $rawLogs->total() }} raw logs
        </small>
        <div>
            {{ $rawLogs->links('pagination::bootstrap-5') }}
        </div>
    </div>
@endif
