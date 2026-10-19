# Matriks Audit White Box

Dokumen ini mencatat cakupan pengujian backend yang sudah dijalankan pada branch `testing-desktop`.

| Modul | Test | Status |
|---|---|---|
| Autentikasi dan logout | `AuthenticationTest` | Teruji |
| Konfirmasi dan perubahan password | `PasswordConfirmationTest`, `PasswordUpdateTest` | Teruji |
| Verifikasi email | `EmailVerificationTest` | Teruji |
| Profil pengguna | `ProfileTest` | Teruji |
| Manajemen dokter dan pembatasan role | `DokterManagementTest` | Teruji |
| Dashboard staff dan akses halaman staff | `StaffFeatureAuditTest` | Teruji |
| Registrasi dan update pasien | `RekamMedisFlowTest`, `StaffFeatureAuditTest` | Teruji |
| Perlindungan penghapusan pasien berhistori | `StaffFeatureAuditTest` | Teruji |
| Antrean harian per poli dan pergantian hari | `KunjunganFlowTest`, `WibTimezoneTest` | Teruji |
| Pembatalan dan pengembalian antrean | `KunjunganCancellationFlowTest` | Teruji |
| Pemeriksaan dokter dan pembatasan poli | `RekamMedisFlowTest`, `PoliSpecificFlowTest` | Teruji |
| Poli Spesialis Kandungan | `PoliSpecificFlowTest` | Teruji, ditemukan isu field lintas poli |
| Poli Anak | `PoliSpecificFlowTest` | Teruji |
| Poli Gigi | `PoliSpecificFlowTest` | Teruji, ditemukan isu field lintas poli |
| Transaksi dan validasi nominal | `TransaksiFlowTest` | Teruji |
| Rekam medis, pagination, dan export PDF | `MedicalRecordExportTest` | Teruji |
| Beban data dan query terbatas | `ScalabilityGuardTest` | Teruji |
| Perlindungan pengiriman ganda pada browser | `tests/Browser/input-protection.mjs` | Tersedia, perlu server browser |
| Screenshot UI desktop dan laporan Black Box | Playwright | Belum dijalankan |
| Code coverage baris | Xdebug/PCOV | Belum tersedia di environment |

## Temuan yang belum diperbaiki

`RekamMedisController` masih menerima dan menyimpan `hasil_pemeriksaan` serta `catatan_tambahan` ketika dokter Poli Gigi atau poli non-kandungan mengirim field tersebut. Test sengaja dibuat gagal untuk membuktikan temuan ini. Perbaikan membutuhkan persetujuan terpisah.
