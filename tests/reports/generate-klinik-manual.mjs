import fs from 'node:fs/promises';
import path from 'node:path';
import {
    AlignmentType,
    Document,
    Footer,
    HeadingLevel,
    ImageRun,
    NumberFormat,
    PageNumber,
    Packer,
    Paragraph,
    Table,
    TableCell,
    TableOfContents,
    TableRow,
    TextRun,
    WidthType,
} from 'docx';
import { estimatePageNumbers } from 'docx/layout';

const root = process.cwd();
const reportDir = path.join(root, 'tests', 'reports');
const screenshotDir = path.join(reportDir, 'blackbox-screenshots');
const output = path.join(reportDir, 'Manualbook-Penggunaan-Klinik-Lala-Medicare-Format-Final.docx');
const children = [];
const font = 'Arial';
const bodySize = 24;
const heading1Size = 28;
const heading2Size = 24;

function paragraph(text, options = {}) {
    const { size = bodySize, bold = false, italic = false, alignment, before = 60, after = 100, keepNext = false } = options;
    return new Paragraph({
        alignment: alignment ?? AlignmentType.JUSTIFIED,
        spacing: { before, after, line: 360, lineRule: 'auto' },
        keepNext,
        children: [new TextRun({ text, font, size, bold, italics: italic })],
    });
}

function addText(text, options) {
    children.push(paragraph(text, options));
}

function addHeading(text, level = HeadingLevel.HEADING_1, pageBreakBefore = false) {
    const size = level === HeadingLevel.HEADING_1 ? heading1Size : heading2Size;
    children.push(new Paragraph({
        heading: level,
        alignment: AlignmentType.JUSTIFIED,
        pageBreakBefore,
        keepNext: true,
        spacing: { before: 120, after: 100, line: 360, lineRule: 'auto' },
        children: [new TextRun({ text, font, size, bold: true })],
    }));
}

function addSteps(steps) {
    steps.forEach((text, index) => {
        children.push(new Paragraph({
            alignment: AlignmentType.JUSTIFIED,
            spacing: { before: 20, after: 75, line: 360, lineRule: 'auto' },
            indent: { left: 360, hanging: 360 },
            children: [new TextRun({ text: `${index + 1}. ${text}`, font, size: bodySize })],
        }));
    });
}

function addBullets(items) {
    items.forEach((text) => {
        children.push(new Paragraph({
            alignment: AlignmentType.JUSTIFIED,
            spacing: { before: 15, after: 70, line: 360, lineRule: 'auto' },
            indent: { left: 360, hanging: 180 },
            children: [new TextRun({ text: `- ${text}`, font, size: bodySize })],
        }));
    });
}

function addNote(text) {
    children.push(new Paragraph({
        shading: { fill: 'EAF4EF', color: 'auto', type: 'clear' },
        alignment: AlignmentType.JUSTIFIED,
        indent: { left: 180, right: 180 },
        spacing: { before: 75, after: 100, line: 360, lineRule: 'auto' },
        children: [new TextRun({ text: `Catatan: ${text}`, font, size: bodySize })],
    }));
}

function makeCell(text, width, header = false) {
    return new TableCell({
        width: { size: width, type: WidthType.PERCENTAGE },
        margins: { top: 100, bottom: 100, left: 100, right: 100 },
        shading: header ? { fill: 'DDEBE4', color: 'auto', type: 'clear' } : undefined,
        children: [new Paragraph({
            alignment: AlignmentType.JUSTIFIED,
            spacing: { before: 0, after: 0, line: 360, lineRule: 'auto' },
            children: [new TextRun({ text, font, size: bodySize, bold: header })],
        })],
    });
}

function addTable(headers, widths, rows) {
    children.push(new Table({
        width: { size: 100, type: WidthType.PERCENTAGE },
        rows: [
            new TableRow({ tableHeader: true, children: headers.map((header, index) => makeCell(header, widths[index], true)) }),
            ...rows.map((row) => new TableRow({ children: row.map((text, index) => makeCell(text, widths[index])) })),
        ],
    }));
    children.push(paragraph('', { after: 30 }));
}

async function addScreenshot(fileName, caption) {
    const file = path.join(screenshotDir, fileName);
    const data = await fs.readFile(file);
    const originalWidth = data.readUInt32BE(16);
    const originalHeight = data.readUInt32BE(20);
    const scale = Math.min(390 / originalWidth, 350 / originalHeight);
    const width = Math.round(originalWidth * scale);
    const height = Math.round(originalHeight * scale);
    children.push(new Paragraph({
        alignment: AlignmentType.CENTER,
        keepNext: true,
        spacing: { before: 80, after: 50 },
        children: [new ImageRun({ data, type: 'png', transformation: { width, height } })],
    }));
    children.push(paragraph(caption, { size: bodySize, italic: true, alignment: AlignmentType.CENTER, before: 20, after: 120 }));
}

async function addProcedure({ title, intro, steps, screenshot, caption, note }) {
    addHeading(title, HeadingLevel.HEADING_2, true);
    addText(intro);
    addSteps(steps);
    if (note) addNote(note);
    if (screenshot) await addScreenshot(screenshot, caption);
}

function addChapter(title, intro = '') {
    addHeading(title, HeadingLevel.HEADING_1, true);
    if (intro) addText(intro);
}

function image(name) {
    return `blackbox-${name}.png`;
}

const cover = [
    paragraph('BUKU PETUNJUK PENGGUNAAN APLIKASI', { size: 32, bold: true, alignment: AlignmentType.CENTER, before: 1500, after: 300 }),
    paragraph('(USER MANUAL)', { size: 28, bold: true, alignment: AlignmentType.CENTER, before: 0, after: 900 }),
    paragraph('SISTEM INFORMASI KLINIK LALA MEDICARE', { size: 28, bold: true, alignment: AlignmentType.CENTER, before: 0, after: 200 }),
    paragraph('Aplikasi Pelayanan Klinik Berbasis Web', { size: 24, alignment: AlignmentType.CENTER, before: 0, after: 150 }),
    paragraph('Panduan untuk Staff/Kasir dan Dokter', { size: 24, alignment: AlignmentType.CENTER, before: 0, after: 1000 }),
    paragraph('KLINIK LALA MEDICARE', { size: 24, bold: true, alignment: AlignmentType.CENTER, before: 700, after: 120 }),
    paragraph('Versi 1.0', { size: bodySize, alignment: AlignmentType.CENTER, before: 1500, after: 100 }),
    paragraph('4 November 2026', { size: bodySize, alignment: AlignmentType.CENTER, before: 0, after: 0 }),
];
children.push(...cover);

addHeading('Tentang Panduan Ini', HeadingLevel.HEADING_1, true);
addText('Buku petunjuk ini menjelaskan cara menggunakan Sistem Informasi Klinik Lala Medicare untuk kegiatan administrasi dan pelayanan pasien. Panduan disusun berdasarkan menu, formulir, serta alur aplikasi yang tersedia pada versi 1.0.');
addText('Isi panduan mencakup persiapan akses, login, penggunaan fitur Staff/Kasir, penggunaan fitur Dokter pada setiap poli, pencatatan pemeriksaan, pembayaran, pencarian riwayat, dan ekspor rekam medis.');
addNote('Screenshot dalam panduan menggunakan data uji aplikasi. Jangan menggunakan data pasien uji sebagai data pelayanan nyata. Nama menu dapat sedikit berubah apabila aplikasi diperbarui.');
children.push(paragraph('Daftar Isi', { size: heading1Size, bold: true, before: 120, after: 100 }));
children.push(new TableOfContents('Daftar Isi', {
    headingStyleRange: '1-2',
    entryAndPageNumberSeparator: '\t',
    hyperlink: true,
    beginDirty: false,
}));
addText('Daftar isi dan nomor halaman dihitung saat dokumen dibuat. Jika isi dokumen kemudian diedit di Word, klik kanan pada daftar isi lalu pilih Update Field / Perbarui Bidang dan Update entire table / Perbarui seluruh tabel.', { size: bodySize, italic: true });

addChapter('1. Pendahuluan', 'Aplikasi digunakan oleh Staff/Kasir dan Dokter untuk mengelola proses pelayanan pasien dari registrasi sampai pembayaran. Pasien tidak login ke aplikasi.');
addHeading('1.1 Tujuan dan Gambaran Sistem', HeadingLevel.HEADING_2);
addText('Sistem menyimpan data pasien, kunjungan, antrean poli, rekam medis, serta transaksi dalam satu aplikasi web. Nomor rekam medis dibuat untuk identitas pasien, sedangkan nomor antrean dibuat terpisah untuk kunjungan pada poli dan tanggal pelayanan.');
addHeading('1.2 Pengguna dan Hak Akses', HeadingLevel.HEADING_2);
addTable(['Role', 'Menu dan kegiatan utama', 'Batas akses'], [18, 45, 37], [
    ['Staff/Kasir', 'Dashboard; Master Data Dokter; Pasien; Riwayat Kunjungan; Transaksi; Riwayat Transaksi; melihat riwayat rekam medis pasien.', 'Mengelola administrasi dan pembayaran. Tidak mengisi hasil pemeriksaan dokter.'],
    ['Dokter', 'Dashboard Dokter; Periksa Pasien; Rekam Medis Pasien; ekspor PDF riwayat sesuai poli.', 'Hanya melihat/memproses antrean dan rekam medis poli yang terhubung ke akun dokter. Tidak mengelola pasien, dokter, atau transaksi.'],
]);
addNote('Pada versi ini menu utama Staff di sidebar berjumlah enam termasuk Home. Halaman Rekam Medis Pasien juga dapat diakses oleh Staff, tetapi belum menjadi item sidebar Staff; bila tidak ada tautan dari halaman lain, gunakan alamat /rekam-medis-pasien setelah login.');
addHeading('1.3 Alur Layanan Utama', HeadingLevel.HEADING_2);
addSteps([
    'Staff mendaftarkan pasien baru atau mencari pasien lama, lalu memilih poli tujuan.',
    'Sistem membuat kunjungan hari itu dengan status Antre dan nomor antrean poli.',
    'Dokter yang bertugas pada poli tersebut memanggil atau memulai pemeriksaan pasien.',
    'Dokter mengisi rekam medis dan menyimpan hasil pemeriksaan. Status kunjungan berubah menjadi Siap Bayar.',
    'Staff memproses pembayaran. Setelah pembayaran berhasil, transaksi menjadi Lunas dan kunjungan menjadi Selesai.',
]);
addTable(['Status pada aplikasi', 'Arti bagi pengguna'], [34, 66], [
    ['Antre', 'Pasien menunggu dipanggil pada poli yang dipilih.'],
    ['Menunggu Dokter / Sedang Diperiksa', 'Dokter telah memanggil pasien dan dapat mengisi pemeriksaan.'],
    ['Siap Bayar', 'Pemeriksaan selesai dan transaksi menunggu diproses Staff/Kasir.'],
    ['Selesai', 'Pembayaran lunas dan kunjungan ditutup.'],
    ['Dibatalkan', 'Kunjungan dihentikan, tetapi tetap tersimpan di riwayat.'],
]);

addChapter('2. Persiapan Menggunakan Aplikasi');
addHeading('2.1 Perangkat Lunak dan Koneksi', HeadingLevel.HEADING_2);
addBullets([
    'Aplikasi dibuka melalui peramban web; tidak perlu memasang aplikasi khusus pada komputer pengguna.',
    'Gunakan Google Chrome atau Microsoft Edge versi terbaru yang didukung perangkat.',
    'Pastikan komputer terhubung ke jaringan internet atau jaringan klinik yang dapat menjangkau server aplikasi.',
    'Alamat aplikasi diberikan oleh pengelola klinik. Gunakan alamat resmi yang diberikan, bukan alamat pengujian.',
]);
addHeading('2.2 Perangkat Keras Pendukung', HeadingLevel.HEADING_2);
addText('Rekomendasi berikut merupakan acuan awal untuk penggunaan klinik skala kecil. Kebutuhan server dapat bertambah mengikuti jumlah pengguna dan volume data.');
addTable(['Kategori', 'Spesifikasi Minimum', 'Rekomendasi Ideal'], [25, 37, 38], [
    ['Perangkat Pengguna (Staff/Dokter)', 'Laptop/PC dual-core, RAM 4 GB, layar 1366 × 768, browser terbaru.', 'Laptop/PC 4-core, RAM 8 GB, layar Full HD, browser terbaru.'],
    ['Server Aplikasi dan Database', '2 vCPU, RAM 4 GB, SSD kosong 25 GB, koneksi jaringan stabil.', '4 vCPU, RAM 8 GB, SSD kosong 50 GB, backup pada media terpisah.'],
]);
addHeading('2.3 Akun dan Keamanan', HeadingLevel.HEADING_2);
addBullets([
    'Gunakan akun pribadi yang dibuat oleh Staff/pengelola klinik. Jangan berbagi akun atau password.',
    'Jangan mencatat password di tempat yang dapat dilihat pasien atau pengunjung.',
    'Klik Keluar setelah selesai, terutama bila memakai komputer bersama.',
    'Jika lupa password, gunakan tombol Lupa Password? pada halaman login dan ikuti pesan untuk menghubungi Staff klinik.',
]);

addChapter('3. Memulai Aplikasi dan Login');
await addProcedure({
    title: '3.1 Membuka Halaman Login',
    intro: 'Aplikasi diakses menggunakan browser pada komputer klinik atau perangkat kerja yang terhubung ke jaringan.',
    steps: [
        'Buka Google Chrome atau Microsoft Edge.',
        'Ketik alamat aplikasi resmi dari pengelola klinik pada address bar, lalu tekan Enter.',
        'Pastikan halaman menampilkan nama Klinik Lala Medicare dan formulir login.',
    ],
    screenshot: image('01-guest-form-login'),
    caption: 'Gambar 3.1 Halaman login Klinik Lala Medicare',
});
await addProcedure({
    title: '3.2 Masuk ke Aplikasi',
    intro: 'Gunakan email akun yang telah dibuat untuk Staff atau Dokter. Username pada tampilan login diisi dengan alamat email.',
    steps: [
        'Isi kolom Username dengan email akun.',
        'Isi kolom Password. Gunakan ikon mata untuk melihat atau menyembunyikan karakter password.',
        'Centang Ingat Saya hanya jika perangkat digunakan secara pribadi dan sesuai kebijakan klinik.',
        'Klik Login. Jika data benar, aplikasi membuka dashboard sesuai role akun.',
    ],
    screenshot: image('01-guest-form-login'),
    caption: 'Gambar 3.2 Form login: Username, Password, Ingat Saya, dan tombol Login',
    note: 'Tombol Lupa Password? menampilkan arahan untuk menghubungi Staff agar memperoleh password baru; perubahan password tidak dilakukan melalui formulir tersebut.',
});
await addProcedure({
    title: '3.3 Keluar dari Aplikasi',
    intro: 'Keluar dari aplikasi untuk mengakhiri sesi dan menjaga kerahasiaan data pasien.',
    steps: [
        'Klik tombol Keluar pada bagian bawah sidebar.',
        'Pada konfirmasi, pilih Ya, Logout untuk mengakhiri sesi, atau Batal untuk kembali bekerja.',
        'Pastikan halaman kembali ke layar login sebelum meninggalkan perangkat.',
    ],
    screenshot: image('02-staff-dashboard-staff'),
    caption: 'Gambar 3.3 Dashboard Staff dengan sidebar tempat tombol Keluar berada',
});

addChapter('4. Panduan Penggunaan untuk Staff/Kasir', 'Staff mengelola data dokter dan pasien, pendaftaran kunjungan, riwayat, dan transaksi. Lakukan pencarian pasien terlebih dahulu sebelum membuat data pasien baru.');
await addProcedure({
    title: '4.1 Dashboard Staff',
    intro: 'Dashboard memberikan ringkasan aktivitas klinik pada hari berjalan.',
    steps: [
        'Setelah login sebagai Staff, pilih Home pada sidebar atau buka dashboard.',
        'Periksa kartu Total Pasien, Antrean Aktif, dan Menunggu Kasir.',
        'Gunakan daftar antrean hari ini untuk melihat nomor antrean, nama pasien, poli, waktu, dan status.',
        'Klik Pasien Baru bila akan mendaftarkan pasien baru.',
    ],
    screenshot: image('02-staff-dashboard-staff'),
    caption: 'Gambar 4.1 Dashboard Staff dan ringkasan kegiatan klinik',
});
await addProcedure({
    title: '4.2 Master Data Dokter',
    intro: 'Menu Master Data Dokter digunakan Staff untuk membuat, memperbarui, atau menghapus akun dan profil Dokter.',
    steps: [
        'Buka Master Data Dokter dari sidebar.',
        'Untuk menambah Dokter, isi Nama Lengkap & Gelar, NIP/SIP, Poli/Spesialisasi, Email, dan Password Awal minimal 8 karakter.',
        'Klik Simpan Data Dokter. Akun dibuat dengan role Dokter dan terhubung ke poli yang dipilih.',
        'Untuk memperbarui data, pilih Edit pada baris Dokter, ubah informasi, lalu klik Simpan Perubahan. Password baru bersifat opsional.',
        'Untuk menghapus Dokter, pilih Hapus dan konfirmasi Ya, Hapus. Penghapusan juga menghapus akun login terkait.',
    ],
    screenshot: image('03-staff-master-data-dokter'),
    caption: 'Gambar 4.2 Form tambah Dokter dan tabel daftar Dokter praktik',
    note: 'Poli yang tersedia pada aplikasi: Poli Umum, Poli Gigi, Poli Anak, dan Poli Spesialis Kandungan. Pastikan poli akun sesuai penugasan agar Dokter hanya memperoleh antrean yang benar.',
});
await addProcedure({
    title: '4.3 Mencari Pasien Lama dan Membuat Antrean',
    intro: 'Untuk pasien yang sudah pernah terdaftar, buat kunjungan dari data pasien yang ditemukan agar nomor rekam medis lama tetap digunakan.',
    steps: [
        'Buka menu Pasien dan pilih tab Cari Pasien Lama.',
        'Masukkan nama, NIK, nomor HP, atau nomor rekam medis pada kolom pencarian, lalu klik Cari.',
        'Periksa kecocokan identitas pada hasil pencarian sebelum membuat kunjungan.',
        'Pada kolom Aksi, pilih Poli Tujuan lalu klik Buat Antrean.',
        'Pastikan pesan berhasil tampil dan status pasien berubah menjadi Kunjungan aktif hari ini / Sudah antre.',
    ],
    screenshot: image('04-staff-pengelolaan-pasien'),
    caption: 'Gambar 4.3 Pencarian pasien lama dan aksi pembuatan antrean',
    note: 'Pasien hanya dapat memiliki satu kunjungan aktif pada hari yang sama. Bila pasien sudah antre, selesaikan atau batalkan kunjungan tersebut sebelum membuat kunjungan lain.',
});
await addProcedure({
    title: '4.4 Mendaftarkan Pasien Baru',
    intro: 'Pendaftaran pasien baru membuat identitas, nomor rekam medis, dan kunjungan antrean dalam satu proses.',
    steps: [
        'Pada menu Pasien, pilih Input Pasien Baru.',
        'Isi Nama Lengkap, Tanggal Lahir, Jenis Kelamin, dan Nomor HP/WhatsApp. Isi NIK, Pekerjaan, dan Alamat bila tersedia.',
        'Pilih Poli Tujuan pada bagian Pendaftaran Antrean Poli Hari Ini.',
        'Periksa kembali data, lalu klik Simpan Pasien & Buat Antrean.',
        'Setelah berhasil, catat nomor RM dan nomor antrean yang tampil pada daftar pasien/kunjungan.',
    ],
    screenshot: image('05-staff-input-pasien-baru'),
    caption: 'Gambar 4.4 Form identitas pasien baru dan pemilihan poli',
    note: 'Nomor rekam medis pasien bersifat tetap. Nomor antrean adalah nomor kunjungan dan dibuat berdasarkan poli serta tanggal.',
});
await addProcedure({
    title: '4.5 Memperbarui atau Menghapus Data Pasien',
    intro: 'Data identitas pasien dapat diperbarui dari daftar pasien. Riwayat kunjungan yang sudah tercatat tidak boleh dihapus melalui penghapusan data pasien.',
    steps: [
        'Cari pasien menggunakan nama, NIK, nomor HP, atau nomor RM.',
        'Gunakan aksi Edit untuk memperbarui identitas, lalu simpan perubahan.',
        'Gunakan Hapus hanya untuk pasien yang belum memiliki riwayat kunjungan dan setelah memastikan identitasnya benar.',
        'Jika pasien memiliki riwayat kunjungan, jangan mencoba menghapusnya; perbaiki informasi melalui pembaruan data yang tersedia.',
    ],
    screenshot: image('04-staff-pengelolaan-pasien'),
    caption: 'Gambar 4.5 Tabel pasien, status kunjungan aktif, dan kolom aksi',
    note: 'Aplikasi menolak penghapusan pasien yang telah memiliki kunjungan agar riwayat medis tidak ikut hilang.',
});
await addProcedure({
    title: '4.6 Riwayat dan Detail Kunjungan',
    intro: 'Riwayat Kunjungan dipakai untuk menelusuri layanan, memeriksa status, dan membuka rincian pasien, pemeriksaan, serta pembayaran.',
    steps: [
        'Buka Riwayat Kunjungan pada sidebar.',
        'Cari berdasarkan nama pasien, NIK, nomor RM, atau nomor kunjungan.',
        'Atur rentang waktu, poli, dan status bila diperlukan, lalu klik Terapkan Filter. Gunakan Reset untuk menghapus filter.',
        'Klik Detail pada baris kunjungan untuk membuka informasi pasien, hasil pemeriksaan, pembayaran, dan status.',
        'Gunakan pagination untuk berpindah halaman hasil.',
    ],
    screenshot: image('06-staff-riwayat-kunjungan'),
    caption: 'Gambar 4.6 Filter dan tabel riwayat kunjungan',
});
await addProcedure({
    title: '4.7 Membatalkan Kunjungan',
    intro: 'Kunjungan berstatus Antre atau Menunggu dapat dibatalkan. Pembatalan tidak menghapus data dari riwayat.',
    steps: [
        'Dari Riwayat Kunjungan, buka Detail pada kunjungan yang akan dibatalkan.',
        'Pada bagian Batalkan Kunjungan, pilih kategori pembatalan.',
        'Isi keterangan. Keterangan wajib jika kategori Lainnya dipilih.',
        'Klik Batalkan Kunjungan, lalu setujui konfirmasi.',
        'Periksa status Dibatalkan dan informasi kategori, waktu, serta nama pembatal pada detail/riwayat.',
    ],
    screenshot: image('07-staff-detail-kunjungan'),
    caption: 'Gambar 4.7 Detail kunjungan, hasil pemeriksaan, dan bagian pembatalan',
    note: 'Kunjungan yang sudah Siap Bayar atau Selesai tidak dapat dibatalkan dari alur ini. Untuk kesalahan transaksi yang sudah lunas, hubungi pengelola klinik.',
});
await addProcedure({
    title: '4.8 Memproses Pembayaran',
    intro: 'Transaksi muncul setelah Dokter menyimpan pemeriksaan. Kasir mengisi biaya dan pembayaran pada modal Proses Pembayaran.',
    steps: [
        'Buka menu Transaksi dan cari pasien berstatus menunggu pembayaran.',
        'Klik Proses Bayar pada baris pasien. Periksa nama, poli, nomor antrean, diagnosis, dan resep yang ditampilkan.',
        'Periksa nilai Jasa Dokter / Tindakan; sesuaikan dengan tarif klinik. Isi Biaya Obat.',
        'Periksa Total Biaya Tagihan yang dihitung otomatis.',
        'Pilih Cash, QRIS, atau Debit. Isi Uang Diterima/Nominal Pembayaran dan pastikan nilainya tidak kurang dari total.',
        'Periksa Uang Kembalian, lalu klik Proses & Selesai. Pastikan pesan berhasil tampil.',
    ],
    screenshot: image('08-staff-proses-transaksi'),
    caption: 'Gambar 4.8 Daftar pasien yang menunggu pembayaran; modal pembayaran dibuka melalui Proses Bayar',
    note: 'Nilai Jasa Dokter/Tindakan dapat terisi awal oleh aplikasi. Pastikan nominalnya benar menurut ketentuan klinik sebelum menyelesaikan transaksi. Total dan kembalian dihitung ulang oleh server.',
});
await addProcedure({
    title: '4.9 Riwayat Transaksi dan Cetak Struk',
    intro: 'Riwayat Transaksi hanya menampilkan pembayaran lunas dan menyediakan ringkasan pendapatan sesuai filter.',
    steps: [
        'Buka Riwayat Transaksi pada sidebar.',
        'Cari berdasarkan nama pasien atau nomor transaksi, atau isi Dari Tanggal dan Sampai Tanggal.',
        'Klik Filter untuk menerapkan pencarian. Klik Reset untuk kembali ke seluruh transaksi.',
        'Periksa total pendapatan, jumlah transaksi lunas, tanggal, pasien, diagnosis, nilai pembayaran, dan status.',
        'Klik Detail pada baris untuk melihat rincian, lalu Cetak Struk bila diperlukan.',
    ],
    screenshot: image('09-staff-riwayat-transaksi'),
    caption: 'Gambar 4.9 Ringkasan, filter tanggal, dan tabel transaksi lunas',
    note: 'Jika jendela cetak tidak muncul, izinkan pop-up pada browser dan ulangi proses Cetak Struk.',
});
await addProcedure({
    title: '4.10 Melihat Rekam Medis Pasien sebagai Staff',
    intro: 'Staff dapat mencari dan melihat rekam medis untuk kebutuhan administrasi. Staff tidak dapat menambah atau mengubah hasil pemeriksaan.',
    steps: [
        'Buka halaman Rekam Medis Pasien. Jika tidak ada tautan pada sidebar Staff, akses /rekam-medis-pasien setelah login.',
        'Cari berdasarkan nama pasien, nomor RM, atau NIK, lalu klik Cari Data.',
        'Periksa ringkasan kunjungan terakhir, diagnosis, dan nama Dokter.',
        'Klik Lihat Detail RM untuk membaca riwayat pemeriksaan pasien.',
        'Klik PDF untuk mengunduh salinan riwayat. Jika riwayat lebih dari 50 pemeriksaan, pilih dan unduh semua bagian yang tersedia.',
    ],
    screenshot: image('10-staff-riwayat-rekam-medis'),
    caption: 'Gambar 4.10 Daftar pasien dengan rekam medis dan aksi detail/PDF',
});

addChapter('5. Panduan Penggunaan untuk Dokter', 'Akun Dokter terhubung ke satu poli. Dashboard, antrean, pemeriksaan, dan riwayat yang ditampilkan mengikuti poli tersebut. Pastikan nama poli di header/dashboard sesuai dengan penugasan.');
await addProcedure({
    title: '5.1 Dashboard Dokter',
    intro: 'Dashboard Dokter menampilkan ringkasan pasien dan antrean pada poli akun yang sedang digunakan.',
    steps: [
        'Login menggunakan email dan password akun Dokter.',
        'Periksa nama dan poli yang tampil pada header/banner.',
        'Tinjau jumlah pasien, antrean aktif, pasien selesai diperiksa, dan tabel antrean hari ini.',
        'Klik Panggil Pasien Selanjutnya untuk memanggil antrean paling awal yang tersedia.',
        'Jika ada pasien yang sedang diperiksa, selesaikan atau kembalikan pasien tersebut sebelum memanggil pasien berikutnya.',
    ],
    screenshot: image('11-dokter-dashboard-dokter'),
    caption: 'Gambar 5.1 Dashboard Dokter dengan antrean sesuai poli akun',
});
await addProcedure({
    title: '5.2 Membuka Antrean dan Memulai Pemeriksaan',
    intro: 'Halaman Periksa Pasien menampilkan kunjungan pada poli Dokter. Pasien berstatus Menunggu harus dimulai sebelum formulir pemeriksaan dapat diisi.',
    steps: [
        'Pilih Periksa Pasien pada sidebar.',
        'Periksa nomor antrean, nama pasien, poli, waktu masuk, dan status pada kartu antrean.',
        'Jika status Menunggu, klik Mulai Periksa. Setelah berhasil, status berubah menjadi Sedang Diperiksa dan formulir terbuka.',
        'Alternatifnya, gunakan Panggil Pasien Selanjutnya pada dashboard untuk memindahkan pasien antre pertama ke status pemeriksaan.',
        'Selesaikan pemeriksaan yang sedang aktif sebelum memanggil pasien kedua pada poli yang sama.',
    ],
    screenshot: image('12-dokter-pemeriksaan-poli-umum'),
    caption: 'Gambar 5.2 Kartu antrean Dokter dan formulir pemeriksaan Poli Umum',
    note: 'Pemeriksaan yang sudah dimulai dapat diselesaikan setelah hari berganti. Antrean lama yang belum dimulai tidak otomatis menjadi antrean hari baru.',
});
await addProcedure({
    title: '5.3 Mengisi Rekam Medis Poli Umum',
    intro: 'Gunakan formulir pemeriksaan untuk mencatat informasi klinis sesuai hasil pemeriksaan. Isi hanya data yang benar dan telah diverifikasi.',
    steps: [
        'Pada formulir pasien berstatus Sedang Diperiksa, isi Tanda Vital bila tersedia: Suhu, Tinggi, Berat, dan Tekanan Darah. Bagian ini opsional.',
        'Isi Keluhan Pasien, Diagnosa, dan Resep Obat. Ketiga kolom bertanda bintang wajib diisi.',
        'Tinjau kembali catatan sebelum disimpan.',
        'Klik Simpan & Selesai Periksa satu kali, lalu tunggu pesan berhasil.',
        'Pastikan kunjungan berubah menjadi Siap Bayar dan pasien dapat diproses Staff/Kasir.',
    ],
    screenshot: image('12-dokter-pemeriksaan-poli-umum'),
    caption: 'Gambar 5.3 Form pemeriksaan poli umum: tanda vital dan catatan wajib',
});
await addProcedure({
    title: '5.4 Mengisi Pemeriksaan Poli Anak',
    intro: 'Alur Poli Anak menggunakan prosedur pemeriksaan standar yang sama; antrean dan rekam medis dibatasi pada Poli Anak.',
    steps: [
        'Pastikan akun Dokter pada header terhubung ke Poli Anak.',
        'Pada pasien Menunggu, klik Mulai Periksa. Jika pasien telah dipanggil, lanjutkan pada formulir yang tersedia.',
        'Catat tanda vital bila tersedia, lalu isi Keluhan Pasien, Diagnosa, dan Resep Obat.',
        'Klik Simpan & Selesai Periksa dan pastikan status menjadi Siap Bayar.',
    ],
    screenshot: image('13-dokter-pemeriksaan-poli-anak'),
    caption: 'Gambar 5.4 Antrean Poli Anak; formulir muncul setelah Mulai Periksa',
    note: 'Screenshot contoh menunjukkan pasien masih berstatus Menunggu. Klik Mulai Periksa terlebih dahulu untuk menampilkan formulir.',
});
await addProcedure({
    title: '5.5 Mengisi Pemeriksaan Poli Gigi',
    intro: 'Alur Poli Gigi juga menggunakan formulir pemeriksaan standar. Hanya kunjungan dengan poli tujuan Poli Gigi yang dapat dibuka Dokter pada poli ini.',
    steps: [
        'Pastikan akun Dokter terhubung ke Poli Gigi.',
        'Buka Periksa Pasien. Bila ada pasien menunggu, klik Mulai Periksa atau panggil dari dashboard.',
        'Isi data tanda vital bila tersedia serta Keluhan Pasien, Diagnosa, dan Resep Obat.',
        'Simpan dengan Simpan & Selesai Periksa. Periksa pesan sukses dan status Siap Bayar.',
    ],
    screenshot: image('14-dokter-pemeriksaan-poli-gigi'),
    caption: 'Gambar 5.5 Halaman antrean Poli Gigi pada data contoh',
    note: 'Screenshot contoh menampilkan antrean kosong. Jika halaman seperti ini tampil, berarti tidak ada antrean aktif pada poli/tanggal tersebut.',
});
await addProcedure({
    title: '5.6 Mengisi Pemeriksaan Poli Spesialis Kandungan',
    intro: 'Selain catatan standar, Poli Spesialis Kandungan memiliki field Hasil Pemeriksaan yang wajib diisi dan Catatan Tambahan yang opsional.',
    steps: [
        'Pastikan akun Dokter terhubung ke Poli Spesialis Kandungan dan pasien berada di antrean poli tersebut.',
        'Mulai pemeriksaan pasien, lalu isi tanda vital bila tersedia dan Keluhan Pasien.',
        'Isi Hasil Pemeriksaan. Field ini wajib diisi untuk Poli Spesialis Kandungan.',
        'Isi Diagnosa dan Resep Obat. Catatan Tambahan dapat digunakan untuk informasi lanjutan yang relevan dan bersifat opsional.',
        'Tinjau seluruh isian, klik Simpan & Selesai Periksa, lalu pastikan status berubah menjadi Siap Bayar.',
    ],
    screenshot: image('15-dokter-pemeriksaan-poli-spesialis-kandungan'),
    caption: 'Gambar 5.6 Form Poli Spesialis Kandungan dengan Hasil Pemeriksaan dan Catatan Tambahan',
});
await addProcedure({
    title: '5.7 Mengembalikan Pasien ke Antrean atau Menandai Tidak Hadir',
    intro: 'Tindakan ini tersedia pada kartu pasien yang sedang dipanggil/diperiksa dan hanya untuk kunjungan yang sesuai poli akun Dokter.',
    steps: [
        'Pilih Kembalikan ke Antrean jika pasien perlu menunggu kembali pada hari yang sama. Nomor antrean pasien akan diperbarui ke posisi belakang.',
        'Pilih Pasien Tidak Hadir bila pasien tidak datang setelah dipanggil, lalu setujui konfirmasi.',
        'Periksa pesan sistem dan status pasien pada daftar antrean.',
    ],
    screenshot: image('12-dokter-pemeriksaan-poli-umum'),
    caption: 'Gambar 5.7 Tombol aksi pada kartu pasien yang sedang diperiksa',
    note: 'Pasien yang dikembalikan harus berasal dari hari yang sama. Pembatalan oleh Dokter hanya tersedia untuk pasien yang sudah dipanggil dan hanya dalam poli Dokter tersebut.',
});
await addProcedure({
    title: '5.8 Membaca Riwayat Rekam Medis dan Ekspor PDF',
    intro: 'Dokter dapat membaca riwayat pemeriksaan sesuai poli yang dimiliki. Rekam medis dari poli lain tidak ditampilkan dalam daftar/detail/PDF Dokter.',
    steps: [
        'Pilih Rekam Medis Pasien pada sidebar.',
        'Cari pasien berdasarkan Nama, No. RM, atau NIK, lalu klik Cari Data.',
        'Pilih Lihat Detail RM untuk membaca identitas pasien dan riwayat pemeriksaan dari poli Dokter.',
        'Klik Ekspor Seluruh Riwayat PDF pada halaman detail untuk mengunduh file.',
        'Jika jumlah pemeriksaan lebih dari 50, pilih bagian PDF dan unduh setiap bagian agar seluruh riwayat diperoleh.',
    ],
    screenshot: image('16-dokter-riwayat-rekam-medis-dokter'),
    caption: 'Gambar 5.8 Daftar riwayat rekam medis Dokter yang dibatasi berdasarkan poli',
    note: 'Screenshot contoh menampilkan empty state karena data fixture tidak memiliki catatan yang cocok pada scope halaman. Jika pasien belum terlihat, periksa kata kunci dan poli akun.',
});

addChapter('6. Alur Layanan dan Aturan Penting');
addHeading('6.1 Urutan Status Kunjungan', HeadingLevel.HEADING_2);
addText('Ikuti urutan layanan berikut agar perpindahan tugas antara Staff, Dokter, dan Kasir berjalan tertib:');
addSteps([
    'Staff mendaftarkan pasien baru atau pasien lama ke salah satu poli. Status awal: Antre.',
    'Dokter memanggil pasien atau memilih Mulai Periksa. Status: Menunggu Dokter / Sedang Diperiksa.',
    'Dokter mengisi lalu menyimpan rekam medis. Status: Siap Bayar; transaksi belum lunas tersedia di menu Transaksi.',
    'Staff mencatat biaya dan pembayaran. Jika uang cukup, status transaksi menjadi Lunas dan status kunjungan menjadi Selesai.',
]);
addHeading('6.2 Empat Poli dan Nomor Antrean', HeadingLevel.HEADING_2);
addTable(['Poli', 'Awalan nomor antrean'], [70, 30], [
    ['Poli Umum', 'A'],
    ['Poli Gigi', 'G'],
    ['Poli Anak', 'C'],
    ['Poli Spesialis Kandungan', 'K'],
]);
addNote('Awalan menunjukkan poli, sedangkan angka menunjukkan urutan antrean. Nomor dapat kembali dimulai dari 001 untuk tanggal/poli yang berbeda.');
addHeading('6.3 Aturan yang Perlu Diingat', HeadingLevel.HEADING_2);
addBullets([
    'Satu pasien hanya memiliki satu kunjungan aktif pada tanggal yang sama.',
    'Dokter hanya melayani poli yang ditetapkan pada profil akunnya.',
    'Rekam medis yang disimpan mengarahkan kunjungan ke Kasir; pembayaran tidak dilakukan oleh Dokter.',
    'Pembayaran tidak dapat diselesaikan bila nominal dibayar kurang dari total tagihan.',
    'Kunjungan yang dibatalkan tetap tersimpan dalam riwayat.',
    'Tanggal dan waktu operasional aplikasi mengikuti WIB.',
]);

addChapter('7. Pemecahan Kendala Umum');
addTable(['Kendala', 'Langkah penanganan'], [34, 66], [
    ['Tidak dapat login', 'Pastikan email dan password benar. Periksa Caps Lock dan koneksi. Jika lupa password, pilih Lupa Password? untuk melihat arahan menghubungi Staff.'],
    ['Menu/halaman menolak akses', 'Pastikan login menggunakan role yang benar. Staff dan Dokter memiliki menu berbeda. Dokter juga dibatasi ke poli akun.'],
    ['Pasien tidak bisa didaftarkan ulang', 'Periksa apakah pasien masih memiliki kunjungan aktif hari ini. Selesaikan pembayaran atau lakukan pembatalan sesuai prosedur sebelum membuat antrean baru.'],
    ['Antrean tidak terlihat oleh Dokter', 'Pastikan poli pada akun Dokter sesuai poli kunjungan. Antrean baru berasal dari tanggal berjalan; kunjungan yang sudah dimulai dapat tetap tampil sampai selesai.'],
    ['Tidak dapat mulai pemeriksaan', 'Periksa apakah masih ada pasien lain yang sedang diperiksa pada poli tersebut. Selesaikan pemeriksaan aktif terlebih dahulu.'],
    ['Rekam medis tidak tersimpan', 'Isi Keluhan, Diagnosa, dan Resep Obat. Untuk Poli Spesialis Kandungan, Hasil Pemeriksaan juga wajib. Periksa pesan validasi sebelum mengirim ulang.'],
    ['Pembayaran ditolak', 'Periksa biaya, metode, dan nominal dibayar. Nominal pembayaran harus sama dengan atau lebih besar dari total tagihan.'],
    ['PDF tidak memuat seluruh riwayat', 'Riwayat panjang dibagi menjadi beberapa file, masing-masing maksimal 50 pemeriksaan. Unduh seluruh bagian yang ditawarkan.'],
]);
addText('Jika kendala tetap terjadi, catat role, poli, waktu kejadian, nomor RM/kunjungan bila tersedia, dan pesan kesalahan. Sampaikan informasi tersebut kepada pengelola aplikasi.');

addChapter('8. Glosarium');
addTable(['Istilah', 'Arti'], [30, 70], [
    ['No. RM', 'Nomor rekam medis tetap sebagai identitas pasien di aplikasi.'],
    ['Kunjungan', 'Catatan pelayanan pasien pada tanggal dan poli tertentu.'],
    ['Antrean', 'Urutan pasien yang menunggu layanan pada suatu poli.'],
    ['Rekam Medis', 'Catatan keluhan, tanda vital bila diisi, hasil pemeriksaan, diagnosis, dan resep dari pemeriksaan.'],
    ['Siap Bayar', 'Status kunjungan setelah Dokter menyimpan pemeriksaan dan sebelum transaksi dilunasi.'],
    ['Lunas', 'Status transaksi setelah pembayaran berhasil dicatat.'],
    ['Poli', 'Tujuan layanan yang menentukan antrean dan cakupan akses Dokter.'],
]);

addChapter('Informasi Perubahan Dokumen');
addTable(['Versi', 'Tanggal', 'Ringkasan'], [15, 25, 60], [
    ['1.0', '4 November 2026', 'Penerbitan awal manualbook penggunaan Sistem Informasi Klinik Lala Medicare untuk Staff/Kasir dan Dokter.'],
]);
addText('Dokumen ini merupakan panduan operasional berdasarkan fitur yang tersedia pada versi aplikasi saat penyusunan. Ikuti kebijakan klinik dan arahan tenaga medis untuk keputusan pelayanan pasien.');

const footer = new Footer({
    children: [new Paragraph({
        alignment: AlignmentType.CENTER,
        spacing: { before: 0, after: 0 },
        children: [
            new TextRun({ text: 'Klinik Lala Medicare  |  Halaman ', font, size: bodySize }),
            new TextRun({ children: [PageNumber.CURRENT], font, size: bodySize }),
        ],
    })],
});

const document = new Document({
    creator: 'Klinik Lala Medicare',
    title: 'Buku Petunjuk Penggunaan Aplikasi Klinik Lala Medicare',
    subject: 'Panduan Staff/Kasir dan Dokter',
    description: 'Manual penggunaan sistem informasi Klinik Lala Medicare.',
    sections: [{
        properties: {
            page: {
                size: { width: 11906, height: 16838 },
                margin: { top: 2268, right: 1701, bottom: 1701, left: 2268 },
                pageNumbers: { start: 1, formatType: NumberFormat.DECIMAL },
            },
        },
        footers: { default: footer },
        children,
    }],
        pageNumbers: estimatePageNumbers,
    styles: {
        paragraphStyles: [
            { id: 'TOC1', name: 'TOC 1', basedOn: 'Normal', run: { font, size: bodySize }, paragraph: { alignment: AlignmentType.JUSTIFIED, spacing: { before: 0, after: 40, line: 360, lineRule: 'auto' } } },
            { id: 'TOC2', name: 'TOC 2', basedOn: 'Normal', run: { font, size: bodySize }, paragraph: { alignment: AlignmentType.JUSTIFIED, spacing: { before: 0, after: 40, line: 360, lineRule: 'auto' } } },
        ],
    },
    features: { updateFields: true },
});

await fs.writeFile(output, await Packer.toBuffer(document));
console.log(`Manualbook dibuat: ${path.relative(root, output)}`);
console.log(`Screenshot tersemat: 16; ukuran teks isi 12 pt.`);
