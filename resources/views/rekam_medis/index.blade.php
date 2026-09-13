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
                @include('layouts.header-page-info', ['breadcrumb' => 'Panel Dokter / Rekam Medis', 'title' => 'Daftar Antrean Pemeriksaan'])
                <div class="flex items-center gap-4">
                    @include('layouts.header-date')
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
                                        <p class="text-xs text-slate-500">{{ $k->poli_tujuan }} • Antrean {{ ($k->masuk_antrean_pada ?? $k->created_at)->timezone('Asia/Jakarta')->format('H:i') }} WIB</p>
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
                                <div class="mb-5 flex flex-wrap justify-end gap-2 border-b border-slate-100 pb-5">
                                    @if($k->tgl_kunjungan->isToday())
                                        <form method="POST" action="{{ route('kunjungan.kembalikan-antrean', $k) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center gap-1.5 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-800 transition hover:bg-amber-100">
                                                <span class="material-symbols-outlined text-[16px]">replay</span> Kembalikan ke Antrean
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('kunjungan.batalkan', $k) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="kategori_pembatalan" value="tidak_hadir">
                                        <input type="hidden" name="alasan_pembatalan" value="Pasien tidak hadir saat dipanggil">
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-2xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100"
                                            onclick="return confirm('Tandai pasien tidak hadir dan batalkan kunjungan ini?')">
                                            <span class="material-symbols-outlined text-[16px]">person_off</span> Pasien Tidak Hadir
                                        </button>
                                    </form>
                                </div>
                                @php $pulihkanInput = (int) old('id_kunjungan') === (int) $k->id_kunjungan; @endphp
                                <form method="POST" action="{{ route('rekam-medis.store') }}" class="medical-record-form" data-visit-id="{{ $k->id_kunjungan }}">
                                    @csrf
                                    <input type="hidden" name="id_kunjungan" value="{{ $k->id_kunjungan }}">
                                    @if($pulihkanInput && $errors->any())
                                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                            Data belum tersimpan. Periksa kolom yang ditandai; isian Anda tetap dipertahankan.
                                        </div>
                                    @endif
                                    <div class="grid grid-cols-1 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-2">Keluhan Pasien *</label>
                                            <textarea name="keluhan" rows="2" maxlength="2000" data-draft-field="keluhan"
                                                class="w-full rounded-xl border {{ $pulihkanInput && $errors->has('keluhan') ? 'border-red-400' : 'border-slate-200' }} bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-100"
                                                placeholder="Tuliskan keluhan yang disampaikan pasien..." required>{{ $pulihkanInput ? old('keluhan') : '' }}</textarea>
                                            @if($pulihkanInput) @error('keluhan') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror @endif
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-2">Diagnosa *</label>
                                            <textarea name="diagnosa" rows="2" maxlength="2000" data-draft-field="diagnosa"
                                                class="w-full rounded-xl border {{ $pulihkanInput && $errors->has('diagnosa') ? 'border-red-400' : 'border-slate-200' }} bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-100"
                                                placeholder="Tuliskan diagnosa medis..." required>{{ $pulihkanInput ? old('diagnosa') : '' }}</textarea>
                                            @if($pulihkanInput) @error('diagnosa') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror @endif
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-600 mb-2">Resep Obat *</label>
                                            <textarea name="resep_obat" rows="2" maxlength="2000" data-draft-field="resep_obat"
                                                class="w-full rounded-xl border {{ $pulihkanInput && $errors->has('resep_obat') ? 'border-red-400' : 'border-slate-200' }} bg-slate-50 px-4 py-2 text-sm outline-none focus:ring-2 focus:ring-sky-100"
                                                placeholder="Tuliskan resep obat yang diberikan..." required>{{ $pulihkanInput ? old('resep_obat') : '' }}</textarea>
                                            @if($pulihkanInput) @error('resep_obat') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror @endif
                                        </div>
                                    </div>
                                    <div class="mt-4 flex justify-end">
                                        <button type="submit" data-submit-button class="inline-flex items-center gap-2 rounded-2xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-60">
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

    <script>
        (() => {
            const completedVisitId = @json(session('completed_visit_id'));
            if (completedVisitId) {
                try {
                    sessionStorage.removeItem(`rekam-medis-draft-${completedVisitId}`);
                } catch (error) {}
            }

            document.querySelectorAll('.medical-record-form').forEach((form) => {
                const storageKey = `rekam-medis-draft-${form.dataset.visitId}`;
                const fields = [...form.querySelectorAll('[data-draft-field]')];
                const hasServerValues = fields.some((field) => field.value !== '');

                if (!hasServerValues) {
                    try {
                        const draft = JSON.parse(sessionStorage.getItem(storageKey) || '{}');
                        fields.forEach((field) => {
                            if (typeof draft[field.dataset.draftField] === 'string') {
                                field.value = draft[field.dataset.draftField];
                            }
                        });
                    } catch (error) {
                        sessionStorage.removeItem(storageKey);
                    }
                }

                const saveDraft = () => {
                    const draft = {};
                    fields.forEach((field) => draft[field.dataset.draftField] = field.value);
                    try {
                        sessionStorage.setItem(storageKey, JSON.stringify(draft));
                    } catch (error) {
                        // Penyimpanan browser bersifat tambahan; kegagalannya tidak boleh menghambat submit.
                    }
                };

                fields.forEach((field) => field.addEventListener('input', saveDraft));
                form.addEventListener('submit', (event) => {
                    if (form.dataset.submitting === 'true') {
                        event.preventDefault();
                        return;
                    }

                    saveDraft();
                    form.dataset.submitting = 'true';
                    const button = form.querySelector('[data-submit-button]');
                    if (button) {
                        button.disabled = true;
                        button.innerHTML = '<span class="material-symbols-outlined text-[18px]">hourglass_top</span> Menyimpan...';
                    }
                });
            });
        })();
    </script>
</body>
</html>
