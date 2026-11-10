<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class RingkasanSheet implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    protected array $filters;
    protected $batches;
    protected $returnedItems;

    public function __construct(array $filters, $batches, $returnedItems)
    {
        $this->filters = $filters;
        $this->batches = $batches;
        $this->returnedItems = $returnedItems;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function columnWidths(): array
    {
        return ['A' => 44, 'B' => 18, 'C' => 18, 'D' => 18, 'E' => 18];
    }

    protected function filterDescription(): string
    {
        $f = [];

        if (!empty($this->filters['search'])) {
            $f[] = 'Pencarian: ' . $this->filters['search'];
        }

        $labels = [
            'dipinjam' => 'Sedang Dipinjam',
            'dikembalikan' => 'Selesai',
            'terlambat' => 'Terlambat',
        ];
        if (!empty($this->filters['status']) && isset($labels[$this->filters['status']])) {
            $f[] = 'Status: ' . $labels[$this->filters['status']];
        }
        if (!empty($this->filters['date_from'])) {
            $f[] = 'Dari: ' . $this->filters['date_from'];
        }
        if (!empty($this->filters['date_to'])) {
            $f[] = 'Sampai: ' . $this->filters['date_to'];
        }

        return empty($f) ? 'Filter: Seluruh Data' : 'Filter Aktif: ' . implode(' | ', $f);
    }

    public function array(): array
    {
        $b = $this->batches;
        $r = $this->returnedItems;

        $allItems     = $b->flatMap(fn ($x) => $x->items);
        $totalTrans   = $b->count();
        $unitPinjam   = $allItems->sum('jumlah');
        $aktif        = $b->filter(fn ($x) => $x->isAktif())->count();
        $selesai      = $b->filter(fn ($x) => $x->isSelesai())->count();
        $terlambat    = $b->filter(fn ($x) => $x->isTerlambat())->count();
        $totalKembali = $r->count();
        $unitKembali  = $r->sum('jumlah');
        $rate         = $unitPinjam > 0 ? round(($unitKembali / $unitPinjam) * 100, 1) : 0;

        $rows = [];
        $rows[] = ['LAPORAN PEMINJAMAN & PENGEMBALIAN ALAT'];
        $rows[] = ['SISTEM INFORMASI MANAJEMEN ALAT LABORATORIUM (SISMALAT)'];
        $rows[] = ['Dicetak pada: ' . now()->format('d F Y, H:i') . ' WIB'];
        $rows[] = [$this->filterDescription()];
        $rows[] = [''];

        // ── A. Ringkasan umum ──
        $rows[] = ['A. RINGKASAN UMUM'];
        $rows[] = ['METRIK', 'NILAI'];
        $rows[] = ['Total Transaksi Peminjaman', $totalTrans];
        $rows[] = ['Total Unit Alat Dipinjam', $unitPinjam];
        $rows[] = ['Transaksi Masih Aktif', $aktif];
        $rows[] = ['Transaksi Selesai', $selesai];
        $rows[] = ['Transaksi Terlambat', $terlambat];
        $rows[] = ['Total Item Dikembalikan', $totalKembali];
        $rows[] = ['Total Unit Dikembalikan', $unitKembali];
        $rows[] = ['Tingkat Pengembalian', $rate . '%'];
        $rows[] = [''];

        // ── B. Rincian per status ──
        $rows[] = ['B. RINCIAN PER STATUS TRANSAKSI'];
        $rows[] = ['STATUS', 'TRANSAKSI', 'UNIT DIPINJAM', 'UNIT DIKEMBALIKAN'];

        $statusGroups = [
            'Dipinjam'              => $b->filter(fn ($x) => $x->status === 'dipinjam'),
            'Sebagian Dikembalikan' => $b->filter(fn ($x) => $x->status === 'sebagian_dikembalikan'),
            'Selesai'               => $b->filter(fn ($x) => $x->status === 'dikembalikan'),
        ];

        foreach ($statusGroups as $label => $group) {
            $rows[] = [
                $label,
                $group->count(),
                $group->flatMap(fn ($x) => $x->items)->sum('jumlah'),
                $group->flatMap(fn ($x) => $x->items)->filter(fn ($i) => $i->status === 'dikembalikan')->sum('jumlah'),
            ];
        }
        $rows[] = ['TOTAL', $totalTrans, $unitPinjam, $unitKembali];
        $rows[] = [''];

        // ── C. Rekap 6 bulan terakhir ──
        $rows[] = ['C. REKAP 6 BULAN TERAKHIR'];
        $rows[] = ['BULAN', 'TRANSAKSI PINJAM', 'UNIT DIPINJAM', 'ITEM KEMBALI', 'UNIT KEMBALI'];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $monthBatches = $b->filter(fn ($x) =>
                $x->waktu_pengambilan && $x->waktu_pengambilan->betweenIncluded($month, $monthEnd)
            );
            $monthReturns = $r->filter(fn ($x) =>
                $x->waktu_pengembalian && $x->waktu_pengembalian->betweenIncluded($month, $monthEnd)
            );

            $rows[] = [
                $month->locale('id')->translatedFormat('F Y'),
                $monthBatches->count(),
                $monthBatches->flatMap(fn ($x) => $x->items)->sum('jumlah'),
                $monthReturns->count(),
                $monthReturns->sum('jumlah'),
            ];
        }
        $rows[] = [''];

        // ── D. Top 5 alat ──
        $rows[] = ['D. TOP 5 ALAT PALING SERING DIPINJAM'];
        $rows[] = ['NAMA ALAT', 'FREKUENSI PINJAM', 'TOTAL UNIT'];

        $top = $allItems
            ->groupBy(fn ($i) => $i->alat?->nama_alat ?? '(Alat terhapus)')
            ->map(fn ($group, $nama) => [
                'nama' => $nama,
                'freq' => $group->count(),
                'unit' => $group->sum('jumlah'),
            ])
            ->sortByDesc('freq')
            ->take(5);

        foreach ($top as $t) {
            $rows[] = [$t['nama'], $t['freq'], $t['unit']];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(1, $sheet->getHighestRow());

                // Judul
                foreach ([1, 2, 3, 4] as $r) {
                    $sheet->mergeCells("A{$r}:E{$r}");
                }
                $sheet->getStyle('A1:E1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '17365D']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getStyle('A2:E2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '35516E']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('A3:E3')->applyFromArray([
                    'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '718096']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getStyle('A4:E4')->applyFromArray([
                    'font' => ['size' => 9, 'color' => ['rgb' => '35516E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F5FA']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(30);

                // Section header (baris 6, 17, 24, 33)
                foreach ([6, 17, 24, 33] as $r) {
                    if ($r <= $lastRow) {
                        $sheet->mergeCells("A{$r}:E{$r}");
                        $sheet->getStyle("A{$r}:E{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '24476B']],
                        ]);
                    }
                }

                // Table header rows (7, 18, 25, 34)
                foreach ([7, 18, 25, 34] as $r) {
                    if ($r <= $lastRow) {
                        $sheet->getStyle("A{$r}:E{$r}")->applyFromArray([
                            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '17365D']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EAF0F6']],
                            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5DEE8']]],
                        ]);
                    }
                }

                // Font umum + border tipis untuk area tabel
                $sheet->getStyle("A1:E{$lastRow}")->getFont()->setName('Arial')->setSize(10);

                // Page setup
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
