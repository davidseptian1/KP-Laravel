@php
    $status = strtolower((string) ($latestActivityItem->status ?? 'pending'));
    $statusBadgeClass = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : ($status === 'selesai' ? 'primary' : ($status === 'selesai_belum_lunas' ? 'secondary' : 'warning')));
    $statusLabel = $status === 'selesai_belum_lunas' ? 'Selesai (Belum Lunas)' : ucfirst($status);
    $entityLabel = $entityLabel ?? 'request deposit';
@endphp

<div class="card border-0 bg-light mb-3">
    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-2">
        <div>
            <div class="fw-semibold mb-1"><i class="ti ti-activity me-1"></i>Aktivitas Terbaru</div>
            @if (!empty($latestActivityItem))
                <small class="text-muted d-block">
                    {{ $latestActivityItem->created_at?->format('d/m/Y H:i') ?? '-' }} · {{ $latestActivityItem->nama_supplier ?? '-' }} · {{ $latestActivityItem->server ?? '-' }}
                </small>
                <small class="text-muted d-block">
                    Nominal: Rp {{ number_format((float) ($latestActivityItem->nominal ?? 0), 0, ',', '.') }}
                </small>
            @else
                <small class="text-muted d-block">Belum ada aktivitas {{ $entityLabel }} pada filter ini.</small>
            @endif
        </div>
        @if (!empty($latestActivityItem))
            <div>
                <span class="badge bg-{{ $statusBadgeClass }}">
                    {{ $statusLabel }}
                </span>
            </div>
        @endif
    </div>
</div>
