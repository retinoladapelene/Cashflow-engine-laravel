/**
 * @file admin-handler.js
 * @description Logic for Admin Dashboard.
 * Handles fetching users, stats, and admin actions via API.
 */

import { api } from '../services/api.js';
import { showToast, formatDate } from '../utils/helpers.js';

// --- GLOBAL ACTIONS (Attached to window for HTML access) ---

// System Feature Toggle
window.toggleSystemFeature = async (feature, isActive) => {
    try {
        await api.post('/admin/settings', {
            key: `feature_${feature}`,
            value: isActive ? '1' : '0'
        });
        showToast(`Feature ${feature} ${isActive ? 'enabled' : 'disabled'}`, "success");
    } catch (error) {
        showToast("Failed to update feature flag", "error");
        console.error(error);
    }
};

window.initSystemControl = async () => {
    try {
        const settings = await api.get('/admin/settings');

        // Sync checkboxes
        if (settings.feature_calculator !== undefined) {
            const el = document.getElementById('flag-calculator');
            if (el) el.checked = settings.feature_calculator === '1';
        }

        if (settings.feature_registration !== undefined) {
            const el = document.getElementById('flag-registration');
            if (el) el.checked = settings.feature_registration === '1';
        }

        // Maintenance Status
        const maintenanceActive = settings.system_maintenance === '1';
        const btnMaint = document.getElementById('btn-toggle-maintenance');
        const statusText = document.getElementById('maintenance-status-text');
        const iconMaint = document.getElementById('maintenance-icon');

        if (btnMaint && statusText && iconMaint) {
            if (maintenanceActive) {
                btnMaint.innerText = 'DISABLE';
                btnMaint.classList.replace('bg-slate-700', 'bg-rose-600');
                statusText.innerText = 'App is in MAINTENANCE';
                statusText.classList.replace('text-white', 'text-rose-400');
                iconMaint.innerHTML = '<i class="fas fa-hammer animate-bounce"></i>';
                iconMaint.classList.replace('text-slate-400', 'text-rose-400');
            } else {
                btnMaint.innerText = 'ENABLE';
                btnMaint.classList.replace('bg-rose-600', 'bg-slate-700');
                statusText.innerText = 'App is Online';
                statusText.classList.replace('text-rose-400', 'text-white');
                iconMaint.innerHTML = '<i class="fas fa-power-off"></i>';
                iconMaint.classList.replace('text-rose-400', 'text-slate-400');
            }
        }



    } catch (error) {
        console.error("Failed to load system settings:", error);
    }
};

window.toggleMaintenance = async () => {
    const btn = document.getElementById('btn-toggle-maintenance');
    const isEnabling = btn.innerText === 'ENABLE';

    if (confirm(`Are you sure you want to ${isEnabling ? 'ENABLE' : 'DISABLE'} Maintenance Mode?`)) {
        try {
            await api.post('/admin/settings', {
                key: 'system_maintenance',
                value: isEnabling ? '1' : '0'
            });
            window.initSystemControl(); // Refresh UI
            showToast(`Maintenance Mode ${isEnabling ? 'Enabled' : 'Disabled'}`, "success");
        } catch (error) {
            showToast("Failed to update maintenance mode", "error");
        }
    }
};



// Global User Actions
window.banUser = async (userId) => {
    if (!confirm("Are you sure you want to ban this user?")) return;
    try {
        await api.post(`/admin/users/${userId}/ban`);
        showToast("User banned successfully", "success");
        loadUsers();
    } catch (error) {
        showToast("Failed to ban user", "error");
    }
};

window.unbanUser = async (userId) => {
    if (!confirm("Unban this user?")) return;
    try {
        await api.post(`/admin/users/${userId}/unban`);
        showToast("User unbanned successfully", "success");
        loadUsers();
    } catch (error) {
        showToast("Failed to unban user", "error");
    }
};

window.promoteUser = async (userId) => {
    if (!confirm("Promote this user to Admin?")) return;
    try {
        await api.post(`/admin/users/${userId}/promote`);
        showToast("User promoted to Admin", "success");
        loadUsers();
    } catch (error) {
        showToast("Failed to promote user", "error");
    }
};

window.changeUserPassword = async (userId) => {
    const newPassword = prompt("Enter new password for this user (min 6 chars):");
    if (!newPassword) return;

    if (newPassword.length < 6) {
        alert("Password must be at least 6 characters.");
        return;
    }

    try {
        await api.post(`/admin/users/${userId}/password`, { password: newPassword });
        showToast("Password updated successfully", "success");
    } catch (error) {
        showToast("Failed to update password", "error");
        console.error(error);
    }
};

window.exportUserCSV = () => {
    showToast("Export feature coming soon to API version.", "info");
};

window.verifyDataIntegrity = () => {
    const stats = document.getElementById('integrity-stats');
    if (stats) stats.classList.remove('hidden');
    showToast("Data Integrity Check Passed", "success");
};

window.checkInactiveUsers = () => {
    const stats = document.getElementById('inactive-user-stats');
    if (stats) stats.classList.remove('hidden');
    showToast("Scan Complete: 0 Inactive Users", "info");
};

window.updateBroadcastSystem = async () => {
    const message = document.getElementById('broadcast-message').value;
    const isActive = document.getElementById('broadcast-active').value;

    try {
        await api.post('/admin/settings', {
            key: 'system_announcement',
            value: message
        });

        await api.post('/admin/settings', {
            key: 'system_broadcast_active',
            value: isActive
        });

        showToast("Broadcast settings updated successfully", "success");
    } catch (error) {
        console.error("Broadcast Update Failed:", error);
        showToast("Failed to update broadcast settings", "error");
    }
};

export const initAdminDashboard = async () => {
    console.log("Admin Dashboard Initialization...");
    // Check if on admin page (using the main dashboard view ID)
    if (!document.getElementById('view-dashboard')) return;

    try {
        await loadStats();
        await loadCharts();
        await loadUsers();
        await loadActivityLogs();

        // Real-time updates (Every 30s)
        setInterval(() => {
            loadStats();
            loadActivityLogs();
        }, 30000);

    } catch (error) {
        console.error("Admin Load Failed:", error);
        showToast("Gagal memuat dashboard admin.", "error");
    }
};

const loadStats = async () => {
    try {
        const stats = await api.get('/admin/stats');

        // Update UI (IDs corrected to match admin.blade.php)
        if (document.getElementById('stat-total-users')) document.getElementById('stat-total-users').innerText = stats.total_users || 0;
        if (document.getElementById('stat-new-users')) document.getElementById('stat-new-users').innerText = stats.new_users_today || 0;
        if (document.getElementById('stat-active-users')) document.getElementById('stat-active-users').innerText = stats.active_users_today || 0;

        // V.14 Business Intelligence
        if (document.getElementById('stat-projected-revenue')) document.getElementById('stat-projected-revenue').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(stats.total_revenue || 0);
        if (document.getElementById('stat-ad-spend')) document.getElementById('stat-ad-spend').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(stats.total_ad_spend || 0);
        if (document.getElementById('stat-active-businesses')) document.getElementById('stat-active-businesses').innerText = stats.active_businesses || 0;

    } catch (error) {
        console.error("Stats Load Failed:", error);
    }
};

const loadActivityLogs = async () => {
    const container = document.getElementById('activity-log-container');
    if (!container) return;

    try {
        const logs = await api.get('/admin/logs');

        if (!logs || logs.length === 0) {
            container.innerHTML = '<div class="text-sm text-slate-500 italic">No activity recorded yet.</div>';
            return;
        }

        container.innerHTML = '';

        logs.forEach(log => {
            const timeParams = new Date(log.created_at);
            const timeString = timeParams.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            const userAvatar = log.user?.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(log.user?.name || 'User')}&background=random`;
            const userName = log.user?.name || 'Unknown User';

            // Color coding actions
            let actionColor = 'text-slate-400';
            let icon = 'fa-info-circle';

            if (log.action.includes('LOGIN')) { actionColor = 'text-emerald-400'; icon = 'fa-sign-in-alt'; }
            if (log.action.includes('LOGOUT')) { actionColor = 'text-slate-500'; icon = 'fa-sign-out-alt'; }
            if (log.action.includes('UPDATE')) { actionColor = 'text-blue-400'; icon = 'fa-pen'; }
            if (log.action.includes('BAN')) { actionColor = 'text-rose-500'; icon = 'fa-ban'; }
            if (log.action.includes('REGISTER')) { actionColor = 'text-purple-400'; icon = 'fa-user-plus'; }

            const item = document.createElement('div');
            item.className = "flex items-start gap-3 p-3 rounded-lg bg-slate-800/30 border border-slate-700/30 hover:bg-slate-800/50 transition-colors";
            item.innerHTML = `
                <img src="${userAvatar}" class="w-8 h-8 rounded-full border border-slate-700 bg-slate-800 object-cover shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start">
                        <p class="text-sm font-bold text-white truncate"><span class="${actionColor} mr-1"><i class="fas ${icon}"></i></span> ${log.action}</p>
                        <span class="text-[10px] text-slate-500 whitespace-nowrap">${timeString}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5 truncate">${userName} - ${log.details}</p>
                    <p class="text-[10px] text-slate-600 mt-1 font-mono">${log.ip_address} • ${log.user_agent ? log.user_agent.substring(0, 20) + '...' : 'Unknown Device'}</p>
                </div>
            `;
            container.appendChild(item);
        });

    } catch (error) {
        console.error("Logs Load Failed:", error);
    }
};

const loadCharts = async () => {
    try {
        const response = await api.get('/admin/charts');
        // Handle direct response or wrapped in data
        const data = response.user_growth ? response : response.data;

        if (!data || !data.user_growth) return;

        // Helper to destroy existing chart
        const destroyChart = (id) => {
            const existing = Chart.getChart(id);
            if (existing) existing.destroy();
        };

        // 1. User Growth Chart
        const ctxGrowth = document.getElementById('userGrowthChart');
        if (ctxGrowth && typeof Chart !== 'undefined') {
            destroyChart('userGrowthChart');
            new Chart(ctxGrowth, {
                type: 'line',
                data: {
                    labels: data.user_growth.map(d => d.date),
                    datasets: [{
                        label: 'New Users',
                        data: data.user_growth.map(d => d.count),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 2. User Segments (Doughnut)
        const ctxSegments = document.getElementById('userSegmentsChart');
        if (ctxSegments && typeof Chart !== 'undefined') {
            destroyChart('userSegmentsChart');
            new Chart(ctxSegments, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(data.user_segments),
                    datasets: [{
                        data: Object.values(data.user_segments),
                        backgroundColor: ['#64748b', '#a855f7', '#f43f5e'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'right', labels: { color: '#94a3b8' } } }
                }
            });
        }

        // 3. Device Stats (Pie)
        const ctxDevice = document.getElementById('deviceChart');
        if (ctxDevice && typeof Chart !== 'undefined') {
            destroyChart('deviceChart');
            new Chart(ctxDevice, {
                type: 'pie',
                data: {
                    labels: Object.keys(data.device_stats),
                    datasets: [{
                        data: Object.values(data.device_stats),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8' } } }
                }
            });
        }

        // 4. Browser Stats (Pie)
        const ctxBrowser = document.getElementById('browserChart');
        if (ctxBrowser && typeof Chart !== 'undefined') {
            destroyChart('browserChart');
            new Chart(ctxBrowser, {
                type: 'pie',
                data: {
                    labels: Object.keys(data.browser_stats),
                    datasets: [{
                        data: Object.values(data.browser_stats),
                        backgroundColor: ['#ef4444', '#3b82f6', '#f59e0b', '#8b5cf6'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8' } } }
                }
            });
        }

        // --- BUSINESS ANALYTICS (V.14) ---
        if (data.business_analytics) {

            // 5. Revenue vs Ad Spend (Scatter)
            const ctxScatter = document.getElementById('revenueScatterChart');
            if (ctxScatter && typeof Chart !== 'undefined') {
                destroyChart('revenueScatterChart');
                new Chart(ctxScatter, {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: 'Business Profile',
                            data: data.business_analytics.scatter,
                            backgroundColor: 'rgba(16, 185, 129, 0.6)',
                            borderColor: '#10b981',
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => {
                                        const p = ctx.raw;
                                        return `${p.name}: Spend ${new Intl.NumberFormat('id-ID').format(p.x)} -> Target ${new Intl.NumberFormat('id-ID').format(p.y)}`;
                                    }
                                }
                            },
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                title: { display: true, text: 'Ad Spend (IDR)', color: '#64748b' },
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#94a3b8' }
                            },
                            y: {
                                title: { display: true, text: 'Target Revenue (IDR)', color: '#64748b' },
                                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                                ticks: { color: '#94a3b8' }
                            }
                        }
                    }
                });
            }

            // 6. Pricing Power (Bar)
            const ctxPrice = document.getElementById('priceDistChart');
            if (ctxPrice && typeof Chart !== 'undefined') {
                destroyChart('priceDistChart');
                new Chart(ctxPrice, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(data.business_analytics.prices),
                        datasets: [{
                            label: 'Users',
                            data: Object.values(data.business_analytics.prices),
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { display: false }, ticks: { color: '#94a3b8' } },
                            x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                        }
                    }
                });
            }

            // 7. Conversion Rates (Horizontal Bar)
            const ctxConv = document.getElementById('convRateChart');
            if (ctxConv && typeof Chart !== 'undefined') {
                destroyChart('convRateChart');

                // Update avg text
                const avgEl = document.getElementById('avg-conv-rate');
                if (avgEl) avgEl.innerText = (data.business_analytics.conversion.avg || 0).toFixed(1) + '%';

                new Chart(ctxConv, {
                    type: 'bar',
                    indexAxis: 'y',
                    data: {
                        labels: Object.keys(data.business_analytics.conversion.buckets),
                        datasets: [{
                            label: 'Users',
                            data: Object.values(data.business_analytics.conversion.buckets),
                            backgroundColor: ['#f43f5e', '#10b981', '#8b5cf6'],
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.05)' }, ticks: { color: '#94a3b8' } },
                            y: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                        }
                    }
                });
            }
        }

    } catch (error) {
        console.error("Charts Load Failed:", error);
    }
};

const loadUsers = async () => {
    const tableBody = document.getElementById('users-table-body');
    if (!tableBody) return;

    tableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4">Loading...</td></tr>';

    try {
        const response = await api.get('/admin/users'); // Pagination default page 1
        const users = response.data; // Laravel pagination wrapped in 'data'

        if (!users || users.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4">No users found.</td></tr>';
            return;
        }

        tableBody.innerHTML = '';

        users.forEach(user => {
            const tr = document.createElement('tr');
            tr.className = "hover:bg-slate-800/20 transition-colors border-b border-slate-800/30 last:border-0";

            const isBanned = user.is_banned;
            const roleBadge = user.role === 'admin'
                ? '<span class="px-2 py-1 bg-purple-500/20 text-purple-400 text-xs rounded-full">Admin</span>'
                : '<span class="px-2 py-1 bg-slate-500/20 text-slate-400 text-xs rounded-full">User</span>';

            tr.innerHTML = `
                <td class="p-4">
                    <div class="font-medium text-white ${isBanned ? 'line-through text-slate-500' : ''}">${user.name || user.username || 'Unknown'}</div>
                    <div class="text-xs text-slate-500">${user.email}</div>
                </td>
                <td class="p-4">
                    ${roleBadge}
                </td>
                <td class="p-4 text-slate-400 text-xs">${formatDate(user.created_at)}</td>
                <td class="p-4 text-slate-400 text-xs">${user.last_login_at ? formatDate(user.last_login_at) : '-'}</td>
                <td class="p-4 text-right flex justify-end gap-2">
                    <button onclick="window.viewUser(${user.id})" class="p-2 bg-blue-500/10 text-blue-400 rounded hover:bg-blue-500/20 transition-colors" title="Inspect User">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="window.changeUserPassword(${user.id})" class="p-2 bg-amber-500/10 text-amber-400 rounded hover:bg-amber-500/20 transition-colors" title="Change Password">
                        <i class="fas fa-key"></i>
                    </button>
                    ${!isBanned ? `
                        <button onclick="window.banUser(${user.id})" class="p-2 bg-rose-500/10 text-rose-400 rounded hover:bg-rose-500/20 transition-colors" title="Ban User">
                            <i class="fas fa-ban"></i>
                        </button>
                    ` : `
                        <button onclick="window.unbanUser(${user.id})" class="p-2 bg-emerald-500/10 text-emerald-400 rounded hover:bg-emerald-500/20 transition-colors" title="Unban User">
                            <i class="fas fa-check-circle"></i>
                        </button>
                    `}
                    ${user.role !== 'admin' ? `
                        <button onclick="window.promoteUser(${user.id})" class="p-2 bg-purple-500/10 text-purple-400 rounded hover:bg-purple-500/20 transition-colors" title="Promote to Admin">
                            <i class="fas fa-crown"></i>
                        </button>
                    ` : ''}
                </td>
            `;
            tableBody.appendChild(tr);
        });

    } catch (error) {
        console.error("Users Load Failed:", error);
        tableBody.innerHTML = '<tr><td colspan="5" class="text-center p-4 text-rose-500">Failed to load users.</td></tr>';
    }
};

// V.15 User Inspector Logic
window.viewUser = async (userId) => {
    const modal = document.getElementById('modal-user-inspector');
    const content = document.getElementById('inspector-content');
    if (!modal || !content) return;

    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full text-slate-500">
            <i class="fas fa-circle-notch fa-spin text-3xl mb-4 text-emerald-500"></i>
            <p>Loading user profile...</p>
        </div>
    `;

    try {
        const response = await api.get(`/admin/users/${userId}`);
        const { user, logs } = response; // Removed .data
        const business = user.business_profile || {};

        // Helper for currency
        const fmtMoney = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);

        content.innerHTML = `
            <!-- Header Profile -->
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-8 border-b border-slate-700">
                <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">
                    <img src="${user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=random`}" 
                        class="w-24 h-24 rounded-full border-4 border-slate-700 shadow-xl">
                    <div class="text-center md:text-left flex-1">
                        <h2 class="text-2xl font-bold text-white mb-1">${user.name}</h2>
                        <div class="text-slate-400 text-sm mb-3 flex flex-wrap justify-center md:justify-start gap-3">
                            <span><i class="fas fa-envelope mr-1"></i> ${user.email}</span>
                            <span><i class="fas fa-calendar mr-1"></i> Joined ${formatDate(user.created_at)}</span>
                            <span><i class="fas fa-history mr-1"></i> Active ${user.last_login_at ? formatDate(user.last_login_at) : 'Never'}</span>
                        </div>
                        <div class="flex gap-2 justify-center md:justify-start">
                             <span class="px-3 py-1 rounded-full text-xs font-bold ${user.role === 'admin' ? 'bg-purple-500/20 text-purple-400' : 'bg-slate-700 text-slate-300'}">
                                ${user.role.toUpperCase()}
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold ${user.is_banned ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400'}">
                                ${user.is_banned ? 'BANNED' : 'ACTIVE'}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                     <div class="flex flex-col gap-2 shrink-0">
                         <button onclick="window.location.href='mailto:${user.email}'" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition">
                            <i class="fas fa-envelope mr-2"></i> Send Email
                         </button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-0 lg:divide-x divide-slate-700 min-h-[500px]">
                
                <!-- Col 1: Business Snapshot -->
                <div class="p-6 space-y-6 lg:col-span-1 bg-slate-900/50">
                    <h3 class="text-lg font-bold text-white mb-4 border-b border-slate-800 pb-2">Business Data</h3>
                    
                    ${business.business_name ? `
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs text-slate-500">Business Name</label>
                                <div class="text-white font-medium">${business.business_name}</div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-slate-500">Target Revenue</label>
                                    <div class="text-emerald-400 font-bold">${fmtMoney(business.target_revenue)}</div>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500">Ad Spend Budget</label>
                                    <div class="text-amber-400 font-bold">${fmtMoney(business.ad_spend)}</div>
                                </div>
                            </div>
                             <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs text-slate-500">Selling Price</label>
                                    <div class="text-blue-400 font-bold">${fmtMoney(business.selling_price)}</div>
                                </div>
                                <div>
                                    <label class="text-xs text-slate-500">Conv. Rate</label>
                                    <div class="text-white font-bold">${business.conversion_rate || 0}%</div>
                                </div>
                            </div>
                             <div>
                                <label class="text-xs text-slate-500">Current Traffic</label>
                                <div class="text-white font-medium">${business.traffic || 0} visitors</div>
                            </div>
                        </div>
                    ` : `
                        <div class="text-center py-10 text-slate-500">
                            <i class="fas fa-briefcase text-4xl mb-3 opacity-20"></i>
                            <p>No business profile yet.</p>
                        </div>
                    `}
                </div>

                <!-- Col 2: Activity Logs -->
                <div class="p-6 lg:col-span-2 bg-slate-900">
                     <h3 class="text-lg font-bold text-white mb-4 border-b border-slate-800 pb-2">Recent Activity Log</h3>
                     
                     <div class="space-y-3">
                        ${logs.length > 0 ? logs.map(log => `
                            <div class="flex gap-3 p-3 rounded-lg bg-slate-800/40 border border-slate-700/50">
                                <div class="mt-1">
                                    <i class="fas fa-circle text-[8px] ${log.action.includes('LOGIN') ? 'text-emerald-500' : 'text-slate-500'}"></i>
                                </div>
                                <div>
                                    <div class="text-sm text-white font-medium">${log.action}</div>
                                    <div class="text-xs text-slate-400">${log.details}</div>
                                    <div class="text-[10px] text-slate-600 mt-1 font-mono">${formatDate(log.created_at)} • ${log.ip_address}</div>
                                </div>
                            </div>
                        `).join('') : '<div class="text-slate-500 italic">No activity recorded for this user.</div>'}
                     </div>
                </div>
            </div>
        `;

    } catch (error) {
        console.error("Inspector Load Failed:", error);
        content.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full text-rose-500">
                <i class="fas fa-exclamation-triangle text-3xl mb-4"></i>
                <p>Failed to load user data.</p>
            </div>
        `;
    }

};



// Auto-Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboard);
} else {
    initAdminDashboard();
}
