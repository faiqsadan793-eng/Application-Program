<!-- Global Skeleton Loading Screen (Professional Shimmer Effect) -->
<div id="page-skeleton-overlay" class="fixed inset-0 z-[100] flex bg-slate-100 transition-opacity duration-300 pointer-events-none" style="opacity: 1;">
    <!-- Skeleton Sidebar (Ukuran dan posisi 100% presisi dengan sidebar asli) -->
    <div class="hidden md:flex w-64 min-w-[16rem] max-w-[16rem] flex-col border-r border-slate-100 bg-white shrink-0 select-none">
        <!-- Logo + Brand skeleton -->
        <div class="border-b border-slate-100 px-5 py-4 flex items-center gap-2.5">
            <div class="h-8 w-8 rounded-lg bg-emerald-100/70 animate-pulse"></div>
            <div class="space-y-1.5 flex-1">
                <div class="h-3.5 w-24 rounded bg-slate-200 animate-pulse"></div>
                <div class="h-2.5 w-16 rounded bg-slate-100 animate-pulse"></div>
            </div>
        </div>

        <!-- Navigation items skeleton -->
        <div class="flex-1 space-y-2 px-3 py-5">
            <div class="h-11 w-full rounded-xl bg-emerald-100/60 animate-pulse"></div>
            <div class="h-11 w-full rounded-xl bg-slate-100/80 animate-pulse"></div>
            <div class="h-11 w-full rounded-xl bg-slate-100/80 animate-pulse"></div>
            <div class="h-11 w-full rounded-xl bg-slate-100/80 animate-pulse"></div>
            <div class="h-11 w-full rounded-xl bg-slate-100/80 animate-pulse"></div>
            <div class="h-11 w-full rounded-xl bg-slate-100/80 animate-pulse"></div>
        </div>

        <!-- Logout button skeleton -->
        <div class="px-4 py-5 border-t border-slate-200">
            <div class="h-11 w-full rounded-2xl bg-red-50 animate-pulse"></div>
        </div>
    </div>

    <!-- Skeleton Main Area -->
    <div class="flex flex-1 flex-col overflow-hidden">
        <!-- Skeleton Header (min-h-20) -->
        <header class="min-h-20 border-b border-slate-200 bg-white px-6 flex items-center justify-between shrink-0">
            <div class="space-y-2">
                <div class="h-3 w-40 rounded bg-slate-100 animate-pulse"></div>
                <div class="h-5 w-48 rounded bg-slate-200 animate-pulse"></div>
            </div>
            <div class="flex items-center gap-3">
                <div class="h-9 w-32 rounded-lg bg-slate-100 animate-pulse hidden sm:block"></div>
                <div class="h-9 w-9 rounded-full bg-emerald-100 animate-pulse"></div>
            </div>
        </header>

        <!-- Skeleton Content Body -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
            <!-- Top Metric Cards Row (4 cards) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-24 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-9 w-9 rounded-xl bg-emerald-50 animate-pulse"></div>
                    </div>
                    <div class="h-8 w-16 rounded bg-slate-200 animate-pulse"></div>
                    <div class="h-3 w-32 rounded bg-slate-100 animate-pulse"></div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-28 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-9 w-9 rounded-xl bg-blue-50 animate-pulse"></div>
                    </div>
                    <div class="h-8 w-16 rounded bg-slate-200 animate-pulse"></div>
                    <div class="h-3 w-28 rounded bg-slate-100 animate-pulse"></div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-28 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-9 w-9 rounded-xl bg-amber-50 animate-pulse"></div>
                    </div>
                    <div class="h-8 w-16 rounded bg-slate-200 animate-pulse"></div>
                    <div class="h-3 w-24 rounded bg-slate-100 animate-pulse"></div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-28 rounded bg-slate-200 animate-pulse"></div>
                        <div class="h-9 w-9 rounded-xl bg-purple-50 animate-pulse"></div>
                    </div>
                    <div class="h-8 w-16 rounded bg-slate-200 animate-pulse"></div>
                    <div class="h-3 w-28 rounded bg-slate-100 animate-pulse"></div>
                </div>
            </div>

            <!-- Big Main Content Card / Table Skeleton -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="h-10 w-full sm:w-72 rounded-xl bg-slate-100 animate-pulse"></div>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto">
                        <div class="h-10 w-28 rounded-xl bg-slate-100 animate-pulse"></div>
                        <div class="h-10 w-36 rounded-xl bg-emerald-100/70 animate-pulse"></div>
                    </div>
                </div>

                <div class="space-y-3.5">
                    <div class="h-11 rounded-lg bg-slate-100/80 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50/70 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50/70 animate-pulse"></div>
                </div>
            </div>
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
            }, 300);
        }

        // Tunggu sampai font dan gambar dasar siap
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(function() {
                setTimeout(hideSkeleton, 180);
            });
        } else {
            window.addEventListener('load', function() {
                setTimeout(hideSkeleton, 180);
            });
        }

        // Fallback maksimal 1.2 detik agar tidak pernah macet
        setTimeout(hideSkeleton, 1200);
    })();
</script>
