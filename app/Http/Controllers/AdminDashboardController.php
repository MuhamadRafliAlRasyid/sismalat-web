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
    /* ═══════ ALAT ═══════ */
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
        $qKal = Alat::query();
        if ($this->hasCol('alats', 'deleted_at')) {
            $qKal->whereNull('deleted_at');
        }
        $alatPerluKalibrasi = $qKal->whereIn('status', ['warning', 'expired'])->count();
    }

    /* ═══════ BATCH PEMINJAMAN ═══════ */
    $totalBatch     = PengambilanAlat::count();
    $batchAktif     = PengambilanAlat::aktif()->count();
    $batchTerlambat = PengambilanAlat::terlambat()->count();
    $batchWarning   = PengambilanAlat::mendekatiJatuhTempo(3)->count();
    $batchSelesai   = PengambilanAlat::selesai()->count();

    /* ═══════ ITEM / UNIT ═══════ */
    $unitDipinjam     = (int) PengambilanAlatItem::where('status', 'dipinjam')->sum('jumlah');
    $unitDikembalikan = (int) PengambilanAlatItem::where('status', 'dikembalikan')->sum('jumlah');
    $jenisDipinjam    = PengambilanAlatItem::where('status', 'dipinjam')->distinct('alat_id')->count('alat_id');

    /* ═══════ ALIAS UNTUK VIEW LAMA ═══════ */
    $totalDipinjam     = $unitDipinjam;
    $totalDikembalikan = $unitDikembalikan;
    $totalPengambilan  = $totalBatch;
    $totalSelesai      = $batchSelesai;

    /* ═══════ TOP ALAT ═══════ */
    $alatTerpopuler = PengambilanAlatItem::query()
        ->select('alat_id', DB::raw('SUM(jumlah) as total_pinjam'))
        ->with('alat')
        ->groupBy('alat_id')
        ->orderByDesc('total_pinjam')
        ->limit(5)
        ->get();

    /* ═══════ RECENT ═══════ */
    $recentBatches = PengambilanAlat::with(['user', 'bagian', 'items.alat'])
        ->latest('waktu_pengambilan')
        ->limit(5)
        ->get();

    $perhatian = PengambilanAlat::with(['items.alat', 'user'])
        ->aktif()
        ->whereNotNull('lama_pinjam')
        ->get()
        ->filter(fn ($p) => $p->sisa_hari !== null && $p->sisa_hari <= 3)
        ->sortBy('sisa_hari')
        ->values();

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

        'alatTerpopuler',
        'recentBatches',
        'perhatian'
    ));
}
}
