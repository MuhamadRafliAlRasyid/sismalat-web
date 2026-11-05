<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlatKirSeeder extends Seeder
{
    /** Pool referensi: alat standar dari tabel alats (belum ber-ruangan & bersertifikat) */
    protected $referensi;

    public function run(): void
    {
        // ══════════ 1) KATEGORI ══════════
        $kat = [
            'uttp'  => Kategori::firstOrCreate(['nama' => 'UTTP / Alat Ukur'],        ['keterangan' => 'Alat ukur, takar, timbang dan perlengkapannya']),
            'mebel' => Kategori::firstOrCreate(['nama' => 'Mebel & Furnitur'],        ['keterangan' => 'Meja, kursi, lemari, rak, sofa']),
            'elek'  => Kategori::firstOrCreate(['nama' => 'Elektronik'],              ['keterangan' => 'AC, TV, printer, laptop, audio']),
            'perl'  => Kategori::firstOrCreate(['nama' => 'Perlengkapan & Perkakas'], ['keterangan' => 'Perlengkapan kantor, keselamatan, perkakas']),
        ];

        // ══════════ 2) RUANGAN (PJ diambil dari tabel users) ══════════
        $pjMap = [
            'C1'  => 'dede.supriyadi@sismalat.local',
            'C3'  => 'agung.lantika@sismalat.local',
            'C4'  => 'umawan@sismalat.local',
            'C5'  => 'fitra.hanida@sismalat.local',
            'C6'  => 'achmad.priyono@sismalat.local',
            'C10' => 'resa.makasuci@sismalat.local',
            'C11' => 'susilawati@sismalat.local',
            'C12' => 'ricky.hermawan@sismalat.local',
        ];

        $ruangans = [];
        foreach ($pjMap as $kode => $email) {
            $ruangans[$kode] = Ruangan::firstOrCreate(
                ['kode_ruangan' => $kode],
                ['user_id' => User::where('email', $email)->first()?->id]
            );
        }

        // ══════════ 3) REFERENSI DARI TABEL alats ══════════
        $this->referensi = Alat::whereNull('ruangan_id')
            ->whereNotNull('no_sertifikat')
            ->get();

        // ══════════ 4) DATA KIR (89 baris dari KIR KANTOR.xls) ══════════
        // Format: [ruangan, nama, merk, ukuran, volume, baik, kb, rb, ket(tahun)]
        $items = [
            // ── C11 (SUSILAWATI, S.T) ──
            ['C11', 'MEJA KERJA',        null, '1 BIRO', 1, 1, 0, 0, 2014],
            ['C11', 'KURSI KERJA',       null, null,     1, 1, 0, 0, 2014],
            ['C11', 'KURSI TAMU',        null, null,     2, 2, 0, 0, 2014],
            ['C11', 'AIR CONDISIONER',   'LG', null,     1, 1, 0, 0, 2014],
            ['C11', 'KURSI + MEJA TAMU', null, null,     1, 1, 0, 0, 2014],
            ['C11', 'LEMARI BESI',       null, null,     1, 1, 0, 0, 2014],

            // ── C12 (RICKY HERMAWAN, A.Md) ──
            ['C12', 'KURSI TAMU',        null, null,     1, 1, 0, 0, 2014],

            // ── C1 (DEDE SUPRIYADI) ──
            ['C1', 'MEJA RESEPSIONIS',   null, '1 SET',  1, 1, 0, 0, 2015],
            ['C1', 'KURSI LIPAT',        null, null,     2, 2, 0, 0, 2014],
            ['C1', 'KURSI TUNGGU',       null, null,     2, 2, 0, 0, 2014],
            ['C1', 'AIR CONDISIONER',    'SAMSUNG', '2 PK', 1, 1, 0, 0, 2014],
            ['C1', 'SOFA TAMU',          null, null,     1, 1, 0, 0, 2015],
            ['C1', 'LEMARI BESI',        null, null,     1, 1, 0, 0, 2014],

            // ── C3 (AGUNG LANTIKA PRATAMA, A.Md) ──
            ['C3', 'RAK KAYU',           null, null,     2, 2, 0, 0, null],
            ['C3', 'KURSI',              null, null,     4, 0, 4, 0, null],
            ['C3', 'SEPATU BOT',         null, '2 SET',  2, 2, 0, 0, null],
            ['C3', 'BEJANA UKUR STANDAR','DB', '50 LITER', 1, 1, 0, 0, 2002],
            ['C3', 'TIMBANGAN PEGAS',    null, null,     9, 9, 0, 0, 2010],
            ['C3', 'TIMBANGAN UKUR ULANG','METTLER TOLEDO', '30 kg', 1, 0, 1, 0, 2014],
            ['C3', 'VISI MISI',          null, null,     1, 1, 0, 0, null],
            ['C3', 'PETA',               null, null,     1, 1, 0, 0, 2014],

            // ── C5 (FITRA HANIDA FAUZIYAH, S.Si) ──
            ['C5', 'MEJA KERJA',         null, '1/2 BIRO', 1, 1, 0, 0, null],
            ['C5', 'KURSI KERJA',        null, null,     1, 1, 0, 0, null],
            ['C5', 'RAK BESI',           null, null,     1, 1, 0, 0, 2015],
            ['C5', 'RAK KAYU',           null, null,     1, 1, 0, 0, null],
            ['C5', 'AIR CONDISIONER',    null, '1/2 PK', 1, 1, 0, 0, null],

            // ── C6 (ACHMAD PRIYONO, S.T) ──
            ['C6', 'JAS LAB',            null, null,     2, 2, 0, 0, 2014],
            ['C6', 'BEJANA UKUR',        'MJ', '20 l',   1, 1, 0, 0, 2014],
            ['C6', 'LANDASAN BEJANA',    null, null,     1, 1, 0, 0, 2014],
            ['C6', 'NERACA A',           'MDH', '50 kg', 1, 1, 0, 0, 2014],
            ['C6', 'NERACA B',           'MDH', '10 kg', 1, 1, 0, 0, 2014],
            ['C6', 'NERACA C',           'MDH', '1 kg',  1, 1, 0, 0, 2014],
            ['C6', 'NERACA D',           'MDH', '50 g',  1, 1, 0, 0, 2014],
            ['C6', 'NERACA E',           'MDH', '1 g',   1, 1, 0, 0, 2014],
            ['C6', 'DEHUMIDIFIER',       'Best Air', null, 1, 1, 0, 0, 2014],
            ['C6', 'THERMOHYGROMETER ANALOG', 'Germany', null, 1, 1, 0, 0, null],
            ['C6', 'THERMOHYGROMETER DIGITAL', 'Extech', null, 1, 1, 0, 0, null],
            ['C6', 'PEMADAM KEBAKARAN',  'Protech', null, 1, 1, 0, 0, null],
            ['C6', 'TIMBANGAN ELEKTRONIK','Vibra', '62 kg,d=0,1 g', 1, 1, 0, 0, null],
            ['C6', 'METER KERJA',        'MRI', '1 m',   1, 1, 0, 0, null],
            ['C6', 'LEMARI KACA 1',      'VIP', null,    1, 1, 0, 0, 2014],
            ['C6', 'LEMARI KACA 2',      'ITAKA', null,  1, 1, 0, 0, 2014],
            ['C6', 'LEMARI KACA 3',      'VIP', null,    1, 1, 0, 0, 2014],
            ['C6', 'AC',                 'LG', '2 pk',   1, 1, 0, 0, 2014],
            ['C6', 'JAS LABORATORIUM',   'DELL', null,   1, 1, 0, 0, 2014],
            ['C6', 'PRINTER LASER',      'CANNON', null, 1, 1, 0, 0, 2014],

            // ── C10 (RESA MAKASUCI, S.T) ──
            ['C10', 'MEJA KERJA',        'EXPO', '1/2 BIRO', 10, 8, 2, 0, 2014],
            ['C10', 'FILLING CABINET',   'VIP', null,     1, 8, 0, 0, 2014], // sesuai dokumen
            ['C10', 'LEMARI',            'VIP', null,     1, 1, 0, 0, 2014],
            ['C10', 'TELEVISI',          'POLYTRON', '32"', 1, 1, 0, 0, 2014],
            ['C10', 'DVD',               'POLYTRON', null, 1, 1, 0, 0, 2014],
            ['C10', 'SPEAKER',           'POLYTRON', null, 1, 1, 0, 0, 2014],
            ['C10', 'AMPLIFIER',         'POLYTRON', null, 1, 1, 0, 0, 2014],
            ['C10', 'KURSI KERJA',       null, null,    10, 8, 0, 2, 2014],
            ['C10', 'AIR CONDISIONER',   'LG', '1/5 PK', 5, 2, 0, 3, null],
            ['C10', 'PRINTER',           'CANNON', null, 1, 1, 0, 0, null],
            ['C10', 'LAPTOP',            'COMPAC', null, 1, 1, 0, 0, null],
            ['C10', 'PETA',              null, null,     1, 1, 0, 0, null],
            ['C10', 'SOFA TAMU',         null, null,     1, 1, 0, 0, 2015],
            ['C10', 'STRUKTUR ORGANISASI', null, null,   1, 1, 0, 0, null],

            // ── C4 (UMAWAN, S.T) ──
            ['C4', 'BATU BIDUR',         'YOKO', '20 kg', 42, 42, 0, 0, 2002],
            ['C4', 'BATU BIDUR',         'RC',   '20 kg', 93, 93, 0, 0, 2014],
            ['C4', 'BATU BIDUR',         'DLA',  '20 kg', 100, 100, 0, 0, 2015],
            ['C4', 'MEJA SIDANG',        null, null,     4, 4, 0, 0, 2014],
            ['C4', 'STANDAR DACIN',      null, '100 kg', 2, 2, 0, 0, 2014],
            ['C4', 'BEJANA UKUR',        'DB',  '20 l',  1, 1, 0, 0, 2002],
            ['C4', 'BEJANA UKUR',        'MDH', '20 l',  1, 1, 0, 0, 2014],
            ['C4', 'BEJANA UKUR',        'DB',  '10 l',  1, 1, 0, 0, 2002],
            ['C4', 'BEJANA UKUR',        'DB',  '5 l',   1, 1, 0, 0, 2002],
            ['C4', 'TIMBANGAN ELEKTRONIK','CAHAYA ADIL', '15 kg', 18, 18, 0, 0, 2013],
            ['C4', 'LANDASAN BEJANA',    null, null,     2, 2, 0, 0, null],
            ['C4', 'PEMADAM KEBAKARAN',  'Protech', null, 1, 1, 0, 0, null],
            ['C4', 'TRIPOD',             null, null,     2, 2, 0, 0, 2014],
            ['C4', 'RAGUM',              null, null,     1, 1, 0, 0, null],
            ['C4', 'TONGKAT UKUR',       null, '2 METER', 1, 1, 0, 0, 2014],
            ['C4', 'ANAK TIMBANGAN',     null, '2 kg',  18, 18, 0, 0, null],
            ['C4', 'ANAK TIMBANGAN',     'VIP', '5 kg',  9, 9, 0, 0, null],
            ['C4', 'ANAK TIMBANGAN',     'LG',  '10 kg', 8, 8, 0, 0, null],
            ['C4', 'FORKLIFT MINI',      'KRISBOW', '2 TON', 1, 1, 0, 0, 2014],
            ['C4', 'SALIB UKUR',         null, null,     1, 1, 0, 0, null],
            ['C4', 'STELAN TIMBANGAN MEJA', null, null, 30, 30, 0, 0, 2013],
            ['C4', 'WATER PAS',          null, null,     3, 3, 0, 0, null],
            ['C4', 'LANDASAN CTT',       null, null,     1, 1, 0, 0, 2002],
            ['C4', 'TOOL SEET',          null, null,     4, 0, 4, 0, null],
            ['C4', 'KUNCI SHOCK',        null, '1 SET',  1, 1, 0, 0, null],
            ['C4', 'TIMBANGAN ELEKTRONIK','GWIN', '62 kg', 1, 1, 0, 0, null],
            ['C4', 'FRAME UKUR',         null, '100 kg', 1, 1, 0, 0, 2002],
            ['C4', 'PROSEDUR TERA ULANG', null, null,    1, 1, 0, 0, 2013],
            ['C4', 'THERMOMETER',        null, null,     1, 1, 0, 0, null],
        ];

        $this->command->info('');
        $this->command->info('📦 Import ' . count($items) . ' barang KIR + pencocokan referensi...');
        $this->command->info(str_repeat('─', 72));

        $matched = 0;

        foreach ($items as [$kode, $nama, $merk, $ukuran, $volume, $baik, $kb, $rb, $tahun]) {
            if (empty($ruangans[$kode])) {
                $this->command->warn("⚠️  Ruangan {$kode} tidak ditemukan — skip: {$nama}");
                continue;
            }

            $volumeU = strtoupper(trim((string) $volume));
            $jumlah  = (int) preg_replace('/[^0-9]/', '', $volumeU);
            $satuan  = str_contains($volumeU, 'SET') ? 'set' : 'unit';

            // 🔍 Cari referensi di tabel alats (nama sama → isi field detail)
            $ref = $this->cariReferensi($nama, $merk, $ukuran);

            $data = [
                'jumlah'             => $jumlah,
                'satuan'             => $satuan,
                'jumlah_baik'        => $baik,
                'jumlah_kurang_baik' => $kb,
                'jumlah_rusak_berat' => $rb,
                'tahun_pengadaan'    => is_numeric($tahun) ? (int) $tahun : null,
                'kategori_id'        => $ref?->kategori_id ?? $kat[$this->klasifikasi($nama)]->id,
            ];

            if ($ref) {
                $matched++;
                $data = array_merge($data, [
                    'kelas'         => $ref->kelas,
                    'tipe'          => $ref->tipe,
                    'no_seri'       => $ref->no_seri,
                    'no_identitas'  => $ref->no_identitas,
                    'kapasitas'     => $ref->kapasitas,
                    'daya_baca'     => $ref->daya_baca,
                    'no_sertifikat' => $ref->no_sertifikat,
                    'masa_berlaku'  => $ref->masa_berlaku,
                ]);
                $this->command->info("✅ MATCH | {$nama} ({$kode}) ← ref #{$ref->id} | seri {$ref->no_seri} | berlaku {$ref->masa_berlaku}");
            }

            Alat::updateOrCreate(
                [
                    'nama_alat'  => $nama,
                    'merk'       => $merk,
                    'ukuran'     => $ukuran,
                    'ruangan_id' => $ruangans[$kode]->id,
                ],
                $data
            );
        }

        $this->command->info(str_repeat('─', 72));
        $this->command->info("✅ Selesai! {$matched} alat ukur diperkaya dari tabel referensi.");
        $this->command->info('');
    }

    // ═══════════════════════ PENCOCOKAN REFERENSI ═══════════════════════

    /** Alias nama KIR → nama di tabel referensi */
    private const ALIAS = [
        'THERMOMETER'              => 'TERMOMETER DIGITAL',
        'TONGKAT UKUR'             => 'TONGKAT DUGA',
        'THERMOHYGROMETER ANALOG'  => 'THERMOHYGROMETER',
        'THERMOHYGROMETER DIGITAL' => 'THERMOHYGROMETER',
        'BEJANA UKUR STANDAR'      => 'BEJANA UKUR',
    ];

    private function cariReferensi(string $nama, ?string $merk, ?string $ukuran): ?Alat
    {
        $namaNorm = $this->norm(self::ALIAS[strtoupper(trim($nama))] ?? $nama);
        $merkAda  = $merk && $merk !== '-';
        [$kapNum, $kapUnit] = $this->capParts($ukuran);

        $candidates = $this->referensi->filter(fn ($a) => $this->norm($a->nama_alat) === $namaNorm);
        if ($candidates->isEmpty()) return null;

        $merkCocok = fn (Alat $a) => strtolower(trim((string) $a->merk)) === strtolower(trim((string) $merk));
        $kapCocok  = function (Alat $a) use ($kapNum, $kapUnit) {
            if ($kapNum <= 0) return false;
            [$rNum, $rUnit] = $this->capParts($a->kapasitas);
            return $rNum == $kapNum && ($rUnit === '' || $kapUnit === '' || $rUnit === $kapUnit);
        };

        // P1: merk + kapasitas cocok
        if ($merkAda && ($m = $candidates->first(fn ($a) => $merkCocok($a) && $kapCocok($a)))) return $m;
        // P2: merk kosong di KIR + kapasitas cocok
        if (!$merkAda && ($m = $candidates->first(fn ($a) => $kapCocok($a)))) return $m;
        // P3: merk cocok + ukuran KIR kosong
        if ($merkAda && !$kapNum && ($m = $candidates->first(fn ($a) => $merkCocok($a)))) return $m;
        // P4: merk & ukuran kosong, kandidat tunggal
        if (!$merkAda && !$kapNum && $candidates->count() === 1) return $candidates->first();

        return null;
    }

    /** Klasifikasi barang non-referensi ke kategori */
    private function klasifikasi(string $jenis): string
    {
        $j = strtoupper($jenis);

        $uttp = ['TIMBANGAN', 'NERACA', 'BEJANA', 'ANAK TIMBANGAN', 'BATU BIDUR', 'DACIN',
                 'METER', 'THERMOMETER', 'THERMOHYGROMETER', 'SALIB UKUR', 'TONGKAT',
                 'FRAME UKUR', 'STELAN', 'WATER PAS', 'LANDASAN'];
        $mebel = ['MEJA', 'KURSI', 'LEMARI', 'SOFA', 'RAK', 'FILLING'];
        $elek  = ['AIR CONDISIONER', 'AC', 'TELEVISI', 'DVD', 'SPEAKER', 'AMPLIFIER', 'PRINTER', 'LAPTOP', 'DEHUMIDIFIER'];

        foreach ($uttp as $k)  if (str_contains($j, $k)) return 'uttp';
        foreach ($mebel as $k) if (str_contains($j, $k)) return 'mebel';
        foreach ($elek as $k)  if (str_contains($j, $k)) return 'elek';

        return 'perl';
    }

    private function norm(?string $v): string
    {
        return trim(preg_replace('/\s+/', ' ', strtolower((string) $v)));
    }

    private function capParts(?string $v): array
    {
        if (!$v || $v === '-') return [0, ''];
        preg_match('/([0-9]+(?:[.,][0-9]+)?)/', $v, $m);
        $num = (float) str_replace(',', '.', $m[1] ?? 0);
        $unit = '';
        if (preg_match('/(liter|ml|kg|mg|g|ton|mm|cm|m|l)\b/i', $v, $mu)) {
            $unit = strtolower($mu[1]);
            if ($unit === 'liter') $unit = 'l';
        }
        return [$num, $unit];
    }
}
