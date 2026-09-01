<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Dokter - Klinik Lala Medicare</title>
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

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex h-20 items-center justify-between border-b border-slate-200 bg-white px-6">
                @include('layouts.header-page-info', ['breadcrumb' => 'Klinik Lala Medicare / Master Data', 'title' => 'Manajemen Data Dokter'])
                <div class="flex items-center gap-3">
                    @include('layouts.header-date')
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-900">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500">Staff</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-emerald-100 font-semibold text-emerald-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-6">
                <section class="mb-7">
                    <p class="text-sm font-semibold text-emerald-700">Master Data</p>
                    <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Manajemen Data Dokter</h1>
                    <p class="mt-2 text-sm text-slate-500">Kelola informasi dokter, poli praktik, dan akun login dokter.</p>
                </section>

                @if (session('success'))
                    <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-semibold">Data belum dapat disimpan.</p>
                        <p class="mt-1">Periksa kembali isian yang ditandai.</p>
                    </div>
                @endif

                <div class="grid gap-6 xl:grid-cols-[minmax(290px,360px)_minmax(0,1fr)]">
                    <section class="h-fit rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:sticky xl:top-6">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                <span class="material-symbols-outlined">person_add</span>
                            </div>
                            <div>
                                <h2 class="font-semibold text-slate-900">Tambah Dokter Baru</h2>
                                <p class="text-xs text-slate-500">Buat profil dan akun login.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('dokter.store') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Lengkap &amp; Gelar</label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required placeholder="dr. Nama Lengkap" class="w-full rounded-xl border @error('name') border-red-400 @else border-slate-200 @enderror bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="nip_sip" class="mb-1.5 block text-xs font-semibold text-slate-600">NIP / SIP</label>
                                <input id="nip_sip" name="nip_sip" type="text" value="{{ old('nip_sip') }}" required placeholder="SIP. 445/001/DS/2026" class="w-full rounded-xl border @error('nip_sip') border-red-400 @else border-slate-200 @enderror bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                @error('nip_sip') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="poli" class="mb-1.5 block text-xs font-semibold text-slate-600">Poli / Spesialisasi</label>
                                <select id="poli" name="poli" required class="w-full rounded-xl border @error('poli') border-red-400 @else border-slate-200 @enderror bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <option value="" disabled {{ old('poli') ? '' : 'selected' }}>Pilih poli</option>
                                    @foreach($poliList as $poli)
                                        <option value="{{ $poli }}" @selected(old('poli') === $poli)>{{ $poli }}</option>
                                    @endforeach
                                </select>
                                @error('poli') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-600">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required placeholder="dokter@klinik.com" class="w-full rounded-xl border @error('email') border-red-400 @else border-slate-200 @enderror bg-slate-50 px-3 py-2.5 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="password" class="mb-1.5 block text-xs font-semibold text-slate-600">Password Awal</label>
                                <div class="relative">
                                    <input id="password" name="password" type="password" required minlength="8" placeholder="Minimal 8 karakter" class="w-full rounded-xl border @error('password') border-red-400 @else border-slate-200 @enderror bg-slate-50 py-2.5 pl-3 pr-11 text-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <button type="button" onclick="togglePasswordVisibility('password', 'passwordEyeIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-emerald-600" aria-label="Tampilkan atau sembunyikan password">
                                        <span id="passwordEyeIcon" class="material-symbols-outlined text-xl">visibility</span>
                                    </button>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500">Password dapat diubah staff melalui tombol edit.</p>
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                <span class="material-symbols-outlined text-lg">save</span>
                                Simpan Data Dokter
                            </button>
                        </form>
                    </section>

                    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-semibold text-slate-900">Daftar Dokter Praktik</h2>
                                <p class="mt-1 text-xs text-slate-500">Akun dokter yang dapat masuk ke sistem.</p>
                            </div>
                            <span class="inline-flex w-fit rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">{{ $dokters->count() }} Dokter</span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4">No</th>
                                        <th class="px-6 py-4">Nama Dokter</th>
                                        <th class="px-6 py-4">NIP / SIP</th>
                                        <th class="px-6 py-4">Poli / Spesialis</th>
                                        <th class="px-6 py-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($dokters as $index => $dokter)
                                        <tr class="transition hover:bg-slate-50">
                                            <td class="px-6 py-4 text-slate-500">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 font-semibold text-slate-900">{{ $dokter->user->name }}</td>
                                            <td class="px-6 py-4 text-slate-600">{{ $dokter->nip_sip }}</td>
                                            <td class="px-6 py-4"><span class="inline-flex rounded-full bg-sky-50 px-2.5 py-1 text-xs font-semibold text-sky-700">{{ $dokter->poli }}</span></td>
                                            <td class="px-6 py-4">
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" onclick="openEditModal(this)" data-action="{{ route('dokter.update', $dokter) }}" data-name="{{ $dokter->user->name }}" data-email="{{ $dokter->user->email }}" data-nip-sip="{{ $dokter->nip_sip }}" data-poli="{{ $dokter->poli }}" class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">Edit</button>
                                                    <button type="button" onclick="openDeleteModal(this)" data-action="{{ route('dokter.destroy', $dokter) }}" data-name="{{ $dokter->user->name }}" class="rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-100">Hapus</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                                <span class="material-symbols-outlined mb-2 block text-3xl text-slate-300">medical_services</span>
                                                Belum ada data dokter. Tambahkan dokter melalui formulir di sebelah kiri.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-emerald-700">Data Dokter</p>
                    <h2 id="editModalTitle" class="mt-1 text-xl font-bold text-slate-900">Edit Dokter</h2>
                </div>
                <button type="button" onclick="closeModal('editModal')" class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800" aria-label="Tutup">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <form id="editForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="editName" class="mb-1.5 block text-xs font-semibold text-slate-600">Nama Lengkap &amp; Gelar</label>
                    <input id="editName" name="name" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="editNipSip" class="mb-1.5 block text-xs font-semibold text-slate-600">NIP / SIP</label>
                    <input id="editNipSip" name="nip_sip" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="editPoli" class="mb-1.5 block text-xs font-semibold text-slate-600">Poli / Spesialisasi</label>
                    <select id="editPoli" name="poli" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        @foreach($poliList as $poli)
                            <option value="{{ $poli }}">{{ $poli }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="editEmail" class="mb-1.5 block text-xs font-semibold text-slate-600">Email</label>
                    <input id="editEmail" name="email" type="email" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                </div>
                <div>
                    <label for="editPassword" class="mb-1.5 block text-xs font-semibold text-slate-600">Password Baru <span class="font-normal text-slate-400">(opsional)</span></label>
                    <div class="relative">
                        <input id="editPassword" name="password" type="password" minlength="8" placeholder="Kosongkan jika tidak diubah" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-3 pr-11 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <button type="button" onclick="togglePasswordVisibility('editPassword', 'editPasswordEyeIcon')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 transition hover:text-emerald-600" aria-label="Tampilkan atau sembunyikan password">
                            <span id="editPasswordEyeIcon" class="material-symbols-outlined text-xl">visibility</span>
                        </button>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500">Isi password baru minimal 8 karakter untuk mereset akses dokter.</p>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeModal('editModal')" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
        <div class="w-full max-w-md rounded-3xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-red-100 text-red-700"><span class="material-symbols-outlined">warning</span></div>
            <h2 id="deleteModalTitle" class="mt-4 text-xl font-bold text-slate-900">Hapus data dokter?</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">Data <span id="deleteDoctorName" class="font-semibold text-slate-700"></span> dan akun loginnya akan dihapus permanen dari database.</p>
            <form id="deleteForm" method="POST" class="mt-6 flex justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeModal('deleteModal')" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Ya, Hapus</button>
            </form>
        </div>
    </div>

    <script>
        function showModal(id) {
            const modal = document.getElementById(id);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEditModal(button) {
            document.getElementById('editForm').action = button.dataset.action;
            document.getElementById('editName').value = button.dataset.name;
            document.getElementById('editNipSip').value = button.dataset.nipSip;
            document.getElementById('editPoli').value = button.dataset.poli;
            document.getElementById('editEmail').value = button.dataset.email;
            document.getElementById('editPassword').value = '';
            showModal('editModal');
        }

        function openDeleteModal(button) {
            document.getElementById('deleteForm').action = button.dataset.action;
            document.getElementById('deleteDoctorName').textContent = button.dataset.name;
            showModal('deleteModal');
        }

        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            icon.textContent = isHidden ? 'visibility_off' : 'visibility';
        }

        ['editModal', 'deleteModal'].forEach((id) => {
            document.getElementById(id).addEventListener('click', function (event) {
                if (event.target === this) closeModal(id);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal('editModal');
                closeModal('deleteModal');
            }
        });
    </script>
</body>
</html>
