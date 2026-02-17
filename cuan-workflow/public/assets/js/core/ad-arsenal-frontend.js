/**
 * @file ad-arsenal-frontend.js
 * @description Renders Ad Arsenal cards on the Landing Page.
 * Uses Public API Endpoint.
 */

import { api } from '../services/api.js';

document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('ad-arsenal-container');
    if (!container) return;

    // Render Skeleton
    container.innerHTML = `
        <div class="animate-pulse bg-slate-200 dark:bg-slate-800 rounded-3xl h-64 w-full"></div>
        <div class="animate-pulse bg-slate-200 dark:bg-slate-800 rounded-3xl h-64 w-full hidden md:block"></div>
        <div class="animate-pulse bg-slate-200 dark:bg-slate-800 rounded-3xl h-64 w-full hidden md:block"></div>
    `;

    try {
        const ads = await api.get('/arsenal');

        if (!ads || ads.length === 0) {
            container.innerHTML = `
                <div class="col-span-3 text-center py-10">
                    <p class="text-slate-500">Belum ada tools tambahan saat ini.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = '';

        // API response is already sorted by sort_order if backend handles it
        // If not, sort here: ads.sort((a,b) => a.sort_order - b.sort_order);

        ads.forEach((data) => {
            const card = document.createElement('div');
            // Responsive classes mirroring the original design
            card.className = "min-w-[85vw] md:min-w-0 snap-center bg-white dark:bg-slate-800 p-6 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group flex flex-col h-full";

            // Icon styling based on tag logic or default
            let iconClass = "fa-rocket";
            let colorClass = "text-emerald-600 dark:text-emerald-400";
            let bgClass = "bg-emerald-100 dark:bg-emerald-900/30";

            if (data.tag === 'NEW') {
                iconClass = "fa-star";
                colorClass = "text-amber-600 dark:text-amber-400";
                bgClass = "bg-amber-100 dark:bg-amber-900/30";
            } else if (data.tag === 'HOT') {
                iconClass = "fa-fire";
                colorClass = "text-rose-600 dark:text-rose-400";
                bgClass = "bg-rose-100 dark:bg-rose-900/30";
            }

            card.innerHTML = `
                <div class="w-12 h-12 ${bgClass} ${colorClass} rounded-2xl flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas ${iconClass}"></i>
                </div>
                <h4 class="font-bold text-lg mb-2 text-slate-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                    ${data.title}
                </h4>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6 leading-relaxed flex-grow">
                    ${data.description}
                </p>
                <a href="${data.link}" target="_blank"
                    class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl font-bold text-xs uppercase tracking-wider flex items-center justify-center gap-2 transition-colors shadow-lg shadow-emerald-500/20">
                    Akses Sekarang <i class="fas fa-arrow-right"></i>
                </a>
            `;
            container.appendChild(card);
        });

    } catch (error) {
        console.error("Ad Arsenal Frontend Load Failed:", error);
        container.innerHTML = `
            <div class="col-span-3 text-center py-10">
                <p class="text-rose-500">Gagal memuat tools.</p>
            </div>
        `;
    }
});
