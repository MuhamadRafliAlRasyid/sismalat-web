<?php

namespace App\Exports;

use App\Models\Alat;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithAutoFilter;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet;

class AlatExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithEvents,
    WithAutoFilter,           // ⭐ INI YANG MEMBUAT AUTOFILTER MUNCUL
    ShouldAutoSize,
    WithColumnWidths,
    WithStrictNullComparison
{
    protected $filters;
    protected $lastRow = 1;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Alat::with(['kategori', 'ruangan', 'ruangan.user']);

        if (!empty($this->filters['ruangan_id'])) {
            $query->where('ruangan_id', $this->filters['ruangan_id']);
        }
        if (!empty($this->filters['kategori_id'])) {
            $query->where('kategori_id', $this->filters['kategori_id']);
        }
        if (!empty($this->filters['kondisi'])) {
            if ($this->filters['kondisi'] === 'baik') $query->where('jumlah_baik', '>', 0);
            elseif ($this->filters['kondisi'] === 'kurang_baik') $query->where('jumlah_kurang_baik', '>', 0);
            elseif ($this->filters['kondisi'] === 'rusak_berat') $query->where('jumlah_rusak_berat', '>', 0);
        }

        return $query->orderBy('ruangan_id')->orderBy('kategori_id')->orderBy('nama_alat');
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Ruangan',
            'Nama Ruangan',
            'Penanggung Jawab',
            'Kategori',
            'Nama Alat',
            'Merk',
            'Tipe',
            'No. Seri',
            'Kapasitas',
            'Ukuran',
            'Satuan',
            'Total Unit',
            'Kondisi Baik',
            'Kurang Baik',
            'Rusak Berat',
            'Tahun Pengadaan',
            'No. Sertifikat',
            'Masa Berlaku',
            'Status Kalibrasi',
        ];
    }

    public function map($alat): array
    {
        static $no = 0;
        $no++;
        $this->lastRow = $no + 2; // +2 karena ada header title

        // Hitung status kalibrasi
        $statusKalibrasi = '-';
        if ($alat->masa_berlaku) {
            $today = now();
            $expire = \Carbon\Carbon::parse($alat->masa_berlaku);
            $diffDays = $today->diffInDays($expire, false);

            if ($diffDays < 0) $statusKalibrasi = 'EXPIRED';
            elseif ($diffDays <= 7) $statusKalibrasi = 'WARNING (' . (int)$diffDays . ' hari)';
            else $statusKalibrasi = 'AKTIF';
        }

        return [
            $no,
            $alat->ruangan ? $alat->ruangan->kode_ruangan : '-',
            $alat->ruangan ? $alat->ruangan->nama_ruangan : 'Tanpa Ruangan',
            $alat->ruangan && $alat->ruangan->user ? $alat->ruangan->user->name : '-',
            $alat->kategori ? $alat->kategori->nama : '-',
            $alat->nama_alat,
            $alat->merk ?: '-',
            $alat->tipe ?: '-',
            $alat->no_seri ?: ($alat->no_identitas ?: '-'),
            $alat->kapasitas ?: '-',
            $alat->ukuran ?: '-',
            $alat->satuan ?: 'unit',
            $alat->jumlah,
            $alat->jumlah_baik,
            $alat->jumlah_kurang_baik,
            $alat->jumlah_rusak_berat,
            $alat->tahun_pengadaan ?: '-',
            $alat->no_sertifikat ?: '-',
            $alat->masa_berlaku ? $alat->masa_berlaku->format('d-m-Y') : '-',
            $statusKalibrasi,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 12,  // Kode Ruangan
            'C' => 25,  // Nama Ruangan
            'D' => 22,  // Penanggung Jawab
            'E' => 18,  // Kategori
            'F' => 28,  // Nama Alat
            'G' => 15,  // Merk
            'H' => 12,  // Tipe
            'I' => 15,  // No Seri
            'J' => 12,  // Kapasitas
            'K' => 10,  // Ukuran
            'L' => 8,   // Satuan
            'M' => 10,  // Total
            'N' => 10,  // Baik
            'O' => 11,  // Kurang Baik
            'P' => 11,  // Rusak Berat
            'Q' => 10,  // Tahun
            'R' => 20,  // Sertifikat
            'S' => 12,  // Masa Berlaku
            'T' => 18,  // Status
        ];
    }

    /**
     * Autofilter range - INI KUNCI AGAR FILTER MUNCUL DI HEADER
     */
    public function autoFilter(): string
    {
        // Autofilter akan di-set manual di registerEvents agar dinamis
        return '';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();

                // ===== 1. TAMBAHKAN JUDUL LAPORAN DI BARIS 1 =====
                $sheet->insertNewRowBefore(1, 2); // Sisipkan 2 baris baru di atas

                // Baris 1: Judul Utama
                $sheet->setCellValue('A1', 'LAPORAN INVENTARIS ALAT UKUR');
                $sheet->mergeCells('A1:T1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => '92400E']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(28);

                // Baris 2: Informasi Filter & Tanggal
                $filterText = 'Tanggal Cetak: ' . now()->format('d F Y H:i');
                if (!empty($this->filters['ruangan_id'])) {
                    $ruangan = \App\Models\Ruangan::find($this->filters['ruangan_id']);
                    if ($ruangan) $filterText .= ' | Ruangan: ' . $ruangan->kode_ruangan . ' - ' . $ruangan->nama_ruangan;
                }
                if (!empty($this->filters['kategori_id'])) {
                    $kategori = \App\Models\Kategori::find($this->filters['kategori_id']);
                    if ($kategori) $filterText .= ' | Kategori: ' . $kategori->nama;
                }
                if (!empty($this->filters['kondisi'])) {
                    $filterText .= ' | Kondisi: ' . ucfirst(str_replace('_', ' ', $this->filters['kondisi']));
                }

                $sheet->setCellValue('A2', $filterText);
                $sheet->mergeCells('A2:T2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10, 'color' => ['argb' => '64748B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // ===== 2. HEADER DATA SEKARANG DI BARIS 3 =====
                $headerRange = 'A3:T3';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size' => 10,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => '92400E'], // Amber dark
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '78350F'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(35);

                // ===== 3. ⭐ AUTOFILTER (INI YANG ANDA CARI!) =====
                $lastRow = $sheet->getHighestRow();
                $autoFilterRange = 'A3:T' . $lastRow;
                $sheet->setAutoFilter($autoFilterRange);

                // ===== 4. ⭐ FREEZE PANES (Header tetap terlihat saat scroll) =====
                $sheet->freezePane('A4');

                // ===== 5. STYLE DATA BODY =====
                if ($lastRow >= 4) {
                    $dataRange = 'A4:T' . $lastRow;
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'E2E8F0'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'font' => ['size' => 10],
                    ]);

                    // Zebra striping (baris genap warna amber muda)
                    for ($row = 4; $row <= $lastRow; $row++) {
                        if ($row % 2 == 0) {
                            $sheet->getStyle("A{$row}:T{$row}")->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFFBF2'],
                                ],
                            ]);
                        }
                    }
                }

                // ===== 6. ALIGNMENT KOLOM TERTENTU =====
                // Center: No, Kode, Kapasitas, Ukuran, Satuan, semua angka
                $centerColumns = ['A', 'B', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'S'];
                foreach ($centerColumns as $col) {
                    $sheet->getStyle("{$col}4:{$col}{$lastRow}")
                          ->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // ===== 7. ANGKA BOLD DI KOLOM TOTAL (M) =====
                $sheet->getStyle("M4:M{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'B45309'], 'size' => 11],
                ]);

                // ===== 8. ⭐ CONDITIONAL FORMATTING - WARNA KOLOM KONDISI =====
                // Hijau untuk kolom "Baik"
                $sheet->getStyle("N4:N{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'D1FAE5'], // Hijau muda
                    ],
                    'font' => ['bold' => true, 'color' => ['argb' => '065F46']],
                ]);

                // Kuning untuk kolom "Kurang Baik"
                $sheet->getStyle("O4:O{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FEF3C7'], // Kuning muda
                    ],
                    'font' => ['bold' => true, 'color' => ['argb' => '92400E']],
                ]);

                // Merah untuk kolom "Rusak Berat"
                $sheet->getStyle("P4:P{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FEE2E2'], // Merah muda
                    ],
                    'font' => ['bold' => true, 'color' => ['argb' => '991B1B']],
                ]);

                // ===== 9. STATUS KALIBRASI - WARNA BERDASARKAN NILAI =====
                for ($row = 4; $row <= $lastRow; $row++) {
                    $statusValue = $sheet->getCell("T{$row}")->getValue();

                    if (strpos($statusValue, 'EXPIRED') !== false) {
                        $sheet->getStyle("T{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FEE2E2'],
                            ],
                            'font' => ['bold' => true, 'color' => ['argb' => '991B1B']],
                        ]);
                    } elseif (strpos($statusValue, 'WARNING') !== false) {
                        $sheet->getStyle("T{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FEF3C7'],
                            ],
                            'font' => ['bold' => true, 'color' => ['argb' => '92400E']],
                        ]);
                    } elseif ($statusValue === 'AKTIF') {
                        $sheet->getStyle("T{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'D1FAE5'],
                            ],
                            'font' => ['bold' => true, 'color' => ['argb' => '065F46']],
                        ]);
                    }
                }

                // ===== 10. TAMBAHKAN SUMMARY ROW DI BAWAH =====
                $summaryRow = $lastRow + 2;
                $sheet->setCellValue("A{$summaryRow}", 'TOTAL KESELURUHAN:');
                $sheet->mergeCells("A{$summaryRow}:L{$summaryRow}");

                // Rumus SUM untuk kolom angka
                $sheet->setCellValue("M{$summaryRow}", "=SUM(M4:M{$lastRow})");
                $sheet->setCellValue("N{$summaryRow}", "=SUM(N4:N{$lastRow})");
                $sheet->setCellValue("O{$summaryRow}", "=SUM(O4:O{$lastRow})");
                $sheet->setCellValue("P{$summaryRow}", "=SUM(P4:P{$lastRow})");

                $sheet->getStyle("A{$summaryRow}:P{$summaryRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'B45309'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THICK,
                            'color' => ['argb' => '78350F'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension($summaryRow)->setRowHeight(25);

                // ===== 11. PRINT SETTINGS =====
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // Print title rows (header repeat saat print)
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);

                // Margin
                $sheet->getPageMargins()->setTop(0.5);
                $sheet->getPageMargins()->setRight(0.5);
                $sheet->getPageMargins()->setLeft(0.5);
                $sheet->getPageMargins()->setBottom(0.5);

                // Header & Footer saat print
                $sheet->getHeaderFooter()->setOddHeader('&L&DIN PERINDAG KARAWANG&C&BLAPORAN INVENTARIS&R&Dicetak: &D');
                $sheet->getHeaderFooter()->setOddFooter('&LSISMALAT System&CHalaman &P dari &N&R&Dokumen Resmi');
            },
        ];
    }
}
