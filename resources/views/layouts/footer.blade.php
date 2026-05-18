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

        <script>
            (function () {
                function normalizeNominalValue(raw) {
                    if (!raw) {
                        return '';
                    }

                    let nominal = raw.trim();
                    nominal = nominal.replace(/\s+/g, '');

                    const hasComma = nominal.includes(',');
                    const hasDot = nominal.includes('.');

                    if (hasComma && hasDot) {
                        nominal = nominal.replace(/\./g, '');
                        nominal = nominal.replace(/,/g, '.');
                    } else if (hasComma) {
                        nominal = nominal.replace(/,/g, '.');
                    } else if (hasDot) {
                        const parts = nominal.split('.');
                        if (parts.length > 2) {
                            nominal = parts.join('');
                        } else if (parts.length === 2 && parts[1].length === 3) {
                            nominal = parts.join('');
                        }
                    }

                    nominal = nominal.replace(/[^0-9.]/g, '');
                    if (nominal === '' || nominal === '.') {
                        return '';
                    }

                    return nominal;
                }

                function numberToWords(n) {
                    const units = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan'];

                    if (n === 0) {
                        return 'nol';
                    }

                    function inWords(value) {
                        if (value < 10) {
                            return units[value];
                        }
                        if (value < 20) {
                            if (value === 10) return 'sepuluh';
                            if (value === 11) return 'sebelas';
                            return units[value - 10] + ' belas';
                        }
                        if (value < 100) {
                            const tens = Math.floor(value / 10);
                            const rest = value % 10;
                            return units[tens] + ' puluh' + (rest ? ' ' + inWords(rest) : '');
                        }
                        if (value < 200) {
                            return 'seratus' + (value > 100 ? ' ' + inWords(value - 100) : '');
                        }
                        if (value < 1000) {
                            const hundreds = Math.floor(value / 100);
                            const rest = value % 100;
                            return inWords(hundreds) + ' ratus' + (rest ? ' ' + inWords(rest) : '');
                        }
                        if (value < 2000) {
                            return 'seribu' + (value > 1000 ? ' ' + inWords(value - 1000) : '');
                        }
                        if (value < 1000000) {
                            const thousands = Math.floor(value / 1000);
                            const rest = value % 1000;
                            return inWords(thousands) + ' ribu' + (rest ? ' ' + inWords(rest) : '');
                        }
                        if (value < 1000000000) {
                            const millions = Math.floor(value / 1000000);
                            const rest = value % 1000000;
                            return inWords(millions) + ' juta' + (rest ? ' ' + inWords(rest) : '');
                        }
                        if (value < 1000000000000) {
                            const billions = Math.floor(value / 1000000000);
                            const rest = value % 1000000000;
                            return inWords(billions) + ' miliar' + (rest ? ' ' + inWords(rest) : '');
                        }
                        return '';
                    }

                    return inWords(n).trim();
                }

                function getNominalWords(rawValue) {
                    const normalized = normalizeNominalValue(rawValue);
                    if (!normalized) {
                        return '';
                    }

                    const parts = normalized.split('.');
                    const integerPart = parseInt(parts[0] || '0', 10);
                    const decimalPart = parts[1] || '';

                    if (Number.isNaN(integerPart)) {
                        return '';
                    }

                    const words = numberToWords(integerPart);
                    if (!words) {
                        return '';
                    }

                    if (decimalPart && /[^0]/.test(decimalPart)) {
                        const decimals = decimalPart.split('').map(function (digit) {
                            const wordsDigit = numberToWords(parseInt(digit, 10));
                            return wordsDigit || '';
                        }).filter(Boolean).join(' ');
                        return words + ' koma ' + decimals + ' rupiah';
                    }

                    return words + ' rupiah';
                }

                function findNominalHelperFor(input) {
                    let helper = null;
                    
                    const parent = input.parentElement;
                    if (parent) {
                        helper = parent.querySelector('.js-nominal-display');
                        if (helper) return helper;
                    }
                    
                    const container = input.closest('.mb-3, .col-md-6, .form-group, [class*="col-"]');
                    if (container) {
                        helper = container.querySelector('.js-nominal-display');
                        if (helper) return helper;
                    }
                    
                    const next = input.nextElementSibling;
                    if (next && next.classList && next.classList.contains('js-nominal-display')) {
                        return next;
                    }
                    
                    const nextSibling = input.parentElement?.nextElementSibling;
                    if (nextSibling && nextSibling.classList && nextSibling.classList.contains('js-nominal-display')) {
                        return nextSibling;
                    }
                    
                    return null;
                }

                function updateNominalHelper(input) {
                    const helper = findNominalHelperFor(input);
                    if (!helper) {
                        console.warn('Helper not found for nominal input:', input);
                        return;
                    }
                    const value = input.value || input.getAttribute('value') || '';
                    const text = getNominalWords(value);
                    if (text) {
                        helper.textContent = 'Nominal dalam kata: ' + text;
                        helper.style.display = 'block';
                    } else {
                        helper.textContent = '';
                        helper.style.display = 'none';
                    }
                }

                function refreshNominalHelpers() {
                    document.querySelectorAll('input[name="nominal"]').forEach(function (input) {
                        updateNominalHelper(input);
                    });
                }

                document.addEventListener('DOMContentLoaded', function () {
                    setTimeout(function () {
                        refreshNominalHelpers();
                    }, 100);
                    
                    document.querySelectorAll('input[name="nominal"]').forEach(function (input) {
                        input.addEventListener('input', function () {
                            updateNominalHelper(input);
                        });
                    });
                    
                    document.querySelectorAll('.modal').forEach(function (modalEl) {
                        modalEl.addEventListener('shown.bs.modal', function () {
                            setTimeout(function () {
                                refreshNominalHelpers();
                            }, 50);
                            modalEl.querySelectorAll('input[name="nominal"]').forEach(function (input) {
                                const existingListener = input.__nominalListenerAttached;
                                if (!existingListener) {
                                    input.__nominalListenerAttached = true;
                                    input.addEventListener('input', function () {
                                        updateNominalHelper(input);
                                    });
                                }
                            });
                        });
                    });
                });
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

        <script>
            (function () {
                function initSidebarCategoryToggle() {
                    const sidebar = document.querySelector('.pc-sidebar .pc-navbar');
                    if (!sidebar) return;

                    const captions = Array.from(sidebar.querySelectorAll('.pc-item.pc-caption'));
                    captions.forEach((caption, index) => {
                        const label = caption.querySelector('label');
                        if (!label) return;

                        const key = 'sidebar_category_collapsed_' + (label.textContent || '').trim() + '_' + index;
                        const groupItems = [];
                        let next = caption.nextElementSibling;
                        while (next && !next.classList.contains('pc-caption')) {
                            if (next.classList.contains('pc-item')) {
                                groupItems.push(next);
                            }
                            next = next.nextElementSibling;
                        }

                        if (!groupItems.length) return;

                        caption.dataset.collapseKey = key;

                        const applyState = (collapsed) => {
                            caption.classList.toggle('is-collapsed', collapsed);
                            groupItems.forEach((item) => {
                                item.style.display = collapsed ? 'none' : '';
                            });
                        };

                        const saved = localStorage.getItem(key);
                        applyState(saved === '1');

                        if (!label.dataset.boundCollapse) {
                            label.dataset.boundCollapse = '1';
                            label.addEventListener('click', function () {
                                const isCollapsed = !caption.classList.contains('is-collapsed');
                                applyState(isCollapsed);
                                localStorage.setItem(key, isCollapsed ? '1' : '0');
                            });
                        }
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initSidebarCategoryToggle);
                } else {
                    initSidebarCategoryToggle();
                }

                setTimeout(initSidebarCategoryToggle, 100);
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



    @stack('scripts')

        </body>

        </html>