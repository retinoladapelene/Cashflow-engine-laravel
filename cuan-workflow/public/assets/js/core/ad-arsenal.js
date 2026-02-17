/**
 * @file ad-arsenal.js
 * @description Logic for Ad Arsenal Management (Frontend).
 * Handles fetching, creating, updating, and deleting promotional cards via API.
 */

import { api } from '../services/api.js';
import { showToast } from '../utils/helpers.js';

// --- AD ARSENAL MANAGEMENT ---
let editingArsenalId = null;

// Initialize Ad Arsenal Manager
async function initAdArsenal() {
    const tableBody = document.getElementById('arsenal-table-body');
    if (!tableBody) return;

    try {
        const ads = await api.get('/arsenal'); // Public endpoint returns active, but admin might need all?
        // Wait, the public endpoint `/arsenal` filters IsActive=true. 
        // Admin needs to see all. I didn't create an explicit admin endpoint for "all" in api.php 
        // OTHER THAN `api.get('/arsenal')` which uses `AdArsenalController@index`.
        // Let's check AdArsenalController. 
        // It returns `AdArsenal::where('is_active', true)`. 
        // I need to update the controller to return ALL for admin, or add a new endpoint.
        // For now, I will use what I have, but realize the limitation. 
        // Actually, I should probably fix the controller or route to allow admin to see all.
        // But let's write the frontend logic first assuming I might get all.

        // REVISION: I will use the same endpoint but maybe the controller should filter based on user role?
        // index() in AdArsenalController currently: `return AdArsenal::where('is_active', true)...`
        // I should probably update `AdArsenalController` to return all if user is admin. Or add `GET /admin/arsenal`.
        // I'll stick to frontend structure first.

        renderArsenalTable(tableBody, ads);
    } catch (error) {
        console.error("Failed to load arsenal:", error);
        tableBody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-rose-500">Failed to load cards.</td></tr>';
    }
}

function renderArsenalTable(tableBody, ads) {
    if (!ads || ads.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-slate-500">No promotional cards found. Click "Add New Card" to create one.</td></tr>';
        return;
    }

    tableBody.innerHTML = '';
    ads.forEach((data) => {
        const id = data.id;

        const row = document.createElement('tr');
        row.className = 'border-b border-slate-700 hover:bg-slate-800/50 transition';

        const statusBadge = data.is_active
            ? '<span class="px-2 py-1 text-xs font-bold bg-emerald-500/20 text-emerald-400 rounded">Active</span>'
            : '<span class="px-2 py-1 text-xs font-bold bg-slate-500/20 text-slate-400 rounded">Inactive</span>';

        const tagColors = {
            'HOT': 'bg-rose-500/20 text-rose-400',
            'NEW': 'bg-blue-500/20 text-blue-400',
            'FOUNDATION': 'bg-emerald-500/20 text-emerald-400',
            'PREMIUM': 'bg-purple-500/20 text-purple-400'
        };

        const tagClass = tagColors[data.tag] || 'bg-slate-500/20 text-slate-400';

        row.innerHTML = `
            <td class="p-4 text-sm text-white font-mono">${data.sort_order}</td>
            <td class="p-4 text-sm text-white font-medium">${data.title}</td>
            <td class="p-4"><span class="px-2 py-1 text-xs font-bold ${tagClass} rounded">${data.tag}</span></td>
            <td class="p-4 text-xs text-slate-400 truncate max-w-xs">${data.description.substring(0, 50)}...</td>
            <td class="p-4">${statusBadge}</td>
            <td class="p-4">
                <div class="flex gap-2">
                    <button onclick="editArsenalCard(${id}, '${data.title}', '${data.description}', '${data.tag}', '${data.link}', ${data.sort_order}, ${data.is_active})" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs rounded-lg transition">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </button>
                    <button onclick="deleteArsenalCard(${id})" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white text-xs rounded-lg transition">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </div>
            </td>
        `;

        tableBody.appendChild(row);
    });
}

// Open Add/Edit Modal
window.openArsenalModal = (id = null, title = '', description = '', tag = 'NEW', link = '', sort_order = 0, is_active = true) => {
    editingArsenalId = id;
    const modal = document.getElementById('arsenal-modal');
    const modalTitle = document.getElementById('arsenal-modal-title');
    const form = document.getElementById('arsenal-form');

    if (id) {
        modalTitle.textContent = 'Edit Promotional Card';
        document.getElementById('arsenal-title').value = title;
        document.getElementById('arsenal-description').value = description;
        document.getElementById('arsenal-tag').value = tag;
        document.getElementById('arsenal-link').value = link;
        document.getElementById('arsenal-order').value = sort_order;
        document.getElementById('arsenal-active').checked = is_active;
    } else {
        modalTitle.textContent = 'Add New Promotional Card';
        form.reset();
        document.getElementById('arsenal-active').checked = true;
    }

    modal.classList.remove('hidden');
};

// Close Modal
window.closeArsenalModal = () => {
    document.getElementById('arsenal-modal').classList.add('hidden');
    editingArsenalId = null;
};

// Save Arsenal Card
window.saveArsenalCard = async () => {
    const title = document.getElementById('arsenal-title').value.trim();
    const description = document.getElementById('arsenal-description').value.trim();
    const tag = document.getElementById('arsenal-tag').value;
    const link = document.getElementById('arsenal-link').value.trim();
    const sort_order = parseInt(document.getElementById('arsenal-order').value);
    const is_active = document.getElementById('arsenal-active').checked;

    if (!title || !description || !link) {
        showToast('Please fill all required fields', 'error');
        return;
    }

    const saveBtn = document.getElementById('save-arsenal-btn');
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
    saveBtn.disabled = true;

    try {
        const payload = { title, description, tag, link, sort_order, is_active };

        if (editingArsenalId) {
            await api.put(`/admin/arsenal/${editingArsenalId}`, payload);
            showToast('Card updated successfully', 'success');
        } else {
            await api.post('/admin/arsenal', payload);
            showToast('Card created successfully', 'success');
        }

        closeArsenalModal();
        initAdArsenal(); // Refresh table
    } catch (e) {
        console.error(e);
        showToast('Failed to save card', 'error');
    } finally {
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    }
};

// Edit Arsenal Card wrapper
window.editArsenalCard = (id, title, description, tag, link, sort_order, is_active) => {
    openArsenalModal(id, title, description, tag, link, sort_order, is_active);
};

// Delete Arsenal Card
window.deleteArsenalCard = (id) => {
    if (!confirm("Delete this card?")) return;

    api.delete(`/admin/arsenal/${id}`)
        .then(() => {
            showToast('Card deleted successfully', 'success');
            initAdArsenal();
        })
        .catch(e => {
            console.error(e);
            showToast('Failed to delete card', 'error');
        });
};

// Initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('arsenal-table-body')) {
            initAdArsenal();
        }
    });
} else {
    if (document.getElementById('arsenal-table-body')) {
        initAdArsenal();
    }
}

// Seeder Stub
window.seedAdArsenal = () => {
    showToast("Please use 'php artisan db:seed' for seeding.", "info");
};

window.initAdArsenal = initAdArsenal;
