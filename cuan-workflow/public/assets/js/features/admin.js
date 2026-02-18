import { showToast } from '../utils/helpers.js';

export function initAdminDashboard() {
    console.log("Initializing Admin Dashboard...");

    // Example: Add an 'Edit' button to all strategies if they don't have one
    const strategies = document.querySelectorAll('.strategy-card'); // Assuming class name

    strategies.forEach(card => {
        const title = card.querySelector('h3')?.innerText;

        const existingBtn = card.querySelector('.admin-edit-btn');
        if (!existingBtn) {
            const btn = document.createElement('button');
            btn.className = "admin-edit-btn absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded shadow";
            btn.innerText = "Edit";
            btn.onclick = () => showToast(`Edit Strategy: ${title}`, 'info');
            card.style.position = 'relative';
            card.appendChild(btn);
        }
    });

    // You can inject more admin controls here
    const nav = document.querySelector('nav .flex.items-center.gap-4');
    if (nav) {
        const adminBadge = document.createElement('span');
        adminBadge.className = "px-3 py-1 bg-red-600 text-white text-xs font-bold rounded-full uppercase tracking-wider";
        adminBadge.innerText = "Admin Mode";
        nav.insertBefore(adminBadge, nav.firstChild);
    }
}
