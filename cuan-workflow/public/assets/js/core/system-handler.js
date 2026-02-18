/**
 * @file system-handler.js
 * @description Handles System Settings (Flags, Broadcasts).
 * Currently stubbed/local defaults as API transition is in progress.
 */

import { api } from '../services/api.js';

// Init
document.addEventListener('DOMContentLoaded', () => {
    // console.log("[System] Initializing...");
    checkSystemSettings();
});

async function checkSystemSettings() {
    try {
        // Fetch from API
        const settings = await api.get('/public/system/settings').catch(() => null) || await api.get('/system/settings');

        if (!settings) throw new Error("No settings data");

        applySettings(settings);

    } catch (error) {
        console.error("[System] Failed to load settings", error);
    }
}

function applySettings(data) {
    if (!data) return;

    // Flags
    if (data.flags) {
        const calcSection = document.getElementById('calculator-section');
        if (calcSection) {
            if (data.flags.calculator === false) calcSection.classList.add('hidden');
            else calcSection.classList.remove('hidden');
        }

        const exportSection = document.getElementById('export-section');
        if (exportSection) {
            if (data.flags.export_pdf === false) exportSection.classList.add('hidden');
            else exportSection.classList.remove('hidden');
        }
    }

    // Broadcast
    if (data.broadcast) {
        const banner = document.getElementById('system-broadcast');
        const textEl = document.getElementById('broadcast-text');

        if (banner && textEl) {
            if (data.broadcast.isActive && data.broadcast.message) {
                textEl.textContent = data.broadcast.message;
                banner.classList.remove('hidden');
            } else {
                banner.classList.add('hidden');
            }
        }
    }
}
