document.addEventListener('DOMContentLoaded', function () {
    const rail = document.getElementById('primeRail');
    const toggle = document.getElementById('primeRailToggle');

    if (rail && toggle) {
        // Force expanded by default to match Prime desktop (ignore stale collapsed preference once).
        localStorage.setItem('primeRailExpanded', '1');
        rail.classList.add('is-expanded');
        document.body.classList.add('prime-rail-expanded');

        toggle.addEventListener('click', function () {
            rail.classList.toggle('is-expanded');
            document.body.classList.toggle('prime-rail-expanded');
            localStorage.setItem('primeRailExpanded', rail.classList.contains('is-expanded') ? '1' : '0');
            document.querySelectorAll('.prime-rail-group.is-flyout-open').forEach(function (g) {
                g.classList.remove('is-flyout-open');
            });
        });
    }

    document.querySelectorAll('.prime-rail-group').forEach(function (group) {
        const trigger = group.querySelector('.prime-rail-trigger');
        const flyout = group.querySelector('.prime-rail-flyout');
        if (!trigger || !flyout) return;

        trigger.addEventListener('click', function (e) {
            if (!rail || !rail.classList.contains('is-expanded')) {
                e.preventDefault();
                const open = group.classList.contains('is-flyout-open');
                document.querySelectorAll('.prime-rail-group.is-flyout-open').forEach(function (g) {
                    g.classList.remove('is-flyout-open');
                });
                if (!open) {
                    group.classList.add('is-flyout-open');
                    const rect = trigger.getBoundingClientRect();
                    flyout.style.top = rect.top + 'px';
                }
                return;
            }
            e.preventDefault();
            const wasOpen = group.classList.contains('is-open');
            document.querySelectorAll('.prime-rail-group.is-open').forEach(function (g) {
                if (g !== group) g.classList.remove('is-open');
            });
            group.classList.toggle('is-open', !wasOpen);
        });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.prime-rail-group')) {
            document.querySelectorAll('.prime-rail-group.is-flyout-open').forEach(function (g) {
                g.classList.remove('is-flyout-open');
            });
        }
    });

    // Mobile drawer
    const drawer = document.getElementById('primeMobileDrawer');
    function openDrawer() {
        if (!drawer) return;
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('prime-drawer-open');
    }
    function closeDrawer() {
        if (!drawer) return;
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('prime-drawer-open');
    }
    ['primeMobileMenuBtn', 'primeMobileMenuNavBtn'].forEach(function (id) {
        const btn = document.getElementById(id);
        if (btn) btn.addEventListener('click', openDrawer);
    });
    if (drawer) {
        drawer.querySelectorAll('[data-prime-drawer-close]').forEach(function (el) {
            el.addEventListener('click', closeDrawer);
        });
        drawer.querySelectorAll('.prime-mobile-drawer__nav a').forEach(function (link) {
            link.addEventListener('click', closeDrawer);
        });
    }
});
