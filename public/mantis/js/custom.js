// Custom JavaScript for e-SMT Application

document.addEventListener('DOMContentLoaded', function() {
    
    console.log('Custom JS Loaded');
    
    // Hide preloader after page load
    const loaderBg = document.querySelector('.loader-bg');
    if (loaderBg) {
        setTimeout(() => {
            loaderBg.style.display = 'none';
        }, 500);
    }

    // Sidebar Toggle for Desktop & Mobile
    const sidebarHide = document.getElementById('sidebar-hide');
    const mobileCollapse = document.getElementById('mobile-collapse');
    const sidebar = document.querySelector('.pc-sidebar');
    
    console.log('Sidebar Hide Button:', sidebarHide);
    console.log('Mobile Collapse Button:', mobileCollapse);
    console.log('Sidebar Element:', sidebar);
    
    // Desktop sidebar toggle
    if (sidebarHide) {
        console.log('Adding click listener to desktop toggle');
        sidebarHide.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Desktop toggle clicked!');
            document.body.classList.toggle('pc-sidebar-hide');
            
            // Save state to localStorage
            if (document.body.classList.contains('pc-sidebar-hide')) {
                localStorage.setItem('sidebarState', 'hidden');
                console.log('Sidebar hidden');
            } else {
                localStorage.setItem('sidebarState', 'visible');
                console.log('Sidebar visible');
            }
        });
        
        // Restore sidebar state from localStorage
        const savedState = localStorage.getItem('sidebarState');
        if (savedState === 'hidden') {
            document.body.classList.add('pc-sidebar-hide');
        }
    } else {
        console.error('Desktop toggle button not found!');
    }
    
    // Mobile sidebar toggle
    if (mobileCollapse && sidebar) {
        console.log('Adding click listener to mobile toggle');
        mobileCollapse.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Mobile toggle clicked!');
            sidebar.classList.toggle('mob-sidebar-active');
            
            // Add overlay
            let overlay = document.querySelector('.pc-sidebar-overlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'pc-sidebar-overlay';
                document.body.appendChild(overlay);
                
                overlay.addEventListener('click', function() {
                    console.log('Overlay clicked');
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
    }

    // Scroll to Top Button
    const btnToTop = document.querySelector('.btn-to-top');
    if (btnToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                btnToTop.style.display = 'flex';
            } else {
                btnToTop.style.display = 'none';
            }
        });

        btnToTop.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    // Initialize Feather Icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Add active class to current menu item
    const currentPath = window.location.pathname;
    document.querySelectorAll('.pc-navbar .pc-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.closest('.pc-item').classList.add('active');
        }
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    // Auto-close alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (typeof bootstrap !== 'undefined') {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                alert.style.display = 'none';
            }
        }, 5000);
    });

    // Tooltips initialization
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined') {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Format currency inputs
    const currencyInputs = document.querySelectorAll('.currency-input');
    currencyInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = new Intl.NumberFormat('id-ID').format(value);
        });
    });

    // Confirm before delete
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                e.preventDefault();
            }
        });
    });
});

// Format number to Indonesian currency
function formatCurrency(number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
}

// Show toast notification
function showToast(message, type = 'success') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    // Create toast container if doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = toastContainer.lastElementChild;
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
    
    // Remove toast after hidden
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}
