<?php

namespace App\Models;

use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PengembalianAlat extends Model
{
    use HasFactory, HasHashId, Notifiable;

    protected $table = 'pengembalian_alats';

    protected $appends = ['hashid'];

    protected $fillable = [
        'pengambilan_alat_id',
        'pengambilan_alat_item_id', // ← TAMBAHKAN INI (Wajib untuk fitur multi-item return)
        'user_id',
        'jumlah',
        'tanggal_pengembalian',
        'keterangan',
        'foto',
    ];

    protected $casts = [
        // Gunakan 'datetime' agar jam pengembalian tidak hilang (sesuai skema DB)
        'tanggal_pengembalian' => 'datetime',
    ];

    /* ================= RELASI ================= */

    public function pengambilan()
    {
        return $this->belongsTo(PengambilanAlat::class, 'pengambilan_alat_id');
    }

    /**
     * Relasi ke item spesifik dari batch peminjaman.
     * Digunakan untuk mengetahui alat mana yang dikembalikan dari sebuah batch.
     */
    public function item()
    {
        return $this->belongsTo(PengambilanAlatItem::class, 'pengambilan_alat_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ================= ACCESSOR ================= */

    public function getFotoThumbAttribute()
    {
        if (!$this->foto) return null;
        return asset('storage/pengembalian/thumb_' . $this->foto);
    }

    public function getFotoUrlAttribute()
    {
        if (!$this->foto) return null;
        return asset('storage/pengembalian/' . $this->foto);
    }
}
