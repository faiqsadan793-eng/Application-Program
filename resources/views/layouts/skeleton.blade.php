<!-- Global Skeleton Loading Screen (Professional Shimmer Effect) -->
<div id="page-skeleton-overlay" class="fixed inset-0 z-50 flex bg-slate-100 transition-opacity duration-300 pointer-events-none" style="opacity: 1;">
    <!-- Skeleton Sidebar (matches sidebar.blade.php width) -->
    <div class="hidden md:flex w-64 flex-col border-r border-slate-200 bg-white p-5 shrink-0 select-none">
        <!-- Logo + Brand skeleton -->
        <div class="flex items-center gap-3 pb-6 border-b border-slate-100">
            <div class="h-10 w-10 rounded-xl bg-slate-200 animate-pulse"></div>
            <div class="space-y-2 flex-1">
                <div class="h-4 w-32 rounded bg-slate-200 animate-pulse"></div>
                <div class="h-3 w-20 rounded bg-slate-150 bg-slate-100 animate-pulse"></div>
            </div>
        </div>

        <!-- Navigation items skeleton -->
        <div class="mt-6 flex-1 space-y-3">
            <div class="h-10 w-full rounded-xl bg-emerald-100/60 animate-pulse"></div>
            <div class="h-10 w-full rounded-xl bg-slate-100 animate-pulse"></div>
            <div class="h-10 w-full rounded-xl bg-slate-100 animate-pulse"></div>
            <div class="h-10 w-full rounded-xl bg-slate-100 animate-pulse"></div>
            <div class="h-10 w-full rounded-xl bg-slate-100 animate-pulse"></div>
            <div class="h-10 w-full rounded-xl bg-slate-100 animate-pulse"></div>
        </div>

        <!-- Bottom user info skeleton -->
        <div class="pt-4 border-t border-slate-100 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-slate-200 animate-pulse"></div>
            <div class="space-y-1.5 flex-1">
                <div class="h-3.5 w-24 rounded bg-slate-200 animate-pulse"></div>
                <div class="h-3 w-16 rounded bg-slate-100 animate-pulse"></div>
            </div>
        </div>
    </div>

    <!-- Skeleton Main Area -->
    <div class="flex flex-1 flex-col overflow-hidden">
        <!-- Skeleton Header -->
        <header class="h-20 border-b border-slate-200 bg-white px-6 flex items-center justify-between shrink-0">
            <div class="space-y-2">
                <div class="h-3 w-36 rounded bg-slate-100 animate-pulse"></div>
                <div class="h-6 w-48 rounded bg-slate-200 animate-pulse"></div>
            </div>
            <div class="flex items-center gap-3">
                <div class="h-9 w-32 rounded-lg bg-slate-100 animate-pulse hidden sm:block"></div>
                <div class="h-9 w-9 rounded-full bg-slate-200 animate-pulse"></div>
            </div>
        </header>

        <!-- Skeleton Content Body (Matching User's Reference Layout) -->
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

            <!-- Big Main Content Card / Table Skeleton (Matching the user's uploaded image) -->
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
                <!-- Toolbar / Filter Bar -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div class="h-10 w-full sm:w-72 rounded-xl bg-slate-100 animate-pulse"></div>
                    <div class="flex items-center gap-2.5 w-full sm:w-auto">
                        <div class="h-10 w-28 rounded-xl bg-slate-100 animate-pulse"></div>
                        <div class="h-10 w-36 rounded-xl bg-emerald-100/70 animate-pulse"></div>
                    </div>
                </div>

                <!-- Table Rows Skeleton -->
                <div class="space-y-3.5">
                    <!-- Table Header -->
                    <div class="h-11 rounded-lg bg-slate-100/80 animate-pulse"></div>
                    <!-- 5 Table Body Rows -->
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50/70 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50/70 animate-pulse"></div>
                    <div class="h-14 rounded-lg bg-slate-50 animate-pulse"></div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Smoothly remove skeleton loader once page content and assets are loaded
    (function() {
        function dismissSkeleton() {
            var overlay = document.getElementById('page-skeleton-overlay');
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(function() {
                    if (overlay && overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                }, 300);
            }
        }

        // Hide when DOM is ready
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(dismissSkeleton, 150);
        } else {
            window.addEventListener('DOMContentLoaded', function() {
                setTimeout(dismissSkeleton, 150);
            });
            window.addEventListener('load', dismissSkeleton);
        }

        // Safety fallback: ensure skeleton is never stuck longer than 1.5s
        setTimeout(dismissSkeleton, 1500);
    })();
</script>

