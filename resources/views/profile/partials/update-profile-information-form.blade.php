<section>
    <header class="border-b border-slate-100 pb-4 mb-6">
        <div class="flex items-center gap-2.5 text-slate-900">
            <span class="material-symbols-outlined text-2xl text-emerald-600">person</span>
            <h3 class="text-lg font-bold">Informasi Profil</h3>
        </div>
        <p class="mt-1 text-sm text-slate-500">
            Perbarui nama lengkap dan alamat email yang digunakan untuk login ke sistem klinik.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap *</label>
            <input id="name" name="name" type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email *</label>
            <input id="email" name="email" type="email" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-900 outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Peran / Hak Akses</label>
            <input type="text" readonly disabled class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm text-slate-500 cursor-not-allowed font-medium" value="{{ ucfirst($user->role ?? 'Staff') }}" />
            <p class="mt-1 text-[11px] text-slate-400">Hak akses ditentukan oleh administrator dan tidak dapat diubah sendiri.</p>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                <span class="material-symbols-outlined text-[18px]">save</span>
                Simpan Profil
            </button>

            @if (session('status') === 'profile-updated')
                <p class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-200 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    Profil berhasil diperbarui.
                </p>
            @endif
        </div>
    </form>
</section>
