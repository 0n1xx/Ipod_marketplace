document.addEventListener('DOMContentLoaded', function() {
    document.documentElement.classList.add('js');
    initMobileNav();
    initScrollReveal();
    initPasswordToggles();
    initPasswordValidation();
    initToasts();
    initProductGallery();
    initConfirmForms();
    initDropzones();
    initSubmitGuard();
});

/* Scroll reveal: elements with [data-reveal] fade in when they enter the viewport */
function initScrollReveal() {
    const els = document.querySelectorAll('[data-reveal]');
    if (!els.length) return;

    if (!('IntersectionObserver' in window)) {
        els.forEach((el) => el.classList.add('is-in'));
        return;
    }

    const io = new IntersectionObserver((entries) => {
        entries.forEach((en) => {
            if (en.isIntersecting) {
                en.target.classList.add('is-in');
                io.unobserve(en.target);
            }
        });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });

    els.forEach((el) => io.observe(el));
}

/* Show / hide password for [data-pw-toggle] buttons */
function initPasswordToggles() {
    document.querySelectorAll('[data-pw-toggle]').forEach((btn) => {
        btn.setAttribute('aria-pressed', 'false');
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.getAttribute('aria-controls'));
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });
}

/* Custom file picker: show chosen filename(s) inside the dropzone label */
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

/* Matches redesign-html: data-menu-toggle + #mobile-menu overlay */
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
    const thumbnails = document.querySelectorAll('.thumb[data-image], [data-thumb]');
    if (!mainImage || !thumbnails.length) return;

    thumbnails.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const src = thumb.dataset.image || thumb.getAttribute('data-thumb');
            if (src) mainImage.src = src;
            thumbnails.forEach((t) => t.setAttribute('aria-pressed', 'false'));
            thumb.setAttribute('aria-pressed', 'true');
        });
    });
}

/* Password rules, strength meter, and confirm match */
function initPasswordValidation() {
    const password = document.querySelector('[data-password]') || document.getElementById('password');
    const rulesList = document.getElementById('password-rules') || document.querySelector('[data-rule]')?.closest('ul');
    if (!password || !rulesList) return;

    const tests = {
        length: (v) => v.length >= 8,
        upper:  (v) => /[A-Z]/.test(v),
        lower:  (v) => /[a-z]/.test(v),
        digit:  (v) => /\d/.test(v),
        symbol: (v) => /[^A-Za-z0-9]/.test(v),
    };

    const confirmInput = document.querySelector('[data-confirm]') || document.getElementById('confirm_password');
    const match = document.querySelector('[data-match]') || document.getElementById('password-match');
    const strength = document.querySelector('[data-strength]');
    const strengthLabel = document.querySelector('[data-strength-label]');
    const names = ['Password strength', 'Very weak', 'Weak', 'Fair', 'Good', 'Strong'];

    const check = () => {
        const v = password.value;
        let met = 0;
        rulesList.querySelectorAll('[data-rule]').forEach((li) => {
            const ok = tests[li.getAttribute('data-rule')]?.(v);
            li.classList.toggle('is-met', !!ok);
            if (ok) met++;
        });

        if (strength) {
            const level = v ? met : 0;
            strength.setAttribute('data-level', String(level));
            if (strengthLabel) strengthLabel.textContent = names[level];
        }

        if (confirmInput && match) {
            const c = confirmInput.value;
            if (!c) {
                match.textContent = '';
                match.className = 'match';
                return;
            }
            const same = c === v;
            const okText = match.getAttribute('data-ok') || match.dataset.matchText || 'Passwords match';
            const badText = match.getAttribute('data-bad') || match.dataset.mismatchText || 'Passwords do not match';
            match.textContent = same ? okText : badText;
            match.className = 'match ' + (same ? 'is-ok' : 'is-bad');
        }
    };

    password.addEventListener('input', check);
    if (confirmInput) confirmInput.addEventListener('input', check);
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
        toast.addEventListener('transitionend', () => toast.remove());
    }, 4000);
}
