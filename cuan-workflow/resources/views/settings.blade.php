<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - CuanCapital</title>
    <!-- Use dummy csrf token, actual requests use API with Bearer token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('assets/icon/logo-2.svg') }}" type="image/svg+xml">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans antialiased transition-colors duration-300 opacity-0 transition-opacity">

    <!-- Loading Overlay -->
    <div id="page-loader" class="fixed inset-0 bg-slate-50 dark:bg-slate-900 z-[9999] flex items-center justify-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-500"></div>
    </div>

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('index') }}" class="flex items-center gap-2 group">
                    <i class="fas fa-arrow-left text-slate-400 group-hover:text-emerald-500 transition-colors"></i>
                    <span class="font-bold text-slate-700 dark:text-slate-300 group-hover:text-emerald-500 transition-colors">Back to Dashboard</span>
                </a>
                <div class="text-lg font-bold">Account Settings</div>
                <button id="btn-logout-nav" class="text-rose-500 hover:text-rose-600 font-medium text-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </button>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-10">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            
            <!-- Sidebar Tabs -->
            <div class="md:col-span-1 space-y-2">
                <button onclick="switchTab('profile')" id="tab-profile" class="w-full text-left px-4 py-3 rounded-xl font-medium transition-all duration-200 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400">
                    <i class="fas fa-user mr-2"></i> Profile
                </button>
                <button onclick="switchTab('preferences')" id="tab-preferences" class="w-full text-left px-4 py-3 rounded-xl font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                    <i class="fas fa-sliders-h mr-2"></i> Preferences
                </button>
                <button onclick="switchTab('security')" id="tab-security" class="w-full text-left px-4 py-3 rounded-xl font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                    <i class="fas fa-shield-alt mr-2"></i> Security
                </button>
                <button onclick="switchTab('system')" id="tab-system" class="w-full text-left px-4 py-3 rounded-xl font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all duration-200">
                    <i class="fas fa-history mr-2"></i> Activity Log
                </button>
            </div>

            <!-- Content Area -->
            <div class="md:col-span-3">
                
                <!-- Profile Tab -->
                <div id="content-profile" class="space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                        <h2 class="text-xl font-bold mb-6">Profile Information</h2>
                        
                        <form id="form-profile" class="space-y-4">
                            <div class="flex items-center gap-6 mb-6">
                                <div class="relative group">
                                    <img id="avatar-preview" src="" 
                                         class="w-24 h-24 rounded-full object-cover border-4 border-slate-100 dark:border-slate-700 group-hover:border-emerald-500 transition-colors">
                                    <label for="avatar-input" class="absolute bottom-0 right-0 bg-emerald-500 text-white w-8 h-8 rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:bg-emerald-600 transition-colors">
                                        <i class="fas fa-camera text-xs"></i>
                                    </label>
                                    <input type="file" id="avatar-input" name="avatar" class="hidden" accept="image/*">
                                </div>
                                <div>
                                    <p id="disp-name" class="font-bold text-lg">Loading...</p>
                                    <p id="disp-email" class="text-slate-500 text-sm">...</p>
                                    <span id="disp-role" class="inline-block mt-2 px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs rounded">
                                        User
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Full Name</label>
                                    <input type="text" name="name" id="inp-name"
                                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Username</label>
                                    <input type="text" name="username" id="inp-username"
                                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                    <p id="error-username" class="text-rose-500 text-sm mt-1 hidden"></p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">WhatsApp Number</label>
                                    <input type="text" name="whatsapp" id="inp-whatsapp"
                                           class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email (Read Only)</label>
                                    <input type="text" id="inp-email" readonly disabled
                                           class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-slate-500 cursor-not-allowed">
                                </div>
                            </div>

                            <div class="pt-4 flex justify-end">
                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-lg font-bold transition-colors shadow-lg shadow-emerald-500/20">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preferences Tab -->
                <div id="content-preferences" class="space-y-6 hidden">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                        <h2 class="text-xl font-bold mb-6">App Preferences</h2>
                        
                        <div class="space-y-6">
                            <!-- Dark Mode Toggle -->
                            <div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-xl">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                                        <i class="fas fa-moon text-slate-600 dark:text-yellow-400"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold">Dark Mode</p>
                                        <p class="text-sm text-slate-500">Switch between light and dark themes</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="toggle-darkmode" class="sr-only peer">
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 dark:peer-focus:ring-emerald-800 rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                            
                            <!-- Logout (Redundant but requested) -->
                            <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                                <button onclick="handleLogout()" class="w-full flex items-center justify-center gap-2 text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 py-3 rounded-xl transition-colors font-bold border border-rose-200 dark:border-rose-900/50">
                                    <i class="fas fa-sign-out-alt"></i> Log Out from this Device
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div id="content-security" class="space-y-6 hidden">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                        <h2 class="text-xl font-bold mb-6">Security Settings</h2>

                        <div id="google-auth-msg" class="hidden bg-blue-50 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 p-4 rounded-xl flex items-start gap-3">
                            <i class="fas fa-info-circle mt-1"></i>
                            <div>
                                <p class="font-bold">Google Account Connected</p>
                                <p class="text-sm mt-1">You are logged in via Google. Password management is handled by your Google Account.</p>
                            </div>
                        </div>

                        <form id="form-password" class="space-y-4 max-w-md hidden">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Current Password</label>
                                <input type="password" name="current_password" required
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">New Password</label>
                                <input type="password" name="new_password" required
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Confirm New Password</label>
                                <input type="password" name="new_password_confirmation" required
                                       class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                            <button type="submit" class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-6 py-2.5 rounded-lg font-bold hover:opacity-90 transition-opacity">
                                Update Password
                            </button>
                        </form>

                        <div class="mt-10 border-t border-slate-200 dark:border-slate-700 pt-6">
                            <h3 class="font-bold mb-4">Active Sessions</h3>
                            <div class="text-sm text-slate-500 italic">Session management coming soon.</div>
                        </div>
                    </div>
                </div>

                <!-- System Tab -->
                <div id="content-system" class="space-y-6 hidden">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                        <h2 class="text-xl font-bold mb-6">Activity Log</h2>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-slate-500 dark:text-slate-400">
                                <thead class="text-xs text-slate-700 uppercase bg-slate-50 dark:bg-slate-700 dark:text-slate-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">Action</th>
                                        <th scope="col" class="px-6 py-3">Date</th>
                                        <th scope="col" class="px-6 py-3">IP Address</th>
                                    </tr>
                                </thead>
                                <tbody id="activity-log-body">
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center">Loading logs...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="bg-rose-50 dark:bg-rose-900/10 rounded-2xl p-6 border border-rose-200 dark:border-rose-900/30">
                        <h2 class="text-xl font-bold text-rose-600 mb-2">Danger Zone</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">Once you delete your account, there is no going back. Please be certain.</p>
                        <button class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-bold text-sm transition-colors">
                            Delete Account
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Client-Side Logic -->
    <script type="module">
        import { Toast } from '/assets/js/components/toast-notification.js';
        import { api } from '/assets/js/services/api.js';
        import { formatDate } from '/assets/js/utils/helpers.js';

        let currentUser = null;

        // --- Init Data Fetch ---
        const initSettings = async () => {
            const token = localStorage.getItem('auth_token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            try {
                // Fetch User
                currentUser = await api.get('/me');
                populateProfile(currentUser);
                toggleSecurityView(currentUser);
                
                // Fetch Logs
                loadActivityLogs();

                // Show Page
                document.getElementById('page-loader').classList.add('hidden');
                document.body.classList.remove('opacity-0');

            } catch (error) {
                console.error("Failed to load settings:", error);
                Toast.error("Session expired, please login again.");
                localStorage.removeItem('auth_token');
                setTimeout(() => window.location.href = '/login', 1500);
            }
        };

        const populateProfile = (user) => {
            // Header
            document.getElementById('disp-name').innerText = user.name;
            document.getElementById('disp-email').innerText = user.email;
            document.getElementById('disp-role').innerText = user.role.toUpperCase();
            
            // Avatar
            const avatarUrl = user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}`;
            document.getElementById('avatar-preview').src = avatarUrl;
            
            // Inputs
            document.getElementById('inp-name').value = user.name;
            document.getElementById('inp-username').value = user.username || '';
            document.getElementById('inp-whatsapp').value = user.whatsapp || '';
            document.getElementById('inp-email').value = user.email;
        };

        const toggleSecurityView = (user) => {
            if (user.auth_provider === 'google') {
                document.getElementById('google-auth-msg').classList.remove('hidden');
                document.getElementById('form-password').classList.add('hidden');
            } else {
                document.getElementById('google-auth-msg').classList.add('hidden');
                document.getElementById('form-password').classList.remove('hidden');
            }
        };

        const loadActivityLogs = async () => {
            try {
                const logs = await api.get('/settings/activity-logs');
                const tbody = document.getElementById('activity-log-body');
                
                if (!logs || logs.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="px-6 py-4 text-center">No activity recorded yet.</td></tr>';
                    return;
                }

                tbody.innerHTML = logs.map(log => `
                    <tr class="bg-white border-b dark:bg-slate-800 dark:border-slate-700">
                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">${log.action || 'Unknown Action'}</td>
                        <td class="px-6 py-4">${formatDate(log.created_at)}</td>
                        <td class="px-6 py-4">${log.ip_address || '-'}</td>
                    </tr>
                `).join('');

            } catch (error) {
                console.error("Log fetch failed", error);
            }
        };

        // --- Tab Switching Logic ---
        window.switchTab = (tabName) => {
            // Hide all content
            ['profile', 'preferences', 'security', 'system'].forEach(t => {
                document.getElementById(`content-${t}`).classList.add('hidden');
                document.getElementById(`tab-${t}`).classList.remove('bg-emerald-50', 'dark:bg-emerald-900/20', 'text-emerald-600', 'dark:text-emerald-400');
                document.getElementById(`tab-${t}`).classList.add('text-slate-600', 'dark:text-slate-400');
            });

            // Show selected content
            document.getElementById(`content-${tabName}`).classList.remove('hidden');
            
            // Highlight tab
            const activeTab = document.getElementById(`tab-${tabName}`);
            activeTab.classList.remove('text-slate-600', 'dark:text-slate-400');
            activeTab.classList.add('bg-emerald-50', 'dark:bg-emerald-900/20', 'text-emerald-600', 'dark:text-emerald-400');
        };

        // --- Dark Mode Logic ---
        const themeToggleBtn = document.getElementById('toggle-darkmode');
        
        // Init state
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleBtn.checked = true;
        }

        themeToggleBtn.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            }
        });

        // --- Logout Logic ---
        window.handleLogout = async () => {
            if(!confirm("Are you sure you want to logout?")) return;
            try {
                await api.post('/logout');
            } catch (error) { console.warn("Logout API failed") }
            
            localStorage.removeItem('auth_token');
            window.location.href = '/login';
        };
        document.getElementById('btn-logout-nav').addEventListener('click', handleLogout);

        // --- Profile Update ---
        const profileForm = document.getElementById('form-profile');
        profileForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Reset Errors
            const usernameInput = document.getElementById('inp-username');
            const usernameError = document.getElementById('error-username');
            
            usernameInput.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
            usernameInput.classList.add('border-slate-300', 'dark:border-slate-700', 'focus:border-emerald-500', 'focus:ring-emerald-500');
            usernameError.classList.add('hidden');
            usernameError.textContent = '';

            const formData = new FormData(profileForm);
            
            try {
                // Use API service but we need to handle FormData manually or use api.post with customized headers
                // Since api.js might assume JSON, let's check its implementation or use fetch directly with token
                const token = localStorage.getItem('auth_token');
                const response = await fetch('/api/settings/profile', { // Use API route
                    // Wait, our web route is now unprotected, but it's a POST.
                    // If we use 'auth:sanctum' on the POST route, we need Bearer token.
                    
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                         // Do NOT set Content-Type for FormData, browser does it with boundary
                         'Accept': 'application/json'
                    },
                    body: formData
                });

                const result = await response.json();

                if (!response.ok) {
                    if (response.status === 422 && result.errors) {
                        let handled = false;
                        
                        // Handle Username Error Inline
                        if (result.errors.username) {
                            const usernameInput = document.getElementById('inp-username');
                            const usernameError = document.getElementById('error-username');
                            
                            usernameInput.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500');
                            usernameInput.classList.remove('border-slate-300', 'dark:border-slate-700', 'focus:border-emerald-500', 'focus:ring-emerald-500');
                            
                            usernameError.textContent = result.errors.username[0];
                            usernameError.classList.remove('hidden');
                            handled = true;
                        }

                        // If only username error, don't show toast
                        if (Object.keys(result.errors).length === 1 && result.errors.username) {
                            return; 
                        }
                        
                        // If there are other errors, show them in toast
                        if (!handled || Object.keys(result.errors).length > 1) {
                             const errorMessages = Object.values(result.errors).flat().join('\n');
                             throw new Error(errorMessages);
                        }
                        return;
                    }
                    throw new Error(result.message || 'Update failed');
                }

                Toast.success("Profile updated successfully!");
               
                // Refresh local data visual
                if (result.user) populateProfile(result.user);

            } catch (error) {
                console.error(error);
                Toast.error(error.message);
            }
        });

        // --- Password Update ---
        const passForm = document.getElementById('form-password');
        if (passForm) {
            passForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(passForm);
                const data = Object.fromEntries(formData.entries());

                try {
                    await api.post('/settings/password', data);
                    Toast.success("Password changed successfully!");
                    passForm.reset();
                } catch (error) {
                    Toast.error(error.message);
                }
            });
        }

        // --- Avatar Preview ---
        document.getElementById('avatar-input').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar-preview').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Start
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'profile';
        
        initSettings().then(() => {
            switchTab(activeTab);
        });

    </script>
</body>
</html>
