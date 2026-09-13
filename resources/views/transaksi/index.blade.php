@php $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kasir & Transaksi - Klinik Lala Medicare</title>
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
                @include('layouts.header-page-info', ['breadcrumb' => 'Klinik Lala Medicare / Transaksi', 'title' => 'Pembayaran Pasien'])
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
                <div class="max-w-[1200px] mx-auto">
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

                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">Kasir & Pembayaran</h2>
                        <p class="text-sm text-slate-500 mt-1">Daftar pasien yang menunggu proses pembayaran di kasir.</p>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-2xl text-orange-500">payments</span>
                                <h3 class="text-lg font-semibold">Menunggu Pembayaran</h3>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-orange-100 px-3 py-1 text-sm font-semibold text-orange-700">
                                {{ $transaksis->count() }} transaksi
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">No. Antrean</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Nama Pasien</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Diagnosa</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Total Biaya</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($transaksis as $t)
                                        <tr class="hover:bg-slate-50">
                                            <td class="px-6 py-5 font-semibold text-orange-700">A-{{ str_pad($t->id_kunjungan, 3, '0', STR_PAD_LEFT) }}</td>
                                            <td class="px-6 py-5">
                                                <p class="font-semibold text-slate-900">{{ $t->kunjungan->pasien->nama ?? '-' }}</p>
                                                <p class="text-xs text-slate-500">{{ $t->kunjungan->poli_tujuan ?? '-' }}</p>
                                            </td>
                                            <td class="px-6 py-5 text-slate-600 max-w-[200px] truncate">
                                                {{ $t->kunjungan->rekamMedis->diagnosa ?? '-' }}
                                            </td>
                                             <td class="px-6 py-5 font-semibold text-slate-800">
                                                 Rp {{ number_format($t->total_biaya, 0, ',', '.') }}
                                             </td>
                                             <td class="px-6 py-5 text-center">
                                                 <button type="button"
                                                         data-transaction-id="{{ $t->id_transaksi }}"
                                                         onclick="openPaymentModal({{ json_encode([
                                                             'id_transaksi' => $t->id_transaksi,
                                                             'no_antrean' => 'A-' . str_pad($t->id_kunjungan, 3, '0', STR_PAD_LEFT),
                                                             'nama_pasien' => $t->kunjungan->pasien->nama ?? '-',
                                                             'poli' => $t->kunjungan->poli_tujuan ?? '-',
                                                             'diagnosa' => $t->kunjungan->rekamMedis->diagnosa ?? '-',
                                                             'resep_obat' => $t->kunjungan->rekamMedis->resep_obat ?? '-',
                                                             'jasa_dokter' => 50000,
                                                             'update_url' => route('transaksi.update', $t),
                                                         ]) }})"
                                                         class="inline-flex items-center gap-1.5 rounded-2xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                                     <span class="material-symbols-outlined text-[16px]">payments</span> Proses Bayar
                                                 </button>
                                             </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center">
                                                <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">payments</span>
                                                <p class="text-slate-500">Tidak ada transaksi yang menunggu pembayaran.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($transaksis->hasPages())
                            <div class="border-t border-slate-200 px-6 py-4">
                                {{ $transaksis->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Pembayaran Rinci -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-3xl w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2 text-slate-900">
                    <span class="material-symbols-outlined text-2xl text-emerald-600">payments</span>
                    <h3 class="font-bold text-lg">Proses Pembayaran</h3>
                </div>
                <button onclick="closePaymentModal()" class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <form id="paymentForm" method="POST" action="" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" id="paymentTransactionId" name="payment_transaction_id" value="">

                <div id="paymentValidationAlert" class="hidden rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700" role="alert"></div>

                <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 lg:items-start">
                    <div class="space-y-4">
                <!-- Info Pasien -->
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-150 text-sm">
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">No. Antrean</p>
                        <p id="modalNoAntrean" class="font-bold text-slate-800 text-base mt-0.5">-</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Poli Tujuan</p>
                        <p id="modalPoli" class="font-semibold text-slate-800 text-base mt-0.5">-</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Nama Pasien</p>
                        <p id="modalNamaPasien" class="font-bold text-slate-900 mt-0.5">-</p>
                    </div>
                </div>

                <!-- Info Medis -->
                <div class="space-y-2 bg-sky-50/50 p-4 rounded-2xl border border-sky-100 text-sm">
                    <div>
                        <p class="text-xs text-sky-700 font-semibold uppercase tracking-wider">Diagnosa Dokter</p>
                        <p id="modalDiagnosa" class="text-slate-800 mt-0.5 font-medium">-</p>
                    </div>
                    <div class="border-t border-sky-100 pt-2">
                        <p class="text-xs text-sky-700 font-semibold uppercase tracking-wider">Resep Obat</p>
                        <p id="modalResepObat" class="text-slate-800 mt-0.5 font-medium whitespace-pre-line">-</p>
                    </div>
                </div>
                    </div>

                <!-- Form Rincian Transaksi -->
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 flex min-h-8 items-end text-xs font-semibold uppercase tracking-wider text-slate-600">Jasa Dokter / Tindakan</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="text" id="displayBiayaTindakan" value="50.000" inputmode="numeric" autocomplete="off" oninput="formatPaymentInput(this); calculateTotal()" class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-100" required />
                                 <input type="hidden" id="inputJasaDokter" name="biaya_tindakan" value="50000" />
                             </div>
                             @error('biaya_tindakan') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 flex min-h-8 items-end text-xs font-semibold uppercase tracking-wider text-slate-600">Biaya Obat</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="text" id="displayBiayaObat" value="0" inputmode="numeric" autocomplete="off" oninput="formatPaymentInput(this); calculateTotal()" class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-100" required />
                                 <input type="hidden" id="inputBiayaObat" name="biaya_obat" value="0" />
                             </div>
                             @error('biaya_obat') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-700">Total Biaya Tagihan</span>
                            <div class="relative w-44">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-bold">Rp</span>
                                <input type="text" id="inputTotalBiaya" readonly class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-slate-200 bg-slate-100 font-bold text-emerald-800 text-sm outline-none" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <p class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Metode Pembayaran</p>
                        <input type="hidden" id="inputMetodePembayaran" name="metode_pembayaran" value="cash" />
                        <div class="grid grid-cols-3 gap-2" role="group" aria-label="Metode pembayaran">
                            <button type="button" data-payment-method="cash" onclick="selectPaymentMethod('cash')" class="inline-flex items-center justify-center gap-1.5 rounded-xl border px-3 py-3 text-sm font-bold transition focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <span class="material-symbols-outlined text-[19px]">payments</span>
                                Cash
                            </button>
                            <button type="button" data-payment-method="qr" onclick="selectPaymentMethod('qr')" class="inline-flex items-center justify-center gap-1.5 rounded-xl border px-3 py-3 text-sm font-bold transition focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <span class="material-symbols-outlined text-[19px]">qr_code_2</span>
                                QRIS
                            </button>
                            <button type="button" data-payment-method="debit" onclick="selectPaymentMethod('debit')" class="inline-flex items-center justify-center gap-1.5 rounded-xl border px-3 py-3 text-sm font-bold transition focus:outline-none focus:ring-2 focus:ring-emerald-200">
                                <span class="material-symbols-outlined text-[19px]">credit_card</span>
                                Debit
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-3">
                        <div>
                            <label id="labelUangDibayar" class="mb-1 flex min-h-8 items-end text-xs font-bold uppercase tracking-wider text-slate-700">Uang Diterima (Cash)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-bold">Rp</span>
                                <input type="text" id="displayUangDibayar" value="0" inputmode="numeric" autocomplete="off" oninput="formatPaymentInput(this); calculateChange()" class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-emerald-300 bg-emerald-50/50 text-sm font-bold text-emerald-900 outline-none focus:ring-2 focus:ring-emerald-200" required />
                                 <input type="hidden" id="inputUangDibayar" name="uang_dibayar" value="0" />
                             </div>
                             @error('uang_dibayar') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 flex min-h-8 items-end text-xs font-semibold uppercase tracking-wider text-slate-600">Uang Kembalian</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="text" id="inputKembalian" readonly class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-slate-200 bg-slate-100 font-semibold text-slate-700 text-sm outline-none" />
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" onclick="closePaymentModal()" class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" id="paymentSubmitButton" class="rounded-2xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-100 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60">
                        Proses & Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const maxPaymentAmount = 9999999999;
        let currentPaymentId = null;
        let restoringPayment = false;

        function paymentValue(value) {
            return Number(String(value).replace(/\D/g, '')) || 0;
        }

        function formatRupiahNumber(value) {
            return paymentValue(value).toLocaleString('id-ID');
        }

        function formatPaymentInput(input) {
            input.value = formatRupiahNumber(input.value);
        }

        function setPaymentAlert(message = '') {
            const alertBox = document.getElementById('paymentValidationAlert');
            alertBox.textContent = message;
            alertBox.classList.toggle('hidden', message === '');
        }

        function paymentStorageKey(id) {
            return `payment-draft-${id}`;
        }

        function savePaymentDraft() {
            if (!currentPaymentId || restoringPayment) return;

            const draft = {
                biaya_tindakan: paymentValue(document.getElementById('displayBiayaTindakan').value),
                biaya_obat: paymentValue(document.getElementById('displayBiayaObat').value),
                uang_dibayar: paymentValue(document.getElementById('displayUangDibayar').value),
                metode_pembayaran: document.getElementById('inputMetodePembayaran').value,
            };

            try {
                sessionStorage.setItem(paymentStorageKey(currentPaymentId), JSON.stringify(draft));
            } catch (error) {
                // Penyimpanan browser bersifat tambahan; kegagalannya tidak boleh menghambat pembayaran.
            }
        }

        function loadPaymentDraft(id) {
            try {
                const draft = JSON.parse(sessionStorage.getItem(paymentStorageKey(id)) || 'null');
                return draft && typeof draft === 'object' ? draft : null;
            } catch (error) {
                return null;
            }
        }

        function openPaymentModal(data) {
            currentPaymentId = Number(data.id_transaksi);
            restoringPayment = true;
            document.getElementById('modalNoAntrean').innerText = data.no_antrean;
            document.getElementById('modalPoli').innerText = data.poli;
            document.getElementById('modalNamaPasien').innerText = data.nama_pasien;
            document.getElementById('modalDiagnosa').innerText = data.diagnosa;
            document.getElementById('modalResepObat').innerText = data.resep_obat;

            document.getElementById('displayBiayaTindakan').value = formatRupiahNumber(data.jasa_dokter || 50000);
            document.getElementById('displayBiayaObat').value = '0';
            document.getElementById('displayUangDibayar').value = '0';
            selectPaymentMethod('cash');

            const form = document.getElementById('paymentForm');
            form.action = data.update_url;
            form.dataset.submitting = 'false';
            document.getElementById('paymentTransactionId').value = currentPaymentId;
            const submitButton = document.getElementById('paymentSubmitButton');
            submitButton.disabled = false;
            submitButton.textContent = 'Proses & Selesai';

            const draft = loadPaymentDraft(currentPaymentId);
            if (draft) {
                document.getElementById('displayBiayaTindakan').value = formatRupiahNumber(draft.biaya_tindakan || 0);
                document.getElementById('displayBiayaObat').value = formatRupiahNumber(draft.biaya_obat || 0);
                document.getElementById('displayUangDibayar').value = formatRupiahNumber(draft.uang_dibayar || 0);
                if (['cash', 'qr', 'debit'].includes(draft.metode_pembayaran)) {
                    selectPaymentMethod(draft.metode_pembayaran);
                }
            }

            restoringPayment = false;
            calculateTotal();
            setPaymentAlert();

            const modal = document.getElementById('paymentModal');
            modal.classList.remove('hidden');
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('hidden');
            setPaymentAlert();
        }

        function calculateTotal() {
            const jasaDokter = paymentValue(document.getElementById('displayBiayaTindakan').value);
            const biayaObat = paymentValue(document.getElementById('displayBiayaObat').value);
            const total = jasaDokter + biayaObat;
            document.getElementById('inputJasaDokter').value = jasaDokter;
            document.getElementById('inputBiayaObat').value = biayaObat;
            document.getElementById('inputTotalBiaya').value = formatRupiahNumber(total);

            calculateChange();
        }

        function calculateChange() {
            const total = paymentValue(document.getElementById('inputTotalBiaya').value);
            const uangDibayar = paymentValue(document.getElementById('displayUangDibayar').value);
            const kembalian = uangDibayar - total;
            document.getElementById('inputUangDibayar').value = uangDibayar;
            document.getElementById('inputKembalian').value = formatRupiahNumber(kembalian >= 0 ? kembalian : 0);
            savePaymentDraft();
        }

        function selectPaymentMethod(method) {
            document.getElementById('inputMetodePembayaran').value = method;
            handlePaymentMethodChange();
        }

        function handlePaymentMethodChange() {
            const method = document.getElementById('inputMetodePembayaran').value;
            const paymentInput = document.getElementById('displayUangDibayar');
            const isCash = method === 'cash';

            document.querySelectorAll('[data-payment-method]').forEach((button) => {
                const isSelected = button.dataset.paymentMethod === method;
                button.classList.toggle('border-emerald-600', isSelected);
                button.classList.toggle('bg-emerald-600', isSelected);
                button.classList.toggle('text-white', isSelected);
                button.classList.toggle('shadow-sm', isSelected);
                button.classList.toggle('border-slate-300', !isSelected);
                button.classList.toggle('bg-white', !isSelected);
                button.classList.toggle('text-slate-600', !isSelected);
            });

            document.getElementById('labelUangDibayar').textContent = isCash
                ? 'Uang Diterima (Cash)'
                : `Nominal Pembayaran (${method === 'qr' ? 'QRIS' : 'Debit'})`;
            paymentInput.readOnly = false;
            paymentInput.classList.remove('bg-slate-100');
            paymentInput.classList.add('bg-emerald-50/50');

            calculateTotal();
        }

        document.getElementById('paymentForm').addEventListener('submit', function (event) {
            if (this.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            calculateTotal();

            const biayaTindakan = paymentValue(document.getElementById('displayBiayaTindakan').value);
            const biayaObat = paymentValue(document.getElementById('displayBiayaObat').value);
            const total = paymentValue(document.getElementById('inputTotalBiaya').value);
            const uangDibayar = paymentValue(document.getElementById('displayUangDibayar').value);

            if ([biayaTindakan, biayaObat, uangDibayar, total].some((value) => value > maxPaymentAmount)) {
                event.preventDefault();
                setPaymentAlert('Nominal maksimal yang dapat disimpan adalah Rp 9.999.999.999. Periksa kembali rincian pembayaran.');
                return;
            }

            if (uangDibayar < total) {
                event.preventDefault();
                setPaymentAlert('Nominal pembayaran kurang dari total tagihan. Silakan periksa kembali nominal yang diterima.');
                document.getElementById('displayUangDibayar').focus();
                return;
            }

            savePaymentDraft();
            this.dataset.submitting = 'true';
            const submitButton = document.getElementById('paymentSubmitButton');
            submitButton.disabled = true;
            submitButton.textContent = 'Memproses...';
        });

        const completedPaymentId = @json(session('completed_payment_id'));
        if (completedPaymentId) {
            try {
                sessionStorage.removeItem(paymentStorageKey(completedPaymentId));
            } catch (error) {}
        }

        const failedPaymentId = @json(old('payment_transaction_id'));
        if (failedPaymentId) {
            const trigger = document.querySelector(`[data-transaction-id="${failedPaymentId}"]`);
            if (trigger) {
                trigger.click();
                restoringPayment = true;
                document.getElementById('displayBiayaTindakan').value = formatRupiahNumber(@json(old('biaya_tindakan', 0)));
                document.getElementById('displayBiayaObat').value = formatRupiahNumber(@json(old('biaya_obat', 0)));
                document.getElementById('displayUangDibayar').value = formatRupiahNumber(@json(old('uang_dibayar', 0)));
                const oldMethod = @json(old('metode_pembayaran', 'cash'));
                selectPaymentMethod(['cash', 'qr', 'debit'].includes(oldMethod) ? oldMethod : 'cash');
                restoringPayment = false;
                calculateTotal();
                setPaymentAlert(@json($errors->all()).join(' '));
            }
        }
    </script>
</body>
</html>
