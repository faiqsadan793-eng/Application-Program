import { chromium } from 'playwright-core';
import {spawn, spawnSync} from 'node:child_process';
import {mkdirSync, mkdtempSync, writeFileSync, rmSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {join} from 'node:path';

let baseUrl = process.env.BROWSER_BASE_URL;
const chromePath = process.env.CHROME_PATH || (process.platform === 'win32'
    ? 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe'
    : '/usr/bin/google-chrome');
let testDirectory = null;
let server = null;
let doctorEmail = process.env.BROWSER_DOCTOR_EMAIL;
let doctorPassword = process.env.BROWSER_DOCTOR_PASSWORD;
let staffEmail = process.env.BROWSER_STAFF_EMAIL;
let staffPassword = process.env.BROWSER_STAFF_PASSWORD;
const projectDirectory = process.cwd();

if (!baseUrl) {
    testDirectory = mkdtempSync(join(tmpdir(), 'eklinik-browser-'));
    const database = join(testDirectory, 'browser.sqlite');
    const compiledViews = join(testDirectory, 'views');
    writeFileSync(database, '');
    mkdirSync(compiledViews);
    const testEnv = {
        ...process.env,
        APP_ENV: 'testing', APP_DEBUG: 'false', DB_CONNECTION: 'sqlite', DB_DATABASE: database,
        SESSION_DRIVER: 'file', CACHE_STORE: 'array', QUEUE_CONNECTION: 'sync',
        VIEW_COMPILED_PATH: compiledViews,
    };
    for (const args of [
        ['artisan', 'migrate:fresh', '--force', '--no-interaction'],
        ['artisan', 'db:seed', '--class=Database\\Seeders\\BrowserTestSeeder', '--force', '--no-interaction'],
    ]) {
        const result = spawnSync('php', args, {env: testEnv, stdio: 'inherit'});
        if (result.status !== 0) throw new Error(`Persiapan browser test gagal: php ${args.join(' ')}`);
    }
    const port = 18137;
    baseUrl = `http://127.0.0.1:${port}`;
    server = spawn('php', [
        '-S', `127.0.0.1:${port}`,
        join(projectDirectory, 'vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'),
    ], {cwd: join(projectDirectory, 'public'), env: testEnv, stdio: 'ignore'});
    for (let attempt = 0; attempt < 50; attempt++) {
        try {
            const response = await fetch(`${baseUrl}/login`);
            if (response.ok && (await response.text()).includes('name="email"')) break;
        } catch {}
        if (attempt === 49) throw new Error('Server browser test tidak dapat dijalankan.');
        await new Promise((resolve) => setTimeout(resolve, 200));
    }
    doctorEmail = 'dokter.browser@test.local';
    doctorPassword = 'browser-test-password';
    staffEmail = 'staff.browser@test.local';
    staffPassword = 'browser-test-password';
}

async function login(page, email, password) {
    await page.goto(`${baseUrl}/login`);
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await Promise.all([
        page.waitForURL(/\/dashboard$/),
        page.locator('button[type="submit"]').click(),
    ]);
}

function requireCredential(name) {
    const value = process.env[name];
    if (!value) throw new Error(`${name} wajib diisi untuk pengujian browser.`);
    return value;
}

const browser = await chromium.launch({headless: true, executablePath: chromePath});

try {
    const doctorPage = await browser.newPage();
    await login(
        doctorPage,
        doctorEmail || requireCredential('BROWSER_DOCTOR_EMAIL'),
        doctorPassword || requireCredential('BROWSER_DOCTOR_PASSWORD'),
    );
    await doctorPage.goto(`${baseUrl}/rekam-medis`);
    const medicalForm = doctorPage.locator('.medical-record-form').first();
    if (await medicalForm.count() === 0) {
        throw new Error('Siapkan satu pasien berstatus menunggu_dokter pada poli akun dokter.');
    }
    const marker = `Draft browser ${Date.now()}`;
    await medicalForm.locator('[name="keluhan"]').fill(marker);
    await doctorPage.reload();
    if (await doctorPage.locator('.medical-record-form [name="keluhan"]').first().inputValue() !== marker) {
        throw new Error('Draft rekam medis tidak pulih setelah refresh.');
    }
    const medicalGuard = await doctorPage.locator('.medical-record-form').first().evaluate((form) => {
        const first = new Event('submit', {bubbles: true, cancelable: true});
        const second = new Event('submit', {bubbles: true, cancelable: true});
        form.dispatchEvent(first);
        form.dispatchEvent(second);
        return {
            submitting: form.dataset.submitting,
            disabled: form.querySelector('[data-submit-button]').disabled,
            secondPrevented: second.defaultPrevented,
        };
    });
    if (medicalGuard.submitting !== 'true' || !medicalGuard.disabled || !medicalGuard.secondPrevented) {
        throw new Error('Pelindung pengiriman ganda rekam medis tidak bekerja.');
    }

    const staffPage = await browser.newPage();
    await login(
        staffPage,
        staffEmail || requireCredential('BROWSER_STAFF_EMAIL'),
        staffPassword || requireCredential('BROWSER_STAFF_PASSWORD'),
    );
    await staffPage.goto(`${baseUrl}/transaksi`);
    const paymentTrigger = staffPage.locator('[data-transaction-id]').first();
    if (await paymentTrigger.count() === 0) {
        throw new Error('Siapkan satu transaksi berstatus belum dibayar.');
    }
    const transactionId = await paymentTrigger.getAttribute('data-transaction-id');
    await paymentTrigger.click();
    await staffPage.locator('#displayBiayaObat').fill('12345');
    await staffPage.locator('#displayBiayaObat').dispatchEvent('input');
    await staffPage.reload();
    await staffPage.locator(`[data-transaction-id="${transactionId}"]`).click();
    if (await staffPage.locator('#displayBiayaObat').inputValue() !== '12.345') {
        throw new Error('Draft pembayaran tidak pulih setelah refresh.');
    }
    await staffPage.locator('#displayUangDibayar').fill('100000');
    await staffPage.locator('#displayUangDibayar').dispatchEvent('input');
    const paymentGuard = await staffPage.locator('#paymentForm').evaluate((form) => {
        const first = new Event('submit', {bubbles: true, cancelable: true});
        const second = new Event('submit', {bubbles: true, cancelable: true});
        form.dispatchEvent(first);
        form.dispatchEvent(second);
        return {
            submitting: form.dataset.submitting,
            disabled: form.querySelector('#paymentSubmitButton').disabled,
            secondPrevented: second.defaultPrevented,
        };
    });
    if (paymentGuard.submitting !== 'true' || !paymentGuard.disabled || !paymentGuard.secondPrevented) {
        throw new Error('Pelindung pengiriman ganda pembayaran tidak bekerja.');
    }

    console.log('Browser test lulus: draft dan pengiriman ganda rekam medis serta pembayaran.');
} finally {
    await browser.close();
    if (server) {
        if (process.platform === 'win32') {
            spawnSync('taskkill', ['/PID', String(server.pid), '/T', '/F'], {stdio: 'ignore'});
        } else {
            server.kill();
        }
        server.unref();
    }
    if (testDirectory) rmSync(testDirectory, {recursive: true, force: true});
}
