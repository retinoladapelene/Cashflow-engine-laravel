/**
 * @file logger.js
 * @description System & Admin Logger.
 * Currently logs to console, but can be connected to API if needed.
 */

import { api } from '../services/api.js';

export async function logActivity(userId, action, details) {
    console.log(`[ACTIVITY] User: ${userId} | Action: ${action} | Details: ${details}`);
    // Optional: Send to API
    // await api.post('/logs/activity', { action, details });
}

export async function logAdminAction(action, targetId, details) {
    console.log(`[ADMIN AUDIT] Action: ${action} | Target: ${targetId} | Details: ${details}`);
    // Optional: Send to API
    // await api.post('/logs/audit', { action, targetId, details });
}
