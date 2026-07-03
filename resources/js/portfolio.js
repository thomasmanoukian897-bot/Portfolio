const filterButtons = document.querySelectorAll('[data-portfolio-filter]');
const portfolioItems = document.querySelectorAll('[data-portfolio-category]');

if (filterButtons.length > 0 && portfolioItems.length > 0) {
    function setActiveFilter(button) {
        filterButtons.forEach((filterButton) => {
            const isActive = filterButton === button;

            filterButton.classList.toggle('bg-slate-900', isActive);
            filterButton.classList.toggle('text-white', isActive);
            filterButton.classList.toggle('glass-card', ! isActive);
            filterButton.classList.toggle('text-slate-600', ! isActive);
            filterButton.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function applyFilter(category) {
        portfolioItems.forEach((item) => {
            const itemCategory = item.getAttribute('data-portfolio-category');
            const isVisible = category === 'all' || itemCategory === category;

            item.classList.toggle('hidden', ! isVisible);
        });
    }

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const category = button.getAttribute('data-portfolio-filter') ?? 'all';

            setActiveFilter(button);
            applyFilter(category);
        });
    });
}
