
<?php

use Illuminate\Support\Facades\Schedule;
use App\Http\Controllers\PengambilanAlatController;

/*
|--------------------------------------------------------------------------
| Sismalat - Peminjaman Warning Scheduler
|--------------------------------------------------------------------------
|
| Memeriksa peminjaman yang mendekati jatuh tempo atau sudah terlambat.
| Notifikasi dikirim ke database admin/super.
| Email kepada peminjam dikirim secara manual oleh admin.
|
*/

Schedule::call(function (): void {
    app(PengambilanAlatController::class)
        ->checkPeminjamanWarning();
})
    ->name('check-peminjaman-warning')
    ->everyMinute()
    ->withoutOverlapping();
