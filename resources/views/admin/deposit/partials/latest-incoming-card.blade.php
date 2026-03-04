@php
    $color = trim((string) ($latestIncomingServerColor ?? 'primary'));
    $allowed = ['primary', 'success', 'info', 'warning', 'danger', 'secondary'];
    if (!in_array($color, $allowed, true)) {
        $color = 'primary';
    }
@endphp

<div class="card border-0 bg-{{ $color }}-subtle mb-3">
    <div class="card-body py-2 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold text-{{ $color }} mb-0"><i class="ti ti-clock-hour-4 me-1"></i>Data Baru Masuk Terbaru</div>
            <small class="text-muted">Server: <strong>{{ $latestIncomingServer ?: '-' }}</strong></small>
        </div>
        <div class="text-end">
            <div class="fw-bold text-{{ $color }}" id="latestIncomingLabel">
                {{ $latestIncomingAt ? \Carbon\Carbon::parse($latestIncomingAt)->format('d/m/Y H:i:s') : '-' }}
            </div>
        </div>
    </div>
</div>
