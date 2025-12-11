<!-- Modal -->
<div class="modal fade" id="modalMinusanDestroy{{ $item->id }}" tabindex="-1" aria-labelledby="modalLabel{{ $item->id }}"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="modalLabel{{ $item->id }}"><i class="ti ti-alert-triangle me-2"></i>Konfirmasi Hapus Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body">
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="ti ti-alert-circle me-2"></i>
                    <div>
                        Apakah Anda yakin ingin menghapus data transaksi ini?
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <tr>
                            <th width="35%">Tanggal</th>
                            <td>{{ $item->tanggal }}</td>
                        </tr>
                        <tr>
                            <th>Server</th>
                            <td>{{ $item->server }}</td>
                        </tr>
                        <tr>
                            <th>Nama</th>
                            <td>{{ $item->nama }}</td>
                        </tr>
                        <tr>
                            <th>SPL</th>
                            <td>{{ $item->spl }}</td>
                        </tr>
                        <tr>
                            <th>Produk</th>
                            <td>{{ $item->produk }}</td>
                        </tr>
                        <tr>
                            <th>Nomor</th>
                            <td>{{ $item->nomor }}</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Qty</th>
                            <td>{{ $item->qty }}</td>
                        </tr>
                        <tr>
                            <th>Total/Org</th>
                            <td>Rp {{ number_format($item->total_per_orang, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <th>Keterangan</th>
                            <td><span class="badge bg-light-{{ $item->keterangan == 'Dialihkan' ? 'warning' : 'success' }}">{{ $item->keterangan }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Batal
                </button>

                <form action="{{ route('minusanDestroy', $item->id)}}" method="post" class="d-inline">
                  @csrf
                  @method('delete') 
                    <button type="submit" class="btn btn-danger">
                        <i class="ti ti-trash me-1"></i> Hapus Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
