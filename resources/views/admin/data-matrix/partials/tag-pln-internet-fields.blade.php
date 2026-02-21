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
</div>
