/**
 * Mentor Lab - Frontend Logic V2
 * Handles Mode Switching, Animations, AJAX Calculations, and Dashboard Rendering.
 */

import { api } from '../services/api.js';
import { formatCurrency, select, showToast } from '../utils/helpers.js';

class MentorLab {
    constructor() {
        this.mode = 'optimizer'; // 'optimizer' or 'planner'
        this.baseline = null;
        this.optimizerResult = null;
        this.plannerResult = null;
        this.updateTimeout = null;

        this.init();
    }

    init() {
        // Mode Switching
        const modeOptimizerBtn = select('#mode-optimizer-btn');
        const modePlannerBtn = select('#mode-planner-btn');

        if (modeOptimizerBtn && modePlannerBtn) {
            modeOptimizerBtn.addEventListener('click', () => this.switchMode('optimizer'));
            modePlannerBtn.addEventListener('click', () => this.switchMode('planner'));
        }

        // Form Submit
        const form = select('#mentor-form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.calculateBaseline();
            });
        }

        // Planner Preset
        const businessType = select('#planner-type');
        if (businessType) {
            businessType.addEventListener('change', () => this.loadPreset(businessType.value));
        }

        // Sliders
        this.bindSliders();

        // Upsell Simulator
        const btnUpsell = select('#btn-simulate-upsell');
        if (btnUpsell) {
            btnUpsell.addEventListener('click', () => this.simulateUpsell());
        }

        // RESTORE SESSION
        this.restoreSession();
    }

    async restoreSession() {
        try {
            const response = await api.get('/mentor/simulation/latest');
            if (response && response.success) {

                // Store Key Results
                // Note: The backend only returns the LATEST simulation. 
                // It doesn't return separate results for optimizer and planner if they were different sessions.
                // We'll just assume the latest one applies to its mode.
                if (response.mode === 'optimizer') {
                    this.optimizerResult = response;
                } else {
                    this.plannerResult = response;
                }

                // Restore Inputs
                const input = response.input;
                if (response.mode === 'optimizer') {
                    if (select('#opt-traffic')) select('#opt-traffic').value = input.traffic;
                    if (select('#opt-conversion')) select('#opt-conversion').value = input.conversion;
                    if (select('#opt-price')) select('#opt-price').value = input.price;
                    if (select('#opt-cost')) select('#opt-cost').value = input.cost;
                    if (select('#opt-fixed')) select('#opt-fixed').value = input.fixed_cost;
                } else {
                    // Start Planner inputs
                    if (select('#plan-target')) select('#plan-target').value = input.target_revenue;
                    // Only specific fields map directly back for planner if we reverse logic. 
                    // For now just ensure target is there.
                }

                // Restore Mode (and render)
                if (response.mode) this.switchMode(response.mode);
            }
        } catch (e) {
            console.log("No previous session found or auth error", e);
        }
    }

    switchMode(mode) {
        this.mode = mode;

        // UI Toggle
        const optBtn = select('#mode-optimizer-btn');
        const planBtn = select('#mode-planner-btn');

        optBtn.classList.toggle('bg-emerald-600', mode === 'optimizer');
        optBtn.classList.toggle('text-white', mode === 'optimizer');
        optBtn.classList.toggle('bg-slate-100', mode !== 'optimizer');
        optBtn.classList.toggle('text-slate-600', mode !== 'optimizer');

        planBtn.classList.toggle('bg-blue-600', mode === 'planner');
        planBtn.classList.toggle('text-white', mode === 'planner');
        planBtn.classList.toggle('bg-slate-100', mode !== 'planner');
        planBtn.classList.toggle('text-slate-600', mode !== 'planner');

        // Form Visibility
        select('#optimizer-inputs').classList.toggle('hidden', mode !== 'optimizer');
        select('#planner-inputs').classList.toggle('hidden', mode !== 'planner');

        // Logic: Restore Dashboard if result exists for this mode
        const storedResult = (mode === 'optimizer') ? this.optimizerResult : this.plannerResult;

        if (storedResult) {
            this.baseline = storedResult.baseline;
            this.renderDashboard(storedResult, false);
        } else {
            // Hide Dashboard if no data for this mode yet
            select('#mentor-board-container').classList.add('min-h-[600px]'); // Keep height
            select('#mentor-dashboard').classList.add('hidden');

            // Should we load preset? Only if planner and no result
            if (mode === 'planner') {
                const currentType = select('#planner-type').value;
                if (!this.plannerResult) {
                    this.loadPreset(currentType);
                }
            }
        }
    }

    async loadPreset(type) {
        try {
            const data = await api.get(`/mentor/preset?type=${type}`);
            select('#planner-conversion').value = data.conversion;
            select('#planner-margin').value = (data.margin * 100).toFixed(0);
        } catch (e) {
            console.error(e);
        }
    }

    async calculateBaseline() {
        this.showLoading();

        // Simulate "Processing" delay for UX
        await new Promise(r => setTimeout(r, 2000));

        try {
            let payload = {};

            if (this.mode === 'optimizer') {
                payload = {
                    mode: 'optimizer',
                    traffic: select('#opt-traffic').value,
                    conversion: select('#opt-conversion').value,
                    price: select('#opt-price').value,
                    cost: select('#opt-cost').value,
                    fixed_cost: select('#opt-fixed').value,
                };
            } else {
                // Planner Mode: Reverse Engineer Inputs
                const targetIncome = parseFloat(select('#plan-target').value) || 0;
                const price = parseFloat(select('#plan-price').value) || 0;
                const conversion = parseFloat(select('#planner-conversion').value) || 1;
                const margin = parseFloat(select('#planner-margin').value) / 100;

                // Simple reverse calc for baseline
                const cost = price * (1 - margin);
                const marginPerUnit = price - cost;

                // Units needed to hit target profit (Assuming Target == Net Profit for simplicity, ignoring fixed cost in presets)
                const units = marginPerUnit > 0 ? targetIncome / marginPerUnit : 0;
                const trafficNeeded = units / (conversion / 100);

                payload = {
                    mode: 'planner',
                    traffic: Math.ceil(trafficNeeded),
                    conversion: conversion,
                    price: price,
                    cost: cost,
                    fixed_cost: 0,
                    target_revenue: targetIncome // Pass for feasibility check
                };
            }
            // Use API Route (Sanctum Auth)
            const response = await api.post('/mentor/calculate', payload);
            this.baseline = response.baseline;

            // Save to local persistence var
            if (this.mode === 'optimizer') {
                this.optimizerResult = response;
            } else {
                this.plannerResult = response;
            }

            this.renderDashboard(response, false);

        } catch (e) {
            console.error(e);
            showToast(e.message || "Calculation failed", 'error');
        } finally {
            this.hideLoading();
        }
    }

    renderDashboard(data, allowReset = true) {
        const d = select('#mentor-dashboard');
        d.classList.remove('hidden');

        // 1. BASELINE SUMMARY
        select('#res-revenue').innerText = formatCurrency(data.baseline.revenue);
        select('#res-gross').innerText = formatCurrency(data.baseline.gross_profit);
        select('#res-net').innerText = formatCurrency(data.baseline.net_profit);
        select('#res-margin').innerText = (data.baseline.margin * 100).toFixed(1) + '%';

        // Planner Gap Alert
        const gapAlert = select('#planner-gap-alert');
        if (this.mode === 'planner') {
            // Logic: Check if result meets target? 
            // Actually payload sent optimized traffic, so it SHOULD hit target. 
            // But let's show breakdown instead.
            gapAlert.classList.add('hidden');
        } else {
            gapAlert.classList.add('hidden');
        }

        // 2. SENSITIVITY ANALYSIS
        select('#sens-traffic-val').innerText = '+' + formatCurrency(data.sensitivity.traffic_impact);
        select('#sens-conv-val').innerText = '+' + formatCurrency(data.sensitivity.conversion_impact);
        select('#sens-price-val').innerText = '+' + formatCurrency(data.sensitivity.price_impact);

        // Normalize bars (Max value = 100%)
        const impacts = [data.sensitivity.traffic_impact, data.sensitivity.conversion_impact, data.sensitivity.price_impact];
        const maxImpact = Math.max(...impacts);

        select('#sens-traffic-bar').style.width = ((data.sensitivity.traffic_impact / maxImpact) * 100) + '%';
        select('#sens-conv-bar').style.width = ((data.sensitivity.conversion_impact / maxImpact) * 100) + '%';
        select('#sens-price-bar').style.width = ((data.sensitivity.price_impact / maxImpact) * 100) + '%';

        // 3. BREAK-EVEN
        select('#be-units').innerText = data.break_even.break_even_units;
        select('#be-traffic').innerText = data.break_even.break_even_traffic;

        if (data.break_even.is_traffic_warning) {
            select('#be-warning').classList.remove('hidden');
            select('#be-success').classList.add('hidden');
        } else {
            select('#be-warning').classList.add('hidden');
            select('#be-success').classList.remove('hidden');
        }

        // 4. DIAGNOSTIC RESULT
        select('#diag-primary').innerText = data.diagnostic.primary_issue;
        select('#diag-rec').innerText = this.getRecommendation(data.diagnostic.primary_issue);

        // 5. PLANNER SPECIFIC: TARGET BREAKDOWN
        const plannerBreakdown = select('#planner-breakdown');
        if (this.mode === 'planner') {
            plannerBreakdown.classList.remove('hidden');

            select('#plan-units').innerText = data.baseline.units_sold.toLocaleString();
            select('#plan-traffic').innerText = data.baseline.traffic.toLocaleString();
            select('#plan-conversion').innerText = data.baseline.conversion_rate.toFixed(1) + '%';

            // Est Budget (Assume CPC 2000 for now or logic?)
            // Let's take global CPC assumption or just placeholder logic
            const cpc = 2000;
            const budget = data.baseline.traffic * cpc;
            select('#plan-budget').innerText = formatCurrency(budget);

            // Feasibility Badge
            const feasibiltyEl = select('#plan-feasibility');
            // Logic: Is Traffic realistic?
            // > 100k visitors for new business = High Risk
            if (data.baseline.traffic > 50000) {
                feasibiltyEl.innerText = "HIGH RISK";
                feasibiltyEl.className = "bg-rose-100 text-rose-700 px-3 py-1 rounded-full text-xs font-bold uppercase";
            } else if (data.baseline.traffic > 10000) {
                feasibiltyEl.innerText = "CHALLENGING";
                feasibiltyEl.className = "bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold uppercase";
            } else {
                feasibiltyEl.innerText = "REALISTIC";
                feasibiltyEl.className = "bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full text-xs font-bold uppercase";
            }

        } else {
            plannerBreakdown.classList.add('hidden');
        }

        // Reset Sliders & Upsell
        if (allowReset) {
            this.resetSliders();
        }
        select('#upsell-result').classList.add('hidden');
    }

    getRecommendation(issue) {
        if (issue.includes('Konversi')) return "Improve Offer, Landing Page Quality, Trust Signals.";
        if (issue.includes('Margin')) return "Increase Price, Bundle Products, Negotiate COGS.";
        if (issue.includes('Traffic')) return "Expand Organic Content, Paid Ads, Influencer Marketing.";
        return "Scale smoothly.";
    }

    bindSliders() {
        ['traffic', 'conversion', 'price'].forEach(type => {
            const slider = select(`#slider-${type}`);
            if (slider) {
                slider.addEventListener('input', (e) => {
                    this.updateScenario(type, parseFloat(e.target.value));
                    let suffix = '%'; // Default
                    // Display text update
                    select(`#val-${type}`).innerText = (e.target.value > 0 ? '+' : '') + e.target.value + suffix;
                });
            }
        });
    }

    updateScenario(type, value) {
        if (this.updateTimeout) clearTimeout(this.updateTimeout);
        this.updateTimeout = setTimeout(() => {
            // Re-render immediately for responsiveness?
            // For now, allow scenario update.
            this.runSimulation(type, value);
        }, 50);
    }

    async runSimulation(changedType, changedValue) {
        if (!this.baseline) return;

        // Construct changes payload
        let changes = {};

        // Read all slider values
        const tVal = parseFloat(select('#slider-traffic').value);
        const cVal = parseFloat(select('#slider-conversion').value);
        const pVal = parseFloat(select('#slider-price').value);

        changes['traffic_pct'] = tVal / 100;
        changes['conversion_delta'] = cVal; // Absolute
        changes['price_pct'] = pVal / 100;

        try {
            const payload = {
                // We need to send original inputs. 
                // Luckily FinancialEngine returns them in baseline object.
                traffic: this.baseline.traffic,
                conversion: this.baseline.conversion_rate,
                price: this.baseline.price,
                cost: this.baseline.cost,
                fixed_cost: this.baseline.fixed_cost,
                changes: changes
            };

            // Use API Route
            const response = await api.post('/mentor/simulate', payload);

            // Update Projected Revenue in UI
            const sim = response.simulation;

            // Update "Projected Revenue" & "Growth"
            const resultBox = select('#sim-growth').parentElement.parentElement.querySelector('h4');
            if (resultBox) resultBox.innerText = formatCurrency(sim.new_revenue);

            const growthEl = select('#sim-growth');
            const growth = (sim.revenue_change * 100).toFixed(1);
            growthEl.innerText = (growth > 0 ? '+' : '') + growth + '%';
            growthEl.className = `text-lg font-black ${growth >= 0 ? 'text-emerald-500' : 'text-rose-500'}`;

        } catch (e) {
            console.warn(e);
        }
    }

    async simulateUpsell() {
        if (!this.baseline) return;

        const price = select('#up-price').value;
        const rate = select('#up-rate').value;

        if (!price || !rate) return showToast("Please fill upsell fields", "warning");

        const btn = select('#btn-simulate-upsell');
        const startHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Simulating...';

        try {
            const payload = {
                traffic: this.baseline.traffic,
                conversion: this.baseline.conversion_rate,
                price: this.baseline.price,
                cost: this.baseline.cost,
                fixed_cost: this.baseline.fixed_cost,
                upsell_price: price,
                take_rate: rate
            };

            // Use API Route
            const response = await api.post('/mentor/upsell', payload);
            const data = response.upsell;

            select('#upsell-result').classList.remove('hidden');
            select('#up-new-revenue').innerText = formatCurrency(data.total_revenue);
            select('#up-increase').innerText = '+' + data.increase_pct.toFixed(1) + '%';

        } catch (e) {
            console.error(e);
        } finally {
            btn.innerHTML = startHtml; // Restore button text
        }
    }

    resetSliders() {
        ['traffic', 'conversion', 'price'].forEach(t => {
            select(`#slider-${t}`).value = 0;
            select(`#val-${t}`).innerText = '0%';
        });
        const simGrowth = select('#sim-growth');
        if (simGrowth) {
            simGrowth.innerText = '+0%';
            const h4 = simGrowth.parentElement.parentElement.querySelector('h4');
            if (h4) h4.innerText = "Coming Soon (Live)";
        }
    }

    showLoading() {
        select('#mentor-board-container').scrollIntoView({ behavior: 'smooth' });
        select('#mentor-loading').classList.remove('hidden');
        select('#mentor-dashboard').classList.add('hidden');

        // Randomize loading text
        const texts = [
            "Analyzing your business structure...",
            "Calculating financial baseline...",
            "Identifying bottlenecks...",
            "Projecting growth scenarios..."
        ];
        let i = 0;
        this.loadingInterval = setInterval(() => {
            const txt = select('#loading-text');
            if (txt) txt.innerText = texts[i % texts.length];
            i++;
        }, 800);
    }

    hideLoading() {
        if (this.loadingInterval) clearInterval(this.loadingInterval);
        select('#mentor-loading').classList.add('hidden');
    }
}

export const mentorLab = new MentorLab();
