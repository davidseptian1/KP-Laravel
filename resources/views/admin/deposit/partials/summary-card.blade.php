<div class="card border-0 bg-light mb-3">
    <div class="card-body py-2">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="fw-semibold">
                <i class="ti ti-report-money me-1"></i>Total Data Sesuai Filter
            </div>
            <div class="d-flex flex-wrap gap-3">
                <div>
                    <small class="text-muted d-block">Total Request</small>
                    <span class="fw-semibold">{{ number_format((int) ($monitoringSummary->total_request ?? 0), 0, ',', '.') }}</span>
                </div>
                <div>
                    <small class="text-muted d-block">Total Nominal</small>
                    <span class="fw-semibold text-primary">Rp {{ number_format((float) ($monitoringSummary->total_nominal ?? 0), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if (!empty($monitoringByBank) && count($monitoringByBank) > 0)
            <div class="mt-2 pt-2 border-top">
                <div class="small text-muted mb-1">Total per Bank (sesuai filter)</div>
                @foreach ($monitoringByBank as $bankRow)
                    <div class="small d-flex flex-wrap justify-content-between gap-2">
                        <span>- Bank {{ $bankRow->bank_name ?? '-' }} ({{ number_format((int) ($bankRow->total_request ?? 0), 0, ',', '.') }} trx)</span>
                        <span class="fw-semibold">Rp {{ number_format((float) ($bankRow->total_nominal ?? 0), 0, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
