document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('#page-search');

    if (!input) {
        return;
    }

    const searchableItems = document.querySelectorAll('.card, .data-table tbody tr, .simple-list li');

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();

        searchableItems.forEach((item) => {
            const text = item.textContent.toLowerCase();

            if (!query || text.includes(query)) {
                item.classList.remove('is-hidden-by-search');
            } else {
                item.classList.add('is-hidden-by-search');
            }
        });
    });
});