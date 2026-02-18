import { api } from '../services/api.js';
import { select, showToast, showConfirm } from '../utils/helpers.js';

class RoadmapHandler {
    constructor() {
        this.init();
    }

    init() {
        // Wait for DOM to be ready just in case, though module scripts are deferred
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    setup() {
        const btnGenerate = select('#btn-generate-roadmap');
        if (btnGenerate) {
            btnGenerate.addEventListener('click', () => this.generateRoadmap());
        }
    }

    async generateRoadmap() {
        const btn = select('#btn-generate-roadmap');
        if (!btn) return;

        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;

        try {
            // Pass suppressAuthRedirect: true to prevent auto-redirect
            const response = await api.post(
                '/mentor/roadmap/generate',
                {},
                { suppressAuthRedirect: true, useApiPrefix: false }
            );

            if (response.success && response.roadmap) {
                this.renderRoadmap(response.roadmap);
                showToast('Roadmap generated successfully!', 'success');
                // Scroll to roadmap
                const container = select('#roadmap-container');
                if (container) container.scrollIntoView({ behavior: 'smooth' });
            } else {
                showToast(response.message || 'Failed to generate.', 'error');
            }
        } catch (e) {
            console.error(e);

            // Check if it's a 401 / Login issue
            if (e.message.includes('login') || e.message.includes('Unauthenticated') || e.message.includes('Unauthorized')) {
                showConfirm(
                    "Anda perlu login untuk menyimpan dan melihat Roadmap bisnis Anda. Login sekarang?",
                    () => { window.location.href = '/login'; }, // On Yes
                    () => { } // On Cancel
                );
            } else {
                showToast(e.message || 'Failed to generate roadmap', 'error');
            }
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    renderRoadmap(roadmap) {
        const container = select('#roadmap-container');
        const stepsContainer = select('#roadmap-steps');
        if (!container || !stepsContainer) return;

        container.classList.remove('hidden');

        let html = `
            <!-- Vertical Connector Line (Absolute) -->
            <div class="absolute left-8 md:left-1/2 top-4 bottom-4 w-0.5 bg-slate-200 dark:bg-slate-700 -ml-px hidden md:block"></div>
        `;

        roadmap.steps.forEach((step, index) => {
            const isLeft = index % 2 === 0;
            const isCompleted = step.status === 'completed';
            const isLocked = step.status === 'locked';

            html += `
            <div class="relative flex items-center justify-between md:justify-normal md:${isLeft ? 'flex-row-reverse' : 'flex-row'} mb-8 group" data-step-id="${step.id}">
                
                <!-- 1. The Empty Space (For ZigZag) -->
                <div class="hidden md:block w-5/12"></div>
                
                <!-- 2. The Center Connector Dot -->
                <div class="absolute left-8 md:left-1/2 -ml-3 md:-ml-3 w-6 h-6 rounded-full border-4 ${isCompleted ? 'border-emerald-500 bg-white' : 'border-slate-200 bg-slate-100'} z-10 transition-colors duration-500"></div>

                <!-- 3. The Content Card -->
                <div class="w-full pl-16 md:pl-0 md:w-5/12 ${isLeft ? 'md:mr-auto md:pr-8 md:text-right' : 'md:ml-auto md:pl-8 md:text-left'}">
                    <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border ${isCompleted ? 'border-emerald-200' : 'border-slate-200'} dark:border-slate-700 shadow-sm transition-all hover:shadow-md relative overflow-hidden group-hover:-translate-y-1 duration-300">
                        
                        <!-- Status Badge -->
                        <div class="text-[10px] font-bold uppercase tracking-wider mb-2 ${isCompleted ? 'text-emerald-500' : 'text-slate-400'}">
                            Step ${step.order} • ${step.strategy_tag}
                        </div>

                        <h4 class="font-bold text-lg text-slate-900 dark:text-white mb-2">${step.title}</h4>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">${step.description}</p>

                        <!-- Actions Checklist -->
                        <div class="space-y-2 text-left">
                            ${step.actions.map(action => `
                                <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-900/50 cursor-pointer transition-colors">
                                    <div class="relative flex items-center mt-0.5">
                                        <input type="checkbox" 
                                            class="peer h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500 cursor-pointer roadmap-action-checkbox"
                                            ${action.is_completed ? 'checked' : ''}
                                            ${isLocked ? 'disabled' : ''}
                                            data-action-id="${action.id}"
                                        >
                                    </div>
                                    <span class="text-sm text-slate-600 dark:text-slate-300 ${action.is_completed ? 'text-emerald-600 line-through' : ''} peer-checked:text-emerald-600 peer-checked:line-through transition-colors select-none">
                                        ${action.action_text}
                                    </span>
                                </label>
                            `).join('')}
                        </div>

                        ${isLocked ? `
                        <div class="absolute inset-0 bg-slate-100/50 dark:bg-slate-900/50 backdrop-blur-[1px] flex items-center justify-center z-20">
                            <div class="bg-white dark:bg-slate-800 px-4 py-2 rounded-full shadow-lg flex items-center gap-2 text-slate-500 text-xs font-bold border border-slate-200">
                                <i class="fas fa-lock"></i> Locked
                            </div>
                        </div>
                        ` : ''}

                    </div>
                </div>
            </div>
            `;
        });

        stepsContainer.innerHTML = html;
        this.bindCheckboxes();
    }

    bindCheckboxes() {
        const checkboxes = document.querySelectorAll('.roadmap-action-checkbox');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', (e) => this.toggleAction(e.target));
        });
    }

    async toggleAction(checkbox) {
        const actionId = checkbox.dataset.actionId;
        const isChecked = checkbox.checked;
        const label = checkbox.closest('label').querySelector('span');

        if (label) {
            if (isChecked) label.classList.add('text-emerald-600', 'line-through');
            else label.classList.remove('text-emerald-600', 'line-through');
        }

        try {
            const response = await api.post(`/mentor/roadmap/action/${actionId}/toggle`, {}, { useApiPrefix: false });

            if (response.success && response.step_completed) {
                showToast('Step Completed! Next step unlocked.', 'success');
                this.refreshRoadmap();
            }
        } catch (e) {
            console.error(e);
            checkbox.checked = !isChecked;
            if (label) {
                if (isChecked) label.classList.remove('text-emerald-600', 'line-through');
                else label.classList.add('text-emerald-600', 'line-through');
            }
            showToast('Failed to update action', 'error');
        }
    }

    async loadRoadmap() {
        try {
            const response = await api.get('/mentor/roadmap', { useApiPrefix: false });
            if (response.success && response.roadmap) {
                this.renderRoadmap(response.roadmap);
            } else {
                this.renderEmptyState();
            }
        } catch (e) {
            console.error(e);
            this.renderEmptyState(); // Render empty state on error as well
        }
    }
}

export const roadmapHandler = new RoadmapHandler();
