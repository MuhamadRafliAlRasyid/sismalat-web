<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class RuanganSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk membuat data ruangan KIR.
     * Menggunakan mapping berdasarkan email user (lebih stabil daripada ID).
     */
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // DATA RUANGAN KIR (dari file KIR KANTOR.xlsx)
        // ═══════════════════════════════════════════════════════════════
        $ruangans = [
            [
                'kode_ruangan' => 'C1',
                'nama_ruangan' => 'Ruang Resepsionis / Lobby',
                'email_pj'     => 'dede.supriyadi@sismalat.local',
            ],
            [
                'kode_ruangan' => 'C3',
                'nama_ruangan' => 'Ruang Alat Tera / Ukur Ulang',
                'email_pj'     => 'agung.lantika@sismalat.local',
            ],
            [
                'kode_ruangan' => 'C4',
                'nama_ruangan' => 'Ruang UTTP (Ukur, Takar, Timbang, Perlengkapan)',
                'email_pj'     => 'umawan@sismalat.local',
            ],
            [
                'kode_ruangan' => 'C5',
                'nama_ruangan' => 'Ruang Staf Teknis',
                'email_pj'     => 'fitra.hanida@sismalat.local',
            ],
            [
                'kode_ruangan' => 'C6',
                'nama_ruangan' => 'Laboratorium UTTP',
                'email_pj'     => 'achmad.priyono@sismalat.local',
            ],
            [
                'kode_ruangan' => 'C10',
                'nama_ruangan' => 'Ruang Rapat / Kepala Bidang',
                'email_pj'     => 'resa.makasuci@sismalat.local',
            ],
            [
                'kode_ruangan' => 'C11',
                'nama_ruangan' => 'Ruang Staf Administratif',
                'email_pj'     => 'susilawati@sismalat.local',
            ],
            [
                'kode_ruangan' => 'C12',
                'nama_ruangan' => 'Ruang Tamu / Tenaga Ahli',
                'email_pj'     => 'ricky.hermawan@sismalat.local',
            ],
        ];

        $this->command->info('');
        $this->command->info('🏢  Mulai seeder Ruangan KIR...');
        $this->command->info(str_repeat('─', 60));

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($ruangans as $data) {
            // Cari user PJ berdasarkan email
            $user = User::where('email', $data['email_pj'])->first();

            if (!$user) {
                $this->command->warn("⚠️  User dengan email {$data['email_pj']} tidak ditemukan. Lewati ruangan {$data['kode_ruangan']}.");
                $skipped++;
                continue;
            }

            // Gunakan firstOrCreate untuk menghindari duplikasi
            $ruangan = Ruangan::updateOrCreate(
                ['kode_ruangan' => $data['kode_ruangan']],
                [
                    'nama_ruangan' => $data['nama_ruangan'],
                    'user_id'      => $user->id,
                ]
            );

            $status = $ruangan->wasRecentlyCreated ? '✨ CREATED' : '🔄 UPDATED';

            if ($ruangan->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }

            $this->command->info(sprintf(
                '%s | %s → %s (%s)',
                $status,
                $ruangan->kode_ruangan,
                $user->nama_lengkap,
                $user->nip_formatted ?? $user->nip
            ));
        }

        $this->command->info(str_repeat('─', 60));
        $this->command->info("✅ Selesai! Created: {$created}, Updated: {$updated}, Skipped: {$skipped}");
        $this->command->info('');
    }
}
