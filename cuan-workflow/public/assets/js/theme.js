import { select, listen } from './utils/helpers.js';

export const initTheme = () => {
    const themeToggleBtn = select('#theme-toggle');
    const darkIcon = select('#theme-toggle-dark-icon');
    const lightIcon = select('#theme-toggle-light-icon');
    const logo = select('#site-logo');

    const updateLogo = (isDark) => {
        if (logo) {
            // Use absolute paths to avoid issues with nested routes
            logo.src = isDark ? '/assets/icon/logo-darkmode.svg' : '/assets/icon/logo.svg';
        }
    };

    const setTheme = (theme) => {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            if (lightIcon) lightIcon.classList.remove('hidden');
            if (darkIcon) darkIcon.classList.add('hidden');
            updateLogo(true);
            localStorage.setItem('color-theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark');
            if (lightIcon) lightIcon.classList.add('hidden');
            if (darkIcon) darkIcon.classList.remove('hidden');
            updateLogo(false);
            localStorage.setItem('color-theme', 'light');
        }
    };

    const localTheme = localStorage.getItem('color-theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (localTheme === 'dark' || (!localTheme && systemDark)) {
        setTheme('dark');
    } else {
        setTheme('light');
    }

    if (themeToggleBtn) {
        listen(themeToggleBtn, 'click', async () => {

            if (!document.startViewTransition) {

                startLegacyTransition();
                return;
            }

            try {
                const transition = document.startViewTransition(() => {
                    const isDark = document.documentElement.classList.contains('dark');
                    setTheme(isDark ? 'light' : 'dark');
                });

                await transition.finished;
            } catch (e) {
                console.error("View Transition failed", e);

                const isDark = document.documentElement.classList.contains('dark');
                setTheme(isDark ? 'light' : 'dark');
            }
        });
    }

    function startLegacyTransition() {
        document.documentElement.classList.add('theme-transitioning');
        void document.documentElement.offsetWidth;
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                const isDark = document.documentElement.classList.contains('dark');
                setTheme(isDark ? 'light' : 'dark');
                setTimeout(() => {
                    document.documentElement.classList.remove('theme-transitioning');
                }, 500);
            });
        });
    }
};
