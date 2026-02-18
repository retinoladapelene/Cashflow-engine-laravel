<!DOCTYPE html>
<html lang="id">

<head>
    <style>
        .locked {
            display: none !important;
        }
    </style>
    <script type="module">
        import { initAuthListener } from './assets/js/core/auth-engine.js';
        initAuthListener();
    </script>

    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
                Memuat Mesin Cuan...</p>
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
                        title="Panduan Tour">
                        <i class="fas fa-circle-question text-xl"></i>
                    </button>
                    <select id="currency-selector"
                        class="bg-slate-100 dark:bg-slate-800 border-none text-slate-900 dark:text-white text-xs md:text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2 cursor-pointer font-bold">
                        <option value="IDR">IDR (Rp)</option>
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                        <option value="MYR">MYR (RM)</option>
                        <option value="SGD">SGD (S$)</option>
                        <option value="AUD">AUD (A$)</option>
                        <option value="JPY">JPY (¥)</option>
                    </select>
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
                            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mb-0.5">Welcome back,</p>
                            <h3 id="greeting-text" class="text-xl font-bold text-slate-900 dark:text-white leading-none"></h3>
                        </div>
                    </div>

                    <div
                        class="inline-flex items-center px-4 py-1.5 rounded-full border border-emerald-100 bg-emerald-50 dark:bg-emerald-900/30 dark:border-emerald-800 backdrop-blur-sm">
                        <span class="flex h-2 w-2 rounded-full bg-emerald-500 mr-2 animate-pulse"></span>
                        <span
                            class="text-xs font-bold uppercase tracking-widest text-emerald-700 dark:text-emerald-400">Bukan
                            Sekadar Motivasi</span>
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
                        Berhenti menebak-nebak nasib bisnis Anda. Gunakan <strong>Framework CuanCapital</strong> yang
                        sudah teruji untuk membangun mesin uang yang <strong>Predictable</strong> &
                        <strong>Scalable</strong>. Tanpa Basa-basi.
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
        <section id="goal-planner"
            class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-3">
            <div class="p-8 lg:p-10 bg-slate-900 dark:bg-slate-950 text-white space-y-6">
                <div>
                    <h3 class="text-2xl font-bold">Reverse Goal Planner <br><span
                            class="text-emerald-400 text-sm font-mono tracking-widest uppercase">Precision
                            Edition</span></h3>
                    <p class="text-slate-400 text-sm mt-4 leading-relaxed">
                        Set Target. Biar Data yang Bicara. <br>
                        Breakdown traffic & sales yang wajib dikejar. <span class="text-emerald-400 font-bold">No
                            guessing.</span>
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-emerald-400 uppercase tracking-wider">Mau Cuan Berapa?
                            <span class="currency-label">(IDR)</span></label>

                        <div class="relative mt-1">
                            <input type="number" id="goal-income" placeholder="10000000"
                                class="w-full bg-white/10 backdrop-blur-sm border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 font-mono text-lg font-bold transition-all">
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500 pointer-events-none currency-label">IDR</span>
                        </div>

                    </div>
                    <div>
                        <label class="text-[10px] font-black text-emerald-400 uppercase tracking-wider">Harga Produk
                            Kamu
                            <span class="currency-label">(IDR)</span></label>

                        <div class="relative mt-1">
                            <input type="number" id="goal-price" placeholder="200000"
                                class="w-full bg-white/10 backdrop-blur-sm border border-slate-700 rounded-xl px-4 py-3 text-white placeholder-slate-500 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 font-mono text-lg font-bold transition-all">
                            <span
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-500 pointer-events-none currency-label">IDR</span>
                        </div>

                    </div>
                    <button id="btn-calculate-goal"
                        class="w-full bg-emerald-600 hover:bg-emerald-500 py-4 rounded-xl font-bold transition shadow-lg shadow-emerald-900/50 hover:shadow-emerald-500/20 active:scale-95 text-sm tracking-wide uppercase">
                        Generate Blueprint Saya
                    </button>
                </div>
            </div>

            <div
                class="col-span-2 p-8 lg:p-10 grid grid-cols-1 md:grid-cols-2 gap-8 items-center bg-emerald-50/30 dark:bg-slate-800/50">
                <div class="space-y-6">
                    <div
                        class="p-4 bg-white dark:bg-slate-900 border border-emerald-100 dark:border-slate-700 rounded-2xl shadow-sm">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-wider">Target Sales Wajib</p>
                        <div id="goal-qty" class="text-3xl font-black text-slate-900 dark:text-white mt-1">50 Unit</div>
                        <p class="text-emerald-600 dark:text-emerald-400 text-[10px] font-bold mt-1">Harus closing
                            segini.</p>
                    </div>

                    <div
                        class="p-4 bg-white dark:bg-slate-900 border border-emerald-100 dark:border-slate-700 rounded-2xl shadow-sm">
                        <p class="text-slate-400 text-[10px] font-black uppercase tracking-wider">Traffic Minimal</p>
                        <div id="goal-traffic" class="text-3xl font-black text-slate-900 dark:text-white mt-1">5.000
                            Visitor</div>
                        <p class="text-emerald-600 dark:text-emerald-400 text-[10px] font-bold mt-1">Jangan komplain
                            sepi kalo traffic belum
                            segini.</p>
                    </div>

                    <div class="p-4 bg-emerald-600 text-white rounded-2xl shadow-md border-t-4 border-emerald-400">
                        <p class="text-emerald-100 text-[10px] font-black uppercase tracking-wider">Focus on This ONE
                        <p id="goal-priority" class="text-sm font-bold mt-2 leading-relaxed">
                            Manfaatkan Strategi Affiliate untuk mendapatkan traffic tersebut dengan cepat!
                        </p>
                    </div>
                </div>

                <div class="chart-container">
                    <canvas id="goalFunnelChart"></canvas>
                </div>
            </div>
        </section>

        <section id="calculator-section"
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">

                <div class="space-y-6">
                    <div>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white">Simulasi Profit</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">Mainkan angka, temukan
                            <span class="font-bold text-slate-700 dark:text-slate-200">potensi profitmu.</span>
                        </p>
                    </div>

                    <div class="space-y-8">

                        <div class="relative group">
                            <div
                                class="absolute -left-4 top-0 bottom-0 w-1 bg-gradient-to-b from-blue-500 to-transparent rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <h4
                                class="text-sm font-black text-blue-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-funnel-dollar"></i> Efisiensi Penjualan
                            </h4>

                            <div class="space-y-4">

                                <div
                                    class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700/50 hover:border-blue-500/30 transition-colors">
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-[10px] font-black text-white uppercase tracking-widest">Harga
                                            Jual</label>
                                        <span id="price-val" class="text-sm font-bold text-white">Rp 150.000</span>
                                    </div>
                                    <input type="number" id="price-input" min="0" step="1000" placeholder="150000"
                                        class="w-full bg-white/5 border border-slate-700 rounded-lg px-4 py-3 text-sm font-bold text-white focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all font-mono">
                                    <p class="text-[10px] text-white mt-2 italic"><i
                                            class="fas fa-lightbulb text-yellow-500 mr-1"></i>Tip: Naikkan harga
                                        jika
                                        value produkmu tinggi dan brand kuat.</p>
                                </div>

                                <div
                                    class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700/50 hover:border-blue-500/30 transition-colors">
                                    <div class="flex justify-between items-center mb-2">
                                        <label
                                            class="text-[10px] font-black text-white uppercase tracking-widest">Conversion
                                            Rate</label>
                                        <span id="conv-val" class="text-sm font-bold text-white">2.0%</span>
                                    </div>
                                    <input type="range" id="conv-input" min="0.1" max="10" step="0.1" value="2.0"
                                        class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-500 hover:accent-blue-400 transition-all">
                                    <p class="text-[10px] text-slate-400 mt-2 italic flex flex-col gap-1">
                                        <span class="flex justify-between w-full opacity-50">
                                            <span><i class="fas fa-arrow-down text-rose-500 mr-1"></i>0.1%</span>
                                            <span>10%<i class="fas fa-arrow-up text-emerald-500 ml-1"></i></span>
                                        </span>
                                        <span class="text-white"><i
                                                class="fas fa-lightbulb text-yellow-500 mr-1"></i>Tip: Perbaiki
                                            Copywriting & Offer untuk menaikkan konversi.</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="relative group">
                            <div
                                class="absolute -left-4 top-0 bottom-0 w-1 bg-gradient-to-b from-rose-500 to-transparent rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                            </div>
                            <h4
                                class="text-sm font-black text-rose-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <i class="fas fa-bullhorn"></i> Biaya Pemasaran
                            </h4>

                            <div class="space-y-4">

                                <div
                                    class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700/50 hover:border-rose-500/30 transition-colors">
                                    <div class="flex justify-between items-center mb-2">
                                        <label
                                            class="text-[10px] font-black text-white uppercase tracking-widest">Traffic
                                            / Visitor</label>
                                        <span id="traffic-val" class="text-sm font-bold text-white">5.000</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <input type="range" id="traffic-input" min="100" max="100000" step="100"
                                            value="1000"
                                            class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-rose-500 hover:accent-rose-400 transition-all mt-2">
                                        <input type="number" id="traffic-manual" placeholder="1000" min="0"
                                            class="w-24 bg-white/5 border border-slate-700 rounded-lg px-2 py-1 text-xs font-bold text-white focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-all font-mono">
                                    </div>
                                    <p class="text-[10px] text-white mt-2 italic"><i
                                            class="fas fa-lightbulb text-yellow-500 mr-1"></i>Tip: Gunakan konten
                                        viral
                                        (Organic) atau iklan (Paid) untuk mendatangkan pengunjung.</p>
                                </div>

                                <div
                                    class="bg-slate-900/50 p-4 rounded-2xl border border-slate-700/50 hover:border-rose-500/30 transition-colors">
                                    <div class="flex justify-between items-center mb-2">
                                        <label class="text-[10px] font-black text-white uppercase tracking-widest">Ad
                                            Spend (Budget Iklan)</label>
                                        <span id="ad-spend-val" class="text-sm font-bold text-white">Rp 0</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <input type="range" id="ad-spend-input" min="0" max="100000000" step="50000"
                                            value="0"
                                            class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-rose-500 hover:accent-rose-400 transition-all mt-2">
                                        <input type="number" id="ad-spend-manual" placeholder="0" min="0"
                                            class="w-24 bg-white/5 border border-slate-700 rounded-lg px-2 py-1 text-xs font-bold text-white focus:outline-none focus:border-rose-500 focus:ring-1 focus:ring-rose-500 transition-all font-mono">
                                    </div>
                                    <p class="text-[10px] text-white mt-2 italic"><i
                                            class="fas fa-lightbulb text-yellow-500 mr-1"></i>Tip: Mulai budget
                                        kecil,
                                        scale up pelan-pelan. Budget tinggi = Lebih banyak traffic.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div
                    class="relative bg-slate-900 dark:bg-slate-950 rounded-3xl p-6 md:p-8 text-white shadow-2xl shadow-emerald-900/20 border border-slate-800 flex flex-col justify-between h-full">

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-6">
                        <div class="w-full sm:w-auto">
                            <p class="text-emerald-400 text-[10px] font-black uppercase tracking-widest mb-1">Total
                                Profit Bersih (Bulan Ini)</p>
                            <h3 id="total-revenue"
                                class="text-xl sm:text-2xl md:text-3xl font-black tracking-tight text-white break-words">
                                IDR
                                3.000.000<span
                                    class="currency-label text-sm sm:text-base font-medium opacity-50 ml-1">(IDR)</span>
                            </h3>
                            <p id="revenue-terbilang"
                                class="text-[10px] sm:text-xs text-slate-400 font-medium mt-1 uppercase tracking-wide">
                                Tiga Juta Rupiah
                            </p>
                        </div>
                        <div class="text-left sm:text-right w-full sm:w-auto">
                            <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest mb-1">Sales</p>
                            <p id="total-sales" class="text-lg sm:text-xl font-bold text-white">20 Unit</p>
                        </div>
                    </div>

                    <div class="bg-amber-900/20 p-4 rounded-xl border border-amber-500/30 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-amber-200 text-[10px] font-black uppercase tracking-wider mb-1">✨
                                    Magic
                                    Number</p>
                                <p id="magic-number" class="text-xl sm:text-2xl font-black text-amber-400">IDR
                                    6.000.000
                                </p>
                                <p class="text-amber-200/70 text-[9px] mt-1">Revenue opportunity dengan asumsi
                                    konversi
                                    4% </p>
                            </div>

                        </div>
                    </div>

                    <div class="relative w-full h-64 bg-slate-800/50 rounded-xl border border-slate-700/50 p-2 mb-4">
                        <canvas id="profitChart"></canvas>
                    </div>

                    <div class="bg-emerald-900/30 p-4 rounded-xl border border-emerald-500/20">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                            <div class="w-full sm:w-auto">
                                <p class="text-[10px] text-emerald-200 font-bold uppercase tracking-wider">Proyeksi
                                    1
                                    Tahun (Akumulasi)</p>
                                <p id="yearly-profit"
                                    class="text-base sm:text-lg font-black text-white mt-1 break-words">IDR
                                    36.000.000<span
                                        class="currency-label text-xs sm:text-sm font-medium opacity-50 ml-1">(IDR)</span>
                                </p>
                                <p id="yearly-terbilang"
                                    class="text-[9px] sm:text-[10px] text-emerald-200/80 font-medium mt-1 uppercase tracking-wide">
                                    Tiga Puluh Enam Juta Rupiah</p>
                            </div>
                            <div class="text-left sm:text-right w-full sm:w-auto">
                                <span id="profit-growth-label"
                                    class="inline-block text-[10px] sm:text-xs font-bold text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded whitespace-nowrap">+Stable
                                    Growth</span>
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
                <span class="text-emerald-600 dark:text-emerald-400 font-bold">Jangan Perang Tanpa Senjata.</span>
                Lengkapi strategi Anda dengan tools premium ini untuk <span
                    class="text-slate-900 dark:text-white font-bold">akselerasi profit 2x lebih cepat</span>.
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
                    Uji potensi bisnis Anda sebelum bakar uang. Optimasi strategi yang sudah jalan atau rencanakan bisnis baru dengan data.
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
                                <p class="text-xs text-emerald-600 dark:text-emerald-500">Masukkan data real dari bisnis Anda saat ini untuk diagnosa bottleneck.</p>
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
                                <p class="text-xs text-blue-600 dark:text-blue-500">Gunakan benchmark industri untuk simulasi bisnis baru Anda.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tipe Bisnis</label>
                                <select id="planner-type" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg px-4 py-2.5 focus:ring-blue-500">
                                    <option value="digital">Produk Digital (E-course/Software)</option>
                                    <option value="physical">Produk Fisik (Fashion/F&B)</option>
                                    <option value="service">Jasa / Agency</option>
                                </select>
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
                                        <input type="number" id="planner-conversion" disabled class="w-full bg-slate-100 dark:bg-slate-800 border-transparent rounded px-3 py-1.5 text-sm font-mono text-slate-600 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 mb-1">Est. Margin (%)</label>
                                        <input type="number" id="planner-margin" disabled class="w-full bg-slate-100 dark:bg-slate-800 border-transparent rounded px-3 py-1.5 text-sm font-mono text-slate-600 cursor-not-allowed">
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
                        <h3 class="text-xl font-bold text-slate-800 dark:text-white animate-pulse mb-2" id="loading-text">Analyzing Business Structure...</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Please wait while we calculate your potential.</p>
                    </div>

                    <div id="mentor-dashboard" class="space-y-6 hidden">
                        
                        <!-- A. BASELINE SUMMARY -->
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm relative overflow-hidden">
                            <div class="absolute top-0 right-0 p-4 opacity-10">
                                <i class="fas fa-chart-line text-9xl text-slate-900 dark:text-white"></i>
                            </div>
                            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-widest mb-4">Financial Baseline</h3>
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
                            <div class="grid grid-cols-3 gap-2 sm:gap-4 text-center">
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
                        
                        <!-- G. DYNAMIC ROADMAP (Hidden by Default) -->
                        <div id="roadmap-container" class="hidden mt-8 border-t border-slate-200 dark:border-slate-700 pt-8">
                            <div class="text-center mb-8">
                                <h3 class="text-2xl font-black text-slate-900 dark:text-white">Your Personalized Roadmap</h3>
                                <p class="text-slate-500 dark:text-slate-400">Step-by-step actions to reach your goals.</p>
                            </div>
                            
                            <!-- Roadmap Steps Container -->
                            <div id="roadmap-steps" class="max-w-3xl mx-auto space-y-0 relative">
                                <!-- Vertical Connector Line (Absolute) -->
                                <div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-slate-200 dark:bg-slate-700 -ml-px hidden md:block group-hover:bg-emerald-500 transition-colors"></div>
                                
                                <!-- Steps will be injected here via JS -->
                            </div>
                        </div>

                    </div>
                </div>
            </div>
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
            window.addEventListener('load', () => {
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
</body>

</html>