import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Close any <details data-dropdown> when clicking outside or pressing Escape.
document.addEventListener('click', (event) => {
    document.querySelectorAll('details[data-dropdown][open]').forEach((details) => {
        if (!details.contains(event.target)) {
            details.removeAttribute('open');
        }
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }
    document.querySelectorAll('details[data-dropdown][open]').forEach((details) => {
        details.removeAttribute('open');
        const summary = details.querySelector('summary');
        if (summary instanceof HTMLElement) {
            summary.focus();
        }
    });
});
