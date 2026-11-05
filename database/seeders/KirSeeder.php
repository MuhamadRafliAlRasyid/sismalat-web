<?php
// database/seeders/KirSeeder.php

namespace Database\Seeders;

use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KirSeeder extends Seeder
{
    public function run(): void
    {
        // Data PJ dari file KIR KANTOR.xlsx
        $penanggungJawab = [
            [
                'name'           => 'SUSILAWATI',
                'gelar_belakang' => 'S.T',
                'nip'            => '197210102006042019',
                'jabatan'        => 'Penanggung Jawab Ruangan C11',
                'email'          => 'susilawati@sismalat.local',
                'ruangan_kode'   => 'C11',
            ],
            [
                'name'           => 'RICKY HERMAWAN',
                'gelar_belakang' => 'A.Md',
                'nip'            => '198805152010011005',
                'jabatan'        => 'Tenaga Ahli/Teknis',
                'email'          => 'ricky.hermawan@sismalat.local',
                'ruangan_kode'   => 'C12',
            ],
            [
                'name'           => 'DEDE SUPRIYADI',
                'nip'            => '197112062012121001',
                'jabatan'        => 'Penanggung Jawab Ruangan C1',
                'email'          => 'dede.supriyadi@sismalat.local',
                'ruangan_kode'   => 'C1',
            ],
            [
                'name'           => 'AGUNG LANTIKA PRATAMA',
                'gelar_belakang' => 'A.Md',
                'nip'            => '198506072015031004',
                'jabatan'        => 'Tenaga Ahli/Teknis',
                'email'          => 'agung.lantika@sismalat.local',
                'ruangan_kode'   => 'C3',
            ],
            [
                'name'           => 'FITRA HANIDA FAUZIYAH',
                'gelar_belakang' => 'S.Si',
                'nip'            => '198506072015032004',
                'jabatan'        => 'Penanggung Jawab Ruangan C5',
                'email'          => 'fitra.hanida@sismalat.local',
                'ruangan_kode'   => 'C5',
            ],
            [
                'name'           => 'ACHMAD PRIYONO',
                'gelar_belakang' => 'S.T',
                'nip'            => '198311162011011002',
                'jabatan'        => 'Penanggung Jawab Ruangan C6',
                'email'          => 'achmad.priyono@sismalat.local',
                'ruangan_kode'   => 'C6',
            ],
            [
                'name'           => 'RESA MAKASUCI',
                'gelar_belakang' => 'S.T',
                'nip'            => '198607172010011007',
                'jabatan'        => 'Penanggung Jawab Ruangan C10',
                'email'          => 'resa.makasuci@sismalat.local',
                'ruangan_kode'   => 'C10',
            ],
            [
                'name'           => 'UMAWAN',
                'gelar_belakang' => 'S.T',
                'nip'            => '198410102011011001',
                'jabatan'        => 'Penanggung Jawab Ruangan C4',
                'email'          => 'umawan@sismalat.local',
                'ruangan_kode'   => 'C4',
            ],
            // Pengurus & Kepala Dinas (untuk tanda tangan KIR)
            [
                'name'           => 'HERU TRIASAPTA',
                'nip'            => '196512261990101001',
                'jabatan'        => 'Pengurus/Penyimpan Aset Daerah',
                'email'          => 'heru.triasapta@sismalat.local',
                'ruangan_kode'   => null,
            ],
            [
                'name'           => 'HANAFI',
                'gelar_depan'    => 'Ir. H.',
                'gelar_belakang' => 'MM',
                'nip'            => '196402101990031005',
                'jabatan'        => 'Kepala Dinas Perindustrian, Perdagangan, Pertambangan dan Energi',
                'email'          => 'hanafi@sismalat.local',
                'ruangan_kode'   => null,
                'role'           => 'admin',
            ],
        ];

        foreach ($penanggungJawab as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'            => $data['name'],
                    'gelar_depan'     => $data['gelar_depan'] ?? null,
                    'gelar_belakang'  => $data['gelar_belakang'] ?? null,
                    'nip'             => $data['nip'],
                    'jabatan'         => $data['jabatan'],
                    'password'        => Hash::make('rafli123'),
                    'role'            => $data['role'] ?? 'karyawan',
                    'email_verified_at' => now(),
                ]
            );

            // Buat ruangan jika kode_ruangan ada
            if (!empty($data['ruangan_kode'])) {
                Ruangan::updateOrCreate(
                    ['kode_ruangan' => $data['ruangan_kode']],
                    [
                        'nama_ruangan' => 'Ruangan ' . $data['ruangan_kode'],
                        'user_id'      => $user->id,
                    ]
                );
            }
        }

        $this->command->info('✅ User PJ & Ruangan berhasil di-seed.');
    }
}
