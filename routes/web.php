<?php

/* ========================================================================
 * IMPORTS
 * ======================================================================== */

// Controllers
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AlatsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\BagiansController;
use App\Http\Controllers\KalibrasiAlatController;
use App\Http\Controllers\KaryawanDashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PengambilanAlatController;
use App\Http\Controllers\PengambilanSparepartController;
use App\Http\Controllers\PengembalianAlatController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\PublicPeminjamanAlatController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\QrScannerController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\SuperDashboardController;
use App\Http\Controllers\UserController;

// Exports
use App\Exports\PengambilanExport;
use App\Mail\EmailPercobaan;

// Models
use App\Models\Alat;
use App\Models\Bagian;

// Facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Maatwebsite\Excel\Facades\Excel;


/* ========================================================================
 * 1. PUBLIC ROUTES (tanpa auth)
 * ======================================================================== */

// Landing page
Route::get('/', function () {
    return view('landing', [
        'alats'   => Alat::query()
            ->whereNull('deleted_at')
            ->where('jumlah', '>', 0)
            ->orderBy('nama_alat')
            ->get(),
        'bagians' => Bagian::query()->orderBy('nama')->get(),
    ]);
})->name('landing');

// Peminjaman publik
Route::post('/peminjaman-publik', [PublicPeminjamanAlatController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('peminjaman-publik.store');

Route::get('/peminjaman/status/{token}', [PublicPeminjamanAlatController::class, 'track'])
    ->name('peminjaman-publik.track');

// QR Scanner (pre-login)
Route::get('/scan-qr', [QrScannerController::class, 'index'])->name('qr-scanner');
Route::post('/scan-qr/process', [QrScannerController::class, 'process'])->name('qr-scanner.process');

// Tes email (debugging SMTP)
Route::post(
    '/pengambilan-alat/{hashid}/peringatan/{warningType}/kirim-email',
    [PengambilanAlatController::class, 'sendWarningEmail']
)->name('pengambilan_alat.send-warning-email');

/* ========================================================================
 * 2. AUTHENTICATION ROUTES (guest only)
 * ======================================================================== */

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Login dengan konteks sparepart (redirect setelah login)
    Route::get('/login/{spareparts_hashid}', [AuthController::class, 'showLoginWithSparepart'])
        ->name('login.with-sparepart');
    Route::post('/login/{spareparts_hashid}', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirects'])->name('google.redirects');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


/* ========================================================================
 * 3. AUTHENTICATED ROUTES (semua user terautentikasi)
 * ======================================================================== */

Route::middleware('auth')->group(function () {

    /* -------------------------
       Onboarding
       ------------------------- */
    Route::post('/onboarding/complete', function () {
        Auth::user()->forceFill(['onboarding_completed' => true])->save();
        return response()->json(['status' => 'success']);
    })->name('onboarding.complete');

    /* -------------------------
       Notifications (navbar)
       ------------------------- */
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('readAll');

        // AJAX endpoints untuk navbar dropdown
        Route::post('/{id}/mark-read', function ($id) {
            Auth::user()->notifications()->findOrFail($id)->markAsRead();
            return response()->json(['success' => true]);
        })->name('markRead');

        Route::post('/mark-all-read', function () {
            Auth::user()->unreadNotifications->markAsRead();
            return response()->json(['success' => true]);
        })->name('markAllRead');
    });

    /* -------------------------
       Dashboards (per role)
       ------------------------- */
    Route::middleware('role:karyawan')->group(function () {
        Route::get('/karyawan/dashboard', [KaryawanDashboardController::class, 'index'])
            ->name('karyawan.dashboard');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');
    });

    Route::middleware('role:super')->group(function () {
        Route::get('/super/dashboard', [SuperDashboardController::class, 'index'])
            ->name('super.dashboard');
    });

    /* -------------------------
       ALAT (terpadu)
       -------------------------
       URUTAN WAJIB: route statis → route dinamis {hashid}.
       Jika tidak, URL /riwayat-alat, /create, /trashed akan "dicuri"
       oleh pola /{hashid} dan berakhir 404.
       ------------------------- */
    Route::prefix('alats')->name('alats.')->group(function () {

        // Statis — semua role
        Route::get('/', [AlatsController::class, 'index'])->name('index');
        Route::get('/riwayat-alat', [AlatsController::class, 'daftarRiwayat'])->name('daftarRiwayat');

        // Statis — khusus admin/super
        Route::middleware('role:admin,super')->group(function () {
            Route::get('/trashed', [AlatsController::class, 'trashed'])->name('trashed');
            Route::get('/create', [AlatsController::class, 'create'])->name('create');
            Route::post('/', [AlatsController::class, 'store'])->name('store');
            Route::get('/export/excel', [AlatsController::class, 'exportExcel'])->name('export.excel');
            Route::get('/export/pdf', [AlatsController::class, 'exportPdf'])->name('export.pdf');
        });

        // Dinamis — semua role
        Route::get('/{hashid}', [AlatsController::class, 'show'])->name('show');
        Route::get('/{hashid}/riwayat', [AlatsController::class, 'riwayat'])->name('riwayat');

        // Dinamis — khusus admin/super
        Route::middleware('role:admin,super')->group(function () {
            Route::get('/{hashid}/edit', [AlatsController::class, 'edit'])->name('edit');
            Route::put('/{hashid}', [AlatsController::class, 'update'])->name('update');
            Route::delete('/{hashid}', [AlatsController::class, 'destroy'])->name('destroy');
            Route::post('/{hashid}/restore', [AlatsController::class, 'restore'])->name('restore');
            Route::delete('/{hashid}/force-delete', [AlatsController::class, 'forceDelete'])->name('forceDelete');
        });
    });

    /* -------------------------
       PENGAMBILAN ALAT (multi-item)
       -------------------------
       URUTAN: statis → dinamis
       ------------------------- */
 /* -------------------------
   PENGAMBILAN ALAT (multi-item)
   -------------------------
   URUTAN: statis → dinamis
   ------------------------- */
Route::prefix('pengambilan_alat')->name('pengambilan_alat.')->group(function () {

    // Statis — semua role
    Route::get('/', [PengambilanAlatController::class, 'index'])->name('index');
    Route::get('/create', [PengambilanAlatController::class, 'create'])->name('create');
    Route::post('/', [PengambilanAlatController::class, 'store'])->name('store');

    // ✅ Export routes — WAJIB SEBELUM {hashid} & tanpa middleware role (diproses di controller)
    Route::get('/export/excel', [PengambilanAlatController::class, 'exportExcel'])->name('export.excel');
    Route::get('/export/pdf', [PengambilanAlatController::class, 'exportPdf'])->name('export.pdf');
    Route::get('/export/pdf/{hashid}', [PengambilanAlatController::class, 'exportPdf'])->name('export.single');

    // ⬅️ Route publik untuk verifikasi QR
    Route::get('/verifikasi/{token}', [\App\Http\Controllers\PengambilanAlatVerificationController::class, 'show'])
        ->withoutMiddleware(['auth'])
        ->name('verification');

    // Dinamis — semua role
    Route::get('/{hashid}', [PengambilanAlatController::class, 'show'])->name('show');

    // Dinamis — khusus admin/super
    Route::middleware('role:admin,super')->group(function () {
        Route::post('/{hashid}/approve', [PengambilanAlatController::class, 'approve'])->name('approve');
        Route::get('/{hashid}/edit', [PengambilanAlatController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [PengambilanAlatController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [PengambilanAlatController::class, 'destroy'])->name('destroy');
    });
});
    /* -------------------------
       PENGEMBALIAN ALAT (multi-item)
       ------------------------- */
    Route::prefix('pengembalian_alat')->name('pengembalian_alat.')->group(function () {

        // Statis
        Route::get('/', [PengembalianAlatController::class, 'index'])->name('index');
        Route::get('/export/pdf', [PengembalianAlatController::class, 'exportPdf'])->name('export');

        // Dinamis
        Route::get('/create/{hashid}', [PengembalianAlatController::class, 'create'])->name('create');
        Route::post('/store/{hashid}', [PengembalianAlatController::class, 'store'])->name('store');
        Route::get('/{hashid}', [PengembalianAlatController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [PengembalianAlatController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [PengembalianAlatController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [PengembalianAlatController::class, 'destroy'])->name('destroy');
        Route::get('/export/pdf/{hashid}', [PengembalianAlatController::class, 'exportPdf'])->name('export.single');
    });

    /* -------------------------
       PENGAMBILAN SPAREPART (legacy)
       ------------------------- */
    Route::prefix('pengambilan')->name('pengambilan.')->group(function () {

        // Statis
        Route::get('/', [PengambilanSparepartController::class, 'index'])->name('index');
        Route::get('/create', [PengambilanSparepartController::class, 'create'])->name('create');
        Route::get('/create/{spareparts_id}', [PengambilanSparepartController::class, 'create'])
            ->name('create.with-sparepart');
        Route::post('/', [PengambilanSparepartController::class, 'store'])->name('store');
        Route::get('/export-pdf/{id?}', [PengambilanSparepartController::class, 'exportPdf'])->name('exportpdf');

        // Dinamis
        Route::get('/{id}', [PengambilanSparepartController::class, 'show'])->name('show');
        Route::get('/{pengambilanSparepart}/edit', [PengambilanSparepartController::class, 'edit'])->name('edit');
        Route::put('/{pengambilanSparepart}', [PengambilanSparepartController::class, 'update'])->name('update');
        Route::delete('/{pengambilanSparepart}', [PengambilanSparepartController::class, 'destroy'])->name('destroy');
    });

    /* -------------------------
       PENGEMBALIAN (legacy)
       ------------------------- */
    Route::prefix('pengembalian')->name('pengembalian.')->group(function () {

        // Statis
        Route::get('/', [PengembalianController::class, 'index'])->name('index');
        Route::get('/create', [PengembalianController::class, 'create'])->name('create');
        Route::post('/', [PengembalianController::class, 'store'])->name('store');

        // Dinamis
        Route::get('/{hashid}', [PengembalianController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [PengembalianController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [PengembalianController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [PengembalianController::class, 'destroy'])->name('destroy');
    });

    /* -------------------------
       PURCHASE REQUEST
       ------------------------- */
    Route::prefix('purchase-requests')->name('purchase_requests.')->group(function () {

        // Statis & Dinamis — semua role
        Route::get('/', [PurchaseRequestController::class, 'index'])->name('index');
        Route::get('/{hashid}', [PurchaseRequestController::class, 'show'])->name('show');

        // Khusus admin/super
        Route::middleware('role:admin,super')->group(function () {
            Route::get('/create', [PurchaseRequestController::class, 'create'])->name('create');
            Route::post('/', [PurchaseRequestController::class, 'store'])->name('store');
            Route::get('/unduh', [PurchaseRequestController::class, 'unduh'])->name('unduh');
            Route::get('/{hashid}/edit', [PurchaseRequestController::class, 'edit'])->name('edit');
            Route::put('/{hashid}', [PurchaseRequestController::class, 'update'])->name('update');
            Route::delete('/{hashid}', [PurchaseRequestController::class, 'destroy'])->name('destroy');
            Route::post('/{hashid}/approve', [PurchaseRequestController::class, 'approve'])->name('approve');
            Route::post('/{hashid}/reject', [PurchaseRequestController::class, 'reject'])->name('reject');
            Route::post('/{hashid}/complete', [PurchaseRequestController::class, 'complete'])->name('complete');
        });
    });

    /* -------------------------
       Export Pengambilan (legacy Excel)
       ------------------------- */
    Route::get('/pengambilan/export/{id?}', function ($id = null) {
        return Excel::download(
            new PengambilanExport($id),
            'pengambilan_' . ($id ? 'id_' . $id : 'all') . '.xlsx'
        );
    })->name('pengambilan.export');

    /* -------------------------
       Legacy: Sparepart Log
       ------------------------- */
    Route::get('/sparepart/log/{id}', function ($id) {
        return view('sparepart-log-page', ['id' => $id]);
    })->name('sparepart.log');
});


/* ========================================================================
 * 4. ADMIN / SUPER ONLY ROUTES
 * ======================================================================== */

Route::middleware(['auth', 'role:admin,super'])->group(function () {

    /* -------------------------
       KALIBRASI
       ------------------------- */
    Route::prefix('kalibrasis')->name('kalibrasis.')->group(function () {

    Route::get('/', [KalibrasiAlatController::class, 'index'])
        ->name('index');

    Route::get('/create/{hashid}', [KalibrasiAlatController::class, 'create'])
        ->name('create');

    Route::post('/store/{hashid}', [KalibrasiAlatController::class, 'store'])
        ->name('store');

    // BUKTI SERTIFIKAT
    Route::get('/{hashid}/sertifikat', [
        KalibrasiAlatController::class,
        'sertifikat'
    ])->name('sertifikat');

    Route::get('/{hashid}', [KalibrasiAlatController::class, 'show'])
        ->name('show');

    Route::get('/{hashid}/edit', [KalibrasiAlatController::class, 'edit'])
        ->name('edit');

    Route::put('/{hashid}', [KalibrasiAlatController::class, 'update'])
        ->name('update');

    Route::delete('/{hashid}', [KalibrasiAlatController::class, 'destroy'])
        ->name('destroy');
});

    /* -------------------------
       KATEGORI
       ------------------------- */
    Route::prefix('kategoris')->name('kategoris.')->group(function () {

        // Statis
        Route::get('/', [KategoriController::class, 'index'])->name('index');
        Route::get('/create', [KategoriController::class, 'create'])->name('create');
        Route::post('/', [KategoriController::class, 'store'])->name('store');

        // Dinamis
        Route::get('/{hashid}', [KategoriController::class, 'show'])->name('show');
        Route::get('/{hashid}/edit', [KategoriController::class, 'edit'])->name('edit');
        Route::put('/{hashid}', [KategoriController::class, 'update'])->name('update');
        Route::delete('/{hashid}', [KategoriController::class, 'destroy'])->name('destroy');
    });

    /* -------------------------
       RUANGAN
       ------------------------- */
    // Route::prefix('ruangans')->name('ruangans.')->group(function () {

    //     // Statis
    //     Route::get('/', [RuanganController::class, 'index'])->name('index');
    //     Route::get('/create', [RuanganController::class, 'create'])->name('create');
    //     Route::post('/', [RuanganController::class, 'store'])->name('store');

    //     // Dinamis
    //     Route::get('/{hashid}', [RuanganController::class, 'show'])->name('show');
    //     Route::get('/{hashid}/edit', [RuanganController::class, 'edit'])->name('edit');
    //     Route::put('/{hashid}', [RuanganController::class, 'update'])->name('update');
    //     Route::delete('/{hashid}', [RuanganController::class, 'destroy'])->name('destroy');
    // });

    /* -------------------------
       BAGIAN
       ------------------------- */
    Route::prefix('bagian')->name('bagian.')->group(function () {

        // Statis
        Route::get('/', [BagiansController::class, 'index'])->name('index');
        Route::get('/create', [BagiansController::class, 'create'])->name('create');
        Route::post('/', [BagiansController::class, 'store'])->name('store');

        // Dinamis (parameter {bagian} = model binding)
        Route::get('/{bagian}/edit', [BagiansController::class, 'edit'])->name('edit');
        Route::put('/{bagian}', [BagiansController::class, 'update'])->name('update');
        Route::delete('/{bagian}', [BagiansController::class, 'destroy'])->name('destroy');
    });

    /* -------------------------
       USER MANAGEMENT (ANGGOTA)
       ------------------------- */
    Route::prefix('anggota')->name('admin.')->group(function () {

        // Statis
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');

        // Dinamis (parameter {user} = model binding)
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });
});
