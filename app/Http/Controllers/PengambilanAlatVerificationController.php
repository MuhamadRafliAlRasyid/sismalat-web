<?php

namespace App\Http\Controllers;

use App\Models\PengambilanAlat;
use Illuminate\Http\Request;

class PengambilanAlatVerificationController extends Controller
{
    /**
     * Tampilkan halaman verifikasi publik berdasarkan token.
     * Route ini TIDAK memerlukan autentikasi (dapat diakses siapa saja dengan token valid).
     */
    public function show(string $token)
{
    $data = PengambilanAlat::with([
        'user',
        'bagian',
        'items.alat',
        'approver',
    ])
    ->where('verification_token', $token)
    ->firstOrFail();

    return view('pengambilan_alat.verification', [
        'data' => $data,
    ]);
}
}
