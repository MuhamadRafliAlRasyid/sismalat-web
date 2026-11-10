<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class PeminjamanSheet implements FromArray, WithHeadings, WithColumnWidths, WithEvents, WithTitle
{
    protected $batches;

    public function __construct($batches)
    {
        $this->batches = $batches;
    }

    public function title(): string
    {
        return 'Peminjaman';
    }

    public function headings(): array
    {
        return [
            'NO', 'KODE TRANSAKSI', 'TANGGAL PINJAM', 'NAMA PEMINJAM', 'TIPE PEMINJAM',
            'BAGIAN / INSTANSI', 'AKUN PENGGUNA', 'NAMA ALAT', 'MERK', 'TIPE ALAT',
            'NO SERI', 'JUMLAH', 'SATUAN', 'STATUS ITEM', 'KEPERLUAN',
            'LAMA PINJAM (HARI)', 'JATUH TEMPO', 'SISA HARI', 'STATUS TRANSAKSI',
            'DISETUJUI OLEH', 'TANGGAL PERSETUJUAN',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6, 'B' => 14, 'C' => 16, 'D' => 24, 'E' => 14,
            'F' => 22, 'G' => 20, 'H' => 26, 'I' => 16, 'J' => 16,
            'K' => 16, 'L' => 9, 'M' => 9, 'N' => 14, 'O' => 34,
            'P' => 12, 'Q' => 13, 'R' => 16, 'S' => 20, 'T' => 20, 'U' => 17,
        ];
    }

    public function array(): array
    {
        $rows = [];
        $no = 0;

        foreach ($this->batches as $b) {
            $bagian = $b->bagian?->nama ?? $b->instansi_peminjam ?? '-';

            foreach ($b->items as $item) {
                $no++;
                $alat = $item->alat;

                $rows[] = [
                    $no,
                    strtoupper($b->hashid),
                    $b->waktu_pengambilan?->format('d/m/Y H:i') ?? '-',
                    $b->nama_peminjam ?: ($b->user?->name ?? '-'),
                    $b->tipe_peminjam ?: '-',
                    $bagian,
                    $b->user?->name ?? '-',
                    $alat?->nama_alat ?? '-',
                    $alat?->merk ?? '-',
                    $alat?->tipe ?? '-',
                    $alat?->no_seri ?? '-',
                    (int) $item->jumlah,
                    $item->satuan ?: 'unit',
                    $item->status_label,
                    $b->keperluan,
                    (int) $b->lama_pinjam,
                    $b->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-',
                    $item->isDikembalikan() ? 'Selesai' : $b->sisa_hari_label,
                    $b->status_label,
                    $b->approved_by_name ?: '-',
                    $b->approved_at?->format('d/m/Y H:i') ?? '-',
                ];
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(2, $sheet->getHighestRow());
                $lastCol = 'U';

                // Header tabel
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F6F43']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5DEE8']]],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(34);

                if ($lastRow >= 2) {
                    $sheet->getStyle("A2:{$lastCol}{$lastRow}")->applyFromArray([
                        'font' => ['size' => 9],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['rgb' => 'DCE3EA']]],
                    ]);

                    foreach (['A', 'L', 'M', 'N', 'P', 'Q', 'R'] as $col) {
                        $sheet->getStyle("{$col}2:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    // Pewarnaan status
                    for ($row = 2; $row <= $lastRow; $row++) {
                        $statusItem = $sheet->getCell("N{$row}")->getValue();
                        $styleItem = match ($statusItem) {
                            'Dikembalikan' => ['bg' => 'E7F5ED', 'text' => '16794A'],
                            'Dipinjam'     => ['bg' => 'FFF4D6', 'text' => 'A66A00'],
                            default        => null,
                        };
                        if ($styleItem) {
                            $sheet->getStyle("N{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $styleItem['text']]],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $styleItem['bg']]],
                            ]);
                        }

                        $statusBatch = $sheet->getCell("S{$row}")->getValue();
                        $styleBatch = match ($statusBatch) {
                            'Selesai'               => ['bg' => 'E7F5ED', 'text' => '16794A'],
                            'Sebagian Dikembalikan' => ['bg' => 'FFF4D6', 'text' => 'A66A00'],
                            'Dipinjam'              => ['bg' => 'EAF2FB', 'text' => '1F4E79'],
                            default                 => null,
                        };
                        if ($styleBatch) {
                            $sheet->getStyle("S{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => $styleBatch['text']]],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $styleBatch['bg']]],
                            ]);
                        }

                        // Sisa hari terlambat = merah
                        $sisa = $sheet->getCell("R{$row}")->getValue();
                        if (is_string($sisa) && str_starts_with($sisa, 'Terlambat')) {
                            $sheet->getStyle("R{$row}")->getFont()->setBold(true);
                            $sheet->getStyle("R{$row}")->getFont()->getColor()->setRGB('C53030');
                        }
                    }
                }

                $sheet->freezePane('A2');
                $sheet->setAutoFilter("A1:{$lastCol}{$lastRow}");
                $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
            },
        ];
    }
}
