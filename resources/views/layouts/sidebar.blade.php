@php $user = Auth::user(); @endphp
<aside class="hidden shrink-0 flex-col border-r border-slate-100 bg-white md:flex md:w-64">
    <div class="border-b border-slate-100 px-5 py-4">
        <div class="flex items-center gap-2.5">
            <div class="flex h-8 w-8 items-center justify-center text-emerald-600">
                <span class="material-symbols-outlined text-2xl">medical_services</span>
            </div>
            <div>
                <h1 class="text-sm font-bold leading-tight text-slate-800">Klinik Lala</h1>
                <p class="text-[11px] leading-tight text-slate-500">Medicare</p>
            </div>
        </div>
    </div>

    @php
        $active = 'bg-emerald-600 text-white shadow-sm';
        $inactive = 'text-slate-600 hover:bg-emerald-50 hover:text-emerald-700';
        $dashboardActive  = request()->routeIs('dashboard') ? $active : $inactive;
        $dokterActive     = request()->routeIs('dokter.*') ? $active : $inactive;
        $pasienActive     = request()->routeIs('pasien.*') ? $active : $inactive;
        $kunjunganActive  = request()->routeIs('kunjungan.*') ? $active : $inactive;
        $transaksiActive  = request()->routeIs('transaksi.*') ? $active : $inactive;
        $riwayatTransaksiActive = request()->routeIs('riwayat-transaksi.*') ? $active : $inactive;
        $periksaActive = request()->routeIs('rekam-medis.*') ? $active : $inactive;
        $rekamMedisPasienActive = request()->routeIs('rekam-medis-pasien.*') ? $active : $inactive;
    @endphp

    <nav class="custom-scrollbar flex-1 space-y-1.5 overflow-y-auto px-3 py-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors {{ $dashboardActive }}">
            <span class="material-symbols-outlined text-[21px]">home</span>
            <span class="font-medium">Home</span>
        </a>

        @if($user->role === 'staff')
            <a href="{{ route('dokter.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors {{ $dokterActive }}">
                <span class="material-symbols-outlined text-[21px]">stethoscope</span>
                <span class="font-medium">Master Data Dokter</span>
            </a>
            <a href="{{ route('pasien.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors {{ $pasienActive }}">
                <span class="material-symbols-outlined text-[21px]">person</span>
                <span class="font-medium">Pasien</span>
            </a>
            <a href="{{ route('kunjungan.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors {{ $kunjunganActive }}">
                <span class="material-symbols-outlined text-[21px]">history</span>
                <span class="font-medium">Riwayat Kunjungan</span>
            </a>
            <a href="{{ route('transaksi.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors {{ $transaksiActive }}">
                <span class="material-symbols-outlined text-[21px]">payments</span>
                <span class="font-medium">Transaksi</span>
            </a>
            <a href="{{ route('riwayat-transaksi.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors {{ $riwayatTransaksiActive }}">
                <span class="material-symbols-outlined text-[21px]">receipt_long</span>
                <span class="font-medium">Riwayat Transaksi</span>
            </a>
        @elseif($user->role === 'dokter')
            <a href="{{ route('rekam-medis.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors {{ $periksaActive }}">
                <span class="material-symbols-outlined">medical_services</span>
                <span class="font-medium">Periksa Pasien</span>
            </a>
            <a href="{{ route('rekam-medis-pasien.index') }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-colors {{ $rekamMedisPasienActive }}">
                <span class="material-symbols-outlined">description</span>
                <span>Rekam Medis Pasien</span>
            </a>
        @endif
    </nav>

    <div class="px-4 py-5 border-t border-slate-200">
        <button type="button" onclick="openLogoutModal()" class="w-full flex items-center justify-center gap-2 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100">
            <span class="material-symbols-outlined">logout</span>
            Keluar
        </button>
    </div>
</aside>

<div id="logoutModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
    <div class="w-full max-w-sm rounded-3xl border border-slate-200 bg-white p-6 text-center shadow-2xl">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100 text-red-600">
            <span class="material-symbols-outlined text-3xl">logout</span>
        </div>
        <h2 id="logoutModalTitle" class="mt-4 text-xl font-bold text-slate-900">Yakin ingin logout?</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Anda perlu login kembali untuk mengakses akun ini.</p>

        <div class="mt-6 flex gap-3">
            <button type="button" onclick="closeLogoutModal()" class="flex-1 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                Batal
            </button>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit" class="w-full rounded-2xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                    Ya, Logout
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openLogoutModal() {
        document.getElementById('logoutModal').classList.remove('hidden');
        document.getElementById('logoutModal').classList.add('flex');
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
        document.getElementById('logoutModal').classList.remove('flex');
    }

    document.getElementById('logoutModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeLogoutModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeLogoutModal();
        }
    });
</script>
