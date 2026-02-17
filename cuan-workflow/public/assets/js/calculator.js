import { select, selectAll } from './utils/helpers.js';
import { businessCore } from './core/BusinessCore.js';

let funnelChart;
let profitChart;

// --- UTILS ---
const memoize = (fn) => {
    const cache = new Map();
    return (...args) => {
        const key = args.join('|');
        if (cache.has(key)) return cache.get(key);
        const result = fn(...args);
        cache.set(key, result);
        return result;
    };
};

// === METRICS NOW CALCULATED BY BUSINESSCORE ===
// Removed local calculateMetrics - now using businessCore.state.fullMetrics

let isDragging = false;
let pendingUpdate = false;
let domUpdateBatch = {};

const currencyConfig = {
    IDR: { locale: 'id-ID', symbol: 'Rp', scale: ['Ribu', 'Juta', 'Miliar', 'Triliun'] },
    USD: { locale: 'en-US', symbol: '$', scale: ['Thousand', 'Million', 'Billion', 'Trillion'] },
    EUR: { locale: 'de-DE', symbol: '€', scale: ['Thousand', 'Million', 'Billion', 'Trillion'] },
    GBP: { locale: 'en-GB', symbol: '£', scale: ['Thousand', 'Million', 'Billion', 'Trillion'] },
    MYR: { locale: 'ms-MY', symbol: 'RM', scale: ['Ribu', 'Juta', 'Miliar', 'Triliun'] },
    SGD: { locale: 'en-SG', symbol: 'S$', scale: ['Thousand', 'Million', 'Billion', 'Trillion'] },
    AUD: { locale: 'en-AU', symbol: 'A$', scale: ['Thousand', 'Million', 'Billion', 'Trillion'] },
    JPY: { locale: 'ja-JP', symbol: '¥', scale: ['Thousand', 'Million', 'Billion', 'Trillion'] }
};

export function getSelectedCurrency() {
    const selector = select('#currency-selector');
    return selector ? selector.value : 'IDR';
}

export function formatMoney(amount, currencyCode) {
    const config = currencyConfig[currencyCode] || currencyConfig.IDR;
    return config.symbol + " " + new Intl.NumberFormat(config.locale).format(amount);
}

function queueDomUpdate(selector, value, type = 'text', classesToAdd = [], classesToRemove = []) {
    domUpdateBatch[selector] = { value, type, classesToAdd, classesToRemove };

    if (!pendingUpdate) {
        pendingUpdate = true;
        requestAnimationFrame(flushDomUpdates);
    }
}

function flushDomUpdates() {
    for (const selector in domUpdateBatch) {
        const el = select(selector);
        if (el) {
            const update = domUpdateBatch[selector];
            if (update.type === 'text') el.innerText = update.value;
            // No need for 'value' type update here as inputs update themselves or via BusinessCore listener

            if (update.classesToRemove.length) el.classList.remove(...update.classesToRemove);
            if (update.classesToAdd.length) el.classList.add(...update.classesToAdd);
        }
    }
    domUpdateBatch = {};
    pendingUpdate = false;
}

// --- GOAL PLANNER (REVERSE ENGINEERING) ---
// --- GOAL PLANNER (REVERSE ENGINEERING) ---
export function calculateGoal() {
    console.log("Goals: Calculating...");

    // 1. INPUT: Read DIRECTLY from DOM (User Intent)
    const incomeInput = select('#goal-income');
    const priceInput = select('#goal-price');

    let targetRevenue = 0;
    let targetPrice = 0;

    if (incomeInput) targetRevenue = parseFloat(incomeInput.value) || 0;
    if (priceInput) targetPrice = parseFloat(priceInput.value) || 0;

    // 2. STATE: Update BusinessCore (Single Source of Truth)
    // This ensures other components (like Wizard or Simulator) stay in sync
    businessCore.updateGoalParams({
        targetRevenue: targetRevenue,
        targetPrice: targetPrice
    });

    // Force immediate save for critical goal updates
    businessCore.saveToApi();

    // 3. REACTIVE: Read back from State
    const s = businessCore.state;
    const income = s.targetRevenue || 0;
    // Use targetPrice if set, otherwise fallback to sellingPrice
    const price = s.sellingPrice || 0;

    const currency = getSelectedCurrency();

    // Use BusinessCore calculations for accurate metrics
    const metrics = businessCore.state.fullMetrics;
    const targetQty = isFinite(metrics?.revenueGoal?.requiredSales) ? metrics.revenueGoal.requiredSales : Math.ceil(income / (price || 1));
    const traffic = isFinite(metrics?.revenueGoal?.requiredTraffic) ? metrics.revenueGoal.requiredTraffic : (targetQty * 100);
    const leads = targetQty * 10;

    const heroRevenueEl = select('#hero-total-revenue');

    if (heroRevenueEl) {
        requestAnimationFrame(() => {
            heroRevenueEl.innerText = formatMoney(income, currency);
            heroRevenueEl.classList.remove('animate-pulse');
            void heroRevenueEl.offsetWidth;
            heroRevenueEl.classList.add('animate-pulse');
            setTimeout(() => heroRevenueEl.classList.remove('animate-pulse'), 500);
        });
    }

    if (price > 0) {
        const heroSalesEl = select('#hero-target-sales');
        if (heroSalesEl) {
            requestAnimationFrame(() => {
                heroSalesEl.innerText = new Intl.NumberFormat('id-ID').format(targetQty) + " Unit";
            });
        }

        queueDomUpdate('#goal-qty', new Intl.NumberFormat('id-ID').format(targetQty) + " Unit");
        queueDomUpdate('#goal-traffic', new Intl.NumberFormat('id-ID').format(traffic) + " Visitor");

        updateGoalChart(traffic, leads, targetQty);
    }
}

let goalChartTimeout;
export function updateGoalChart(traffic, leads, sales) {
    if (goalChartTimeout) clearTimeout(goalChartTimeout);

    const updateTask = () => {
        const ctxEl = select('#goalFunnelChart');
        if (!ctxEl || typeof Chart === 'undefined') return;

        if (funnelChart) {
            funnelChart.data.datasets[0].data = [traffic, leads, sales];
            funnelChart.update('none');
            return;
        }

        const ctx = ctxEl.getContext('2d');
        funnelChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Traffic', 'Lead (10%)', 'Sales (1%)'],
                datasets: [{
                    label: 'Orang',
                    data: [traffic, leads, sales],
                    backgroundColor: ['#e2e8f0', '#93c5fd', '#10b981'],
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { display: false, grid: { display: false } },
                    y: { grid: { display: false } }
                }
            }
        });
    };

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(updateTask);
    } else {
        setTimeout(updateTask, 50);
    }
}

// --- PROFIT SIMULATOR ---
let chartUpdateTimeout;

function updateNumbers(price, traffic, conv, adSpend, currency) {
    // Get metrics from BusinessCore instead of local calculation
    const metrics = businessCore.state.fullMetrics;

    // Fallback to simple calculation if fullMetrics not ready
    const sales = metrics?.currentSalesQty || Math.floor(traffic * (conv / 100));
    const revenue = metrics?.monthlyRevenue || (sales * price);
    const netProfit = metrics?.monthlyProfit || (revenue - adSpend);
    const annualProfit = metrics?.annualProfit || (netProfit * 12);

    // Magic number calculation (4% conversion rate benchmark)
    const magicSales = Math.floor(traffic * 0.04);
    const magicRevenue = (magicSales * price) - adSpend;

    queueDomUpdate('#price-val', formatMoney(price, currency));
    queueDomUpdate('#traffic-val', new Intl.NumberFormat('id-ID').format(traffic));
    queueDomUpdate('#conv-val', conv.toFixed(1) + "%");
    queueDomUpdate('#ad-spend-val', formatMoney(adSpend, currency));

    queueDomUpdate('#total-sales', sales + " Unit");

    const isLoss = netProfit < 0;
    const profitClassAdd = isLoss ? ['text-rose-500'] : ['text-white'];
    const profitClassRemove = isLoss ? ['text-white', 'text-emerald-400'] : ['text-rose-500', 'text-emerald-400'];

    queueDomUpdate('#total-revenue', formatMoney(netProfit, currency), 'text', profitClassAdd, profitClassRemove);
    queueDomUpdate('#yearly-profit', formatMoney(annualProfit, currency), 'text', profitClassAdd, profitClassRemove);

    const isMagicLoss = magicRevenue < 0;
    const magicClassAdd = isMagicLoss ? ['text-rose-500'] : [];
    const magicClassRemove = isMagicLoss ? ['text-white', 'text-emerald-400'] : ['text-rose-500'];
    queueDomUpdate('#magic-number', formatMoney(magicRevenue, currency), 'text', magicClassAdd, magicClassRemove);

    const profitText = formatCurrencyText(netProfit, currency);
    const annualText = formatCurrencyText(annualProfit, currency);

    const textClassAdd = isLoss ? ['text-rose-400'] : [];
    const textClassRemove = isLoss ? [] : ['text-rose-400'];

    queueDomUpdate('#revenue-terbilang', profitText, 'text', textClassAdd, textClassRemove);
    queueDomUpdate('#yearly-terbilang', annualText, 'text', textClassAdd, textClassRemove);
    queueDomUpdate('#revenue-text', profitText, 'text', isLoss ? ['text-rose-500'] : [], isLoss ? [] : ['text-rose-500']);

    let cuanText = "Let's go";
    let multiplier = (currency !== 'IDR') ? 0.00007 : 1;

    if (netProfit < 0) cuanText = "Boncos 😭";
    else if (netProfit >= 5000000000 * multiplier) cuanText = "GACOR 🔥";
    else if (netProfit >= 1000000000 * multiplier) cuanText = "The 1%";
    else if (netProfit >= 500000000 * multiplier) cuanText = "Crazy Rich";
    else if (netProfit >= 250000000 * multiplier) cuanText = "Sultan Mode";
    else if (netProfit >= 100000000 * multiplier) cuanText = "Ngebut Parah";
    else if (netProfit >= 50000000 * multiplier) cuanText = "Laju Kenceng";
    else if (netProfit >= 25000000 * multiplier) cuanText = "Mesin Nyala";
    else if (netProfit >= 10000000 * multiplier) cuanText = "Gas";
    else if (netProfit >= 3000000 * multiplier) cuanText = "Let's go";

    queueDomUpdate('#cuan-meter-text', cuanText);

    return { netProfit, isLoss, currency };
}

function updateCharts(netProfit, isLoss, currency) {
    if (chartUpdateTimeout) clearTimeout(chartUpdateTimeout);

    const updateTask = () => {
        const labels = Array.from({ length: 12 }, (_, i) => `Bulan ${i + 1}`);
        const data = [];
        let cumulative = 0;
        for (let i = 0; i < 12; i++) {
            cumulative += netProfit;
            data.push(cumulative);
        }

        updateProfitChart(labels, data, currency, isLoss);
    };

    if ('requestIdleCallback' in window) {
        window.requestIdleCallback(updateTask);
    } else {
        setTimeout(updateTask, 50);
    }
}

// Function to handle UI updates from User Input
// AND trigger BusinessCore update
// Function to handle UI updates from State
export function updateCalculator() {
    console.log("Calculator: Updating...");
    // Reactive: Read from State instead of DOM
    const s = businessCore.state;
    const price = s.sellingPrice || 0;
    const traffic = s.traffic || 0;
    const conv = s.conversionRate || 0;
    const adSpend = s.adSpend || 0;
    const currency = getSelectedCurrency();

    // Trigger UI updates
    const { netProfit, isLoss } = updateNumbers(price, traffic, conv, adSpend, currency);

    // Debounce chart updates
    if (chartUpdateTimeout) clearTimeout(chartUpdateTimeout);
    chartUpdateTimeout = setTimeout(() => {
        updateCharts(netProfit, isLoss, currency);
    }, 100);

    selectAll('.currency-label').forEach(el => {
        if (el.innerText !== `(${currency})`) el.innerText = `(${currency})`;
    });
}

function updateProfitChart(labels, data, currency, isLoss) {
    const ctxEl = select('#profitChart');
    if (!ctxEl || typeof Chart === 'undefined') return;

    const colorMain = isLoss ? '#f43f5e' : '#10b981';
    const colorBgStart = isLoss ? 'rgba(244, 63, 94, 0.5)' : 'rgba(16, 185, 129, 0.5)';
    const colorBgEnd = isLoss ? 'rgba(244, 63, 94, 0)' : 'rgba(16, 185, 129, 0)';

    if (profitChart) {
        profitChart.options.animation = isDragging ? false : { duration: 400, easing: 'easeOutQuart' };
        profitChart.data.datasets[0].data = data;
        profitChart.data.datasets[0].borderColor = colorMain;
        profitChart.data.datasets[0].pointBorderColor = colorMain;
        profitChart.data.datasets[0].pointHoverBackgroundColor = colorMain;
        profitChart.data.datasets[0].shadowColor = colorMain;

        const ctx = profitChart.ctx;
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, colorBgStart);
        gradient.addColorStop(1, colorBgEnd);
        profitChart.data.datasets[0].backgroundColor = gradient;

        profitChart.update(isDragging ? 'none' : 'active');
        return;
    }

    const ctx = ctxEl.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, colorBgStart);
    gradient.addColorStop(1, colorBgEnd);

    profitChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Proyeksi Profit Kumulatif',
                data: data,
                borderColor: colorMain,
                backgroundColor: gradient,
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: colorMain,
                pointHoverBackgroundColor: colorMain,
                pointHoverBorderColor: '#fff',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4,
                shadowBlur: 15,
                shadowColor: colorMain
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 800 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    monitor: 'none',
                    callbacks: {
                        title: (c) => 'Bulan ke-' + c[0].label,
                        label: (c) => `Estimasi Profit: ${formatMoney(c.raw, currency)}`
                    },
                    displayColors: false,
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    titleColor: '#fff',
                    bodyColor: '#cbd5e1',
                    padding: 12,
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    titleFont: { family: 'Inter', weight: 'bold', size: 12 },
                    bodyFont: { family: 'Inter', size: 12 }
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { family: 'Inter', size: 10 } } },
                y: { grid: { color: 'rgba(255,255,255,0.05)', borderDash: [5, 5] }, ticks: { color: '#94a3b8', callback: function (value) { return formatCurrencyText(value, currency); }, font: { family: 'Inter', size: 10 } }, border: { display: false } }
            },
            interaction: { mode: 'index', intersect: false }
        },
        plugins: [{
            beforeDraw: (chart) => {
                const ctx = chart.ctx;
                const _stroke = ctx.stroke;
                ctx.stroke = function () {
                    ctx.save();
                    ctx.shadowColor = chart.data.datasets[0].shadowColor || '#000';
                    ctx.shadowBlur = chart.data.datasets[0].shadowBlur || 0;
                    ctx.shadowOffsetX = 0; ctx.shadowOffsetY = 0;
                    _stroke.apply(this, arguments);
                    ctx.restore();
                };
            }
        }]
    });
}

function formatCurrencyText(value, currencyCode) {
    const config = currencyConfig[currencyCode] || currencyConfig.IDR;
    const scale = config.scale;
    const currencyName = (currencyCode === 'IDR') ? ' Rupiah' : (' ' + currencyCode);

    const isNegative = value < 0;
    const absValue = Math.abs(value);
    let prefix = isNegative ? "Minus " : "";

    if (absValue === 0) return "Nol" + currencyName;

    let formatted = "";
    if (absValue >= 1000000000000) formatted = (absValue / 1000000000000).toFixed(1).replace(/\.0$/, '') + " " + scale[3];
    else if (absValue >= 1000000000) formatted = (absValue / 1000000000).toFixed(1).replace(/\.0$/, '') + " " + scale[2];
    else if (absValue >= 1000000) formatted = (absValue / 1000000).toFixed(1).replace(/\.0$/, '') + " " + scale[1];
    else if (absValue >= 1000) formatted = (absValue / 1000).toFixed(1).replace(/\.0$/, '') + " " + scale[0];
    else formatted = new Intl.NumberFormat('id-ID').format(absValue);

    return prefix + formatted + currencyName;
}

// DOM Listeners moved to main-controller.js
