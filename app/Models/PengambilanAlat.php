<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengambilanAlat extends Model
{
    use HasFactory, HasHashId, Notifiable;

    protected $table = 'pengambilan_alats';

    protected $appends = ['hashid'];

    protected $fillable = [
    'user_id',
    'bagian_id',
    'nama_peminjam',
    'tipe_peminjam',
    'email_peminjam',
    'telepon_peminjam',
    'instansi_peminjam',
    'kanal_notifikasi',

    // Referensi alat utama untuk kompatibilitas lama
    'alat_id',
    'jumlah',
    'satuan',

    'keperluan',
    'waktu_pengambilan',
    'lama_pinjam',
    'status',
    'foto',
    'last_warned_at',
    'warning_15_sent_at',
    'warning_1day_sent_at',
    'is_critical',
    'tracking_token_hash',
];

    protected $casts = [
        'waktu_pengambilan'    => 'datetime',
        'lama_pinjam'          => 'integer',
        'last_warned_at'       => 'datetime',
        'warning_15_sent_at'   => 'datetime',
        'warning_1day_sent_at' => 'datetime',
        'is_critical'          => 'boolean',
    ];

    /* ================= RELASI ================= */

    public function items()
    {
        return $this->hasMany(PengambilanAlatItem::class, 'pengambilan_alat_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alat()
    {
        return $this->belongsTo(Alat::class);
    }

public function pengembalian(): HasMany
{
    return $this->hasMany(
        PengembalianAlat::class,
        'pengambilan_alat_id',
        'id'
    );
}

    public function bagian()
    {
        return $this->belongsTo(Bagian::class);
    }

    /**
     * Relasi many-to-many ke alat melalui items
     */
    public function alats()
    {
        return $this->belongsToMany(Alat::class, 'pengambilan_alat_items', 'pengambilan_alat_id', 'alat_id')
            ->withPivot('jumlah', 'satuan', 'status')
            ->withTimestamps();
    }

    /* ================= SCOPES ================= */

    public function scopeAktif(Builder $q): Builder
    {
        return $q->whereIn('status', ['dipinjam', 'sebagian_dikembalikan']);
    }

    public function scopeSelesai(Builder $q): Builder
    {
        return $q->where('status', 'dikembalikan');
    }

    public function scopeTerlambat(Builder $q): Builder
    {
        return $q->aktif()
            ->whereNotNull('waktu_pengambilan')
            ->whereNotNull('lama_pinjam')
            ->whereRaw('DATE_ADD(waktu_pengambilan, INTERVAL lama_pinjam DAY) < ?', [now()]);
    }

    public function scopeMendekatiJatuhTempo(Builder $q, int $hari = 7): Builder
    {
        return $q->aktif()
            ->whereNotNull('waktu_pengambilan')
            ->whereNotNull('lama_pinjam')
            ->whereRaw('DATE_ADD(waktu_pengambilan, INTERVAL lama_pinjam DAY) BETWEEN ? AND ?', [
                now()->startOfDay(),
                now()->addDays($hari)->endOfDay(),
            ]);
    }

    /* ================= ACCESSOR ================= */

    public function getTanggalJatuhTempoAttribute(): ?Carbon
    {
        if (!$this->waktu_pengambilan || !$this->lama_pinjam) return null;
        return $this->waktu_pengambilan->copy()->addDays((int) $this->lama_pinjam);
    }

    public function getSisaHariAttribute(): ?int
    {
        $jt = $this->tanggal_jatuh_tempo;
        if (!$jt) return null;
        if ($this->status === 'dikembalikan') return 0;
        return (int) now()->startOfDay()->diffInDays($jt->startOfDay(), false);
    }

    public function getSisaHariLabelAttribute(): string
    {
        $sisa = $this->sisa_hari;
        if ($sisa === null) return '-';
        if ($this->status === 'dikembalikan') return 'Selesai';

        return match (true) {
            $sisa > 1   => "{$sisa} hari lagi",
            $sisa === 1 => 'Besok',
            $sisa === 0 => 'Hari ini',
            default     => 'Terlambat ' . abs($sisa) . ' hari',
        };
    }

    public function getPercentaseSisaAttribute(): float
    {
        if (!$this->lama_pinjam || $this->lama_pinjam <= 0) return 0;
        $sisa = $this->sisa_hari;
        if ($sisa === null || $sisa < 0) return 0;
        return round(($sisa / $this->lama_pinjam) * 100, 2);
    }

    public function getTotalItemAttribute(): int
    {
        return $this->items()->count();
    }

    public function getTotalDikembalikanAttribute(): int
    {
        return $this->items()->dikembalikan()->count();
    }

    public function getRingkasanAlatAttribute(): string
    {
        return $this->items
            ->map(fn ($item) => $item->alat?->nama_alat . " ({$item->jumlah})")
            ->filter()
            ->implode(', ');
    }

    public function getStatusPinjamAttribute(): string
    {
        if ($this->status === 'dikembalikan') return 'selesai';
        $sisa = $this->sisa_hari;
        if ($sisa === null) return 'unknown';

        return match (true) {
            $sisa < 0  => 'terlambat',
            $sisa <= 3 => 'warning',
            default    => 'aman',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dipinjam'              => 'Dipinjam',
            'sebagian_dikembalikan' => 'Sebagian Dikembalikan',
            'dikembalikan'          => 'Selesai',
            default                 => ucfirst($this->status ?? 'Unknown'),
        };
    }

    /* ================= WARNING ACCESSORS ================= */

    public function getShouldWarn15Attribute(): bool
    {
        if (!$this->isAktif() || !$this->lama_pinjam) return false;
        $sisa = $this->sisa_hari;
        if ($sisa === null || $sisa < 0) return false;
        if ($sisa > $this->lama_pinjam * 0.15) return false;
        if (!$this->warning_15_sent_at) return true;
        return $this->warning_15_sent_at->diffInDays(now()) >= 3;
    }

    public function getShouldWarn1DayAttribute(): bool
    {
        if (!$this->isAktif() || !$this->lama_pinjam) return false;
        $sisa = $this->sisa_hari;
        if ($sisa === null || $sisa < 0) return false;
        if ($sisa > 1) return false;
        if (!$this->warning_1day_sent_at) return true;
        return !$this->warning_1day_sent_at->isToday();
    }

    public function getShouldWarnAttribute(): bool
    {
        return $this->should_warn_15 || $this->should_warn_1day;
    }

    /* ================= HELPER METHODS ================= */

    public function isAktif(): bool
    {
        return in_array($this->status, ['dipinjam', 'sebagian_dikembalikan']);
    }

    public function isTerlambat(): bool
    {
        return $this->status_pinjam === 'terlambat';
    }

    public function isSelesai(): bool
    {
        return $this->status === 'dikembalikan';
    }

    /**
     * Recalculate status header berdasarkan status semua item
     */
    public function recalculateStatus(): void
    {
        $total = $this->items()->count();
        $dikembalikan = $this->items()->dikembalikan()->count();

        if ($total === 0) {
            $newStatus = 'dipinjam';
        } elseif ($dikembalikan === $total) {
            $newStatus = 'dikembalikan';
        } elseif ($dikembalikan > 0) {
            $newStatus = 'sebagian_dikembalikan';
        } else {
            $newStatus = 'dipinjam';
        }

        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }
    }

    public function markWarning15Sent(): bool
    {
        return $this->update([
            'warning_15_sent_at' => now(),
            'last_warned_at'     => now(),
        ]);
    }

    public function markWarning1DaySent(): bool
    {
        return $this->update([
            'warning_1day_sent_at' => now(),
            'last_warned_at'       => now(),
            'is_critical'          => true,
        ]);
    }

    public function resetWarnings(): void
    {
        $this->update([
            'warning_15_sent_at'   => null,
            'warning_1day_sent_at' => null,
            'last_warned_at'       => null,
            'is_critical'          => false,
        ]);
    }

    public function getFotoThumbAttribute(): ?string
    {
        if (!$this->foto) return null;
        return asset('storage/pengambilan/thumb_' . $this->foto);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) return null;
        return asset('storage/pengambilan/' . $this->foto);
    }

    public function getRouteKeyName(): string
    {
        return 'hashid';
    }
}
