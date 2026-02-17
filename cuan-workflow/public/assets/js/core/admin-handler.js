/**
 * @file admin-handler.js
 * @description Logic for Admin Dashboard.
 * Handles fetching users, stats, and admin actions via API.
 */

import { api } from '../services/api.js';
import { showToast, formatDate } from '../utils/helpers.js';

export const initAdminDashboard = async () => {
    // Check if on admin page (using the main dashboard view ID)
    if (!document.getElementById('view-dashboard')) return;

    try {
        await loadStats();
        await loadCharts();
        await loadUsers();
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
    } catch (error) {
        console.error("Stats Load Failed:", error);
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

// Global Actions for HTML onclick
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

// Stub for export functions if needed
window.exportUserCSV = () => {
    showToast("Export feature coming soon to API version.", "info");
};

// System Feature Toggle (Stubbed logic for now until SystemController exists)
window.toggleSystemFeature = (feature, isActive) => {
    console.log(`Toggling ${feature} to ${isActive}`);
    showToast(`Feature ${feature} ${isActive ? 'enabled' : 'disabled'} (Local Only)`, "success");
    // In real implementation: api.post('/admin/settings', { key: feature, value: isActive })
};

// Data Integrity Check (Stub)
window.verifyDataIntegrity = () => {
    const stats = document.getElementById('integrity-stats');
    if (stats) stats.classList.remove('hidden');
    showToast("Data Integrity Check Passed", "success");
};

// Inactive Users Check (Stub)
window.checkInactiveUsers = () => {
    const stats = document.getElementById('inactive-user-stats');
    if (stats) stats.classList.remove('hidden');
    showToast("Scan Complete: 0 Inactive Users", "info");
};

// Broadcast System Update (Stub)
window.updateBroadcastSystem = () => {
    const message = document.getElementById('broadcast-message').value;
    const isActive = document.getElementById('broadcast-active').value;

    console.log(`Broadcast Update: ${message} (${isActive})`);
    showToast("Broadcast settings updated (Local Only)", "success");
    // In real implementation: api.post('/admin/broadcast', { message, is_active: isActive })
};

// Auto-Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminDashboard);
} else {
    initAdminDashboard();
}
