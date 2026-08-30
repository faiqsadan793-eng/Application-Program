<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Kunjungan - Klinik Lala Medicare</title>
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
                <div class="w-full max-w-xl">
                    {{-- Header search langsung submit form filter --}}
                    <form method="GET" action="{{ route('kunjungan.index') }}" class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama pasien atau No. RM..."
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                    </form>
                </div>
                <div class="flex items-center gap-4">
                    <div class="h-8 w-px bg-slate-200"></div>
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

                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">Riwayat Kunjungan Pasien</h2>
                        <p class="text-sm text-slate-500 mt-1">Daftar histori rekam kunjungan pasien di Klinik Lala Medicare.</p>
                    </div>

                    {{-- Filter Panel — semua input terhubung ke controller --}}
                    <form method="GET" action="{{ route('kunjungan.index') }}" id="filter-form">
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 mb-6">
                            <div class="flex flex-wrap gap-4 items-end">

                                {{-- Search --}}
                                <div class="flex-1 min-w-[220px]">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Cari Pasien</label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                                        <input type="text" name="search" value="{{ request('search') }}"
                                            placeholder="Nama pasien atau No. RM..."
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                                    </div>
                                </div>

                                {{-- Rentang Waktu --}}
                                <div class="min-w-[170px]">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Rentang Waktu</label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">calendar_month</span>
                                        <select name="rentang"
                                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm outline-none transition focus:border-emerald-500 appearance-none">
                                            <option value="7"     {{ request('rentang','7') === '7'     ? 'selected' : '' }}>7 Hari Terakhir</option>
                                            <option value="30"    {{ request('rentang','7') === '30'    ? 'selected' : '' }}>30 Hari Terakhir</option>
                                            <option value="bulan" {{ request('rentang','7') === 'bulan' ? 'selected' : '' }}>Bulan Ini</option>
                                            <option value="semua" {{ request('rentang','7') === 'semua' ? 'selected' : '' }}>Semua Waktu</option>
                                        </select>
                                    </div>
                                </div>

                                {{-- Poli --}}
                                <div class="min-w-[160px]">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Poli</label>
                                    <select name="poli"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm outline-none transition focus:border-emerald-500 appearance-none">
                                        <option value="semua"     {{ request('poli','semua') === 'semua'     ? 'selected' : '' }}>Semua Poli</option>
                                        <option value="Poli Umum" {{ request('poli') === 'Poli Umum' ? 'selected' : '' }}>Poli Umum</option>
                                        <option value="Poli Gigi" {{ request('poli') === 'Poli Gigi' ? 'selected' : '' }}>Poli Gigi</option>
                                        <option value="Poli Anak" {{ request('poli') === 'Poli Anak' ? 'selected' : '' }}>Poli Anak</option>
                                    </select>
                                </div>

                                {{-- Status --}}
                                <div class="min-w-[160px]">
                                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Status</label>
                                    <select name="status"
                                        class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 px-4 text-sm outline-none transition focus:border-emerald-500 appearance-none">
                                        <option value="semua"     {{ request('status','semua') === 'semua'      ? 'selected' : '' }}>Semua Status</option>
                                        <option value="menunggu"  {{ request('status') === 'menunggu'  ? 'selected' : '' }}>Menunggu</option>
                                        <option value="siap_bayar"{{ request('status') === 'siap_bayar'? 'selected' : '' }}>Siap Bayar</option>
                                        <option value="selesai"   {{ request('status') === 'selesai'   ? 'selected' : '' }}>Selesai</option>
                                    </select>
                                </div>

                                {{-- Tombol --}}
                                <div class="flex gap-2">
                                    <button type="submit"
                                        class="rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        Terapkan Filter
                                    </button>
                                    @if(request()->hasAny(['search','rentang','poli','status']))
                                        <a href="{{ route('kunjungan.index') }}"
                                            class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                            Reset
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </form>

                    {{-- Info jumlah hasil --}}
                    <div class="flex items-center justify-between mb-3 px-1">
                        <p class="text-sm text-slate-500">
                            Menampilkan <span class="font-semibold text-slate-700">{{ $kunjungans->total() }}</span> kunjungan
                            @if(request('search'))
                                untuk pencarian "<span class="font-semibold text-slate-700">{{ request('search') }}</span>"
                            @endif
                        </p>
                    </div>

                    {{-- Tabel --}}
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">No. RM</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Nama Pasien</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Tanggal &amp; Jam</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Poli</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Status</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($kunjungans as $kunjungan)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-5 font-semibold text-sky-700">
                                                RM-{{ str_pad($kunjungan->id_kunjungan, 3, '0', STR_PAD_LEFT) }}
                                            </td>
                                            <td class="px-6 py-5">
                                                <p class="font-semibold text-slate-900">{{ $kunjungan->pasien->nama ?? '-' }}</p>
                                                <p class="text-xs text-slate-400 mt-0.5">
                                                    ID: RM-{{ str_pad($kunjungan->pasien->id_pasien ?? 0, 4, '0', STR_PAD_LEFT) }}
                                                </p>
                                            </td>
                                            <td class="px-6 py-5 text-slate-500">
                                                {{ $kunjungan->tgl_kunjungan->format('d M Y') }}
                                                <span class="block text-xs">{{ $kunjungan->created_at->format('H:i') }} WIB</span>
                                            </td>
                                            <td class="px-6 py-5 font-medium text-slate-700">
                                                {{ $kunjungan->poli_tujuan ?? '-' }}
                                            </td>
                                            <td class="px-6 py-5">
                                                @if($kunjungan->status === 'selesai')
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-semibold text-emerald-800">Selesai</span>
                                                @elseif($kunjungan->status === 'siap_bayar')
                                                    <span class="inline-flex rounded-full bg-orange-100 px-3 py-1 text-[11px] font-semibold text-orange-800">Siap Bayar</span>
                                                @elseif(in_array($kunjungan->status, ['antre', 'menunggu_dokter']))
                                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-[11px] font-semibold text-yellow-800">Menunggu</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-5 text-center">
                                                <a href="{{ route('kunjungan.show', $kunjungan->id_kunjungan) }}"
                                                    class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-4 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-700">
                                                    <span class="material-symbols-outlined text-[15px]">visibility</span>
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-16 text-center">
                                                <span class="material-symbols-outlined text-5xl text-slate-300 block mb-3">manage_search</span>
                                                <p class="text-slate-500 font-medium">Tidak ada data kunjungan</p>
                                                <p class="text-slate-400 text-sm mt-1">Coba ubah filter atau kata kunci pencarian.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($kunjungans->hasPages())
                            <div class="px-6 py-4 border-t border-slate-200">
                                {{ $kunjungans->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </main>
        </div>
    </div>
</body>
</html>
