<?php

namespace App\Exports;

use App\Exports\Sheets\PeminjamanSheet;
use App\Exports\Sheets\PengembalianSheet;
use App\Exports\Sheets\RingkasanSheet;
use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanPinjamKembaliExport implements WithMultipleSheets
{
    protected array $filters;
    protected $batches;
    protected $returnedItems;
    protected $pengembalianMap;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->loadData();
    }

    /**
     * Satu kali query untuk semua sheet (hemat memori & cepat).
     */
    protected function loadData(): void
    {
        $isAdmin = Auth::check() && Auth::user()->hasRole(['admin', 'super']);

        $query = PengambilanAlat::with(['user', 'bagian', 'approver', 'items.alat']);

        // Non-admin hanya boleh export data miliknya sendiri
        if (Auth::check() && !$isAdmin) {
            $query->where('user_id', Auth::id());
        }

        // ── Filter status ──
        $status = $this->filters['status'] ?? null;
        if ($status === 'dipinjam') {
            $query->whereIn('status', ['dipinjam', 'sebagian_dikembalikan']);
        } elseif ($status === 'dikembalikan') {
            $query->where('status', 'dikembalikan');
        } elseif ($status === 'terlambat') {
            $query->terlambat();
        }

        // ── Filter rentang tanggal ──
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('waktu_pengambilan', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('waktu_pengambilan', '<=', $this->filters['date_to']);
        }

        // ── Pencarian ──
        if (!empty($this->filters['search'])) {
            $search = trim($this->filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('nama_peminjam', 'like', "%{$search}%")
                    ->orWhere('keperluan', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('bagian', fn ($bq) => $bq->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('items.alat', function ($aq) use ($search) {
                        $aq->where('nama_alat', 'like', "%{$search}%")
                            ->orWhere('merk', 'like', "%{$search}%")
                            ->orWhere('no_seri', 'like', "%{$search}%");
                    });
            });
        }

        $this->batches = $query->orderByDesc('waktu_pengambilan')->get();

        // Item yang sudah dikembalikan (sumber sheet Pengembalian)
        $this->returnedItems = $this->batches
            ->flatMap(fn ($b) => $b->items)
            ->filter(fn ($i) => $i->status === 'dikembalikan')
            ->values();

        // Map bukti pengembalian (petugas penerima + keterangan)
        $ids = $this->returnedItems->pluck('id')->all();
        $this->pengembalianMap = $ids
            ? PengembalianAlat::whereIn('pengambilan_alat_item_id', $ids)
                ->with('user')
                ->get()
                ->keyBy('pengambilan_alat_item_id')
            : collect();
    }

    public function sheets(): array
    {
        return [
            new RingkasanSheet($this->filters, $this->batches, $this->returnedItems),
            new PeminjamanSheet($this->batches),
            new PengembalianSheet($this->batches, $this->pengembalianMap),
        ];
    }
}
