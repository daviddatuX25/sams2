const toggleButton = document.querySelector('.theme-toggle');
const html = document.documentElement;

// Load saved theme from localStorage
const savedTheme = localStorage.getItem('theme') || 'light';
html.setAttribute('data-bs-theme', savedTheme);

toggleButton.addEventListener('click', () => {
    const currentTheme = html.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    html.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
});

// Accessibility: Allow toggling with Enter or Space key
toggleButton.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleButton.click();
    }
});