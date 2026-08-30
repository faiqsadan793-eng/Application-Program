<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'nip_sip', 'poli'])]
class Dokter extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_dokter';

    /**
     * Akun yang dipakai dokter untuk login.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
