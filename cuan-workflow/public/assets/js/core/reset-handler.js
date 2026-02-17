/**
 * @file reset-handler.js
 * @description Password Reset Handler.
 * Stubbed during MySQL Migration.
 */

// DOM Elements
const resetForm = document.getElementById('reset-form');
const verifyingState = document.getElementById('verifying-state');
const errorState = document.getElementById('error-state');

document.addEventListener('DOMContentLoaded', async () => {
    console.warn("Password Reset is currently disabled during system migration.");

    if (verifyingState) verifyingState.classList.add('hidden');
    if (errorState) {
        errorState.classList.remove('hidden');
        errorState.innerHTML = `
            <div class="text-center p-8">
                <h3 class="text-xl font-bold text-white mb-2">System Upgrade</h3>
                <p class="text-slate-400">Fitur Reset Password sedang dalam pemeliharaan.</p>
                <p class="text-slate-400 mt-2">Silakan hubungi Admin untuk reset password manual.</p>
                <a href="/login" class="inline-block mt-4 text-emerald-400 hover:text-emerald-300">Kembali ke Login</a>
            </div>
        `;
    }
});
