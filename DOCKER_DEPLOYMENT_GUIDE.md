# 🏥 Panduan Deployment E-Klinik ke Home Server via Docker

Panduan lengkap untuk memindahkan dan menjalankan aplikasi Laravel **E-Klinik Syahdan** ke Home Server tanpa perlu install PHP, MySQL, Nginx, atau Node.js manual di server.

---

## 📋 1. Persiapan di Home Server (Cukup Install Docker)

Jika di Home Server Anda (Ubuntu/Debian) belum terpasang Docker, jalankan **1 baris perintah ini**:

```bash
curl -fsSL https://get.docker.com | sh
```

Tambahkan user Anda ke grup docker agar tidak perlu ketik `sudo` terus:
```bash
sudo usermod -aG docker $USER
newgrp docker
```

---

## 📦 2. Pindahkan Folder Proyek ke Server

Pindahkan seluruh folder proyek ini ke home server (bisa via `git clone`, `scp`, `rsync`, atau copy lewat USB/Samba Share).

Masuk ke direktori proyek di server:
```bash
cd /path/ke/eklinik-syahdan
```

---

## ⚙️ 3. Konfigurasi Environment (`.env`)

Salin template environment khusus docker:
```bash
cp .env.docker.example .env
```

*Jika perlu mengubah port atau password database, Anda bisa mengedit file `.env`:*
- `APP_PORT=80` *(Port aplikasi utama, misal: http://192.168.1.50)*
- `PMA_PORT=8081` *(Port phpMyAdmin, misal: http://192.168.1.50:8081)*
- `APP_URL=http://<IP_SERVER_ANDA>` *(Ganti dengan IP lokal server, misal: http://192.168.1.50)*

---

## 🚀 4. Nyalakan Docker Container

Jalankan perintah berikut untuk membuat dan menyalakan semua container:

```bash
docker compose up -d --build
```

> **Catatan:** MariaDB akan **otomatis mengimpor database awal** dari file `db_eklinik_ta.sql` saat container database pertama kali dibuat!

---

## 🔑 5. Langkah Inisialisasi Pertama Kali

Setelah container menyala, jalankan perintah ini di terminal server (hanya sekali saat pertama setup):

```bash
# 1. Generate Application Key (jika belum ada)
docker compose exec app php artisan key:generate

# 2. Buat symlink storage upload
docker compose exec app php artisan storage:link

# 3. Jalankan migrasi database (jika ada update migration terbaru)
docker compose exec app php artisan migrate --force

# 4. Optimasi cache produksi
docker compose exec app php artisan optimize
```

---

## 🌐 6. Mengakses Aplikasi dari Laptop/HP Lain di Jaringan yang Sama

Buka browser dari perangkat mana saja yang terhubung ke WiFi/LAN yang sama:

- **Aplikasi E-Klinik**: `http://<IP_HOME_SERVER>` (Contoh: `http://192.168.1.50`)
- **phpMyAdmin (Kelola Database)**: `http://<IP_HOME_SERVER>:8081`
  - **Server**: `db`
  - **Username**: `root` atau `eklinik_user`
  - **Password**: `rootklinik123` (atau sesuai yang ada di `.env`)

---

## 🛠️ Perintah Berguna Sehari-hari

| Kebutuhan | Perintah |
| :--- | :--- |
| **Cek status semua container** | `docker compose ps` |
| **Melihat log aplikasi** | `docker compose logs -f app` |
| **Melihat log webserver** | `docker compose logs -f webserver` |
| **Restart aplikasi** | `docker compose restart` |
| **Matikan container** | `docker compose down` |
| **Jalankan perintah Artisan** | `docker compose exec app php artisan <perintah>` |
| **Masuk ke terminal container** | `docker compose exec app bash` |

---

## 💾 7. Backup Otomatis dan Salinan Perangkat Kedua

Untuk menjaga data rekam medis dan transaksi pasien tetap aman, aktifkan backup harian otomatis:

1. Beri izin eksekusi script backup:
   ```bash
   chmod +x backup-db.sh
   ```
2. Sambungkan drive atau NAS tujuan. Folder tujuan harus berada di perangkat fisik
   yang berbeda dari disk database.

3. Pasang jadwal harian secara otomatis:
   ```bash
   chmod +x install-backup-cron.sh
   BACKUP_COPY_DIR=/mnt/backup-eklinik ./install-backup-cron.sh
   ```

   Jadwal bawaan berjalan setiap pukul 23:00. Jadwal dapat diubah dengan
   `BACKUP_CRON_SCHEDULE`, misalnya `BACKUP_CRON_SCHEDULE="0 1 * * *"`.

Data backup tersimpan di `backups/` dan disalin secara atomik ke `BACKUP_COPY_DIR`.
Script memakai kunci proses agar dua backup tidak berjalan bersamaan, memvalidasi
dump dan kedua arsip, serta baru membersihkan file lama setelah backup terbaru berhasil.

Periksa log setiap pagi di `storage/logs/database-backup.log`. Jalankan satu backup
manual setelah pemasangan untuk memastikan drive tujuan dapat ditulis:

```bash
BACKUP_COPY_DIR=/mnt/backup-eklinik ./backup-db.sh
```

Uji setiap backup penting dengan restore ke database pengujian terpisah:

```bash
./verify-backup.sh ./backups/backup_eklinik_YYYYMMDD_HHMMSS.sql.gz
```

Perintah tersebut membuat database sementara dengan awalan `eklinik_restore_test_`, mengimpor backup, memeriksa keberadaan tabel inti dan integritas seluruh tabel, kemudian menghapus database pengujian secara otomatis. Database aplikasi utama tidak dijadikan target restore.

---

## ☁️ 8. Mengakses via Cloudflare Quick Tunnel (Akses dari Luar / Internet)

Proyek ini sudah dilengkapi dengan service **Cloudflare Tunnel** otomatis di dalam `docker-compose.yml` tanpa perlu install apapun di OS server!

### Cara Melihat URL Random Cloudflare:
Cukup jalankan perintah ini di terminal server:
```bash
docker compose logs -f tunnel
```

Di terminal akan muncul URL publik acak seperti:
```text
+--------------------------------------------------------------------------------------------+
|  Your quick Tunnel has been created! Visit it at (it may take some time to be reachable):  |
|  https://random-words-1234.trycloudflare.com                                               |
+--------------------------------------------------------------------------------------------+
```

### ✨ Keunggulan & Penyesuaian yang Sudah Dilakukan:
1. **Bebas Port Forwarding**: Anda bisa mengakses klinik dari luar rumah / jaringan internet tanpa perlu IP Public Static atau setting router.
2. **Anti-Error "Mixed Content" & Livewire**: Sistem Laravel sudah dikonfigurasi (`TrustProxies` dan `forceScheme('https')`) sehingga semua aset Vite, Tailwind, dan Livewire otomatis berjalan di **HTTPS** tanpa error HTTP/HTTPS mismatch.
