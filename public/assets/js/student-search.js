const searchInput = document.getElementById('Student');
const searchResults = document.getElementById('searchResults');
let debounceTimer;

const searchUsers = async (query) => {
    if (query.length < 3) {
        searchResults.style.display = 'none';
        return;
    }

    searchResults.style.display = 'block';
    searchResults.innerHTML = 'Vyhledávám...';

    try {
        const response = await fetch(`search.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.error) {
            if (data.error.includes('přihlaste se znovu')) {
                window.location.href = 'callback.php';
                return;
            }
            searchResults.innerHTML = data.error;
            return;
        }

        if (data.users.length === 0) {
            searchResults.innerHTML = 'Žádní studenti nenalezeni';
            return;
        }

        searchResults.innerHTML = data.users.map(user => `
            <div class="search-item hover:bg-slate-300">
                <div>${user.displayName} - <span class="text-sm text-gray-600 dark:text-[var(--color-text-muted)]">${user.email.split('@')[0]}</span></div>
            </div>
        `).join('');

    } catch (error) {
        searchResults.innerHTML = 'Chyba při vyhledávání';
        console.error('Search error:', error);
    }
};

searchInput.addEventListener('input', (e) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        searchUsers(e.target.value);
    }, 300);
});

document.addEventListener('click', (e) => {
    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
        searchResults.style.display = 'none';
    }
});

searchResults.addEventListener('click', (e) => {
    const searchItem = e.target.closest('.search-item');
    if (searchItem) {
        searchInput.value = searchItem.querySelector('div').textContent;
        searchResults.style.display = 'none';
    }
});
