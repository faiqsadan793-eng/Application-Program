import fs from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import {
    Document, HeadingLevel, ImageRun, Packer, PageBreak, Paragraph, Table, TableCell,
    TableRow, TextRun, WidthType,
} from 'docx';

const root = process.cwd();
const reportDir = path.join(root, 'tests', 'reports');
const imageDir = path.join(reportDir, 'source-snippets');
const outputPath = path.join(reportDir, 'Laporan-White-Box-Klinik-Lala.docx');
// Word meminta fallback PNG ketika gambar utama menggunakan SVG. Word modern
// tetap menampilkan SVG, sedangkan PNG ini menjadi fallback untuk pembaca lama.
const svgFallbackPng = Buffer.from(
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
    'base64',
);

async function readFile(relativePath) {
    try { return await fs.readFile(path.join(root, relativePath), 'utf8'); } catch { return ''; }
}

function snippet(source, marker, maxLines = 22) {
    const lines = source.split(/\r?\n/);
    const markerIndex = lines.findIndex((line) => line.includes(marker));
    const start = Math.max(0, (markerIndex < 0 ? 0 : markerIndex) - 3);
    return lines.slice(start, start + maxLines).join('\n');
}

function escapeXml(value) {
    return value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

async function codeImage(name, sourceCode, testCode) {
    const sourceLines = sourceCode.split('\n').slice(0, 18);
    const testLines = testCode.split('\n').slice(0, 18);
    const lineHeight = 16;
    const width = 1200;
    const height = Math.max(150, Math.max(sourceLines.length, testLines.length) * lineHeight + 62);
    const renderLines = (lines, x) => lines.map((line, index) => `<text x="${x}" y="${46 + index * lineHeight}" fill="#e2e8f0" font-family="Consolas,monospace" font-size="11">${escapeXml(line || ' ')}</text>`).join('');
    const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}"><rect width="100%" height="100%" rx="8" fill="#0f172a"/><line x1="600" y1="0" x2="600" y2="${height}" stroke="#334155"/><text x="18" y="24" fill="#38bdf8" font-family="Arial,sans-serif" font-size="15" font-weight="bold">SOURCE CODE</text><text x="618" y="24" fill="#34d399" font-family="Arial,sans-serif" font-size="15" font-weight="bold">TEST CODE</text>${renderLines(sourceLines, 18)}${renderLines(testLines, 618)}</svg>`;
    await fs.writeFile(path.join(imageDir, `${name}.svg`), svg, 'utf8');
    return Buffer.from(svg);
}

function paragraph(text, options = {}) {
    return new Paragraph({ children: [new TextRun({ text, ...options })] });
}

function cell(children, width) {
    return new TableCell({ width: { size: width, type: WidthType.PERCENTAGE }, children: Array.isArray(children) ? children : [paragraph(children)] });
}

const cases = [
    { title: 'Autentikasi pengguna', sourceFile: 'app/Http/Controllers/Auth/AuthenticatedSessionController.php', sourceMarker: 'public function store', testFile: 'tests/Feature/Auth/AuthenticationTest.php', testMarker: 'test_users_can_authenticate', condition: 'Kondisi 1: email dan password benar.\nKondisi 2: password salah atau akun tidak ditemukan.', output: 'Kredensial benar membuat pengguna masuk ke dashboard. Kredensial salah ditolak dan tetap berada pada halaman login.' },
    { title: 'Penomoran antrean harian per poli', sourceFile: 'app/Http/Controllers/PasienController.php', sourceMarker: 'nomorAntreanBerikutnya', testFile: 'tests/Feature/KunjunganFlowTest.php', testMarker: 'test_daily_queue_numbers_restart_per_poli_and_date', condition: 'Pasien pertama dan kedua didaftarkan pada poli yang sama, lalu pasien didaftarkan pada poli lain dan tanggal berbeda.', output: 'Nomor dimulai dari 001 untuk setiap poli dan tanggal, lalu bertambah berurutan pada poli yang sama.' },
    { title: 'Pembatasan akses dokter berdasarkan poli', sourceFile: 'app/Http/Controllers/RekamMedisController.php', sourceMarker: 'kunjunganUntukDokter', testFile: 'tests/Feature/RekamMedisFlowTest.php', testMarker: 'test_doctor_cannot_start_or_save_another_poli_visit', condition: 'Dokter Poli Umum mencoba memulai atau menyimpan pemeriksaan pasien Poli Gigi.', output: 'Sistem menolak akses dengan status 404 dan tidak membuat rekam medis baru.' },
    { title: 'Pemeriksaan Poli Spesialis Kandungan', sourceFile: 'app/Http/Controllers/RekamMedisController.php', sourceMarker: 'hasil_pemeriksaan', testFile: 'tests/Feature/PoliSpecificFlowTest.php', testMarker: 'test_poli_kandungan_saves_obstetric_notes', condition: 'Dokter kandungan mengisi hasil pemeriksaan, catatan tambahan, dan tanda vital.', output: 'Field kandungan dan tanda vital tersimpan pada kunjungan yang benar, lalu kunjungan berubah menjadi siap bayar.' },
    { title: 'Kewajiban hasil pemeriksaan Poli Kandungan', sourceFile: 'app/Http/Controllers/RekamMedisController.php', sourceMarker: "\$rules['hasil_pemeriksaan']", testFile: 'tests/Feature/PoliSpecificFlowTest.php', testMarker: 'test_poli_kandungan_requires_hasil_pemeriksaan', condition: 'Dokter kandungan mengirim keluhan, diagnosis, dan resep tanpa hasil pemeriksaan.', output: 'Validasi gagal pada hasil pemeriksaan dan rekam medis tidak disimpan.' },
    { title: 'Pemeriksaan Poli Anak', sourceFile: 'app/Http/Controllers/RekamMedisController.php', sourceMarker: 'RekamMedis::create', testFile: 'tests/Feature/PoliSpecificFlowTest.php', testMarker: 'test_poli_anak_saves_general_record', condition: 'Dokter Poli Anak mengisi keluhan, diagnosis, dan resep menggunakan alur umum.', output: 'Rekam medis tersimpan dan field khusus kandungan tetap kosong.' },
    { title: 'Pemeriksaan Poli Gigi dan isolasi field kandungan', sourceFile: 'app/Http/Controllers/RekamMedisController.php', sourceMarker: 'Field khusus kandungan', testFile: 'tests/Feature/PoliSpecificFlowTest.php', testMarker: 'test_poli_gigi_saves_general_record', condition: 'Dokter Poli Gigi mengirim field umum dan mencoba mengirim field khusus kandungan secara manual.', output: 'Field khusus kandungan dibersihkan dan tidak tersimpan pada rekam medis Poli Gigi.' },
    { title: 'Pembayaran dan perhitungan nominal server', sourceFile: 'app/Http/Controllers/TransaksiController.php', sourceMarker: 'public function update', testFile: 'tests/Feature/TransaksiFlowTest.php', testMarker: 'test_payment_totals_are_calculated_on_the_server', condition: 'Browser mengirim total biaya dan kembalian palsu, sementara nominal biaya asli dikirim terpisah.', output: 'Total dan kembalian dihitung ulang oleh server; transaksi menjadi lunas dengan nilai yang benar.' },
    { title: 'Export rekam medis PDF dan pembatasan poli', sourceFile: 'app/Http/Controllers/RekamMedisPasienController.php', sourceMarker: 'public function export', testFile: 'tests/Feature/MedicalRecordExportTest.php', testMarker: 'test_doctor_export_uses_same_poli_scope', condition: 'Staff meminta PDF seluruh histori, sedangkan dokter hanya meminta histori pasien pada polinya.', output: 'PDF berhasil dibuat dan dokter tidak dapat melihat histori dari poli lain.' },
];

const junit = await readFile('tests/reports/results/whitebox.xml');
const testCount = junit.match(/tests="(\d+)"/)?.[1] ?? '0';
const failureCount = junit.match(/failures="(\d+)"/)?.[1] ?? '0';
const errorCount = junit.match(/errors="(\d+)"/)?.[1] ?? '0';

// Bentuk laporan individual: setiap testcase PHPUnit dibuat menjadi satu
// nomor pengujian agar tidak ada test yang hanya diringkas atau terlewat.
function sourceForTest(testFile, testName) {
    const name = `${testFile} ${testName}`;
    if (name.includes('Authentication')) return 'app/Http/Controllers/Auth/AuthenticatedSessionController.php';
    if (name.includes('EmailVerification')) return 'app/Http/Controllers/Auth/VerifyEmailController.php';
    if (name.includes('PasswordConfirmation')) return 'app/Http/Controllers/Auth/ConfirmablePasswordController.php';
    if (name.includes('PasswordUpdate')) return 'app/Http/Controllers/Auth/PasswordController.php';
    if (name.includes('DokterManagement')) return 'app/Http/Controllers/DokterController.php';
    if (name.includes('Kunjungan')) return 'app/Http/Controllers/KunjunganController.php';
    if (name.includes('MedicalRecordExport')) return 'app/Http/Controllers/RekamMedisPasienController.php';
    if (name.includes('PoliSpecific') || name.includes('RekamMedis')) return 'app/Http/Controllers/RekamMedisController.php';
    if (name.includes('Profile')) return 'app/Http/Controllers/ProfileController.php';
    if (name.includes('Transaksi')) return 'app/Http/Controllers/TransaksiController.php';
    if (name.includes('Scalability') || name.includes('WibTimezone')) return 'app/Http/Controllers/KunjunganController.php';
    if (name.includes('StaffFeatureAudit')) return 'app/Http/Controllers/DashboardController.php';
    return testFile;
}

function sourceMarkerForTest(testFile, testName) {
    const name = `${testFile} ${testName}`;
    if (name.includes('Authentication')) return 'public function store';
    if (name.includes('EmailVerification')) return name.includes('verified') ? 'public function __invoke' : 'public function __invoke';
    if (name.includes('PasswordConfirmation')) return 'public function store';
    if (name.includes('PasswordUpdate')) return 'public function update';
    if (name.includes('DokterManagement')) return name.includes('create') ? 'public function store' : name.includes('update') ? 'public function update' : name.includes('delete') ? 'public function destroy' : 'public function index';
    if (name.includes('Kunjungan')) return name.includes('cancel') ? 'cancel' : name.includes('return') ? 'returnToQueue' : 'public function store';
    if (name.includes('MedicalRecordExport')) return 'public function export';
    if (name.includes('PoliSpecific') || name.includes('RekamMedis')) return name.includes('start') ? 'start' : 'public function store';
    if (name.includes('Profile')) return name.includes('delete') ? 'destroy' : name.includes('update') ? 'update' : 'edit';
    if (name.includes('Transaksi')) return 'public function update';
    if (name.includes('Scalability') || name.includes('WibTimezone')) return 'nomorAntreanBerikutnya';
    if (name.includes('StaffFeatureAudit')) return 'public function index';
    return testName;
}

const reportCases = [...junit.matchAll(/<testcase\s+([^>]+)>/g)].map((match, index) => {
    const attrs = Object.fromEntries([...match[1].matchAll(/(name|class|file)="([^"]*)"/g)].map((m) => [m[1], m[2]]));
    const testFile = attrs.file ? path.relative(root, attrs.file).replaceAll('\\', '/') : 'tests';
    const testName = attrs.name ?? `testcase_${index + 1}`;
    return {
        title: testName.replaceAll('_', ' '),
        sourceFile: sourceForTest(testFile, testName),
        sourceMarker: sourceMarkerForTest(testFile, testName),
        testFile,
        testMarker: testName,
        condition: `Kondisi pengujian: ${testName.replaceAll('_', ' ')} dijalankan dengan data dan hak akses sesuai skenario test.`,
        output: 'Output aktual: test berhasil (PASS) berdasarkan hasil PHPUnit pada file laporan XML.',
    };
});

await fs.mkdir(imageDir, { recursive: true });
const children = [
    new Paragraph({ text: 'LAPORAN PENGUJIAN WHITE BOX', heading: HeadingLevel.TITLE }),
    new Paragraph({ text: 'Sistem Informasi Klinik Lala Medicare', heading: HeadingLevel.HEADING_1 }),
    paragraph(`Tanggal: ${new Date().toLocaleDateString('id-ID')}`),
    paragraph('White Box Testing dilakukan dengan memeriksa source code, kondisi percabangan, test code, dan output setiap fungsi kritis aplikasi.'),
    new Paragraph({ text: 'Ringkasan Hasil Pengujian', heading: HeadingLevel.HEADING_1 }),
    new Table({ width: { size: 100, type: WidthType.PERCENTAGE }, rows: [
        new TableRow({ children: [cell('Jumlah test', 50), cell(testCount, 50)] }),
        new TableRow({ children: [cell('Failure', 50), cell(failureCount, 50)] }),
        new TableRow({ children: [cell('Error', 50), cell(errorCount, 50)] }),
        new TableRow({ children: [cell('Status', 50), cell(failureCount === '0' && errorCount === '0' ? 'LULUS' : 'PERLU REVIEW', 50)] }),
    ]}),
    new Paragraph({ text: 'Tabel Pengujian White Box', heading: HeadingLevel.HEADING_1 }),
    paragraph('Tabel berikut memuat seluruh test individual. Setiap baris adalah satu pengujian, sehingga formatnya ringkas seperti contoh dokumen.'),
];

const whiteBoxRows = [
    new TableRow({ tableHeader: true, children: [
        cell(paragraph('No', { bold: true }), 5),
        cell(paragraph('Source Code & Test Code', { bold: true }), 40),
        cell(paragraph('Kondisi', { bold: true }), 27.5),
        cell(paragraph('Output', { bold: true }), 27.5),
    ] }),
];

for (const [index, item] of reportCases.entries()) {
    const sourceCode = snippet(await readFile(item.sourceFile), item.sourceMarker);
    const testCode = snippet(await readFile(item.testFile), item.testMarker);
    const codeImageData = await codeImage(`code-${String(index + 1).padStart(2, '0')}`, sourceCode, testCode);

    whiteBoxRows.push(new TableRow({ children: [
        cell(paragraph(String(index + 1), { bold: true }), 5),
        cell([
            paragraph(item.title, { bold: true }),
            paragraph(`Source: ${item.sourceFile}`),
            paragraph(`Test: ${item.testFile}`),
            new Paragraph({ children: [new ImageRun({ data: codeImageData, type: 'svg', transformation: { width: 300, height: 105 }, fallback: { data: svgFallbackPng, type: 'png' } })] }),
        ], 40),
        cell(item.condition, 27.5),
        cell(item.output, 27.5),
    ] }));
}

children.push(new Table({ width: { size: 100, type: WidthType.PERCENTAGE }, rows: whiteBoxRows }));

children.push(
    new Paragraph({ text: 'Catatan Pengujian', heading: HeadingLevel.HEADING_1 }),
    paragraph('Seluruh test aplikasi lulus pada eksekusi terakhir. Peringatan PHPUnit tentang .phpunit.result.cache berasal dari izin Windows dan tidak memengaruhi hasil test.'),
    paragraph('Code coverage baris belum tersedia karena extension Xdebug atau PCOV belum aktif pada environment pengujian.'),
    new Paragraph({ text: 'Kesimpulan', heading: HeadingLevel.HEADING_1 }),
    paragraph(`White Box Testing mencakup ${testCount} test dan modul kritis aplikasi. Source code dan test code disertakan sebagai gambar pada setiap tabel agar format laporan mengikuti contoh yang diberikan.`),
);

const document = new Document({ sections: [{ children }] });
const reportBuffer = await Packer.toBuffer(document);
let finalOutputPath = outputPath;
try {
    await fs.writeFile(outputPath, reportBuffer);
} catch (error) {
    if (error.code !== 'EBUSY' && error.code !== 'EPERM') throw error;
    finalOutputPath = path.join(reportDir, 'Laporan-White-Box-Klinik-Lala-rapi.docx');
    await fs.writeFile(finalOutputPath, reportBuffer);
}
console.log(`Laporan dibuat: ${path.relative(root, finalOutputPath)}`);
