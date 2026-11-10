<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class KaryawanDashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        /*
         * Base query selalu dibatasi ke user yang sedang login.
         * Relasi `alat` mengikuti struktur yang digunakan dashboard karyawan saat ini.
         */
        $basePeminjaman = PengambilanAlat::query()
            ->with('alat')
            ->where('user_id', $userId);

        $totalDipinjam = (clone $basePeminjaman)
            ->where('status', 'dipinjam')
            ->count();

        $totalDikembalikan = PengembalianAlat::query()
            ->where('user_id', $userId)
            ->count();

        $totalRiwayat = (clone $basePeminjaman)->count();

        $alatDipinjam = (clone $basePeminjaman)
            ->where('status', 'dipinjam')
            ->latest()
            ->get();

        $pengambilanTerbaru = (clone $basePeminjaman)
            ->latest()
            ->limit(5)
            ->get();

        /* ═══════ INVENTARIS YANG DAPAT DIPILIH ═══════ */
        $hasJumlahColumn = Schema::hasColumn('alats', 'jumlah');
        $alatQuery = Alat::query();

        if (Schema::hasColumn('alats', 'deleted_at')) {
            $alatQuery->whereNull('deleted_at');
        }

        if ($hasJumlahColumn) {
            $alatQuery->where('jumlah', '>', 0);
            $totalStokTersedia = (int) (clone $alatQuery)->sum('jumlah');
        } else {
            $totalStokTersedia = 0;
        }

        $totalAlatTersedia = (clone $alatQuery)->count();
        $alatTersedia = (clone $alatQuery)
            ->orderBy('nama_alat')
            ->get();

        /* ═══════ FILTER DAN RIWAYAT PEMINJAMAN ═══════ */
        $statusFilter = $request->query('status');
        $keyword = trim((string) $request->query('q', ''));

        $riwayatQuery = (clone $basePeminjaman);

        if (in_array($statusFilter, ['dipinjam', 'dikembalikan'], true)) {
            $riwayatQuery->where('status', $statusFilter);
        }

        if ($keyword !== '') {
            $riwayatQuery->whereHas('alat', function ($query) use ($keyword) {
                $query->where('nama_alat', 'like', '%' . $keyword . '%');
            });
        }

        $riwayatPeminjaman = $riwayatQuery
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('karyawan.dashboard', compact(
            'totalDipinjam',
            'totalDikembalikan',
            'totalRiwayat',
            'pengambilanTerbaru',
            'alatDipinjam',
            'alatTersedia',
            'totalAlatTersedia',
            'totalStokTersedia',
            'hasJumlahColumn',
            'riwayatPeminjaman',
            'statusFilter',
            'keyword'
        ));
    }
}
