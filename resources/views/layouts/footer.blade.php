        <!-- jQuery (needed for DataTables) -->
        <script src="{{ asset('sbadmin2/vendor/jquery/jquery.min.js') }}"></script>

        <!-- Hide Preloader Immediately -->
        <script>
            window.addEventListener('load', function() {
                const loader = document.querySelector('.loader-bg');
                if (loader) {
                    loader.style.transition = 'opacity 0.3s';
                    loader.style.opacity = '0';
                    setTimeout(() => {
                        loader.style.display = 'none';
                    }, 300);
                }
            });
        </script>

        <!-- Popper JS -->
        <script src="{{ asset('mantis/js/plugins/popper.min.js') }}"></script>

        <!-- Bootstrap 5 -->
        <script src="{{ asset('mantis/js/plugins/bootstrap.min.js') }}"></script>

        <script src="{{ asset('mantis/js/plugins/simplebar.min.js') }}"></script>
        <script src="{{ asset('mantis/js/pcoded.js') }}"></script>
        <script src="{{ asset('mantis/js/plugins/feather.min.js') }}"></script>


        Custom JS
        <script src="{{ asset('mantis/js/custom.js') }}"></script>

        <!-- Sidebar Toggle Fix - Inline Script -->
        <script>
            (function() {
                console.log('=== Sidebar Toggle Script Loaded ===');

                function initSidebarToggle() {
                    const sidebarHide = document.getElementById('sidebar-hide');
                    const mobileCollapse = document.getElementById('mobile-collapse');
                    const sidebar = document.querySelector('.pc-sidebar');

                    console.log('Elements found:', {
                        sidebarHide: !!sidebarHide,
                        mobileCollapse: !!mobileCollapse,
                        sidebar: !!sidebar
                    });

                    // Desktop toggle
                    if (sidebarHide) {
                        // Remove any existing listeners
                        const newSidebarHide = sidebarHide.cloneNode(true);
                        sidebarHide.parentNode.replaceChild(newSidebarHide, sidebarHide);

                        newSidebarHide.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('=== DESKTOP TOGGLE CLICKED ===');

                            document.body.classList.toggle('pc-sidebar-hide');

                            const isHidden = document.body.classList.contains('pc-sidebar-hide');
                            console.log('Sidebar is now:', isHidden ? 'HIDDEN' : 'VISIBLE');
                            localStorage.setItem('sidebarState', isHidden ? 'hidden' : 'visible');
                        });

                        console.log('Desktop toggle listener attached');
                    }

                    // Mobile toggle
                    if (mobileCollapse && sidebar) {
                        const newMobileCollapse = mobileCollapse.cloneNode(true);
                        mobileCollapse.parentNode.replaceChild(newMobileCollapse, mobileCollapse);

                        newMobileCollapse.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('=== MOBILE TOGGLE CLICKED ===');

                            sidebar.classList.toggle('mob-sidebar-active');

                            let overlay = document.querySelector('.pc-sidebar-overlay');
                            if (!overlay) {
                                overlay = document.createElement('div');
                                overlay.className = 'pc-sidebar-overlay';
                                document.body.appendChild(overlay);

                                overlay.addEventListener('click', function() {
                                    sidebar.classList.remove('mob-sidebar-active');
                                    overlay.classList.remove('show');
                                });
                            }

                            if (sidebar.classList.contains('mob-sidebar-active')) {
                                setTimeout(() => overlay.classList.add('show'), 10);
                            } else {
                                overlay.classList.remove('show');
                            }
                        });

                        console.log('Mobile toggle listener attached');
                    }

                    // Restore state
                    const savedState = localStorage.getItem('sidebarState');
                    if (savedState === 'hidden') {
                        document.body.classList.add('pc-sidebar-hide');
                    }
                }

                // Initialize immediately if DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initSidebarToggle);
                } else {
                    initSidebarToggle();
                }

                // Also try after a short delay as fallback
                setTimeout(initSidebarToggle, 100);
            })();
        </script>

        <!-- Profile Dropdown Script -->
        <script>
            (function() {
                console.log('=== Profile Dropdown Script Loaded ===');

                function initProfileDropdown() {
                    const profileToggle = document.getElementById('dropdownProfile');
                    const profileMenu = document.getElementById('dropdownProfileMenu');

                    console.log('Profile elements:', {
                        toggle: !!profileToggle,
                        menu: !!profileMenu
                    });

                    if (!profileToggle || !profileMenu) {
                        console.error('Profile dropdown elements not found!');
                        return;
                    }

                    // Remove any existing listeners by cloning
                    const newToggle = profileToggle.cloneNode(true);
                    profileToggle.parentNode.replaceChild(newToggle, profileToggle);

                    newToggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('=== PROFILE CLICKED ===');

                        const isShown = profileMenu.classList.contains('show');

                        // Close all other dropdowns
                        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                            if (menu !== profileMenu) {
                                menu.classList.remove('show');
                            }
                        });

                        // Toggle this dropdown
                        if (!isShown) {
                            profileMenu.classList.add('show');
                            newToggle.setAttribute('aria-expanded', 'true');
                            console.log('Dropdown OPENED');
                        } else {
                            profileMenu.classList.remove('show');
                            newToggle.setAttribute('aria-expanded', 'false');
                            console.log('Dropdown CLOSED');
                        }
                    });

                    // Close when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!newToggle.contains(e.target) && !profileMenu.contains(e.target)) {
                            profileMenu.classList.remove('show');
                            newToggle.setAttribute('aria-expanded', 'false');
                        }
                    });

                    // Close when pressing Escape
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape') {
                            profileMenu.classList.remove('show');
                            newToggle.setAttribute('aria-expanded', 'false');
                        }
                    });

                    console.log('Profile dropdown initialized successfully');
                }

                // Initialize
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initProfileDropdown);
                } else {
                    initProfileDropdown();
                }

                setTimeout(initProfileDropdown, 100);
            })();
        </script>

        <!-- DataTables -->
        <script src="{{ asset('sbadmin2/vendor/datatables/jquery.dataTables.min.js') }}"></script>
        <script src="{{ asset('sbadmin2/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>

        <!-- SweetAlert2 -->
        <script src="{{ asset('sweetalert2/dist/sweetalert2.all.min.js') }}"></script>

        <script>
            // Initialize DataTables with modern styling
            $(document).ready(function() {
                if ($('#dataTable').length) {
                    $('#dataTable').DataTable({
                        "language": {
                            "lengthMenu": "Tampilkan _MENU_ data per halaman",
                            "zeroRecords": "Data tidak ditemukan",
                            "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                            "infoEmpty": "Tidak ada data tersedia",
                            "infoFiltered": "(difilter dari _MAX_ total data)",
                            "search": "Cari:",
                            "paginate": {
                                "first": "Pertama",
                                "last": "Terakhir",
                                "next": "Selanjutnya",
                                "previous": "Sebelumnya"
                            }
                        },
                        "pageLength": 10,
                        "responsive": true
                    });
                }
            });
        </script>

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        {{-- ================= GLOBAL NOTIFICATION ================= --}}

        @if(session('login_success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Welcome 👋',
                text: @json(session('login_success')),
                confirmButtonText: 'Masuk Dashboard',
                confirmButtonColor: '#2563EB'
            });
        </script>
        @endif

        @if(session('error'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: @json(session('error')),
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        </script>
        @endif

        @if(session('warning'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: @json(session('warning')),
                showConfirmButton: false,
                timer: 3500
            });
        </script>
        @endif

        @if(session('success'))
        <script>
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: @json(session('success')),
                showConfirmButton: false,
                timer: 3000
            });
        </script>
        @endif



        </body>

        </html>