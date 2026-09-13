<?php

namespace Tests\Feature;

use App\Models\{Dokter, Kunjungan, Pasien, RekamMedis, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicalRecordExportTest extends TestCase
{
    use RefreshDatabase;

    private function patient(): Pasien
    {
        return Pasien::create(['nama' => 'Pasien PDF', 'tanggal_lahir' => '1990-01-01', 'jenis_kelamin' => 'Perempuan', 'no_hp' => '08123456789']);
    }

    private function record(Pasien $patient, string $poli, string $diagnosis): void
    {
        $visit = Kunjungan::create(['id_pasien' => $patient->id_pasien, 'tgl_kunjungan' => now()->toDateString(), 'poli_tujuan' => $poli, 'status' => Kunjungan::STATUS_SELESAI]);
        RekamMedis::create(['id_kunjungan' => $visit->id_kunjungan, 'nama_dokter' => 'Dokter Arsip', 'keluhan' => "Keluhan\nBaris kedua <script>alert(1)</script>", 'diagnosa' => $diagnosis, 'resep_obat' => 'Resep pasien']);
    }

    public function test_staff_gets_real_private_pdf_and_detail_is_paginated(): void
    {
        $patient = $this->patient();
        for ($i = 0; $i < 12; $i++) {
            $this->record($patient, 'Poli Umum', 'Diagnosis '.$i);
        }
        $this->actingAs(User::factory()->create(['role' => 'staff']));
        $this->get(route('rekam-medis-pasien.show', $patient))->assertOk()
            ->assertSee('Detail Pasien')->assertSee('Ekspor Seluruh Riwayat PDF')
            ->assertViewHas('kunjungans', fn ($rows) => $rows->count() === 10 && $rows->total() === 12);
        $response = $this->get(route('rekam-medis-pasien.pdf', $patient))->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
    }

    public function test_doctor_export_uses_same_poli_scope_as_detail(): void
    {
        $patient = $this->patient();
        $this->record($patient, 'Poli Gigi', 'Rahasia gigi');
        $doctor = User::factory()->create(['role' => 'dokter']);
        Dokter::create(['user_id' => $doctor->id, 'nip_sip' => 'PDF-SIP', 'poli' => 'Poli Umum']);
        $this->actingAs($doctor)->get(route('rekam-medis-pasien.pdf', $patient))->assertNotFound();
        $this->record($patient, 'Poli Umum', 'Diagnosis umum');
        $this->get(route('rekam-medis-pasien.show', $patient))->assertOk()->assertSee('Diagnosis umum')->assertDontSee('Rahasia gigi');
        // Inspect the actual HTML supplied to the renderer, not only the download header.
        view()->composer('rekam_medis_pasien.pdf', function ($view) {
            $this->assertCount(1, $view->getData()['kunjungans']);
            $this->assertSame('Poli Umum', $view->getData()['kunjungans']->first()->poli_tujuan);
        });
        $this->get(route('rekam-medis-pasien.pdf', $patient))->assertOk();
    }

    public function test_guest_and_doctor_without_profile_cannot_export(): void
    {
        $patient = $this->patient();
        $this->get(route('rekam-medis-pasien.pdf', $patient))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'dokter']))
            ->get(route('rekam-medis-pasien.pdf', $patient))->assertForbidden();
    }

    public function test_index_loads_only_latest_record_for_each_patient(): void
    {
        foreach (range(1, 2) as $i) {
            $patient = $this->patient();
            $this->record($patient, 'Poli Umum', 'Lama');
            $this->record($patient, 'Poli Umum', 'Terbaru');
        }
        $this->actingAs(User::factory()->create(['role' => 'staff']))
            ->get(route('rekam-medis-pasien.index'))->assertOk()->assertViewHas('rekamMedis', function ($rows) {
                return $rows->count() === 2 && $rows->every(fn ($p) => $p->kunjungans->count() === 1 && $p->kunjungans->first()->rekamMedis->diagnosa === 'Terbaru');
            });
    }

    public function test_large_history_can_export_every_part_without_silent_truncation(): void
    {
        $patient = $this->patient();
        foreach (range(1, 51) as $i) {
            $this->record($patient, 'Poli Umum', 'Diagnosis '.$i);
        }
        $this->actingAs(User::factory()->create(['role' => 'staff']));
        $this->get(route('rekam-medis-pasien.pdf', $patient))->assertRedirect(route('rekam-medis-pasien.show', $patient))->assertSessionHas('error');
        view()->composer('rekam_medis_pasien.pdf', function ($view) {
            $data = $view->getData();
            $this->assertSame(51, $data['total']);
            $this->assertSame(2, $data['parts']);
            $this->assertCount($data['part'] === 1 ? 50 : 1, $data['kunjungans']);
            $this->assertSame($data['part'] === 1 ? 'Diagnosis 51' : 'Diagnosis 1', $data['kunjungans']->first()->rekamMedis->diagnosa);
        });
        foreach ([1, 2] as $part) {
            $this->get(route('rekam-medis-pasien.pdf', ['pasien' => $patient, 'bagian' => $part]))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        }
        $this->getJson(route('rekam-medis-pasien.pdf', ['pasien' => $patient, 'bagian' => 3]))->assertUnprocessable();
    }
}
