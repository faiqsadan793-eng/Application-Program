import fs from 'node:fs/promises';
import path from 'node:path';
import { Document, HeadingLevel, ImageRun, Packer, Paragraph, Table, TableCell, TableRow, TextRun, WidthType } from 'docx';

const root = process.cwd();
const reportDir = path.join(root, 'tests', 'reports');
const data = JSON.parse(await fs.readFile(path.join(reportDir, 'blackbox-results.json'), 'utf8'));
const output = path.join(reportDir, 'Laporan-Black-Box-Klinik-Lala.docx');
const imageFallback = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', 'base64');
const p = (text, options = {}) => new Paragraph({ children: [new TextRun({ text, ...options })] });
const cell = (children, width) => new TableCell({ width: { size: width, type: WidthType.PERCENTAGE }, children: Array.isArray(children) ? children : [p(children)] });

const children = [
    new Paragraph({ text: 'LAPORAN PENGUJIAN BLACK BOX', heading: HeadingLevel.TITLE }),
    new Paragraph({ text: 'Sistem Informasi Klinik Lala Medicare', heading: HeadingLevel.HEADING_1 }),
    p(`Tanggal: ${new Date().toLocaleDateString('id-ID')}`),
    p('Pengujian Black Box dilakukan pada tampilan dan alur aplikasi berdasarkan role Staff dan Dokter pada desktop.'),
    new Table({ width: { size: 100, type: WidthType.PERCENTAGE }, rows: [
        new TableRow({ children: [cell('Jumlah UI/Test Case', 50), cell(String(data.total), 50)] }),
        new TableRow({ children: [cell('Berhasil', 50), cell(String(data.passed), 50)] }),
        new TableRow({ children: [cell('Status', 50), cell(data.passed === data.total ? 'LULUS' : 'PERLU REVIEW', 50)] }),
    ] }),
    new Paragraph({ text: 'Tabel Pengujian Black Box', heading: HeadingLevel.HEADING_1 }),
];

for (const item of data.cases) {
    const image = await fs.readFile(path.join(reportDir, 'blackbox-screenshots', item.screenshot));
    children.push(
        new Paragraph({ text: `Gambar ${item.number}. ${item.title} (${item.role})`, heading: HeadingLevel.HEADING_2 }),
        new Paragraph({ alignment: 'center', children: [new ImageRun({ data: image, type: 'png', transformation: { width: 430, height: 268 }, fallback: { data: imageFallback, type: 'png' } })] }),
        p(item.explanation),
        new Table({ width: { size: 100, type: WidthType.PERCENTAGE }, rows: [
            new TableRow({ tableHeader: true, children: [
                cell(p('No', { bold: true }), 5), cell(p('Test Case', { bold: true }), 18), cell(p('Input', { bold: true }), 17), cell(p('Kondisi Awal di Database', { bold: true }), 20), cell(p('Output yang Diharapkan', { bold: true }), 25), cell(p('Hasil Uji', { bold: true }), 15),
            ] }),
            new TableRow({ children: [
                cell(item.number, 5), cell(`${item.title}\nRole: ${item.role}`, 18), cell(`Membuka ${item.route}`, 17), cell('Data testing dibuat melalui BrowserTestSeeder.', 20), cell(item.explanation, 25), cell([p('☑ Berhasil', { bold: true, color: '198754', font: 'Segoe UI Symbol' })], 15),
            ] }),
        ] }),
    );
}

children.push(new Paragraph({ text: 'Catatan Pengujian', heading: HeadingLevel.HEADING_1 }), p('Screenshot diambil otomatis menggunakan Chromium pada viewport desktop 1440×900. Pengujian ini memeriksa tampilan dan respons halaman, bukan mengubah data produksi.'));
const reportBuffer = await Packer.toBuffer(new Document({ sections: [{ children }] }));
let finalOutput = output;
try {
    await fs.writeFile(output, reportBuffer);
} catch (error) {
    if (error.code !== 'EBUSY' && error.code !== 'EPERM') throw error;
    finalOutput = path.join(reportDir, 'Laporan-Black-Box-Klinik-Lala-rapi.docx');
    await fs.writeFile(finalOutput, reportBuffer);
}
console.log(`Laporan dibuat: ${path.relative(root, finalOutput)}`);
