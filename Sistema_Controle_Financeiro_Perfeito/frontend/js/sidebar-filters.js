/**
 * Scripts for handling sidebar category filtering
 * For Sistema de Controle Financeiro
 */

document.addEventListener('DOMContentLoaded', function () {
    // Listen for clicks on category links in the sidebar
    const sidebarContainer = document.getElementById('tiposNavegacao');

    if (sidebarContainer) {
        sidebarContainer.addEventListener('click', function (e) {
            const target = e.target.closest('a');

            if (target) {
                // Check if this is a category link (not the "All" link)
                if (target.dataset.tipo) {
                    e.preventDefault();

                    // Get the category type from data attribute
                    const tipo = target.dataset.tipo;

                    // Apply filters
                    estado.filtros.tipo = tipo;
                    estado.paginaAtual = 1;

                    // Update visual indicators
                    document.getElementById('viewTitle').textContent = tipo;
                    document.getElementById('viewHeader').classList.remove('d-none');

                    // Reload data with the filter
                    carregarMovimentacoes();
                    exibirFiltrosAplicados();

                    // Close sidebar on mobile
                    if (window.innerWidth < 992) {
                        const sidebar = document.querySelector('.sidebar');
                        if (sidebar.classList.contains('active')) {
                            document.getElementById('sidebar-toggle').click();
                        }
                    }
                }
                // If it's the "All" link, clear filters
                else if (target.classList.contains('view-all')) {
                    e.preventDefault();

                    // Clear type filter but keep other filters
                    estado.filtros.tipo = '';
                    estado.paginaAtual = 1;

                    // Hide view header
                    document.getElementById('viewHeader').classList.add('d-none');

                    // Reload data
                    carregarMovimentacoes();
                    exibirFiltrosAplicados();

                    // Close sidebar on mobile
                    if (window.innerWidth < 992) {
                        const sidebar = document.querySelector('.sidebar');
                        if (sidebar.classList.contains('active')) {
                            document.getElementById('sidebar-toggle').click();
                        }
                    }
                }
            }
        });
    }
});

/**
 * This function updates the navigation sidebar with active categories
 * and adds data attributes for filtering
 */
function updateSidebarNavigation() {
    const navLinks = document.getElementById('tiposNavegacao');
    if (!navLinks) return;

    // Get all li elements
    const listItems = navLinks.querySelectorAll('li');

    // For each li, add data attributes to the link
    listItems.forEach(li => {
        const link = li.querySelector('a');
        if (link) {
            // Skip the "All" link
            if (link.textContent.trim() !== 'Visão Geral') {
                // Get category name from the link text or existing data
                const categoryText = link.querySelector('span') ?
                    link.querySelector('span').textContent.trim() : link.textContent.trim();

                // Add data attribute for filtering
                link.setAttribute('data-tipo', categoryText);

                // Add class to identify as category link
                link.classList.add('category-link');
            } else {
                link.classList.add('view-all');
            }
        }
    });

    // Add click event listeners to data-tipo links if not already added
    const categoryLinks = navLinks.querySelectorAll('[data-tipo]');
    categoryLinks.forEach(link => {
        // Check if URL has view parameter matching this category
        const urlParams = new URLSearchParams(window.location.search);
        const viewType = urlParams.get('view');

        if (viewType && viewType === link.dataset.tipo) {
            // Trigger an automatic click
            setTimeout(() => {
                link.click();
            }, 100);
        }
    });
}