<!DOCTYPE html>
<html lang="id">

<head>
    <!-- Resource Hints -->
    <link rel="preconnect" href="https://www.gstatic.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://firestore.googleapis.com">
    <link rel="dns-prefetch" href="https://firestore.googleapis.com">
    <link rel="modulepreload" href="{{ asset('assets/js/core/admin-handler.js?v=2') }}">
    <link rel="modulepreload" href="{{ asset('assets/js/core/ad-arsenal.js') }}">

    <!-- Security Gate V.12 (Admin Restricted) -->
    <style>
        .locked {
            display: none !important;
        }
    </style>
    <script type="module">
        import { initAuthListener } from './assets/js/core/auth-engine.js';
        initAuthListener();
    </script>


    <!-- Admin Handler Module -->
    <script type="module" src="{{ asset('assets/js/core/admin-handler.js?v=2') }}"></script>
    <script type="module" src="{{ asset('assets/js/core/ad-arsenal.js') }}"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Command Center - CuanCapital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Chart.js for Analytics -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            -webkit-backdrop-filter: blur(12px);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Custom Scrollbar for Neon Emerald Theme */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(16, 185, 129, 0.05);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(16, 185, 129, 0.3);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(16, 185, 129, 0.5);
        }
    </style>
</head>

<body
    class="bg-slate-950 text-slate-100 opacity-0 transition-opacity duration-300 h-screen w-full overflow-hidden flex bg-[#0f172a] locked-screen">


    <!-- Mobile Sidebar Backdrop -->
    <div id="sidebar-backdrop" onclick="toggleSidebar()"
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-20 hidden opacity-0 transition-opacity duration-300 md:hidden">
    </div>

    <!-- Sidebar (Mobile: Off-canvas, Desktop: Fixed Left) -->
    <aside id="sidebar-panel"
        class="w-64 h-full glass-panel border-r border-slate-800 flex flex-col z-30 shrink-0 absolute md:relative transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out bg-slate-900/95 md:bg-transparent">
        <!-- Logo Area -->
        <div class="p-4 md:p-6 border-b border-slate-800/50 flex justify-between items-center h-16 shrink-0">
            <h1 class="text-xl font-bold bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">
                <i class="fas fa-shield-alt mr-2"></i>Admin
            </h1>
            <!-- Mobile Close Button -->
            <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white p-2">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-2 md:p-4 space-y-1 overflow-y-auto">
            <!-- Dashboard Section -->
            <button onclick="switchTab('dashboard')"
                class="w-full text-left px-3 py-2 md:px-4 md:py-3 rounded-xl hover:bg-slate-800/50 transition-colors flex items-center gap-2 md:gap-3 text-emerald-400 bg-slate-800/30 ring-1 ring-emerald-500/20"
                id="tab-dashboard">
                <i class="fas fa-chart-line md:w-5"></i>
                <span class="text-sm md:text-base font-medium">Dashboard</span>
            </button>

            <!-- Content Management Section -->
            <div class="pt-4 pb-2">
                <div class="px-3 mb-2">
                    <span
                        class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider">Content</span>
                </div>
                <button onclick="switchTab('arsenal')"
                    class="w-full text-left px-3 py-2 md:px-4 md:py-3 rounded-xl hover:bg-slate-800/50 transition-colors flex items-center gap-2 md:gap-3 text-slate-400"
                    id="tab-arsenal">
                    <i class="fas fa-bullhorn md:w-5"></i>
                    <span class="text-sm md:text-base">Ad Arsenal</span>
                </button>
            </div>

            <!-- User Operations Section -->
            <div class="pt-2 pb-2">
                <div class="px-3 mb-2">
                    <span class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider">User
                        Ops</span>
                </div>
                <button onclick="switchTab('users')"
                    class="w-full text-left px-3 py-2 md:px-4 md:py-3 rounded-xl hover:bg-slate-800/50 transition-colors flex items-center gap-2 md:gap-3 text-slate-400"
                    id="tab-users">
                    <i class="fas fa-users md:w-5"></i>
                    <span class="text-sm md:text-base">User Management</span>
                </button>
            </div>

            <!-- System Control Section -->
            <div class="pt-2 pb-2">
                <div class="px-3 mb-2">
                    <span class="text-[10px] md:text-xs font-bold text-slate-500 uppercase tracking-wider">System</span>
                </div>
                <button onclick="switchTab('system')"
                    class="w-full text-left px-3 py-2 md:px-4 md:py-3 rounded-xl hover:bg-slate-800/50 transition-colors flex items-center gap-2 md:gap-3 text-slate-400"
                    id="tab-system">
                    <i class="fas fa-cogs md:w-5"></i>
                    <span class="text-sm md:text-base">System Control</span>
                </button>
                <button onclick="switchTab('maintenance')"
                    class="w-full text-left px-3 py-2 md:px-4 md:py-3 rounded-xl hover:bg-slate-800/50 transition-colors flex items-center gap-2 md:gap-3 text-slate-400"
                    id="tab-maintenance">
                    <i class="fas fa-broom md:w-5"></i>
                    <span class="text-sm md:text-base">Maintenance</span>
                </button>
            </div>
        </nav>

        <!-- Footer / Back Button -->
        <div class="p-4 border-t border-slate-800/50">
            <button onclick="window.location.href='{{ route('index') }}'"
                class="w-full px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-sm transition-colors text-slate-300 flex items-center justify-center">
                <i class="fas fa-arrow-left mr-2"></i> Back to App
            </button>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full min-w-0 bg-slate-900/50 relative overflow-hidden">

        <!-- Header -->
        <header
            class="h-16 glass-panel border-b border-slate-800/50 flex items-center justify-between px-4 md:px-8 shrink-0 z-20">
            <div class="flex items-center gap-3">
                <!-- Hamburger Menu -->
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white p-2 -ml-2">
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <h2 class="text-sm md:text-lg font-medium text-slate-200 truncate" id="page-title">Dashboard Overview
                </h2>
            </div>
            <div class="flex items-center gap-2 md:gap-4">
                <div
                    class="flex items-center gap-2 text-[10px] md:text-xs text-emerald-400 bg-emerald-900/20 px-2 py-0.5 md:px-3 md:py-1 rounded-full border border-emerald-500/20 whitespace-nowrap">
                    <span class="w-1.5 h-1.5 md:w-2 md:h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="hidden md:inline">System Online</span><span class="md:hidden">Online</span>
                </div>
                <button
                    class="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center hover:bg-slate-700 transition shrink-0">
                    <i class="fas fa-bell text-slate-400 text-xs"></i>
                </button>
            </div>
        </header>

        <!-- Scrollable Container -->
        <div class="flex-1 overflow-y-auto w-full p-4 md:p-8 relative">

            <!-- Dashboard View -->
            <div id="view-dashboard" class="space-y-6 pb-20 md:pb-0">
                <!-- Analytics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Total Users -->
                    <div class="glass-panel rounded-2xl p-6 relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 w-24 h-24 bg-blue-500/10 rounded-full blur-2xl group-hover:bg-blue-500/20 transition">
                        </div>
                        <div class="text-slate-400 text-sm mb-1">Total Users</div>
                        <div class="text-3xl font-bold text-white mb-2" id="stat-total-users">Loading...</div>
                        <div class="text-xs text-blue-400 flex items-center gap-1">
                            <i class="fas fa-users"></i> All time signups
                        </div>
                    </div>

                    <!-- Active Today -->
                    <div class="glass-panel rounded-2xl p-6 relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition">
                        </div>
                        <div class="text-slate-400 text-sm mb-1">Active Today</div>
                        <div class="text-3xl font-bold text-white mb-2" id="stat-active-users">Loading...</div>
                        <div class="text-xs text-emerald-400 flex items-center gap-1">
                            <i class="fas fa-bolt"></i> Logged in last 24h
                        </div>
                    </div>

                    <!-- New This Week -->
                    <div class="glass-panel rounded-2xl p-6 relative overflow-hidden group">
                        <div
                            class="absolute -right-4 -top-4 w-24 h-24 bg-purple-500/10 rounded-full blur-2xl group-hover:bg-purple-500/20 transition">
                        </div>
                        <div class="text-slate-400 text-sm mb-1">New This Week</div>
                        <div class="text-3xl font-bold text-white mb-2" id="stat-new-users">Loading...</div>
                        <div class="text-xs text-purple-400 flex items-center gap-1">
                            <i class="fas fa-chart-line"></i> Growth rate
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Log -->
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-lg font-medium text-white mb-4">Latest System Activity</h3>
                    <div class="space-y-3 max-h-[400px] overflow-y-auto custom-scrollbar pr-2"
                        id="activity-log-container">
                        <div class="text-sm text-slate-500 italic">Listening for events...</div>
                    </div>
                </div>

                <!-- Visual Analytics Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- User Growth Chart -->
                    <div class="glass-panel rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-white">User Growth</h3>
                            <span class="text-xs text-slate-500">Last 30 Days</span>
                        </div>
                        <div class="relative h-64">
                            <canvas id="userGrowthChart"></canvas>
                        </div>
                    </div>

                    <!-- User Loyalty Tracker (V.13) -->
                    <div class="glass-panel rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-white">User Retention Pulse</h3>
                            <span class="text-xs text-slate-500">Last 24 Hours</span>
                        </div>
                        <div class="relative h-64">
                            <canvas id="userRetentionChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- User Segments Chart -->
                <div class="glass-panel rounded-2xl p-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-white">User Segments</h3>
                        <span class="text-xs text-slate-500">Distribution</span>
                    </div>
                    <div class="relative h-64 flex items-center justify-center">
                        <canvas id="userSegmentsChart" class="max-w-md mx-auto"></canvas>
                    </div>
                </div>

                <!-- Tech Analytics (V.13) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <!-- Device Distribution -->
                    <div class="glass-panel rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-white">Device Distribution</h3>
                            <span class="text-xs text-slate-500">Active Sessions</span>
                        </div>
                        <div class="relative h-64 flex items-center justify-center">
                            <canvas id="deviceChart" class="max-w-xs mx-auto"></canvas>
                        </div>
                    </div>

                    <!-- Browser Usage -->
                    <div class="glass-panel rounded-2xl p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-white">Browser Usage</h3>
                            <span class="text-xs text-slate-500">Platform Stats</span>
                        </div>
                        <div class="relative h-64 flex items-center justify-center">
                            <canvas id="browserChart" class="max-w-xs mx-auto"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Management View -->
            <div id="view-users" class="hidden space-y-6 pb-20 md:pb-0">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="text-slate-400 text-sm">Manage Registered Users</div>
                    <div class="flex gap-2 w-full md:w-auto">
                        <input type="text" placeholder="Search email..."
                            class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:border-emerald-500 transition w-full md:w-auto">
                        <button onclick="exportUserCSV()"
                            class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg text-slate-300 transition shrink-0">
                            <i class="fas fa-download"></i> CSV
                        </button>
                    </div>
                </div>

                <div class="glass-panel rounded-2xl overflow-hidden overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[600px]">
                        <thead>
                            <tr
                                class="bg-slate-900/50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                                <th class="p-4 font-medium">User</th>
                                <th class="p-4 font-medium">Role</th>
                                <th class="p-4 font-medium">Joined</th>
                                <th class="p-4 font-medium">Last Active</th>
                                <th class="p-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body" class="divide-y divide-slate-800/50 text-sm">
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-500">Loading users...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- System Control View -->
            <div id="view-system" class="hidden space-y-6 pb-20 md:pb-0">

                <!-- 1. Feature Flags -->
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4"><i class="fas fa-toggle-on text-emerald-400 mr-2"></i>
                        Feature Flags</h3>
                    <p class="text-sm text-slate-400 mb-6">Enable or disable core features in real-time. Changes apply
                        immediately to all connected clients.</p>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="feature-flags-container">
                        <!-- Flag Item: Calculator -->
                        <div
                            class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl border border-slate-700">
                            <div>
                                <div class="text-sm font-bold text-white">Profit Simulator</div>
                                <div class="text-xs text-slate-500">Calculator feature</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="flag-calculator"
                                    onchange="toggleSystemFeature('calculator', this.checked)">
                                <div
                                    class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                                </div>
                            </label>
                        </div>

                        <!-- Flag Item: Export PDF -->
                        <div
                            class="flex items-center justify-between p-4 bg-slate-800/50 rounded-xl border border-slate-700">
                            <div>
                                <div class="text-sm font-bold text-white">Export PDF</div>
                                <div class="text-xs text-slate-500">Allow users to download reports</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="flag-export_pdf"
                                    onchange="toggleSystemFeature('export_pdf', this.checked)">
                                <div
                                    class="w-11 h-6 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                                </div>
                            </label>
                        </div>


                    </div>
                </div>

                <!-- 2. Broadcast Center -->
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4"><i class="fas fa-bullhorn text-amber-400 mr-2"></i>
                        Broadcast Center</h3>
                    <p class="text-sm text-slate-400 mb-6">Send a global announcement to all active users. Appears as a
                        top banner.</p>

                    <div class="space-y-4">
                        <div class="flex gap-4">
                            <div class="flex-1">
                                <label class="block text-xs text-slate-500 mb-1">Announcement Message</label>
                                <input type="text" id="broadcast-message"
                                    placeholder="e.g., System maintenance scheduled for 22:00 WIB..."
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-amber-500 transition">
                            </div>
                            <div class="w-32">
                                <label class="block text-xs text-slate-500 mb-1">Status</label>
                                <select id="broadcast-active"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-3 text-white focus:outline-none focus:border-amber-500 transition">
                                    <option value="false">Inactive</option>
                                    <option value="true">Active</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button onclick="updateBroadcastSystem()"
                                class="px-6 py-2 bg-amber-600 hover:bg-amber-500 text-white font-bold rounded-lg shadow-lg shadow-amber-900/20 transition">
                                <i class="fas fa-paper-plane mr-2"></i> Update Broadcast
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Maintenance View -->
            <div id="view-maintenance" class="hidden space-y-6 pb-20 md:pb-0">
                <div class="glass-panel rounded-2xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4"><i class="fas fa-broom text-purple-400 mr-2"></i>
                        Database Maintenance</h3>
                    <p class="text-sm text-slate-400 mb-6">Tools to clean up inactive users and verify data integrity.
                    </p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tool 1: Inactive Users -->
                        <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700">
                            <h4 class="text-white font-bold mb-2">Cleanup Inactive Users</h4>
                            <p class="text-xs text-slate-500 mb-4">Find users who haven't logged in for 30+ days.</p>
                            <div id="inactive-user-stats" class="text-sm text-rose-400 font-bold mb-4 hidden">Found:
                                <span>0</span> users
                            </div>
                            <div class="flex gap-3">
                                <button onclick="checkInactiveUsers()"
                                    class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-xs rounded-lg transition">Scan</button>
                                <button onclick="purgeInactiveUsers()"
                                    class="px-4 py-2 bg-rose-900/50 hover:bg-rose-900 text-rose-400 hover:text-white text-xs rounded-lg transition border border-rose-500/20">Purge</button>
                            </div>
                        </div>

                        <!-- Tool 2: Data Verification -->
                        <div class="bg-slate-800/50 p-6 rounded-xl border border-slate-700">
                            <h4 class="text-white font-bold mb-2">Verify Data Integrity</h4>
                            <p class="text-xs text-slate-500 mb-4">Scan strategies for schema errors or missing fields.
                            </p>
                            <div id="integrity-stats" class="text-sm text-emerald-400 font-bold mb-4 hidden">Status:
                                <span>OK</span>
                            </div>
                            <button onclick="verifyDataIntegrity()"
                                class="px-4 py-2 bg-emerald-900/50 hover:bg-emerald-900 text-emerald-400 hover:text-white text-xs rounded-lg transition border border-emerald-500/20">Run
                                Integrity Check</button>
                        </div>


                    </div>
                </div>
            </div>

            <!-- Ad Arsenal Manager View -->
            <div id="view-arsenal" class="hidden space-y-6 pb-20 md:pb-0">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-3xl font-black text-white tracking-tight">Ad Arsenal Manager</h2>
                        <p class="text-slate-400 mt-2">Atur kartu "Scaling Arsenal" di halaman depan.</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="seedAdArsenal()"
                            class="bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-3 rounded-xl text-sm font-bold transition flex items-center gap-2 border border-slate-700">
                            <i class="fas fa-database"></i> Seed Default Data
                        </button>
                        <button onclick="openArsenalModal()"
                            class="bg-emerald-500 hover:bg-emerald-600 text-white px-6 py-3 rounded-xl font-bold hover:shadow-lg hover:shadow-emerald-500/20 transition flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add New Card
                        </button>
                    </div>
                </div>

                <div class="glass-panel rounded-2xl overflow-hidden overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr
                                class="bg-slate-900/50 text-slate-400 text-xs uppercase tracking-wider border-b border-slate-800">
                                <th class="p-4 font-medium">Order</th>
                                <th class="p-4 font-medium">Title</th>
                                <th class="p-4 font-medium">Tag</th>
                                <th class="p-4 font-medium">Description</th>
                                <th class="p-4 font-medium">Status</th>
                                <th class="p-4 font-medium text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="arsenal-table-body" class="divide-y divide-slate-800/50 text-sm">
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-500">Loading promotional cards...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>



    <!-- Ad Arsenal Modal -->
    <div id="arsenal-modal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden p-4">
        <div
            class="bg-slate-900 border border-slate-700 rounded-2xl w-full max-w-2xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold text-white mb-4" id="arsenal-modal-title">Add New Promotional Card</h3>
            <form id="arsenal-form" class="space-y-4" onsubmit="event.preventDefault(); saveArsenalCard();">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Card Title *</label>
                    <input type="text" id="arsenal-title" required
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-emerald-500 transition"
                        placeholder="e.g., Automation Suite">
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Description *</label>
                    <textarea id="arsenal-description" required rows="3"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-emerald-500 transition resize-none"
                        placeholder="Brief description of the product/service..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Tag</label>
                        <select id="arsenal-tag"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-emerald-500 transition">
                            <option value="HOT">🔥 HOT</option>
                            <option value="NEW">✨ NEW</option>
                            <option value="FOUNDATION">⚡ FOUNDATION</option>
                            <option value="PREMIUM">💎 PREMIUM</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Display Order</label>
                        <input type="number" id="arsenal-order" min="1" value="1"
                            class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-emerald-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-slate-400 mb-1">Link URL *</label>
                    <input type="url" id="arsenal-link" required
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-white focus:outline-none focus:border-emerald-500 transition"
                        placeholder="https://example.com/product">
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="arsenal-active" checked
                        class="w-4 h-4 bg-slate-800 border-slate-700 rounded text-emerald-600 focus:ring-emerald-500">
                    <label class="text-sm text-slate-300">Active (visible on homepage)</label>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
                    <button type="button" onclick="closeArsenalModal()"
                        class="px-6 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-white font-medium transition">
                        Cancel
                    </button>
                    <button type="submit" id="save-arsenal-btn"
                        class="px-6 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium shadow-lg shadow-emerald-900/20 transition">
                        <i class="fas fa-save mr-2"></i> Save Card
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar-panel');
            const backdrop = document.getElementById('sidebar-backdrop');

            // Toggle Sidebar Position
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
            } else {
                sidebar.classList.add('-translate-x-full');
            }

            // Toggle Backdrop Visibility
            if (backdrop.classList.contains('hidden')) {
                backdrop.classList.remove('hidden');
                setTimeout(() => backdrop.classList.remove('opacity-0'), 10); // Fade in
            } else {
                backdrop.classList.add('opacity-0');
                setTimeout(() => backdrop.classList.add('hidden'), 300); // Wait for fade out
            }
        }

        function switchTab(tabId) {
            // Close sidebar on mobile when navigating
            const sidebar = document.getElementById('sidebar-panel');
            if (window.innerWidth < 768 && !sidebar.classList.contains('-translate-x-full')) {
                toggleSidebar();
            }

            const tabs = ['dashboard', 'users', 'system', 'maintenance', 'arsenal'];

            // 1. Hide all views and reset tabs
            tabs.forEach(t => {
                const view = document.getElementById('view-' + t);
                if (view) view.classList.add('hidden');

                const btn = document.getElementById('tab-' + t);
                if (btn) {
                    btn.className = "flex items-center space-x-3 px-4 py-3 text-slate-400 hover:bg-slate-800/50 hover:text-white rounded-xl transition-all w-full";
                }
            });

            // 2. Show selected view
            const selectedView = document.getElementById('view-' + tabId);
            if (selectedView) {
                selectedView.classList.remove('hidden');
            } else {
                console.error(`View not found for tab: ${tabId}`);
            }

            // 3. Highlight selected tab
            const activeBtn = document.getElementById('tab-' + tabId);
            if (activeBtn) {
                activeBtn.className = "flex items-center space-x-3 px-4 py-3 bg-emerald-600 text-white shadow-lg shadow-emerald-900/20 rounded-xl transition-all w-full";
            }

            // 4. Update Title
            const titles = {
                'dashboard': 'Dashboard Overview',
                'users': 'User Management',
                'system': 'System Control',
                'maintenance': 'System Maintenance',
                'arsenal': 'Ad Arsenal Manager'
            };
            document.getElementById('page-title').innerText = titles[tabId] || 'Dashboard';

            // 5. Lazy Load Modules
            if (tabId === 'users' && !window.hasLoadedUserMgmt) {
                if (window.initUserManagement) {
                    window.initUserManagement();
                    window.hasLoadedUserMgmt = true;
                }
            }
            if (tabId === 'system' && !window.hasLoadedSystem) {
                if (window.initSystemControl) {
                    window.initSystemControl();
                    window.hasLoadedSystem = true;
                }
            }
        }


    </script>
    <!-- --- UI OVERLAY SYSTEM --- -->

    <!-- Toast Container (Fixed Top-Right) -->
    <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none"></div>

    <!-- 1. Universal Confirmation Modal -->
    <div id="modal-confirm"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300">
        <div class="bg-slate-900 border border-slate-700 rounded-xl p-6 w-full max-w-sm shadow-2xl transform scale-95 transition-transform duration-300"
            id="modal-confirm-content">
            <div class="flex items-center gap-3 mb-4">
                <div class="p-3 rounded-full bg-rose-900/30 text-rose-500" id="modal-icon-bg">
                    <i class="fas fa-exclamation-triangle text-xl" id="modal-icon"></i>
                </div>
                <h3 class="text-lg font-bold text-white" id="modal-title">Confirm Action</h3>
            </div>
            <p class="text-slate-400 text-sm mb-6" id="modal-message">Are you sure you want to proceed?</p>
            <div class="flex justify-end gap-3">
                <button id="btn-cancel-confirm"
                    class="px-4 py-2 rounded-lg text-slate-400 hover:bg-slate-800 transition">Cancel</button>
                <button id="btn-action-confirm"
                    class="px-4 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white font-medium transition shadow-lg shadow-rose-900/20">Confirm</button>
            </div>
        </div>
    </div>

    <!-- 2. History/Version Modal -->
    <div id="modal-history"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm hidden">
        <div class="bg-slate-900 border border-slate-700 rounded-xl w-full max-w-2xl h-[80vh] flex flex-col shadow-2xl">
            <div class="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-800/50">
                <h3 class="text-lg font-bold text-white"><i class="fas fa-history text-emerald-400 mr-2"></i> Version
                    History</h3>
                <button onclick="closeModal('modal-history')" class="text-slate-400 hover:text-white"><i
                        class="fas fa-times"></i></button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3" id="history-list">
                <!-- History Items Injected Here -->
                <div class="text-center text-slate-500 mt-10">Loading history...</div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script type="module" src="{{ asset('assets/js/core/admin-handler.js') }}"></script>
</body>

</html>