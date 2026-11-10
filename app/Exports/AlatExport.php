<?php

namespace App\Exports;

use App\Models\Alat;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AlatExport implements
    FromQuery,
    WithMapping,
    WithHeadings,
    WithCustomStartCell,
    WithColumnWidths,
    WithColumnFormatting,
    WithEvents,
    WithTitle
{
    protected array $filters;
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Data
    |--------------------------------------------------------------------------
    */

    private function filteredQuery(): Builder
    {
        $query = Alat::query()
            ->with(['kategori', 'ruangan']);

        // Skip soft-deleted
        if (\Illuminate\Support\Facades\Schema::hasColumn('alats', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        // Pencarian
        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        // Filter kategori
        if (!empty($this->filters['kategori_id'])) {
            $query->where('kategori_id', $this->filters['kategori_id']);
        }

        // Filter ruangan
        if (!empty($this->filters['ruangan_id'])) {
            $query->where('ruangan_id', $this->filters['ruangan_id']);
        }

        // Filter status kalibrasi
        $status = $this->filters['status_kalibrasi'] ?? null;

        if (in_array($status, ['expired', 'warning', 'active', 'unknown'], true)) {
            $today = now()->startOfDay();
            $warningEnd = $today->copy()->addDays(30);

            switch ($status) {
                case 'expired':
                    $query->whereNotNull('masa_berlaku')
                        ->whereDate('masa_berlaku', '<', $today->toDateString());
                    break;

                case 'warning':
                    $query->whereNotNull('masa_berlaku')
                        ->whereDate('masa_berlaku', '>=', $today->toDateString())
                        ->whereDate('masa_berlaku', '<=', $warningEnd->toDateString());
                    break;

                case 'active':
                    $query->whereNotNull('masa_berlaku')
                        ->whereDate('masa_berlaku', '>', $warningEnd->toDateString());
                    break;

                case 'unknown':
                    $query->whereNull('masa_berlaku');
                    break;
            }
        }

        return $query;
    }

    public function query(): Builder
    {
        return $this->filteredQuery()
            ->orderBy('nama_alat')
            ->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Structure
    |--------------------------------------------------------------------------
    */

    public function title(): string
    {
        return 'Laporan Data Alat';
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA ALAT',
            'KELAS',
            'MEREK',
            'TIPE / MODEL',
            'NOMOR SERI',
            'NOMOR IDENTITAS',
            'KAPASITAS',
            'DAYA BACA',
            'JUMLAH UNIT',
            'KATEGORI',
            'LOKASI / RUANGAN',
            'MASA BERLAKU KALIBRASI',
            'STATUS KALIBRASI',
            'SISA HARI',
            'NOMOR SERTIFIKAT',
            'RINGKASAN KONDISI',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Map Data
    |--------------------------------------------------------------------------
    */

    private function safeText($value, string $fallback = '-'): string
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return $fallback;
        }

        // Mencegah teks dibaca sebagai formula Excel
        if (preg_match('/^[=+\-@]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    public function map($alat): array
    {
        $this->rowNumber++;

        $statusLabels = [
            'active'  => 'Aktif',
            'warning' => 'Segera Kalibrasi',
            'expired' => 'Kedaluwarsa',
            'unknown' => 'Belum Terjadwal',
        ];

        $namaRuangan = '-';
        if ($alat->ruangan) {
            $kode = $alat->ruangan->kode_ruangan ?? '';
            $nama = $alat->ruangan->nama_ruangan ?? '';
            $namaRuangan = trim($kode . ' - ' . $nama, ' -');
            if ($namaRuangan === '') $namaRuangan = '-';
        }

        $masaBerlaku = $alat->masa_berlaku
            ? ExcelDate::dateTimeToExcel($alat->masa_berlaku)
            : '-';

        return [
            $this->rowNumber,
            $this->safeText($alat->nama_alat),
            $this->safeText($alat->kelas),
            $this->safeText($alat->merk),
            $this->safeText($alat->tipe),
            $this->safeText($alat->no_seri),
            $this->safeText($alat->no_identitas),
            $this->safeText($alat->kapasitas),
            $this->safeText($alat->daya_baca),
            (int) ($alat->jumlah ?? 0),
            $this->safeText($alat->kategori->nama ?? null),
            $this->safeText($namaRuangan),
            $masaBerlaku,
            $statusLabels[$alat->status] ?? '-',
            $alat->status_hari !== null ? (int) $alat->status_hari : '-',
            $this->safeText($alat->no_sertifikat),
            $this->safeText($alat->kondisi_summary),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Column Width & Format
    |--------------------------------------------------------------------------
    */

    public function columnWidths(): array
    {
        return [
            'A' => 7, 'B' => 30, 'C' => 12, 'D' => 17, 'E' => 19,
            'F' => 19, 'G' => 21, 'H' => 17, 'I' => 14, 'J' => 12,
            'K' => 21, 'L' => 28, 'M' => 22, 'N' => 21, 'O' => 13,
            'P' => 22, 'Q' => 37,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'J' => '#,##0',
            'M' => 'dd mmmm yyyy',
            'O' => '0;[Red]-0;-',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Information
    |--------------------------------------------------------------------------
    */

    private function filterDescription(): string
    {
        $filters = [];

        if (!empty($this->filters['search'])) {
            $filters[] = 'Pencarian: ' . $this->filters['search'];
        }

        if (!empty($this->filters['kategori_id'])) {
            $kategori = \App\Models\Kategori::find($this->filters['kategori_id']);
            $filters[] = 'Kategori: ' . ($kategori ? $kategori->nama : 'Terfilter');
        }

        if (!empty($this->filters['ruangan_id'])) {
            $ruangan = \App\Models\Ruangan::find($this->filters['ruangan_id']);
            $filters[] = 'Ruangan: ' . ($ruangan ? $ruangan->kode_ruangan . ' - ' . $ruangan->nama_ruangan : 'Terfilter');
        }

        $statusLabels = [
            'active'  => 'Aktif',
            'warning' => 'Segera Kalibrasi',
            'expired' => 'Kedaluwarsa',
            'unknown' => 'Belum Terjadwal',
        ];

        $status = $this->filters['status_kalibrasi'] ?? null;
        if (isset($statusLabels[$status])) {
            $filters[] = 'Status: ' . $statusLabels[$status];
        }

        return empty($filters)
            ? 'Filter: Seluruh Data'
            : 'Filter Aktif: ' . implode(' | ', $filters);
    }

    /*
    |--------------------------------------------------------------------------
    | Excel Styling
    |--------------------------------------------------------------------------
    */

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(8, $sheet->getHighestRow());

                // ── Report Header ──
                $sheet->mergeCells('A1:Q1');
                $sheet->mergeCells('A2:Q2');
                $sheet->mergeCells('A3:Q3');
                $sheet->mergeCells('A4:Q4');

                $sheet->setCellValue('A1', 'LAPORAN DATA INVENTARIS ALAT');
                $sheet->setCellValue('A2', 'SISTEM INFORMASI MANAJEMEN ALAT LABORATORIUM');
                $sheet->setCellValue('A3', 'Dicetak pada: ' . now()->format('d F Y, H:i') . ' WIB');
                $sheet->setCellValue('A4', $this->filterDescription());

                // ── Summary Data ──
                $baseQuery = $this->filteredQuery();
                $totalData = (clone $baseQuery)->count();
                $totalUnit = (clone $baseQuery)->sum('jumlah');
                $totalKategori = (clone $baseQuery)->whereNotNull('kategori_id')->distinct()->count('kategori_id');
                $totalRuangan = (clone $baseQuery)->whereNotNull('ruangan_id')->distinct()->count('ruangan_id');

                $cards = [
                    ['range' => 'A6:D6', 'valueRange' => 'A7:D7', 'cell' => 'A6', 'valueCell' => 'A7', 'label' => 'TOTAL DATA ALAT', 'value' => number_format($totalData, 0, ',', '.')],
                    ['range' => 'E6:H6', 'valueRange' => 'E7:H7', 'cell' => 'E6', 'valueCell' => 'E7', 'label' => 'TOTAL UNIT', 'value' => number_format($totalUnit, 0, ',', '.')],
                    ['range' => 'I6:L6', 'valueRange' => 'I7:L7', 'cell' => 'I6', 'valueCell' => 'I7', 'label' => 'JUMLAH KATEGORI', 'value' => number_format($totalKategori, 0, ',', '.')],
                    ['range' => 'M6:Q6', 'valueRange' => 'M7:Q7', 'cell' => 'M6', 'valueCell' => 'M7', 'label' => 'JUMLAH RUANGAN', 'value' => number_format($totalRuangan, 0, ',', '.')],
                ];

                foreach ($cards as $card) {
                    $sheet->mergeCells($card['range']);
                    $sheet->mergeCells($card['valueRange']);
                    $sheet->setCellValue($card['cell'], $card['label']);
                    $sheet->setCellValue($card['valueCell'], $card['value']);

                    $sheet->getStyle($card['range'])->applyFromArray([
                        'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '52647A']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF0F6']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);

                    $sheet->getStyle($card['valueRange'])->applyFromArray([
                        'font' => ['bold' => true, 'size' => 17, 'color' => ['rgb' => '17365D']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F4F7FA']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D8E1EA']]],
                    ]);
                }

                // ── General Font ──
                $sheet->getStyle("A1:Q{$lastRow}")->getFont()->setName('Arial')->setSize(10);

                // ── Title Styling ──
                $sheet->getStyle('A1:Q1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 19, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '17365D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle('A2:Q2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '35516E']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle('A3:Q3')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '718096'], 'italic' => true],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle('A4:Q4')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '35516E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F5FA']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                ]);

                // ── Table Heading ──
                $sheet->getStyle('A8:Q8')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '24476B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5DEE8']]],
                ]);

                // ── Table Body ──
                if ($lastRow >= 9) {
                    $sheet->getStyle("A9:Q{$lastRow}")->applyFromArray([
                        'font' => ['size' => 9, 'color' => ['rgb' => '283747']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'DCE3EA']]],
                    ]);

                    foreach (['A', 'J', 'N', 'O'] as $column) {
                        $sheet->getStyle("{$column}9:{$column}{$lastRow}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    for ($row = 9; $row <= $lastRow; $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(32);

                        // Pewarnaan status kalibrasi
                        $status = $sheet->getCell("N{$row}")->getValue();
                        $statusStyle = match ($status) {
                            'Aktif' => ['bg' => 'E7F5ED', 'text' => '16794A'],
                            'Segera Kalibrasi' => ['bg' => 'FFF4D6', 'text' => 'A66A00'],
                            'Kedaluwarsa' => ['bg' => 'FDECEC', 'text' => 'C53030'],
                            'Belum Terjadwal' => ['bg' => 'EEF1F5', 'text' => '64748B'],
                            default => null,
                        };

                        if ($statusStyle) {
                            $sheet->getStyle("N{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $statusStyle['text']]],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusStyle['bg']]],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                            ]);
                        }

                        // Sisa hari negatif merah
                        $sisaHari = $sheet->getCell("O{$row}")->getValue();
                        if (is_numeric($sisaHari) && $sisaHari < 0) {
                            $sheet->getStyle("O{$row}")->getFont()->getColor()->setRGB('C53030');
                            $sheet->getStyle("O{$row}")->getFont()->setBold(true);
                        }
                    }
                }

                // ── Row Heights ──
                $sheet->getRowDimension(1)->setRowHeight(34);
                $sheet->getRowDimension(2)->setRowHeight(21);
                $sheet->getRowDimension(3)->setRowHeight(18);
                $sheet->getRowDimension(4)->setRowHeight(24);
                $sheet->getRowDimension(5)->setRowHeight(9);
                $sheet->getRowDimension(6)->setRowHeight(21);
                $sheet->getRowDimension(7)->setRowHeight(34);
                $sheet->getRowDimension(8)->setRowHeight(38);

                // ── Filter, Freeze & Page Setup ──
                $sheet->setAutoFilter("A8:Q{$lastRow}");
                $sheet->freezePane('A9');
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(8, 8);
                $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
                $sheet->getHeaderFooter()->setOddFooter('&L SISMALAT&C Halaman &P dari &N&R Dokumen Internal');
            },
        ];
    }
}
