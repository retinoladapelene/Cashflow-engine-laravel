/**
 * Profit Simulator 3.0 (Mentally Safe)
 * Handles Zone Selection, Level Adjustment, and API Simulation.
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Config & Global State
    const config = {
        apiEndpoint: '/profit-simulator/simulate'
    };

    // 2. Listener for Reverse Goal Planner Updates
    window.addEventListener('reverse-goal-planner:update', function (e) {
        // Update global baseline
        window.manualBaseline = e.detail.baseline || e.detail; // Handle both direct and nested formats

        // Check Goal Status for Guardrail
        const isLocked = e.detail.isLocked || (window.manualBaseline && window.manualBaseline.risk_level === 'High Risk');
        const gate = document.getElementById('ps-gate-overlay');
        if (gate) {
            if (isLocked) gate.classList.remove('hidden');
            else gate.classList.add('hidden');
        }

        // Reset Simulator State ONLY if this is a fresh update (not init)
        if (!e.detail.isInit) {
            resetSimulatorUI();
        }

        // Store Session ID
        if (e.detail.session_id) {
            window.latestSessionId = e.detail.session_id;
        }
    });

    function resetSimulatorUI() {
        document.querySelectorAll('.zone-card').forEach(c => {
            c.classList.remove('border-blue-500', 'border-emerald-500', 'border-amber-500', 'border-rose-500', 'ring-2', 'ring-offset-2');
            const selector = c.querySelector('.level-selector');
            if (selector) selector.classList.add('hidden');
        });
        const resultEl = document.getElementById('simulation-result');
        if (resultEl) resultEl.classList.add('hidden');

        const defaultEl = document.getElementById('ps-default-state');
        if (defaultEl) defaultEl.classList.remove('hidden');
    }

    // 3. Zone Selection Logic (Single Focus)
    const zones = document.querySelectorAll('.zone-card');
    zones.forEach(zone => {
        zone.addEventListener('click', function (e) {
            if (e.target.classList.contains('level-btn')) return;

            zones.forEach(z => {
                z.classList.remove('border-blue-500', 'border-emerald-500', 'border-amber-500', 'border-rose-500', 'ring-2', 'ring-offset-2');
                const selector = z.querySelector('.level-selector');
                if (selector) selector.classList.add('hidden');
            });

            const zoneType = this.dataset.zone;
            let borderColor = 'border-blue-500';
            if (zoneType === 'conversion') borderColor = 'border-emerald-500';
            if (zoneType === 'pricing') borderColor = 'border-amber-500';
            if (zoneType === 'cost') borderColor = 'border-rose-500';

            this.classList.add(borderColor, 'ring-2', 'ring-offset-2');
            const selector = this.querySelector('.level-selector');
            if (selector) selector.classList.remove('hidden');
        });
    });

    // 4. Level Selection & Simulation Trigger
    const levelBtns = document.querySelectorAll('.level-btn');
    levelBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const parent = this.closest('.level-selector');
            parent.querySelectorAll('.level-btn').forEach(b => b.classList.remove('bg-slate-200', 'dark:bg-slate-700'));
            this.classList.add('bg-slate-200', 'dark:bg-slate-700');

            const zoneCard = this.closest('.zone-card');
            const zone = zoneCard.dataset.zone;
            const level = this.dataset.level;

            runSimulation(zone, level);
        });
    });

    // 5. Simulation Logic
    async function runSimulation(zone, level) {
        if (!window.latestSessionId && !window.manualBaseline) {
            alert("Silakan buat rencana di Reverse Goal Planner terlebih dahulu.");
            document.getElementById('reverse-planner-section').scrollIntoView({ behavior: 'smooth' });
            return;
        }

        const payload = {
            zone: zone,
            level: parseInt(level),
            session_id: window.latestSessionId || null,
            manual_baseline: window.manualBaseline
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        try {
            document.getElementById('ps-profit-range').textContent = 'Menghitung...';

            const response = await fetch(config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            if (data.status === 'success') {
                renderResult(data.result);
            } else {
                alert("Error: " + data.message);
            }
        } catch (error) {
            console.error(error);
            alert("Terjadi kesalahan sistem saat simulasi.");
        }
    }

    function renderResult(result) {
        window.latestResult = result;
        const saveBtn = document.getElementById('apply-strategy-btn');
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            saveBtn.innerHTML = 'Simpan Strategi';
        }

        const defaultState = document.getElementById('ps-default-state');
        if (defaultState) defaultState.classList.add('hidden');

        const resultCard = document.getElementById('simulation-result');
        if (resultCard) resultCard.classList.remove('hidden');

        const rangeEl = document.getElementById('ps-profit-range');
        if (rangeEl) rangeEl.textContent = result.projected_range.label;

        const insightEl = document.getElementById('ps-insight');
        if (insightEl) insightEl.textContent = result.insight;

        const effortEl = document.getElementById('ps-effort');
        if (effortEl) effortEl.textContent = result.effort_level;

        const riskEl = document.getElementById('ps-risk');
        if (riskEl) {
            riskEl.textContent = result.risk_level + " (" + result.risk_label + ")";
            riskEl.className = 'font-bold';
            if (result.risk_level === 'High' || result.risk_level === 'Tinggi') riskEl.classList.add('text-rose-400');
            else if (result.risk_level === 'Moderate' || result.risk_level === 'Moderat') riskEl.classList.add('text-amber-400');
            else riskEl.classList.add('text-emerald-400');
        }

        const reflectionEl = document.getElementById('ps-reflection');
        if (reflectionEl) reflectionEl.textContent = result.reflection_prompt;

        const deltaDisplay = document.getElementById('profit-delta-display');
        if (deltaDisplay) {
            const isPositive = result.delta_val >= 0;
            deltaDisplay.textContent = (isPositive ? '+' : '') + formatCurrency(result.delta_val) + ' vs Goal';
            deltaDisplay.className = isPositive ? 'text-sm text-emerald-400 mt-2 font-bold' : 'text-sm text-rose-400 mt-2 font-bold';
        }
    }

    // 6. Save Strategy
    const saveBtn = document.getElementById('apply-strategy-btn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function () {
            if (!window.latestResult || !window.manualBaseline) return;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            saveBtn.disabled = true;

            try {
                const payload = {
                    session_id: window.latestSessionId || null,
                    zone: window.latestResult.zone,
                    level: window.latestResult.level,
                    baseline: window.manualBaseline,
                    result: window.latestResult
                };
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const response = await fetch('/profit-simulator/store', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                if (data.status === 'success') {
                    saveBtn.innerHTML = '<i class="fas fa-check"></i> Tersimpan!';
                    saveBtn.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
                    saveBtn.classList.add('bg-slate-500', 'cursor-default');
                    alert("Rencana berhasil disimpan!");
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error(error);
                alert("Gagal menyimpan: " + error.message);
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        });
    }

    // 7. Auto-Initialization
    if (window.savedSimulationData) {
        const sim = window.savedSimulationData;
        const zoneCard = document.querySelector(`.zone-card[data-zone="${sim.leverage_zone}"]`);
        if (zoneCard) {
            zoneCard.click();
            const levelBtn = zoneCard.querySelector(`.level-btn[data-level="${sim.effort_score}"]`);
            if (levelBtn) {
                levelBtn.classList.add('bg-slate-200', 'dark:bg-slate-700');
            }
        }

        // Adapter for Saved Data to match result format
        const mockResult = {
            zone: sim.leverage_zone,
            level: sim.effort_score,
            projected_range: { label: formatCurrency(sim.projected_net_profit) }, // Simple display
            insight: sim.mentor_focus_area,
            effort_level: sim.effort_score === 1 ? 'Rendah' : (sim.effort_score === 2 ? 'Sedang' : 'Tinggi'),
            risk_level: sim.effort_score === 1 ? 'Stabil' : (sim.effort_score === 2 ? 'Moderat' : 'Tinggi'),
            risk_label: sim.primary_constraint,
            reflection_prompt: 'Blueprint ini dimuat dari data terakhir kamu.',
            delta_val: sim.profit_delta
        };
        renderResult(mockResult);
    }

    function formatCurrency(num) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(num);
    }
});
