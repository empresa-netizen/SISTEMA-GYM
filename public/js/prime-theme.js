(function () {
    var STORAGE_KEY = 'prime-theme';

    function getTheme() {
        var stored = localStorage.getItem(STORAGE_KEY);
        if (stored === 'light') return 'light';
        if (stored === 'dark') return 'dark';
        return 'dark';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-prime-theme', theme);
        localStorage.setItem(STORAGE_KEY, theme);
        document.querySelectorAll('[data-prime-theme-toggle]').forEach(function (btn) {
            var icon = btn.querySelector('i');
            if (!icon) return;
            if (theme === 'light') {
                icon.className = 'ri-moon-line';
                btn.setAttribute('title', 'Modo escuro');
                btn.setAttribute('aria-label', 'Ativar modo escuro');
            } else {
                icon.className = 'ri-sun-line';
                btn.setAttribute('title', 'Modo claro');
                btn.setAttribute('aria-label', 'Ativar modo claro');
            }
        });
    }

    function toggleTheme() {
        applyTheme(getTheme() === 'dark' ? 'light' : 'dark');
    }

    window.PrimeTheme = { getTheme: getTheme, applyTheme: applyTheme, toggleTheme: toggleTheme };

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getTheme());
        document.querySelectorAll('[data-prime-theme-toggle]').forEach(function (btn) {
            btn.addEventListener('click', toggleTheme);
        });
        document.querySelectorAll('[data-prime-theme-set]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                applyTheme(btn.getAttribute('data-prime-theme-set') === 'light' ? 'light' : 'dark');
            });
        });
    });
})();
