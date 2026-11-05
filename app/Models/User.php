<?php

namespace App\Models;

use App\Models\Bagian;
use App\Models\Traits\HasHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, HasTeams, Notifiable, TwoFactorAuthenticatable, HasHashId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bagian_id',
        'google_id',
        'profile_photo_path',
        'onboarding_completed',
        // ✨ Field KIR baru
        'nip',
        'jabatan',
        'gelar_depan',
        'gelar_belakang',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'hashid',
        'nama_lengkap',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'onboarding_completed' => 'boolean',
        ];
    }

    // ================= RELASI =================

    public function bagian()
    {
        return $this->belongsTo(Bagian::class);
    }

    /**
     * ✨ Satu user bisa menjadi Penanggung Jawab banyak ruangan (KIR)
     */
    public function ruangans()
    {
        return $this->hasMany(Ruangan::class);
    }

    /**
     * ✨ Riwayat peminjaman alat yang dilakukan user
     */
    public function pengambilanAlats()
    {
        return $this->hasMany(PengambilanAlat::class);
    }

    // ================= ACCESSOR =================

    /**
     * ✨ Nama lengkap dengan gelar (depan + nama + belakang)
     * Contoh: "SUSILAWATI, S.T" atau "Ir. H. HANAFI, MM"
     */
    public function getNamaLengkapAttribute(): string
    {
        $parts = array_filter([
            $this->gelar_depan,
            $this->name,
            $this->gelar_belakang,
        ]);

        $nama = implode(' ', $parts);

        // Jika tidak ada nama sama sekali, fallback ke name
        return $nama ?: $this->name;
    }

    /**
     * ✨ Label untuk dropdown pemilihan PJ ruangan
     * Contoh: "SUSILAWATI, S.T (NIP. 19721010 200604 2 019) — Penanggung Jawab C11"
     */
    public function getLabelRuanganAttribute(): string
    {
        $nama = $this->nama_lengkap;
        $nip  = $this->nip ? " (NIP. {$this->nip})" : '';
        $jab  = $this->jabatan ? " — {$this->jabatan}" : '';

        return "{$nama}{$nip}{$jab}";
    }

    /**
     * ✨ Format NIP dengan spasi agar mudah dibaca
     * Input: "197210102006042019"
     * Output: "19721010 200604 2 019"
     */
    public function getNipFormattedAttribute(): ?string
    {
        if (!$this->nip) {
            return null;
        }

        $nip = preg_replace('/\D/', '', $this->nip);

        if (strlen($nip) === 18) {
            return substr($nip, 0, 8) . ' ' .
                   substr($nip, 8, 6) . ' ' .
                   substr($nip, 14, 1) . ' ' .
                   substr($nip, 15, 3);
        }

        return $this->nip;
    }

    // ================= ROLE CHECKER =================

   // ================= ROLE CHECKER =================

public function isAdmin(): bool
{
    return $this->role === 'admin';
}

public function isSuper(): bool
{
    return $this->role === 'super';
}

public function isKaryawan(): bool
{
    return $this->role === 'karyawan';
}

public function isAdminOrSuper(): bool
{
    return in_array($this->role, ['admin', 'super']);
}

/**
 * ✨ Cek apakah user memiliki salah satu role dari daftar yang diberikan.
 */
public function hasRole($roles): bool
{
    $roles = is_array($roles) ? $roles : [$roles];
    return in_array($this->role, $roles, true);
}

/**
 * ✨ Cek apakah user ini bisa menjadi PJ ruangan
 */
public function bisaJadiPJ(): bool
{
    return $this->isAdminOrSuper() || !empty($this->nip);
}

    // ================= SLACK NOTIFICATION =================

    public function routeNotificationForSlack($notification)
    {
        return env('SLACK_WEBHOOK_URL');
    }
}
