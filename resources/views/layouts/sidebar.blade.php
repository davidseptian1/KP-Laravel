<!-- [ Sidebar Menu ] start -->
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand text-primary">
                <!-- ========   Logo   ============ -->
                <div class="logo-icon">
                    <i class="ti ti-activity"></i>
                </div>
                <span class="b-title">e-SMT</span>
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">

                <!-- Dashboard -->
                <li class="pc-item {{ $menuDashboard ?? '' }}">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext">Dashboard</span>
                    </a>
                </li>

                @if (auth()->check() && in_array(auth()->user()->jabatan, ['Admin','Superadmin']))

                <li class="pc-item pc-caption">
                    <label>Data Management</label>
                </li>

                <!-- Data Minusan -->
                <li class="pc-item {{ $menuAdminMinusan ?? '' }}">
                    <a href="{{ route('minusan') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-database"></i></span>
                        <span class="pc-mtext">Data Minusan</span>
                    </a>
                </li>

                    <li class="pc-item pc-caption">
                        <label>Monitoring Manajemen</label>
                    </li>

                <!-- Reimburse (Admin) -->
                <li class="pc-item {{ $menuAdminReimburse ?? '' }}">
                    <a href="{{ route('admin.reimburse.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-receipt"></i></span>
                        <span class="pc-mtext">Reimburse Monitoring</span>
                    </a>
                </li>

                <!-- Pengajuan Data Monitoring (Admin) -->
                <li class="pc-item {{ $menuAdminDataRequest ?? '' }}">
                    <a href="{{ route('admin.data-request.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-file-text"></i></span>
                        <span class="pc-mtext">Monitoring<br>Pengajuan Data</span>
                    </a>
                </li>

                <!-- Peminjaman Barang Monitoring (Admin) -->
                <li class="pc-item {{ $menuAdminLoanRequest ?? '' }}">
                    <a href="{{ route('admin.loan-request.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-file-text"></i></span>
                        <span class="pc-mtext">Monitoring<br>Peminjaman Barang</span>
                    </a>
                </li>


                <!-- Deposit Monitoring (Admin) -->
                <li class="pc-item {{ $menuAdminDepositMonitoring ?? '' }}">
                    <a href="{{ route('admin.deposit.monitoring') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-list-check"></i></span>
                        <span class="pc-mtext">Monitoring Deposit</span>
                    </a>
                </li>

                <!-- Bon/Hutang Monitoring (Admin) -->
                <li class="pc-item {{ $menuAdminBonHutangMonitoring ?? '' }}">
                    <a href="{{ route('admin.deposit.monitoring-hutang') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-wallet"></i></span>
                        <span class="pc-mtext d-flex align-items-center justify-content-between w-100">
                            <span>Monitoring Bon/Hutang</span>
                            <span id="sidebar-hutang-counter"
                                  data-counter-url="{{ route('sidebar.hutang-belum-lunas-count') }}"
                                  class="badge rounded-pill {{ ($jumlahHutangBelumLunas ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2 px-2 py-1"
                                  style="font-size: 0.9rem; min-width: 34px; text-align: center;">
                                {{ $jumlahHutangBelumLunas ?? 0 }}
                            </span>
                        </span>
                    </a>
                </li>

                    <li class="pc-item pc-caption">
                        <label>Form Manajement</label>
                    </li>

                    <!-- Reimburse Form (Admin) -->
                    <li class="pc-item {{ $menuAdminReimburseForm ?? '' }}">
                        <a href="{{ route('admin.reimburse.forms') }}" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-link"></i></span>
                            <span class="pc-mtext">Reimburse Form</span>
                        </a>
                    </li>

                    <!-- Pengajuan Data Form (Admin) -->
                    <li class="pc-item {{ $menuAdminDataRequestForm ?? '' }}">
                        <a href="{{ route('admin.data-request.forms') }}" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-link"></i></span>
                            <span class="pc-mtext">Form Pengajuan Data</span>
                        </a>
                    </li>

                    <!-- Peminjaman Barang Form (Admin) -->
                    <li class="pc-item {{ $menuAdminLoanRequestForm ?? '' }}">
                        <a href="{{ route('admin.loan-request.forms') }}" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-link"></i></span>
                            <span class="pc-mtext">Form Peminjaman<br>Barang</span>
                        </a>
                    </li>

                    <!-- Deposit Form (Admin) -->
                    <li class="pc-item {{ $menuAdminDepositForm ?? '' }}">
                        <a href="{{ route('admin.deposit.forms') }}" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-clipboard"></i></span>
                            <span class="pc-mtext">Form Deposit</span>
                        </a>
                    </li>

                <li class="pc-item pc-caption">
                    <label>Data Matrix</label>
                </li>

                <li class="pc-item {{ $menuDataMatrixTagPascaBayar ?? '' }}">
                    <a href="{{ route('admin.data-matrix.tag-pasca-bayar') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-file-text"></i></span>
                        <span class="pc-mtext">Tag Nomor Pasca<br>Bayar</span>
                    </a>
                </li>

                <li class="pc-item {{ $menuDataMatrixTagPlnInternet ?? '' }}">
                    <a href="{{ route('admin.data-matrix.tag-pln-internet') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-bolt"></i></span>
                        <span class="pc-mtext">Tag PLN & Internet</span>
                    </a>
                </li>

                <li class="pc-item {{ $menuDataMatrixTagLainnya ?? '' }}">
                    <a href="{{ route('admin.data-matrix.tag-lainnya') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-list-check"></i></span>
                        <span class="pc-mtext">Tag lainnya</span>
                    </a>
                </li>

                <li class="pc-item pc-caption">
                    <label>Riwayat Tagihan Matrix</label>
                </li>

                <li class="pc-item {{ $menuDataMatrixHistoryTagPascaBayar ?? '' }}">
                    <a href="{{ route('admin.data-matrix.history.tag-pasca-bayar') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-history"></i></span>
                        <span class="pc-mtext">Riwayat Tagihan Nomor<br>Pasca Bayar</span>
                    </a>
                </li>

                <li class="pc-item {{ $menuDataMatrixHistoryTagPlnInternet ?? '' }}">
                    <a href="{{ route('admin.data-matrix.history.tag-pln-internet') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-history"></i></span>
                        <span class="pc-mtext">Riwayat Tagihan PLN & Internet</span>
                    </a>
                </li>

                <li class="pc-item {{ $menuDataMatrixHistoryTagLainnya ?? '' }}">
                    <a href="{{ route('admin.data-matrix.history.tag-lainnya') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-history"></i></span>
                        <span class="pc-mtext">Tagihan lainnya</span>
                    </a>
                </li>

                <li class="pc-item pc-caption">
                    <label>Notifikasi Tagihan</label>
                </li>

                <li class="pc-item {{ $menuNotifikasiSemuaTagihan ?? '' }}">
                    <a href="{{ route('admin.data-matrix.notifikasi.semua-tagihan') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-bell"></i></span>
                        <span class="pc-mtext">Semua Tagihan</span>
                    </a>
                </li>

                @endif

                @if (auth()->check() && in_array(auth()->user()->jabatan, ['Admin','HRD','Superadmin']))

                <li class="pc-item pc-caption">
                    <label>Reports</label>
                </li>

                <!-- Rekap Bulanan -->
                <li class="pc-item {{ $menuAdminRekap ?? '' }}">
                    <a href="{{ route('admin.rekap.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-calendar"></i></span>
                        <span class="pc-mtext">Rekap Bulanan</span>
                    </a>
                </li>

                <!-- Rekap Tilangan -->
                <li class="pc-item {{ $menuAdminTilangan ?? '' }}">
                    <a href="{{ route('admin.rekap.tilangan') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-file-text"></i></span>
                        <span class="pc-mtext">Rekap Tilangan</span>
                    </a>
                </li>

                <!-- ⭐ Report Khusus -->
                <li class="pc-item {{ $menuAdminReportKhusus ?? '' }}">
                    <a href="{{ route('admin.report.khusus.index') }}" class="pc-link">
                        <span class="pc-micon">
                            <i class="ti ti-alert-circle"></i>
                        </span>
                        <span class="pc-mtext">Report Khusus</span>
                    </a>
                </li>

                <!-- Analisis Deposit -->
                <li class="pc-item {{ $menuDepositAnalysis ?? '' }}">
                    <a href="{{ route('admin.deposit.analysis') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-chart-bar"></i></span>
                        <span class="pc-mtext">Analisis Deposit</span>
                    </a>
                </li>

                @endif

                @if (auth()->check() && in_array(auth()->user()->jabatan, ['Admin','Superadmin']))

                <!-- Analisis Transaksi -->
                <li class="pc-item {{ $menuTransaksi ?? '' }}">
                    <a href="{{ route('transaksi.analisis') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-chart-bar"></i></span>
                        <span class="pc-mtext">Analisis Transaksi</span>
                    </a>
                </li>

                @endif


                @if (auth()->check() && auth()->user()->jabatan === 'Superadmin')

                <li class="pc-item pc-caption">
                    <label>System</label>
                </li>

                <!-- User Management -->
                <li class="pc-item {{ $menuAdminUser ?? '' }}">
                    <a href="{{ route('user') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-users"></i></span>
                        <span class="pc-mtext">User Management</span>
                    </a>
                </li>

                <!-- Supplier Management -->
                <li class="pc-item {{ $menuAdminSupplier ?? '' }}">
                    <a href="{{ route('admin.supplier.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-building-store"></i></span>
                        <span class="pc-mtext">Supplier Manajemen</span>
                    </a>
                </li>

                <!-- Server Management -->
                <li class="pc-item {{ $menuAdminServer ?? '' }}">
                    <a href="{{ route('admin.server.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-server"></i></span>
                        <span class="pc-mtext">Server Manajemen</span>
                    </a>
                </li>

                <!-- Bank Management -->
                <li class="pc-item {{ $menuAdminBank ?? '' }}">
                    <a href="{{ route('admin.bank.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-building-bank"></i></span>
                        <span class="pc-mtext">Bank Manajemen</span>
                    </a>
                </li>

                <!-- Rek Manual Management -->
                <li class="pc-item {{ $menuAdminRekManual ?? '' }}">
                    <a href="{{ route('admin.rek-manual.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-credit-card"></i></span>
                        <span class="pc-mtext">Rek Manual Manajement</span>
                    </a>
                </li>

                <li class="pc-item {{ $menuSuperadminLogs ?? '' }}">
                    <a href="{{ route('superadmin.logs.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-history"></i></span>
                        <span class="pc-mtext">Lihat Logs</span>
                    </a>
                </li>
                <!-- PERSEDIAAN STOK 
                 1.pembelian stok, ngisi form persatu-->
                <li class="pc-item pc-caption">
                    <label>Persediaan Stok</label>
                </li>

                <!-- Persediaan Stok (Admin menu link) -->
                <li class="pc-item {{ $menuAdminPersediaan ?? '' }}">
                    <a href="{{ route('admin.persediaan-stok.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-package"></i></span>
                        <span class="pc-mtext">Persediaan Stok</span>
                    </a>
                </li>

                <li class="pc-item pc-caption">
                    <label>API Manajement</label>
                </li>

                <li class="pc-item {{ $menuApiManagement ?? '' }}">
                    <a href="{{ route('admin.api-management.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-api"></i></span>
                        <span class="pc-mtext">Semua Monitoring API</span>
                    </a>
                </li>

                @endif

                @if (auth()->check() && in_array(auth()->user()->jabatan, ['Staff','Superadmin']))

                <li class="pc-item pc-caption">
                    <label>Data</label>
                </li>

                <!-- Data Minusan (Staff) -->
                <li class="pc-item {{ $menuAdminMinusan ?? '' }}">
                    <a href="{{ route('minusan') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-database"></i></span>
                        <span class="pc-mtext">Data Minusan</span>
                    </a>
                </li>

                <!-- Reimburse (Staff) -->
                <li class="pc-item {{ $menuReimburse ?? '' }}">
                    <a href="{{ route('reimburse.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-receipt"></i></span>
                        <span class="pc-mtext">Reimburse</span>
                    </a>
                </li>

                <!-- Request Deposit (Staff) -->
                <li class="pc-item {{ $menuDepositRequest ?? '' }}">
                    <a href="{{ route('deposit.request.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-wallet"></i></span>
                        <span class="pc-mtext">Request Deposit</span>
                    </a>
                </li>

                <!-- Riwayat Hutang/Bon (Staff) -->
                <li class="pc-item {{ $menuRiwayatHutangBon ?? '' }}">
                    <a href="{{ route('deposit.request.hutang.index') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-history"></i></span>
                        <span class="pc-mtext">Riwayat Hutang/Bon</span>
                    </a>
                </li>

                <!-- Permintaan Persediaan (Staff) -->
                <li class="pc-item {{ $menuPersediaanRequest ?? '' }}">
                    <a href="{{ route('persediaan.create') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-box"></i></span>
                        <span class="pc-mtext">Permintaan Persediaan</span>
                    </a>
                </li>

                @endif

            </ul>

            <!-- Company Info Card -->
            <div class="card pc-user-card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="ti ti-building text-primary" style="font-size: 40px;"></i>
                    </div>
                    <h6 class="mb-1">PT. CHIKA MULYA</h6>
                    <p class="text-muted text-sm mb-0">MULTIMEDIA</p>
                </div>
            </div>
        </div>
    </div>
</nav>
<!-- [ Sidebar Menu ] end -->

@push('scripts')
<script>
    (function () {
        const badge = document.getElementById('sidebar-hutang-counter');
        if (!badge) {
            return;
        }

        const endpoint = badge.getAttribute('data-counter-url');
        if (!endpoint) {
            return;
        }

        const setValue = function (value) {
            const count = Number.isFinite(value) ? value : 0;
            badge.textContent = String(count);
            badge.classList.remove('bg-danger', 'bg-secondary');
            badge.classList.add(count > 0 ? 'bg-danger' : 'bg-secondary');
        };

        const fetchCount = function () {
            fetch(endpoint, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('failed');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    setValue(parseInt(payload.count, 10) || 0);
                })
                .catch(function () {
                });
        };

        fetchCount();
        setInterval(fetchCount, 5000);
    })();
</script>
@endpush