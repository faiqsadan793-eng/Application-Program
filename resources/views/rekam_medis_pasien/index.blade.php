@php $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rekam Medis Pasien - Klinik Lala Medicare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Public+Sans:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2 { font-family: 'Public Sans', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="flex min-h-screen">
        @include('layouts.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="h-16 border-b border-slate-200 bg-white px-6 flex items-center justify-between shadow-sm">
                @include('layouts.header-page-info', ['breadcrumb' => 'Klinik Lala Medicare / Rekam Medis', 'title' => 'Rekam Medis Pasien'])
                <div class="flex items-center gap-3">
                    @include('layouts.header-date')
                    <div class="ml-1 flex items-center gap-3 border-l border-slate-200 pl-4">
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $isDokter ? ($user->dokter->poli ?? 'Dokter') : 'Staff Klinik' }}</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-emerald-100 font-semibold text-emerald-700">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6 md:p-10">
                <div class="mx-auto max-w-6xl">
                    <section class="mb-7">
                        <h1 class="text-3xl font-bold tracking-tight text-emerald-800">Rekam Medis Pasien</h1>
                        <p class="mt-2 text-sm text-slate-500">Pencarian dan histori rekam medis pasien Klinik Lala Medicare.</p>
                    </section>

                    <form method="GET" action="{{ route('rekam-medis-pasien.index') }}" class="mb-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-100">
                        <div class="flex flex-col items-end gap-4 md:flex-row">
                            <div class="w-full flex-1">
                                <label for="search" class="mb-1 block text-xs font-medium text-slate-700">Cari Pasien</label>
                                <div class="relative">
                                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                                    <input id="search" name="search" value="{{ request('search') }}" placeholder="Cari Nama Pasien, No. RM, atau NIK..." class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-4 text-sm outline-none transition focus:border-emerald-700 focus:ring-2 focus:ring-emerald-100">
                                </div>
                            </div>
                            <button type="submit" class="inline-flex h-[42px] w-full items-center justify-center gap-2 rounded-lg bg-emerald-700 px-6 text-sm font-semibold text-white transition hover:bg-emerald-800 md:w-auto">
                                <span class="material-symbols-outlined text-[18px]">search</span>Cari Data
                            </button>
                        </div>
                    </form>

                    <section class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="overflow-x-auto">
                            <table class="min-w-[860px] w-full text-left text-sm">
                                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-600">
                                    <tr>
                                        <th class="px-5 py-4">No. RM</th><th class="px-5 py-4">Nama Pasien</th><th class="px-5 py-4">Kunjungan Terakhir</th><th class="px-5 py-4">Diagnosis Terakhir</th><th class="px-5 py-4">Dokter</th><th class="px-5 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse($rekamMedis as $pasien)
                                        @php $terakhir = $pasien->kunjungans->first(); $rm = $terakhir?->rekamMedis; @endphp
                                        <tr class="transition hover:bg-emerald-50/30">
                                            <td class="px-5 py-4 font-semibold text-emerald-700">{{ $pasien->no_rm }}</td>
                                            <td class="px-5 py-4"><p class="font-semibold text-slate-800">{{ $pasien->nama }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $pasien->tanggal_lahir?->age ?? '-' }} Thn</p></td>
                                            <td class="px-5 py-4 text-slate-700">{{ $terakhir?->tgl_kunjungan?->translatedFormat('d M Y') ?? '-' }}</td>
                                            <td class="max-w-[230px] px-5 py-4 text-slate-700"><p class="truncate" title="{{ $rm?->diagnosa }}">{{ $rm?->diagnosa ?? '-' }}</p></td>
                                            <td class="px-5 py-4 text-slate-700">{{ $rm?->nama_dokter ?? $rm?->dokter?->user?->name ?? '-' }}</td>
                                            <td class="px-5 py-4 text-right"><div class="flex justify-end gap-2"><a href="{{ route('rekam-medis-pasien.show', $pasien) }}" class="inline-flex whitespace-nowrap rounded-lg border border-emerald-700 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">Lihat Detail RM</a><a href="{{ route('rekam-medis-pasien.pdf', $pasien) }}" aria-label="Ekspor PDF rekam medis {{ $pasien->nama }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-800"><span class="material-symbols-outlined text-base">download</span>PDF</a></div></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="px-6 py-16 text-center"><span class="material-symbols-outlined block text-5xl text-slate-300">folder_open</span><p class="mt-3 font-medium text-slate-500">Data rekam medis tidak ditemukan.</p><p class="mt-1 text-sm text-slate-400">Coba ubah kata kunci atau filter poli.</p></td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($rekamMedis->hasPages())<div class="border-t border-slate-200 bg-slate-50 px-5 py-4">{{ $rekamMedis->links() }}</div>@endif
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
