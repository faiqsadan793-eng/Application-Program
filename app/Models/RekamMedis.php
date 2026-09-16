<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RekamMedis extends Model
{
    use HasFactory;

    protected $table = 'rekam_medises';
    protected $primaryKey = 'id_rm';

    protected $fillable = [
        'id_kunjungan',
        'id_dokter',
        'nama_dokter',
        'keluhan',
        'suhu_tubuh',
        'tinggi_badan',
        'berat_badan',
        'tekanan_darah',
        'hasil_pemeriksaan',
        'diagnosa',
        'resep_obat',
        'catatan_tambahan',
    ];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id_kunjungan');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'id_dokter', 'id_dokter');
    }
}
