<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Alat extends Model
{
    use HasFactory, SoftDeletes, HasHashId;

    protected $fillable = [
        'nama_alat', 'kelas', 'merk', 'tipe', 'no_seri', 'no_identitas',
        'kapasitas', 'daya_baca', 'jumlah', 'no_sertifikat', 'masa_berlaku',
        'kategori_id', 'qr_code', 'foto', 'last_notified_at',
        // Kolom KIR
        'ruangan_id', 'ukuran', 'satuan', 'jumlah_baik',
        'jumlah_kurang_baik', 'jumlah_rusak_berat', 'tahun_pengadaan',
    ];

    protected $appends = ['hashid', 'status', 'status_hari', 'status_pinjam', 'kondisi_summary'];

    protected $casts = [
        'masa_berlaku' => 'date',
        'tahun_pengadaan' => 'integer',
    ];

    // ================= RELASI =================
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function pengambilanItems(): HasMany
    {
        return $this->hasMany(PengambilanAlatItem::class, 'alat_id', 'id');
    }

    public function pengambilan(): HasMany
    {
        return $this->hasMany(PengambilanAlat::class);
    }

    public function pengembalian(): HasManyThrough
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

    public function kalibrasis(): HasMany
    {
        return $this->hasMany(KalibrasiAlat::class);
    }

    // ================= ACCESSOR =================

    public function getLabelAttribute(): string
    {
        return "{$this->nama_alat} | {$this->merk} | {$this->tipe} | {$this->no_seri}";
    }

    public function getKondisiSummaryAttribute(): string
    {
        $parts = [];
        if ($this->jumlah_baik > 0) $parts[] = "{$this->jumlah_baik} Baik";
        if ($this->jumlah_kurang_baik > 0) $parts[] = "{$this->jumlah_kurang_baik} Kurang Baik";
        if ($this->jumlah_rusak_berat > 0) $parts[] = "{$this->jumlah_rusak_berat} Rusak Berat";
        return implode(', ', $parts) ?: '-';
    }

    /**
     * Status kalibrasi: expired / warning / active / unknown
     * Menggunakan threshold 30 hari (sesuai view index).
     */
    public function getStatusAttribute(): string
    {
        if (!$this->masa_berlaku) {
            return 'unknown';
        }

        $today = Carbon::today()->startOfDay();
        $expire = Carbon::parse($this->masa_berlaku)->startOfDay();

        if ($expire->lessThan($today)) {
            return 'expired';
        }

        $diffDays = (int) $today->diffInDays($expire, false);
        return $diffDays <= 30 ? 'warning' : 'active';
    }

    public function getStatusHariAttribute(): ?int
    {
        if (!$this->masa_berlaku) {
            return null;
        }

        $today = Carbon::today()->startOfDay();
        $expire = Carbon::parse($this->masa_berlaku)->startOfDay();

        return (int) $today->diffInDays($expire, false);
    }

    /**
     * FIX BUG: Gunakan query EXISTS, bukan contains() pada relasi.
     * contains() hanya bekerja di Collection, bukan di query builder.
     */
    public function getStatusPinjamAttribute(): ?string
    {
        // Gunakan whereHas untuk menghindari lazy-load yang menghasilkan query builder
        $hasDipinjam = $this->pengambilan()
            ->where('status', 'dipinjam')
            ->exists();

        if (!$hasDipinjam) {
            // Cek apakah ada riwayat sama sekali
            $hasAny = $this->pengambilan()->exists();
            return $hasAny ? 'dikembalikan' : null;
        }

        return 'dipinjam';
    }

    public function getFotoThumbAttribute(): ?string
    {
        if (!$this->foto) return null;
        return asset('storage/alat/thumb_' . $this->foto);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) return null;
        return asset('storage/alat/' . $this->foto);
    }

    // ================= SCOPE =================

    /**
     * Pencarian multi-field dengan fallback kolom via Schema check.
     */
        // ================= SCOPE =================

    /**
     * SMART MULTI-FIELD SEARCH
     *
     * - Query dipecah per kata; SEMUA kata harus cocok (AND).
     * - Tiap kata dicocokkan ke kolom yang relevan (OR di dalam kata).
     * - Spasi diabaikan saat pencocokan ("20kg" menemukan "20 kg").
     * - Token angka+satuan hanya dicari di kolom ukuran (presisi).
     * - Operator prefix: kelas: merk: kapasitas: seri: identitas: ruangan:
     */

public function scopeSearch($query, ?string $keyword)
{
    $keyword = trim($keyword ?? '');

    if ($keyword === '') {
        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Daftar Kolom Pencarian
    |--------------------------------------------------------------------------
    */

    $allColumns = [
        'nama_alat',
        'merk',
        'tipe',
        'no_seri',
        'no_identitas',
        'kelas',
        'kapasitas',
        'ukuran',
        'daya_baca',
        'satuan',
    ];

    // Kolom yang digunakan untuk pencarian ukuran/pengukuran.
    $measureColumns = [
        'kapasitas',
        'ukuran',
        'daya_baca',
    ];

    // Kolom identitas atau kode alat.
    $codeColumns = [
        'no_seri',
        'no_identitas',
    ];

    // Hanya gunakan kolom yang benar-benar tersedia.
    $available = array_values(array_filter(
        $allColumns,
        fn ($col) => \Illuminate\Support\Facades\Schema::hasColumn(
            'alats',
            $col
        )
    ));

    if (empty($available)) {
        return $query;
    }

    $measureAvailable = array_values(array_intersect(
        $available,
        $measureColumns
    ));

    $codeAvailable = array_values(array_intersect(
        $available,
        $codeColumns
    ));

    /*
    |--------------------------------------------------------------------------
    | 2. Ekstrak Operator Prefix
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | kelas:m2
    | merk:mettler
    | kapasitas:20
    | seri:12345
    | identitas:ALAT-001
    | ruangan:"lab kimia"
    |
    */

    $operators = [];

    $clean = preg_replace_callback(
        '/\b(kelas|merk|tipe|kapasitas|seri|identitas|ruangan)\s*:\s*("([^"]+)"|\S+)/iu',
        function ($matches) use (&$operators) {

            $field = mb_strtolower($matches[1]);

            $value = trim(
                $matches[3] ?? trim($matches[2], '"')
            );

            if ($value !== '') {
                $operators[$field] = $value;
            }

            return ' ';
        },
        $keyword
    );

    /*
    |--------------------------------------------------------------------------
    | 3. Pecah Keyword Menjadi Token
    |--------------------------------------------------------------------------
    |
    | Contoh:
    | anak timbangan 20 kg
    |
    | Hasil:
    | anak
    | timbangan
    | 20
    | kg
    |
    | Angka dan satuan yang menempel juga dipisahkan:
    | 20kg menjadi 20 dan kg.
    |
    */

    $rawTokens = preg_split(
        '/\s+/u',
        trim($clean),
        -1,
        PREG_SPLIT_NO_EMPTY
    ) ?: [];

    $tokens = [];

    $supportedUnits = [
        'kg',
        'g',
        'mg',
        'l',
        'ml',
        'mm',
        'cm',
        'm',
        's',
        'c',
        '%',
    ];

    foreach ($rawTokens as $token) {

        $token = trim($token, "\"'");

        if ($token === '') {
            continue;
        }

        // Pisahkan angka dan satuan yang menyatu.
        if (
            preg_match(
                '/^(\d+(?:[.,]\d+)?)(kg|mg|ml|mm|cm|g|l|m|s|c|%)$/iu',
                $token,
                $matches
            )
        ) {

            $tokens[] = $matches[1];
            $tokens[] = $matches[2];

            continue;
        }

        $tokens[] = $token;
    }

    // Buang token terlalu pendek, kecuali angka dan satuan yang dikenal.
    $tokens = array_values(array_filter(
        $tokens,
        function ($token) use ($supportedUnits) {

            return mb_strlen($token) >= 2
                || ctype_digit($token)
                || in_array(
                    mb_strtolower($token),
                    $supportedUnits,
                    true
                );
        }
    ));

    /*
    |--------------------------------------------------------------------------
    | 4. Fungsi Normalisasi Kolom
    |--------------------------------------------------------------------------
    |
    | Menghilangkan spasi saat membandingkan data.
    |
    | Contoh:
    | 20 kg = 20kg
    | 0.01 g = 0.01g
    |
    */

    $noSpace = function (string $column) {
        return \Illuminate\Support\Facades\DB::raw(
            "REPLACE(IFNULL({$column}, ''), ' ', '')"
        );
    };

    /*
    |--------------------------------------------------------------------------
    | 5. Eksekusi Pencarian
    |--------------------------------------------------------------------------
    |
    | AND antar-token:
    | Semua kata harus ditemukan.
    |
    | OR antar-kolom:
    | Setiap kata boleh ditemukan di kolom berbeda.
    |
    */

    return $query->where(function ($q) use (
        $tokens,
        $operators,
        $available,
        $measureAvailable,
        $codeAvailable,
        $noSpace
    ) {

        /*
        |--------------------------------------------------------------------------
        | 5A. Pencarian Umum Multi-Kata
        |--------------------------------------------------------------------------
        */

        foreach ($tokens as $token) {

            $like = '%' . str_replace(' ', '', $token) . '%';

            // Secara default cari di semua kolom.
            $cols = $available;

            /*
            |--------------------------------------------------------------------------
            | Prioritas Kolom Untuk Angka
            |--------------------------------------------------------------------------
            |
            | Angka murni:
            | Cari di kapasitas, ukuran, daya baca,
            | nomor seri, dan nomor identitas.
            |
            | Satuan seperti kg, g, ml:
            | Dicari di semua kolom, termasuk satuan.
            |
            */

            if (
                preg_match(
                    '/^\d+(?:[.,]\d+)?$/u',
                    $token
                )
            ) {

                if ($codeAvailable) {

                    $cols = array_values(array_unique(
                        array_merge(
                            $measureAvailable,
                            $codeAvailable
                        )
                    ));

                } elseif ($measureAvailable) {

                    $cols = $measureAvailable;
                }
            }

            if (empty($cols)) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | OR Antar-Kolom
            |--------------------------------------------------------------------------
            */

            $q->where(function ($sub) use (
                $cols,
                $like,
                $noSpace
            ) {

                foreach ($cols as $index => $col) {

                    $method = $index === 0
                        ? 'where'
                        : 'orWhere';

                    $sub->{$method}(
                        $noSpace($col),
                        'LIKE',
                        $like
                    );
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 5B. Operator Prefix
        |--------------------------------------------------------------------------
        |
        | Operator diterapkan sebagai filter tambahan (AND).
        |
        */

        foreach ($operators as $field => $value) {

            $like = '%' . str_replace(' ', '', $value) . '%';

            /*
            |--------------------------------------------------------------------------
            | Filter Ruangan
            |--------------------------------------------------------------------------
            */

            if ($field === 'ruangan') {

                $q->whereHas('ruangan', function ($r) use (
                    $like,
                    $noSpace
                ) {

                    $r->where(
                        $noSpace('kode_ruangan'),
                        'LIKE',
                        $like
                    )
                    ->orWhere(
                        $noSpace('nama_ruangan'),
                        'LIKE',
                        $like
                    );
                });

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Mapping Alias Kolom
            |--------------------------------------------------------------------------
            */

            $col = match ($field) {

                'seri' => 'no_seri',

                'identitas' => 'no_identitas',

                default => $field,
            };

            /*
            |--------------------------------------------------------------------------
            | Pastikan Kolom Tersedia
            |--------------------------------------------------------------------------
            */

            if (in_array($col, $available, true)) {

                $q->where(
                    $noSpace($col),
                    'LIKE',
                    $like
                );
            }
        }
    });
}
}
