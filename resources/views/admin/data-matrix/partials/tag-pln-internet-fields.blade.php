<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control" value="{{ old('nama', $item->nama ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Nomor PLN & Internet</label>
        <input type="text" name="nomor_pln_internet" class="form-control" value="{{ old('nomor_pln_internet', $item->nomor_pln_internet ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Atas Nama</label>
        <input type="text" name="atas_nama" class="form-control" value="{{ old('atas_nama', $item->atas_nama ?? '') }}" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Bank</label>
        <input type="text" name="bank" class="form-control" value="{{ old('bank', $item->bank ?? '') }}">
    </div>
    <div class="col-md-8">
        <label class="form-label">Keterangan</label>
        <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan', $item->keterangan ?? '') }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Januari 2026 - Tagihan</label>
        <input type="number" step="0.01" min="0" name="periode_januari_2026_tagihan" class="form-control" value="{{ old('periode_januari_2026_tagihan', $item->periode_januari_2026_tagihan ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Januari 2026 - Tanggal Payment</label>
        <input type="date" name="periode_januari_2026_tanggal_payment" class="form-control" value="{{ old('periode_januari_2026_tanggal_payment', isset($item?->periode_januari_2026_tanggal_payment) ? $item->periode_januari_2026_tanggal_payment->format('Y-m-d') : '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Februari 2026 - Tagihan</label>
        <input type="number" step="0.01" min="0" name="periode_februari_2026_tagihan" class="form-control" value="{{ old('periode_februari_2026_tagihan', $item->periode_februari_2026_tagihan ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Februari 2026 - Tanggal Payment</label>
        <input type="date" name="periode_februari_2026_tanggal_payment" class="form-control" value="{{ old('periode_februari_2026_tanggal_payment', isset($item?->periode_februari_2026_tanggal_payment) ? $item->periode_februari_2026_tanggal_payment->format('Y-m-d') : '') }}">
    </div>
</div>
