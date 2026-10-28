document.addEventListener('DOMContentLoaded', function() {
    initMobileNav();
    initHeroSwatches();
    // Server-rendered .alert_success/.alert_failure stay hidden in CSS
    // and surface as short-lived toast notifications instead
    initToasts();
    initPasswordValidation();
    initProductGallery();
    initConfirmForms();
    initDropzones();
    initSubmitGuard();
});

// Home hero: swap product photo when a colour swatch is pressed
function initHeroSwatches() {
    const heroImg = document.querySelector('[data-hero-image]');
    if (!heroImg) return;

    const heroLabel = document.querySelector('[data-hero-label]');
    const swatches = document.querySelectorAll('[data-swatch]');

    swatches.forEach((sw) => {
        const pre = new Image();
        pre.src = sw.getAttribute('data-swatch');
    });

    swatches.forEach((sw) => {
        sw.addEventListener('click', () => {
            if (sw.getAttribute('aria-pressed') === 'true') return;
            swatches.forEach((other) => {
                other.setAttribute('aria-pressed', other === sw ? 'true' : 'false');
            });
            const name = sw.getAttribute('data-name') || '';
            heroImg.classList.add('is-swapping');
            window.setTimeout(() => {
                heroImg.src = sw.getAttribute('data-swatch');
                heroImg.alt = 'Restored iPod Classic in ' + name.toLowerCase();
                if (heroLabel) heroLabel.textContent = 'iPod Classic · ' + name;
                heroImg.classList.remove('is-swapping');
            }, 200);
        });
    });
}

// Custom file picker: show chosen filename(s) inside the dropzone label
function initDropzones() {
    document.querySelectorAll('[data-dropzone-input]').forEach((input) => {
        const label = document.querySelector(`label.dropzone[for="${input.id}"]`);
        if (!label) return;
        const status = label.querySelector('span');
        const fallback = status ? status.textContent : '';

        input.addEventListener('change', () => {
            if (!status) return;
            const files = Array.from(input.files || []);
            if (!files.length) {
                status.textContent = fallback;
                return;
            }
            status.textContent = files.length === 1
                ? files[0].name
                : files.length + ' files selected';
        });
    });
}

// Matches redesign-html: data-menu-toggle + #mobile-menu overlay
function initMobileNav() {
    const menuBtn = document.querySelector('[data-menu-toggle]');
    const menu = document.getElementById('mobile-menu');
    if (!menuBtn || !menu) return;

    const setMenu = (open) => {
        menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Menu');
        menu.hidden = !open;
        document.documentElement.classList.toggle('menu-open', open);
    };

    menuBtn.addEventListener('click', () => {
        setMenu(menuBtn.getAttribute('aria-expanded') !== 'true');
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !menu.hidden) {
            setMenu(false);
            menuBtn.focus();
        }
    });

    window.matchMedia('(min-width: 901px)').addEventListener('change', (mq) => {
        if (mq.matches) setMenu(false);
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
