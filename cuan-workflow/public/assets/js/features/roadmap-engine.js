import { api } from '../services/api.js';
import { select, showToast, showConfirm } from '../utils/helpers.js';

class RoadmapHandler {
    constructor() {
        this.container = null;
        this.cardsWrapper = null;
        this.svg = null;
        this.cards = [];
        this.isMobile = false;
        this.init();
    }

    init() {
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

        // Initialize Container Structure
        const mainContainer = select('#roadmap-steps');
        if (mainContainer) {
            // Ensure the required HTML structure exists
            if (!mainContainer.querySelector('.roadmap-cards')) {
                mainContainer.className = "relative w-full max-w-5xl mx-auto pt-10 pb-20 px-4 md:px-0";
                mainContainer.innerHTML = `
                    <svg id="roadmap-svg" class="absolute top-0 left-0 w-full h-full pointer-events-none z-0 overflow-visible roadmap-svg"></svg>
                    <div class="roadmap-cards space-y-24 relative z-10"></div>
                `;
            }

            this.container = mainContainer;
            this.cardsWrapper = this.container.querySelector('.roadmap-cards');
            this.svg = this.container.querySelector('.roadmap-svg');

            // Auto-load existing roadmap
            this.loadRoadmap();
        }
    }

    /* ===============================
       DATA FETCHING & GENERATION
    =============================== */

    async generateRoadmap() {
        const btn = select('#btn-generate-roadmap');
        if (!btn) return;

        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;

        try {
            const response = await api.post('/mentor/roadmap/generate', {}, { suppressAuthRedirect: true });

            if (response.success && response.roadmap) {
                this.renderCards(response.roadmap.steps);
                showToast('Roadmap generated successfully!', 'success');
                const container = select('#roadmap-container');
                if (container) container.scrollIntoView({ behavior: 'smooth' });
            } else {
                showToast(response.message || 'Failed to generate.', 'error');
            }
        } catch (e) {
            console.error(e);
            if (e.message.includes('login') || e.message.includes('Unauthenticated') || e.message.includes('Unauthorized')) {
                showConfirm(
                    "Anda perlu login untuk menyimpan dan melihat Roadmap bisnis Anda. Login sekarang?",
                    () => { window.location.href = '/login'; },
                    () => { }
                );
            } else {
                showToast(e.message || 'Failed to generate roadmap', 'error');
            }
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    async loadRoadmap() {
        try {
            const response = await api.get('/mentor/roadmap');
            if (response.success && response.roadmap) {
                const container = select('#roadmap-container');
                if (container) container.classList.remove('hidden');

                this.renderCards(response.roadmap.steps);
            } else {
                this.renderEmptyState();
            }
        } catch (e) {
            console.error(e);
            this.renderEmptyState();
        }
    }

    renderEmptyState() {
        const container = select('#roadmap-container');
        if (container) container.classList.add('hidden');
        const btn = select('#btn-generate-roadmap');
        if (btn) btn.disabled = false;
    }

    /* ===============================
       CARD RENDERING
    =============================== */

    renderCards(steps) {
        if (!this.cardsWrapper) return;

        this.cardsWrapper.innerHTML = '';

        steps.forEach((step, index) => {
            const isCompleted = step.status === 'completed';
            const isLocked = step.status === 'locked';
            const tools = this.getRecommendedTools(step.strategy_tag);

            // Layout Alignment Logic
            const alignClass = index % 2 === 0 ? 'md:justify-start' : 'md:justify-end';

            const card = document.createElement('div');
            card.className = `roadmap-card flex ${alignClass} justify-center w-full step-item group perspective-1000`;
            card.dataset.stepId = step.id;
            card.dataset.index = index;
            card.dataset.status = step.status;

            // Inner visual card HTML
            const innerHTML = `
                <div class="
                    relative
                    w-full md:w-5/12
                    bg-white dark:bg-slate-800
                    rounded-2xl
                    border ${isCompleted ? 'border-emerald-500/50 shadow-[0_0_15px_rgba(16,185,129,0.1)]' : 'border-slate-200 dark:border-slate-700'}
                    shadow-sm
                    transition-all duration-500
                    hover:shadow-lg hover:-translate-y-1
                    overflow-hidden
                    z-20
                " onclick="roadmapHandler.toggleCard(this)">
                    
                    <!-- Header -->
                    <div class="p-6 md:p-8 flex justify-between items-start gap-4 cursor-pointer">
                        <div>
                            <div class="text-[10px] font-bold uppercase tracking-wider mb-2 ${isCompleted ? 'text-emerald-500' : 'text-slate-400'}">
                                STEP ${index + 1} • ${step.strategy_tag}
                            </div>
                            <h3 class="font-bold text-xl md:text-2xl text-slate-900 dark:text-white mb-2 leading-tight">
                                ${step.title}
                            </h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400 line-clamp-2">
                                ${step.description}
                            </p>
                        </div>
                        <div class="transform transition-transform duration-300 chevron-icon mt-2 text-slate-400">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>

                    <!-- Body (Expandable) -->
                    <div class="card-body hidden border-t border-slate-100 dark:border-slate-700/50 bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="p-6 md:p-8 space-y-6">
                            
                            <!-- Actions -->
                            <div>
                                <h5 class="text-xs font-bold uppercase text-slate-400 mb-4 tracking-wider">Action Items</h5>
                                <div class="space-y-3 text-left" onclick="event.stopPropagation()">
                                    ${step.actions.map(action => `
                                        <label class="flex items-start gap-3 p-3 rounded-xl hover:bg-white dark:hover:bg-slate-800 cursor-pointer transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-700 group/item">
                                            <div class="relative flex items-center mt-0.5">
                                                <input type="checkbox" 
                                                    class="peer h-5 w-5 rounded border-slate-300 dark:border-slate-600 text-emerald-600 focus:ring-emerald-500 accent-emerald-500 cursor-pointer roadmap-action-checkbox transition-all"
                                                    ${action.is_completed ? 'checked' : ''}
                                                    ${isLocked ? 'disabled' : ''}
                                                    data-action-id="${action.id}"
                                                >
                                            </div>
                                            <span class="text-sm font-medium text-slate-600 dark:text-slate-300 ${action.is_completed ? 'text-emerald-600 line-through decoration-emerald-500/50' : ''} peer-checked:text-emerald-600 peer-checked:line-through peer-checked:decoration-emerald-500/50 transition-colors select-none group-hover/item:text-slate-900 dark:group-hover/item:text-white">
                                                ${action.action_text}
                                            </span>
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                            
                            <!-- Tools -->
                            ${tools.length > 0 ? `
                            <div class="pt-6 border-t border-slate-200 dark:border-slate-700 border-dashed">
                                <h5 class="text-xs font-bold uppercase text-slate-400 mb-4 tracking-wider">Recommended Tools</h5>
                                <div class="flex flex-wrap gap-3">
                                    ${tools.map(tool => `
                                        <a href="${tool.url}" target="_blank" onclick="event.stopPropagation()" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-medium text-slate-600 dark:text-slate-300 hover:border-blue-400 hover:text-blue-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-300">
                                            <img src="https://www.google.com/s2/favicons?domain=${tool.domain}&sz=32" class="w-4 h-4 rounded-full" alt="${tool.name}">
                                            ${tool.name}
                                        </a>
                                    `).join('')}
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Locked Overlay -->
                    ${isLocked ? `
                    <div class="absolute inset-0 bg-slate-100/60 dark:bg-slate-900/60 backdrop-blur-[2px] flex items-center justify-center z-30 cursor-not-allowed transition-all duration-300" onclick="event.stopPropagation()">
                         <div class="bg-white dark:bg-slate-800 px-6 py-3 rounded-full shadow-xl flex items-center gap-3 text-slate-500 text-sm font-bold border border-slate-200 dark:border-slate-700 animate-pulse">
                            <i class="fas fa-lock text-slate-400"></i> Locked Step
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;

            card.innerHTML = innerHTML;
            this.cardsWrapper.appendChild(card);
        });

        this.bindCheckboxes();
        this.initObservers();
        this.initScrollAnimation();

        // Initial responsive check
        this.handleResponsiveMode();

        // Initial render of connectors
        requestAnimationFrame(() => this.renderConnectors());
    }

    /* ===============================
       INTERACTION LOGIC (Expand/Checkbox)
    =============================== */

    toggleCard(cardElement) {
        // cardElement is the inner div passed by onclick
        const body = cardElement.querySelector('.card-body');
        const chevron = cardElement.querySelector('.chevron-icon');
        const isHidden = body.classList.contains('hidden');

        if (isHidden) {
            // OPEN
            body.classList.remove('hidden');
            body.style.maxHeight = '0';
            body.style.opacity = '0';
            body.style.overflow = 'hidden';

            void body.offsetHeight; // Force reflow

            body.style.transition = 'max-height 0.6s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.5s ease-out';
            body.style.maxHeight = body.scrollHeight + 'px';
            body.style.opacity = '1';

            chevron.classList.add('rotate-180');
        } else {
            // CLOSE
            body.style.maxHeight = body.scrollHeight + 'px';
            body.style.opacity = '1';
            body.style.overflow = 'hidden';

            void body.offsetHeight; // Force reflow

            body.style.transition = 'max-height 0.5s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.4s ease-in';
            body.style.maxHeight = '0';
            body.style.opacity = '0';

            chevron.classList.remove('rotate-180');

            setTimeout(() => {
                if (body.style.maxHeight === '0px') {
                    body.classList.add('hidden');
                    body.style.removeProperty('max-height');
                    body.style.removeProperty('opacity');
                    body.style.removeProperty('transition');
                    body.style.removeProperty('overflow');
                }
            }, 500);
        }
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

        // Optimistic Update
        if (label) {
            if (isChecked) label.classList.add('text-emerald-600', 'line-through', 'decoration-emerald-500/50');
            else label.classList.remove('text-emerald-600', 'line-through', 'decoration-emerald-500/50');
        }

        try {
            const response = await api.post(`/mentor/roadmap/action/${actionId}/toggle`);

            if (response.success && response.step_completed) {
                showToast('Step Completed! Unleashing next level...', 'success');
                this.loadRoadmap(); // Reload to update status and unlocks
            }
        } catch (e) {
            console.error(e);
            // Revert
            checkbox.checked = !isChecked;
            if (label) {
                if (isChecked) label.classList.remove('text-emerald-600', 'line-through', 'decoration-emerald-500/50');
                else label.classList.add('text-emerald-600', 'line-through', 'decoration-emerald-500/50');
            }
            showToast('Failed to update action', 'error');
        }
    }


    /* ===============================
       CONNECTOR ENGINE (ULTRA PREMIUM)
    =============================== */

    renderConnectors() {
        if (!this.svg) return;

        this.svg.innerHTML = this.getGradientDefs();

        this.cards = Array.from(this.cardsWrapper.querySelectorAll('.roadmap-card'));
        if (this.cards.length < 2) return;

        this.cards.forEach((card, i) => {
            if (i === this.cards.length - 1) return;
            this.createConnection(card, this.cards[i + 1], i);
        });
    }

    createConnection(cardA, cardB, index) {
        // Target inner visuals for better accuracy
        const visualA = cardA.firstElementChild; // The .relative.w-full div
        const visualB = cardB.firstElementChild;

        const rectA = visualA.getBoundingClientRect();
        const rectB = visualB.getBoundingClientRect();
        const containerRect = this.container.getBoundingClientRect();

        // Start: Center Bottom of Visual A
        const startX = rectA.left + rectA.width / 2 - containerRect.left;
        const startY = rectA.bottom - containerRect.top;

        // End: Center Top of Visual B
        const endX = rectB.left + rectB.width / 2 - containerRect.left;
        const endY = rectB.top - containerRect.top;

        // Dynamic curve logic
        const curveOffset = this.isMobile ? 40 : 80;

        // Bezier points
        // Creates a smooth S-curve flow from Top to Bottom
        const d = `
            M ${startX},${startY}
            C ${startX},${startY + curveOffset}
              ${endX},${endY - curveOffset}
              ${endX},${endY}
        `;

        // Background Path (Subtle guide)
        const bgPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
        bgPath.setAttribute("d", d);
        bgPath.setAttribute("stroke", "#e2e8f0"); // slate-200
        bgPath.setAttribute("stroke-width", "3");
        bgPath.setAttribute("fill", "none");
        bgPath.setAttribute("class", "dark:stroke-slate-700/50");
        this.svg.appendChild(bgPath);

        // Active State Logic
        const stepStatus = cardA.dataset.status;
        const isActive = (stepStatus === 'completed');

        if (isActive) {
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", d);
            path.setAttribute("fill", "none");
            path.setAttribute("stroke", "url(#flowGradient)");
            path.setAttribute("stroke-width", "3");
            path.setAttribute("class", "connector-path opacity-0");
            path.setAttribute("filter", "url(#neon-glow)");

            this.svg.appendChild(path);

            this.animatePathDraw(path);
            this.addFlowParticle(path);
        }
    }

    /* ===============================
       ANIMATION
    =============================== */

    animatePathDraw(path) {
        const length = path.getTotalLength();

        path.style.strokeDasharray = length;
        path.style.strokeDashoffset = length;

        requestAnimationFrame(() => {
            // Force Reflow
            path.getBoundingClientRect();

            path.style.transition = "stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1)";
            path.style.strokeDashoffset = "0";
            path.classList.remove('opacity-0');
        });
    }

    addFlowParticle(path) {
        const circle = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        circle.setAttribute("r", "4");
        circle.setAttribute("fill", "#fff");
        circle.setAttribute("filter", "url(#neon-glow)");

        const animateMotion = document.createElementNS("http://www.w3.org/2000/svg", "animateMotion");
        animateMotion.setAttribute("dur", "3s");
        animateMotion.setAttribute("repeatCount", "indefinite");
        animateMotion.setAttribute("rotate", "auto");
        // Ease in-out for natural flow
        animateMotion.setAttribute("calcMode", "spline");
        animateMotion.setAttribute("keySplines", "0.4 0 0.2 1; 0.4 0 0.2 1");
        animateMotion.setAttribute("keyTimes", "0;0.5;1"); // Loop logic? Standard linear is safer for loop:
        // Reset to linear for continuous loop
        animateMotion.removeAttribute("calcMode");
        animateMotion.removeAttribute("keySplines");
        animateMotion.removeAttribute("keyTimes");


        const mpath = document.createElementNS("http://www.w3.org/2000/svg", "mpath");
        // Unique ID for path ref
        const pathId = `path-${Math.random().toString(36).substr(2, 9)}`;
        path.setAttribute("id", pathId);

        mpath.setAttributeNS("http://www.w3.org/1999/xlink", "href", `#${pathId}`);

        animateMotion.appendChild(mpath);
        circle.appendChild(animateMotion);
        this.svg.appendChild(circle);
    }

    /* ===============================
       OBSERVERS
    =============================== */

    initObservers() {
        const resizeObserver = new ResizeObserver(() => {
            requestAnimationFrame(() => this.renderConnectors());
        });

        if (this.container) resizeObserver.observe(this.container);

        // Observe cards for expansion
        const visuals = this.cardsWrapper.querySelectorAll('.roadmap-card > div'); // Inner visuals
        visuals.forEach(v => resizeObserver.observe(v));

        window.addEventListener("resize", () => {
            this.handleResponsiveMode();
            this.renderConnectors();
        });
    }

    /* ===============================
       SCROLL TRIGGERED ANIMATION
    =============================== */

    initScrollAnimation() {
        // Observer to reveal connectors as they come into view
        const observer = new IntersectionObserver(
            entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.renderConnectors();
                        // Optional: Could trigger staggered reveal of cards
                    }
                });
            },
            { threshold: 0.1 }
        );

        if (this.cardsWrapper) observer.observe(this.cardsWrapper);
    }

    /* ===============================
       RESPONSIVE LOGIC
    =============================== */

    handleResponsiveMode() {
        this.isMobile = window.innerWidth < 768;

        if (!this.cardsWrapper) return;

        // Dynamic Spacing Adjustment
        if (this.isMobile) {
            this.cardsWrapper.classList.remove("space-y-24");
            this.cardsWrapper.classList.add("space-y-16");
        } else {
            this.cardsWrapper.classList.remove("space-y-16");
            this.cardsWrapper.classList.add("space-y-24");
        }
    }

    /* ===============================
       SVG TOOLS
    =============================== */

    getGradientDefs() {
        return `
            <defs>
                <linearGradient id="flowGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#10B981" />
                    <stop offset="100%" stop-color="#22D3EE" />
                </linearGradient>
                <filter id="neon-glow" x="-50%" y="-50%" width="200%" height="200%">
                    <feGaussianBlur in="SourceGraphic" stdDeviation="2" result="blur5" />
                    <feGaussianBlur in="SourceGraphic" stdDeviation="4" result="blur10" />
                    <feMerge>
                        <feMergeNode in="blur5" />
                        <feMergeNode in="blur10" />
                        <feMergeNode in="SourceGraphic" />
                    </feMerge>
                </filter>
            </defs>
        `;
    }

    // Helpers
    getRecommendedTools(tag) {
        const toolsMap = {
            'Traffic Scaling': [
                { name: 'Canva', url: 'https://canva.com', domain: 'canva.com' },
                { name: 'CapCut', url: 'https://capcut.com', domain: 'capcut.com' },
                { name: 'Meta Ads', url: 'https://business.facebook.com/', domain: 'facebook.com' }
            ],
            'Margin Improvement': [
                { name: 'Google Sheets', url: 'https://sheets.google.com', domain: 'google.com' },
                { name: 'Excel', url: 'https://office.com', domain: 'office.com' }
            ],
            'Monetization Expansion': [
                { name: 'Midtrans', url: 'https://midtrans.com', domain: 'midtrans.com' },
                { name: 'Xendit', url: 'https://xendit.co', domain: 'xendit.co' }
            ]
        };
        return toolsMap[tag] || [{ name: 'Canva', url: 'https://canva.com', domain: 'canva.com' }];
    }
}

export const roadmapHandler = new RoadmapHandler();
window.roadmapHandler = roadmapHandler;
