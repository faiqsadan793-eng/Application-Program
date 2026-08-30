<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    use HasFactory;

    protected $table = 'pasiens';
    protected $primaryKey = 'id_pasien';

    protected $fillable = [
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'no_hp',
        'nik',
        'pekerjaan',
        'alamat',
    ];

    /**
     * Nomor rekam medis adalah identitas permanen pasien, bukan nomor kunjungan.
     */
    public static function nomorRekamMedisUntuk(int $idPasien): string
    {
        return 'RM-' . str_pad((string) $idPasien, 6, '0', STR_PAD_LEFT);
    }

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
        ];
    }

    /**
     * Satu pasien bisa memiliki banyak riwayat kunjungan.
     */
    public function kunjungans()
    {
        return $this->hasMany(Kunjungan::class, 'id_pasien', 'id_pasien');
    }
}
