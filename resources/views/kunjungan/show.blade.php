<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Kunjungan RM-{{ str_pad($kunjungan->id_kunjungan, 3, '0', STR_PAD_LEFT) }} - Klinik Lala Medicare</title>
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
                <div class="flex items-center gap-3">
                    <a href="{{ route('kunjungan.index') }}"
                        class="flex items-center justify-center rounded-full border border-slate-200 bg-slate-50 p-2 text-slate-600 transition hover:bg-slate-100">
                        <span class="material-symbols-outlined text-[20px]">arrow_back</span>
                    </a>
                    <div>
                        <p class="text-xs text-slate-400">Riwayat Kunjungan</p>
                        <p class="text-sm font-semibold text-slate-900">
                            Detail Kunjungan — RM-{{ str_pad($kunjungan->id_kunjungan, 3, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>
                </div>
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
                <div class="max-w-6xl mx-auto space-y-5">

                    @if(session('success'))
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Judul, tombol kembali, dan status --}}
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <a href="{{ route('kunjungan.index') }}"
                                class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                                Kembali ke Riwayat
                            </a>
                            <h2 class="text-xl font-bold text-slate-900">Detail Kunjungan</h2>
                        </div>
                        @if($kunjungan->status === 'selesai')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-semibold text-emerald-800">
                                <span class="material-symbols-outlined text-[16px]">check_circle</span> Selesai
                            </span>
                        @elseif($kunjungan->status === 'dibatalkan')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-4 py-1.5 text-sm font-semibold text-red-800">
                                <span class="material-symbols-outlined text-[16px]">cancel</span> Dibatalkan
                            </span>
                        @elseif($kunjungan->status === 'siap_bayar')
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-4 py-1.5 text-sm font-semibold text-orange-800">
                                <span class="material-symbols-outlined text-[16px]">payments</span> Siap Bayar
                            </span>
                        @elseif(in_array($kunjungan->status, ['antre', 'menunggu_dokter']))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 px-4 py-1.5 text-sm font-semibold text-yellow-800">
                                <span class="material-symbols-outlined text-[16px]">schedule</span> Menunggu
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">
                    {{-- CARD 1: Info Kunjungan & Pasien --}}
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center gap-3">
                            <span class="material-symbols-outlined text-emerald-600">person</span>
                            <h3 class="font-semibold text-slate-800">Informasi Pasien & Kunjungan</h3>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">

                                {{-- Kolom kiri --}}
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">No. Rekam Medis</p>
                                        <p class="mt-1 font-semibold text-sky-700 text-lg">
                                            RM-{{ str_pad($kunjungan->id_kunjungan, 3, '0', STR_PAD_LEFT) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Nama Pasien</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ $kunjungan->pasien->nama ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">NIK</p>
                                        <p class="mt-1 text-slate-700">{{ $kunjungan->pasien->nik ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">No. HP</p>
                                        <p class="mt-1 text-slate-700">{{ $kunjungan->pasien->no_hp ?? '-' }}</p>
                                    </div>
                                </div>

                                {{-- Kolom kanan --}}
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Kunjungan</p>
                                        <p class="mt-1 font-semibold text-slate-900">
                                            {{ $kunjungan->tgl_kunjungan->translatedFormat('d F Y') }}
                                        </p>
                                        <p class="text-xs text-slate-400">Jam masuk antrean: {{ ($kunjungan->masuk_antrean_pada ?? $kunjungan->created_at)->timezone('Asia/Jakarta')->format('H:i') }} WIB</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Poli Tujuan</p>
                                        <p class="mt-1 font-semibold text-slate-900">{{ $kunjungan->poli_tujuan ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jenis Kelamin</p>
                                        <p class="mt-1 text-slate-700">{{ $kunjungan->pasien->jenis_kelamin ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tanggal Lahir</p>
                                        <p class="mt-1 text-slate-700">
                                            {{ $kunjungan->pasien->tanggal_lahir
                                                ? $kunjungan->pasien->tanggal_lahir->translatedFormat('d F Y')
                                                : '-' }}
                                        </p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- CARD 2: Rekam Medis --}}
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center gap-3">
                            <span class="material-symbols-outlined text-sky-600">medical_information</span>
                            <h3 class="font-semibold text-slate-800">Hasil Pemeriksaan</h3>
                        </div>

                        @if($kunjungan->rekamMedis)
                            <div class="p-6 space-y-5">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Keluhan Pasien</p>
                                    <div class="rounded-2xl bg-slate-50 border border-slate-100 px-4 py-3 text-sm text-slate-700 leading-relaxed">
                                        {{ $kunjungan->rekamMedis->keluhan }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Diagnosa</p>
                                    <div class="rounded-2xl bg-sky-50 border border-sky-100 px-4 py-3 text-sm text-slate-700 leading-relaxed">
                                        {{ $kunjungan->rekamMedis->diagnosa }}
                                    </div>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Resep Obat</p>
                                    <div class="rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 text-sm text-slate-700 leading-relaxed">
                                        {{ $kunjungan->rekamMedis->resep_obat }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="p-10 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">stethoscope</span>
                                <p class="text-slate-400 text-sm">Belum ada hasil pemeriksaan untuk kunjungan ini.</p>
                            </div>
                        @endif
                    </div>

                    @if(in_array($kunjungan->status, ['antre', 'menunggu_dokter']))
                        <form method="POST" action="{{ route('kunjungan.batalkan', $kunjungan) }}" class="rounded-3xl border border-red-200 bg-red-50 p-5 lg:col-span-2">
                            @csrf
                            @method('PATCH')
                            <label for="kategori_pembatalan" class="block text-sm font-semibold text-red-900">Batalkan kunjungan</label>
                            <p class="mt-1 text-xs text-red-700">Data kunjungan tetap tersimpan dalam riwayat dan pasien dapat didaftarkan kembali.</p>
                            <div class="mt-3 grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,2fr)_auto]">
                                <div>
                                    <select id="kategori_pembatalan" name="kategori_pembatalan" required
                                        class="w-full rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100">
                                        <option value="">Pilih kategori</option>
                                        @foreach(\App\Models\Kunjungan::KATEGORI_PEMBATALAN as $nilai => $label)
                                            <option value="{{ $nilai }}" @selected(old('kategori_pembatalan') === $nilai)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('kategori_pembatalan') <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                                </div>
                                <textarea id="alasan_pembatalan" name="alasan_pembatalan" rows="2" maxlength="500"
                                    class="min-h-20 w-full rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm outline-none focus:border-red-400 focus:ring-2 focus:ring-red-100"
                                    placeholder="Keterangan tambahan (wajib bila memilih Lainnya)">{{ old('alasan_pembatalan') }}</textarea>
                                <button type="submit" class="self-end rounded-2xl bg-red-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-700"
                                    onclick="return confirm('Batalkan kunjungan ini? Data tetap tersimpan dalam riwayat.')">
                                    Batalkan Kunjungan
                                </button>
                            </div>
                            @error('alasan_pembatalan') <p class="mt-2 text-xs font-medium text-red-700">{{ $message }}</p> @enderror
                        </form>
                    @elseif($kunjungan->status === 'dibatalkan')
                        <div class="rounded-3xl border border-red-200 bg-red-50 p-5 lg:col-span-2">
                            <h3 class="font-semibold text-red-900">Informasi Pembatalan</h3>
                            <dl class="mt-3 grid gap-4 text-sm md:grid-cols-4">
                                <div><dt class="text-xs font-semibold uppercase text-red-500">Kategori</dt><dd class="mt-1 text-red-900">{{ $kunjungan->kategoriPembatalanLabel() }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase text-red-500">Keterangan</dt><dd class="mt-1 text-red-900">{{ $kunjungan->alasan_pembatalan ?: '-' }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase text-red-500">Waktu</dt><dd class="mt-1 text-red-900">{{ $kunjungan->dibatalkan_pada?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d F Y, H:i') }} WIB</dd></div>
                                <div><dt class="text-xs font-semibold uppercase text-red-500">Dibatalkan oleh</dt><dd class="mt-1 text-red-900">{{ $kunjungan->nama_pembatal ?? 'Pengguna yang sudah tidak aktif' }}</dd></div>
                            </dl>
                        </div>
                    @endif
                    </div>

                    {{-- CARD 3: Transaksi / Pembayaran --}}
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center gap-3">
                            <span class="material-symbols-outlined text-orange-500">payments</span>
                            <h3 class="font-semibold text-slate-800">Informasi Pembayaran</h3>
                        </div>

                        @if($kunjungan->transaksi)
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">No. Transaksi</p>
                                        <p class="mt-1 font-semibold text-slate-900">
                                            TRX-{{ str_pad($kunjungan->transaksi->id_transaksi, 4, '0', STR_PAD_LEFT) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Biaya</p>
                                        <p class="mt-1 font-bold text-slate-900 text-lg">
                                            @if($kunjungan->transaksi->total_biaya > 0)
                                                Rp {{ number_format($kunjungan->transaksi->total_biaya, 0, ',', '.') }}
                                            @else
                                                <span class="text-slate-400 text-sm font-normal">Belum diisi</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Status Pembayaran</p>
                                        <div class="mt-1">
                                            @if($kunjungan->transaksi->status_pembayaran === 'lunas')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                                    <span class="material-symbols-outlined text-[14px]">check_circle</span> Lunas
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-800">
                                                    <span class="material-symbols-outlined text-[14px]">pending</span> Belum Dibayar
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($kunjungan->transaksi->status_pembayaran === 'lunas')
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Metode Pembayaran</p>
                                            <p class="mt-1 font-semibold text-slate-900">
                                                {{ \App\Models\Transaksi::labelMetodePembayaran($kunjungan->transaksi->metode_pembayaran) }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="p-10 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">receipt_long</span>
                                <p class="text-slate-400 text-sm">Belum ada data pembayaran untuk kunjungan ini.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </main>
        </div>
    </div>
</body>
</html>
