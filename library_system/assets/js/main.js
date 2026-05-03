/**
 * LibraX - Main JavaScript
 * assets/js/main.js
 */

// --- Navbar scroll effect & hamburger toggle ---
const navbar = document.getElementById('navbar');
const hamburger = document.getElementById('hamburger');
const navLinks = document.getElementById('navLinks');

window.addEventListener('scroll', () => {
    if (window.scrollY > 20) {
        navbar?.classList.add('scrolled');
    } else {
        navbar?.classList.remove('scrolled');
    }
});

hamburger?.addEventListener('click', () => {
    navLinks?.classList.toggle('open');
});

// --- Auto-dismiss alerts after 4 seconds ---
document.querySelectorAll('.alert').forEach(alert => {
    setTimeout(() => {
        alert.style.transition = 'opacity 0.5s';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    }, 4000);
});

// --- Live search filter for tables ---
const searchInput = document.getElementById('searchInput');
if (searchInput) {
    searchInput.addEventListener('input', function () {
        const term = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        let found = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const match = text.includes(term);
            row.style.display = match ? '' : 'none';
            if (match) found++;
        });
        // Show empty state if no results
        const emptyMsg = document.getElementById('emptySearch');
        if (emptyMsg) {
            emptyMsg.style.display = found === 0 ? 'block' : 'none';
        }
    });
}

// --- Confirm before delete ---
document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', function (e) {
        if (!confirm('Are you sure you want to delete this record? This cannot be undone.')) {
            e.preventDefault();
        }
    });
});

// --- Form validation ---
document.querySelectorAll('form[data-validate]').forEach(form => {
    form.addEventListener('submit', function (e) {
        let valid = true;
        form.querySelectorAll('[required]').forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--danger)';
                valid = false;
            } else {
                field.style.borderColor = '';
            }
        });
        if (!valid) {
            e.preventDefault();
            showToast('Please fill in all required fields.', 'danger');
        }
    });
});

// --- Simple toast notification ---
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type}`;
    toast.style.cssText = `
        position: fixed; bottom: 24px; right: 24px;
        z-index: 9999; min-width: 280px; max-width: 380px;
        animation: fadeInUp 0.3s ease;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.4s';
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}
