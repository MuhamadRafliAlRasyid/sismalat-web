<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksSchemaColumns;
use App\Models\Alat;
use App\Models\PengambilanAlat;
use App\Models\PengambilanAlatItem;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    use ChecksSchemaColumns;

    public function index()
    {
        /* =========================================================
         * INVENTARIS ALAT
         * ======================================================= */
        $qTotal = Alat::query();
        if ($this->hasCol('alats', 'deleted_at')) {
            $qTotal->whereNull('deleted_at');
        }
        $totalAlat = $qTotal->count();

        $qStok = Alat::query();
        if ($this->hasCol('alats', 'deleted_at')) {
            $qStok->whereNull('deleted_at');
        }
        $stokTersedia = $this->hasCol('alats', 'jumlah')
            ? (int) $qStok->sum('jumlah')
            : 0;

        $alatPerluKalibrasi = 0;
        if ($this->hasCol('alats', 'status')) {
            $qKalibrasi = Alat::query();
            if ($this->hasCol('alats', 'deleted_at')) {
                $qKalibrasi->whereNull('deleted_at');
            }
            $alatPerluKalibrasi = $qKalibrasi
                ->whereIn('status', ['warning', 'expired'])
                ->count();
        }

        /* =========================================================
         * RINGKASAN BATCH PEMINJAMAN
         * ======================================================= */
        $totalBatch     = PengambilanAlat::count();
        $batchAktif     = PengambilanAlat::aktif()->count();
        $batchTerlambat = PengambilanAlat::terlambat()->count();
        $batchWarning   = PengambilanAlat::mendekatiJatuhTempo(3)->count();
        $batchSelesai   = PengambilanAlat::selesai()->count();

        /* =========================================================
         * RINGKASAN UNIT
         * ======================================================= */
        $unitDipinjam     = (int) PengambilanAlatItem::where('status', 'dipinjam')->sum('jumlah');
        $unitDikembalikan = (int) PengambilanAlatItem::where('status', 'dikembalikan')->sum('jumlah');
        $jenisDipinjam    = PengambilanAlatItem::where('status', 'dipinjam')
            ->distinct('alat_id')
            ->count('alat_id');

        // Alias agar view/fitur lama yang mungkin memakai variabel ini tetap kompatibel.
        $totalDipinjam     = $unitDipinjam;
        $totalDikembalikan = $unitDikembalikan;
        $totalPengambilan  = $totalBatch;
        $totalSelesai      = $batchSelesai;

        $totalUnitTercatat = $unitDipinjam + $unitDikembalikan;
        $rasioPengembalian = $totalUnitTercatat > 0
            ? round(($unitDikembalikan / $totalUnitTercatat) * 100)
            : 0;

        /* =========================================================
         * TOP ALAT
         * ======================================================= */
        $alatTerpopuler = PengambilanAlatItem::query()
            ->select('alat_id', DB::raw('SUM(jumlah) as total_pinjam'))
            ->with('alat')
            ->groupBy('alat_id')
            ->orderByDesc('total_pinjam')
            ->limit(5)
            ->get();
        $topAlatLabels = $alatTerpopuler
            ->map(fn ($row) => $row->alat ? $row->alat->nama_alat : 'Alat dihapus')
            ->values();
        $topAlatValues = $alatTerpopuler
            ->pluck('total_pinjam')
            ->map(fn ($value) => (int) $value)
            ->values();

        /* =========================================================
         * DATA KANBAN
         * Batasi data aktif yang diambil agar dashboard tetap ringan.
         * Kategori ditentukan dari accessor sisa_hari pada model.
         * ======================================================= */
        $kanbanOpen = PengambilanAlat::aktif()
            ->with(['user', 'bagian', 'items.alat'])
            ->latest('waktu_pengambilan')
            ->limit(150)
            ->get();

        $kanbanColumns = [
            'terlambat' => $kanbanOpen->filter(function ($batch) {
                return $batch->sisa_hari !== null && (int) $batch->sisa_hari < 0;
            })->take(8)->values(),

            'warning' => $kanbanOpen->filter(function ($batch) {
                return $batch->sisa_hari !== null
                    && (int) $batch->sisa_hari >= 0
                    && (int) $batch->sisa_hari <= 3;
            })->take(8)->values(),

            'aktif' => $kanbanOpen->filter(function ($batch) {
                return $batch->sisa_hari === null || (int) $batch->sisa_hari > 3;
            })->take(8)->values(),

            'selesai' => PengambilanAlat::selesai()
                ->with(['user', 'bagian', 'items.alat'])
                ->latest('waktu_pengambilan')
                ->limit(8)
                ->get(),
        ];

        $kanbanCounts = [
            'terlambat' => $kanbanOpen->filter(fn ($batch) => $batch->sisa_hari !== null && (int) $batch->sisa_hari < 0)->count(),
            'warning' => $kanbanOpen->filter(fn ($batch) => $batch->sisa_hari !== null && (int) $batch->sisa_hari >= 0 && (int) $batch->sisa_hari <= 3)->count(),
            'aktif' => $kanbanOpen->filter(fn ($batch) => $batch->sisa_hari === null || (int) $batch->sisa_hari > 3)->count(),
            'selesai' => $batchSelesai,
        ];

        /* =========================================================
         * AKTIVITAS TERBARU & ALERT
         * ======================================================= */
        $recentBatches = PengambilanAlat::with(['user', 'bagian', 'items.alat'])
            ->latest('waktu_pengambilan')
            ->limit(6)
            ->get();

        $perhatian = PengambilanAlat::with(['items.alat', 'user', 'bagian'])
            ->aktif()
            ->whereNotNull('lama_pinjam')
            ->get()
            ->filter(fn ($p) => $p->sisa_hari !== null && (int) $p->sisa_hari <= 3)
            ->sortBy('sisa_hari')
            ->values();

        /* =========================================================
         * SPC C-CHART: JUMLAH BATCH PEMINJAMAN PER HARI
         * Menggunakan 30 hari kalender, termasuk hari ini.
         * Batas kendali c-chart: c-bar +/- 3 * sqrt(c-bar).
         * ======================================================= */
        $spcEnd = now()->endOfDay();
        $spcStart = $spcEnd->copy()->subDays(29)->startOfDay();

        $spcRows = PengambilanAlat::query()
            ->whereBetween('waktu_pengambilan', [$spcStart, $spcEnd])
            ->selectRaw('DATE(waktu_pengambilan) as tanggal, COUNT(*) as total')
            ->groupByRaw('DATE(waktu_pengambilan)')
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $spcLabels = [];
        $spcValues = [];

        for ($date = $spcStart->copy(); $date->lte($spcEnd); $date->addDay()) {
            $tanggal = $date->toDateString();
            $spcLabels[] = $date->format('d M');
            $spcValues[] = (int) ($spcRows->get($tanggal)->total ?? 0);
        }

        $jumlahHariSpc = count($spcValues);
        $spcMean = $jumlahHariSpc > 0 ? array_sum($spcValues) / $jumlahHariSpc : 0;
        $spcDistance = 3 * sqrt(max($spcMean, 0));
        $spcUcl = $spcMean + $spcDistance;
        $spcLcl = max(0, $spcMean - $spcDistance);
        $spcOutOfControl = collect($spcValues)
            ->filter(fn ($value) => $value > $spcUcl || $value < $spcLcl)
            ->count();

        $spcSummary = [
            'mean' => round($spcMean, 2),
            'ucl' => round($spcUcl, 2),
            'lcl' => round($spcLcl, 2),
            'out_of_control' => $spcOutOfControl,
            'period_days' => $jumlahHariSpc,
        ];

        return view('admin.dashboard', compact(
            'totalAlat',
            'alatPerluKalibrasi',
            'stokTersedia',
            'totalBatch',
            'batchAktif',
            'batchTerlambat',
            'batchWarning',
            'batchSelesai',
            'unitDipinjam',
            'unitDikembalikan',
            'jenisDipinjam',
            'totalDipinjam',
            'totalDikembalikan',
            'totalPengambilan',
            'totalSelesai',
            'rasioPengembalian',
            'alatTerpopuler',
            'topAlatLabels',
            'topAlatValues',
            'kanbanColumns',
            'kanbanCounts',
            'recentBatches',
            'perhatian',
            'spcLabels',
            'spcValues',
            'spcSummary'
        ));
    }
}
