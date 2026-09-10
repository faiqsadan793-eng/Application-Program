<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Klinik Lala Medicare - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
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
    @php $user = Auth::user(); @endphp

    <div class="flex min-h-screen">
        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col">
            <header class="h-20 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
                @include('layouts.header-page-info', ['breadcrumb' => 'Klinik Lala Medicare / Dashboard', 'title' => 'Dashboard Utama'])
                <div class="flex items-center gap-4">
                    @include('layouts.header-date')
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ ucfirst($user->role ?? 'Staff') }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-emerald-100 text-emerald-700 font-semibold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <section class="mb-6">
                    <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 to-emerald-500 p-6 text-white shadow-lg shadow-emerald-200/50">
                        <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm uppercase tracking-[0.24em] text-emerald-200">Selamat Datang</p>
                                <h1 class="mt-3 text-3xl font-semibold">{{ $user->name }}</h1>
                                <p class="mt-2 max-w-2xl text-sm text-emerald-100/90">Berikut adalah ringkasan antrean dan aktivitas klinik hari ini.</p>
                            </div>
                            <a href="{{ route('pasien.create') }}" class="inline-flex h-12 items-center gap-2 rounded-2xl bg-white px-5 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">
                                <span class="material-symbols-outlined">add_circle</span>
                                Pasien Baru
                            </a>
                        </div>
                    </div>
                </section>

                <section class="grid gap-5 md:grid-cols-3 mb-6">
                    <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined text-2xl">groups</span>
                            </div>
                            <p class="text-sm text-slate-500">Total Pasien</p>
                        </div>
                        <p class="mt-6 text-3xl font-semibold text-slate-900">{{ $totalPasien }}</p>
                    </div>
                    <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                <span class="material-symbols-outlined text-2xl">queue</span>
                            </div>
                            <p class="text-sm text-slate-500">Antrean Aktif</p>
                        </div>
                        <p class="mt-6 text-3xl font-semibold text-slate-900">{{ $antreanAktif }}</p>
                    </div>
                    <div class="rounded-3xl bg-white p-6 shadow-sm border border-slate-200">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-100 text-orange-700">
                                <span class="material-symbols-outlined text-2xl">payments</span>
                            </div>
                            <p class="text-sm text-slate-500">Menunggu Kasir</p>
                        </div>
                        <p class="mt-6 text-3xl font-semibold text-slate-900">{{ $menungguKasir }}</p>
                    </div>
                </section>

                <section class="rounded-3xl bg-white shadow-sm border border-slate-200 overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-5">
                        <div class="flex items-center gap-3 text-slate-900">
                            <span class="material-symbols-outlined text-2xl text-emerald-600">list_alt</span>
                            <h2 class="text-lg font-semibold">Daftar Antrean Hari Ini</h2>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">No. Antrean</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">Nama Pasien</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">Poli</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide">Status</th>
                                    <th class="px-6 py-4 font-semibold uppercase tracking-wide text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse($antreanHariIni as $antrean)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-5 font-semibold text-slate-900">A-{{ str_pad($antrean->id_kunjungan, 3, '0', STR_PAD_LEFT) }}</td>
                                        <td class="px-6 py-5">
                                            <p class="font-semibold text-slate-900">{{ $antrean->pasien->nama ?? '-' }}</p>
                                            <p class="text-xs text-slate-500">ID: RM-{{ str_pad($antrean->id_pasien, 4, '0', STR_PAD_LEFT) }}</p>
                                        </td>
                                        <td class="px-6 py-5 text-slate-600">{{ $antrean->poli_tujuan }}</td>
                                        <td class="px-6 py-5">
                                            @if($antrean->status == 'antre')
                                                <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-[11px] font-semibold text-yellow-800">Antre</span>
                                            @elseif($antrean->status == 'menunggu_dokter')
                                                <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold text-sky-800">Pemeriksaan</span>
                                            @elseif($antrean->status == 'siap_bayar')
                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">Siap Bayar</span>
                                            @elseif($antrean->status == 'dibatalkan')
                                                <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-[11px] font-semibold text-red-800">Dibatalkan</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-800">Selesai</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @if($antrean->status == 'antre' || $antrean->status == 'menunggu_dokter')
                                                <span class="inline-flex items-center gap-1 rounded-2xl bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 border border-sky-200">
                                                    <span class="material-symbols-outlined text-[16px]">schedule</span> Menunggu
                                                </span>
                                            @elseif($antrean->status == 'siap_bayar')
                                                <span class="inline-flex items-center gap-1 rounded-2xl bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-700 border border-orange-200">
                                                    <span class="material-symbols-outlined text-[16px]">payments</span> Ke Kasir
                                                </span>
                                            @elseif($antrean->status == 'dibatalkan')
                                                <span class="inline-flex items-center gap-1 rounded-2xl border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">
                                                    <span class="material-symbols-outlined text-[16px]">cancel</span> Dibatalkan
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-2xl bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700 border border-emerald-200">
                                                    <span class="material-symbols-outlined text-[16px]">check_circle</span> Selesai
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-slate-500">Belum ada antrean untuk hari ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
