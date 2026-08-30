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
                <div class="w-full max-w-xl">
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input type="text" placeholder="Cari transaksi..." class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                    </div>
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
                                                         onclick="openPaymentModal({{ json_encode([
                                                             'id_transaksi' => $t->id_transaksi,
                                                             'no_antrean' => 'A-' . str_pad($t->id_kunjungan, 3, '0', STR_PAD_LEFT),
                                                             'nama_pasien' => $t->kunjungan->pasien->nama ?? '-',
                                                             'poli' => $t->kunjungan->poli_tujuan ?? '-',
                                                             'diagnosa' => $t->kunjungan->rekamMedis->diagnosa ?? '-',
                                                             'resep_obat' => $t->kunjungan->rekamMedis->resep_obat ?? '-',
                                                             'jasa_dokter' => 50000,
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
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Pembayaran Rinci -->
    <div id="paymentModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
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

                <!-- Form Rincian Transaksi -->
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Jasa Dokter / Tindakan</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="number" id="inputJasaDokter" name="biaya_tindakan" value="50000" min="0" oninput="calculateTotal()" class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-100" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Biaya Obat</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="number" id="inputBiayaObat" name="biaya_obat" value="0" min="0" oninput="calculateTotal()" class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold outline-none focus:ring-2 focus:ring-emerald-100" required />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-700">Total Biaya Tagihan</span>
                            <div class="relative w-44">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-bold">Rp</span>
                                <input type="number" id="inputTotalBiaya" readonly min="0" class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-slate-200 bg-slate-100 font-bold text-emerald-800 text-sm outline-none" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Uang Diterima (Tunai)</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm font-bold">Rp</span>
                                <input type="number" id="inputUangDibayar" name="uang_dibayar" value="0" min="0" oninput="calculateChange()" class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-emerald-300 bg-emerald-50/50 text-sm font-bold text-emerald-900 outline-none focus:ring-2 focus:ring-emerald-200" required />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider mb-1">Uang Kembalian</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">Rp</span>
                                <input type="number" id="inputKembalian" readonly min="0" class="pl-9 pr-3 py-2.5 w-full rounded-xl border border-slate-200 bg-slate-100 font-semibold text-slate-700 text-sm outline-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" onclick="closePaymentModal()" class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" class="rounded-2xl bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-md shadow-emerald-100 transition hover:bg-emerald-700">
                        Proses & Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal(data) {
            document.getElementById('modalNoAntrean').innerText = data.no_antrean;
            document.getElementById('modalPoli').innerText = data.poli;
            document.getElementById('modalNamaPasien').innerText = data.nama_pasien;
            document.getElementById('modalDiagnosa').innerText = data.diagnosa;
            document.getElementById('modalResepObat').innerText = data.resep_obat;

            document.getElementById('inputJasaDokter').value = data.jasa_dokter || 50000;
            document.getElementById('inputBiayaObat').value = 0;
            document.getElementById('inputUangDibayar').value = 0;

            const form = document.getElementById('paymentForm');
            form.action = `/transaksi/${data.id_transaksi}`;

            calculateTotal();

            const modal = document.getElementById('paymentModal');
            modal.classList.remove('hidden');
        }

        function closePaymentModal() {
            const modal = document.getElementById('paymentModal');
            modal.classList.add('hidden');
        }

        function calculateTotal() {
            const jasaDokter = parseFloat(document.getElementById('inputJasaDokter').value) || 0;
            const biayaObat = parseFloat(document.getElementById('inputBiayaObat').value) || 0;
            const total = jasaDokter + biayaObat;
            document.getElementById('inputTotalBiaya').value = total;

            calculateChange();
        }

        function calculateChange() {
            const total = parseFloat(document.getElementById('inputTotalBiaya').value) || 0;
            const uangDibayar = parseFloat(document.getElementById('inputUangDibayar').value) || 0;
            const kembalian = uangDibayar - total;
            document.getElementById('inputKembalian').value = kembalian >= 0 ? kembalian : 0;
        }
    </script>
</body>
</html>
