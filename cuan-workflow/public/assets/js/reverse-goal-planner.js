/**
 * Reverse Goal Planner 2.0
 * Handles form submission, calculation requests, and result rendering.
 */

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('reverse-planner-form');
    const calculateBtn = document.getElementById('rp-calculate-btn');
    const resultsContainer = document.getElementById('rp-results');

    if (!form) return; // Exit if element not found

    // Initialize from saved data
    if (window.savedPlannerData) {
        const data = window.savedPlannerData;

        // Populate Inputs
        if (document.getElementById('rp-model')) document.getElementById('rp-model').value = data.business_model;
        if (document.getElementById('rp-target-profit')) document.getElementById('rp-target-profit').value = data.target_profit;
        if (document.getElementById('rp-capital')) document.getElementById('rp-capital').value = data.capital_available;
        if (document.getElementById('rp-timeline')) document.getElementById('rp-timeline').value = data.timeline_days;
        if (document.getElementById('rp-hours')) document.getElementById('rp-hours').value = data.hours_per_day;
        if (document.getElementById('rp-price')) document.getElementById('rp-price').value = data.selling_price; // Note: may be null in some versions
        if (document.getElementById('rp-strategy')) document.getElementById('rp-strategy').value = data.traffic_strategy;

        // Prepare context for renderResults adapter
        const mockResult = {
            logic_version: data.logic_version,
            input: {
                business_model: data.business_model,
                traffic_strategy: data.traffic_strategy,
                target_profit: data.target_profit,
                timeline_days: data.timeline_days,
                capital_available: data.capital_available,
                hours_per_day: data.hours_per_day,
                assumed_margin: data.assumed_margin,
                assumed_conversion: data.assumed_conversion,
                assumed_cpc: data.assumed_cpc
            },
            output: {
                unit_profit: data.unit_net_profit,
                required_units: data.required_units,
                required_traffic: data.required_traffic,
                total_ad_spend: data.required_ad_budget,
                execution_load_ratio: data.execution_load_ratio,
                selling_price: data.selling_price || 0
            },
            scores: {
                goal_status: data.risk_level === 'Realistic' ? 'Siap Gaskeun' : (data.risk_level === 'Challenging' ? 'Butuh Penyesuaian' : 'Terlalu Berat'),
                constraint_message: JSON.parse(data.constraint_snapshot || '{}').message || 'Berdasarkan data tersimpan.',
                status_color: data.risk_level === 'Realistic' ? 'green' : (data.risk_level === 'Challenging' ? 'yellow' : 'red'),
                learning_moment: 'Rencana ini dimuat dari sesi terakhir kamu.',
                recommendations: [] // Not saved, but can be empty
            }
        };

        // Render Results
        renderResults(mockResult);
        resultsContainer.classList.remove('hidden');
        window.latestSessionId = data.id;

        // Setup baseline for simulator (mimic successful fetch behavior)
        const cogs = data.selling_price * (1 - (data.assumed_margin / 100));
        window.manualBaseline = {
            price: data.selling_price,
            traffic: data.required_traffic,
            conversion_rate: data.assumed_conversion,
            cogs: cogs,
            fixed_cost: 0,
            ad_spend: data.required_ad_budget
        };
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // 1. Collect Data
        const payload = {
            business_model: document.getElementById('rp-model').value,
            target_profit: document.getElementById('rp-target-profit').value,
            capital_available: document.getElementById('rp-capital').value,
            timeline_days: document.getElementById('rp-timeline').value,
            hours_per_day: document.getElementById('rp-hours').value,
            selling_price: document.getElementById('rp-price').value,
            traffic_strategy: document.getElementById('rp-strategy').value
        };

        // 2. Show Loading State
        const originalBtnText = calculateBtn.innerHTML;
        calculateBtn.disabled = true;
        calculateBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Analyzing...';

        // 3. Send Request
        fetch('/reverse-planner/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify(payload)
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderResults(data.data);

                    // Construct Manual Baseline for Profit Simulator
                    const rInput = data.data.input;
                    const rOutput = data.data.output;

                    const cogs = rOutput.selling_price * (1 - (rInput.assumed_margin / 100));

                    const manualBaseline = {
                        price: rOutput.selling_price,
                        traffic: rOutput.required_traffic,
                        conversion_rate: rInput.assumed_conversion,
                        cogs: cogs,
                        fixed_cost: 0, // Not captured in Planner
                        ad_spend: rOutput.total_ad_spend
                    };

                    // Broadcast to Profit Simulator
                    window.manualBaseline = manualBaseline;
                    window.dispatchEvent(new CustomEvent('reverse-goal-planner:update', {
                        detail: manualBaseline
                    }));

                    resultsContainer.classList.remove('hidden');
                    // Smooth scroll to results
                    resultsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    alert('Calculation failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                calculateBtn.disabled = false;
                calculateBtn.innerHTML = originalBtnText;
            });
    });

    function renderResults(data) {
        // Unpack data
        const input = data.input;
        const output = data.output;
        const scores = data.scores; // Contains Mentally Safe fields

        // 1. Update Version (Safe Check)
        const versionEl = document.getElementById('rp-logic-version');
        if (versionEl) {
            versionEl.textContent = data.logic_version || 'v2.0';
        }

        // 2. Update Simple Milestones
        updateText('rp-req-units', formatNumber(output.required_units) + ' Units');
        updateText('rp-daily-units', formatNumber(Math.ceil(output.required_units / input.timeline_days)));

        updateText('rp-req-traffic', formatNumber(output.required_traffic) + ' Visitors');
        updateText('rp-req-budget', formatCurrency(output.total_ad_spend));

        // 3. Render Status Card (Mentally Safe)
        const statusCard = document.getElementById('rp-status-card');
        const statusIcon = document.getElementById('rp-status-icon');

        updateText('rp-goal-status', scores.goal_status);
        updateText('rp-constraint-msg', scores.constraint_message);

        // Remove old classes
        statusCard.classList.remove('border-emerald-500', 'border-amber-500', 'border-rose-500', 'bg-emerald-50', 'bg-amber-50', 'bg-rose-50', 'dark:bg-emerald-900/10', 'dark:bg-amber-900/10', 'dark:bg-rose-900/10');

        let iconClass = '';
        if (scores.status_color === 'green') {
            statusCard.classList.add('border-emerald-500', 'bg-emerald-50', 'dark:bg-emerald-900/10');
            iconClass = 'fas fa-check-circle text-emerald-500';
        } else if (scores.status_color === 'yellow') {
            statusCard.classList.add('border-amber-500', 'bg-amber-50', 'dark:bg-amber-900/10');
            iconClass = 'fas fa-exclamation-circle text-amber-500';
        } else {
            statusCard.classList.add('border-rose-500', 'bg-rose-50', 'dark:bg-rose-900/10');
            iconClass = 'fas fa-times-circle text-rose-500';
        }
        statusIcon.innerHTML = `<i class="${iconClass}"></i>`;

        // 4. Render Insight / Learning Moment
        const learningEl = document.getElementById('rp-learning-moment');
        learningEl.innerHTML = scores.learning_moment;

        // 5. Render Recommendations (if any)
        const recBox = document.getElementById('rp-recommendations-box');
        const recContainer = document.getElementById('rp-rec-container');
        recContainer.innerHTML = ''; // Clear

        if (scores.recommendations && scores.recommendations.length > 0) {
            recBox.classList.remove('hidden');
            scores.recommendations.forEach(rec => {
                const btn = document.createElement('button');
                btn.className = 'w-full text-left p-3 rounded-xl border transition-all hover:shadow-md bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:border-emerald-400 group';
                btn.innerHTML = `
                    <p class="text-xs font-bold uppercase text-emerald-600 mb-1">Solusi: ${rec.type.toUpperCase()}</p>
                    <p class="font-bold text-slate-800 dark:text-white text-sm group-hover:text-emerald-600">${rec.label}</p>
                    <p class="text-xs text-slate-500 mt-1">${rec.desc}</p>
                `;

                // Bind Click
                btn.onclick = (e) => {
                    e.preventDefault();
                    applyRecommendation(rec.type, rec.value);
                };
                recContainer.appendChild(btn);
            });
        } else {
            recBox.classList.add('hidden');
        }

        // 6. Setup "Why?" Modal Data
        // Simple logic: "Cukup" or "Kurang (gap %)"
        const capCover = output.total_ad_spend > 0 ? (input.capital_available / output.total_ad_spend) * 100 : 100;
        const execCover = output.daily_hours_needed > 0 ? (input.hours_per_day / output.daily_hours_needed) * 100 : 100;

        updateText('rp-why-capital', capCover >= 100 ? 'Cukup' : `Kurang (Cover ${Math.round(capCover)}%)`);
        updateText('rp-why-hours', execCover >= 100 ? 'Cukup' : `Overload (Cover ${Math.round(execCover)}%)`);
        updateText('rp-why-margin', input.assumed_margin + '%');

        // 7. Simulator Gate Event
        // If "Terlalu Berat", we lock. Else we unlock.
        const isLocked = (scores.status_color === 'red');
        window.dispatchEvent(new CustomEvent('reverse-goal-planner:update', {
            detail: {
                baseline: window.manualBaseline, // Was set in previous logic block (added in Step 1)
                isLocked: isLocked,
                statusMsg: scores.constraint_message
            }
        }));
    }

    // Apply Recommendation
    function applyRecommendation(type, value) {
        if (type === 'timeline') {
            document.getElementById('rp-timeline').value = value;
        } else if (type === 'target') {
            document.getElementById('rp-target-profit').value = value;
        } else if (type === 'hours') {
            document.getElementById('rp-hours').value = value;
        }
        // Auto submit
        document.getElementById('reverse-planner-form').dispatchEvent(new Event('submit'));
    }

    // "Why" Modal Handlers
    const whyBtn = document.getElementById('rp-why-btn');
    const whyModal = document.getElementById('rp-why-modal');
    const whyClose = document.getElementById('rp-why-close');

    if (whyBtn) {
        whyBtn.onclick = () => whyModal.classList.remove('hidden');
        whyClose.onclick = () => whyModal.classList.add('hidden');
    }

    // Helper: Update Text content safely
    function updateText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    // Helper: Format Currency (IDR)
    function formatCurrency(num) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    }

    // Helper: Format Number
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
});
