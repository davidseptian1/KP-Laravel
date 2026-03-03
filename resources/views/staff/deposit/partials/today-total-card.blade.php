@php
    $totalNominal = (float) ($todayDepositSummary->total_nominal ?? 0);
    $totalRequest = (int) ($todayDepositSummary->total_request ?? 0);
@endphp

<div class="card border-0 bg-warning-subtle mb-3">
    <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 py-2">
        <div>
            <div class="fw-semibold text-warning-emphasis mb-1"><i class="ti ti-report-money me-1"></i>Total Deposit Hari Ini</div>
            <small class="text-muted d-block">Jumlah request deposit hari ini: {{ number_format($totalRequest, 0, ',', '.') }}</small>
        </div>
        <div class="text-md-end">
            <div class="fw-bold fs-5 text-warning-emphasis mb-0">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
        </div>
    </div>
</div>
