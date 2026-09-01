# Revisi 1 - Ringkasan Perubahan

Dokumen ini mencatat perubahan yang telah diterapkan pada aplikasi Klinik Lala Medicare.

## 1. Halaman Detail Kunjungan

- Layout detail kunjungan diubah menjadi dua kolom pada layar besar.
  - Bagian kiri menampilkan informasi pasien dan kunjungan.
  - Bagian kanan menampilkan hasil pemeriksaan.
  - Informasi pembayaran diletakkan di bawah kedua bagian tersebut.
- Tombol **Kembali ke Riwayat** dipindahkan ke bagian atas halaman agar lebih mudah dijangkau.
- Pada layar kecil, layout tetap responsif dan otomatis tampil vertikal.

## 2. Menu Pasien

- Tombol tab **Cari Pasien Lama** dan **Input Pasien Baru** kini memiliki status aktif yang jelas.
- Tab halaman aktif menggunakan warna hijau, selaras dengan indikator menu aktif pada sidebar.
- Tab yang tidak aktif tetap berwarna netral dengan efek hover.

## 3. Header Aplikasi

- Seluruh search bar yang sebelumnya berada di header atas telah dihapus karena tidak digunakan.
- Pencarian dan filter yang berada di area konten halaman tetap dipertahankan karena masih berfungsi.
- Header kini menampilkan informasi halaman di sisi kiri berupa breadcrumb dan judul halaman.
- Hari dan tanggal aktual ditampilkan di sisi kanan header.
- Tanggal menggunakan Bahasa Indonesia dan zona waktu WIB (`Asia/Jakarta`).
- Informasi header diterapkan secara konsisten pada Dashboard, Pasien, Kunjungan, Transaksi, Master Dokter, dan Rekam Medis.

## 4. Pagination

- Riwayat Transaksi dibatasi maksimal **7 data per halaman**.
- Riwayat Kunjungan dibatasi maksimal **7 data per halaman**.
- Data Pasien dibatasi maksimal **7 data per halaman**.
- Filter dan pencarian tetap terbawa saat pengguna berpindah halaman.

## 5. Perbaikan Riwayat Transaksi

- Perhitungan **Total Pendapatan** dan **Jumlah Transaksi Lunas** diperbaiki.
- Kedua ringkasan sekarang dihitung dari seluruh hasil filter, bukan hanya dari data pada halaman aktif.
- Nilai ringkasan tetap tampil dan tidak berubah saat pengguna membuka halaman pagination berikutnya, kecuali ada transaksi baru atau filter diubah.

## 6. Perbaikan Proses Pembayaran

- Pada metode pembayaran **QRIS** dan **Debit**, biaya obat dapat diisi manual.
- Nominal pembayaran QRIS dan Debit juga dapat diisi manual.
- Nominal pembayaran tidak lagi otomatis di-reset ke total tagihan saat biaya obat diubah.
- Validasi nominal pembayaran dan perhitungan total/kembalian di server tetap dipertahankan.

## Pengujian

- Seluruh perubahan tampilan telah dikompilasi melalui cache Blade tanpa error.
- Syntax controller yang diubah untuk pagination telah diperiksa tanpa error.
