<?php

namespace App\Models;

use App\Models\Traits\HasHashId; // ✅ 1. Import Trait
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory, HasHashId; // ✅ 2. Tambahkan HasHashId di sini

    protected $fillable = [
        'kode_ruangan',
        'nama_ruangan',
        'user_id',
    ];

    // ✅ 3. Tambahkan 'hashid' ke appends agar otomatis muncul saat model di-serialize
    protected $appends = ['hashid'];

    // ================= RELASI =================
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alats()
    {
        return $this->hasMany(Alat::class);
    }

    // ================= ACCESSOR =================

    public function getPenanggungJawabAttribute(): ?string
    {
        return $this->user?->name; // Catatan: pastikan kolom di tabel users adalah 'name' sesuai DB Anda
    }

    public function getNipAttribute(): ?string
    {
        return $this->user?->nip ?? '-'; // Pastikan kolom nip ada di tabel users, atau sesuaikan
    }

    public function getLabelAttribute(): string
    {
        $pj = $this->user ? " — {$this->user->name}" : '';
        return "{$this->kode_ruangan}{$pj}";
    }

    public function getStatistikAttribute(): array
    {
        return [
            'total_item'        => $this->alats()->count(),
            'total_baik'        => $this->alats()->sum('jumlah_baik'),
            'total_kurang_baik' => $this->alats()->sum('jumlah_kurang_baik'),
            'total_rusak_berat' => $this->alats()->sum('jumlah_rusak_berat'),
        ];
    }
}
