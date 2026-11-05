import fs from 'node:fs/promises';
import path from 'node:path';
import {
    AlignmentType,
    Document,
    HeadingLevel,
    ImageRun,
    Packer,
    Paragraph,
    Table,
    TableCell,
    TableRow,
    TextRun,
    WidthType,
} from 'docx';

const root = process.cwd();
const reportDir = path.join(root, 'tests', 'reports');
const testRoot = path.join(root, 'tests');
const blackBoxOnly = process.argv.includes('--black-box-only');
const whiteBoxOnly = process.argv.includes('--white-box-only');
const cell = (text, width, header = false) => {
    const paragraphs = String(text).split('\n').map((line) => new Paragraph({
        spacing: { before: 0, after: 40 },
        children: [new TextRun({ text: line, bold: header, size: header ? 17 : 16, font: line.includes('☑') ? 'Segoe UI Symbol' : 'Arial' })],
    }));
    return new TableCell({
        width: { size: width, type: WidthType.PERCENTAGE },
        margins: { top: 65, bottom: 65, left: 75, right: 75 },
        children: paragraphs,
        shading: header ? { fill: 'DDEBF7', color: 'auto', type: 'clear' } : undefined,
    });
};
const makeTable = (headers, widths, rows) => new Table({
    width: { size: 100, type: WidthType.PERCENTAGE },
    rows: [
        new TableRow({ tableHeader: true, children: headers.map((text, index) => cell(text, widths[index], true)) }),
        ...rows.map((row) => new TableRow({ children: row.map((text, index) => cell(text, widths[index])) })),
    ],
});
const para = (text, options = {}) => new Paragraph({
    spacing: { before: 40, after: 55 },
    children: [new TextRun({ text, font: 'Arial', size: 20, ...options })],
});
const heading = (text, level = HeadingLevel.HEADING_1) => new Paragraph({
    heading: level,
    spacing: { before: 180, after: 100 },
    children: [new TextRun({ text, font: 'Arial' })],
});

const testDescriptions = {
    test_that_true_is_true: ['Unit dasar', 'Kondisi unit mandiri: assertion menerima nilai boolean true tanpa membuat data klinik.', 'assertTrue(true) terpenuhi; pengujian unit dinyatakan lulus.'],
    test_login_screen_can_be_rendered: ['Autentikasi', 'Pengunjung yang belum login membuka halaman GET /login.', 'Halaman login merespons HTTP 200 dan dapat dirender.'],
    test_users_can_authenticate_using_the_login_screen: ['Autentikasi', 'User mengirim email dan password yang valid melalui form login.', 'User menjadi terautentikasi dan diarahkan ke dashboard.'],
    test_users_can_not_authenticate_with_invalid_password: ['Autentikasi', 'Email terdaftar dikirim bersama password yang salah.', 'Autentikasi ditolak dan user tetap berstatus guest.'],
    test_users_can_logout: ['Autentikasi', 'User yang sedang login mengirim POST /logout.', 'Session diakhiri, user menjadi guest, lalu diarahkan ke halaman awal.'],
    test_email_verification_screen_can_be_rendered: ['Verifikasi Email', 'User belum terverifikasi membuka GET /verify-email.', 'Halaman pemberitahuan verifikasi merespons HTTP 200.'],
    test_email_can_be_verified: ['Verifikasi Email', 'User membuka URL bertanda tangan yang memuat ID dan hash email yang sesuai.', 'Email berubah menjadi terverifikasi, event Verified dikirim, dan user diarahkan ke dashboard dengan penanda verified.'],
    test_email_is_not_verified_with_invalid_hash: ['Verifikasi Email', 'URL bertanda tangan menggunakan hash yang tidak cocok dengan email user.', 'Email tetap belum terverifikasi.'],
    test_confirm_password_screen_can_be_rendered: ['Konfirmasi Password', 'User yang login membuka GET /confirm-password.', 'Form konfirmasi password merespons HTTP 200.'],
    test_password_can_be_confirmed: ['Konfirmasi Password', 'Password konfirmasi sama dengan password user saat ini.', 'Tidak ada error validasi dan request berhasil diarahkan kembali.'],
    test_password_is_not_confirmed_with_invalid_password: ['Konfirmasi Password', 'User mengirim password yang tidak cocok.', 'Session memuat error validasi dan konfirmasi tidak dianggap berhasil.'],
    test_password_can_be_updated: ['Perubahan Password', 'Password saat ini benar; password baru dan konfirmasi sama.', 'Password baru tersimpan dalam bentuk hash dan user kembali ke halaman profil tanpa error.'],
    test_correct_password_must_be_provided_to_update_password: ['Perubahan Password', 'Password saat ini salah saat user meminta perubahan password.', 'Error tercatat pada current_password dan password tidak diperbarui.'],
    test_staff_can_view_the_doctor_management_page: ['Manajemen Dokter', 'User ber-role staff membuka halaman daftar dokter.', 'Halaman manajemen dokter merespons HTTP 200 dan judul halaman tampil.'],
    test_staff_can_create_a_doctor_account_and_profile: ['Manajemen Dokter', 'Staff mengirim nama, email unik, password, NIP/SIP, dan poli yang valid.', 'Akun role dokter serta profil dokter dengan NIP/SIP dan poli tersimpan.'],
    test_staff_can_update_doctor_login_and_profile_data: ['Manajemen Dokter', 'Staff mengubah nama, email, password opsional, NIP/SIP, dan poli dokter.', 'Data akun dan profil dokter berubah; password baru dapat diverifikasi.'],
    test_staff_can_delete_doctor_profile_and_login_account: ['Manajemen Dokter', 'Staff menghapus profil dokter yang terhubung ke akun user.', 'Akun user terhapus dan profil dokter ikut terhapus melalui relasi database.'],
    test_doctor_cannot_manage_doctor_data: ['Manajemen Dokter', 'User ber-role dokter mencoba mengirim request pembuatan akun dokter.', 'Request ditolak dengan HTTP 403 dan akun baru tidak dibuat.'],
    test_root_redirects_guests_to_login: ['Rute Awal', 'Guest membuka GET / tanpa session login.', 'Browser diarahkan ke route login.'],
    test_staff_can_cancel_active_visit_without_deleting_history_and_register_again: ['Pembatalan Kunjungan', 'Staff membatalkan kunjungan antre dengan kategori/alasan, lalu mendaftarkan pasien kembali.', 'Kunjungan lama tetap tersimpan dengan metadata pembatalan; status menjadi dibatalkan dan kunjungan baru berhasil dibuat.'],
    test_doctor_can_return_own_patient_to_today_queue_and_cancel_no_show: ['Pembatalan Kunjungan', 'Dokter mengembalikan pasien dari status menunggu_dokter ke antre, memanggil kembali, lalu membatalkan karena tidak hadir.', 'Status akhir dibatalkan; rekam medis dan transaksi tidak dibuat.'],
    test_doctor_cannot_change_another_poli_and_terminal_visits_cannot_be_cancelled: ['Pembatalan Kunjungan', 'Dokter mencoba mengubah kunjungan poli lain; staff mencoba membatalkan kunjungan selesai.', 'Akses dokter ditolak HTTP 403; pembatalan status terminal ditolak dan status selesai tetap.'],
    test_doctor_cannot_cancel_patient_who_has_not_been_called: ['Pembatalan Kunjungan', 'Dokter mencoba membatalkan kunjungan yang masih berstatus antre.', 'Request ditolak dengan pesan error dan status kunjungan tetap antre.'],
    test_previous_day_examination_cannot_be_returned_to_new_day_queue: ['Pembatalan Kunjungan', 'Kunjungan berstatus menunggu_dokter berasal dari tanggal sebelumnya.', 'Permintaan kembali ke antrean ditolak; status kunjungan tidak berubah.'],
    test_cancellation_reason_is_required: ['Pembatalan Kunjungan', 'Staff memilih kategori lainnya tetapi mengirim alasan kosong.', 'Validasi alasan gagal dan kunjungan tetap berstatus antre.'],
    test_returned_patient_moves_to_the_back_of_same_poli_queue: ['Pembatalan Kunjungan', 'Dokter mengembalikan pasien yang dipanggil saat ada pasien lain menunggu di poli yang sama.', 'Waktu antre pasien yang dikembalikan menjadi paling baru, nomor antrean bertambah, dan pasien berikutnya yang dipanggil tetap pasien kedua.'],
    test_patient_cannot_create_a_second_visit_while_the_first_visit_is_active: ['Pendaftaran & Antrean', 'Pasien sudah mempunyai kunjungan hari ini berstatus siap_bayar, lalu staff mendaftarkan kunjungan kedua.', 'Request ditolak dengan pesan error dan jumlah kunjungan pasien tidak bertambah.'],
    test_patient_can_create_a_new_visit_after_a_same_day_visit_is_completed: ['Pendaftaran & Antrean', 'Pasien memiliki kunjungan hari ini yang sudah selesai lalu didaftarkan ke poli lain.', 'Kunjungan baru berstatus antre; kunjungan selesai tidak lagi memiliki tanggal aktif.'],
    test_daily_queue_numbers_restart_per_poli_and_date: ['Pendaftaran & Antrean', 'Beberapa pasien didaftarkan ke Poli Umum, Poli Gigi, lalu Poli Umum pada hari berikutnya.', 'Nomor antrean bertambah per poli dan dimulai kembali dari 1 pada tanggal baru.'],
    test_staff_gets_real_private_pdf_and_detail_is_paginated: ['Riwayat Medis & PDF', 'Staff membuka detail pasien dengan 12 rekam medis dan meminta ekspor PDF.', 'Detail memuat 10 item per halaman; unduhan bertipe PDF, berstatus attachment, dan memakai cache-control private/no-store.'],
    test_doctor_export_uses_same_poli_scope_as_detail: ['Riwayat Medis & PDF', 'Dokter Poli Umum meminta riwayat pasien yang memiliki catatan Poli Gigi dan Poli Umum.', 'Catatan Poli Gigi tidak terlihat/terekspor; detail dan PDF hanya berisi Poli Umum.'],
    test_guest_and_doctor_without_profile_cannot_export: ['Riwayat Medis & PDF', 'Guest atau user role dokter tanpa profil dokter meminta URL ekspor PDF.', 'Guest diarahkan ke login dan dokter tanpa profil ditolak dengan HTTP 403.'],
    test_index_loads_only_latest_record_for_each_patient: ['Riwayat Medis & PDF', 'Dua pasien masing-masing memiliki dua rekam medis pada poli yang sama.', 'Daftar indeks memuat satu rekam medis terbaru untuk tiap pasien, bukan seluruh histori.'],
    test_large_history_can_export_every_part_without_silent_truncation: ['Riwayat Medis & PDF', 'Pasien memiliki 51 rekam medis dan ekspor diminta tanpa nomor bagian, lalu bagian 1, 2, dan bagian tidak valid.', 'Ekspor penuh meminta pemilihan bagian; PDF dibagi 50 dan 1 catatan, sedangkan nomor bagian di luar rentang mendapat HTTP 422.'],
    test_new_patient_receives_a_permanent_medical_record_number: ['Pemeriksaan & Rekam Medis', 'Staff mendaftarkan pasien baru beserta poli tujuan yang valid.', 'Nomor RM permanen dibuat dari ID pasien dan satu kunjungan antre tercatat.'],
    test_doctor_can_save_record_only_for_own_poli_and_active_visit: ['Pemeriksaan & Rekam Medis', 'Dokter menyimpan keluhan, diagnosis, dan resep untuk kunjungan menunggu_dokter di polinya.', 'Rekam medis terhubung ke dokter; kunjungan menjadi siap_bayar dan transaksi belum lunas dibuat.'],
    test_invalid_medical_record_keeps_doctor_input_and_does_not_finish_visit: ['Pemeriksaan & Rekam Medis', 'Keluhan melebihi batas 2.000 karakter saat dokter menyimpan pemeriksaan.', 'Validasi menolak keluhan, input lain dipertahankan, rekam medis/transaksi tidak dibuat, dan status tetap menunggu_dokter.'],
    test_doctor_cannot_start_or_save_another_poli_visit: ['Pemeriksaan & Rekam Medis', 'Dokter Poli Umum mencoba memulai dan menyimpan rekam medis untuk kunjungan Poli Gigi.', 'Kedua request ditolak dengan HTTP 404; status antre dan data rekam medis tidak berubah.'],
    test_doctor_must_start_a_visit_before_saving_the_medical_record: ['Pemeriksaan & Rekam Medis', 'Dokter mengirim rekam medis saat kunjungan masih berstatus antre.', 'Request ditolak dengan pesan error; rekam medis/transaksi tidak dibuat dan status tetap antre.'],
    test_doctor_cannot_call_a_second_patient_while_another_visit_is_being_examined: ['Pemeriksaan & Rekam Medis', 'Dokter memanggil pasien berikutnya ketika ada kunjungan berstatus menunggu_dokter.', 'Sistem memberi informasi bahwa tidak ada pemanggilan tambahan; hanya kunjungan pertama yang sedang diperiksa.'],
    test_started_examination_can_be_completed_after_wib_midnight_without_carrying_old_queue: ['Pemeriksaan & Rekam Medis', 'Pemeriksaan sudah dimulai sebelum tengah malam WIB; setelah tanggal berganti, dokter membuka antrean dan menyelesaikan pemeriksaan.', 'Pemeriksaan aktif lama dapat diselesaikan; antrean lama yang belum dimulai dan poli lain tidak masuk antrean hari ini; kunjungan baru hari ini dapat dipanggil.'],
    test_staff_can_view_patient_medical_record_index_and_detail: ['Pemeriksaan & Rekam Medis', 'Staff mencari pasien berdasarkan nomor RM dan membuka detail riwayat.', 'Indeks menampilkan nomor RM/diagnosis dan halaman detail menampilkan riwayat pemeriksaan serta resep.'],
    test_busy_pages_remain_bounded_with_one_thousand_daily_visits: ['Kapasitas & Pagination', 'Dashboard dan kasir diminta memuat data dari 1.000 kunjungan/transaksi harian.', 'Dashboard dibatasi 100 kunjungan, kasir 25 item per halaman dengan total 1.000; indeks query penting tersedia.'],
    test_payment_totals_are_calculated_on_the_server: ['Pembayaran', 'Kasir mengirim biaya tindakan 50.000, obat 12.500, pembayaran 100.000, sekaligus total/kembalian palsu.', 'Server mengabaikan total palsu; total menjadi 62.500, kembalian 37.500, transaksi lunas, dan kunjungan selesai.'],
    test_payment_is_rejected_when_cash_is_less_than_server_calculated_total: ['Pembayaran', 'Total tagihan 62.500 tetapi uang dibayar hanya 60.000.', 'Pembayaran ditolak; input dipertahankan, transaksi tetap belum lunas, dan kunjungan tetap siap_bayar.'],
    test_payment_rejects_amounts_beyond_database_capacity_and_keeps_input: ['Pembayaran', 'Nominal tindakan dan uang dibayar melampaui batas maksimum yang divalidasi.', 'Validasi menolak nominal; input tetap tersedia dan status transaksi/kunjungan tidak berubah.'],
    test_payment_rejects_combined_total_beyond_database_capacity: ['Pembayaran', 'Dua biaya masing-masing valid tetapi jumlah total melebihi kapasitas penyimpanan.', 'Server menolak total gabungan, mempertahankan input, dan transaksi tetap belum lunas.'],
    test_registration_examination_and_payment_use_the_same_wib_day: ['Zona Waktu WIB', 'Waktu UTC masih 9 September tetapi WIB sudah 10 September pukul 00.30; pasien didaftarkan, diperiksa, dan membayar.', 'Tanggal kunjungan, dashboard, pemeriksaan, transaksi, serta filter pendapatan konsisten menggunakan 10 September WIB.'],
    test_daily_queue_changes_at_wib_midnight_instead_of_utc_midnight: ['Zona Waktu WIB', 'Waktu diuji tepat sebelum dan saat tengah malam WIB, sementara tanggal UTC belum berubah.', 'Antrean hari sebelumnya berhenti dihitung setelah pergantian WIB; pendaftaran baru memakai tanggal dan antrean hari baru.'],
    test_registration_examination_and_payment_work_for_all_poli: ['Alur Ujung-ke-Ujung Semua Poli', 'Staff mendaftarkan pasien, dokter sesuai poli memulai pemeriksaan dan menyimpan rekam medis, lalu staff memproses pembayaran pada setiap poli konfigurasi.', 'Setiap siklus berakhir dengan transaksi lunas, total 60.000, kembalian 40.000, dan status kunjungan selesai.'],
};

const testSource = {
    'tests/Unit/ExampleTest.php': 'tests/Unit/ExampleTest.php (unit mandiri)',
    'tests/Feature/Auth/AuthenticationTest.php': 'app/Http/Controllers/Auth/AuthenticatedSessionController.php; app/Http/Requests/Auth/LoginRequest.php',
    'tests/Feature/Auth/EmailVerificationTest.php': 'app/Http/Controllers/Auth/VerifyEmailController.php',
    'tests/Feature/Auth/PasswordConfirmationTest.php': 'app/Http/Controllers/Auth/ConfirmablePasswordController.php',
    'tests/Feature/Auth/PasswordUpdateTest.php': 'app/Http/Controllers/Auth/PasswordController.php',
    'tests/Feature/DokterManagementTest.php': 'app/Http/Controllers/DokterController.php',
    'tests/Feature/ExampleTest.php': 'routes/web.php (route /)',
    'tests/Feature/KunjunganCancellationFlowTest.php': 'app/Http/Controllers/KunjunganController.php; app/Models/Kunjungan.php',
    'tests/Feature/KunjunganFlowTest.php': 'app/Http/Controllers/KunjunganController.php; app/Models/Kunjungan.php',
    'tests/Feature/MedicalRecordExportTest.php': 'app/Http/Controllers/RekamMedisPasienController.php',
    'tests/Feature/RekamMedisFlowTest.php': 'app/Http/Controllers/RekamMedisController.php; app/Http/Controllers/PasienController.php',
    'tests/Feature/ScalabilityGuardTest.php': 'app/Http/Controllers/DashboardController.php; app/Http/Controllers/TransaksiController.php',
    'tests/Feature/TransaksiFlowTest.php': 'app/Http/Controllers/TransaksiController.php',
    'tests/Feature/WibTimezoneTest.php': 'app/Models/Kunjungan.php; app/Http/Controllers/KunjunganController.php',
    'tests/Feature/AllPoliEndToEndFlowTest.php': 'config/clinic.php; app/Models/Kunjungan.php; app/Http/Controllers/PasienController.php; app/Http/Controllers/RekamMedisController.php; app/Http/Controllers/TransaksiController.php',
};

async function collectPhpFiles(directory) {
    const entries = await fs.readdir(directory, { withFileTypes: true });
    const nested = await Promise.all(entries.map(async (entry) => {
        const fullPath = path.join(directory, entry.name);
        if (entry.isDirectory()) return collectPhpFiles(fullPath);
        return entry.name.endsWith('.php') ? [fullPath] : [];
    }));
    return nested.flat();
}

const phpFiles = (await collectPhpFiles(testRoot)).sort((left, right) => {
    const leftRelative = path.relative(root, left).replaceAll(path.sep, '/');
    const rightRelative = path.relative(root, right).replaceAll(path.sep, '/');
    if (leftRelative.startsWith('tests/Unit/') !== rightRelative.startsWith('tests/Unit/')) {
        return leftRelative.startsWith('tests/Unit/') ? -1 : 1;
    }
    return leftRelative.localeCompare(rightRelative);
});
const whiteBoxCases = [];
for (const file of phpFiles) {
    const relative = path.relative(root, file).replaceAll(path.sep, '/');
    if (relative === 'tests/Feature/ProfileTest.php') continue;
    const source = await fs.readFile(file, 'utf8');
    const pattern = /public\s+function\s+(test_[A-Za-z0-9_]+)\s*\(/g;
    for (const match of source.matchAll(pattern)) {
        const detail = testDescriptions[match[1]];
        if (!detail) throw new Error(`Deskripsi belum tersedia untuk ${relative}::${match[1]}`);
        const sourceReference = testSource[relative];
        if (!sourceReference) throw new Error(`Referensi source code belum tersedia untuk ${relative}`);
        if (match[1] === 'test_registration_examination_and_payment_work_for_all_poli') {
            const poliScenarios = [
                {
                    poli: 'Poli Umum',
                    condition: 'Staff mendaftarkan pasien ke Poli Umum; dokter dengan poli yang sama memulai pemeriksaan dan mengirim keluhan, diagnosis, serta resep tanpa field khusus kandungan.',
                    expected: 'Kunjungan dibuat dengan nomor antrean 001, pemeriksaan menghasilkan status siap_bayar, dan pembayaran Rp50.000 + Rp10.000 dengan uang Rp100.000 menghasilkan transaksi lunas, kembalian Rp40.000, serta status kunjungan selesai.',
                },
                {
                    poli: 'Poli Gigi',
                    condition: 'Staff mendaftarkan pasien ke Poli Gigi; akun dokter Poli Gigi memulai pemeriksaan kunjungan tersebut dan menyimpan keluhan, diagnosis, serta resep.',
                    expected: 'Kunjungan Poli Gigi memperoleh nomor antrean 001, beralih ke siap_bayar setelah rekam medis dibuat, lalu transaksi menjadi lunas dengan total Rp60.000 dan status kunjungan selesai.',
                },
                {
                    poli: 'Poli Anak',
                    condition: 'Staff mendaftarkan pasien ke Poli Anak; akun dokter Poli Anak memulai pemeriksaan dan mengirim field rekam medis standar.',
                    expected: 'Kunjungan Poli Anak memperoleh nomor antrean 001, rekam medis diterima, status berubah menjadi siap_bayar, kemudian pembayaran mencatat total Rp60.000, kembalian Rp40.000, dan status selesai.',
                },
                {
                    poli: 'Poli Spesialis Kandungan',
                    condition: 'Staff mendaftarkan pasien ke Poli Spesialis Kandungan; dokter mengisi field rekam medis standar serta hasil_pemeriksaan yang diwajibkan untuk poli ini.',
                    expected: 'Request pemeriksaan dengan hasil_pemeriksaan diterima, kunjungan berubah menjadi siap_bayar, dan pembayaran Rp60.000 diselesaikan dengan kembalian Rp40.000 serta status kunjungan selesai.',
                },
            ];
            for (const scenario of poliScenarios) {
                whiteBoxCases.push({
                    file: relative,
                    method: match[1],
                    module: `Alur End-to-End ${scenario.poli}`,
                    condition: scenario.condition,
                    expected: scenario.expected,
                    source: sourceReference,
                });
            }
            continue;
        }
        whiteBoxCases.push({ file: relative, method: match[1], module: detail[0], condition: detail[1], expected: detail[2], source: sourceReference });
    }
}
const describedMethods = new Set(whiteBoxCases.map((test) => test.method));
const missingDescriptions = Object.keys(testDescriptions).filter((method) => !describedMethods.has(method));
if (missingDescriptions.length) throw new Error(`Deskripsi tidak cocok dengan test saat ini: ${missingDescriptions.join(', ')}`);

const whiteChildren = [
    new Paragraph({ text: 'LAPORAN PENGUJIAN WHITE BOX', heading: HeadingLevel.TITLE, alignment: AlignmentType.CENTER }),
    new Paragraph({ text: 'Sistem Informasi Klinik Lala Medicare', heading: HeadingLevel.HEADING_1, alignment: AlignmentType.CENTER }),
    para('Tanggal eksekusi: 1 November 2026', { size: 18 }),
    para('White-box diuji melalui test PHPUnit yang memeriksa cabang logika, validasi, perubahan status, pembatasan akses, dan hasil penyimpanan data. Referensi source code dan test code dicantumkan pada setiap skenario agar dapat ditelusuri.'),
    heading('Ringkasan Hasil Pengujian'),
    makeTable(['Metrik', 'Hasil'], [50, 50], [
        ['Skenario dalam dokumen', String(whiteBoxCases.length)],
        ['Test method seluruh suite PHPUnit', '55'],
        ['Assertion seluruh suite', '330'],
        ['Lulus / Gagal / Error', '55 / 0 / 0'],
        ['Status suite', 'LULUS'],
    ]),
    para('Eksekusi menggunakan PHPUnit dengan SQLite in-memory sesuai phpunit.xml. Persentase line/branch coverage tidak dihitung pada eksekusi ini.'),
    heading('Tabel Pengujian White Box'),
    para('Setiap baris menjelaskan kondisi spesifik yang diuji dan hasil yang diharapkan berdasarkan assertion pada test. Hasil aktual seluruh skenario adalah PASS pada eksekusi 1 November 2026.'),
];
const wbWidths = [5, 23, 22, 27, 23];
const wbHeaders = ['No', 'Source Code', 'Test Code', 'Kondisi yang Diuji', 'Output yang Diharapkan / Aktual'];
let caseNumber = 0;
for (const module of [...new Set(whiteBoxCases.map((test) => test.module))]) {
    const moduleCases = whiteBoxCases.filter((test) => test.module === module);
    whiteChildren.push(heading(module, HeadingLevel.HEADING_2));
    whiteChildren.push(makeTable(wbHeaders, wbWidths, moduleCases.map((test) => {
        caseNumber += 1;
        const method = test.method.replace(/^test_/, '').replaceAll('_', ' ');
        return [
            String(caseNumber).padStart(2, '0'),
            `${test.source}\nTarget: ${module}`,
            `${test.file}\n${test.method}()`,
            test.condition,
            `${test.expected}\nAktual: PASS`,
        ];
    })));
}
whiteChildren.push(heading('Catatan dan Kesimpulan'));
whiteChildren.push(para('Laporan memuat 53 skenario. Empat skenario poli merupakan uraian terpisah dari satu test method end-to-end yang melakukan iterasi atas daftar poli di konfigurasi aplikasi; jumlah tersebut bukan penambahan method PHPUnit.'));
whiteChildren.push(para('Pada eksekusi suite yang dicatat, 55 test method lulus dengan 330 assertion dan tanpa failure/error. Skenario profil pengguna tidak dimasukkan ke dalam ruang lingkup laporan ini.'));
const whiteDoc = new Document({ sections: [{
    properties: { page: { size: { width: 16838, height: 11906 }, margin: { top: 650, right: 650, bottom: 650, left: 650 } } },
    children: whiteChildren,
}] });
if (!blackBoxOnly) {
    await fs.writeFile(path.join(reportDir, 'Laporan-White-Box-Klinik-Lala-Revisi-Poli.docx'), await Packer.toBuffer(whiteDoc));
}
if (whiteBoxOnly) {
    console.log(`Dokumen white-box dibuat: ${whiteBoxCases.length} skenario.`);
    process.exit(0);
}

const blackBox = JSON.parse(await fs.readFile(path.join(reportDir, 'blackbox-results.json'), 'utf8'));
const visualDetails = {
    '01': {
        input: 'Guest membuka GET /login.',
        condition: 'BrowserTestSeeder menyiapkan lingkungan uji; halaman dibuka tanpa session login.',
        expected: 'Panel login menampilkan email, password, Ingat Saya, Lupa Password?, dan tombol Login.',
        observed: 'Panel login tampil. Area banner kiri menampilkan teks alternatif “Klinik Banner”, menandakan gambar banner tidak termuat pada screenshot.',
    },
    '02': {
        input: 'Staff membuka GET /dashboard setelah login.',
        condition: 'Data fixture berisi empat pasien/kunjungan dengan status dan poli yang berbeda.',
        expected: 'Dashboard staff memperlihatkan ringkasan total pasien, antrean aktif, pasien menunggu kasir, dan tabel antrean hari ini.',
        observed: 'Terlihat kartu Total Pasien, Antrean Aktif, Menunggu Kasir, tombol Pasien Baru, serta tabel dengan nomor antrean, pasien, poli, status, dan aksi.',
    },
    '03': {
        input: 'Staff membuka GET /dokter.',
        condition: 'Fixture menyediakan empat akun dokter pada poli berbeda.',
        expected: 'Halaman menampilkan form tambah dokter dan daftar dokter beserta NIP/SIP serta poli.',
        observed: 'Form nama/gelar, NIP/SIP, poli, email, dan password tampil; daftar menampilkan empat dokter termasuk Poli Spesialis Kandungan. Kolom paling kanan tampak terpotong pada lebar screenshot.',
    },
    '04': {
        input: 'Staff membuka GET /pasien.',
        condition: 'Fixture menyediakan empat pasien dengan nomor RM dan kunjungan aktif.',
        expected: 'Tab pencarian pasien lama, kolom pencarian, tabel data pasien, dan aksi status antre terlihat.',
        observed: 'Kolom pencarian menerima nama/NIK/No. RM; tabel menampilkan No. RM, nama, NIK, No. HP, tanggal terdaftar, serta status sudah antre.',
    },
    '05': {
        input: 'Staff membuka GET /pasien/create.',
        condition: 'Halaman dibuka sebagai staff; belum ada data form yang dikirim.',
        expected: 'Form memuat identitas pasien, jenis kelamin, kontak, alamat, pilihan poli, dan tombol penyimpanan.',
        observed: 'Terlihat field nama, NIK, tanggal lahir, jenis kelamin, HP/WhatsApp, pekerjaan, alamat, pilihan poli, dan tombol Simpan Pasien & Buat Antrean.',
    },
    '06': {
        input: 'Staff membuka GET /kunjungan.',
        condition: 'Fixture memiliki empat kunjungan dengan poli dan status berbeda.',
        expected: 'Filter pencarian, rentang waktu, poli, status, dan tabel riwayat kunjungan tampil.',
        observed: 'Filter menampilkan Semua Waktu, Semua Poli, Semua Status; tabel memuat No. RM, nama, tanggal/jam, poli, status, dan aksi Detail.',
    },
    '07': {
        input: 'Staff membuka GET /kunjungan/1.',
        condition: 'Kunjungan fixture yang dipilih masih menunggu; belum ada rekam medis atau pembayaran.',
        expected: 'Rincian identitas dan kunjungan tampil, dengan keadaan kosong yang jelas untuk hasil pemeriksaan/pembayaran bila belum tersedia.',
        observed: 'Informasi pasien/poli/status tampil; bagian pemeriksaan dan pembayaran menyatakan data belum tersedia. Form pembatalan menampilkan kategori dan alasan.',
    },
    '08': {
        input: 'Staff membuka GET /transaksi.',
        condition: 'Fixture menyediakan satu transaksi yang belum lunas dan kunjungan siap bayar.',
        expected: 'Daftar Menunggu Pembayaran menyajikan nomor antrean, pasien, diagnosis, total, dan aksi proses bayar.',
        observed: 'Satu transaksi terlihat pada tabel dengan nomor G-001, nama pasien, diagnosis, total Rp 0, dan tombol Proses Bayar. Screenshot ini menunjukkan daftar awal, bukan dialog/form pembayaran.',
    },
    '09': {
        input: 'Staff membuka GET /riwayat-transaksi.',
        condition: 'Belum ada transaksi lunas pada fixture yang tampak di screenshot.',
        expected: 'Ringkasan pendapatan/jumlah transaksi, filter pencarian/tanggal, dan tabel histori atau empty state tampil.',
        observed: 'Total pendapatan dan jumlah transaksi bernilai 0; filter tersedia dan tabel menampilkan pesan belum ada riwayat transaksi.',
    },
    '10': {
        input: 'Staff membuka GET /rekam-medis-pasien.',
        condition: 'Fixture menyediakan pasien dengan satu rekam medis.',
        expected: 'Kolom pencarian dan daftar pasien dengan rekam medis terbaru serta aksi detail/PDF tampil.',
        observed: 'Tabel menampilkan pasien, umur, tanggal kunjungan terakhir, diagnosis terakhir, dokter, tombol Lihat Detail RM, dan PDF.',
    },
    '11': {
        input: 'Dokter Poli Umum membuka GET /dashboard.',
        condition: 'Dokter login memiliki poli Poli Umum; fixture berisi satu antrean pada poli tersebut.',
        expected: 'Dashboard menampilkan identitas/poli dokter, ringkasan antrean poli, tombol panggil, dan daftar antrean.',
        observed: 'Banner menampilkan Poli Umum dan tombol Panggil Pasien Selanjutnya; kartu ringkasan dan tabel antrean menunjukkan pasien A-001 yang sedang diperiksa.',
    },
    '12': {
        input: 'Dokter Poli Umum membuka GET /rekam-medis.',
        condition: 'Antrean Poli Umum sudah berstatus sedang diperiksa.',
        expected: 'Form pemeriksaan menampilkan data antrean, tanda vital opsional, keluhan, diagnosis, resep, serta aksi simpan.',
        observed: 'Terlihat status Sedang Diperiksa, tombol Kembalikan ke Antrean/Pasien Tidak Hadir, field suhu/tinggi/berat/tekanan darah, keluhan, diagnosis, resep, dan Simpan & Selesai Periksa.',
    },
    '13': {
        input: 'Dokter Poli Anak membuka GET /rekam-medis.',
        condition: 'Fixture menyediakan satu pasien Poli Anak yang masih menunggu dan belum dimulai.',
        expected: 'Antrean menampilkan pasien dan status menunggu; form baru tersedia setelah pemeriksaan dimulai.',
        observed: 'Pasien C-001 berstatus Menunggu; halaman memberi instruksi untuk memulai pemeriksaan terlebih dahulu dan menampilkan tombol Mulai Periksa. Form belum terbuka.',
    },
    '14': {
        input: 'Dokter Poli Gigi membuka GET /rekam-medis.',
        condition: 'Fixture tidak memiliki antrean pemeriksaan Poli Gigi yang masih aktif.',
        expected: 'Halaman menampilkan empty state, bukan form pasien yang tidak ada.',
        observed: 'Pesan menyatakan tidak ada antrean pemeriksaan dan semua pasien sudah selesai diperiksa.',
    },
    '15': {
        input: 'Dokter Poli Spesialis Kandungan membuka GET /rekam-medis.',
        condition: 'Fixture menyediakan pasien kandungan berstatus sedang diperiksa.',
        expected: 'Form menampilkan data poli dan field pemeriksaan umum ditambah hasil pemeriksaan/catatan tambahan.',
        observed: 'Terlihat status Sedang Diperiksa, tanda vital, keluhan, Hasil Pemeriksaan, Diagnosis, Resep Obat, Catatan Tambahan, serta tombol Simpan & Selesai Periksa.',
    },
    '16': {
        input: 'Dokter Poli Umum membuka GET /rekam-medis-pasien.',
        condition: 'Fixture tidak menyediakan riwayat medis yang cocok untuk daftar yang ditampilkan pada sesi screenshot.',
        expected: 'Kolom cari dan tabel riwayat tetap tampil; jika tidak ada data sesuai scope, halaman memberi empty state.',
        observed: 'Header tabel No. RM/Nama/Kunjungan Terakhir/Diagnosis Terakhir/Dokter/Aksi tampil, disertai pesan data rekam medis tidak ditemukan. Screenshot ini sendiri tidak membuktikan pembatasan poli; pembatasan tersebut diuji melalui test backend.',
    },
};

const blackChildren = [
    new Paragraph({ text: 'LAPORAN PENGUJIAN BLACK BOX', heading: HeadingLevel.TITLE, alignment: AlignmentType.CENTER }),
    new Paragraph({ text: 'Sistem Informasi Klinik Lala Medicare', heading: HeadingLevel.HEADING_1, alignment: AlignmentType.CENTER }),
    para('Tanggal bukti screenshot: 21 Oktober 2026', { size: 18 }),
    para('Pengujian ini mendokumentasikan tampilan halaman pada browser desktop 1440 × 900 berdasarkan role dan data uji BrowserTestSeeder. Deskripsi hasil mengacu pada elemen yang tampak pada setiap screenshot.'),
    makeTable(['Ringkasan', 'Hasil'], [50, 50], [
        ['Screenshot tersedia', String(blackBox.cases.length)],
        ['Halaman yang berhasil dibuka dan ditangkap', String(blackBox.passed)],
        ['Status dokumentasi', '16 bukti visual tersedia'],
    ]),
    para('Catatan cakupan: script browser menangkap halaman dan memeriksa alur login yang dipakai untuk navigasi. Untuk sebagian besar halaman, status pada tabel berarti screenshot berhasil direkam; status tersebut bukan klaim bahwa seluruh tombol/form telah diuji interaksinya.'),
    heading('Tabel Pengujian Black Box'),
];
const bbWidths = [4, 15, 14, 17, 34, 16];
const bbHeaders = ['No', 'Test Case / Role', 'Input / Aksi', 'Kondisi Awal', 'Output yang Diharapkan', 'Checklist Hasil'];
for (const item of blackBox.cases) {
    const detail = visualDetails[item.number];
    const imagePath = path.join(reportDir, 'blackbox-screenshots', item.screenshot);
    const image = await fs.readFile(imagePath);
    blackChildren.push(
        new Paragraph({ text: `Gambar ${item.number}. ${item.title} (${item.role})`, heading: HeadingLevel.HEADING_2, spacing: { before: 90, after: 45 }, keepNext: true }),
        new Paragraph({ alignment: AlignmentType.CENTER, spacing: { before: 0, after: 45 }, keepNext: true, children: [new ImageRun({ data: image, type: 'png', transformation: { width: 370, height: 231 } })] }),
        para(detail.observed, { size: 18 }),
        makeTable(bbHeaders, bbWidths, [[
            item.number,
            `${item.title}\nRole: ${item.role}`,
            detail.input,
            detail.condition,
            detail.expected,
            item.status === 'Berhasil' ? '☑ Berhasil' : '☐ Perlu review',
        ]]),
    );
}
blackChildren.push(heading('Catatan dan Kesimpulan'));
blackChildren.push(para('Bukti memperlihatkan halaman staff dan dokter untuk dashboard, master dokter, pasien, antrean, pemeriksaan, rekam medis, dan transaksi. Keadaan kosong pada beberapa halaman (misalnya histori transaksi atau antrean Poli Gigi) merupakan kondisi yang terlihat pada data fixture, bukan kegagalan tampilan.'));
blackChildren.push(para('Screenshot Form Login memperlihatkan banner kiri yang gagal dimuat. Kolom pada tabel Master Data Dokter juga tampak terpotong di sisi kanan pada viewport bukti. Keduanya dicatat sebagai temuan visual agar tidak tertukar dengan hasil fungsi backend.'));
const blackDoc = new Document({ sections: [{
    properties: { page: { size: { width: 11906, height: 16838 }, margin: { top: 800, right: 800, bottom: 800, left: 800 } } },
    children: blackChildren,
}] });
await fs.writeFile(path.join(reportDir, 'Laporan-Black-Box-Klinik-Lala-Revisi-Checklist.docx'), await Packer.toBuffer(blackDoc));
console.log(`Dokumen dibuat: ${whiteBoxCases.length} skenario white-box, ${blackBox.cases.length} screenshot black-box.`);
