<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login - Klinik Lala Medicare</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>
<body class="h-full overflow-x-hidden antialiased">

<div class="flex min-h-screen w-full">
    
    <!-- Left Side: Image Banner (50% Width Desktop) -->
    <div class="hidden lg:flex lg:w-1/2 relative bg-slate-800">
        <img class="absolute inset-0 w-full h-full object-cover opacity-90" 
             src="https://images.unsplash.com/photo-1629909613654-28e377c37b09?q=80&w=1200&auto=format&fit=crop" 
             alt="Klinik Banner"/>
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/80 via-emerald-900/20 to-transparent"></div>
        
        <!-- Bottom Text -->
        <div class="absolute bottom-0 left-0 p-12 z-10 text-white space-y-2">
            <h2 class="text-3xl font-bold tracking-tight">Penjelasan Klinik</h2>
            <p class="text-sm opacity-90 max-w-md leading-relaxed">
               Layanan kesehatan profesional dengan pendekatan modern yang berpusat pada pasien, dirancang untuk generasi baru.
            </p>
        </div>
    </div>

    <!-- Right Side: Login Form (50% Width Desktop) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 bg-[#f7f9ff]">
        <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-6">
            
            <!-- Header Brand -->
            <div class="text-center space-y-1">
                <div class="inline-flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[#006c4f] text-3xl" style="font-variation-settings: 'FILL' 1;">medical_services</span>
                    <h1 class="text-2xl font-bold text-[#006c4f] tracking-tight">Klinik Lala Medicare</h1>
                </div>
                <p class="text-xs text-slate-500">Silakan masuk ke akun Anda</p>
            </div>

            <!-- Session Alert Status -->
            @if (session('status'))
                <div class="p-3 text-xs text-emerald-800 bg-emerald-50 rounded-lg border border-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Form Section -->
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-600" for="email">Username</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-slate-400 text-lg pointer-events-none">person</span>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#20c997] focus:ring-1 focus:ring-[#20c997] transition" 
                               placeholder="Enter your email"/>
                    </div>
                    @error('email')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Input -->
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-600" for="password">Password</label>
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-3 text-slate-400 text-lg pointer-events-none">lock</span>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#20c997] focus:ring-1 focus:ring-[#20c997] transition" 
                               placeholder="Enter your password"/>
                        <button type="button" 
                                onclick="togglePasswordVisibility()" 
                                class="absolute right-3 text-slate-400 hover:text-emerald-600 focus:outline-none">
                            <span class="material-symbols-outlined text-lg" id="eyeIcon">visibility</span>
                        </button>
                    </div>
                    @error('password')
                        <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                        <input type="checkbox" 
                               id="remember_me" 
                               name="remember" 
                               class="rounded border-slate-300 text-[#20c997] focus:ring-[#20c997]">
                        <span>Ingat Saya</span>
                    </label>
                    <button type="button" onclick="showPasswordHelp()" class="text-[#006c4f] font-semibold hover:underline">
                        Lupa Password?
                    </button>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-2.5 px-4 bg-[#20c997] hover:bg-[#006c4f] text-white font-semibold text-sm rounded-lg shadow-md hover:shadow-lg transition duration-200 cursor-pointer">
                    Login
                </button>
            </form>

        </div>
    </div>
</div>

<div id="passwordHelpModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 px-4" role="dialog" aria-modal="true" aria-labelledby="passwordHelpTitle">
    <div class="w-full max-w-sm rounded-2xl border border-emerald-100 bg-white p-6 text-center shadow-2xl shadow-slate-900/20">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
            <span class="material-symbols-outlined">support_agent</span>
        </div>
        <h2 id="passwordHelpTitle" class="mt-4 text-lg font-bold text-slate-800">Butuh password baru?</h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Silakan konfirmasi kepada staff Klinik Lala Medicare untuk mendapatkan password baru.
        </p>
        <button type="button" onclick="hidePasswordHelp()" class="mt-5 w-full rounded-lg bg-[#20c997] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#006c4f]">
            Mengerti
        </button>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            eyeIcon.textContent = 'visibility';
        }
    }

    function showPasswordHelp() {
        const modal = document.getElementById('passwordHelpModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function hidePasswordHelp() {
        const modal = document.getElementById('passwordHelpModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('passwordHelpModal').addEventListener('click', function (event) {
        if (event.target === this) {
            hidePasswordHelp();
        }
    });
</script>

</body>
</html>
