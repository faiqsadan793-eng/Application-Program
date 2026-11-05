<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengelolaan Pasien - Klinik Lala Medicare</title>
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
    @php $user = Auth::user(); @endphp

    <div class="flex min-h-screen">
        @include('layouts.sidebar')

        <div class="flex min-w-0 flex-1 flex-col">
            {{-- HEADER --}}
            <header class="min-h-20 bg-white border-b border-slate-200 px-3 py-3 sm:px-6 flex items-center justify-between gap-3">
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

            <main class="flex-1 overflow-y-auto p-3 sm:p-6">
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
                    @if ($errors->any())
                        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                            <p class="font-semibold">Data pasien belum dapat disimpan:</p>
                            <ul class="mt-1 list-disc list-inside space-y-0.5 text-xs">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Page Header --}}
                    <div class="flex flex-col md:flex-row md:items-end justify-between mb-6 gap-4">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold">Pengelolaan & Registrasi Pasien</h2>
                            <p class="text-sm text-slate-500 mt-1">Cari pasien lama atau daftarkan pasien baru untuk membuat antrean poli hari ini.</p>
                        </div>
                        <div class="flex w-full sm:w-auto items-center gap-1 sm:gap-2 bg-slate-50 p-1 rounded-full border border-slate-200">
                            <a href="{{ route('pasien.index') }}"
                                class="flex-1 sm:flex-none whitespace-nowrap px-3 sm:px-5 py-2 text-center text-sm rounded-full bg-emerald-600 text-white font-semibold shadow-sm transition hover:bg-emerald-700">
                                Cari Pasien Lama
                            </a>
                            <a href="{{ route('pasien.create') }}"
                                class="flex-1 sm:flex-none whitespace-nowrap px-3 sm:px-5 py-2 text-center text-sm rounded-full text-slate-600 font-medium transition hover:bg-white/50">
                                Input Pasien Baru
                            </a>
                        </div>
                    </div>

                    <div class="space-y-6">

                        {{-- Search Form --}}
                        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                            <form method="GET" action="{{ route('pasien.index') }}" class="flex flex-col gap-3 sm:flex-row">
                                <div class="flex-1 relative">
                                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">person_search</span>
                                    <input name="search" value="{{ request('search') }}"
                                        class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none transition focus:ring-2 focus:ring-emerald-100 focus:border-emerald-400"
                                        placeholder="Ketik Nama Pasien, NIK, atau No. Rekam Medis (RM)..."
                                        type="text" autocomplete="off">
                                </div>
                                <button type="submit"
                                    class="w-full sm:w-auto justify-center bg-emerald-600 text-white px-6 py-3 rounded-xl font-semibold flex items-center gap-2 transition hover:bg-emerald-700">
                                    <span class="material-symbols-outlined">search</span>
                                    Cari
                                </button>
                            </form>
                        </div>

                        {{-- Tabel pasien terintegrasi: pasien terbaru atau hasil pencarian --}}
                        <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm">
                            <div class="px-3 sm:px-6 py-4 border-b border-slate-100 bg-slate-50 flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
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
                                    <table class="w-full min-w-[760px] divide-y divide-slate-200 text-left text-sm">
                                        <thead class="bg-slate-50 text-slate-500">
                                            <tr>
                                                <th class="whitespace-nowrap px-3 sm:px-6 py-3 font-semibold uppercase tracking-wide">No. RM</th>
                                                <th class="whitespace-nowrap px-3 sm:px-6 py-3 font-semibold uppercase tracking-wide">Nama Pasien</th>
                                                <th class="whitespace-nowrap px-3 sm:px-6 py-3 font-semibold uppercase tracking-wide">NIK</th>
                                                <th class="whitespace-nowrap px-3 sm:px-6 py-3 font-semibold uppercase tracking-wide">No. HP</th>
                                                <th class="whitespace-nowrap px-3 sm:px-6 py-3 font-semibold uppercase tracking-wide">Terdaftar</th>
                                                <th class="whitespace-nowrap px-3 sm:px-6 py-3 font-semibold uppercase tracking-wide text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 bg-white">
                                            @foreach($pasiens as $p)
                                                <tr class="hover:bg-slate-50">
                                                    <td class="whitespace-nowrap px-3 sm:px-6 py-4 font-semibold text-emerald-700">{{ $p->no_rm ?: 'RM-' . str_pad($p->id_pasien, 6, '0', STR_PAD_LEFT) }}</td>
                                                    <td class="px-3 sm:px-6 py-4">
                                                        <div class="font-semibold text-slate-900">{{ $p->nama }}</div>
                                                        @if($p->has_active_kunjungan_today)
                                                            <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                                                <span class="material-symbols-outlined text-[12px]">check_circle</span>
                                                                Kunjungan aktif hari ini
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="whitespace-nowrap px-3 sm:px-6 py-4 text-slate-600">{{ $p->nik ?? '-' }}</td>
                                                    <td class="whitespace-nowrap px-3 sm:px-6 py-4 text-slate-600">{{ $p->no_hp ?? '-' }}</td>
                                                    <td class="whitespace-nowrap px-3 sm:px-6 py-4 text-slate-500">{{ $p->created_at?->timezone('Asia/Jakarta')->locale('id')->translatedFormat('d M Y') ?? '-' }}</td>
                                                    <td class="whitespace-nowrap px-3 sm:px-6 py-4 text-right">
                                                        <div class="flex items-center justify-end gap-2">
                                                            <button type="button"
                                                                onclick="openEditPasienModal(this)"
                                                                data-action="{{ route('pasien.update', $p->id_pasien) }}"
                                                                data-nama="{{ $p->nama }}"
                                                                data-nik="{{ $p->nik }}"
                                                                data-tanggal-lahir="{{ $p->tanggal_lahir?->format('Y-m-d') }}"
                                                                data-jenis-kelamin="{{ $p->jenis_kelamin }}"
                                                                data-no-hp="{{ $p->no_hp }}"
                                                                data-pekerjaan="{{ $p->pekerjaan }}"
                                                                data-alamat="{{ $p->alamat }}"
                                                                class="inline-flex items-center gap-1 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100 whitespace-nowrap">
                                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                                                Edit
                                                            </button>

                                                            @if($p->has_active_kunjungan_today)
                                                                <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-100 px-3 py-2 text-xs font-semibold text-emerald-700 whitespace-nowrap">
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
                                                                        @foreach(config('clinic.poli') as $poli)
                                                                            <option value="{{ $poli }}">{{ $poli }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700 whitespace-nowrap">
                                                                        <span class="material-symbols-outlined text-[16px]">add</span>
                                                                        Buat Antrean
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($pasiens->hasPages())
                                    <div class="border-t border-slate-100 px-3 sm:px-6 py-4">
                                        {{ $pasiens->links() }}
                                    </div>
                                @endif
                            @endif
                        </div>

                    </div>{{-- end space-y-6 --}}
                </div>
            </main>
        </div>
    </div>
    {{-- Modal Edit Pasien --}}
    <div id="editPasienModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="editPasienModalTitle">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
            <div class="mb-5 flex items-start justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Manajemen Pasien</p>
                    <h2 id="editPasienModalTitle" class="mt-1 text-xl font-bold text-slate-900">Edit Data Pasien</h2>
                </div>
                <button type="button" onclick="closeEditPasienModal()" class="rounded-xl p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="editPasienForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="editNama" class="mb-1 block text-xs font-semibold text-slate-600">Nama Lengkap Pasien *</label>
                    <input id="editNama" name="nama" type="text" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="editNik" class="mb-1 block text-xs font-semibold text-slate-600">NIK (16 Digit)</label>
                        <input id="editNik" name="nik" type="text" maxlength="16" placeholder="320xxxxxxxxxxxxx" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label for="editNoHp" class="mb-1 block text-xs font-semibold text-slate-600">Nomor HP / WA *</label>
                        <input id="editNoHp" name="no_hp" type="text" required maxlength="15" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="editTanggalLahir" class="mb-1 block text-xs font-semibold text-slate-600">Tanggal Lahir *</label>
                        <input id="editTanggalLahir" name="tanggal_lahir" type="date" required max="{{ now()->setTimezone('Asia/Jakarta')->subDay()->toDateString() }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    </div>
                    <div>
                        <label for="editJenisKelamin" class="mb-1 block text-xs font-semibold text-slate-600">Jenis Kelamin *</label>
                        <select id="editJenisKelamin" name="jenis_kelamin" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="editPekerjaan" class="mb-1 block text-xs font-semibold text-slate-600">Pekerjaan</label>
                    <input id="editPekerjaan" name="pekerjaan" type="text" placeholder="Contoh: Karyawan Swasta" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="editAlamat" class="mb-1 block text-xs font-semibold text-slate-600">Alamat Lengkap</label>
                    <textarea id="editAlamat" name="alamat" rows="3" maxlength="500" placeholder="Alamat lengkap pasien..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeEditPasienModal()" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditPasienModal(button) {
            document.getElementById('editPasienForm').action = button.dataset.action;
            document.getElementById('editNama').value = button.dataset.nama || '';
            document.getElementById('editNik').value = button.dataset.nik || '';
            document.getElementById('editNoHp').value = button.dataset.noHp || '';
            document.getElementById('editTanggalLahir').value = button.dataset.tanggalLahir || '';
            document.getElementById('editJenisKelamin').value = button.dataset.jenisKelamin || 'Laki-laki';
            document.getElementById('editPekerjaan').value = button.dataset.pekerjaan || '';
            document.getElementById('editAlamat').value = button.dataset.alamat || '';
            
            const modal = document.getElementById('editPasienModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditPasienModal() {
            const modal = document.getElementById('editPasienModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        document.getElementById('editPasienModal')?.addEventListener('click', function(event) {
            if (event.target === this) {
                closeEditPasienModal();
            }
        });
    </script>
</body>
</html>
