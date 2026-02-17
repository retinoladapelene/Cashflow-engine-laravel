/**
 * Custom Toast Notification System
 * Style: Glassmorphism + Neon
 */
export class Toast {
    static containerId = 'toast-container';

    static init() {
        if (!document.getElementById(this.containerId)) {
            const container = document.createElement('div');
            container.id = this.containerId;
            container.className = 'fixed top-4 right-4 flex flex-col gap-3 pointer-events-none';
            container.style.cssText = 'z-index: 99999 !important; position: fixed; top: 1rem; right: 1rem;';
            document.body.appendChild(container);
        }
    }

    static show(message, type = 'info') {
        this.init();
        const container = document.getElementById(this.containerId);

        // Styles based on type
        const styles = {
            success: {
                border: 'border-emerald-500/50',
                bg: 'bg-emerald-900/80',
                text: 'text-emerald-50',
                icon: '<i class="fas fa-check-circle text-emerald-400 text-xl"></i>',
                shadow: 'shadow-[0_0_15px_-3px_rgba(16,185,129,0.3)]'
            },
            error: {
                border: 'border-rose-500/50',
                bg: 'bg-rose-900/80',
                text: 'text-rose-50',
                icon: '<i class="fas fa-exclamation-circle text-rose-400 text-xl"></i>',
                shadow: 'shadow-[0_0_15px_-3px_rgba(244,63,94,0.3)]'
            },
            info: {
                border: 'border-blue-500/50',
                bg: 'bg-slate-900/80',
                text: 'text-slate-50',
                icon: '<i class="fas fa-info-circle text-blue-400 text-xl"></i>',
                shadow: 'shadow-[0_0_15px_-3px_rgba(59,130,246,0.3)]'
            }
        };

        const style = styles[type] || styles.info;

        // Create notification element
        const toast = document.createElement('div');
        toast.className = `
            pointer-events-auto
            flex items-center gap-4
            min-w-[320px] max-w-sm
            p-4 rounded-xl
            border ${style.border}
            ${style.bg} backdrop-blur-md
            ${style.text}
            ${style.shadow}
            transform transition-all duration-300 ease-out
            translate-x-full opacity-0
        `;

        toast.innerHTML = `
            <div class="flex-shrink-0">
                ${style.icon}
            </div>
            <div class="flex-1 font-medium text-sm">
                ${message}
            </div>
            <button class="text-white/40 hover:text-white transition-colors" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => {
                if (toast.parentElement) toast.remove();
            }, 300);
        }, 5000);
    }

    static success(message) {
        this.show(message, 'success');
    }

    static error(message) {
        this.show(message, 'error');
    }

    static info(message) {
        this.show(message, 'info');
    }
}
