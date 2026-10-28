/* QuébecIpod — small progressive-enhancement script for the static redesign.
   Each block only runs if its markup is on the page. */
(function () {
    'use strict';

    var $ = function (sel, root) { return (root || document).querySelector(sel); };
    var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

    /* ---- Fake submit: hide a form and show its success message ----
       <form data-success="#id"> ... </form>  <div id="id" hidden>…</div> */
    $$('form[data-success]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var target = $(form.getAttribute('data-success'));
            if (!target) return;
            if (form.hasAttribute('data-keep')) { target.hidden = false; return; }
            form.hidden = true;
            target.hidden = false;
        });
    });

    /* ---- Shop: live search ---- */
    var search = $('[data-shop-search]');
    if (search) {
        var cards = $$('[data-product]');
        var empty = $('[data-shop-empty]');
        var grid = $('[data-shop-grid]');
        var count = $('[data-shop-count]');
        var filter = function () {
            var q = search.value.trim().toLowerCase();
            var shown = 0;
            cards.forEach(function (card) {
                var match = !q || card.getAttribute('data-product').toLowerCase().indexOf(q) !== -1;
                card.hidden = !match;
                if (match) shown++;
            });
            if (grid) grid.hidden = shown === 0;
            if (empty) empty.hidden = shown !== 0;
            if (count) count.textContent = shown + (shown === 1 ? ' iPod' : ' iPods');
        };
        search.addEventListener('input', filter);
        search.form && search.form.addEventListener('submit', function (e) { e.preventDefault(); filter(); });
        var clear = $('[data-shop-clear]');
        clear && clear.addEventListener('click', function () { search.value = ''; filter(); search.focus(); });
    }

    /* ---- Product: thumbnail gallery ---- */
    var main = $('#main-product-image');
    if (main) {
        $$('[data-thumb]').forEach(function (btn, _, all) {
            btn.addEventListener('click', function () {
                main.src = btn.getAttribute('data-thumb');
                all.forEach(function (b) { b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
            });
        });
    }

    /* ---- Home: hero colour picker ---- */
    var heroImg = $('[data-hero-image]');
    if (heroImg) {
        var heroLabel = $('[data-hero-label]');
        var swatches = $$('[data-swatch]');
        swatches.forEach(function (sw) { var pre = new Image(); pre.src = sw.getAttribute('data-swatch'); });
        swatches.forEach(function (sw) {
            sw.addEventListener('click', function () {
                if (sw.getAttribute('aria-pressed') === 'true') return;
                swatches.forEach(function (o) { o.setAttribute('aria-pressed', o === sw ? 'true' : 'false'); });
                var name = sw.getAttribute('data-name');
                heroImg.classList.add('is-swapping');
                setTimeout(function () {
                    heroImg.src = sw.getAttribute('data-swatch');
                    heroImg.alt = 'Restored iPod Classic in ' + name.toLowerCase();
                    if (heroLabel) heroLabel.textContent = 'iPod Classic · ' + name;
                    heroImg.classList.remove('is-swapping');
                }, 200);
            });
        });
    }

    /* ---- Mobile menu ---- */
    var menuBtn = $('[data-menu-toggle]');
    var menu = $('#mobile-menu');
    if (menuBtn && menu) {
        var setMenu = function (open) {
            menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            menuBtn.setAttribute('aria-label', open ? 'Close menu' : 'Menu');
            menu.hidden = !open;
            document.documentElement.classList.toggle('menu-open', open);
        };
        menuBtn.addEventListener('click', function () {
            setMenu(menuBtn.getAttribute('aria-expanded') !== 'true');
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !menu.hidden) { setMenu(false); menuBtn.focus(); }
        });
        // Close if the window grows past the mobile breakpoint
        window.matchMedia('(min-width: 901px)').addEventListener('change', function (mq) {
            if (mq.matches) setMenu(false);
        });
    }

    /* ---- Product: add to cart (main button + phone action bar) ---- */
    var addBtns = $$('[data-add-to-cart]');
    if (addBtns.length) {
        var added = false;
        addBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (added) return;
                added = true;
                addBtns.forEach(function (b) { b.hidden = true; });
                $$('[data-view-cart]').forEach(function (v) { v.hidden = false; });
                $$('[data-cart-count]').forEach(function (badge) {
                    badge.textContent = String((parseInt(badge.textContent, 10) || 0) + 1);
                    badge.hidden = false;
                });
            });
        });
    }

    /* ---- Cart: remove items ---- */
    var cartList = $('[data-cart-list]');
    if (cartList) {
        var syncCart = function () {
            var n = $$('[data-cart-item]', cartList).length;
            $$('[data-items-count]').forEach(function (el) { el.textContent = String(n); });
            var label = $('[data-items-label]');
            if (label) label.textContent = n + (n === 1 ? ' item' : ' items');
            $$('[data-cart-count]').forEach(function (badge) { badge.textContent = String(n); badge.hidden = n === 0; });
            $('[data-cart-filled]').hidden = n === 0;
            $('[data-cart-empty]').hidden = n !== 0;
        };
        cartList.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-remove]');
            if (!btn) return;
            btn.closest('[data-cart-item]').remove();
            syncCart();
        });
    }

    /* ---- Password rules + match ---- */
    var pw = $('[data-password]');
    if (pw) {
        var tests = {
            length: function (v) { return v.length >= 8; },
            upper: function (v) { return /[A-Z]/.test(v); },
            lower: function (v) { return /[a-z]/.test(v); },
            digit: function (v) { return /[0-9]/.test(v); },
            symbol: function (v) { return /[^A-Za-z0-9]/.test(v); }
        };
        var confirmInput = $('[data-confirm]');
        var match = $('[data-match]');
        var check = function () {
            var v = pw.value;
            $$('[data-rule]').forEach(function (li) {
                var ok = tests[li.getAttribute('data-rule')](v);
                li.classList.toggle('is-met', ok);
            });
            if (confirmInput && match) {
                var c = confirmInput.value;
                if (!c) { match.textContent = ''; match.className = 'match'; return; }
                var same = c === v;
                match.textContent = same ? match.getAttribute('data-ok') : match.getAttribute('data-bad');
                match.className = 'match ' + (same ? 'is-ok' : 'is-bad');
            }
        };
        pw.addEventListener('input', check);
        confirmInput && confirmInput.addEventListener('input', check);
    }

    /* ---- Admin: inventory actions ---- */
    var inv = $('[data-inventory]');
    if (inv) {
        var stats = function () {
            var rows = $$('tr[data-row]', inv);
            var sold = rows.filter(function (r) { return r.getAttribute('data-sold') === '1'; }).length;
            $('[data-stat-total]').textContent = rows.length;
            $('[data-stat-sold]').textContent = sold;
            $('[data-stat-available]').textContent = rows.length - sold;
        };
        var formTitle = $('[data-form-title]');
        var formSubmit = $('[data-form-submit]');
        var cancel = $('[data-form-cancel]');
        var nameInput = $('#a-name');
        var imgHelp = $('[data-img-help]');
        var setEditing = function (name) {
            formTitle.textContent = name ? 'Edit · ' + name : 'Create a new Product';
            formSubmit.textContent = name ? 'Save changes' : 'Create Product';
            cancel.hidden = !name;
            nameInput.value = name || '';
            imgHelp.textContent = name
                ? 'You can leave this empty if you don’t want to change the image.'
                : 'Upload a clear image of the product (JPEG, PNG, GIF).';
        };
        inv.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-action]');
            if (!btn) return;
            var row = btn.closest('tr');
            var action = btn.getAttribute('data-action');
            if (action === 'toggle') {
                var nowSold = row.getAttribute('data-sold') !== '1';
                row.setAttribute('data-sold', nowSold ? '1' : '0');
                var pill = $('.pill', row);
                pill.textContent = nowSold ? 'Sold' : 'Available';
                pill.classList.toggle('pill--sold', nowSold);
                btn.textContent = nowSold ? 'Mark as Available' : 'Mark as Sold';
            } else if (action === 'delete') {
                if (window.confirm('Delete this product permanently?')) row.remove();
            } else if (action === 'edit') {
                setEditing(row.getAttribute('data-name'));
                $('#create').scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            stats();
        });
        cancel.addEventListener('click', function () { setEditing(''); });
    }
})();
