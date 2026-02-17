import { initAuthListener, logoutUser } from './core/auth-engine.js';
import { mainController } from './core/main-controller.js';
import { calculateGoal, updateCalculator } from './calculator.js';
import { initTheme } from './theme.js';
import { startTour } from './tour.js';
import { select, listen } from './utils/helpers.js';
import { initScrollEffects, initBackToTop } from './ui.js';



/**
 * Initialize dynamic greeting based on time and user name
 */
function initGreeting() {
    const greetingContainer = select('#greeting-container');
    const greetingText = select('#greeting-text');

    if (!greetingContainer || !greetingText) return;

    // Get user display name from session storage
    const displayName = sessionStorage.getItem('cuan_user_display_name') || 'Sobat Cuan';

    // Get current hour
    const hour = new Date().getHours();

    // Determine greeting based on time
    let timeGreeting = '';
    if (hour >= 5 && hour < 11) {
        timeGreeting = 'Selamat Pagi';
    } else if (hour >= 11 && hour < 15) {
        timeGreeting = 'Selamat Siang';
    } else if (hour >= 15 && hour < 18) {
        timeGreeting = 'Selamat Sore';
    } else {
        timeGreeting = 'Selamat Malam';
    }

    // Set greeting text
    greetingText.textContent = `${timeGreeting}, ${displayName}`;

    // Set avatar
    const avatarImg = select('#hero-avatar');
    if (avatarImg) {
        const storedAvatar = sessionStorage.getItem('cuan_user_avatar');
        // Use stored avatar or generate UI Avatar regular fallback
        const avatarUrl = storedAvatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(displayName)}&background=10b981&color=fff`;
        avatarImg.src = avatarUrl;
    }

    // Show greeting container
    greetingContainer.classList.remove('hidden');
}


window.onload = function () {
    console.log('UI V.7 Initialized - Debugging Mode On');

    const splash = select('#splash-screen');
    if (splash) {
        setTimeout(() => {
            splash.style.opacity = '0';
            setTimeout(() => {
                splash.remove();
            }, 1000);
        }, 2000);
    }

    try {
        initTheme();
        initScrollEffects();
        initBackToTop();
        initAuthListener();
        mainController.init();

        // Initialize dynamic greeting
        initGreeting();

        if (sessionStorage.getItem('cuan_user_role') && !localStorage.getItem('cuan_tour_seen')) {
            setTimeout(() => {
                startTour();
            }, 1000);
        }

        const tourBtn = select('#start-tour');
        if (tourBtn) listen(tourBtn, 'click', () => startTour());

        const reloadBtn = select('#btn-reload');
        if (reloadBtn) {
            listen(reloadBtn, 'click', () => {
                location.reload();
            });
        }

        const logoutBtn = select('#btn-logout');
        if (logoutBtn) listen(logoutBtn, 'click', () => logoutUser());
        const btnCalculateGoal = select('button[onclick="calculateGoal()"]');

        const btnCalcGoal = select('#btn-calculate-goal');
        if (btnCalcGoal) listen(btnCalcGoal, 'click', calculateGoal);

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('./sw.js')
                .then(() => console.log('SW Registered'))
                .catch(e => console.log('SW Fail:', e));
        }

        calculateGoal();
        updateCalculator();
    } catch (e) {
        console.error("Main JS Error:", e);
        alert("Terjadi kesalahan pada aplikasi: " + e.message + ". Silakan refresh halaman.");
    }
};
