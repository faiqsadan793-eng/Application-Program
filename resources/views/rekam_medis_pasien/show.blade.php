@php $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Rekam Medis - Klinik Lala Medicare</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{font-family:Inter,sans-serif}.medical-note{white-space:pre-wrap;overflow-wrap:anywhere}</style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<div class="flex min-h-screen">
    @include('layouts.sidebar')
    <div class="min-w-0 flex-1">
        <header class="flex min-h-16 items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-3">
            @include('layouts.header-page-info', ['breadcrumb' => 'Klinik Lala Medicare / Rekam Medis / Detail Pasien', 'title' => 'Riwayat Rekam Medis'])
            <div class="flex shrink-0 items-center gap-3">
                @include('layouts.header-date')
                <div class="hidden text-right lg:block"><p class="text-sm font-semibold">{{ $user->name }}</p><p class="text-xs text-slate-500">{{ $user->role === 'dokter' ? $user->dokter?->poli : 'Staff Klinik' }}</p></div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
            </div>
        </header>
        <main class="p-4 md:p-8">
            <div class="mx-auto max-w-6xl">
                <a href="{{ route('rekam-medis-pasien.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 hover:underline"><span class="material-symbols-outlined text-lg">arrow_back</span>Daftar Rekam Medis</a>
                <div class="my-6 flex flex-wrap items-start justify-between gap-4">
                    <div><h1 class="text-2xl font-bold tracking-tight text-emerald-800">Rekam Medis Pasien</h1><p class="mt-2 text-sm text-slate-500">Identitas dan riwayat pemeriksaan pasien.</p></div>
                    <a href="{{ route('rekam-medis-pasien.pdf', $pasien) }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-700 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-800"><span class="material-symbols-outlined text-lg">download</span>Ekspor Seluruh Riwayat PDF</a>
                </div>
                @if(session('error'))<div role="alert" class="mb-5 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">{{ session('error') }}</div>@endif
                @if($kunjungans->total() > 50)
                    <form action="{{ route('rekam-medis-pasien.pdf', $pasien) }}" method="GET" class="mb-5 flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 bg-white p-4">
                        <label for="pdf-part" class="text-sm text-slate-600">Unduh seluruh bagian untuk riwayat lengkap:</label>
                        <select id="pdf-part" name="bagian" class="rounded-lg border-slate-300 text-sm">
                            @for($part = 1; $part <= ceil($kunjungans->total() / 50); $part++)
                                <option value="{{ $part }}">Bagian {{ $part }} · Pemeriksaan {{ ($part - 1) * 50 + 1 }}–{{ min($part * 50, $kunjungans->total()) }}</option>
                            @endfor
                        </select>
                        <button class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white">Unduh PDF Bagian</button>
                    </form>
                @endif
                <section aria-label="Identitas pasien" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-5"><span class="material-symbols-outlined rounded-xl bg-emerald-50 p-3 text-emerald-700">person</span><div class="min-w-0"><h2 class="break-words text-xl font-bold">{{ $pasien->nama }}</h2><p class="mt-1 text-sm font-semibold text-emerald-700">{{ $pasien->no_rm }}</p></div></div>
                    <dl class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div><dt class="text-xs text-slate-500">Tanggal lahir</dt><dd class="mt-1 text-sm font-medium">{{ $pasien->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-500">Jenis kelamin</dt><dd class="mt-1 text-sm font-medium">{{ $pasien->jenis_kelamin ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-500">NIK</dt><dd class="mt-1 break-all text-sm font-medium">{{ $pasien->nik ?? '-' }}</dd></div>
                        <div><dt class="text-xs text-slate-500">Nomor telepon</dt><dd class="mt-1 text-sm font-medium">{{ $pasien->no_hp ?? '-' }}</dd></div>
                    </dl>
                </section>
                <div class="mb-4 mt-8 flex flex-wrap items-center justify-between gap-2"><h2 class="text-lg font-bold">Riwayat Pemeriksaan <span class="ml-2 rounded-full bg-emerald-100 px-3 py-1 text-sm text-emerald-800">{{ $kunjungans->total() }}</span></h2><p class="text-xs text-slate-500">Terbaru terlebih dahulu · {{ $user->role === 'dokter' ? $user->dokter?->poli : 'Seluruh poli' }}</p></div>
                <div class="space-y-5">
                    @foreach($kunjungans as $kunjungan)
                        @php $rm = $kunjungan->rekamMedis; @endphp
                        <article class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50 px-6 py-4">
                                <div><h3 class="font-semibold">{{ $kunjungan->tgl_kunjungan->translatedFormat('d F Y') }}</h3><p class="mt-1 text-sm text-slate-500">{{ $kunjungan->poli_tujuan }} · {{ $rm->nama_dokter ?? $rm->dokter?->user?->name ?? '-' }}</p></div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Selesai diperiksa</span>
                            </div>
                            <dl class="grid gap-6 p-6 md:grid-cols-2">
                                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Keluhan</dt><dd class="medical-note mt-2 text-sm leading-7 text-slate-700">{{ $rm->keluhan ?: '-' }}</dd></div>
                                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Diagnosis</dt><dd class="medical-note mt-2 text-sm leading-7 text-slate-700">{{ $rm->diagnosa ?: '-' }}</dd></div>
                                <div class="rounded-lg bg-emerald-50 p-4 md:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Resep Obat</dt><dd class="medical-note mt-2 text-sm leading-7 text-slate-700">{{ $rm->resep_obat ?: '-' }}</dd></div>
                            </dl>
                        </article>
                    @endforeach
                </div>
                <div class="mt-6">{{ $kunjungans->links() }}</div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
