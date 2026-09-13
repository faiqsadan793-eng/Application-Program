<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><title>Rekam Medis Pasien</title>
<style>
@page { margin: 38px 42px 48px; }
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #243746; line-height: 1.6; }
h1 { font-size: 20px; color: #047857; margin: 0; }
h3 { font-size: 11px; padding: 9px; background: #ecfdf5; border-left: 3px solid #047857; page-break-after: avoid; }
.muted { color: #64748b; } .identity { border-top: 2px solid #047857; border-bottom: 1px solid #cbd5e1; padding: 12px 0; }
.label { font-weight: bold; margin: 12px 0 3px; page-break-after: avoid; }
.note { white-space: pre-wrap; overflow-wrap: anywhere; word-wrap: break-word; margin: 0; }
.footer { position: fixed; bottom: -28px; font-size: 8px; color: #64748b; }
</style></head><body>
<h1>Klinik Lala Medicare</h1><p class="muted">REKAM MEDIS PASIEN · {{ $poli ?: 'Seluruh poli' }}</p>
<div class="identity"><strong>{{ $pasien->nama }} — {{ $pasien->no_rm }}</strong><br>
Tanggal lahir: {{ $pasien->tanggal_lahir?->translatedFormat('d F Y') ?? '-' }} · Jenis kelamin: {{ $pasien->jenis_kelamin ?? '-' }}<br>
NIK: {{ $pasien->nik ?? '-' }} · Telepon: {{ $pasien->no_hp ?? '-' }}</div>
<p class="muted">Dicetak: {{ now()->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') }} WIB<br>Bagian {{ $part }} dari {{ $parts }} · {{ $kunjungans->count() }} dari total {{ $total }} pemeriksaan · Urutan terbaru terlebih dahulu</p>
@foreach($kunjungans as $kunjungan)
    @php $rm = $kunjungan->rekamMedis; @endphp
    <h3>{{ $loop->iteration }}. {{ $kunjungan->tgl_kunjungan->translatedFormat('d F Y') }} · {{ $kunjungan->poli_tujuan }}<br>Dokter: {{ $rm->nama_dokter ?? $rm->dokter?->user?->name ?? '-' }}</h3>
    @foreach(['Keluhan' => $rm->keluhan, 'Diagnosis' => $rm->diagnosa, 'Resep obat' => $rm->resep_obat] as $label => $value)
        <p class="label">{{ $label }}</p><p class="note">{{ $value ?: '-' }}</p>
    @endforeach
@endforeach
<div class="footer">Klinik Lala Medicare · {{ $pasien->no_rm }} · Dokumen rekam medis pasien</div>
</body></html>
