/**
 * Main Controller
 * The central hub for UI interaction and State Management.
 * Replaces ReactiveBinder.js
 */

import { businessCore } from './BusinessCore.js';
import { select, showToast } from '../utils/helpers.js';
import { updateCalculator } from '../calculator.js';

// MAPPING: HTML ID -> BusinessCore State Key
const INPUT_MAP = {
    // Profit Simulator
    'price-input': 'sellingPrice',
    'traffic-input': 'traffic',
    'traffic-manual': 'traffic',
    'conv-input': 'conversionRate',
    'ad-spend-input': 'adSpend',
    'ad-spend-manual': 'adSpend',

    // Goal Planner
    'goal-income': 'targetRevenue',

    // Empire Growth
    'empire-business-name': 'businessName',
    'empire-selling-price': 'sellingPrice',
    'empire-variable-cost': 'variableCosts',
    'empire-fixed-costs': 'fixedCosts',
    'empire-target-sales': 'targetRevenue', // Linked to target revenue logic
    'empire-available-cash': 'availableCash',
    'empire-capacity-input': 'maxCapacity' // NEW: Capacity Input
};

class MainController {
    constructor() {
        if (MainController.instance) return MainController.instance;
        MainController.instance = this;
        this.isTyping = false;
        console.log('🎮 MainController Initialized');
    }

    init() {
        this.bindInputs();
        this.subscribeToCore();
    }

    /**
     * Bind HTML inputs to BusinessCore state
     */
    bindInputs() {
        Object.keys(INPUT_MAP).forEach(id => {
            const el = select(`#${id}`);
            if (!el) return;

            const stateKey = INPUT_MAP[id];

            el.addEventListener('input', (e) => {
                this.isTyping = true;

                let value = e.target.value;
                // Convert to number for numeric fields
                if (e.target.type === 'number' || e.target.type === 'range') {
                    value = parseFloat(value) || 0;
                }

                // Special handling for synced inputs
                this.syncHelper(id, value);

                // Special handling for Target Sales in Empire (converts to Revenue)
                if (id === 'empire-target-sales') {
                    const price = businessCore.state.sellingPrice || 0;
                    businessCore.updateState({ targetRevenue: value * price });
                } else {
                    // Update Core Normally
                    businessCore.updateState({ [stateKey]: value });
                }

                setTimeout(() => this.isTyping = false, 50);
            });
        });
    }

    /**
     * Helper to sync related inputs (like Slider + Number input)
     */
    syncHelper(id, value) {
        if (id === 'traffic-input') {
            const manual = select('#traffic-manual');
            if (manual) manual.value = value;
        } else if (id === 'traffic-manual') {
            const slider = select('#traffic-input');
            if (slider) slider.value = value;
        } else if (id === 'ad-spend-input') {
            const manual = select('#ad-spend-manual');
            if (manual) manual.value = value;
        } else if (id === 'ad-spend-manual') {
            const slider = select('#ad-spend-input');
            if (slider) slider.value = value;
        }
    }

    /**
     * Subscribe to BusinessCore updates
     */
    subscribeToCore() {
        businessCore.subscribe((state) => {
            this.updateUI(state);
            this.handleCrisisMode(state);

            // Legacy support
            if (typeof updateCalculator === 'function') updateCalculator();
        });
    }

    /**
     * Update UI elements from State (One-way sync back)
     */
    updateUI(state) {
        if (this.isTyping) return; // Don't interrupt user typing

        Object.keys(INPUT_MAP).forEach(id => {
            const el = select(`#${id}`);
            const key = INPUT_MAP[id];

            if (el && state[key] !== undefined) {
                if (document.activeElement !== el) {
                    // Show placeholder for 0 values on non-range inputs
                    if (state[key] === 0 && el.type !== 'range') {
                        el.value = '';
                    } else {
                        el.value = state[key];
                    }
                }
            }
        });

        // Special: Update Empire Target Sales input (derived from Revenue / Price)
        const empireTargetSales = select('#empire-target-sales');
        if (empireTargetSales && document.activeElement !== empireTargetSales) {
            if (state.targetRevenue > 0 && state.sellingPrice > 0) {
                empireTargetSales.value = Math.ceil(state.targetRevenue / state.sellingPrice);
            }
        }
    }

    /**
     * CRISIS MODE: Visual feedback for unrealistic goals
     */
    handleCrisisMode(state) {
        const { gapCapacity, maxCapacity, fullMetrics } = state;
        const capacityInput = select('#empire-capacity-input');

        // RESET STYLES
        if (capacityInput) {
            capacityInput.classList.remove('animate-pulse', 'bg-red-100', 'border-red-500', 'text-red-700');
        }

        // CHECK: CAPAITY CRISIS
        // If Gap < 0 (meaning Required > Capacity)
        if (fullMetrics && fullMetrics.gapCapacity < 0) {
            if (capacityInput) {
                capacityInput.classList.add('animate-pulse', 'bg-red-100', 'border-red-500', 'text-red-700');
            }
        }
    }
}

export const mainController = new MainController();
