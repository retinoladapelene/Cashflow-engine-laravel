/**
 * Reactive Binder
 * The "Glue" between HTML Inputs and BusinessCore State.
 * Handles:
 * 1. Two-way data binding
 * 2. Real-time updates
 * 3. Crisis Mode visualization (Validation Feedback)
 */

import { businessCore } from './BusinessCore.js';
import { select, selectAll } from '../utils/helpers.js';
import { updateCalculator } from '../calculator.js'; // To trigger legacy UI updates if needed

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
    'goal-price': 'targetPrice', // Note: BusinessCore might need this key or mapped to sellingPrice?

    // Empire Growth
    'empire-business-name': 'businessName',
    'empire-selling-price': 'sellingPrice',
    'empire-variable-cost': 'variableCosts',
    'empire-fixed-costs': 'fixedCosts',
    'empire-target-sales': 'salesTarget', // Derived, but editable?
    'empire-available-cash': 'currentCash'
};

class ReactiveBinder {
    constructor() {
        if (ReactiveBinder.instance) return ReactiveBinder.instance;
        ReactiveBinder.instance = this;
        this.isTyping = false;
        console.log('🔗 ReactiveBinder Initialized');
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

                // Special handling for synced inputs (slider vs manual)
                this.syncHelper(id, value);

                // Update Core
                businessCore.updateState({ [stateKey]: value });

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

            // Trigger legacy global updates
            // In a full refactor, these would be replaced by specific renderers
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
                // Check if element is active to avoid cursor jumping
                if (document.activeElement !== el) {
                    el.value = state[key];
                }
            }
        });

        // Update Text Elements (Non-inputs)
        const revenueDisplay = select('#hero-total-revenue');
        if (revenueDisplay && state.fullMetrics) {
            // Assuming we have a format helper or simplified view
            // revenueDisplay.textContent = ...
        }
    }

    /**
     * CRISIS MODE: Visual feedback for unrealistic goals
     */
    handleCrisisMode(state) {
        const warnings = state.warnings || [];
        const isRealistic = state.isRealistic;

        // Reset all inputs styling
        Object.keys(INPUT_MAP).forEach(id => {
            const el = select(`#${id}`);
            if (el) el.classList.remove('border-rose-500', 'bg-rose-50', 'text-rose-700');
        });

        if (!isRealistic && warnings.length > 0) {
            // Identify problematic inputs based on warnings
            // This logic relies on warning string matching or specific flags

            if (warnings.some(w => w.includes('Harga'))) {
                this.highlightError('price-input');
                this.highlightError('empire-selling-price');
            }
            if (warnings.some(w => w.includes('Traffic'))) {
                this.highlightError('traffic-input');
                this.highlightError('traffic-manual');
            }
            if (warnings.some(w => w.includes('Margin'))) {
                this.highlightError('price-input');
                this.highlightError('empire-variable-cost');
            }
        }
    }

    highlightError(id) {
        const el = select(`#${id}`);
        if (el) {
            el.classList.add('border-rose-500', 'bg-rose-50', 'text-rose-700');
        }
    }
}

export const reactiveBinder = new ReactiveBinder();
