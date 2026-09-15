# Runbook Deploy dan Restore E-Klinik

Dokumen ini adalah prosedur operasional untuk deploy aplikasi dan memulihkan database. Jalankan perintah dari folder proyek. Jangan menjalankan `migrate:fresh`, `db:wipe`, atau mengimpor dump ke database produksi tanpa backup dan pemeriksaan nama database.

## 1. Pemeriksaan sebelum deploy

1. Pastikan kode yang akan dirilis sudah ditinjau dan diuji di lingkungan staging.
2. Pastikan akses database produksi, Docker/Compose, PHP, Composer, dan Node tersedia.
3. Pastikan folder backup utama dan perangkat salinan kedua berada pada lokasi berbeda.
4. Pastikan nilai berikut sudah diisi di `.env` produksi dan tidak berasal dari `.env.example`:

   ```dotenv
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=<kunci-unik-produksi>
   APP_URL=https://domain-klinik.example
   DB_CONNECTION=mysql
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=<nama-database-produksi>
   DB_USERNAME=<pengguna-database>
   DB_PASSWORD=<password-kuat>
   SESSION_DRIVER=database
   CACHE_STORE=database
   ```

   Jangan menyimpan `.env`, password database, atau password akun di Git.

## 2. Backup sebelum deploy

Jalankan backup sebelum migration atau perubahan konfigurasi:

```bash
BACKUP_COPY_DIR=/mnt/backup-eksternal ./backup-db.sh
```

Proses hanya dianggap berhasil bila dump, kompresi gzip, dan salinan kedua semuanya berhasil. File backup lama tidak dihapus ketika salah satu tahap gagal.

Setelah file terbentuk, lakukan verifikasi restore ke database pengujian terpisah:

```bash
./verify-backup.sh backups/backup_eklinik_YYYYMMDD_HHMMSS.sql.gz
```

Jangan mengarahkan `verify-backup.sh` ke nama database produksi. Script membuat dan menghapus database pengujian dengan nama khusus.

## 3. Deploy aplikasi

Jalankan secara berurutan:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Migration hanya menambahkan perubahan yang belum tercatat. Jangan memakai `migrate:fresh` atau `migrate:refresh` di production.

Setelah migration, pastikan tabel inti dan akun operasional tersedia:

```bash
php artisan migrate:status
php artisan tinker --execute="dump(App\\Models\\User::count(), App\\Models\\Dokter::count());"
```

Akun dokter harus memiliki baris profil pada tabel `dokters` dan poli yang valid. Jangan memakai password bersama seperti `password123`; buat password unik melalui prosedur administrasi yang aman dan ubah setelah login pertama.

## 4. Pemeriksaan setelah deploy

1. Buka halaman login dan dashboard.
2. Login sebagai staff dan pastikan menu pasien, kunjungan, transaksi, serta riwayat tampil.
3. Login sebagai dokter dan pastikan hanya antrean polinya yang terlihat.
4. Buat satu kunjungan uji, mulai pemeriksaan, simpan rekam medis, dan pastikan transaksi terbentuk.
5. Selesaikan pembayaran uji dan pastikan status kunjungan berubah menjadi selesai.
6. Hapus atau tandai data uji sesuai kebijakan klinik; jangan menghapus data produksi.
7. Periksa `storage/logs/laravel.log` dan log web server untuk error baru.

## 5. Prosedur restore darurat

1. Hentikan akses aplikasi atau aktifkan maintenance mode.
2. Ambil backup terbaru yang sudah lolos `verify-backup.sh`.
3. Backup kondisi database saat ini sebelum restore agar dapat dilakukan rollback.
4. Pastikan nama database target benar, lalu impor hanya ke database target:

   ```bash
   gzip -dc backups/backup_eklinik_YYYYMMDD_HHMMSS.sql.gz | \
     docker compose exec -T db sh -eu -c \
     'MYSQL_PWD="$MYSQL_ROOT_PASSWORD" mariadb --user=root "$MYSQL_DATABASE"'
   ```

5. Jalankan `php artisan migrate --force` bila backup berasal dari versi aplikasi yang lebih lama.
6. Jalankan pemeriksaan login, dashboard, kunjungan, rekam medis, dan transaksi.
7. Nonaktifkan maintenance mode setelah data dan alur utama tervalidasi.

Restore harus dilakukan oleh administrator yang ditunjuk. Jangan menguji restore langsung pada database produksi.

## 6. Kriteria deploy dianggap berhasil

Deploy dianggap berhasil hanya jika:

- `APP_DEBUG=false` aktif;
- migration selesai tanpa error;
- akun staff dan profil dokter tersedia;
- backup sebelum deploy dapat direstore ke database pengujian;
- login staff dan dokter berhasil;
- alur kunjungan, rekam medis, pembayaran, dan PDF dapat dibuka;
- tidak ada error baru pada log aplikasi dan web server.

