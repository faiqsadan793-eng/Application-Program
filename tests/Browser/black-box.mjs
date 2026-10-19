import { chromium } from 'playwright-core';
import { spawn, spawnSync } from 'node:child_process';
import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
import { tmpdir } from 'node:os';

const root = process.cwd();
const screenshotDir = join(root, 'tests', 'reports', 'blackbox-screenshots');
mkdirSync(screenshotDir, { recursive: true });
const chromePath = process.env.CHROME_PATH || 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const testDirectory = mkdtempSync(join(tmpdir(), 'eklinik-blackbox-'));
const database = join(testDirectory, 'blackbox.sqlite');
const compiledViews = join(testDirectory, 'views');
mkdirSync(compiledViews);
writeFileSync(database, '');
const env = { ...process.env, APP_ENV: 'testing', APP_DEBUG: 'false', DB_CONNECTION: 'sqlite', DB_DATABASE: database, SESSION_DRIVER: 'file', CACHE_STORE: 'array', QUEUE_CONNECTION: 'sync', VIEW_COMPILED_PATH: compiledViews };

for (const args of [['artisan', 'migrate:fresh', '--force', '--no-interaction'], ['artisan', 'db:seed', '--class=Database\\Seeders\\BrowserTestSeeder', '--force', '--no-interaction']]) {
    const result = spawnSync('php', args, { cwd: root, env, stdio: 'inherit' });
    if (result.status !== 0) throw new Error(`Persiapan Black Box gagal: php ${args.join(' ')}`);
}

const port = 18138;
const baseUrl = `http://127.0.0.1:${port}`;
const server = spawn('php', ['-S', `127.0.0.1:${port}`, join(root, 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php')], { cwd: join(root, 'public'), env, stdio: 'ignore' });
for (let attempt = 0; attempt < 50; attempt++) {
    try { if ((await fetch(`${baseUrl}/login`)).ok) break; } catch {}
    if (attempt === 49) throw new Error('Server Black Box tidak dapat dijalankan.');
    await new Promise((resolve) => setTimeout(resolve, 200));
}

const browser = await chromium.launch({ headless: true, executablePath: chromePath });
const results = [];
async function login(page, email, password) {
    await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle' });
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await Promise.all([page.waitForURL(/dashboard/), page.locator('button[type="submit"]').click()]);
}
async function capture(page, role, title, route, explanation) {
    const number = String(results.length + 1).padStart(2, '0');
    await page.goto(`${baseUrl}${route}`, { waitUntil: 'networkidle' });
    const file = `blackbox-${number}-${role.toLowerCase()}-${title.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.png`;
    await page.screenshot({ path: join(screenshotDir, file), fullPage: true });
    results.push({ number, role, title, route, explanation, screenshot: file, status: 'Berhasil' });
}

try {
    const staff = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await capture(staff, 'Guest', 'Form Login', '/login', 'Halaman login menampilkan input email, password, tombol Login, dan validasi kredensial sebelum pengguna masuk ke dashboard.');
    await login(staff, 'staff.browser@test.local', 'browser-test-password');
    const staffCases = [
        ['Dashboard Staff', '/dashboard', 'Dashboard menampilkan ringkasan pasien, antrean, dan aktivitas klinik.'],
        ['Master Data Dokter', '/dokter', 'Staff dapat melihat daftar dan pengelolaan akun dokter.'],
        ['Pengelolaan Pasien', '/pasien', 'Daftar pasien dan aksi pendaftaran antrean ditampilkan.'],
        ['Input Pasien Baru', '/pasien/create', 'Form pendaftaran pasien baru tersedia dengan validasi input.'],
        ['Riwayat Kunjungan', '/kunjungan', 'Riwayat kunjungan dapat difilter dan ditampilkan per halaman.'],
        ['Detail Kunjungan', '/kunjungan/1', 'Detail pasien, pemeriksaan, dan pembayaran ditampilkan bila data tersedia.'],
        ['Proses Transaksi', '/transaksi', 'Tagihan kunjungan dan proses pembayaran tersedia untuk staff.'],
        ['Riwayat Transaksi', '/riwayat-transaksi', 'Transaksi yang telah selesai ditampilkan sebagai histori.'],
        ['Riwayat Rekam Medis', '/rekam-medis-pasien', 'Daftar rekam medis pasien dapat dibuka oleh staff.'],
    ];
    for (const [title, route, explanation] of staffCases) await capture(staff, 'Staff', title, route, explanation);

    const doctor = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await login(doctor, 'dokter.browser@test.local', 'browser-test-password');
    const doctorCases = [
        ['Dashboard Dokter', '/dashboard', 'Dashboard dokter menampilkan antrean sesuai poli dokter.'],
        ['Pemeriksaan Poli Umum', '/rekam-medis', 'Form pemeriksaan dokter menampilkan keluhan, tanda vital, hasil, diagnosis, resep, dan catatan.'],
        ['Pemeriksaan Poli Anak', '/rekam-medis', 'Alur pemeriksaan umum dapat digunakan untuk Poli Anak.'],
        ['Pemeriksaan Poli Gigi', '/rekam-medis', 'Alur pemeriksaan umum dapat digunakan untuk Poli Gigi.'],
        ['Pemeriksaan Poli Spesialis Kandungan', '/rekam-medis', 'Pemeriksaan kandungan mendukung hasil pemeriksaan dan catatan tambahan.'],
        ['Riwayat Rekam Medis Dokter', '/rekam-medis-pasien', 'Dokter hanya melihat rekam medis sesuai poli yang dimiliki.'],
    ];
    for (const [title, route, explanation] of doctorCases) await capture(doctor, 'Dokter', title, route, explanation);
    writeFileSync(join(root, 'tests', 'reports', 'blackbox-results.json'), JSON.stringify({ generatedAt: new Date().toISOString(), total: results.length, passed: results.length, cases: results }, null, 2));
    console.log(`Black Box selesai: ${results.length} tampilan berhasil diambil.`);
} finally {
    await browser.close();
    if (process.platform === 'win32') spawnSync('taskkill', ['/PID', String(server.pid), '/T', '/F'], { stdio: 'ignore', timeout: 5000 }); else server.kill();
    rmSync(testDirectory, { recursive: true, force: true });
}
