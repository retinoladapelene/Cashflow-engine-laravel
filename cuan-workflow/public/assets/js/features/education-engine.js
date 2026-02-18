import { select, listen, showToast, showConfirm, sanitizeInput } from '../utils/helpers.js';

class EducationEngine {
    constructor() {
        this.ctxData = null;
        this.initialized = false;
    }

    async init() {
        if (this.initialized) return;

        console.log('Context-Aware Learning Engine 1.0 Initializing...');

        // Initial context evaluation
        await this.evaluate();

        // Bind tooltips
        this.bindTooltips();

        // Bind UI triggers (Zone/Level selection in Profit Simulator)
        this.bindSimulatorTriggers();

        this.initialized = true;
    }

    async evaluate(action = null, meta = {}) {
        try {
            const query = action ? `?action=${action}` : '';
            const response = await fetch(`/api/context-evaluate${query}`, {
                method: action ? 'POST' : 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: action ? JSON.stringify(meta) : null
            });

            const result = await response.json();
            if (result.success) {
                this.ctxData = result.data;
                this.handleLayers(result.data);
            }
        } catch (e) {
            console.error('Education Engine Evaluation Error:', e);
        }
    }

    handleLayers(data) {
        // Layer 2: Context Insight (Subtle Banner)
        if (data.contextual_insight) {
            this.showInsightBanner(data.contextual_insight);
        }

        // Layer 3: Behavioral Prompt (Coach Modal)
        if (data.behavioral_prompt) {
            this.showCoachModal(data.behavioral_prompt);
        }
    }

    bindTooltips() {
        // Find all help icons and bind click
        const helpIcons = document.querySelectorAll('.help-icon, [data-term]');
        helpIcons.forEach(icon => {
            icon.addEventListener('click', (e) => {
                e.preventDefault();
                const termKey = icon.getAttribute('data-term');
                if (termKey) this.showTooltip(termKey);
            });
        });
    }

    bindSimulatorTriggers() {
        // Profit Simulator Zone Selection
        const zoneCards = document.querySelectorAll('.zone-card');
        zoneCards.forEach(card => {
            card.addEventListener('click', () => {
                const zone = card.getAttribute('data-zone');
                this.evaluate('zone_selected', { zone });
            });
        });

        // Level Buttons
        const levelBtns = document.querySelectorAll('.level-btn');
        levelBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const level = btn.getAttribute('data-level');
                const zone = btn.closest('.zone-card')?.getAttribute('data-zone');
                this.evaluate('level_selected', { zone, level });
            });
        });
    }

    async showTooltip(termKey) {
        try {
            const response = await fetch(`/api/education/${termKey}`);
            const result = await response.json();

            if (result.success) {
                const data = result.data;

                // Construct Tooltip Content (Layer 1)
                const content = `
                    <div class="space-y-3">
                        <p class="font-bold text-emerald-600 dark:text-emerald-400 border-b border-emerald-100 dark:border-emerald-800 pb-1">${data.term.toUpperCase()}</p>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-200">${data.short_text}</p>
                        ${data.display_mode === 'extended' ? `<p class="text-xs text-slate-500 dark:text-slate-400 mt-2">${data.long_text}</p>` : ''}
                        ${data.contextual_text ? `
                            <div class="mt-3 p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg border border-emerald-100 dark:border-emerald-800">
                                <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-500 uppercase mb-1">Contextual Insight:</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300 font-medium">${data.contextual_text}</p>
                            </div>
                        ` : ''}
                    </div>
                `;

                showToast(content, 'info'); // Using Toast as a simple way to show tooltip-like info for now
            }
        } catch (e) {
            console.error('Tooltip Error:', e);
        }
    }

    showInsightBanner(insight) {
        // Create or find banner container
        let banner = document.getElementById('education-insight-banner');
        if (!banner) {
            const container = document.getElementById('profit-simulator-section') || document.body;
            banner = document.createElement('div');
            banner.id = 'education-insight-banner';
            banner.className = 'mb-6 p-4 rounded-2xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 animate-slide-down';
            container.prepend(banner);
        }

        banner.innerHTML = `
            <div class="flex items-start gap-3">
                <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg text-blue-600 dark:text-blue-400">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-blue-800 dark:text-blue-300">${insight.title || 'Insight Strategis'}</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">${insight.message}</p>
                    ${insight.suggestion ? `<p class="text-[10px] font-bold text-blue-600 dark:text-blue-500 mt-2 uppercase">Saran: ${insight.suggestion}</p>` : ''}
                </div>
                <button onclick="this.closest('#education-insight-banner').remove()" class="ml-auto text-slate-400 hover:text-slate-600"><i class="fas fa-times"></i></button>
            </div>
        `;
    }

    showCoachModal(prompt) {
        showConfirm(prompt.message, () => {
            if (prompt.action_url) window.location.href = prompt.action_url;
        }, null);

        // Customize confirm modal for coaching
        const modal = document.getElementById('custom-confirm-modal');
        if (modal) {
            const h3 = modal.querySelector('h3');
            if (h3) h3.textContent = prompt.title || 'Mini Coach';

            const btnYes = modal.querySelector('#btn-yes-confirm');
            if (btnYes) btnYes.textContent = prompt.action_text || 'Lihat Saran';
        }
    }
}

export const educationEngine = new EducationEngine();
