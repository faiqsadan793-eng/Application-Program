@php $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Dokter - Klinik Lala Medicare</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined { font-size: 20px !important; line-height: 1.1; font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20; }
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
                @include('layouts.header-page-info', ['breadcrumb' => 'Panel Dokter / Dashboard', 'title' => 'Dashboard Utama'])

                <div class="flex items-center gap-4">
                    @include('layouts.header-date')
                    <a href="{{ route('profile.edit') }}" title="Pengaturan Akun & Profil" class="flex items-center gap-3 group transition">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-semibold text-slate-900 group-hover:text-emerald-700 transition">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">Dokter ({{ $dokter->poli ?? '-' }})</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-sky-100 text-sky-700 font-semibold group-hover:border-sky-400 group-hover:shadow-sm transition">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </a>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-3 sm:p-6">
                <!-- Welcome Banner -->
                <section class="mb-6">
                    <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 to-emerald-500 p-4 sm:p-6 text-white shadow-lg shadow-emerald-200/50">
                        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm uppercase tracking-[0.24em] text-emerald-200">Selamat Datang</p>
                                <h1 class="mt-3 text-3xl font-bold">{{ $user->name }}</h1>
                                <p class="mt-2 max-w-2xl text-sm text-emerald-100/90">
                                    Spesialisasi: <span class="font-semibold text-white">{{ $dokter->poli ?? '-' }}</span> | NIP/SIP: {{ $dokter->nip_sip ?? '-' }}
                                </p>
                                <p class="mt-1 text-sm text-emerald-100/90">Berikut adalah ringkasan antrean pasien Anda hari ini.</p>
                            </div>
                            <form action="{{ route('rekam-medis.panggil-selanjutnya') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex h-12 w-full md:w-auto items-center justify-center gap-2 rounded-2xl bg-white px-5 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                                    <span class="material-symbols-outlined">stethoscope</span>
                                    Panggil Pasien Selanjutnya
                                </button>
                            </form>
                        </div>
                    </div>
                </section>

                <!-- Stat Cards Grid -->
                <section class="grid gap-5 md:grid-cols-3 mb-6">
                    <!-- Total Pasien Hari Ini -->
                    <div class="rounded-3xl bg-white p-4 sm:p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined text-2xl">groups</span>
                            </div>
                            <p class="text-sm text-slate-500">Total Pasien Hari Ini</p>
                        </div>
                        <p id="statTotalPasien" class="mt-6 text-3xl font-semibold text-slate-900">{{ $totalPasien }}</p>
                    </div>

                    <!-- Menunggu Diperiksa -->
                    <div class="rounded-3xl bg-white p-4 sm:p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                <span class="material-symbols-outlined text-2xl">hourglass_empty</span>
                            </div>
                            <p class="text-sm text-slate-500">Menunggu Diperiksa</p>
                        </div>
                        <p id="statAntreanAktif" class="mt-6 text-3xl font-semibold text-slate-900">{{ $antreanAktif }}</p>
                    </div>

                    <!-- Selesai Diperiksa -->
                    <div class="rounded-3xl bg-white p-4 sm:p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                <span class="material-symbols-outlined text-2xl">check_circle</span>
                            </div>
                            <p class="text-sm text-slate-500">Selesai Diperiksa</p>
                        </div>
                        <p id="statSelesaiDiperiksa" class="mt-6 text-3xl font-semibold text-slate-900">{{ $selesaiDiperiksa }}</p>
                    </div>
                </section>

                <!-- Patient Queue Table Card -->
                <section class="rounded-3xl bg-white shadow-sm border border-slate-200 overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-3 sm:px-6 py-5 flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3 text-slate-900">
                            <span class="material-symbols-outlined text-2xl text-emerald-600">list_alt</span>
                            <h2 class="text-lg font-semibold">Daftar Antrean Pasien Poli Hari Ini</h2>
                            <span id="liveStatusBadge" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span> Live Update
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="fetchLiveAntrean(true)" title="Perbarui Sekarang" class="text-slate-500 hover:text-emerald-600 transition flex items-center p-1 rounded-lg hover:bg-slate-100">
                                <span class="material-symbols-outlined" id="refreshIcon">refresh</span>
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">No. Antrean</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">Nama Pasien</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">Jam Daftar</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">Poli</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">Status</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="antreanTableBody" class="divide-y divide-slate-200 bg-white">
                                @forelse($antreanHariIni as $antrean)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-5 font-semibold text-slate-900">{{ $antrean->labelAntrean() }}</td>
                                        <td class="px-6 py-5">
                                            <p class="font-semibold text-slate-900">{{ $antrean->pasien->nama ?? '-' }}</p>
                                            <p class="text-xs text-slate-500">ID: RM-{{ str_pad($antrean->id_pasien, 4, '0', STR_PAD_LEFT) }}</p>
                                        </td>
                                        <td class="px-6 py-5 text-slate-500">{{ ($antrean->masuk_antrean_pada ?? $antrean->created_at)->timezone('Asia/Jakarta')->format('H:i') }} WIB</td>
                                        <td class="px-6 py-5 text-slate-600 font-medium">{{ $antrean->poli_tujuan }}</td>
                                        <td class="px-6 py-5">
                                            @if($antrean->status == 'antre')
                                                <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-[11px] font-semibold text-yellow-800">Antre</span>
                                            @elseif($antrean->status == 'menunggu_dokter')
                                                <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold text-sky-800">Sedang Diperiksa</span>
                                            @elseif($antrean->status == 'siap_bayar')
                                                <span class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-[11px] font-semibold text-orange-800">Siap Bayar</span>
                                            @elseif($antrean->status == 'dibatalkan')
                                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-[11px] font-semibold text-red-800">Dibatalkan</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">Selesai</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @if($antrean->status == 'antre')
                                                <form action="{{ route('rekam-medis.mulai', $antrean->id_kunjungan) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                                        <span class="material-symbols-outlined text-[16px]">stethoscope</span> Periksa Pasien
                                                    </button>
                                                </form>
                                            @elseif($antrean->status == 'menunggu_dokter')
                                                <a href="{{ route('rekam-medis.index') }}" class="inline-flex items-center gap-1 rounded-2xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700">
                                                    <span class="material-symbols-outlined text-[16px]">stethoscope</span> Lanjutkan Periksa
                                                </a>
                                            @elseif($antrean->status == 'dibatalkan')
                                                <span class="inline-flex items-center gap-1 rounded-2xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">
                                                    <span class="material-symbols-outlined text-[16px]">cancel</span> Dibatalkan
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-2xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-500 border border-slate-200">
                                                    <span class="material-symbols-outlined text-[16px]">check_circle</span> Selesai
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada antrean untuk poli Anda hari ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>

    {{-- Elemen data penampung agar linter JS di VS Code tidak mengira sintaks Blade @ adalah decorator TS --}}
    <div id="dashboardDoctorMeta" class="hidden"
        data-check-url="{{ route('dashboard.dokter.antrean-check') }}"
        data-csrf-token="{{ csrf_token() }}"
        data-queue-count="{{ (int) $antreanAktif }}">
    </div>

    <script>
        const metaEl = document.getElementById('dashboardDoctorMeta');
        const checkUrl = metaEl.dataset.checkUrl;
        const csrfToken = metaEl.dataset.csrfToken;
        let previousQueueCount = parseInt(metaEl.dataset.queueCount || '0', 10);
        let isFetching = false;

        // Web Audio API Chime (Suara notifikasi bel lembut tanpa butuh file MP3 eksternal)
        function playQueueChime() {
            try {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (!AudioCtx) return;
                const ctx = new AudioCtx();
                if (ctx.state === 'suspended') {
                    ctx.resume();
                }
                const now = ctx.currentTime;

                // Nada 1: Bell lembut 880 Hz (A5)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(880, now);
                gain1.gain.setValueAtTime(0.12, now);
                gain1.gain.exponentialRampToValueAtTime(0.0001, now + 0.35);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.35);

                // Nada 2: Nada harmonik lebih tinggi 1174.66 Hz (D6)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(1174.66, now + 0.12);
                gain2.gain.setValueAtTime(0.14, now + 0.12);
                gain2.gain.exponentialRampToValueAtTime(0.0001, now + 0.55);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.12);
                osc2.stop(now + 0.55);
            } catch (e) {
                // Browser policy / audio disabled: fallback hening tanpa error
            }
        }

        function renderStatusBadge(status) {
            if (status === 'antre') {
                return '<span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-[11px] font-semibold text-yellow-800">Antre</span>';
            } else if (status === 'menunggu_dokter') {
                return '<span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold text-sky-800">Sedang Diperiksa</span>';
            } else if (status === 'siap_bayar') {
                return '<span class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-[11px] font-semibold text-orange-800">Siap Bayar</span>';
            } else if (status === 'dibatalkan') {
                return '<span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-[11px] font-semibold text-red-800">Dibatalkan</span>';
            }
            return '<span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">Selesai</span>';
        }

        function renderActionButton(item) {
            if (item.status === 'antre') {
                return `
                    <form action="${item.route_mulai}" method="POST" class="inline">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <button type="submit" class="inline-flex items-center gap-1 rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                            <span class="material-symbols-outlined text-[16px]">stethoscope</span> Periksa Pasien
                        </button>
                    </form>
                `;
            } else if (item.status === 'menunggu_dokter') {
                return `
                    <a href="${item.route_lanjut}" class="inline-flex items-center gap-1 rounded-2xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700">
                        <span class="material-symbols-outlined text-[16px]">stethoscope</span> Lanjutkan Periksa
                    </a>
                `;
            } else if (item.status === 'dibatalkan') {
                return `
                    <span class="inline-flex items-center gap-1 rounded-2xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">
                        <span class="material-symbols-outlined text-[16px]">cancel</span> Dibatalkan
                    </span>
                `;
            }
            return `
                <span class="inline-flex items-center gap-1 rounded-2xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-500 border border-slate-200">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span> Selesai
                </span>
            `;
        }

        async function fetchLiveAntrean(isManual = false) {
            if (isFetching) return;
            isFetching = true;

            const refreshIcon = document.getElementById('refreshIcon');
            if (refreshIcon && isManual) {
                refreshIcon.classList.add('animate-spin');
            }

            try {
                const response = await fetch(checkUrl, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) throw new Error('Gagal memuat status antrean');

                const data = await response.json();

                // Update stat angka
                const statTotal = document.getElementById('statTotalPasien');
                const statAktif = document.getElementById('statAntreanAktif');
                const statSelesai = document.getElementById('statSelesaiDiperiksa');

                if (statTotal) statTotal.innerText = data.totalPasien;
                if (statAktif) statAktif.innerText = data.antreanAktif;
                if (statSelesai) statSelesai.innerText = data.selesaiDiperiksa;

                // Cek jika ada antrean bertambah untuk notifikasi suara & visual
                if (data.antreanAktif > previousQueueCount) {
                    playQueueChime();
                    const badge = document.getElementById('liveStatusBadge');
                    if (badge) {
                        badge.classList.remove('bg-emerald-100', 'text-emerald-700');
                        badge.classList.add('bg-amber-100', 'text-amber-800');
                        badge.innerHTML = '<span class="h-2 w-2 rounded-full bg-amber-500 animate-ping"></span> Pasien Baru Masuk!';
                        setTimeout(() => {
                            badge.classList.remove('bg-amber-100', 'text-amber-800');
                            badge.classList.add('bg-emerald-100', 'text-emerald-700');
                            badge.innerHTML = '<span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span> Live Update';
                        }, 5000);
                    }
                }
                previousQueueCount = data.antreanAktif;

                // Render tabel antrean
                const tbody = document.getElementById('antreanTableBody');
                if (tbody) {
                    if (!data.antrean || data.antrean.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-slate-500">Belum ada antrean untuk poli Anda hari ini.</td>
                            </tr>
                        `;
                    } else {
                        tbody.innerHTML = data.antrean.map(item => `
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-5 font-semibold text-slate-900">${item.no_antrean}</td>
                                <td class="px-6 py-5">
                                    <p class="font-semibold text-slate-900">${item.nama_pasien}</p>
                                    <p class="text-xs text-slate-500">ID: ${item.no_rm}</p>
                                </td>
                                <td class="px-6 py-5 text-slate-500">${item.jam_daftar}</td>
                                <td class="px-6 py-5 text-slate-600 font-medium">${item.poli || '-'}</td>
                                <td class="px-6 py-5">${renderStatusBadge(item.status)}</td>
                                <td class="px-6 py-5 text-center">${renderActionButton(item)}</td>
                            </tr>
                        `).join('');
                    }
                }
            } catch (err) {
                console.warn('Auto polling antrean error:', err);
            } finally {
                isFetching = false;
                if (refreshIcon) {
                    refreshIcon.classList.remove('animate-spin');
                }
            }
        }

        // Smart polling: setiap 10 detik, hanya jika tab aktif (Page Visibility API)
        let pollingTimer = null;

        function startPolling() {
            if (!pollingTimer) {
                pollingTimer = setInterval(() => {
                    if (!document.hidden) {
                        fetchLiveAntrean(false);
                    }
                }, 10000); // 10 detik
            }
        }

        function stopPolling() {
            if (pollingTimer) {
                clearInterval(pollingTimer);
                pollingTimer = null;
            }
        }

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stopPolling();
            } else {
                fetchLiveAntrean(false);
                startPolling();
            }
        });

        // Jalankan polling
        startPolling();
    </script>
</body>
</html>
