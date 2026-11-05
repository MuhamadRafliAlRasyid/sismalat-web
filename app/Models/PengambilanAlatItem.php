<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class PengambilanAlatItem extends Model
{
    use HasFactory, HasHashId;

    protected $table = 'pengambilan_alat_items';

    protected $fillable = [
        'pengambilan_alat_id',
        'alat_id',
        'jumlah',
        'satuan',
        'status',
        'waktu_pengembalian',
        'foto_pengembalian',
        'catatan_pengembalian',
    ];

    protected $casts = [
        'jumlah'             => 'integer',
        'waktu_pengembalian' => 'datetime',
    ];

    protected $appends = ['hashid'];

    /* ================= RELASI ================= */

    public function pengambilan()
    {
        return $this->belongsTo(PengambilanAlat::class, 'pengambilan_alat_id');
    }

   public function alat(): BelongsTo
{
    return $this->belongsTo(Alat::class, 'alat_id');
}

    /* ================= SCOPES ================= */

    public function scopeDipinjam(Builder $q): Builder
    {
        return $q->where('status', 'dipinjam');
    }

    public function scopeDikembalikan(Builder $q): Builder
    {
        return $q->where('status', 'dikembalikan');
    }

    /* ================= ACCESSOR ================= */

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'dikembalikan' ? 'Dikembalikan' : 'Dipinjam';
    }

    public function isDikembalikan(): bool
    {
        return $this->status === 'dikembalikan';
    }

    public function isDipinjam(): bool
    {
        return $this->status === 'dipinjam';
    }

    /**
     * Hitung hari keterlambatan (0 jika sudah kembali / tidak terlambat)
     */
    public function getHariTerlambatAttribute(): int
    {
        if ($this->isDikembalikan() || !$this->pengambilan) {
            return 0;
        }

        $jatuhTempo = $this->pengambilan->tanggal_jatuh_tempo;
        if (!$jatuhTempo) return 0;

        return max(0, (int) now()->startOfDay()->diffInDays($jatuhTempo->startOfDay(), false) * -1);
    }

    /* ================= METHOD HELPER ================= */

    /**
     * Tandai item sudah dikembalikan + sinkronkan status header
     */
    public function kembalikan(?string $catatan = null, ?string $foto = null): bool
    {
        $ok = $this->update([
            'status'               => 'dikembalikan',
            'waktu_pengembalian'   => now(),
            'catatan_pengembalian' => $catatan,
            'foto_pengembalian'    => $foto,
        ]);

        // ⭐ Perbaikan: status batch otomatis recalculated
        if ($ok && $this->pengambilan) {
            $this->pengambilan->recalculateStatus();
        }

        return $ok;
    }

    public function getRouteKeyName(): string
    {
        return 'hashid';
    }
}
