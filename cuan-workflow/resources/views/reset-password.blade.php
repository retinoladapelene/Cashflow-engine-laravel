<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CuanCapital - Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #0f172a;
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
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4 relative overflow-y-auto text-slate-200">

    <!-- Ambient Background Effects -->
    <div
        class="absolute top-[-20%] left-[-10%] w-[500px] h-[500px] bg-emerald-600/20 rounded-full blur-[120px] pointer-events-none animate-pulse">
    </div>
    <div
        class="absolute bottom-[-20%] right-[-10%] w-[500px] h-[500px] bg-teal-600/10 rounded-full blur-[120px] pointer-events-none animate-pulse delay-1000">
    </div>

    <div class="glass-panel w-full max-w-md rounded-3xl p-8 relative z-10 animate-fade-in-up">

        <!-- Logo & Header -->
        <div class="text-center mb-10">
            <div
                class="w-16 h-16 bg-emerald-500/10 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-emerald-500/20 shadow-[0_0_20px_rgba(16,185,129,0.2)]">
                <i class="fas fa-key text-3xl text-emerald-500"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight mb-2">Reset <span
                    class="text-emerald-500 neon-text">Password</span></h1>
            <p class="text-slate-400 text-sm">Create a new strong password for your account</p>
        </div>

        <!-- Initial Loading State -->
        <div id="verifying-state" class="text-center py-8">
            <div class="spinner mx-auto mb-4 w-8 h-8"></div>
            <p class="text-slate-400 text-sm">Verifying reset link...</p>
        </div>

        <!-- Error State -->
        <div id="error-state" class="hidden text-center py-8">
            <div class="w-16 h-16 bg-rose-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-times-circle text-3xl text-rose-500"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">Link Invalid or Expired</h3>
            <p class="text-slate-400 text-sm mb-6">This password reset link is invalid or has expired.</p>
            <a href="{{ route('login') }}"
                class="inline-block bg-slate-700 hover:bg-slate-600 text-white font-medium py-3 px-6 rounded-xl transition-all">
                Back to Login
            </a>
        </div>

        <!-- Reset Form -->
        <form id="reset-form" class="space-y-5 hidden">
            <!-- New Password -->
            <div>
                <label class="block text-xs font-bold uppercase text-emerald-500/80 mb-1 ml-1 tracking-wider">New
                    Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="password" id="input-new-pass" required
                        class="w-full input-glass rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-slate-500 focus:outline-none"
                        placeholder="••••••••">
                    <button type="button" id="toggle-new-pass"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-500 hover:text-white focus:outline-none">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div>
                <label class="block text-xs font-bold uppercase text-emerald-500/80 mb-1 ml-1 tracking-wider">Confirm
                    Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                    <input type="password" id="input-confirm-pass" required
                        class="w-full input-glass rounded-xl pl-11 pr-4 py-3.5 text-white placeholder-slate-500 focus:outline-none"
                        placeholder="••••••••">
                </div>
            </div>

            <!-- Strength Indicator -->
            <div class="space-y-2">
                <div class="flex justify-between text-xs text-slate-400">
                    <span>Password Strength:</span>
                    <span id="strength-text" class="font-bold">WEAK</span>
                </div>
                <div class="w-full bg-slate-700/50 rounded-full h-1.5 overflow-hidden">
                    <div id="strength-bar" class="h-full bg-rose-500 w-0 transition-all duration-300"></div>
                </div>
                <p id="password-match-msg" class="text-xs text-rose-400 hidden"><i
                        class="fas fa-exclamation-triangle mr-1"></i> Passwords do not match</p>
            </div>

            <button type="submit" id="btn-reset" disabled
                class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl transition-all neon-btn flex items-center justify-center gap-2 mt-6">
                <span>Update Password</span>
                <div class="spinner hidden" id="loading-spinner"></div>
            </button>
        </form>

    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-6 right-6 z-50 flex flex-col gap-3 pointer-events-none"></div>

    <script type="module" src="{{ asset('assets/js/core/reset-handler.js') }}"></script>
    <script>
        // Toggle Password View
        document.getElementById('toggle-new-pass').addEventListener('click', function () {
            const input = document.getElementById('input-new-pass');
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
    </script>
</body>

</html>