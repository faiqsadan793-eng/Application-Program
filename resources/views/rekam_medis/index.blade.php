@php $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pemeriksaan Dokter - Klinik Lala Medicare</title>
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
    <div class="flex min-h-screen">
        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col">
            <header class="h-20 bg-white border-b border-slate-200 px-6 flex items-center justify-between">
                <div>
                    <p class="text-sm text-slate-500">Panel Dokter</p>
                    <p class="font-semibold text-slate-900">Daftar Antrean Pemeriksaan</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="h-8 w-px bg-slate-200"></div>
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:block text-right">
                            <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500">Dokter</p>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-sky-100 text-sky-700 font-semibold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <div class="max-w-[1000px] mx-auto">
                    @if(session('success'))
                        <div class="mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm font-semibold text-emerald-800 flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="mb-6 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm font-semibold text-sky-800">
                            {{ session('info') }}
                        </div>
                    @endif

                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">Antrean Pemeriksaan</h2>
                        <p class="text-sm text-slate-500 mt-1">Pasien yang menunggu pemeriksaan dokter hari ini.</p>
                    </div>

                    @forelse($antrean as $k)
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm mb-4 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <span class="font-semibold text-emerald-700">A-{{ str_pad($k->id_kunjungan, 3, '0', STR_PAD_LEFT) }}</span>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $k->pasien->nama ?? '-' }}</p>
                                        <p class="text-xs text-slate-500">{{ $k->poli_tujuan }} • Daftar {{ $k->created_at->format('H:i') }} WIB</p>
                                    </div>
                                </div>
                                @if($k->status == 'antre')
                                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-[11px] font-semibold text-yellow-800">Menunggu</span>
                                @elseif($k->status == 'menunggu_dokter')
                                    <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-[11px] font-semibold text-sky-800">Sedang Diperiksa</span>
                                @endif
                            </div>

                            <div class="p-6">
                                @if($k->status === 'antre')
                                    <p class="mb-4 text-sm text-slate-500">Mulai pemeriksaan terlebih dahulu sebelum mengisi rekam medis.</p>
                                    <form method="POST" action="{{ route('rekam-medis.mulai', $k->id_kunjungan) }}" class="flex justify-end">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                            <span class="material-symbols-outlined text-[18px]">stethoscope</span>
                                            Mulai Periksa
                                        </button>
                                    </form>
                                @else
                                <form method="POST" action="{{ route('rekam-medis.store') }}">
                                    @csrf
                                    <input type="hidden" name="id_kunjungan" value="{{ $k->id_kunjungan }}">
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-2">Keluhan Pasien *</label>
                                            <textarea name="keluhan" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-100" placeholder="Tuliskan keluhan yang disampaikan pasien..." required></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-2">Diagnosa *</label>
                                            <textarea name="diagnosa" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-100" placeholder="Tuliskan diagnosa medis..." required></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-2">Resep Obat *</label>
                                            <textarea name="resep_obat" rows="2" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-100" placeholder="Tuliskan resep obat yang diberikan..." required></textarea>
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700">
                                            <span class="material-symbols-outlined text-[18px]">save</span>
                                            Simpan & Selesai Periksa
                                        </button>
                                    </div>
                                </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-12 text-center">
                            <span class="material-symbols-outlined text-5xl text-slate-300 block mb-3">medical_services</span>
                            <p class="text-slate-500 font-medium">Tidak ada antrean pemeriksaan saat ini.</p>
                            <p class="text-slate-400 text-sm mt-1">Semua pasien sudah selesai diperiksa.</p>
                        </div>
                    @endforelse
                </div>
            </main>
        </div>
    </div>
</body>
</html>
