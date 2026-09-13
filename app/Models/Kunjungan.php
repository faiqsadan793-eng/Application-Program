<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungans';
    protected $primaryKey = 'id_kunjungan';

    protected $fillable = [
        'id_pasien',
        'tgl_kunjungan',
        'masuk_antrean_pada',
        'status',
        'poli_tujuan',
        'kategori_pembatalan',
        'alasan_pembatalan',
        'dibatalkan_pada',
        'dibatalkan_oleh',
        'nama_pembatal',
    ];

    protected function casts(): array
    {
        return [
            'tgl_kunjungan' => 'date',
            'active_tgl_kunjungan' => 'date',
            'masuk_antrean_pada' => 'datetime',
            'dibatalkan_pada' => 'datetime',
        ];
    }

    // Status constants agar tidak ada typo di seluruh codebase
    const STATUS_ANTRE           = 'antre';
    const STATUS_MENUNGGU_DOKTER = 'menunggu_dokter';
    const STATUS_SIAP_BAYAR      = 'siap_bayar';
    const STATUS_SELESAI         = 'selesai';
    const STATUS_DIBATALKAN      = 'dibatalkan';

    const KATEGORI_PEMBATALAN = [
        'tidak_hadir' => 'Pasien tidak hadir',
        'dibatalkan_pasien' => 'Dibatalkan oleh pasien',
        'salah_poli' => 'Salah memilih poli',
        'pulang_sebelum_diperiksa' => 'Pulang sebelum diperiksa',
        'rujukan' => 'Dirujuk ke fasilitas lain',
        'lainnya' => 'Lainnya',
    ];

    // Daftar poli valid — single source of truth
    const POLI_LIST = [
        'Poli Umum',
        'Poli Gigi',
        'Poli Anak',
    ];

    /** Status yang masih menghalangi pasien membuat kunjungan baru di hari yang sama. */
    const STATUS_AKTIF = [
        self::STATUS_ANTRE,
        self::STATUS_MENUNGGU_DOKTER,
        self::STATUS_SIAP_BAYAR,
    ];

    protected static function booted(): void
    {
        static::creating(function (self $kunjungan): void {
            $kunjungan->syncTanggalKunjunganAktif();
            $kunjungan->masuk_antrean_pada ??= now();
        });

        static::updating(function (self $kunjungan): void {
            if ($kunjungan->isDirty(['status', 'tgl_kunjungan'])) {
                $kunjungan->syncTanggalKunjunganAktif();
            }
        });
    }

    public static function statusAktif(string $status): bool
    {
        return in_array($status, self::STATUS_AKTIF, true);
    }

    public static function tanggalHariIni(): string
    {
        return now()->setTimezone('Asia/Jakarta')->toDateString();
    }

    public function scopePadaHariIni($query)
    {
        $mulai = now()->setTimezone('Asia/Jakarta')->startOfDay();

        return $query
            ->where('tgl_kunjungan', '>=', $mulai->toDateString())
            ->where('tgl_kunjungan', '<', $mulai->copy()->addDay()->toDateString());
    }

    public function kategoriPembatalanLabel(): string
    {
        return self::KATEGORI_PEMBATALAN[$this->kategori_pembatalan] ?? 'Belum dikategorikan';
    }

    private function syncTanggalKunjunganAktif(): void
    {
        $this->active_tgl_kunjungan = self::statusAktif($this->status)
            ? $this->tgl_kunjungan
            : null;
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'id_pasien', 'id_pasien');
    }

    public function rekamMedis()
    {
        return $this->hasOne(RekamMedis::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function transaksi()
    {
        return $this->hasOne(Transaksi::class, 'id_kunjungan', 'id_kunjungan');
    }
}
