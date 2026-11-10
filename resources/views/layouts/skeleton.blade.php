<!-- Global Skeleton Loading Screen (Dynamic Per-Feature Layout) -->
@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $userRole = Auth::check() ? Auth::user()->role : 'staff';
@endphp

<div id="page-skeleton-overlay" class="fixed inset-0 z-[100] flex bg-slate-100 transition-opacity duration-300 pointer-events-none" style="opacity: 1;">
    <!-- Skeleton Sidebar: Ukuran dan posisi presisi dengan sidebar asli -->
    <div class="hidden md:flex w-64 min-w-[16rem] max-w-[16rem] flex-col border-r border-slate-100 bg-white shrink-0 select-none">
        <!-- Logo + Brand -->
        <div class="border-b border-slate-100 px-5 py-4 flex items-center gap-2.5">
            <div class="h-8 w-8 rounded-lg bg-emerald-100/70 animate-pulse"></div>
            <div class="space-y-1.5 flex-1">
                <div class="h-3.5 w-24 rounded bg-slate-200 animate-pulse"></div>
                <div class="h-2.5 w-16 rounded bg-slate-100 animate-pulse"></div>
            </div>
        </div>

        <!-- Navigation Menu Items (Disesuaikan dengan Role) -->
        <div class="flex-1 space-y-2 px-3 py-5">
            @if($userRole === 'dokter')
                {{-- Dokter Menu: Dashboard, Periksa Pasien, Rekam Medis Pasien, Profil --}}
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'dashboard') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'rekam-medis.') && !str_contains($currentRoute, 'rekam-medis-pasien') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'rekam-medis-pasien') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="pt-2 mt-2 border-t border-slate-100">
                    <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'profile') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                </div>
            @else
                {{-- Staff Menu: Dashboard, Dokter, Pasien, Kunjungan, Transaksi, Riwayat Transaksi, Profil --}}
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'dashboard') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'dokter') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'pasien') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'kunjungan') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'transaksi.') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'riwayat-transaksi') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                <div class="pt-2 mt-2 border-t border-slate-100">
                    <div class="h-11 w-full rounded-xl {{ str_contains($currentRoute, 'profile') ? 'bg-emerald-600/30' : 'bg-slate-100/80' }} animate-pulse"></div>
                </div>
            @endif
        </div>

        <!-- Tombol Logout -->
        <div class="px-4 py-5 border-t border-slate-200">
            <div class="h-11 w-full rounded-2xl bg-red-50 animate-pulse"></div>
        </div>
    </div>

    <!-- Skeleton Main Area -->
    <div class="flex flex-1 flex-col overflow-hidden">
        <!-- Header Skeleton (min-h-20) -->
        <header class="min-h-20 border-b border-slate-200 bg-white px-6 flex items-center justify-between shrink-0">
            <div class="space-y-2">
                <div class="h-3 w-40 rounded bg-slate-100 animate-pulse"></div>
                <div class="h-5 w-52 rounded bg-slate-200 animate-pulse"></div>
            </div>
            <div class="flex items-center gap-3">
                <div class="h-9 w-32 rounded-lg bg-slate-100 animate-pulse hidden sm:block"></div>
                <div class="h-9 w-9 rounded-full bg-emerald-100 animate-pulse"></div>
            </div>
        </header>

        <!-- Dynamic Body Content Skeleton per Route -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
            
            {{-- 1. LAYOUT SKELETON: DASHBOARD (Staff & Dokter) --}}
            @if(str_contains($currentRoute, 'dashboard'))
                <!-- Welcome Banner Skeleton -->
                <div class="rounded-3xl bg-emerald-700/20 p-6 sm:p-8 space-y-3 border border-emerald-600/20">
                    <div class="h-4 w-32 rounded bg-emerald-700/30 animate-pulse"></div>
                    <div class="h-8 w-64 rounded bg-emerald-700/40 animate-pulse"></div>
                    <div class="h-3.5 w-80 rounded bg-emerald-700/20 animate-pulse"></div>
                </div>

                <!-- 3 / 4 Cards Row -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="h-4 w-24 rounded bg-slate-200 animate-pulse"></div>
                            <div class="h-9 w-9 rounded-xl bg-emerald-50 animate-pulse"></div>
                        </div>
                        <div class="h-8 w-16 rounded bg-slate-200 animate-pulse"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="h-4 w-24 rounded bg-slate-200 animate-pulse"></div>
                            <div class="h-9 w-9 rounded-xl bg-blue-50 animate-pulse"></div>
                        </div>
                        <div class="h-8 w-16 rounded bg-slate-200 animate-pulse"></div>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="h-4 w-24 rounded bg-slate-200 animate-pulse"></div>
                            <div class="h-9 w-9 rounded-xl bg-amber-50 animate-pulse"></div>
                        </div>
                        <div class="h-8 w-16 rounded bg-slate-200 animate-pulse"></div>
                    </div>
                </div>

                <!-- Antrean Table Card -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <div class="h-5 w-44 rounded bg-slate-200 animate-pulse"></div>
                    <div class="h-10 rounded-lg bg-slate-100 animate-pulse"></div>
                    <div class="h-12 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-12 rounded-lg bg-slate-50/70 animate-pulse"></div>
                </div>

            {{-- 2. LAYOUT SKELETON: PASIEN (Tanpa Card, tapi ada Tabs & Filter Pencarian) --}}
            @elseif(str_contains($currentRoute, 'pasien'))
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-2">
                    <div class="space-y-2">
                        <div class="h-7 w-64 rounded-lg bg-slate-200 animate-pulse"></div>
                        <div class="h-4 w-96 rounded bg-slate-100 animate-pulse"></div>
                    </div>
                    <!-- Tab toggle pill -->
                    <div class="h-10 w-64 rounded-full bg-slate-200 animate-pulse"></div>
                </div>

                <!-- Search Input Bar -->
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="h-11 flex-1 rounded-xl bg-slate-100 animate-pulse"></div>
                        <div class="h-11 w-32 rounded-xl bg-emerald-600/30 animate-pulse"></div>
                    </div>
                </div>

                <!-- Table Pasien -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden space-y-3 p-5">
                    <div class="h-11 rounded-lg bg-slate-100 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50/80 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50/80 animate-pulse"></div>
                </div>

            {{-- 3. LAYOUT SKELETON: TRANSAKSI & KASIR --}}
            @elseif(str_contains($currentRoute, 'transaksi'))
                <div class="space-y-2">
                    <div class="h-7 w-52 rounded-lg bg-slate-200 animate-pulse"></div>
                    <div class="h-4 w-80 rounded bg-slate-100 animate-pulse"></div>
                </div>

                <!-- Big Kasir Table Card -->
                <div class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                        <div class="h-6 w-48 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-7 w-28 rounded-full bg-orange-100 animate-pulse"></div>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="h-11 rounded-lg bg-slate-100 animate-pulse"></div>
                        <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                        <div class="h-14 rounded-lg bg-slate-50/80 animate-pulse"></div>
                        <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    </div>
                </div>

            {{-- 4. LAYOUT SKELETON: REKAM MEDIS & PERIKSA DOKTER --}}
            @elseif(str_contains($currentRoute, 'rekam-medis'))
                <div class="space-y-2">
                    <div class="h-7 w-56 rounded-lg bg-slate-200 animate-pulse"></div>
                    <div class="h-4 w-72 rounded bg-slate-100 animate-pulse"></div>
                </div>

                <!-- Antrean Cards Dokter (Stacked Cards Layout) -->
                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="h-6 w-16 rounded bg-emerald-100 animate-pulse"></div>
                                <div class="h-5 w-40 rounded bg-slate-200 animate-pulse"></div>
                            </div>
                            <div class="h-6 w-24 rounded-full bg-yellow-100 animate-pulse"></div>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="h-10 rounded-lg bg-slate-100 animate-pulse"></div>
                            <div class="h-10 rounded-lg bg-slate-100 animate-pulse"></div>
                            <div class="h-10 rounded-lg bg-slate-100 animate-pulse"></div>
                            <div class="h-10 rounded-lg bg-slate-100 animate-pulse"></div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden p-5 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="h-6 w-16 rounded bg-emerald-100 animate-pulse"></div>
                                <div class="h-5 w-40 rounded bg-slate-200 animate-pulse"></div>
                            </div>
                            <div class="h-6 w-24 rounded-full bg-yellow-100 animate-pulse"></div>
                        </div>
                    </div>
                </div>

            {{-- 5. LAYOUT SKELETON: PROFIL & KEAMANAN AKUN (2 Form Cards) --}}
            @elseif(str_contains($currentRoute, 'profile'))
                <div class="space-y-2">
                    <div class="h-7 w-60 rounded-lg bg-slate-200 animate-pulse"></div>
                    <div class="h-4 w-96 rounded bg-slate-100 animate-pulse"></div>
                </div>

                <!-- Form Card 1: Profil -->
                <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-5 max-w-2xl">
                    <div class="h-6 w-44 rounded bg-slate-200 animate-pulse"></div>
                    <div class="space-y-2">
                        <div class="h-3.5 w-24 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-11 w-full rounded-xl bg-slate-100 animate-pulse"></div>
                    </div>
                    <div class="space-y-2">
                        <div class="h-3.5 w-24 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-11 w-full rounded-xl bg-slate-100 animate-pulse"></div>
                    </div>
                    <div class="h-10 w-28 rounded-xl bg-emerald-600/30 animate-pulse"></div>
                </div>

                <!-- Form Card 2: Password -->
                <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm space-y-5 max-w-2xl">
                    <div class="h-6 w-44 rounded bg-slate-200 animate-pulse"></div>
                    <div class="space-y-2">
                        <div class="h-3.5 w-32 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-11 w-full rounded-xl bg-slate-100 animate-pulse"></div>
                    </div>
                    <div class="space-y-2">
                        <div class="h-3.5 w-32 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-11 w-full rounded-xl bg-slate-100 animate-pulse"></div>
                    </div>
                    <div class="h-10 w-36 rounded-xl bg-emerald-600/30 animate-pulse"></div>
                </div>

            {{-- 6. DEFAULT SKELETON: RIWAYAT KUNJUNGAN / MASTER DOKTER / LAINNYA --}}
            @else
                <div class="flex items-center justify-between pb-2">
                    <div class="space-y-2">
                        <div class="h-7 w-60 rounded-lg bg-slate-200 animate-pulse"></div>
                        <div class="h-4 w-80 rounded bg-slate-100 animate-pulse"></div>
                    </div>
                    <div class="h-10 w-36 rounded-xl bg-emerald-600/30 animate-pulse"></div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
                    <div class="h-11 rounded-lg bg-slate-100 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50/80 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50/80 animate-pulse"></div>
                </div>
            @endif

        </main>
    </div>
</div>

<script>
    (function() {
        var overlay = document.getElementById('page-skeleton-overlay');
        if (!overlay) return;

        function hideSkeleton() {
            if (!overlay) return;
            overlay.style.opacity = '0';
            setTimeout(function() {
                if (overlay && overlay.parentNode) {
                    overlay.parentNode.removeChild(overlay);
                    overlay = null;
                }
            }, 250);
        }

        // Tunggu hingga fonts & layout browser sudah siap
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function() {
                setTimeout(hideSkeleton, 150);
            });
        } else {
            window.addEventListener('load', function() {
                setTimeout(hideSkeleton, 150);
            });
        }

        // Fallback keselamatan maksimal 1 detik
        setTimeout(hideSkeleton, 1000);
    })();
</script>
