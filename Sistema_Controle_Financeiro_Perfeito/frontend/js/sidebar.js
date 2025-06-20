/**
 * Sidebar navigation functionality
 * Handles responsive sidebar behavior for Sistema de Controle Financeiro
 */

document.addEventListener('DOMContentLoaded', function () {
    // Get sidebar elements
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');
    const toggleButtons = document.querySelectorAll('.sidebar-toggle');

    // If sidebar doesn't exist, exit
    if (!sidebar) return;

    // Create backdrop for mobile
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    document.body.appendChild(backdrop);

    // Function to toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('show');

        // Handle body scroll when sidebar is open on mobile
        if (sidebar.classList.contains('show')) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    }

    // Add click event to toggle buttons
    toggleButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            toggleSidebar();
        });
    });

    // Close sidebar when clicking outside on mobile
    backdrop.addEventListener('click', function () {
        if (sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    });

    // Close sidebar when clicking a link on mobile
    const sidebarLinks = sidebar.querySelectorAll('a:not(.dropdown-toggle)');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function () {
            if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
                toggleSidebar();
            }
        });
    });

    // Handle window resize
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    // Special handling for tablets
    if ('ontouchstart' in window) {
        sidebar.addEventListener('touchstart', function (e) {
            // Allow scrolling within sidebar
            e.stopPropagation();
        });
    }

    // Collapse sidebar on load for mobile
    if (window.innerWidth < 992) {
        sidebar.classList.remove('show');
    }
});