# Software Requirements Specification (SRS)
## Aplikasi Klinik Lala

**Versi dokumen:** 1.0  
**Tujuan dokumen:** Menjelaskan fungsi aplikasi Klinik Lala dengan bahasa sederhana agar dapat menjadi acuan bersama bagi pemilik klinik, staf, dokter, dan tim pengembang.

---

## 1. Gambaran Singkat Aplikasi

Aplikasi Klinik Lala adalah sistem pencatatan operasional klinik berbasis web. Sistem ini membantu proses pelayanan pasien, mulai dari pendaftaran, antrean pemeriksaan, pencatatan hasil pemeriksaan dokter, hingga pembayaran di kasir.

Tujuan utamanya adalah agar data pasien, rekam medis, antrean, dan transaksi tersimpan rapi dalam satu sistem. Dengan demikian, pekerjaan staff dan dokter menjadi lebih terarah, serta riwayat pelayanan pasien lebih mudah dicari kembali.

---

## 2. Tujuan Sistem

Sistem ini dibuat untuk:

- Mempercepat proses pendaftaran pasien dan pencarian data pasien.
- Mengatur antrean pasien berdasarkan poli tujuan.
- Membantu dokter melihat pasien yang perlu diperiksa pada polinya.
- Menyimpan keluhan, diagnosis, dan resep obat sebagai rekam medis.
- Mencatat biaya pemeriksaan, obat, uang pembayaran, dan kembalian.
- Menyediakan riwayat kunjungan, rekam medis, dan transaksi sebagai bahan pengecekan.

---

## 3. Pengguna dan Hak Akses

Aplikasi memiliki dua jenis pengguna, yaitu **Staff/Kasir** dan **Dokter**. Setiap pengguna login menggunakan akun masing-masing. Menu yang muncul akan menyesuaikan tugas pengguna tersebut.

| Pengguna | Tanggung jawab utama | Batasan akses |
| --- | --- | --- |
| Staff/Kasir | Mengelola data dokter dan pasien, mendaftarkan kunjungan, memantau antrean, serta menerima pembayaran. | Tidak dapat mengisi hasil pemeriksaan atau rekam medis baru. |
| Dokter | Memeriksa pasien pada poli yang menjadi tanggung jawabnya dan mengisi rekam medis. | Tidak dapat mengelola data pasien, data dokter, maupun transaksi. Dokter hanya melihat antrean dan rekam medis pada polinya sendiri. |

Semua pengguna yang sudah login dapat memperbarui profil dan kata sandinya sendiri, lalu keluar dari sistem dengan aman.

---

## 4. Fitur untuk Staff/Kasir

### 4.1 Dashboard Staff

Halaman awal staff menampilkan ringkasan kondisi klinik hari ini, seperti:

- Jumlah seluruh pasien yang sudah terdaftar.
- Jumlah pasien yang masih mengantre atau menunggu dokter.
- Jumlah pasien yang sudah selesai diperiksa dan menunggu pembayaran.
- Daftar antrean kunjungan hari ini.

### 4.2 Master Data Dokter

Staff dapat mengelola akun dokter dan informasi dokternya dalam satu tempat.

- Menambah dokter baru beserta akun login.
- Mengubah nama, email, kata sandi, nomor NIP/SIP, dan poli dokter.
- Menghapus akun dokter bila sudah tidak digunakan.
- Menentukan poli dokter: Poli Umum, Poli Gigi, atau Poli Anak.

### 4.3 Data Pasien dan Pendaftaran

Staff dapat mengelola data pasien sekaligus melakukan pendaftaran kunjungan.

- Menambah pasien baru.
- Sistem membuat nomor rekam medis pasien secara otomatis, misalnya `RM-000001`.
- Mengisi data penting pasien: nama, tanggal lahir, jenis kelamin, nomor telepon, NIK, pekerjaan, dan alamat.
- Memilih poli tujuan saat pasien baru didaftarkan.
- Mengubah data pasien bila ada kesalahan atau perubahan data.
- Mencari pasien berdasarkan nama, nomor rekam medis, NIK, nomor telepon, atau nomor data pasien.
- Menghapus pasien yang belum memiliki riwayat kunjungan. Pasien yang sudah pernah berkunjung tidak dapat dihapus agar riwayat layanan tetap aman.

### 4.4 Pendaftaran Kunjungan dan Antrean

Untuk pasien lama, staff dapat membuat kunjungan baru dengan memilih poli tujuan. Sistem akan memasukkan pasien ke antrean pada hari yang sama.

Aturan penting antrean:

- Satu pasien hanya boleh memiliki satu kunjungan yang masih aktif dalam satu hari.
- Pasien baru dapat mendaftar kembali setelah kunjungan sebelumnya selesai, termasuk bila masih di hari yang sama.
- Antrean diproses berdasarkan urutan pasien yang lebih dulu terdaftar.
- Status kunjungan akan berubah mengikuti proses pelayanan: **Antre → Sedang diperiksa → Siap bayar → Selesai**.

### 4.5 Riwayat Kunjungan

Staff dapat melihat daftar kunjungan pasien, termasuk data pasien, poli tujuan, status layanan, hasil pemeriksaan, dan transaksi bila tersedia.

Riwayat dapat disaring berdasarkan:

- Nama pasien, NIK, nomor rekam medis, atau nomor kunjungan.
- Periode waktu, misalnya 7 hari terakhir, 30 hari terakhir, bulan berjalan, atau seluruh data.
- Poli tujuan.
- Status kunjungan.

### 4.6 Rekam Medis Pasien

Staff dapat melihat riwayat rekam medis seluruh pasien untuk keperluan administrasi dan pencarian data. Rekam medis berisi informasi dokter pemeriksa, keluhan, diagnosis, dan resep obat.

Staff hanya dapat melihat riwayat tersebut; pengisian hasil pemeriksaan tetap dilakukan oleh dokter.

### 4.7 Pembayaran / Kasir

Setelah dokter menyelesaikan pemeriksaan, data pasien akan masuk ke daftar pembayaran.

Staff/Kasir dapat:

- Melihat pasien yang menunggu pembayaran.
- Mengisi biaya tindakan dan biaya obat.
- Mengisi nominal uang yang diterima dari pasien.
- Melihat total biaya dan kembalian yang dihitung otomatis oleh sistem.
- Menyelesaikan transaksi pembayaran.

Sistem tidak mengizinkan pembayaran bila uang yang diberikan lebih kecil daripada total tagihan. Transaksi yang sudah lunas juga tidak dapat diproses ulang.

### 4.8 Riwayat Transaksi

Staff dapat melihat transaksi yang sudah lunas, mencari transaksi berdasarkan nama pasien atau nomor transaksi, serta menyaringnya berdasarkan rentang tanggal.

Halaman ini juga menampilkan jumlah transaksi dan total pendapatan dari data yang sedang ditampilkan.

---

## 5. Fitur untuk Dokter

### 5.1 Dashboard Dokter

Dokter melihat ringkasan pelayanan untuk polinya sendiri pada hari tersebut, yaitu:

- Jumlah pasien pada poli dokter hari ini.
- Jumlah pasien yang masih menunggu pemeriksaan.
- Jumlah pasien yang sudah selesai diperiksa.
- Daftar antrean pasien pada poli dokter.

### 5.2 Antrean Pemeriksaan

Dokter hanya dapat melihat pasien yang terdaftar pada poli tempat dokter bertugas.

Dokter dapat:

- Memanggil pasien berikutnya dari antrean.
- Memulai pemeriksaan pada pasien tertentu.
- Melihat pasien yang sedang diperiksa.

Dalam satu poli, sistem hanya mengizinkan satu pasien berstatus sedang diperiksa pada saat yang sama. Hal ini membantu mencegah dua pasien diproses bersamaan secara tidak sengaja.

### 5.3 Pengisian Rekam Medis

Setelah pemeriksaan, dokter mengisi:

- Keluhan pasien.
- Diagnosis dokter.
- Resep obat atau anjuran obat.

Saat rekam medis disimpan, sistem akan menyimpan nama dokter yang memeriksa, mengubah status kunjungan menjadi **Siap bayar**, dan membuat data tagihan untuk diproses oleh kasir.

Satu kunjungan hanya dapat memiliki satu rekam medis. Rekam medis tidak dapat diisi untuk pasien dari poli lain.

### 5.4 Riwayat Rekam Medis

Dokter dapat mencari dan melihat rekam medis pasien pada polinya sendiri. Dokter tidak dapat melihat rekam medis dari poli lain melalui akun dokternya.

---

## 6. Alur Penggunaan Aplikasi

Berikut alur pelayanan pasien dari awal hingga selesai:

1. **Login**  
   Staff atau dokter membuka aplikasi dan masuk menggunakan email serta kata sandi masing-masing.

2. **Halaman awal sesuai peran**  
   Setelah login, sistem menampilkan dashboard sesuai pengguna: dashboard staff untuk Staff/Kasir dan dashboard dokter untuk Dokter.

3. **Pendaftaran pasien oleh Staff**  
   Staff mencari data pasien terlebih dahulu. Jika pasien belum ada, staff membuat data pasien baru dan memilih poli tujuan. Sistem membuat nomor rekam medis serta langsung memasukkan pasien ke antrean.

4. **Pendaftaran ulang pasien lama**  
   Jika pasien sudah pernah terdaftar, staff cukup mencari pasien lalu membuat kunjungan baru dan memilih poli tujuan.

5. **Menunggu antrean dokter**  
   Pasien masuk ke antrean pada poli yang dipilih. Dokter pada poli tersebut melihat antrean dari akunnya.

6. **Pemeriksaan oleh Dokter**  
   Dokter memanggil atau memulai pemeriksaan pasien. Dokter mengisi keluhan, diagnosis, dan resep obat, kemudian menyimpan hasil pemeriksaan.

7. **Pasien menuju kasir**  
   Setelah hasil pemeriksaan disimpan, status pasien menjadi siap bayar dan data tagihan muncul pada menu transaksi Staff/Kasir.

8. **Pembayaran oleh Staff/Kasir**  
   Kasir mengisi biaya tindakan, biaya obat, dan uang yang dibayarkan pasien. Sistem menghitung total serta kembalian secara otomatis.

9. **Layanan selesai**  
   Setelah pembayaran berhasil dicatat, status kunjungan menjadi selesai. Data kunjungan, rekam medis, dan transaksi tetap tersimpan sebagai riwayat.

10. **Logout**  
    Setelah selesai menggunakan aplikasi, pengguna memilih menu keluar agar sesi akun ditutup dengan aman.

---

## 7. Data yang Disimpan Sistem

Sistem menyimpan data berikut:

| Kelompok data | Isi utama |
| --- | --- |
| Akun pengguna | Nama, email, kata sandi yang tersimpan aman, dan peran pengguna. |
| Data dokter | Akun dokter, NIP/SIP, serta poli tempat dokter bertugas. |
| Data pasien | Nomor rekam medis, identitas pasien, kontak, pekerjaan, dan alamat. |
| Kunjungan | Pasien yang datang, tanggal kunjungan, poli tujuan, dan status pelayanan. |
| Rekam medis | Dokter pemeriksa, keluhan, diagnosis, dan resep obat. |
| Transaksi | Biaya tindakan, biaya obat, total tagihan, uang yang dibayar, kembalian, dan status pembayaran. |

---

## 8. Aturan Penting Sistem

- Setiap menu dibatasi berdasarkan jenis akun agar tugas staff dan dokter tidak tercampur.
- Dokter hanya dapat menangani antrean dan melihat riwayat rekam medis pada polinya sendiri.
- Nomor rekam medis bersifat tetap untuk setiap pasien.
- Riwayat kunjungan, rekam medis, dan transaksi tidak boleh hilang hanya karena data pasien akan dihapus.
- Pasien tidak dapat memiliki dua antrean aktif pada hari yang sama.
- Satu kunjungan hanya memiliki satu rekam medis dan satu transaksi.
- Pembayaran hanya dapat dilakukan setelah pemeriksaan selesai.
- Total tagihan dan kembalian dihitung oleh sistem untuk mengurangi kesalahan perhitungan.

---

## 9. Metodologi Pengembangan

Pengembangan aplikasi menggunakan pendekatan **Waterfall sederhana**. Pendekatan ini dilakukan secara bertahap agar kebutuhan klinik dapat diterjemahkan menjadi sistem yang jelas.

Tahapannya adalah:

1. **Analisis kebutuhan** — Mengidentifikasi proses pelayanan klinik dan kebutuhan dari Staff/Kasir maupun Dokter.
2. **Perancangan** — Menyusun alur aplikasi, pembagian hak akses, data yang perlu disimpan, dan tampilan halaman.
3. **Pembangunan** — Membuat fitur aplikasi sesuai rancangan yang telah disepakati.
4. **Pengujian** — Memeriksa alur penting, seperti login, pendaftaran pasien, antrean, pemeriksaan, rekam medis, dan pembayaran.
5. **Penerapan dan evaluasi** — Aplikasi digunakan oleh klinik, lalu dapat dievaluasi untuk pengembangan berikutnya bila ada kebutuhan baru.

---

## 10. Teknologi yang Digunakan

Teknologi berikut digunakan untuk membangun aplikasi:

| Teknologi | Kegunaan sederhana |
| --- | --- |
| Laravel 13 | Kerangka utama aplikasi untuk mengatur halaman, proses bisnis, login, dan keamanan akses. |
| PHP 8.3 | Bahasa pemrograman yang menjalankan proses aplikasi di server. |
| SQLite / basis data yang dikonfigurasi | Menyimpan data pasien, dokter, kunjungan, rekam medis, dan transaksi. |
| Blade | Membuat tampilan halaman aplikasi. |
| Tailwind CSS | Membuat tampilan aplikasi lebih rapi dan nyaman digunakan. |
| JavaScript dan Alpine.js | Membantu interaksi sederhana pada tampilan halaman. |
| Vite | Membantu pengelolaan dan penyusunan aset tampilan aplikasi. |
| PHPUnit | Membantu menguji fungsi-fungsi penting aplikasi secara otomatis. |

Teknologi tersebut berjalan pada aplikasi web, sehingga pengguna cukup membuka aplikasi melalui browser tanpa perlu memasang aplikasi khusus di setiap komputer.

---

## 11. Batasan Versi Saat Ini

Dokumen ini menjelaskan fungsi yang tersedia pada versi aplikasi saat ini. Beberapa hal yang belum menjadi bagian dari fungsi utama versi ini antara lain pendaftaran mandiri oleh pasien, cetak struk, stok obat, jadwal praktik dokter, dan laporan yang dapat diunduh. Fitur tersebut dapat dipertimbangkan sebagai pengembangan lanjutan bila diperlukan klinik.

---

## 12. Penutup

Aplikasi Klinik Lala dirancang untuk membuat proses layanan klinik lebih tertib: staff menangani pendaftaran dan pembayaran, dokter menangani pemeriksaan, sedangkan seluruh riwayat pelayanan tersimpan dalam sistem. Dengan pembagian tugas yang jelas, pelayanan pasien dapat dilakukan lebih cepat dan data klinik lebih mudah ditelusuri.
