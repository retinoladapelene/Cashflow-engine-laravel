/**
 * @file auth-engine.js
 * @description Core Authentication Engine.
 * Handles login, register, logout, and role verification using Laravel API.
 */
"use strict";

import { api } from '../services/api.js';
import { showToast, logActivity } from '../utils/helpers.js';

// ==========================================
// CORE AUTH STATE & ROUTE GUARD
// ==========================================

export const initAuthListener = async () => {
    // Check for Auth Token from Social Login Redirect
    const urlParams = new URLSearchParams(window.location.search);
    const authToken = urlParams.get('auth_token');
    const userName = urlParams.get('user_name');

    if (authToken) {
        localStorage.setItem('auth_token', authToken);
        // Clear Query Params
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);

        if (userName) showToast(`Welcome back, ${decodeURIComponent(userName)}!`, "success");
    }

    const isLoginPage = window.location.pathname === '/login' || window.location.pathname === '/login.html';
    const isAdminPage = window.location.pathname === '/admin' || window.location.pathname === '/admin.html';
    const body = document.body;

    const token = localStorage.getItem('auth_token');

    if (token) {
        try {
            // Verify Token & Get User
            const user = await api.get('/me', { useApiPrefix: true });

            // 1. Ban Check
            if (user.is_banned) {
                await logoutUser();
                showToast("ACCOUNT SUSPENDED: Please contact Admin.", "error");
                return;
            }

            // 2. Role & UI Setup
            const role = user.role || 'user';
            sessionStorage.setItem('cuan_user_role', role);
            sessionStorage.setItem('cuan_user_display_name', user.name || user.username);
            sessionStorage.setItem('cuan_user_avatar', user.avatar || '');

            updateAdminUI(role);

            // 3. Redirections
            if (isLoginPage) {
                window.location.href = role === 'admin' ? '/admin' : '/';
                return;
            }

            if (isAdminPage && role !== 'admin') {
                console.warn("Unauthorized Admin Access Attempt");
                window.location.href = '/';
                return;
            }

            // 4. Unlock UI
            requestAnimationFrame(() => {
                body.classList.remove('locked-screen');
                body.classList.remove('opacity-0');
                body.classList.add('opacity-100');
            });

        } catch (error) {
            console.error("Auth Verification Failed:", error);
            // Token invalid?
            localStorage.removeItem('auth_token');
            if (!isLoginPage) window.location.href = '/login';
        }
    } else {
        // Unauthenticated
        if (!isLoginPage) {
            window.location.href = '/login';
        } else {
            requestAnimationFrame(() => {
                body.classList.remove('locked-screen');
            });
        }
    }
};

// ==========================================
// AUTH ACTIONS
// ==========================================

export const loginWithEmail = async (email, password) => {
    try {
        const response = await api.post('/login', { email, password }, { useApiPrefix: true });

        localStorage.setItem('auth_token', response.token);

        // await logActivity(response.user.id, "LOGIN", "Email Login"); // Optional, API can log this
        return { success: true, user: response.user };
    } catch (error) {
        throw error;
    }
};

export const registerWithEmail = async (name, email, password, username, whatsapp) => {
    try {
        const response = await api.post('/register', {
            name, email, password, username, whatsapp
        }, { useApiPrefix: true });

        localStorage.setItem('auth_token', response.token);

        return { success: true, user: response.user };
    } catch (error) {
        throw error;
    }
};

export const logoutUser = async () => {
    try {
        await api.post('/logout', {}, { useApiPrefix: true });
    } catch (e) {
        console.warn("Logout API failed, clearing local state anyway");
    }
    localStorage.removeItem('auth_token');
    sessionStorage.clear();
    window.location.href = '/login';
};

export const checkUsernameAvailability = async (username) => {
    // Implement API check if needed, or skip for now
    // API Controller doesn't have this endpoint yet, assume valid for now or add endpoint
    return true;
};

// Helper to update UI based on role
const updateAdminUI = (role) => {
    const adminElements = document.querySelectorAll('.admin-only');
    adminElements.forEach(el => {
        if (role === 'admin') {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
};

// ==========================================
// UI LOGIC (Kept mostly same, just mapped to new functions)
// ==========================================

export const initLoginUI = () => {
    if (!document.getElementById('auth-form')) return;

    let isRegisterMode = false;
    const form = document.getElementById('auth-form');
    const linkSwitch = document.getElementById('link-switch');
    const btnSubmit = document.getElementById('btn-submit');

    // ... validation logic (simplified for brevity, can keep original fully if needed) ...
    // Since we are replacing the file, let's keep the UI logic but simplified/cleaned

    // Toggle Login/Register
    if (linkSwitch) {
        linkSwitch.addEventListener('click', (e) => {
            e.preventDefault();
            isRegisterMode = !isRegisterMode;
            updateUIState(isRegisterMode);
            validateForm();
        });
    }

    const validateForm = () => {
        const email = document.getElementById('input-email').value;
        const password = document.getElementById('input-password').value;
        let isValid = email && password.length >= 6;

        if (isRegisterMode) {
            const name = document.getElementById('input-name').value;
            const confirm = document.getElementById('input-confirm-password').value;
            isValid = isValid && name && (password === confirm);
        }

        btnSubmit.disabled = !isValid;
    };

    // Attach Listeners
    const inputs = ['input-email', 'input-password', 'input-name', 'input-username', 'input-whatsapp', 'input-confirm-password'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', validateForm);
    });

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('input-email').value;
            const password = document.getElementById('input-password').value;
            const name = document.getElementById('input-name').value;
            const username = document.getElementById('input-username').value;
            const whatsapp = document.getElementById('input-whatsapp').value;

            btnSubmit.disabled = true; // Prevent double submit
            const spinner = document.getElementById('loading-spinner');
            if (spinner) spinner.classList.remove('hidden');

            try {
                if (isRegisterMode) {
                    await registerWithEmail(name, email, password, username, whatsapp);
                    showToast("Account Created!", "success");
                    setTimeout(() => window.location.href = "/", 1500);
                } else {
                    await loginWithEmail(email, password);
                    showToast("Login Successful", "success");
                    setTimeout(() => window.location.reload(), 500);
                }
            } catch (error) {
                console.error(error);
                showToast(error.message || "Authentication failed", 'error');
                btnSubmit.disabled = false;
                if (spinner) spinner.classList.add('hidden');
            }
        });
    }

    // Google Login Stub Removed (Handled via HREF)
};

const updateUIState = (isRegister) => {
    const btnText = isRegister ? "Create Account" : "Sign In";
    const switchText = isRegister ? "Already verified?" : "Don't have an account?";
    const switchAction = isRegister ? "Sign In" : "Create Premium Account";

    document.getElementById('btn-submit').querySelector('span').innerText = btnText;
    document.getElementById('text-switch').innerText = switchText;
    document.getElementById('link-switch').innerText = switchAction;

    const ids = ['name-field-container', 'username-field-container', 'whatsapp-field-container', 'confirm-password-container'];
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (isRegister) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });
};

export const requestPasswordReset = async (email) => {
    try {
        const response = await api.post('/forgot-password', { email }, { useApiPrefix: true });
        return { success: true, message: response.message };
    } catch (error) {
        throw error;
    }
};
