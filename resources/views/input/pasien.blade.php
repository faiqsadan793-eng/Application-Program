@php $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Input Pasien - Klinik Lala Medicare</title>
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
                <div class="w-full max-w-xl">
                    <form method="GET" action="{{ route('pasien.index') }}" class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input type="text" name="search" placeholder="Cari nama pasien, NIK, atau No. RM..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
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
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-4">
                        <div>
                            <h2 class="text-2xl font-bold">Pengelolaan & Registrasi Pasien</h2>
                            <p class="text-sm text-slate-500 mt-1">Cari pasien lama atau daftarkan pasien baru untuk membuat antrean poli hari ini.</p>
                        </div>

                        <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-full border border-slate-200">
                            <a href="{{ route('pasien.index') }}" class="px-5 py-2 rounded-full text-slate-600 font-medium hover:bg-white/50">Cari Pasien Lama</a>
                            <a href="{{ route('pasien.create') }}" class="px-5 py-2 rounded-full bg-white text-emerald-700 font-semibold shadow-sm">Input Pasien Baru</a>
                        </div>
                    </div>

                    <section class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                        <h3 class="text-lg font-semibold mb-3">Form Pendaftaran Pasien Baru & Antrean</h3>
                        <p class="text-sm text-slate-500 mb-6">Lengkapi data identitas pasien baru untuk membuat Rekam Medis (RM) dan antrean poli secara otomatis.</p>

                        <form method="POST" action="{{ route('pasien.store') }}">
                            @csrf
                            @if ($errors->any())
                                <div class="mb-4 rounded-lg bg-red-50 p-3 text-sm text-red-700">
                                    <ul class="list-disc list-inside">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-600">Nama Lengkap Pasien *</label>
                                    <input name="nama" value="{{ old('nama') }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-100 @error('nama') border-red-400 @enderror" placeholder="Contoh: Budi Santoso" required />
                                    @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600">NIK / Nomor Identitas (16 Digit)</label>
                                    <input name="nik" value="{{ old('nik') }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-100 @error('nik') border-red-400 @enderror" placeholder="320xxxxxxxxxxxxx" maxlength="16" />
                                    @error('nik') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-600">Tanggal Lahir *</label>
                                    <input name="tanggal_lahir" type="date" value="{{ old('tanggal_lahir') }}" max="{{ now()->subDay()->toDateString() }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-100 @error('tanggal_lahir') border-red-400 @enderror" required />
                                    @error('tanggal_lahir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-600">Jenis Kelamin *</label>
                                    <select name="jenis_kelamin" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-100 @error('jenis_kelamin') border-red-400 @enderror" required>
                                        <option disabled value="" {{ old('jenis_kelamin') ? '' : 'selected' }}>-- Pilih --</option>
                                        <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('jenis_kelamin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-600">Nomor HP / WhatsApp *</label>
                                    <input name="no_hp" value="{{ old('no_hp') }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-100 @error('no_hp') border-red-400 @enderror" placeholder="08xxxxxxxxxx" maxlength="15" required />
                                    @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-sm font-medium text-slate-600">Pekerjaan</label>
                                    <input name="pekerjaan" value="{{ old('pekerjaan') }}" class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Contoh: Karyawan Swasta" />
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-slate-600">Alamat Lengkap</label>
                                    <textarea name="alamat" rows="4" class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-100" placeholder="Jl. Kebon Jeruk No. 123...">{{ old('alamat') }}</textarea>
                                </div>
                            </div>

                            <div class="mt-6 rounded-lg border border-emerald-100 bg-emerald-50/30 p-4">
                                <h4 class="text-sm font-semibold text-slate-700 mb-3">Pendaftaran Antrean Poli Hari Ini</h4>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600">Pilih Poli Tujuan *</label>
                                    <select name="poli_tujuan" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-emerald-100" required>
                                        <option disabled value="">-- Pilih Poli --</option>
                                        <option value="Poli Umum" {{ old('poli_tujuan') == 'Poli Umum' ? 'selected' : '' }}>Poli Umum</option>
                                        <option value="Poli Gigi" {{ old('poli_tujuan') == 'Poli Gigi' ? 'selected' : '' }}>Poli Gigi</option>
                                        <option value="Poli Anak" {{ old('poli_tujuan') == 'Poli Anak' ? 'selected' : '' }}>Poli Anak</option>
                                    </select>
                                    @error('poli_tujuan')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end items-center gap-3">
                                <a href="{{ route('pasien.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700">Batal</a>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Simpan Pasien & Buat Antrean</button>
                            </div>
                        </form>
                    </section>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
