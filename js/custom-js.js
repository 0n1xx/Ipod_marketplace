document.addEventListener('DOMContentLoaded', function() {
    document.documentElement.classList.add('js');
    initMobileNav();
    initHeaderScroll();
    initScrollReveal();
    initPasswordToggles();
    initPasswordValidation();
    initToasts();
    initProductGallery();
    initAddToCart();
    initSkeletonImages();
    initConfirmForms();
    initDropzones();
    initSubmitGuard();
});

function initHeaderScroll() {
    const header = document.querySelector('.site-header');
    if (!header) return;
    const onScroll = () => {
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

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
        if (form.hasAttribute('data-add-to-cart')) return;
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button || button.disabled) return;
            button.disabled = true;
            button.classList.add('is-loading');
        });
    });
}

function initConfirmForms() {
    const forms = document.querySelectorAll('form.js-confirm');
    if (!forms.length) return;

    const modal = ensureConfirmModal();
    const titleEl = modal.querySelector('[data-confirm-title]');
    const bodyEl = modal.querySelector('[data-confirm-body]');
    const okBtn = modal.querySelector('[data-confirm-ok]');
    const cancelBtn = modal.querySelector('[data-confirm-cancel]');
    const backdrop = modal.querySelector('[data-confirm-backdrop]');
    let pendingForm = null;

    const close = () => {
        modal.hidden = true;
        document.documentElement.classList.remove('confirm-open');
        pendingForm = null;
        okBtn.disabled = false;
        okBtn.classList.remove('is-loading');
    };

    const open = (form) => {
        pendingForm = form;
        titleEl.textContent = form.getAttribute('data-confirm-title') || 'Are you sure?';
        bodyEl.textContent = form.getAttribute('data-confirm') || '';
        okBtn.textContent = form.getAttribute('data-confirm-ok') || 'Delete';
        cancelBtn.textContent = form.getAttribute('data-confirm-cancel') || 'Cancel';
        modal.hidden = false;
        document.documentElement.classList.add('confirm-open');
        okBtn.focus();
    };

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === '1') {
                form.dataset.confirmed = '0';
                return;
            }
            event.preventDefault();
            event.stopImmediatePropagation();
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = false;
                button.classList.remove('is-loading');
            }
            open(form);
        }, true);
    });

    okBtn.addEventListener('click', () => {
        if (!pendingForm) return;
        const form = pendingForm;
        form.dataset.confirmed = '1';
        close();
        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    });

    cancelBtn.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.hidden) close();
    });
}

function ensureConfirmModal() {
    let modal = document.getElementById('confirm-modal');
    if (modal) return modal;

    modal = document.createElement('div');
    modal.id = 'confirm-modal';
    modal.className = 'confirm-modal';
    modal.hidden = true;
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.setAttribute('aria-labelledby', 'confirm-modal-title');
    modal.innerHTML = `
        <div class="confirm-modal__backdrop" data-confirm-backdrop></div>
        <div class="confirm-modal__card">
            <h2 id="confirm-modal-title" class="confirm-modal__title" data-confirm-title></h2>
            <p class="confirm-modal__body" data-confirm-body></p>
            <div class="confirm-modal__actions">
                <button type="button" class="btn btn--outline" data-confirm-cancel>Cancel</button>
                <button type="button" class="btn btn--danger" data-confirm-ok>Delete</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    return modal;
}

/* Swipe / arrow product gallery */
function initProductGallery() {
    const root = document.querySelector('[data-gallery]');
    if (!root) {
        // Legacy thumbs fallback
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
        return;
    }

    const track = root.querySelector('[data-gallery-track]');
    const slides = Array.from(root.querySelectorAll('[data-gallery-slide]'));
    const thumbs = Array.from(root.querySelectorAll('[data-gallery-thumb]'));
    const prev = root.querySelector('[data-gallery-prev]');
    const next = root.querySelector('[data-gallery-next]');
    if (!track || slides.length < 2) return;

    let index = 0;
    const go = (i) => {
        index = (i + slides.length) % slides.length;
        track.scrollTo({ left: slides[index].offsetLeft, behavior: 'smooth' });
        thumbs.forEach((thumb, t) => thumb.setAttribute('aria-pressed', t === index ? 'true' : 'false'));
    };

    prev && prev.addEventListener('click', () => go(index - 1));
    next && next.addEventListener('click', () => go(index + 1));
    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => go(Number(thumb.getAttribute('data-gallery-thumb')) || 0));
    });

    let scrollTimer;
    track.addEventListener('scroll', () => {
        window.clearTimeout(scrollTimer);
        scrollTimer = window.setTimeout(() => {
            const nearest = slides.reduce((best, slide, i) => {
                const dist = Math.abs(slide.offsetLeft - track.scrollLeft);
                return dist < best.dist ? { i, dist } : best;
            }, { i: 0, dist: Infinity });
            index = nearest.i;
            thumbs.forEach((thumb, t) => thumb.setAttribute('aria-pressed', t === index ? 'true' : 'false'));
        }, 60);
    }, { passive: true });
}

/* AJAX add-to-cart + fly-to-cart motion */
function initAddToCart() {
    const form = document.querySelector('form[data-add-to-cart]');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.classList.add('is-loading');
        }

        const body = new FormData(form);
        body.set('ajax', '1');

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });
            const data = await res.json();
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }
            if (!data.ok) throw new Error('add_failed');

            flyToCart(form.getAttribute('data-product-image'));
            updateCartBadges(data.count);

            const actions = form.closest('.actions');
            if (actions) {
                const cartLink = document.querySelector('a.header-link[href*="cart.php"], a.icon-btn[href*="cart.php"]');
                const href = cartLink?.getAttribute('href') || ('cart.php?lang=' + (new URLSearchParams(window.location.search).get('lang') || 'en'));
                const link = document.createElement('a');
                link.href = href;
                link.className = 'btn btn--blue btn--lg btn--block';
                link.textContent = document.documentElement.lang === 'fr' ? 'Voir le panier' : 'View Cart';
                form.replaceWith(link);
            }
        } catch (err) {
            form.submit();
        } finally {
            if (button) {
                button.disabled = false;
                button.classList.remove('is-loading');
            }
        }
    });
}

function updateCartBadges(count) {
    document.querySelectorAll('.count-badge, [data-cart-count]').forEach((badge) => {
        badge.textContent = String(count);
        badge.hidden = !count;
        badge.classList.remove('is-pop');
        void badge.offsetWidth;
        badge.classList.add('is-pop');
    });
    // Create badge if missing on header cart links
    if (count > 0) {
        document.querySelectorAll('.header-link[href*="cart.php"], .icon-btn[href*="cart.php"]').forEach((link) => {
            let badge = link.querySelector('.count-badge');
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'count-badge is-pop';
                link.appendChild(badge);
            }
            badge.textContent = String(count);
            badge.hidden = false;
        });
    }
}

function flyToCart(imageUrl) {
    if (!imageUrl) return;
    const cart = document.querySelector('.header-link[href*="cart.php"], .icon-btn[href*="cart.php"]');
    const source = document.getElementById('main-product-image');
    if (!cart || !source) return;

    const from = source.getBoundingClientRect();
    const to = cart.getBoundingClientRect();
    const ghost = document.createElement('img');
    ghost.src = imageUrl;
    ghost.alt = '';
    ghost.className = 'cart-fly';
    ghost.style.left = from.left + 'px';
    ghost.style.top = from.top + 'px';
    ghost.style.width = Math.min(from.width, 160) + 'px';
    ghost.style.height = Math.min(from.height, 160) + 'px';
    document.body.appendChild(ghost);

    requestAnimationFrame(() => {
        ghost.style.transform = `translate(${to.left - from.left}px, ${to.top - from.top}px) scale(0.15)`;
        ghost.style.opacity = '0.2';
    });
    ghost.addEventListener('transitionend', () => ghost.remove(), { once: true });
}

function initSkeletonImages() {
    document.querySelectorAll('[data-skeleton-img]').forEach((img) => {
        const tile = img.closest('.tile');
        const done = () => tile && tile.classList.add('is-loaded');
        if (img.complete) done();
        else {
            img.addEventListener('load', done, { once: true });
            img.addEventListener('error', done, { once: true });
        }
    });
}

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
