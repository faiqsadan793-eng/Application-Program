<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksis';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_kunjungan',
        'biaya_tindakan',
        'biaya_obat',
        'total_biaya',
        'uang_dibayar',
        'kembalian',
        'metode_pembayaran',
        'status_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'biaya_tindakan' => 'decimal:2',
            'biaya_obat' => 'decimal:2',
            'total_biaya' => 'decimal:2',
            'uang_dibayar' => 'decimal:2',
            'kembalian' => 'decimal:2',
        ];
    }

    const STATUS_BELUM = 'belum';
    const STATUS_LUNAS = 'lunas';

    const METODE_CASH = 'cash';
    const METODE_QR = 'qr';
    const METODE_DEBIT = 'debit';

    public static function labelMetodePembayaran(?string $metode): string
    {
        return match ($metode) {
            self::METODE_QR => 'QRIS',
            self::METODE_DEBIT => 'Debit',
            default => 'Cash',
        };
    }

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan', 'id_kunjungan');
    }
}
