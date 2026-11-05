<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PengambilanAlatItem;

class Alat extends Model
{
    use HasFactory, SoftDeletes, HasHashId;

    protected $fillable = [
        'nama_alat',
        'kelas',
        'merk',
        'tipe',
        'no_seri',
        'no_identitas',
        'kapasitas',
        'daya_baca',
        'jumlah',
        'no_sertifikat',
        'masa_berlaku',
        'kategori_id',
        'qr_code',
        'foto',
        'last_notified_at',
        // === TAMBAHAN KOLOM KIR ===
        'ruangan_id',
        'ukuran',
        'satuan',
        'jumlah_baik',
        'jumlah_kurang_baik',
        'jumlah_rusak_berat',
        'tahun_pengadaan',
    ];

    protected $appends = ['hashid', 'status', 'status_hari', 'status_pinjam', 'kondisi_summary'];

    protected $casts = [
        'masa_berlaku' => 'date',
        'tahun_pengadaan' => 'integer',
    ];

    // ================= RELASI =================
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

public function pengambilanItems(): HasMany
{
    return $this->hasMany(
        PengambilanAlatItem::class,
        'alat_id',
        'id'
    );
}

    public function pengambilan()
    {
        return $this->hasMany(PengambilanAlat::class);
    }

    public function pengembalian()
    {
        return $this->hasManyThrough(
            PengembalianAlat::class,
            PengambilanAlat::class,
            'alat_id',
            'pengambilan_alat_id',
            'id',
            'id'
        );
    }

    public function kalibrasis()
    {
        return $this->hasMany(KalibrasiAlat::class);
    }

    // ================= ACCESSOR =================

    public function getLabelAttribute(): string
    {
        return "{$this->nama_alat} | {$this->merk} | {$this->tipe} | {$this->no_seri}";
    }

    /**
     * Accessor untuk merangkum kondisi barang (B / KB / RB)
     */
    public function getKondisiSummaryAttribute(): string
    {
        $parts = [];
        if ($this->jumlah_baik > 0) $parts[] = "{$this->jumlah_baik} Baik";
        if ($this->jumlah_kurang_baik > 0) $parts[] = "{$this->jumlah_kurang_baik} Kurang Baik";
        if ($this->jumlah_rusak_berat > 0) $parts[] = "{$this->jumlah_rusak_berat} Rusak Berat";

        return implode(', ', $parts) ?: '-';
    }

    public function getStatusAttribute(): string
    {
        if (!$this->masa_berlaku) {
            return 'unknown';
        }

        $today = Carbon::today();
        $expire = Carbon::parse($this->masa_berlaku)->startOfDay();

        if ($expire->isPast()) {
            return 'expired';
        }

        $diffDays = $today->diffInDays($expire, false);
        return $diffDays <= 7 ? 'warning' : 'active';
    }

    public function getStatusHariAttribute(): ?int
    {
        if (!$this->masa_berlaku) {
            return null;
        }

        $today = Carbon::today();
        $expire = Carbon::parse($this->masa_berlaku)->startOfDay();

        return (int) $today->diffInDays($expire, false);
    }

    public function getStatusPinjamAttribute(): ?string
    {
        $pengambilan = $this->pengambilan;

        if ($pengambilan->isEmpty()) {
            return null;
        }

        return $pengambilan->contains('status', 'dipinjam') ? 'dipinjam' : 'dikembalikan';
    }

    public function getFotoThumbAttribute(): ?string
    {
        if (!$this->foto) return null;
        return asset('storage/alat/thumb_' . $this->foto); // Disesuaikan dengan controller upload Anda
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) return null;
        return asset('storage/alat/' . $this->foto);
    }

    // ================= SCOPE =================
    public function scopeSearch($query, ?string $keyword)
{
    $keyword = trim($keyword ?? '');
    if ($keyword === '') {
        return $query;
    }

    // Daftar kolom yang bisa dicari (urutan prioritas)
    $searchableColumns = [
        'nama_alat',
        'merk',
        'tipe',
        'no_seri',
        'no_identitas',
        'kelas',
        'kapasitas',
        'ukuran',
        'daya_baca',
        'nomor_identitas',  // fallback alias
        'kode_alat',        // fallback alias
    ];

    // Filter kolom yang benar-benar ada di tabel
    $availableColumns = array_filter(
        $searchableColumns,
        fn ($col) => Schema::hasColumn('alats', $col)
    );

    if (empty($availableColumns)) {
        return $query;
    }

    $likeKeyword = "%{$keyword}%";

    return $query->where(function ($q) use ($availableColumns, $likeKeyword) {
        foreach ($availableColumns as $index => $column) {
            $method = $index === 0 ? 'where' : 'orWhere';
            // IFNULL agar pencarian tetap work walau kolom berisi NULL
            $q->{$method}(DB::raw("IFNULL({$column}, '')"), 'LIKE', $likeKeyword);
        }
    });
}
}
