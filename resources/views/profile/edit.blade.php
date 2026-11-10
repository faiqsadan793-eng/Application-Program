@php $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Pengguna - Klinik Lala Medicare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    @include('layouts.skeleton')
    <div class="flex min-h-screen">
        @include('layouts.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="min-h-20 bg-white border-b border-slate-200 px-3 py-3 sm:px-6 flex items-center justify-between gap-3">
                @include('layouts.header-page-info', ['breadcrumb' => 'Klinik Lala Medicare / Akun', 'title' => 'Pengaturan Profil & Kata Sandi'])
                <div class="flex items-center gap-4">
                    @include('layouts.header-date')
                    <a href="{{ route('profile.edit') }}" title="Profil & Ganti Kata Sandi" class="flex items-center gap-3 group transition">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-semibold text-slate-900 group-hover:text-emerald-700 transition">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ ucfirst($user->role ?? 'Staff') }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-emerald-400 bg-emerald-100 text-emerald-700 font-semibold shadow-sm transition">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-3 sm:p-6">
                <div class="max-w-[900px] mx-auto space-y-6">
                    <div class="mb-4">
                        <h2 class="text-xl sm:text-2xl font-bold">Profil & Keamanan Akun</h2>
                        <p class="text-sm text-slate-500 mt-1">Perbarui nama pengguna, email, atau ubah kata sandi login Anda.</p>
                    </div>

                    {{-- Form Informasi Profil --}}
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 sm:p-8">
                        <div class="max-w-xl">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    {{-- Form Ganti Password --}}
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 sm:p-8">
                        <div class="max-w-xl">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
