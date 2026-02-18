<!DOCTYPE html>
<html lang="id">

<head>
    <style>
        .locked {
            display: none !important;
        }

        /* Custom Dropdown Styles */
        .custom-dropdown {
            position: relative;
            width: auto;
            min-width: 100px;
        }

        .dropdown-btn {
            width: 100%;
            text-align: left;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0.75rem;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .dark .dropdown-btn {
            background-color: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }

        .dropdown-btn:hover {
            border-color: #10b981;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            background-color: white;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            z-index: 100;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            min-width: 140px;
            overflow: hidden;
        }

        .dark .dropdown-menu {
            background-color: #1e293b;
            border-color: #334155;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            padding: 0.6rem 1rem;
            font-size: 0.75rem;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }

        .dark .dropdown-item {
            color: #94a3b8;
        }

        .dropdown-item:hover {
            background-color: #f1f5f9;
            color: #10b981;
            padding-left: 1.25rem;
        }

        .dark .dropdown-item:hover {
            background-color: #334155;
            color: #10b981;
        }

        .dropdown-item.active {
            background-color: #ecfdf5;
            color: #10b981;
            font-weight: 700;
        }

        .dark .dropdown-item.active {
            background-color: #064e3b;
            color: #34d399;
        }
    </style>
    <script type="module">
        import { initAuthListener } from './assets/js/core/auth-engine.js';
        initAuthListener();
    </script>

    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        window.savedPlannerData = @json($latestSession);
        window.savedSimulationData = @json($latestSimulation);
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CuanCapital - Cashflow Engine - Business Planner</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="{{ asset('assets/icon/logo-2.svg') }}" type="image/svg+xml">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css" />
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script>
        // It's best to inline this in `head` to avoid FOUC
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    <link rel="preload" as="style" href="{{ asset('assets/css/style.css') }}">
    <link rel="preload" as="script" href="{{ asset('assets/js/main.js') }}">
    <script type="module" src="{{ asset('assets/js/core/system-handler.js') }}"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>


</head>

<body
    class="bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans antialiased overflow-x-hidden transition-colors duration-300 locked-screen">


    <!-- System Broadcast Banner -->
    <div id="system-broadcast"
        class="hidden relative z-50 flex items-center gap-x-6 bg-amber-600 px-6 py-2.5 sm:px-3.5 sm:before:flex-1 transition-all duration-300">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <p class="text-sm leading-6 text-white">
                <strong class="font-bold"><i class="fas fa-bullhorn mr-2"></i>Announcement</strong>
                <span id="broadcast-text" class="font-medium">System update in progress...</span>
            </p>
        </div>
        <div class="flex flex-1 justify-end">
            <button type="button"
                class="-m-3 p-3 focus-visible:outline-offset-[-4px] hover:bg-amber-700/50 rounded-lg transition"
                onclick="document.getElementById('system-broadcast').classList.add('hidden')">
                <span class="sr-only">Dismiss</span>
                <i class="fas fa-times text-white text-sm"></i>
            </button>
        </div>
    </div>

    <div id="splash-screen"
        class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-white transition-opacity duration-1000">
        <div class="splash-content flex flex-col items-center">

            <div class="flex flex-row items-center justify-center gap-4 mb-8">

                <div class="splash-logo">
                    <svg width="100" height="100" viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg"
                        class="w-20 h-20 md:w-24 md:h-24 drop-shadow-xl">
                        <g transform="translate(75, 75)">

                            <path d="M -39.7 -15 A 35 35 0 1 0 9.7 -15" fill="none" stroke="#0f172a" stroke-width="12"
                                stroke-linecap="round" />

                            <path d="M -9.7 15 A 35 35 0 1 1 39.7 15" fill="none" stroke="#0f172a" stroke-width="12"
                                stroke-linecap="round" />

                            <line x1="0" y1="-50" x2="0" y2="50" stroke="#10B981" stroke-width="12"
                                stroke-linecap="round" />
                        </g>
                    </svg>
                </div>

                <div class="brand-text overflow-hidden">
                    <h1 class="text-3xl md:text-5xl font-bold text-slate-900 tracking-tight">
                        Cuan<span class="text-emerald-600">Capital</span>
                    </h1>
                </div>
            </div>

            <div class="w-64 h-2 bg-slate-100 rounded-full overflow-hidden mb-6 relative">
                <div
                    class="loading-bar absolute top-0 left-0 h-full bg-emerald-500 rounded-full w-0 shadow-[0_0_10px_rgba(16,185,129,0.3)]">
                </div>
            </div>

            <p class="text-slate-500 font-sans text-xs font-medium tracking-[0.2em] animate-pulse uppercase">
                Lagi Setup Mesin Cuan...</p>
        </div>
    </div>

    <nav
        class="sticky top-0 z-50 bg-transparent border-b border-transparent shadow-none transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <span class="text-2xl"></span>
                    <img id="site-logo" src="{{ asset('assets/icon/logo.svg') }}" alt="CuanCapital Logo" class="h-8 md:h-10">
                </div>
                <div class="flex items-center gap-2 md:gap-4">
                    <!-- Admin Only Link -->
                    <a href="{{ route('admin') }}"
                        class="hidden admin-only p-2 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors mr-1"
                        title="Admin Dashboard">
                        <i class="fas fa-shield-alt text-xl"></i>
                    </a>
                    <button id="start-tour"
                        class="p-2 text-slate-500 dark:text-slate-400 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none"
                        title="Butuh Insight?">
                        <i class="fas fa-circle-question text-xl"></i>
                    </button>
                    <div class="custom-dropdown" id="dropdown-currency">
                        <button class="dropdown-btn" type="button">
                            <span class="selected-value">IDR (Rp)</span>
                            <i class="fas fa-chevron-down text-[10px] ml-2"></i>
                        </button>
                        <div class="dropdown-menu">
                            <div class="dropdown-item active" data-value="IDR">IDR (Rp)</div>
                            <div class="dropdown-item" data-value="USD">USD ($)</div>
                            <div class="dropdown-item" data-value="EUR">EUR (€)</div>
                            <div class="dropdown-item" data-value="GBP">GBP (£)</div>
                            <div class="dropdown-item" data-value="MYR">MYR (RM)</div>
                            <div class="dropdown-item" data-value="SGD">SGD (S$)</div>
                            <div class="dropdown-item" data-value="AUD">AUD (A$)</div>
                            <div class="dropdown-item" data-value="JPY">JPY (¥)</div>
                        </div>
                        <input type="hidden" id="currency-selector" value="IDR">
                    </div>
                    <!-- Settings Dropdown -->
                    <div class="relative">
                        <button id="settings-menu-btn"
                            class="p-2 text-slate-500 dark:text-slate-400 hover:text-emerald-500 dark:hover:text-emerald-400 transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none"
                            title="Settings">
                            <i class="fas fa-cog text-xl"></i>
                        </button>
                        <!-- Dropdown Menu -->
                        <div id="settings-dropdown"
                            class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50 transform origin-top-right transition-all duration-200">
                            <div class="py-1">
                                <a href="{{ route('settings') }}?tab=profile"
                                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-400">
                                    <i class="fas fa-user w-5"></i> Profile
                                </a>
                                <a href="{{ route('settings') }}?tab=preferences"
                                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-400">
                                    <i class="fas fa-sliders-h w-5"></i> Preferences
                                </a>
                                <a href="{{ route('settings') }}?tab=security"
                                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-400">
                                    <i class="fas fa-shield-alt w-5"></i> Security
                                </a>
                                <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                                <a href="{{ route('settings') }}?tab=system"
                                    class="block px-4 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 hover:text-emerald-600 dark:hover:text-emerald-400">
                                    <i class="fas fa-history w-5"></i> Activity Log
                                </a>
                                <div class="border-t border-slate-100 dark:border-slate-700 my-1"></div>
                                <button id="dropdown-logout-btn"
                                    class="w-full text-left block px-4 py-2 text-sm text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 hover:text-rose-700 dark:hover:text-rose-300 transition-colors">
                                    <i class="fas fa-sign-out-alt w-5"></i> Log Out
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="hidden md:flex items-center space-x-6">
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

        <section id="hero" class="relative pt-20 pb-24 lg:pt-32 lg:pb-40 max-w-7xl mx-auto">

            <div class="absolute inset-0 -z-10 h-full w-full bg-white dark:bg-slate-900 bg-[size:6rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] opacity-20 pointer-events-none"
                style="background-image: linear-gradient(0deg, transparent 24%, #e2e8f0 25%, #e2e8f0 26%, transparent 27%, transparent 74%, #e2e8f0 75%, #e2e8f0 76%, transparent 77%, transparent), linear-gradient(90deg, transparent 24%, #e2e8f0 25%, #e2e8f0 26%, transparent 27%, transparent 74%, #e2e8f0 75%, #e2e8f0 76%, transparent 77%, transparent);">
            </div>

            <div class="grid md:grid-cols-12 gap-12 lg:gap-8 items-center">

                <div
                    class="md:col-span-7 flex flex-col items-center md:items-start text-center md:text-left space-y-8 px-4 md:px-0">

                    <div id="greeting-container" class="hidden flex items-center gap-4 mb-4">
                        <img id="hero-avatar" src="" alt="Profile" 
                             class="w-14 h-14 rounded-full object-cover border-2 border-emerald-500 shadow-md p-0.5 bg-white dark:bg-slate-800">
                        <div>
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mb-0.5">Halo lagi, Sobat Cuan! 👋</p>
                            <h3 id="greeting-text" class="text-xl font-bold text-slate-900 dark:text-white leading-none"></h3>
                        </div>
                    </div>

                    <div
                        class="inline-flex items-center px-4 py-1.5 rounded-full border border-emerald-100 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-800 backdrop-blur-sm">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                        <span
                            class="text-xs font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-400">Bisnis Valid, Bukan Cuma Omon-Omon</span>
                    </div>

                    <h1
                        class="text-5xl sm:text-6xl lg:text-8xl font-black tracking-tight text-slate-900 dark:text-white leading-[1.1]">
                        Ubah Rencana Jadi <br>
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500 dark:from-emerald-400 dark:to-teal-300">
                            Profit Nyata
                        </span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-600 dark:text-slate-400 max-w-2xl leading-loose">
                        Stop nebak-nebak soal bisnis. Gunakan <strong>Framework CuanCapital</strong> yang sudah terbukti untuk bikin mesin profit yang <strong>konsisten</strong> dan bisa 
                        <strong>berkembang</strong>
                    </p>

                    <div
                        class="flex flex-col sm:flex-row flex-wrap gap-4 w-full sm:w-auto justify-center md:justify-start">

                        <a href="#calculator-section"
                            class="group relative inline-flex items-center justify-center px-8 py-4 text-base font-bold text-white transition-all duration-200 bg-emerald-600 rounded-full hover:bg-emerald-700 hover:-translate-y-1 hover:shadow-lg hover:shadow-emerald-500/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600">
                            <i class="fas fa-calculator mr-2 group-hover:rotate-12 transition-transform"></i>
                            Aktivasi Dashboard Cuan
                        </a>



                        <a href="#goal-planner"
                            class="group inline-flex items-center justify-center px-8 py-4 text-base font-bold text-slate-700 dark:text-slate-200 transition-all duration-200 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full hover:bg-emerald-50 dark:hover:bg-slate-700 hover:text-emerald-600 dark:hover:text-emerald-400">
                            <i class="fas fa-bullseye mr-2 group-hover:scale-110 transition-transform"></i>
                            Hitung Target Cuan
                        </a>
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <span class="text-sm text-slate-500 dark:text-slate-400 font-medium">Follow us:</span>
                        <a href="https://www.instagram.com/cuancapital.id?igsh=N2Vyb3d0cWpmMzJi" target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-400 transition-all duration-300 hover:scale-110 border-2 border-transparent hover:border-emerald-400 hover:shadow-[0_0_15px_rgba(52,211,153,0.6)] hover:bg-slate-900 group">
                            <i class="fab fa-instagram"></i>
                        </a>

                        <a href="https://www.facebook.com/share/15XZMS8kJKT/" target="_blank" rel="noopener noreferrer"
                            class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-400 transition-all duration-300 hover:scale-110 border-2 border-transparent hover:border-emerald-400 hover:shadow-[0_0_15px_rgba(52,211,153,0.6)] hover:bg-slate-900 group">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.tiktok.com/@cuan.capital.id?_r=1&_t=ZS-93o7k6jZzfu" target="_blank"
                            rel="noopener noreferrer"
                            class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-400 transition-all duration-300 hover:scale-110 border-2 border-transparent hover:border-emerald-400 hover:shadow-[0_0_15px_rgba(52,211,153,0.6)] hover:bg-slate-900 group">
                            <i class="fab fa-tiktok"></i>
                        </a>
                        <a href="mailto:team.cuancapital@gmail.com" target="_blank" rel="noopener noreferrer"
                            class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-400 transition-all duration-300 hover:scale-110 border-2 border-transparent hover:border-emerald-400 hover:shadow-[0_0_15px_rgba(52,211,153,0.6)] hover:bg-slate-900 group">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                </div>

                <div class="md:col-span-5 relative perspective-1000 group mt-12 md:mt-0 px-4">

                    <div
                        class="absolute -top-10 -right-10 w-72 h-72 bg-emerald-400/20 rounded-full blur-3xl opacity-50 animate-pulse">
                    </div>
                    <div class="absolute -bottom-10 -left-10 w-72 h-72 bg-teal-400/20 rounded-full blur-3xl opacity-50">
                    </div>

                    <div
                        class="relative transform transition-transform duration-500 hover:rotate-0 rotate-y-[-12deg] rotate-x-[10deg] hover:scale-105">

                        <div
                            class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl border border-white/20 dark:border-slate-700 rounded-2xl shadow-2xl p-6 relative overflow-hidden">

                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider">Target
                                        Revenue
                                    </div>
                                    <div id="hero-total-revenue"
                                        class="text-2xl font-black text-slate-900 dark:text-white">
                                        Rp 150.000.000</div>
                                </div>
                                <div
                                    class="h-8 w-8 bg-emerald-100 dark:bg-emerald-900 rounded-lg flex items-center justify-center text-emerald-600">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>

                            <div class="flex items-end justify-between h-32 gap-2 mb-4">
                                <div
                                    class="w-full bg-slate-100 dark:bg-slate-700 rounded-t-sm h-[40%] group-hover:h-[50%] transition-all duration-700">
                                </div>
                                <div
                                    class="w-full bg-slate-100 dark:bg-slate-700 rounded-t-sm h-[60%] group-hover:h-[70%] transition-all duration-700 delay-75">
                                </div>
                                <div
                                    class="w-full bg-slate-100 dark:bg-slate-700 rounded-t-sm h-[50%] group-hover:h-[40%] transition-all duration-700 delay-100">
                                </div>
                                <div
                                    class="w-full bg-emerald-200 dark:bg-emerald-800 rounded-t-sm h-[75%] group-hover:h-[85%] transition-all duration-700 delay-150">
                                </div>
                                <div
                                    class="w-full bg-emerald-500 rounded-t-sm h-[90%] group-hover:h-[100%] transition-all duration-700 delay-200 shadow-[0_0_15px_rgba(16,185,129,0.5)]">
                                </div>
                            </div>

                            <div
                                class="flex items-center gap-2 px-3 py-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg border border-emerald-100 dark:border-emerald-800">
                                <span class="relative flex h-2 w-2">
                                    <span
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Engine Status:
                                    Ready to Scale</span>
                            </div>
                        </div>

                        <div class="absolute -right-4 top-10 bg-white dark:bg-slate-800 p-4 rounded-xl shadow-xl border border-slate-100 dark:border-slate-700 animate-bounce"
                            style="animation-duration: 3s;">
                            <div class="text-[10px] text-slate-400 font-bold uppercase">Target Sales</div>
                            <div id="hero-target-sales" class="text-lg font-black text-emerald-500">50 Unit</div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        @if(($settings['feature_calculator'] ?? '1') == '1')
        <section id="goal-planner" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden mb-12">
            <div class="p-8 lg:p-10">
                <div class="text-center mb-10">
                    <h3 class="text-3xl font-bold text-slate-900 dark:text-white">Reverse Goal Planner <span class="text-emerald-500">2.0</span></h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-2">Design bisnis impian sesuai goals hidup kamu. Bukan sebaliknya.</p>
                </div>

                <form id="reverse-planner-form" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Business Model -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            Model Bisnis
                            <i class="fas fa-info-circle ml-1 cursor-pointer text-slate-300 hover:text-emerald-500 transition-colors help-icon" data-term="business_model"></i>
                        </label>
                        <select id="rp-model" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                            <option value="dropship">Dropship (Low Margin, High Vol)</option>
                            <option value="digital">Digital Product (High Margin)</option>
                            <option value="service" selected>Service / Agency (High Ticket)</option>
                            <option value="stock">Stock / Retail (Avg Margin)</option>
                            <option value="affiliate">Affiliate (No Product)</option>
                        </select>
                    </div>

                    <!-- Target Profit -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            Target Cuan Bulanan
                            <i class="fas fa-info-circle ml-1 cursor-pointer text-slate-300 hover:text-emerald-500 transition-colors help-icon" data-term="target_profit"></i>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="number" id="rp-target-profit" placeholder="10000000" required
                                class="w-full pl-12 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Capital Available -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            Modal Ready
                            <i class="fas fa-info-circle ml-1 cursor-pointer text-slate-300 hover:text-emerald-500 transition-colors help-icon" data-term="capital_available"></i>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="number" id="rp-capital" placeholder="5000000" required
                                class="w-full pl-12 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Deadline Target (Hari)</label>
                        <input type="number" id="rp-timeline" value="30" placeholder="30" required
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Selling Price Content -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            Harga Jual (Avg)
                            <i class="fas fa-info-circle ml-1 cursor-pointer text-slate-300 hover:text-emerald-500 transition-colors help-icon" data-term="selling_price"></i>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">Rp</span>
                            <input type="number" id="rp-price" placeholder="150000" required
                                class="w-full pl-12 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white font-mono font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Hours Available -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Daily Grind Time (Jam)</label>
                        <input type="number" id="rp-hours" value="4" max="24" required
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 text-slate-900 dark:text-white font-bold focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Traffic Strategy -->
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center">
                            Strategi Traffic
                            <i class="fas fa-info-circle ml-1 cursor-pointer text-slate-300 hover:text-emerald-500 transition-colors help-icon" data-term="traffic_strategy"></i>
                        </label>
                        <div class="custom-dropdown" id="dropdown-rp-strategy">
                            <button class="dropdown-btn" type="button">
                                <span class="selected-value">Paid Ads (Fast, Capital Heavy)</span>
                                <i class="fas fa-chevron-down text-[10px] ml-2"></i>
                            </button>
                            <div class="dropdown-menu !right-auto !left-0 w-full">
                                <div class="dropdown-item active" data-value="ads">Paid Ads (Fast, Capital Heavy)</div>
                                <div class="dropdown-item" data-value="organic">Organic Content (Slow, Time Heavy)</div>
                                <div class="dropdown-item" data-value="hybrid">Hybrid (Balanced)</div>
                            </div>
                            <input type="hidden" id="rp-strategy" value="ads">
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-end">
                        <button type="submit" id="rp-calculate-btn"
                            class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-emerald-500/30 transition-all transform hover:-translate-y-1">
                            <i class="fas fa-rocket mr-2"></i> Lets Go! Generate Blueprint
                        </button>
                    </div>
                </form>

                <!-- Results Container (Mentally Safe Redesign) -->
                <div id="rp-results" class="hidden mt-12 pt-10 border-t border-slate-200 dark:border-slate-700 animate-fade-in">
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Left: Simplified Milestones (Keep useful numbers) -->
                        <div class="space-y-6">
                            <h4 class="font-bold text-lg text-slate-800 dark:text-white flex items-center gap-2">
                                <i class="fas fa-flag-checkered text-slate-400"></i> Target Milestones
                            </h4>
                            
                            <div class="bg-slate-50 dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                                <div>
                                    <p class="text-xs text-slate-500 uppercase font-bold">Sales Target</p>
                                    <div class="flex items-baseline gap-2">
                                        <p id="rp-req-units" class="text-xl font-black text-slate-800 dark:text-white mt-1">0 Units</p>
                                        <span class="text-xs text-slate-400">/ bulan</span>
                                    </div>
                                    <p class="text-xs text-slate-400 font-medium">~ <span id="rp-daily-units">0</span> sales/hari</p>
                                </div>
                                <div class="w-full h-px bg-slate-200 dark:bg-slate-700"></div>
                                <div>
                                    <p class="text-xs text-slate-500 uppercase font-bold">Kebutuhan Iklan</p>
                                    <p id="rp-req-budget" class="text-xl font-black text-slate-800 dark:text-white mt-1">Rp 0</p>
                                    <p class="text-xs text-slate-400">Total budget selama periode</p>
                                </div>
                                <div class="w-full h-px bg-slate-200 dark:bg-slate-700"></div>
                                <div>
                                    <p class="text-xs text-slate-500 uppercase font-bold">Traffic Needed</p>
                                    <p id="rp-req-traffic" class="text-xl font-black text-slate-800 dark:text-white mt-1">0 Visitors</p>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Psychology Layer (Goal Status & Guide) -->
                        <div class="lg:col-span-2 space-y-6">
                            
                            <!-- Block 1: Goal Status -->
                            <div id="rp-status-card" class="bg-white dark:bg-slate-800 p-6 rounded-2xl border-l-4 shadow-sm transition-colors duration-300">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Status Goals Kamu</p>
                                        <h3 id="rp-goal-status" class="text-2xl font-black text-slate-900 dark:text-white mb-2">Lagi Ngitung...</h3>
                                        <p id="rp-constraint-msg" class="text-sm text-slate-600 dark:text-slate-300">Cek ombak dulu...</p>
                                    </div>
                                    <div id="rp-status-icon" class="text-4xl opacity-20">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                    </div>
                                </div>
                                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                                    <button id="rp-why-btn" class="text-xs font-bold text-slate-400 hover:text-emerald-500 underline decoration-dotted transition-colors">
                                        Kenapa status gue begini?
                                    </button>
                                </div>
                            </div>

                            <!-- Block 2: Focus / Learning Moment -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-5 rounded-2xl border border-blue-100 dark:border-blue-800">
                                <h4 class="font-bold text-blue-800 dark:text-blue-300 mb-2 flex items-center gap-2 text-sm uppercase">
                                    <i class="fas fa-lightbulb text-blue-500"></i> Insight Mentor
                                </h4>
                                <p id="rp-learning-moment" class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">
                                    Bocoran insight bakal muncul di sini.
                                </p>
                            </div>

                            <!-- Block 3: Safe Recommendations -->
                            <div id="rp-recommendations-box" class="hidden">
                                <h4 class="font-bold text-slate-800 dark:text-white mb-3 text-sm uppercase tracking-wide">
                                    Saran Penyesuaian (Opsional)
                                </h4>
                                <div id="rp-rec-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Buttons injected by JS -->
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Hidden Modal for "Why?" transparency -->
                    <div id="rp-why-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 animate-fade-in">
                        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-2xl max-w-sm w-full border border-slate-200 dark:border-slate-700">
                            <h4 class="font-bold text-lg text-slate-900 dark:text-white mb-4">Analisis Cepat</h4>
                            <div class="space-y-4 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Ketersediaan Modal</span>
                                    <span id="rp-why-capital" class="font-bold text-slate-800 dark:text-white">--</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Ketersediaan Waktu</span>
                                    <span id="rp-why-hours" class="font-bold text-slate-800 dark:text-white">--</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Kesehatan Margin</span>
                                    <span id="rp-why-margin" class="font-bold text-slate-800 dark:text-white">--</span>
                                </div>
                            </div>
                            <button id="rp-why-close" class="mt-6 w-full py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl font-bold hover:bg-slate-200">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section id="profit-simulator-section" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-xl relative overflow-hidden transition-all duration-500">
            <!-- Grid Background -->
            <div class="absolute inset-0 bg-grid-slate-100 dark:bg-grid-slate-900/[0.04] bg-[bottom_1px_center] pointer-events-none"></div>

            <div class="relative z-10">
                <!-- Header -->
                <div class="text-center mb-10">
                    <h2 class="text-3xl font-black text-slate-900 dark:text-white mb-2">Area Scale Up <span class="text-emerald-500">Paling Gacor</span></h2>
                    <p class="text-slate-500 dark:text-slate-400">Pilih satu area buat difokuskan. Liat efek cuannya ke dompet kamu.</p>
                </div>

                <!-- Gate Message (Hidden by default) -->
                <div id="ps-gate-overlay" class="hidden absolute inset-0 z-50 bg-white/90 dark:bg-slate-900/90 flex items-center justify-center p-6 text-center backdrop-blur-sm">
                    <div class="max-w-md">
                        <div class="text-5xl mb-4 text-amber-500"><i class="fas fa-lock"></i></div>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Gaskeun Goal Dulu</h3>
                        <p class="text-slate-600 dark:text-slate-300 mb-6">Target bisnis kamu sekarang masih "Keberatan". Optimasi baru bakal legit kakamu fondasi bisnis kamu udah realistis. Coba adjust goals di atas dulu.</p>
                        <button onclick="document.getElementById('reverse-planner-section').scrollIntoView({behavior: 'smooth'})" class="bg-emerald-600 text-white px-6 py-2 rounded-full font-bold hover:bg-emerald-700 transition">
                            Sesuaikan Goal
                        </button>
                    </div>
                </div>

                <!-- Layout: 2 Columns (Zones | Results) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    <!-- Left: Leverage Zones (Grid of 4) -->
                    <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Traffic Zone -->
                        <div class="zone-card group relative bg-slate-50 dark:bg-slate-900 rounded-2xl p-6 border-2 border-transparent hover:border-blue-500 cursor-pointer transition-all duration-300" data-zone="traffic">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-xl text-blue-600 dark:text-blue-400">
                                    <i class="fas fa-users text-xl"></i>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Perbesar (Scale)</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Trafik</h3>
                            <p class="text-xs text-slate-500 mb-4">Mendatangkan lebih banyak pengunjung.</p>
                            
                            <!-- Level Selection (Visible on Active) -->
                            <div class="level-selector hidden mt-4 space-y-2 animate-fade-in">
                                <p class="text-xs font-bold text-blue-500 mb-2">Pilih Tingkat Upaya:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-blue-50 hover:border-blue-300 transition flex flex-col items-center" data-level="1">
                                        <span>Tingkat 1</span>
                                        <span class="font-medium opacity-60 mt-1">Organic (+10%)</span>
                                    </button>
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-blue-50 hover:border-blue-300 transition flex flex-col items-center" data-level="2">
                                        <span>Tingkat 2</span>
                                        <span class="font-medium opacity-60 mt-1">Kampanye Iklan (+20%)</span>
                                    </button>
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-blue-50 hover:border-blue-300 transition flex flex-col items-center" data-level="3">
                                        <span>Tingkat 3</span>
                                        <span class="font-medium opacity-60 mt-1">Agresif (+35%)</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Conversion Zone -->
                        <div class="zone-card group relative bg-slate-50 dark:bg-slate-900 rounded-2xl p-6 border-2 border-transparent hover:border-emerald-500 cursor-pointer transition-all duration-300" data-zone="conversion">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl text-emerald-600 dark:text-emerald-400">
                                    <i class="fas fa-magic text-xl"></i>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Optimasi</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Konversi</h3>
                            <p class="text-xs text-slate-500 mb-4">Meningkatkan efektivitas penjualan.</p>
                            
                            <div class="level-selector hidden mt-4 space-y-2 animate-fade-in">
                                <p class="text-xs font-bold text-emerald-500 mb-2">Pilih Tingkat Upaya:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:border-emerald-300 transition flex flex-col items-center" data-level="1">
                                        <span>Tingkat 1</span>
                                        <span class="font-medium opacity-60 mt-1">Optimasi UI (+5%)</span>
                                    </button>
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:border-emerald-300 transition flex flex-col items-center" data-level="2">
                                        <span>Tingkat 2</span>
                                        <span class="font-medium opacity-60 mt-1">Copywriting (+10%)</span>
                                    </button>
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-emerald-50 hover:border-emerald-300 transition flex flex-col items-center" data-level="3">
                                        <span>Tingkat 3</span>
                                        <span class="font-medium opacity-60 mt-1">Sinkronisasi Funnel (+20%)</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Zone -->
                        <div class="zone-card group relative bg-slate-50 dark:bg-slate-900 rounded-2xl p-6 border-2 border-transparent hover:border-amber-500 cursor-pointer transition-all duration-300" data-zone="pricing">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-amber-100 dark:bg-amber-900/30 rounded-xl text-amber-600 dark:text-amber-400">
                                    <i class="fas fa-tag text-xl"></i>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nilai (Value)</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Harga (Pricing)</h3>
                            <p class="text-xs text-slate-500 mb-4">Menaikkan nilai jual produk.</p>
                            
                            <div class="level-selector hidden mt-4 space-y-2 animate-fade-in">
                                <p class="text-xs font-bold text-amber-500 mb-2">Pilih Tingkat Upaya:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-amber-50 hover:border-amber-300 transition flex flex-col items-center" data-level="1">
                                        <span>Tingkat 1</span>
                                        <span class="font-medium opacity-60 mt-1">Addon (+3%)</span>
                                    </button>
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-amber-50 hover:border-amber-300 transition flex flex-col items-center" data-level="2">
                                        <span>Tingkat 2</span>
                                        <span class="font-medium opacity-60 mt-1">Premium (+7%)</span>
                                    </button>
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-amber-50 hover:border-amber-300 transition flex flex-col items-center" data-level="3">
                                        <span>Tingkat 3</span>
                                        <span class="font-medium opacity-60 mt-1">Elite (+12%)</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cost Zone -->
                        <div class="zone-card group relative bg-slate-50 dark:bg-slate-900 rounded-2xl p-6 border-2 border-transparent hover:border-rose-500 cursor-pointer transition-all duration-300" data-zone="cost">
                            <div class="flex justify-between items-start mb-4">
                                <div class="p-3 bg-rose-100 dark:bg-rose-900/30 rounded-xl text-rose-600 dark:text-rose-400">
                                    <i class="fas fa-scissors text-xl"></i>
                                </div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Efisiensi</span>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Cost (Biaya)</h3>
                            <p class="text-xs text-slate-500 mb-4">Pangkas biaya biar margin makin tebel.</p>
                            
                            <div class="level-selector hidden mt-4 space-y-2 animate-fade-in">
                                <p class="text-xs font-bold text-rose-500 mb-2">Pilih Tingkat Upaya:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-rose-50 hover:border-rose-300 transition flex flex-col items-center" data-level="1">
                                        <span>Tingkat 1</span>
                                        <span class="font-medium opacity-60 mt-1">Sourcing (-5%)</span>
                                    </button>
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-rose-50 hover:border-rose-300 transition flex flex-col items-center" data-level="2">
                                        <span>Tingkat 2</span>
                                        <span class="font-medium opacity-60 mt-1">Operasional Ramping (-10%)</span>
                                    </button>
                                    <button class="level-btn p-2 rounded-lg text-[10px] font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-rose-50 hover:border-rose-300 transition flex flex-col items-center" data-level="3">
                                        <span>Tingkat 3</span>
                                        <span class="font-medium opacity-60 mt-1">Outsource (-15%)</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Intelligence Module (Result) -->
                    <div class="lg:col-span-5 space-y-6">
                        <!-- Default State -->
                        <div id="ps-default-state" class="bg-slate-50 dark:bg-slate-900 text-slate-400 rounded-3xl p-8 border-2 border-dashed border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-center h-full min-h-[300px]">
                            <i class="fas fa-hand-pointer text-4xl mb-4 opacity-50"></i>
                            <p class="text-sm font-medium">Klik salah satu zona di kiri<br>buat liat potensi cuan kamu.</p>
                        </div>

                        <!-- Result Card (Hidden by default) -->
                        <div id="simulation-result" class="hidden bg-slate-900 text-white rounded-3xl p-8 shadow-2xl relative overflow-hidden ring-1 ring-white/10 animate-fade-in">
                            <!-- Decoration -->
                            <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/20 rounded-full blur-3xl -mr-16 -mt-16 pointer-events-none"></div>
                            
                            <div class="relative z-10">
                                <div class="flex items-center gap-2 mb-6">
                                    <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                                    <span class="text-xs font-bold tracking-widest uppercase text-emerald-400">Potensi Hasil</span>
                                </div>

                                <div class="mb-8">
                                    <p class="text-slate-400 text-xs uppercase font-bold mb-1">Estimasi Bonus Cuan</p>
                                    <h3 id="ps-profit-range" class="text-3xl font-black text-white tracking-tight">Rp 0 - 0</h3>
                                    <p id="ps-insight" class="text-sm text-slate-300 mt-2 italic">Klik lever buat liat dampaknya.</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-white/5 p-4 rounded-2xl backdrop-blur-sm border border-white/10">
                                        <p class="text-[10px] uppercase text-slate-400 font-bold mb-1">Tingkat Upaya (Effort)</p>
                                        <p id="ps-effort" class="font-bold text-white">—</p>
                                    </div>
                                    <div class="bg-white/5 p-4 rounded-2xl backdrop-blur-sm border border-white/10">
                                        <p class="text-[10px] uppercase text-slate-400 font-bold mb-1">Profil Resiko</p>
                                        <p id="ps-risk" class="font-bold text-white">—</p>
                                    </div>
                                </div>

                                <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-xl">
                                    <p class="text-xs font-bold text-emerald-400 uppercase mb-1">Reflection (Jujurly)</p>
                                    <p id="ps-reflection" class="text-sm text-white font-medium">Udah siap eksekusi belum?</p>
                                </div>

                                <button class="w-full mt-6 bg-white text-slate-900 font-bold py-3 rounded-xl hover:bg-slate-100 transition-colors">
                                    Simpan Blueprint Ini <i class="fas fa-arrow-right ml-2 opacity-50"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif



        <section id="other-products" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h3 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white mb-2 text-center">The Scaling
                Arsenal</h3>
            <p class="text-center text-slate-500 dark:text-slate-400 mb-10 max-w-2xl mx-auto">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Jangan Perang Tanpa Gear.</span>
                Lengkapi strategi kamu pake tools premium ini buat <span
                    class="text-slate-900 dark:text-white font-bold">akselerasi profit 2x lebih kenceng</span>.
            </p>

            <div class="md:hidden flex justify-center mb-6">
                <span
                    class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 rounded-full text-[10px] font-bold uppercase tracking-wider animate-pulse border border-slate-200 dark:border-slate-700">
                    <i class="fas fa-arrows-left-right text-emerald-500"></i> Geser Samping
                </span>
            </div>

            <div id="ad-arsenal-container"
                class="flex md:flex-wrap justify-center gap-6 overflow-x-auto p-4 md:p-0 snap-x snap-mandatory no-scrollbar">
                <!-- Data loaded via ad-arsenal-frontend.js -->
            </div>
        </section>

    <!-- BUSINESS SIMULATION LAB SECTION -->
    <section id="business-simulation-lab" class="py-20 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <span class="text-emerald-600 dark:text-emerald-400 font-bold tracking-widest uppercase text-sm">Validasi Ide Bisnis</span>
                <h2 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white sm:text-4xl">
                    Business Simulation Lab <span class="bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200 text-xs px-2 py-1 rounded-full align-top ml-2">Beta</span>
                </h2>
                <p class="mt-4 text-xl text-slate-500 dark:text-slate-400 max-w-2xl mx-auto">
                    Uji potensi bisnis kamu sebelum bakar duit. Optimasi yang udah jalan atau plan bisnis baru pake data real.
                </p>
            </div>

            <!-- Mode Selection -->
            <div class="flex justify-center mb-10">
                <div class="bg-white dark:bg-slate-800 p-1.5 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 inline-flex">
                    <button id="mode-optimizer-btn" class="px-6 py-2.5 rounded-xl font-bold text-sm transition-all duration-200 bg-emerald-600 text-white shadow-lg shadow-emerald-500/30">
                        <i class="fas fa-rocket mr-2"></i> OPTIMIZER (Bisnis Jalan)
                    </button>
                    <button id="mode-planner-btn" class="px-6 py-2.5 rounded-xl font-bold text-sm transition-all duration-200 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white">
                        <i class="fas fa-compass mr-2"></i> PLANNER (Bisnis Baru)
                    </button>
                </div>
            </div>

            <!-- Main Lab Container -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Input Panel -->
                <div class="lg:col-span-4 bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700 shadow-xl shadow-slate-200/50 dark:shadow-none">
                    <form id="mentor-form">
                        
                        <!-- OPTIMIZER INPUTS -->
                        <div id="optimizer-inputs" class="space-y-4">
                            <div class="bg-emerald-50 dark:bg-emerald-900/20 p-4 rounded-xl mb-4 border border-emerald-100 dark:border-emerald-800/50">
                                <h4 class="text-sm font-bold text-emerald-800 dark:text-emerald-400 mb-1"><i class="fas fa-info-circle mr-1"></i> Mode Optimizer</h4>
                                <p class="text-xs text-emerald-600 dark:text-emerald-500">Masukin data real bisnis kamu sekarang buat diagnosa bottleneck.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Traffic / Bulan</label>
                                <input type="number" id="opt-traffic" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500" placeholder="1000">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Conv. Rate (%)</label>
                                    <input type="number" step="0.01" id="opt-conversion" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500" placeholder="1.5">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Harga Jual</label>
                                    <input type="number" id="opt-price" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500" placeholder="150000">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">HPP / Unit</label>
                                    <input type="number" id="opt-cost" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500" placeholder="50000">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Fixed Cost</label>
                                    <input type="number" id="opt-fixed" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-emerald-500" placeholder="5000000">
                                </div>
                            </div>
                        </div>

                        <!-- PLANNER INPUTS -->
                        <div id="planner-inputs" class="space-y-4 hidden">
                            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl mb-4 border border-blue-100 dark:border-blue-800/50">
                                <h4 class="text-sm font-bold text-blue-800 dark:text-blue-400 mb-1"><i class="fas fa-info-circle mr-1"></i> Mode Planner</h4>
                                <p class="text-xs text-blue-600 dark:text-blue-500">Pake benchmark industri buat simulasi bisnis baru kamu.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tipe Bisnis</label>
                                <div class="custom-dropdown" id="dropdown-planner-type">
                                    <button class="dropdown-btn" type="button">
                                        <span class="selected-value">Produk Digital (E-course/Software)</span>
                                        <i class="fas fa-chevron-down text-[10px] ml-2"></i>
                                    </button>
                                    <div class="dropdown-menu !right-auto !left-0 w-full">
                                        <div class="dropdown-item active" data-value="digital">Produk Digital (E-course/Software)</div>
                                        <div class="dropdown-item" data-value="physical">Produk Fisik (Fashion/F&B)</div>
                                        <div class="dropdown-item" data-value="service">Jasa / Agency</div>
                                    </div>
                                    <input type="hidden" id="planner-type" value="digital">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Target Profit / Bulan</label>
                                <input type="number" id="plan-target" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-blue-500" placeholder="10000000">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Estimasi Harga Jual</label>
                                <input type="number" id="plan-price" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-blue-500" placeholder="250000">
                            </div>

                            <div class="border-t border-slate-200 dark:border-slate-700 pt-4 mt-4">
                                <p class="text-xs font-bold text-slate-400 mb-3 uppercase tracking-wider">Internal Benchmark Data</p>
                                <div class="grid grid-cols-2 gap-4 opacity-75">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Est. Conversion (%)</label>
                                        <input type="number" id="planner-conversion" disabled value="2.0" class="w-full bg-slate-100 dark:bg-slate-800 border-transparent rounded px-3 py-1.5 text-sm font-mono text-slate-600 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Est. Margin (%)</label>
                                        <input type="number" id="planner-margin" disabled value="40" class="w-full bg-slate-100 dark:bg-slate-800 border-transparent rounded px-3 py-1.5 text-sm font-mono text-slate-600 cursor-not-allowed">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8">
                            <button type="submit" id="mentor-submit-btn" class="w-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold py-3.5 rounded-xl hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                                <i class="fas fa-calculator"></i> Hitung Simulasi
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Dashboard Area -->
                <div id="mentor-board-container" class="lg:col-span-8 relative min-h-[600px]">
                    
                    <!-- Loading Overlay -->
                    <div id="mentor-loading" class="hidden absolute inset-0 bg-white/90 dark:bg-slate-900/90 z-50 flex flex-col items-center justify-center backdrop-blur-sm rounded-3xl transition-all duration-300">
                        <div class="relative w-20 h-20 mb-6">
                            <div class="absolute inset-0 border-4 border-slate-200 dark:border-slate-700 rounded-full"></div>
                            <div class="absolute inset-0 border-4 border-emerald-500 rounded-full border-t-transparent animate-spin"></div>
                            <i class="fas fa-brain absolute inset-0 flex items-center justify-center text-emerald-500 text-2xl animate-pulse"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white animate-pulse mb-2" id="loading-text">Lagi Bedah Struktur Bisnis Kamu...</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Sabar ya, lagi ngitung potensi cuan kamu.</p>
                    </div>

                    <div id="mentor-dashboard" class="space-y-6 hidden">
                        
                        <!-- A. BASELINE SUMMARY -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="fas fa-chart-line text-9xl text-slate-900 dark:text-white"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Baseline Finansial</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 relative z-10">
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">Revenue</p>
                                    <h4 id="res-revenue" class="text-sm sm:text-base md:text-xl font-black text-slate-900 dark:text-white truncate">RP 0</h4>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">Gross Profit</p>
                                    <h4 id="res-gross" class="text-sm sm:text-base md:text-xl font-bold text-emerald-600 truncate">RP 0</h4>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">Net Profit</p>
                                    <h4 id="res-net" class="text-sm sm:text-base md:text-xl font-bold text-emerald-500 truncate">RP 0</h4>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">Margin</p>
                                    <h4 id="res-margin" class="text-sm sm:text-base md:text-xl font-bold text-blue-500 truncate">0%</h4>
                                </div>
                            </div>
                            <!-- Planner Specific: Growth Gap -->
                            <div id="planner-gap-alert" class="hidden mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
                                <div class="flex items-center gap-2 text-rose-500">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span class="text-xs font-bold">Target Gap: <span id="res-gap-val" class="truncate">RP 0</span></span>
                                </div>
                            </div>
                        </div>

                        <!-- PLANNER SPECIFIC: TARGET BREAKDOWN -->
                        <div id="planner-breakdown" class="hidden bg-blue-50 dark:bg-blue-900/20 rounded-2xl p-6 border border-blue-100 dark:border-blue-800/30">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-blue-900 dark:text-blue-100 flex items-center gap-2">
                                        <i class="fas fa-crosshairs"></i> Target Breakdown
                                    </h3>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">What you need to hit your goal.</p>
                                </div>
                                <span id="plan-feasibility" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold uppercase whitespace-nowrap">Analyzing...</span>
                            </div>
                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 text-center">
                                <div class="bg-white dark:bg-slate-800 p-2 sm:p-3 rounded-xl shadow-sm">
                                    <p class="text-[10px] text-slate-400 uppercase font-bold truncate">Sales Needed</p>
                                    <h4 id="plan-units" class="text-sm sm:text-lg font-black text-slate-800 dark:text-white truncate">0</h4>
                                    <p class="text-[10px] text-slate-400">units/mo</p>
                                </div>
                                <div class="bg-white dark:bg-slate-800 p-2 sm:p-3 rounded-xl shadow-sm">
                                    <p class="text-[10px] text-slate-400 uppercase font-bold truncate">Traffic Needed</p>
                                    <h4 id="plan-traffic" class="text-sm sm:text-lg font-black text-slate-800 dark:text-white truncate">0</h4>
                                    <p class="text-[10px] text-slate-400">visitors/mo</p>
                                </div>
                                <div class="bg-white dark:bg-slate-800 p-2 sm:p-3 rounded-xl shadow-sm">
                                    <p class="text-[10px] text-slate-400 uppercase font-bold truncate">Conv. Rate</p>
                                    <h4 id="plan-conversion" class="text-sm sm:text-lg font-black text-slate-800 dark:text-white truncate">0%</h4>
                                    <p class="text-[10px] text-slate-400">assumed</p>
                                </div>
                                <div class="bg-white dark:bg-slate-800 p-2 sm:p-3 rounded-xl shadow-sm">
                                    <p class="text-[10px] text-slate-400 uppercase font-bold truncate">Est. Ad Budget</p>
                                    <h4 id="plan-budget" class="text-xs sm:text-lg font-black text-slate-800 dark:text-white truncate">RP 0</h4>
                                    <p class="text-[10px] text-slate-400">@ 10% ROAS</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- B. SCENARIO PLAYGROUND -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                                <h3 class="font-bold text-sm mb-4 flex items-center gap-2 text-slate-700 dark:text-slate-200">
                                    <i class="fas fa-sliders-h text-slate-400"></i> Scenario Playground
                                </h3>
                                <div class="space-y-5">
                                    <!-- Traffic -->
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <label class="text-xs font-bold text-slate-500">Traffic</label>
                                            <span id="val-traffic" class="text-xs font-bold text-blue-600">0%</span>
                                        </div>
                                        <input type="range" id="slider-traffic" min="-50" max="50" step="5" value="0" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer dark:bg-slate-700 accent-blue-600">
                                    </div>
                                    <!-- Conversion -->
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <label class="text-xs font-bold text-slate-500">Conversion</label>
                                            <span id="val-conversion" class="text-xs font-bold text-emerald-600">0%</span>
                                        </div>
                                        <input type="range" id="slider-conversion" min="-2" max="3" step="0.1" value="0" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer dark:bg-slate-700 accent-emerald-500">
                                    </div>
                                    <!-- Price -->
                                    <div>
                                        <div class="flex justify-between mb-1">
                                            <label class="text-xs font-bold text-slate-500">Price</label>
                                            <span id="val-price" class="text-xs font-bold text-purple-600">0%</span>
                                        </div>
                                        <input type="range" id="slider-price" min="-30" max="50" step="5" value="0" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer dark:bg-slate-700 accent-purple-500">
                                    </div>
                                </div>
                                <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-between items-center">
                                    <div>
                                        <p class="text-xs text-slate-500 uppercase font-bold">Projected Revenue</p>
                                        <h4 class="text-sm sm:text-lg font-bold text-slate-800 dark:text-white truncate max-w-[150px]">Coming Soon</h4>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-slate-500 uppercase font-bold">Growth</p>
                                        <span id="sim-growth" class="text-sm sm:text-lg font-black text-emerald-500">+0%</span>
                                    </div>
                                </div>
                            </div>

                            <!-- C. SENSITIVITY ANALYSIS -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                                <h3 class="font-bold text-sm mb-4 flex items-center gap-2 text-slate-700 dark:text-slate-200">
                                    <i class="fas fa-chart-bar text-slate-400"></i> Sensitivity Analysis
                                </h3>
                                <p class="text-[10px] text-slate-400 mb-4">Impact on Revenue if variable increases by 10%.</p>
                                
                                <div class="space-y-3">
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="font-bold text-slate-600 dark:text-slate-400">Traffic Impact</span>
                                            <span id="sens-traffic-val" class="font-bold text-blue-500 text-[10px] sm:text-xs">+0%</span>
                                        </div>
                                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div id="sens-traffic-bar" class="h-full bg-blue-500" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="font-bold text-slate-600 dark:text-slate-400">Conversion Impact</span>
                                            <span id="sens-conv-val" class="font-bold text-emerald-500 text-[10px] sm:text-xs">+0%</span>
                                        </div>
                                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div id="sens-conv-bar" class="h-full bg-emerald-500" style="width: 0%"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs mb-1">
                                            <span class="font-bold text-slate-600 dark:text-slate-400">Price Impact</span>
                                            <span id="sens-price-val" class="font-bold text-purple-500 text-[10px] sm:text-xs">+0%</span>
                                        </div>
                                        <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div id="sens-price-bar" class="h-full bg-purple-500" style="width: 0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- D. BREAK-EVEN ANALYSIS -->
                            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm">
                                <h3 class="font-bold text-sm mb-4 flex items-center gap-2 text-slate-700 dark:text-slate-200">
                                    <i class="fas fa-balance-scale-right text-slate-400"></i> Break-Even Analysis
                                </h3>
                                <div class="grid grid-cols-2 gap-4 text-center mb-4">
                                    <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl">
                                        <p class="text-[10px] text-slate-400 uppercase font-bold">BEP Units</p>
                                        <h4 id="be-units" class="text-sm sm:text-lg font-bold text-slate-800 dark:text-white truncate">0</h4>
                                    </div>
                                    <div class="p-3 bg-slate-50 dark:bg-slate-900 rounded-xl">
                                        <p class="text-[10px] text-slate-400 uppercase font-bold">BEP Traffic</p>
                                        <h4 id="be-traffic" class="text-sm sm:text-lg font-bold text-slate-800 dark:text-white truncate">0</h4>
                                    </div>
                                </div>
                                <div id="be-warning" class="hidden flex items-start gap-2 text-rose-500 bg-rose-50 dark:bg-rose-900/20 p-3 rounded-lg">
                                    <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                                    <span class="text-xs">Warning: Your current traffic is below break-even point. You are operating at a loss.</span>
                                </div>
                                <div id="be-success" class="hidden flex items-start gap-2 text-emerald-600 bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded-lg">
                                    <i class="fas fa-check-circle mt-0.5 flex-shrink-0"></i>
                                    <span class="text-xs">Great! You are operating above break-even point.</span>
                                </div>
                            </div>

                            <!-- E. UPSELL SIMULATOR -->
                            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                                    <i class="fas fa-rocket text-8xl text-white"></i>
                                </div>
                                <h3 class="font-bold text-sm mb-4 flex items-center gap-2 z-10 relative">
                                    <i class="fas fa-rocket text-emerald-400"></i> Upsell Simulator
                                </h3>
                                
                                <div class="grid grid-cols-2 gap-3 mb-4 relative z-10">
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase">Upsell Price</label>
                                        <input type="number" id="up-price" class="w-full bg-slate-800 border border-slate-600 rounded px-2 py-1.5 text-sm text-white focus:border-emerald-500" placeholder="e.g. 50000">
                                    </div>
                                    <div>
                                        <label class="text-[10px] font-bold text-slate-400 uppercase">Take Rate %</label>
                                        <input type="number" id="up-rate" class="w-full bg-slate-800 border border-slate-600 rounded px-2 py-1.5 text-sm text-white focus:border-emerald-500" placeholder="e.g. 20">
                                    </div>
                                </div>
                                
                                <button id="btn-simulate-upsell" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2 rounded-lg transition-colors relative z-10 mb-4">
                                    Simulate Upsell
                                </button>

                                <div id="upsell-result" class="hidden pt-4 border-t border-slate-700 relative z-10">
                                    <div class="flex justify-between items-end">
                                        <div>
                                            <p class="text-[10px] text-slate-400">New Revenue</p>
                                            <h4 id="up-new-revenue" class="text-sm sm:text-lg font-bold text-white truncate max-w-[120px]">RP 0</h4>
                                        </div>
                                        <span id="up-increase" class="text-emerald-400 font-bold text-lg sm:text-xl">+0%</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- F. DIAGNOSTIC RESULT -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border-l-4 border-rose-500 shadow-sm flex flex-col md:flex-row items-start gap-4">
                            <div class="flex items-center gap-4 w-full">
                                <div class="w-10 h-10 rounded-full bg-rose-100 flex-shrink-0 flex items-center justify-center text-rose-600">
                                    <i class="fas fa-user-md text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-sm uppercase tracking-wide">Diagnostic Result</h4>
                                    <p id="diag-primary" class="text-base sm:text-lg font-bold text-rose-600 mt-1">Analyzing...</p>
                                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">Recommended Focus: <span id="diag-rec" class="font-bold text-slate-700 dark:text-slate-300">...</span></p>
                                </div>
                            </div>
                            <button id="btn-generate-roadmap" class="w-full md:w-auto mt-4 md:mt-0 px-6 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-xl font-bold hover:opacity-90 transition-opacity flex items-center justify-center gap-2 whitespace-nowrap">
                                <i class="fas fa-map-signs"></i> Generate Roadmap
                            </button>
                        </div>
                        


                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- H. ROADMAP SECTION (Full Width & Centered) -->
    <section id="roadmap-container" class="hidden max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 bg-white dark:bg-slate-900 rounded-3xl mt-12 shadow-sm border border-slate-100 dark:border-slate-800">
        <div class="mb-12 text-center">
            <h3 class="text-3xl font-black text-slate-900 dark:text-white flex items-center justify-center gap-3">
                <i class="fas fa-map-signs text-emerald-500"></i>
                Your Personalized Roadmap
            </h3>
            <p class="text-slate-500 dark:text-slate-400 mt-3 text-lg max-w-2xl mx-auto">Based on your simulation results, here are the step-by-step actions you need to reach your goals.</p>
        </div>

        <div id="roadmap-steps" class="relative space-y-8">
            <!-- Steps rendered by JS -->
        </div>
    </section>



        <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 pt-12 pb-32 mt-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center gap-6">
                <div class="text-center">
                    <h4 class="font-bold text-lg text-slate-900 dark:text-white mb-2">Cuan<span
                            class="text-emerald-600 dark:text-emerald-400">Capital</span>
                    </h4>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">© 2026 Fokus pada Eksekusi, Bukan Sekadar Ide.
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <a href="https://www.instagram.com/cuancapital.id?igsh=N2Vyb3d0cWpmMzJi" target="_blank"
                        rel="noopener noreferrer"
                        class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-400 transition-all duration-300 hover:scale-110 border-2 border-transparent hover:border-emerald-400 hover:shadow-[0_0_15px_rgba(52,211,153,0.6)] hover:bg-slate-900 group"
                        title="Instagram">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                    <a href="https://www.facebook.com/share/15XZMS8kJKT/" target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-400 transition-all duration-300 hover:scale-110 border-2 border-transparent hover:border-emerald-400 hover:shadow-[0_0_15px_rgba(52,211,153,0.6)] hover:bg-slate-900 group"
                        title="Facebook">
                        <i class="fab fa-facebook-f text-lg"></i>
                    </a>
                    <a href="https://www.tiktok.com/@cuan.capital.id?_r=1&_t=ZS-93o7k6jZzfu" target="_blank"
                        rel="noopener noreferrer"
                        class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-400 transition-all duration-300 hover:scale-110 border-2 border-transparent hover:border-emerald-400 hover:shadow-[0_0_15px_rgba(52,211,153,0.6)] hover:bg-slate-900 group"
                        title="TikTok">
                        <i class="fab fa-tiktok text-lg"></i>
                    </a>
                    <a href="mailto:team.cuancapital@gmail.com" target="_blank" rel="noopener noreferrer"
                        class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-emerald-400 transition-all duration-300 hover:scale-110 border-2 border-transparent hover:border-emerald-400 hover:shadow-[0_0_15px_rgba(52,211,153,0.6)] hover:bg-slate-900 group"
                        title="Email">
                        <i class="fas fa-envelope text-lg"></i>
                    </a>
                </div>

                <p class="text-xs text-slate-400 dark:text-slate-500">Made with 💚 by CuanCapital Team</p>
            </div>
        </footer>

    </main>





    </div>




    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>

    <button id="backToTop" aria-label="Back to Top" class="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script type="module" src="{{ asset('assets/js/main.js') }}"></script>
    <script>
        // Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('kamuad', () => {
                navigator.serviceWorker.register('{{ asset('sw.js') }}')
                    .then(registration => {
                        console.log('SW registered:', registration);
                    })
                    .catch(error => {
                        console.log('SW registration failed:', error);
                    });
            });
        }
    </script>
    <!-- Impersonation Exit Button -->
    <div id="exit-impersonation" class="fixed bottom-6 right-6 z-50 hidden">
        <div class="bg-rose-600 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 animate-pulse">
            <i class="fas fa-user-secret text-xl"></i>
            <div class="flex flex-col">
                <span class="text-[10px] uppercase font-bold text-rose-200">Viewing As</span>
                <span class="text-sm font-medium" id="imp-name">User</span>
            </div>
            <button onclick="exitImpersonation()"
                class="ml-2 bg-white text-rose-600 px-3 py-1 rounded-lg text-xs font-bold hover:bg-rose-50 transition">
                EXIT
            </button>
        </div>
    </div>
    <script type="module">
        import { logoutUser } from '/assets/js/core/auth-engine.js';
        
        // Logout logic moved to main.js with custom confirmation
    </script>
    <script type="module" src="{{ asset('assets/js/core/ad-arsenal-frontend.js') }}"></script>



    <script type="module" src="{{ asset('assets/js/features/mentor-lab.js') }}"></script>
    <script type="module" src="{{ asset('assets/js/features/roadmap-engine.js') }}"></script>
    <script src="{{ asset('assets/js/reverse-goal-planner.js') }}"></script>
    
    <!-- Profit Simulator Config -->
    <script>
        window.profitSimulatorConfig = {
            sessionId: "{{ session('reverse_goal_session_id') }}" // Or fetch from auth user latest
        };
    </script>
    <script src="{{ asset('assets/js/profit-simulator.js') }}"></script>

    <script>
        // Settings Dropdown Logic
        const settingsBtn = document.getElementById('settings-menu-btn');
        const settingsDropdown = document.getElementById('settings-dropdown');

        if (settingsBtn && settingsDropdown) {
            settingsBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                settingsDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!settingsBtn.contains(e.target) && !settingsDropdown.contains(e.target)) {
                    settingsDropdown.classList.add('hidden');
                }
            });
        }
    </script>
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-24 md:bottom-10 right-4 z-[9999] flex flex-col gap-3 w-full max-w-sm pointer-events-none"></div>

</body>

</html>