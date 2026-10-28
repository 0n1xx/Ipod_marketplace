document.addEventListener('DOMContentLoaded', function() {
    initMobileNav();
    // Server-rendered .alert_success/.alert_failure stay hidden in CSS
    // and surface as short-lived toast notifications instead
    initToasts();
    initPasswordValidation();
    initProductGallery();
    initConfirmForms();
    initSubmitGuard();
});

function initMobileNav() {
    const burger = document.querySelector('.burger');
    const nav = document.querySelector('.site-header .nav');
    if (!burger || !nav) return;

    burger.addEventListener('click', () => {
        const open = nav.classList.toggle('open');
        burger.classList.toggle('active', open);
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
}

function initSubmitGuard() {
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            button.disabled = true;
            button.classList.add('is-loading');
        });
    });
}

// data-confirm on a form replaces inline onsubmit="return confirm(...)"
function initConfirmForms() {
    document.querySelectorAll('form.js-confirm').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm');
            if (message && !window.confirm(message)) {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = false;
                    button.classList.remove('is-loading');
                }
            }
        });
    });
}

function initProductGallery() {
    const mainImage = document.getElementById('main-product-image');
    const thumbnails = document.querySelectorAll('.thumb[data-image]');
    if (!mainImage || !thumbnails.length) return;

    thumbnails.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            mainImage.src = thumb.dataset.image;
            thumbnails.forEach((t) => t.setAttribute('aria-pressed', 'false'));
            thumb.setAttribute('aria-pressed', 'true');
        });
    });
}

function initPasswordValidation() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const rulesList = document.getElementById('password-rules');
    const matchMessage = document.getElementById('password-match');

    if (!password || !rulesList) return;

    const rules = {
        length: (value) => value.length >= 8,
        upper:  (value) => /[A-Z]/.test(value),
        lower:  (value) => /[a-z]/.test(value),
        digit:  (value) => /\d/.test(value),
        symbol: (value) => /[^A-Za-z0-9]/.test(value),
    };

    password.addEventListener('input', () => {
        const value = password.value;
        Object.entries(rules).forEach(([rule, test]) => {
            const item = rulesList.querySelector(`[data-rule="${rule}"]`);
            if (item) item.classList.toggle('is-met', test(value));
        });
        updatePasswordMatch();
    });

    if (confirmPassword) {
        confirmPassword.addEventListener('input', updatePasswordMatch);
    }

    function updatePasswordMatch() {
        if (!confirmPassword || !matchMessage) return;
        if (!confirmPassword.value) {
            matchMessage.textContent = '';
            matchMessage.classList.remove('is-ok', 'is-bad');
            return;
        }
        const matches = password.value === confirmPassword.value;
        matchMessage.textContent = matches
            ? matchMessage.dataset.matchText
            : matchMessage.dataset.mismatchText;
        matchMessage.classList.toggle('is-ok', matches);
        matchMessage.classList.toggle('is-bad', !matches);
    }
}

function initToasts() {
    const sources = document.querySelectorAll('.alert_success, .alert_failure');
    if (!sources.length) return;

    const container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);

    sources.forEach((source) => {
        const type = source.classList.contains('alert_success') ? 'success' : 'error';
        source.querySelectorAll('p').forEach((p) => {
            showToast(p.textContent, type, container);
        });
    });
}

function showToast(message, type, container) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('toast-hide');
        toast.addEventListener('animationend', () => toast.remove());
    }, 4000);
}
