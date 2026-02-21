<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control" value="{{ old('nama', $item->nama ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">No Rekening/VA</label>
        <input type="text" name="no_rekening_va" class="form-control" value="{{ old('no_rekening_va', $item->no_rekening_va ?? '') }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Jumlah</label>
        <input type="number" step="0.01" min="0" name="jumlah" class="form-control" value="{{ old('jumlah', $item->jumlah ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Bank</label>
        <input type="text" name="bank" class="form-control" value="{{ old('bank', $item->bank ?? '') }}">
    </div>
    <div class="col-md-4">
        <label class="form-label">Keterangan</label>
        <input type="text" name="keterangan" class="form-control" value="{{ old('keterangan', $item->keterangan ?? '') }}">
    </div>
</div>
