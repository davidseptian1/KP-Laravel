<div class="card border-0 bg-primary-subtle mb-3">
    <div class="card-body py-2 d-flex justify-content-between align-items-center">
        <div>
            <div class="fw-semibold text-primary mb-0"><i class="ti ti-clock-hour-4 me-1"></i>Data Baru Masuk Terbaru</div>
            <small class="text-muted">Pantau waktu update masuk terbaru secara real-time.</small>
        </div>
        <div class="text-end">
            <div class="fw-bold text-primary" id="latestIncomingLabel">
                {{ $latestIncomingAt ? \Carbon\Carbon::parse($latestIncomingAt)->format('d/m/Y H:i:s') : '-' }}
            </div>
        </div>
    </div>
</div>
