<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengelolaan Pasien - Klinik Lala Medicare</title>
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
            {{-- HEADER --}}
            <header class="h-20 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
                @include('layouts.header-page-info', ['breadcrumb' => 'Klinik Lala Medicare / Pasien', 'title' => 'Pengelolaan & Registrasi Pasien'])
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
                <div class="max-w-[1200px] mx-auto">

                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="mb-5 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-semibold text-emerald-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-5 rounded-2xl bg-red-50 border border-red-200 p-4 text-sm font-semibold text-red-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-red-500">error</span>
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Page Header --}}
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-4">
                        <div>
                            <h2 class="text-2xl font-bold">Pengelolaan & Registrasi Pasien</h2>
                            <p class="text-sm text-slate-500 mt-1">Cari pasien lama atau daftarkan pasien baru untuk membuat antrean poli hari ini.</p>
                        </div>
                        <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-full border border-slate-200">
                            <a href="{{ route('pasien.index') }}"
                                class="px-5 py-2 rounded-full bg-emerald-600 text-white font-semibold shadow-sm transition hover:bg-emerald-700">
                                Cari Pasien Lama
                            </a>
                            <a href="{{ route('pasien.create') }}"
                                class="px-5 py-2 rounded-full text-slate-600 font-medium transition hover:bg-white/50">
                                Input Pasien Baru
                            </a>
                        </div>
                    </div>

                    <div class="space-y-6">

                        {{-- Search Form --}}
                        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                            <form method="GET" action="{{ route('pasien.index') }}" class="flex gap-3">
                                <div class="flex-1 relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">person_search</span>
                                    <input name="search" value="{{ request('search') }}"
                                        class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none transition focus:ring-2 focus:ring-emerald-100 focus:border-emerald-400"
                                        placeholder="Ketik Nama Pasien, NIK, atau No. Rekam Medis (RM)..."
                                        type="text" autocomplete="off">
                                </div>
                                <button type="submit"
                                    class="bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold flex items-center gap-2 transition hover:bg-emerald-700">
                                    <span class="material-symbols-outlined">search</span>
                                    Cari
                                </button>
                            </form>
                        </div>

                        {{-- Tabel pasien terintegrasi: pasien terbaru atau hasil pencarian --}}
                        <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">
                                    {{ $hasSearch ? 'Hasil Pencarian Pasien' : 'Data Pasien Terbaru' }}
                                </h3>
                                @if($hasSearch)
                                    <span class="text-xs text-slate-400">
                                        {{ $pasiens->total() }} pasien ditemukan untuk "<span class="font-semibold text-slate-600">{{ request('search') }}</span>"
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">Menampilkan pasien yang terakhir didaftarkan</span>
                                @endif
                            </div>

                            @if($pasiens->isEmpty())
                                <div class="p-6">
                                    @if($hasSearch)
                                    {{-- State: sudah search tapi tidak ada hasil --}}
                                    <div class="text-center py-10 text-slate-400">
                                        <span class="material-symbols-outlined text-5xl block mb-3 text-slate-300">person_off</span>
                                        <p class="font-medium text-slate-500">Pasien tidak ditemukan</p>
                                        <p class="text-sm mt-1">Coba kata kunci lain atau
                                            <a href="{{ route('pasien.create') }}" class="text-emerald-600 font-semibold hover:underline">daftarkan pasien baru</a>.
                                        </p>
                                    </div>
                                    @else
                                        <div class="text-center py-10 text-slate-400">
                                            <span class="material-symbols-outlined text-5xl block mb-3 text-slate-300">group_off</span>
                                            <p class="font-medium text-slate-500">Belum ada data pasien.</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                        <thead class="bg-slate-50 text-slate-500">
                                            <tr>
                                                <th class="px-6 py-3 font-semibold uppercase tracking-wide">No. RM</th>
                                                <th class="px-6 py-3 font-semibold uppercase tracking-wide">Nama Pasien</th>
                                                <th class="px-6 py-3 font-semibold uppercase tracking-wide">NIK</th>
                                                <th class="px-6 py-3 font-semibold uppercase tracking-wide">No. HP</th>
                                                <th class="px-6 py-3 font-semibold uppercase tracking-wide">Terdaftar</th>
                                                <th class="px-6 py-3 font-semibold uppercase tracking-wide text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-white">
                                            @foreach($pasiens as $p)
                                                <tr class="hover:bg-slate-50">
                                                    <td class="px-6 py-4 font-semibold text-emerald-700">{{ $p->no_rm ?: 'RM-' . str_pad($p->id_pasien, 6, '0', STR_PAD_LEFT) }}</td>
                                                    <td class="px-6 py-4">
                                                        <div class="font-semibold text-slate-900">{{ $p->nama }}</div>
                                                        @if($p->has_active_kunjungan_today)
                                                            <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                                                <span class="material-symbols-outlined text-[12px]">check_circle</span>
                                                                Kunjungan aktif hari ini
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-slate-600">{{ $p->nik ?? '-' }}</td>
                                                    <td class="px-6 py-4 text-slate-600">{{ $p->no_hp ?? '-' }}</td>
                                                    <td class="px-6 py-4 text-slate-500">{{ $p->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y') ?? '-' }}</td>
                                                    <td class="px-6 py-4 text-right">
                                                        @if($p->has_active_kunjungan_today)
                                                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-700">
                                                                <span class="material-symbols-outlined text-[16px]">event_available</span>
                                                                Sudah antre
                                                            </span>
                                                        @else
                                                            <form method="POST" action="{{ route('kunjungan.store') }}" class="inline-flex items-center gap-2">
                                                                @csrf
                                                                <input type="hidden" name="id_pasien" value="{{ $p->id_pasien }}">
                                                                <input type="hidden" name="search_query" value="{{ request('search') }}">
                                                                <select name="poli_tujuan" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" required>
                                                                    <option disabled selected value="">Pilih Poli</option>
                                                                    <option value="Poli Umum">Poli Umum</option>
                                                                    <option value="Poli Gigi">Poli Gigi</option>
                                                                    <option value="Poli Anak">Poli Anak</option>
                                                                </select>
                                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 whitespace-nowrap">
                                                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                                                    Buat Antrean
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($pasiens->hasPages())
                                    <div class="border-t border-slate-100 px-6 py-4">
                                        {{ $pasiens->links() }}
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- Antrean Hari Ini --}}
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Antrean Terbaru Hari Ini</h3>
                                <a href="{{ route('dashboard') }}"
                                    class="text-emerald-700 font-semibold text-sm flex items-center gap-1 hover:underline">
                                    Lihat Semua
                                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                </a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                    <thead class="bg-slate-50 text-slate-500">
                                        <tr>
                                            <th class="px-6 py-3 font-semibold uppercase tracking-wide">No. Antrean</th>
                                            <th class="px-6 py-3 font-semibold uppercase tracking-wide">Nama Pasien</th>
                                            <th class="px-6 py-3 font-semibold uppercase tracking-wide">Poli</th>
                                            <th class="px-6 py-3 font-semibold uppercase tracking-wide">Jam Daftar</th>
                                            <th class="px-6 py-3 font-semibold uppercase tracking-wide text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 bg-white">
                                        @forelse($antreanHariIni as $antrean)
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-6 py-4 font-semibold text-emerald-700">
                                                    A-{{ str_pad($antrean->id_kunjungan, 3, '0', STR_PAD_LEFT) }}
                                                </td>
                                                <td class="px-6 py-4 font-medium text-slate-900">{{ $antrean->pasien->nama ?? '-' }}</td>
                                                <td class="px-6 py-4 text-slate-600">{{ $antrean->poli_tujuan }}</td>
                                                <td class="px-6 py-4 text-slate-500">{{ ($antrean->masuk_antrean_pada ?? $antrean->created_at)->timezone('Asia/Jakarta')->format('H:i') }} WIB</td>
                                                <td class="px-6 py-4 text-right">
                                                    @if($antrean->status == 'antre')
                                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 text-[11px] font-semibold">Antre</span>
                                                    @elseif($antrean->status == 'menunggu_dokter')
                                                        <span class="px-3 py-1 rounded-full bg-sky-100 text-sky-800 text-[11px] font-semibold">Pemeriksaan</span>
                                                    @elseif($antrean->status == 'siap_bayar')
                                                        <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-800 text-[11px] font-semibold">Siap Bayar</span>
                                                    @else
                                                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-[11px] font-semibold">Selesai</span>
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
                        </div>

                    </div>{{-- end space-y-6 --}}
                </div>
            </main>
        </div>
    </div>
</body>
</html>
