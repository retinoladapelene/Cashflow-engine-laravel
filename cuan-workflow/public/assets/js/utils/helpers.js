/**
 * Menampilkan Toast Notification
 * @param {string} message - Pesan yang akan ditampilkan
 * @param {string} type - 'success', 'error', 'info', atau 'warning'
 */
export const showToast = (message, type = 'info') => {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');

    // Icon Mapping
    let icon = 'fa-info-circle';
    let colorClass = 'border-blue-500 bg-slate-800';
    let iconColor = 'text-blue-400';

    if (type === 'success') {
        icon = 'fa-check-circle';
        colorClass = 'border-emerald-500 bg-slate-900 shadow-[0_0_15px_rgba(16,185,129,0.3)]';
        iconColor = 'text-emerald-400';
    } else if (type === 'error') {
        icon = 'fa-exclamation-circle';
        colorClass = 'border-rose-500 bg-slate-900 shadow-[0_0_15px_rgba(244,63,94,0.3)]';
        iconColor = 'text-rose-400';
    } else if (type === 'warning') {
        icon = 'fa-exclamation-triangle';
        colorClass = 'border-amber-500 bg-slate-900';
        iconColor = 'text-amber-400';
    }

    // Minified HTML Structure for security & performance
    toast.className = `flex items-center gap-3 w-full max-w-sm px-4 py-4 rounded-xl border ${colorClass} text-white shadow-lg transform transition-all duration-300 translate-x-10 opacity-0 relative overflow-hidden backdrop-blur-md`;

    toast.innerHTML = `
        <div class="absolute left-0 top-0 bottom-0 w-1 ${type === 'success' ? 'bg-emerald-500' : type === 'error' ? 'bg-rose-500' : type === 'warning' ? 'bg-amber-500' : 'bg-blue-500'}"></div>
        <i class="fas ${icon} ${iconColor} text-lg"></i>
        <div class="flex-1 text-sm font-medium tracking-wide">${message}</div>
        <button onclick="this.parentElement.remove()" class="text-slate-500 hover:text-white transition-colors"><i class="fas fa-times"></i></button>
    `;

    container.appendChild(toast);

    // Animate In
    requestAnimationFrame(() => {
        toast.classList.remove('translate-x-10', 'opacity-0');
    });

    // Auto Remove
    setTimeout(() => {
        toast.classList.add('translate-x-10', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
};

/**
 * Shows a custom confirmation modal
 * @param {string} message - The message to display
 * @param {Function} onConfirm - Callback function when user confirms
 * @param {Function} onCancel - Callback function when user cancels (optional)
 */
export const showConfirm = (message, onConfirm, onCancel = null) => {
    // Create modal container
    const modalId = 'custom-confirm-modal';
    const existingModal = document.getElementById(modalId);
    if (existingModal) existingModal.remove();

    const modal = document.createElement('div');
    modal.id = modalId;
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm opacity-0 transition-opacity duration-300';

    modal.innerHTML = `
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl p-6 max-w-sm w-full mx-4 transform scale-95 transition-transform duration-300 border border-slate-200 dark:border-slate-700">
            <div class="flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center mb-4 text-rose-500 dark:text-rose-400">
                    <i class="fas fa-question text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Konfirmasi</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">${message}</p>
                <div class="flex gap-3 w-full">
                    <button id="btn-cancel-confirm" class="flex-1 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-bold transition-colors">
                        Batal
                    </button>
                    <button id="btn-yes-confirm" class="flex-1 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold transition-colors shadow-lg shadow-rose-500/20">
                        Ya, Lanjutkan
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Animate In
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('div').classList.remove('scale-95');
        modal.querySelector('div').classList.add('scale-100');
    });

    // Handlers
    const close = () => {
        modal.classList.add('opacity-0');
        modal.querySelector('div').classList.remove('scale-100');
        modal.querySelector('div').classList.add('scale-95');
        setTimeout(() => modal.remove(), 300);
    };

    document.getElementById('btn-cancel-confirm').onclick = () => {
        close();
        if (onCancel) onCancel();
    };

    document.getElementById('btn-yes-confirm').onclick = () => {
        close();
        if (onConfirm) onConfirm();
    };
};

/**
 * Format Angka ke Format Mata Uang IDR (Default)
 * @param {number} number 
 * @param {string} currencyCode 
 * @returns {string}
 */
export const formatCurrency = (number, currencyCode = 'IDR') => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: currencyCode,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(number);
};

/**
 * Sanitasi Input String Sederhana
 * @param {string} str 
 * @returns {string}
 */
export const sanitizeInput = (str) => {
    const temp = document.createElement('div');
    temp.textContent = str;
    return temp.innerHTML;
};

// Log Activity (Stubbed / Console only for now)
export const logActivity = async (userId, action, details) => {
    console.log(`[ACTIVITY] ${userId} - ${action}: ${details}`);
};

/**
 * DOM Selector Helper
 * @param {string} selector 
 * @param {Element} scope 
 * @returns {Element}
 */
export const select = (selector, scope = document) => {
    return scope.querySelector(selector);
};

/**
 * DOM Select All Helper
 * @param {string} selector 
 * @param {Element} scope 
 * @returns {NodeList}
 */
export const selectAll = (selector, scope = document) => {
    return scope.querySelectorAll(selector);
};

/**
 * Event Listener Helper
 * @param {Element|string} target 
 * @param {string} event 
 * @param {Function} callback 
 * @param {Element} scope 
 */
export const listen = (target, event, callback, scope = document) => {
    if (typeof target === 'string') {
        const elements = selectAll(target, scope);
        elements.forEach(el => el.addEventListener(event, callback));
    } else if (target instanceof NodeList || Array.isArray(target)) {
        target.forEach(el => el.addEventListener(event, callback));
    } else if (target instanceof Element || target === window || target === document) {
        target.addEventListener(event, callback);
    }
};

/**
 * Detect Device and Browser Info
 * @returns {{device: string, browser: string}}
 */
export const getDeviceInfo = () => {
    const ua = navigator.userAgent;
    let device = "Desktop";
    let browser = "Unknown";

    // Detect Device
    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua)) {
        device = "Mobile";
    }

    // Detect Browser
    if (ua.indexOf("Firefox") > -1) {
        browser = "Firefox";
    } else if (ua.indexOf("SamsungBrowser") > -1) {
        browser = "Samsung Internet";
    } else if (ua.indexOf("Opera") > -1 || ua.indexOf("OPR") > -1) {
        browser = "Opera";
    } else if (ua.indexOf("Trident") > -1) {
        browser = "Internet Explorer";
    } else if (ua.indexOf("Edge") > -1) {
        browser = "Edge";
    } else if (ua.indexOf("Chrome") > -1) {
        browser = "Chrome";
    } else if (ua.indexOf("Safari") > -1) {
        browser = "Safari";
    }

    return { device, browser };
};

/**
 * Format Date to ID-ID Locale
 * @param {string} dateString 
 * @returns {string}
 */
export const formatDate = (dateString, includeTime = true) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    const options = {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    };
    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }
    return new Intl.DateTimeFormat('id-ID', options).format(date);
};
