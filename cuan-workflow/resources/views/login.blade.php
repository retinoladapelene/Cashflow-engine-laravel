<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CuanCapital - Premium Access</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #0f172a;
        }

        .locked-screen {
            display: none !important;
        }

        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .input-glass {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(51, 65, 85, 0.5);
            transition: all 0.3s ease;
        }

        .input-glass:focus {
            background: rgba(15, 23, 42, 0.8);
            border-color: #10b981;
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
        }

        .neon-text {
            text-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
        }

        .neon-btn {
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            transition: all 0.3s ease;
        }

        .neon-btn:hover {
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.6);
            transform: translateY(-2px);
        }

        /* Loading Spinner */
        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.1);
            border-left-color: #10b981;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Error Utility Class */
        .input-error {
            border-color: #f43f5e !important;
            box-shadow: 0 0 15px rgba(244, 63, 94, 0.3) !important;
            animation: shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
        }

        @keyframes shake {

            10%,
            90% {
                transform: translate3d(-1px, 0, 0);
            }

            20%,
            80% {
                transform: translate3d(2px, 0, 0);
            }

            30%,
            50%,
            70% {
                transform: translate3d(-4px, 0, 0);
            }

            40%,
            60% {
                transform: translate3d(4px, 0, 0);
            }
        }
    </style>



</head>

<body class="min-h-screen flex items-center justify-center p-4 relative overflow-y-auto text-slate-200 locked">

    <!-- Ambient Background Effects -->
    <div
        class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-emerald-600/20 rounded-full blur-[120px] pointer-events-none animate-pulse">
    </div>
    <div
        class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] bg-teal-600/10 rounded-full blur-[120px] pointer-events-none animate-pulse delay-1000">
    </div>

    <div class="glass-panel w-full max-w-md rounded-3xl p-8 relative z-10 animate-fade-in-up">

        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('assets/icon/logo-darkmode.svg') }}" alt="CuanCapital"
                class="h-24 mx-auto mb-2 drop-shadow-[0_0_15px_rgba(16,185,129,0.3)]">
            <p class="text-slate-400 text-sm">Sign in to access your Cashflow Engine <span
                    class="text-xs opacity-50 border border-slate-700 rounded px-1 ml-1">V.12</span></p>
        </div>

        <!-- Login Form -->

        <!-- Login Form -->
        <form id="auth-form" class="space-y-5">
            <div id="username-field-container" class="hidden">
                <label
                    class="block text-xs font-bold uppercase text-emerald-500/80 mb-1 ml-1 tracking-wider">Username</label>
                <div class="relative">
                    <i class="fas fa-at absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="text" id="input-username"
                        class="w-full input-glass rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-slate-500 focus:outline-none"
                        placeholder="unique_username">
                    <span id="username-indicator" class="absolute right-4 top-1/2 -translate-y-1/2 text-xs"></span>
                </div>
            </div>

            <div id="name-field-container" class="hidden">
                <label class="block text-xs font-bold uppercase text-emerald-500/80 mb-1 ml-1 tracking-wider">Full
                    Name</label>
                <input type="text" id="input-name"
                    class="w-full input-glass rounded-xl px-4 py-3.5 text-white placeholder-slate-500 focus:outline-none"
                    placeholder="Enter your name">
            </div>

            <div id="whatsapp-field-container" class="hidden">
                <label class="block text-xs font-bold uppercase text-emerald-500/80 mb-1 ml-1 tracking-wider">WhatsApp
                    Number</label>
                <div class="relative">
                    <i class="fab fa-whatsapp absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="tel" id="input-whatsapp"
                        class="w-full input-glass rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-slate-500 focus:outline-none"
                        placeholder="08123456789">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-emerald-500/80 mb-1 ml-1 tracking-wider">Email
                    Address</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="email" id="input-email" required
                        class="w-full input-glass rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-slate-500 focus:outline-none"
                        placeholder="you@example.com">
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-1 ml-1">
                    <label class="block text-xs font-bold uppercase text-emerald-500/80 tracking-wider">Password</label>
                </div>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="password" id="input-password" required
                        class="w-full input-glass rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-slate-500 focus:outline-none"
                        placeholder="••••••••">
                    <button type="button" id="toggle-pass"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div id="confirm-password-container" class="hidden">
                <div class="flex justify-between items-center mb-1 ml-1">
                    <label class="block text-xs font-bold uppercase text-emerald-500/80 tracking-wider">Confirm
                        Password</label>
                </div>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="password" id="input-confirm-password"
                        class="w-full input-glass rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-slate-500 focus:outline-none"
                        placeholder="••••••••">
                    <button type="button" id="toggle-confirm-pass"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <p id="match-error-msg" class="text-xs text-rose-500 mt-1 hidden"><i
                        class="fas fa-exclamation-circle mr-1"></i> <span>Passwords do not match</span></p>
            </div>

            <div id="forgot-pass-container" class="flex justify-end mt-1">
                <a href="#" id="link-forgot-pass" class="text-xs text-emerald-400 hover:underline">Lupa Password?</a>
            </div>


            <button type="submit" id="btn-submit" disabled
                class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl transition-all neon-btn flex items-center justify-center gap-2">
                <span>Sign In</span>
                <div class="spinner hidden" id="loading-spinner"></div>
            </button>

            <div class="relative flex py-2 items-center my-4">
                <div class="flex-grow border-t border-slate-700"></div>
                <span class="flex-shrink-0 mx-4 text-slate-500 text-xs uppercase tracking-widest">ATAU</span>
                <div class="flex-grow border-t border-slate-700"></div>
            </div>

            <a href="{{ route('auth.google') }}"
                class="w-full bg-white/5 hover:bg-white/10 border border-slate-700 hover:border-slate-500 text-white font-medium py-3 rounded-xl transition-all flex items-center justify-center gap-3 group">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg"
                    class="w-5 h-5 group-hover:scale-110 transition-transform" alt="Google">
                <span>Masuk dengan Google</span>
            </a>
        </form>

        <div class="mt-8 text-center">
            <p class="text-slate-400 text-sm">
                <span id="text-switch">Don't have an account?</span>
                <a href="#" id="link-switch"
                    class="text-emerald-400 font-bold hover:text-emerald-300 ml-1 transition-colors">Create
                    An Account</a>
            </p>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-6 right-6 z-50 flex flex-col gap-3 pointer-events-none"></div>

    <!-- Forgot Password Modal -->
    <div id="modal-forgot"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-sm opacity-0 transition-opacity duration-300">
        <div class="glass-panel w-full max-w-sm rounded-3xl p-8 transform scale-95 transition-transform duration-300">
            <div class="text-center mb-6">
                <div
                    class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center mx-auto mb-3 border border-emerald-500/20">
                    <i class="fas fa-key text-xl text-emerald-500"></i>
                </div>
                <h3 class="text-xl font-bold text-white">Reset Password</h3>
                <p class="text-slate-400 text-xs mt-1">Enter your email to receive a reset link.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-emerald-500/80 mb-1 ml-1 tracking-wider">Email
                        Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="email" id="input-forgot-identity"
                            class="w-full input-glass rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-slate-500 focus:outline-none transition-all"
                            placeholder="name@example.com">
                    </div>
                    <p id="forgot-error-msg" class="text-xs text-rose-500 mt-1 hidden"><i
                            class="fas fa-exclamation-circle mr-1"></i> <span>User not found</span></p>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="btn-cancel-reset"
                        class="flex-1 bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 rounded-xl transition-all">
                        Cancel
                    </button>
                    <button type="button" id="btn-confirm-reset"
                        class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-medium py-3 rounded-xl transition-all neon-btn flex items-center justify-center gap-2">
                        <span>Send Link</span>
                        <div class="spinner hidden" id="spinner-reset"></div>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script type="module">
        import { initAuthListener, initLoginUI } from '/assets/js/core/auth-engine.js';

        document.addEventListener('DOMContentLoaded', () => {
            // Init Listener
            initAuthListener();
            // Init UI Logic
            initLoginUI();
        });
    </script>
    <script>
        // Toggle Password View
        function setupToggle(btnId, inputId) {
            const btn = document.getElementById(btnId);
            const input = document.getElementById(inputId);

            if (btn && input) {
                btn.addEventListener('click', function () {
                    const icon = this.querySelector('i');
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            }
        }

        setupToggle('toggle-pass', 'input-password');
        setupToggle('toggle-confirm-pass', 'input-confirm-password');
    </script>
</body>

</html>