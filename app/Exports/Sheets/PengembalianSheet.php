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

class PengembalianSheet implements FromArray, WithHeadings, WithColumnWidths, WithEvents, WithTitle
{
    protected $batches;
    protected $pengembalianMap;

    public function __construct($batches, $pengembalianMap)
    {
        $this->batches = $batches;
        $this->pengembalianMap = $pengembalianMap;
    }

    public function title(): string
    {
        return 'Pengembalian';
    }

    public function headings(): array
    {
        return [
            'NO', 'KODE TRANSAKSI', 'TANGGAL PINJAM', 'NAMA PEMINJAM', 'BAGIAN / INSTANSI',
            'NAMA ALAT', 'MERK', 'NO SERI', 'JUMLAH', 'SATUAN',
            'TANGGAL PENGEMBALIAN', 'LAMA PINJAM AKTUAL (HARI)', 'JATUH TEMPO',
            'KETERLAMBATAN', 'CATATAN PENGEMBALIAN', 'DITERIMA OLEH', 'KODE PENGEMBALIAN',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6, 'B' => 14, 'C' => 16, 'D' => 24, 'E' => 22,
            'F' => 26, 'G' => 16, 'H' => 16, 'I' => 9, 'J' => 9,
            'K' => 18, 'L' => 14, 'M' => 13, 'N' => 18, 'O' => 34,
            'P' => 20, 'Q' => 14,
        ];
    }

    public function array(): array
    {
        $rows = [];
        $no = 0;

        foreach ($this->batches as $b) {
            $bagian = $b->bagian?->nama ?? $b->instansi_peminjam ?? '-';

            foreach ($b->items as $item) {
                if ($item->status !== 'dikembalikan' || !$item->waktu_pengembalian) {
                    continue;
                }

                $no++;
                $alat = $item->alat;
                $bukti = $this->pengembalianMap->get($item->id);

                // Lama pinjam aktual
                $lamaAktual = (int) max(
                    0,
                    $b->waktu_pengambilan
                        ? $b->waktu_pengambilan->startOfDay()->diffInDays($item->waktu_pengembalian->startOfDay(), false)
                        : 0
                );

                // Keterlambatan
                $keterlambatan = 'Tepat Waktu';
                if ($b->tanggal_jatuh_tempo) {
                    $terlambatHari = (int) $b->tanggal_jatuh_tempo
                        ->startOfDay()
                        ->diffInDays($item->waktu_pengembalian->startOfDay(), false);
                    if ($terlambatHari > 0) {
                        $keterlambatan = 'Terlambat ' . $terlambatHari . ' hari';
                    }
                }

                $rows[] = [
                    $no,
                    strtoupper($b->hashid),
                    $b->waktu_pengambilan?->format('d/m/Y H:i') ?? '-',
                    $b->nama_peminjam ?: ($b->user?->name ?? '-'),
                    $bagian,
                    $alat?->nama_alat ?? '-',
                    $alat?->merk ?? '-',
                    $alat?->no_seri ?? '-',
                    (int) $item->jumlah,
                    $item->satuan ?: 'unit',
                    $item->waktu_pengembalian->format('d/m/Y H:i'),
                    $lamaAktual,
                    $b->tanggal_jatuh_tempo?->format('d/m/Y') ?? '-',
                    $keterlambatan,
                    $item->catatan_pengembalian ?: ($bukti?->keterangan ?? '-'),
                    $bukti?->user?->name ?? '-',
                    $bukti ? strtoupper($bukti->hashid) : '-',
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
                $lastCol = 'Q';

                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E79']],
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

                    foreach (['A', 'I', 'J', 'K', 'L', 'M', 'N'] as $col) {
                        $sheet->getStyle("{$col}2:{$col}{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    }

                    for ($row = 2; $row <= $lastRow; $row++) {
                        $value = $sheet->getCell("N{$row}")->getValue();
                        if (is_string($value) && str_starts_with($value, 'Terlambat')) {
                            $sheet->getStyle("N{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => 'C53030']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDECEC']],
                            ]);
                        } elseif ($value === 'Tepat Waktu') {
                            $sheet->getStyle("N{$row}")->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['rgb' => '16794A']],
                                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7F5ED']],
                            ]);
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
