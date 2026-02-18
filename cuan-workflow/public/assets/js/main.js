import { initAuthListener, logoutUser } from './core/auth-engine.js';
import { mainController } from './core/main-controller.js';
import { businessCore } from './core/BusinessCore.js'; // Import Core
import { calculateGoal, updateCalculator } from './calculator.js';
import { initTheme } from './theme.js';
import { startTour } from './tour.js';
import { select, listen, showToast, showConfirm } from './utils/helpers.js';
import { initScrollEffects, initBackToTop } from './ui.js';
import { educationEngine } from './features/education-engine.js';

// ... existing code ...

const logoutBtn = select('#btn-logout');
const dropdownLogoutBtn = select('#dropdown-logout-btn');

const handleLogout = () => {
    showConfirm("Apakah Anda yakin ingin keluar?", () => logoutUser());
};

if (logoutBtn) listen(logoutBtn, 'click', handleLogout);
if (dropdownLogoutBtn) listen(dropdownLogoutBtn, 'click', handleLogout);



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

/**
 * Initialize custom dropdown logic
 */
function initCustomDropdowns() {
    const dropdowns = document.querySelectorAll('.custom-dropdown');

    dropdowns.forEach(dropdown => {
        const btn = dropdown.querySelector('.dropdown-btn');
        const menu = dropdown.querySelector('.dropdown-menu');
        const items = dropdown.querySelectorAll('.dropdown-item');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        const selectedValueSpan = dropdown.querySelector('.selected-value');

        if (!btn || !menu) return;

        // Toggle menu
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            // Close other dropdowns first
            document.querySelectorAll('.dropdown-menu.show').forEach(m => {
                if (m !== menu) m.classList.remove('show');
            });
            menu.classList.toggle('show');
        });

        // Handle item selection
        items.forEach(item => {
            item.addEventListener('click', () => {
                const value = item.getAttribute('data-value');
                const text = item.textContent;

                // Update UI
                if (selectedValueSpan) selectedValueSpan.textContent = text;
                items.forEach(i => i.classList.remove('active'));
                item.classList.add('active');

                // Update hidden input and trigger events
                if (hiddenInput) {
                    hiddenInput.value = value;
                    // Trigger native events so other scripts react (e.g., businessCore, mainController)
                    hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                    hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                // Close menu
                menu.classList.remove('show');
            });
        });
    });

    // Close all dropdowns when clicking outside
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
    });
}


window.onload = async function () { // Make it async
    console.log('UI V.7 Initialized - Debugging Mode On');

    const splash = select('#splash-screen');
    if (splash) {
        setTimeout(() => {
            splash.style.opacity = '0';
            setTimeout(() => {
                splash.remove();
            }, 500);
        }, 500);
    }

    try {
        initTheme();
        initScrollEffects();
        initBackToTop();
        initAuthListener();

        mainController.init();

        // Initialize Core (Loads data from API)
        await businessCore.init();

        // Initialize Education Engine
        await educationEngine.init();

        // Initialize custom dropdowns
        initCustomDropdowns();

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

        // Logout listeners are already handled above via const logoutBtn / dropdownLogoutBtn

        const btnCalculateGoal = select('button[onclick="calculateGoal()"]');

        const btnCalcGoal = select('#btn-calculate-goal');
        if (btnCalcGoal) listen(btnCalcGoal, 'click', calculateGoal);

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('./sw.js?v=15')
                .then(() => console.log('SW Registered'))
                .catch(e => console.log('SW Fail:', e));
        }

        calculateGoal();
        updateCalculator();
    } catch (e) {
        console.error("Main JS Error:", e);
        showToast("Terjadi kesalahan pada aplikasi: " + e.message + ". Silakan refresh halaman.", "error");
    }
};
