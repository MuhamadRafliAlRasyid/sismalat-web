<?php

namespace App\Providers;

use App\Events\SparepartUpdated;
use App\Listeners\SyncToDriveListener;
use App\Models\PengambilanAlat;
use App\Models\PengambilanAlatItem;
use App\Models\User;
use App\Services\HashIdService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Cache variabel fallback dashboard agar tidak dihitung berulang
     * dalam satu request.
     */
    protected ?array $dashboardFallbackVariables = null;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Route model binding user pakai hashid
        |--------------------------------------------------------------------------
        */
        Route::bind('user', function ($value) {
            $id = app(HashIdService::class)->decode($value);

            abort_if(!$id, 404);

            return User::findOrFail($id);
        });

        /*
        |--------------------------------------------------------------------------
        | Fallback variabel dashboard
        |--------------------------------------------------------------------------
        |
        | Ini mencegah error:
        | Undefined variable $totalDikembalikan
        | Undefined variable $totalDipinjam
        | Undefined variable $totalPengambilan
        |
        | Variabel hanya diisi jika view belum memilikinya.
        | Jadi kalau controller sudah mengirim data, data controller tetap dipakai.
        */
        View::composer('*', function ($view) {
            $variables = $this->getDashboardFallbackVariables();

            foreach ($variables as $key => $value) {
                if (!$view->offsetExists($key)) {
                    $view->with($key, $value);
                }
            }
        });
    }

    /**
     * Hitung variabel fallback dashboard sekali per request.
     */
    protected function getDashboardFallbackVariables(): array
    {
        if ($this->dashboardFallbackVariables !== null) {
            return $this->dashboardFallbackVariables;
        }

        $unitDipinjam = 0;
        $unitDikembalikan = 0;
        $jenisDipinjam = 0;

        $totalBatch = 0;
        $batchAktif = 0;
        $batchTerlambat = 0;
        $batchWarning = 0;
        $batchSelesai = 0;

        try {
            /*
            |----------------------------------------------------------------------
            | Statistik unit dari tabel items
            |---------------------------------------------------------------------
            |
            | Setelah refactor multi-alat:
            | - kolom jumlah tidak lagi ada di pengambilan_alats
            | - detail alat/unit ada di pengambilan_alat_items
            */
            if (Schema::hasTable('pengambilan_alat_items')) {
                $unitDipinjam = (int) PengambilanAlatItem::query()
                    ->where('status', 'dipinjam')
                    ->sum('jumlah');

                $unitDikembalikan = (int) PengambilanAlatItem::query()
                    ->where('status', 'dikembalikan')
                    ->sum('jumlah');

                $jenisDipinjam = (int) PengambilanAlatItem::query()
                    ->where('status', 'dipinjam')
                    ->count(DB::raw('DISTINCT alat_id'));
            }

            /*
            |----------------------------------------------------------------------
            | Statistik batch dari tabel header
            |---------------------------------------------------------------------
            */
            if (Schema::hasTable('pengambilan_alats')) {
                $totalBatch = PengambilanAlat::query()->count();
                $batchAktif = PengambilanAlat::query()->aktif()->count();
                $batchTerlambat = PengambilanAlat::query()->terlambat()->count();
                $batchWarning = PengambilanAlat::query()->mendekatiJatuhTempo(3)->count();
                $batchSelesai = PengambilanAlat::query()->selesai()->count();
            }
        } catch (\Throwable $e) {
            /*
            | Jika ada tabel/kolom/scope yang belum cocok, jangan sampai
            | aplikasi crash hanya karena fallback dashboard.
            | Biarkan nilai tetap 0.
            */
            report_if_exists($e);
        }

        /*
        |--------------------------------------------------------------------------
        | Mapping variabel untuk view lama
        |--------------------------------------------------------------------------
        |
        | View dashboard lama mungkin memakai nama variabel:
        | - $totalDipinjam
        | - $totalDikembalikan
        | - $totalPengambilan
        |
        | Sekarang sumber kebenarannya:
        | - unit alat dipinjam/dikembalikan dari pengambilan_alat_items
        | - batch peminjaman dari pengambilan_alats
        */
        return $this->dashboardFallbackVariables = [
            // Item / unit
            'unitDipinjam' => $unitDipinjam,
            'unitDikembalikan' => $unitDikembalikan,
            'jenisDipinjam' => $jenisDipinjam,

            // Alias umum untuk view lama
            'totalDipinjam' => $unitDipinjam,
            'totalDikembalikan' => $unitDikembalikan,
            'totalAlatDipinjam' => $unitDipinjam,
            'totalAlatDikembalikan' => $unitDikembalikan,

            // Batch / transaksi
            'totalBatch' => $totalBatch,
            'totalPengambilan' => $totalBatch,

            'batchAktif' => $batchAktif,
            'totalAktif' => $batchAktif,

            'batchTerlambat' => $batchTerlambat,
            'totalTerlambat' => $batchTerlambat,

            'batchWarning' => $batchWarning,
            'totalWarning' => $batchWarning,

            'batchSelesai' => $batchSelesai,
            'totalSelesai' => $batchSelesai,

            // Variasi nama lain yang mungkin dipakai view
            'totalBatchDikembalikan' => $batchSelesai,
            'totalUnitDikembalikan' => $unitDikembalikan,

            // Fallback collection agar @forelse / @foreach tidak error
            'alatTerpopuler' => collect(),
            'alatTerpinjam' => collect(),
            'recentBatches' => collect(),
            'recentPengambilan' => collect(),
            'pengambilanTerbaru' => collect(),
            'perhatian' => collect(),
            'peminjamanPerluPerhatian' => collect(),
            'perluPerhatian' => collect(),
        ];
    }

    /**
     * Daftar event dan listener.
     */
    protected $listen = [
        SparepartUpdated::class => [
            SyncToDriveListener::class,
        ],
    ];
}
