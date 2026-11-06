<section>
    <header class="border-b border-slate-100 pb-4 mb-6">
        <div class="flex items-center gap-2.5 text-slate-900">
            <span class="material-symbols-outlined text-2xl text-emerald-600">lock_reset</span>
            <h3 class="text-lg font-bold">Perbarui Kata Sandi</h3>
        </div>
        <p class="mt-1 text-sm text-slate-500">
            Pastikan akun Anda menggunakan kata sandi yang aman untuk mencegah akses tidak sah.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Saat Ini *</label>
            <div class="relative">
                <input id="update_password_current_password" name="current_password" type="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-4 pr-9 py-2.5 text-sm text-slate-900 outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500" autocomplete="current-password" required />
                <button type="button" onclick="togglePasswordVisibility('update_password_current_password', this)" tabindex="-1" title="Lihat/Sembunyikan sandi" class="absolute right-2.5 top-1/2 -translate-y-1/2 flex h-6 w-6 items-center justify-center text-slate-400 hover:text-emerald-600 focus:outline-none transition">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                </button>
            </div>
            @if($errors->updatePassword->has('current_password'))
                <p class="mt-1 text-xs font-medium text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Baru *</label>
            <div class="relative">
                <input id="update_password_password" name="password" type="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-4 pr-9 py-2.5 text-sm text-slate-900 outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500" autocomplete="new-password" required />
                <button type="button" onclick="togglePasswordVisibility('update_password_password', this)" tabindex="-1" title="Lihat/Sembunyikan sandi" class="absolute right-2.5 top-1/2 -translate-y-1/2 flex h-6 w-6 items-center justify-center text-slate-400 hover:text-emerald-600 focus:outline-none transition">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                </button>
            </div>
            <p class="mt-1 text-[11px] text-slate-400">Minimal 8 karakter.</p>
            @if($errors->updatePassword->has('password'))
                <p class="mt-1 text-xs font-medium text-red-600">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi Baru *</label>
            <div class="relative">
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-4 pr-9 py-2.5 text-sm text-slate-900 outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500" autocomplete="new-password" required />
                <button type="button" onclick="togglePasswordVisibility('update_password_password_confirmation', this)" tabindex="-1" title="Lihat/Sembunyikan sandi" class="absolute right-2.5 top-1/2 -translate-y-1/2 flex h-6 w-6 items-center justify-center text-slate-400 hover:text-emerald-600 focus:outline-none transition">
                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                </button>
            </div>
            @if($errors->updatePassword->has('password_confirmation'))
                <p class="mt-1 text-xs font-medium text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                <span class="material-symbols-outlined text-[18px]">key</span>
                Perbarui Kata Sandi
            </button>

            @if (session('status') === 'password-updated')
                <p class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-full border border-emerald-200 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    Kata sandi berhasil diperbarui.
                </p>
            @endif
        </div>
    </form>

    <script>
        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const icon = btn.querySelector('.material-symbols-outlined');
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                if (icon) icon.textContent = 'visibility';
            }
        }
    </script>
</section>
