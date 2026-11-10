<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class KalibrasiAlat extends Model
{
    use HasHashId;

    protected $table = 'kalibrasi_alats';

    protected $appends = [
        'hashid',
        'bukti_sertifikat_url',
        'bukti_sertifikat_is_pdf',
    ];

    protected $fillable = [
        'alat_id',
        'tanggal_kalibrasi',
        'masa_berlaku_baru',
        'no_sertifikat',
        'keterangan',
        'bukti_sertifikat',
    ];

    protected $casts = [
        'tanggal_kalibrasi' => 'date',
        'masa_berlaku_baru' => 'date',
    ];

    /** Relasi ke alat yang dikalibrasi. */
   public function alat()
{
    return $this->belongsTo(Alat::class, 'alat_id', 'id');
}

    /** URL publik file sertifikat pada disk public. */
    public function getBuktiSertifikatUrlAttribute(): ?string
    {
        if (!$this->bukti_sertifikat) {
            return null;
        }

        return route('kalibrasis.sertifikat', $this->hashid);
    }

    /** True jika bukti sertifikat berupa PDF. */
    public function getBuktiSertifikatIsPdfAttribute(): bool
    {
        return $this->bukti_sertifikat
            && strtolower(pathinfo($this->bukti_sertifikat, PATHINFO_EXTENSION)) === 'pdf';
    }

    /** Nama file yang ditampilkan pada antarmuka. */
    public function getBuktiSertifikatNameAttribute(): ?string
    {
        return $this->bukti_sertifikat
            ? basename($this->bukti_sertifikat)
            : null;
    }

    /** Scope pencarian riwayat berdasarkan alat. */
    public function scopeUntukAlat(Builder $query, int $alatId): Builder
    {
        return $query->where('alat_id', $alatId);
    }

    /** Scope untuk urutan riwayat kalibrasi terbaru. */
    public function scopeTerbaru(Builder $query): Builder
    {
        return $query
            ->orderByDesc('tanggal_kalibrasi')
            ->orderByDesc('id');
    }
}
