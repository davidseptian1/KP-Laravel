<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Nomor</label>
        <input type="text" name="nomor" class="form-control" value="{{ old('nomor', $item->nomor ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Atas Nama</label>
        <input type="text" name="atas_nama" class="form-control" value="{{ old('atas_nama', $item->atas_nama ?? '') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Chip</label>
        <input type="text" name="chip" class="form-control" value="{{ old('chip', $item->chip ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        @php($statusValue = old('status', $item->status ?? 'Aktif'))
        <select name="status" class="form-select" required>
            @foreach (['Aktif', 'Aktif (Terblokir)', 'Tidak Digunakan', 'Lunas'] as $status)
                <option value="{{ $status }}" {{ $statusValue === $status ? 'selected' : '' }}>{{ $status }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Keterangan</label>
        <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan', $item->keterangan ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Bank</label>
        <input type="text" name="bank" class="form-control" value="{{ old('bank', $item->bank ?? '') }}">
    </div>
</div>
