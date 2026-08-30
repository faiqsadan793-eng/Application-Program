@php $user = Auth::user(); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Riwayat Transaksi - Klinik Lala Medicare</title>
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
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            main { padding: 0 !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="flex min-h-screen">
        @include('layouts.sidebar')

        <div class="flex-1 flex flex-col">
            <header class="h-20 bg-white border-b border-slate-200 px-6 flex items-center justify-between no-print">
                <div class="w-full max-w-xl">
                    <form method="GET" action="{{ route('riwayat-transaksi.index') }}" class="relative">
                        {{-- Preserve date filters when searching --}}
                        @if(request('tanggal_dari'))<input type="hidden" name="tanggal_dari" value="{{ request('tanggal_dari') }}">@endif
                        @if(request('tanggal_sampai'))<input type="hidden" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}">@endif
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama pasien atau No. Transaksi..."
                            class="w-full rounded-2xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-sm text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
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

                    <div class="mb-6">
                        <h2 class="text-2xl font-bold">Riwayat Transaksi</h2>
                        <p class="text-sm text-slate-500 mt-1">Histori transaksi pembayaran pasien dan rekapitulasi kasir.</p>
                    </div>

                    {{-- Summary Cards --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 no-print">
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                                    <span class="material-symbols-outlined text-2xl">account_balance_wallet</span>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Total Pendapatan</p>
                                    <p class="text-2xl font-bold text-slate-900">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                    <span class="material-symbols-outlined text-2xl">receipt_long</span>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Jumlah Transaksi Lunas</p>
                                    <p class="text-2xl font-bold text-slate-900">{{ $jumlahTransaksi }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Filter Bar --}}
                    <form method="GET" action="{{ route('riwayat-transaksi.index') }}" id="filter-form" class="no-print">
                        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-5 mb-6">
                            <div class="flex flex-wrap gap-4 items-end">
                                {{-- Search --}}
                                <div class="flex-1 min-w-[200px]">
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Cari</label>
                                    <div class="relative">
                                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                                        <input type="text" name="search" value="{{ request('search') }}"
                                            placeholder="Nama Pasien / No. Transaksi..."
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                                    </div>
                                </div>

                                {{-- Date from --}}
                                <div class="min-w-[160px]">
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                                    <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                                </div>

                                {{-- Date to --}}
                                <div class="min-w-[160px]">
                                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                                    <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-3 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" />
                                </div>

                                {{-- Buttons --}}
                                <div class="flex gap-2">
                                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                        <span class="material-symbols-outlined text-[18px]">filter_alt</span> Filter
                                    </button>
                                    <a href="{{ route('riwayat-transaksi.index') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                        <span class="material-symbols-outlined text-[18px]">refresh</span> Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- Transaction Table --}}
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="border-b border-slate-200 bg-slate-50 px-6 py-5 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-2xl text-emerald-600">account_balance_wallet</span>
                                <h3 class="text-lg font-semibold">Transaksi Selesai</h3>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700">
                                {{ $riwayat->total() }} transaksi
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                                <thead class="bg-slate-50 text-slate-500">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">No. Transaksi</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Tanggal & Jam</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Nama Pasien</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Diagnosa</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Total Bayar</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide">Status</th>
                                        <th class="px-6 py-4 font-semibold uppercase tracking-wide text-center no-print">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($riwayat as $t)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-6 py-5 font-bold text-slate-800">TRX-{{ str_pad($t->id_transaksi, 5, '0', STR_PAD_LEFT) }}</td>
                                            <td class="px-6 py-5 text-slate-500">{{ $t->updated_at->format('d M Y, H:i') }}</td>
                                            <td class="px-6 py-5">
                                                <p class="font-semibold text-slate-900">{{ $t->kunjungan->pasien->nama ?? '-' }}</p>
                                                <p class="text-xs text-slate-500">{{ $t->kunjungan->poli_tujuan ?? '-' }}</p>
                                            </td>
                                            <td class="px-6 py-5 text-slate-600 max-w-[180px] truncate">{{ $t->kunjungan->rekamMedis->diagnosa ?? '-' }}</td>
                                            <td class="px-6 py-5 font-bold text-slate-900">Rp {{ number_format($t->total_biaya, 0, ',', '.') }}</td>
                                            <td class="px-6 py-5">
                                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-[11px] font-bold text-emerald-700">Lunas</span>
                                            </td>
                                            <td class="px-6 py-5 text-center no-print">
                                                <button type="button"
                                                    onclick='openDetailModal({{ json_encode([
                                                        'no_transaksi' => 'TRX-' . str_pad($t->id_transaksi, 5, '0', STR_PAD_LEFT),
                                                        'tanggal' => $t->updated_at->format('d M Y, H:i') . ' WIB',
                                                        'nama_pasien' => $t->kunjungan->pasien->nama ?? '-',
                                                        'poli' => $t->kunjungan->poli_tujuan ?? '-',
                                                        'diagnosa' => $t->kunjungan->rekamMedis->diagnosa ?? '-',
                                                        'resep_obat' => $t->kunjungan->rekamMedis->resep_obat ?? '-',
                                                        'biaya_tindakan' => $t->biaya_tindakan,
                                                        'biaya_obat' => $t->biaya_obat,
                                                        'total_biaya' => $t->total_biaya,
                                                        'uang_dibayar' => $t->uang_dibayar,
                                                        'kembalian' => $t->kembalian,
                                                    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }})'
                                                    class="inline-flex items-center gap-1.5 rounded-2xl border border-emerald-300 bg-emerald-50 px-4 py-2 text-xs font-bold text-emerald-700 transition hover:bg-emerald-600 hover:text-white hover:border-emerald-600">
                                                    <span class="material-symbols-outlined text-[16px]">receipt_long</span> Detail
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center">
                                                <span class="material-symbols-outlined text-4xl text-slate-300 block mb-2">account_balance_wallet</span>
                                                <p class="text-slate-500">Belum ada riwayat transaksi.</p>
                                                <p class="text-slate-400 text-xs mt-1">Transaksi akan muncul setelah pembayaran selesai diproses.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($riwayat->hasPages())
                            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                                {{ $riwayat->withQueryString()->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </main>
        </div>
    </div>

    {{-- Detail Modal --}}
    <div id="detailModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 no-print">
        <div class="bg-white rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-200">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2 text-slate-900">
                    <span class="material-symbols-outlined text-2xl text-emerald-600">receipt_long</span>
                    <h3 class="font-bold text-lg">Detail Transaksi</h3>
                </div>
                <button onclick="closeDetailModal()" class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto custom-scrollbar" id="printableArea">
                {{-- Info Transaksi --}}
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-200 text-sm">
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">No. Transaksi</p>
                        <p id="detailNoTransaksi" class="font-bold text-slate-800 text-base mt-0.5">-</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Tanggal & Waktu</p>
                        <p id="detailTanggal" class="font-semibold text-slate-800 mt-0.5">-</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Nama Pasien</p>
                        <p id="detailNamaPasien" class="font-bold text-slate-900 mt-0.5">-</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Poli Tujuan</p>
                        <p id="detailPoli" class="font-semibold text-slate-800 mt-0.5">-</p>
                    </div>
                </div>

                {{-- Info Medis --}}
                <div class="space-y-2 bg-sky-50/50 p-4 rounded-2xl border border-sky-100 text-sm">
                    <div>
                        <p class="text-xs text-sky-700 font-semibold uppercase tracking-wider">Diagnosa</p>
                        <p id="detailDiagnosa" class="text-slate-800 mt-0.5 font-medium">-</p>
                    </div>
                    <div class="border-t border-sky-100 pt-2">
                        <p class="text-xs text-sky-700 font-semibold uppercase tracking-wider">Resep Obat</p>
                        <p id="detailResepObat" class="text-slate-800 mt-0.5 font-medium whitespace-pre-line">-</p>
                    </div>
                </div>

                {{-- Rincian Biaya --}}
                <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100 text-sm space-y-2">
                    <p class="text-xs text-emerald-700 font-bold uppercase tracking-wider mb-2">Rincian Pembayaran</p>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Jasa Dokter / Tindakan</span>
                        <span id="detailBiayaTindakan" class="font-semibold text-slate-800">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Biaya Obat</span>
                        <span id="detailBiayaObat" class="font-semibold text-slate-800">Rp 0</span>
                    </div>
                    <div class="flex justify-between border-t border-emerald-200 pt-2 mt-2">
                        <span class="font-bold text-emerald-800">Total Biaya</span>
                        <span id="detailTotalBiaya" class="font-bold text-emerald-800">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Uang Diterima</span>
                        <span id="detailUangDibayar" class="font-semibold text-slate-800">Rp 0</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-600">Kembalian</span>
                        <span id="detailKembalian" class="font-semibold text-slate-800">Rp 0</span>
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex justify-center">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-bold text-emerald-700">
                        <span class="material-symbols-outlined text-[16px]">check_circle</span> Transaksi Lunas
                    </span>
                </div>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4 flex justify-end gap-3">
                <button type="button" onclick="closeDetailModal()" class="rounded-2xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Tutup
                </button>
                <button type="button" onclick="printReceipt()" class="rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 inline-flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[18px]">print</span> Cetak Struk
                </button>
            </div>
        </div>
    </div>

    <script>
        function formatRupiah(angka) {
            const num = parseFloat(angka) || 0;
            return 'Rp ' + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        function openDetailModal(data) {
            document.getElementById('detailNoTransaksi').innerText = data.no_transaksi;
            document.getElementById('detailTanggal').innerText = data.tanggal;
            document.getElementById('detailNamaPasien').innerText = data.nama_pasien;
            document.getElementById('detailPoli').innerText = data.poli;
            document.getElementById('detailDiagnosa').innerText = data.diagnosa;
            document.getElementById('detailResepObat').innerText = data.resep_obat;

            document.getElementById('detailBiayaTindakan').innerText = formatRupiah(data.biaya_tindakan);
            document.getElementById('detailBiayaObat').innerText = formatRupiah(data.biaya_obat);
            document.getElementById('detailTotalBiaya').innerText = formatRupiah(data.total_biaya);
            document.getElementById('detailUangDibayar').innerText = formatRupiah(data.uang_dibayar);
            document.getElementById('detailKembalian').innerText = formatRupiah(data.kembalian);

            document.getElementById('detailModal').classList.remove('hidden');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.add('hidden');
        }

        function printReceipt() {
            const receiptContent = document.getElementById('printableArea').innerHTML;
            const transactionNumber = document.getElementById('detailNoTransaksi').textContent;
            const printWindow = window.open('', '_blank', 'width=720,height=900');

            if (!printWindow) {
                alert('Jendela cetak tidak dapat dibuka. Izinkan pop-up pada browser, lalu coba lagi.');
                return;
            }

            printWindow.document.write(`
                <!DOCTYPE html>
                <html lang="id">
                <head>
                    <meta charset="utf-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <title>Struk ${transactionNumber}</title>
                    <style>
                        * { box-sizing: border-box; }
                        body { margin: 0; padding: 24px; color: #1e293b; font-family: Arial, sans-serif; font-size: 12px; }
                        .receipt { max-width: 620px; margin: 0 auto; }
                        .receipt-header { margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #059669; text-align: center; }
                        .receipt-header h1 { margin: 0; color: #065f46; font-size: 18px; }
                        .receipt-header p { margin: 5px 0 0; color: #64748b; }
                        .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
                        .space-y-4 > * + * { margin-top: 16px; }
                        .space-y-2 > * + * { margin-top: 8px; }
                        .bg-slate-50, .bg-sky-50\\/50, .bg-emerald-50\\/50 { border: 1px solid #cbd5e1; border-radius: 12px; padding: 14px; }
                        .bg-sky-50\\/50 { border-color: #bae6fd; background: #f0f9ff; }
                        .bg-emerald-50\\/50 { border-color: #a7f3d0; background: #ecfdf5; }
                        .text-xs { font-size: 10px; }
                        .font-bold { font-weight: 700; }
                        .font-semibold, .font-medium { font-weight: 600; }
                        .uppercase { text-transform: uppercase; }
                        .tracking-wider { letter-spacing: .05em; }
                        .text-slate-500, .text-slate-600 { color: #64748b; }
                        .text-slate-800, .text-slate-900 { color: #1e293b; }
                        .text-sky-700 { color: #0369a1; }
                        .text-emerald-700, .text-emerald-800 { color: #047857; }
                        .flex { display: flex; }
                        .justify-between { justify-content: space-between; }
                        .justify-center { justify-content: center; }
                        .items-center { align-items: center; }
                        .gap-1\\.5 { gap: 6px; }
                        .border-t { border-top: 1px solid #a7f3d0; }
                        .pt-2 { padding-top: 8px; }
                        .mt-2 { margin-top: 8px; }
                        .whitespace-pre-line { white-space: pre-line; }
                        .rounded-full { border-radius: 9999px; }
                        .bg-emerald-100 { background: #d1fae5; }
                        .px-4 { padding-left: 16px; padding-right: 16px; }
                        .py-1\\.5 { padding-top: 6px; padding-bottom: 6px; }
                        .receipt-footer { margin-top: 20px; color: #64748b; text-align: center; }
                        .material-symbols-outlined { display: none; }
                        @media print { body { padding: 0; } }
                    </style>
                </head>
                <body>
                    <div class="receipt">
                        <div class="receipt-header">
                            <h1>Klinik Lala Medicare</h1>
                            <p>Struk Pembayaran Pasien</p>
                        </div>
                        ${receiptContent}
                        <p class="receipt-footer">Terima kasih. Simpan struk ini sebagai bukti pembayaran.</p>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            window.setTimeout(() => {
                printWindow.focus();
                printWindow.print();
                printWindow.onafterprint = () => printWindow.close();
            }, 250);
        }
    </script>
</body>
</html>
