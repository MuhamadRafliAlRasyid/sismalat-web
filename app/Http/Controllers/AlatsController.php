<?php

namespace App\Http\Controllers;

/* ========================================================================
 * IMPORTS
 * ======================================================================== */

// Models
use App\Models\Alat;
use App\Models\KalibrasiAlat;
use App\Models\PengambilanAlat;
use App\Models\PengembalianAlat;
use App\Models\Ruangan;
use App\Models\User;
use App\Models\PengambilanAlatItem;

// Exports & Notifications
use App\Exports\AlatExport;
use App\Notifications\AlatExpiredNotification;

// Services
use App\Services\HashIdService;

// Third-party: QR Code
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Third-party: Excel & PDF
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

// Laravel: HTTP
use Illuminate\Http\Request;

// Laravel: Pagination
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

// Laravel: Facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Laravel: Other
use Carbon\Carbon;


class AlatsController extends Controller
{
    /* ====================================================================
     * HELPER METHODS (Protected)
     * ==================================================================== */

    /**
     * Resolve hashid ke model Alat (hanya yang aktif / non-trashed).
     */
    protected function resolveHashid(string $hashid): Alat
    {
        $id = app(HashIdService::class)->decode($hashid);
        abort_if(!$id, 404);

        return Alat::findOrFail($id);
    }

    /**
     * Resolve hashid ke model Alat termasuk yang sudah soft-deleted.
     */
    protected function resolveHashidWithTrashed(string $hashid): Alat
    {
        $id = app(HashIdService::class)->decode($hashid);
        abort_if(!$id, 404);

        return Alat::withTrashed()->findOrFail($id);
    }

    /**
     * Normalisasi nama alat untuk grouping folder.
     * Menghapus angka di akhir dan spasi berlebih.
     *
     * Contoh: "Multimeter Digital 2" → "Multimeter Digital"
     */
    protected function normalizeNamaAlat(string $nama): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $nama));
        $normalized = preg_replace('/\d+$/', '', $normalized);

        return trim($normalized);
    }


    /* ====================================================================
     * INDEX & RIWAYAT
     * ==================================================================== */

    /**
     * Tampilkan daftar alat dengan grouping per nama + pagination manual.
     */
    public function index(Request $request)
    {
        // ── Query dasar ──────────────────────────────────────────────
        $query = Alat::with(['kategori', 'ruangan']);

        if (Schema::hasColumn('alats', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        // ── Filter pencarian & dropdown ──────────────────────────────
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('ruangan_id')) {
            $query->where('ruangan_id', $request->ruangan_id);
        }

        if ($request->filled('status_kalibrasi') && Schema::hasColumn('alats', 'status')) {
            $query->where('status', $request->status_kalibrasi);
        }

        // ── Grouping & pagination manual ─────────────────────────────
        $allAlats = $query->orderBy('nama_alat')->get();

        $groupedAlats = $allAlats->groupBy(function ($alat) {
            return $this->normalizeNamaAlat($alat->nama_alat);
        });

        $perPage      = 12;
        $currentPage  = Paginator::resolveCurrentPage('page');
        $currentItems = $groupedAlats
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $alats = new LengthAwarePaginator(
            $currentItems,
            $groupedAlats->count(),
            $perPage,
            $currentPage,
            [
                'path'  => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        // ── Mapping nama pertama per halaman (navigasi cepat) ────────
        $pageNames = [];
        $allNames  = $groupedAlats->keys()->values();

        for ($i = 0; $i < $allNames->count(); $i += $perPage) {
            $pageNum = ($i / $perPage) + 1;
            $pageNames[$pageNum] = $allNames[$i];
        }

        $alats->pageNames = $pageNames;

        // ── Data pendukung ───────────────────────────────────────────
        $kategoris = \App\Models\Kategori::orderBy('nama')->get();
        $ruangans  = Ruangan::orderBy('kode_ruangan')->get();

        $stats = [
            'total_hasil' => $allAlats->count(),
            'total_group' => $groupedAlats->count(),
            'search'      => $request->search,
            'filtered'    => $request->filled('search')
                            || $request->filled('kategori_id')
                            || $request->filled('ruangan_id')
                            || $request->filled('status_kalibrasi'),
        ];

        // ── Periodic check ───────────────────────────────────────────
        $this->checkExpired();

        return view('alats.index', compact('alats', 'kategoris', 'ruangans', 'stats'));
    }

    /**
     * Tampilkan daftar alat yang punya riwayat (pinjam / kembali / kalibrasi).
     */

public function daftarRiwayat(Request $request)
{
    $query = Alat::with([
        'kategori',
        'ruangan',
        'pengambilanItems.pengambilan.user',
        'pengambilanItems.pengambilan.bagian',
        'kalibrasis',
    ])->where(function ($q) {
        $q->whereHas('pengambilanItems')
          ->orWhereHas('kalibrasis');
    });

    // Pencarian alat
    if ($request->filled('search')) {
        $query->search($request->search);
    }

    // Filter berdasarkan status item peminjaman
    if ($request->filled('status')) {
        switch ($request->status) {
            case 'dipinjam':
                $query->whereHas('pengambilanItems', function ($q) {
                    $q->where('status', 'dipinjam');
                });
                break;

            case 'dikembalikan':
                $query->whereHas('pengambilanItems', function ($q) {
                    $q->where('status', 'dikembalikan');
                });
                break;

            case 'dikalibrasi':
                $query->whereHas('kalibrasis');
                break;
        }
    }

    $alats = $query
        ->orderBy('nama_alat')
        ->paginate(12)
        ->withQueryString();

    return view('alats.riwayat-index', compact('alats'));
}


    /**
     * Tampilkan detail riwayat satu alat (pinjam, kembali, kalibrasi).
     */

public function riwayat($hashid)
{
    $alat = $this->resolveHashid($hashid);

    $alat->load([
        'kategori',
        'ruangan',
    ]);

    // Ambil riwayat item peminjaman khusus alat ini
    $items = PengambilanAlatItem::with([
        'pengambilan.user',
        'pengambilan.bagian',
    ])
    ->where('alat_id', $alat->id)
    ->latest()
    ->get();

    // Bentuk data pengambilan untuk Blade
    $pengambilan = $items
        ->filter(fn ($item) => $item->pengambilan !== null)
        ->map(function ($item) {
            $header = $item->pengambilan;

            return (object) [
                'id' => $item->id,
                'pengambilan_alat_id' => $header->id,
                'nama_peminjam' => $header->nama_peminjam,
                'user' => $header->user,
                'bagian' => $header->bagian,
                'jumlah' => $item->jumlah,
                'satuan' => $item->satuan,
                'status' => $item->status,
                'keperluan' => $header->keperluan,
                'waktu_pengambilan' => $header->waktu_pengambilan,
                'foto' => $header->foto,
            ];
        })
        ->values();

    // Riwayat pengembalian khusus item alat ini
    // Menggunakan kolom pengembalian di pengambilan_alat_items
    $pengembalian = $items
        ->filter(fn ($item) =>
            $item->status === 'dikembalikan'
            && $item->pengambilan !== null
        )
        ->map(function ($item) {
            $header = $item->pengambilan;

            return (object) [
                'id' => $item->id,
                'nama_peminjam' => $header->nama_peminjam,
                'user' => $header->user,
                'jumlah' => $item->jumlah,
                'tanggal_pengembalian' => $item->waktu_pengembalian,
                'keterangan' => $item->catatan_pengembalian,
                'foto' => $item->foto_pengembalian,
            ];
        })
        ->values();

    // Riwayat kalibrasi tetap menggunakan alat_id
    $kalibrasis = KalibrasiAlat::where('alat_id', $alat->id)
        ->latest()
        ->get();

    return view('alats.riwayat', compact(
        'alat',
        'pengambilan',
        'pengembalian',
        'kalibrasis'
    ));
}


    /* ====================================================================
     * CRUD
     * ==================================================================== */

    /**
     * Tampilkan form tambah alat baru.
     */
    public function create()
    {
        $kategoris = \App\Models\Kategori::all();
        $ruangans  = Ruangan::orderBy('kode_ruangan')->get();

        return view('alats.create', compact('kategoris', 'ruangans'));
    }

    /**
     * Simpan alat baru ke database.
     */
    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->uploadAndResizeFoto($request->file('foto'));
        }

        $alat = Alat::create($data);
        $this->generateQrCode($alat);

        return redirect()
            ->route('alats.index')
            ->with('success', 'Alat berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail alat.
     */
    public function show(string $hashid)
    {
        $alat = $this->resolveHashid($hashid);
        $alat->load(['kategori', 'kalibrasis', 'ruangan']);

        return view('alats.show', compact('alat'));
    }

    /**
     * Tampilkan form edit alat.
     */
    public function edit(string $hashid)
    {
        $alat      = $this->resolveHashid($hashid);
        $kategoris = \App\Models\Kategori::all();
        $ruangans  = Ruangan::orderBy('kode_ruangan')->get();

        return view('alats.edit', compact('alat', 'kategoris', 'ruangans'));
    }

    /**
     * Update data alat. Regenerate QR jika nama berubah.
     */
    public function update(Request $request, string $hashid)
    {
        $alat = $this->resolveHashid($hashid);
        $data = $request->validate($this->validationRules());

        if ($request->hasFile('foto')) {
            $this->deleteFoto($alat);
            $data['foto'] = $this->uploadAndResizeFoto($request->file('foto'));
        }

        $alat->update($data);

        if ($alat->wasChanged('nama_alat')) {
            $this->generateQrCode($alat);
        }

        return redirect()
            ->route('alats.index')
            ->with('success', 'Alat berhasil diperbarui.');
    }

    /**
     * Soft delete alat (pindah ke trash).
     */
    public function destroy(string $hashid)
    {
        $alat = $this->resolveHashid($hashid);
        $alat->delete();

        return back()->with('success', 'Alat dipindahkan ke tempat sampah.');
    }


    /* ====================================================================
     * SOFT DELETE (Trash)
     * ==================================================================== */

    /**
     * Tampilkan daftar alat yang sudah di-soft-delete.
     */
    public function trashed()
    {
        $data = Alat::onlyTrashed()->paginate(10);

        return view('alats.trashed', compact('data'));
    }

    /**
     * Restore alat dari trash.
     */
    public function restore(string $hashid)
    {
        $alat = $this->resolveHashidWithTrashed($hashid);
        $alat->restore();

        return back()->with('success', 'Alat berhasil dipulihkan.');
    }

    /**
     * Hapus alat secara permanen (termasuk file QR & foto).
     */
    public function forceDelete(string $hashid)
    {
        $alat = $this->resolveHashidWithTrashed($hashid);

        if ($alat->qr_code && Storage::disk('public')->exists($alat->qr_code)) {
            Storage::disk('public')->delete($alat->qr_code);
        }

        $this->deleteFoto($alat);
        $alat->forceDelete();

        return back()->with('success', 'Alat dihapus secara permanen.');
    }


    /* ====================================================================
     * QR CODE
     * ==================================================================== */

    /**
     * Generate QR Code untuk satu alat.
     */
    public function generateQrCode(Alat $alat): void
    {
        Storage::makeDirectory('public/qrcodes', 0755, true);

        $qrCodePath = 'qrcodes/alat_' . $alat->hashid . '_' . Str::slug($alat->nama_alat) . '.png';
        $fullPath   = storage_path('app/public/' . $qrCodePath);

        // Hapus QR lama kalau ada
        if ($alat->qr_code && Storage::disk('public')->exists($alat->qr_code)) {
            Storage::disk('public')->delete($alat->qr_code);
        }

        try {
            $qrCode = new QrCode(
                data: route('login') . '?alat_id=' . $alat->hashid,
                encoding: new Encoding('UTF-8'),
                size: 300,
                margin: 10,
                foregroundColor: new Color(0, 0, 0),
                backgroundColor: new Color(255, 255, 255)
            );

            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $result->saveToFile($fullPath);

            $alat->update(['qr_code' => $qrCodePath]);
        } catch (\Exception $e) {
            Log::error('QR Generation failed for alat ' . $alat->id . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate ulang QR Code untuk SEMUA alat (batch).
     */
    public function generateAllQrCodes()
    {
        try {
            Alat::chunk(100, function ($alats) {
                foreach ($alats as $alat) {
                    $this->generateQrCode($alat);
                }
            });

            return redirect()
                ->route('alats.index')
                ->with('success', 'Semua QR Code alat berhasil diperbarui/di-generate.');
        } catch (\Exception $e) {
            Log::error('Mass QR Generation failed: ' . $e->getMessage());

            return redirect()
                ->route('alats.index')
                ->with('error', 'Terjadi kesalahan saat generate massal: ' . $e->getMessage());
        }
    }


    /* ====================================================================
     * FILE HANDLING (Foto)
     * ==================================================================== */

    /**
     * Upload & resize foto menggunakan native GD.
     * Menghasilkan 2 file: original (max 1200px) + thumbnail (200×200).
     * Otomatis fallback ke JPEG kalau WebP tidak didukung.
     */
    protected function uploadAndResizeFoto($file): string
    {
        Storage::disk('public')->makeDirectory('alat');

        $sourcePath = $file->getRealPath();
        $imageInfo  = @getimagesize($sourcePath);

        if (!$imageInfo) {
            throw new \RuntimeException('File bukan gambar yang valid.');
        }

        $mime = $imageInfo['mime'];

        // Baca gambar sumber berdasarkan MIME
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $source = @imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $source = @imagecreatefrompng($sourcePath);
                break;
            case 'image/webp':
                $source = @imagecreatefromwebp($sourcePath);
                break;
            case 'image/gif':
                $source = @imagecreatefromgif($sourcePath);
                break;
            default:
                throw new \RuntimeException("Format gambar tidak didukung: {$mime}");
        }

        if (!$source) {
            throw new \RuntimeException('Gagal memproses gambar.');
        }

        $origW     = imagesx($source);
        $origH     = imagesy($source);
        $useWebp   = function_exists('imagewebp');
        $extension = $useWebp ? 'webp' : 'jpg';
        $filename  = date('YmdHis') . '_' . Str::random(10) . '.' . $extension;

        $originalPath = storage_path('app/public/alat/' . $filename);
        $thumbPath    = storage_path('app/public/alat/thumb_' . $filename);

        // ── 1. GAMBAR UTAMA (scale down jika > 1200px) ─────────────
        if ($origW > 1200) {
            $newW = 1200;
            $newH = (int) round($origH * (1200 / $origW));
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $resized = imagecreatetruecolor($newW, $newH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        if ($useWebp) {
            imagewebp($resized, $originalPath, 80);
        } else {
            imagejpeg($resized, $originalPath, 85);
        }
        imagedestroy($resized);

        // ── 2. THUMBNAIL (cover 200×200, center crop) ──────────────
        $thumb = imagecreatetruecolor(200, 200);
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);

        $srcRatio = $origW / $origH;
        $dstRatio = 1.0;

        if ($srcRatio > $dstRatio) {
            $srcH = $origH;
            $srcW = (int) round($origH * $dstRatio);
            $srcX = (int) round(($origW - $srcW) / 2);
            $srcY = 0;
        } else {
            $srcW = $origW;
            $srcH = (int) round($origW / $dstRatio);
            $srcX = 0;
            $srcY = (int) round(($origH - $srcH) / 2);
        }

        imagecopyresampled($thumb, $source, 0, 0, $srcX, $srcY, 200, 200, $srcW, $srcH);

        if ($useWebp) {
            imagewebp($thumb, $thumbPath, 60);
        } else {
            imagejpeg($thumb, $thumbPath, 70);
        }
        imagedestroy($thumb);
        imagedestroy($source);

        Log::info('Foto alat berhasil diproses', [
            'filename' => $filename,
            'format'   => $useWebp ? 'webp' : 'jpeg (fallback)',
            'original' => "{$origW}x{$origH}",
            'resized'  => "{$newW}x{$newH}",
            'mime'     => $mime,
        ]);

        return $filename;
    }

    /**
     * Hapus file foto original + thumbnail dari storage.
     */
    protected function deleteFoto(Alat $alat): void
    {
        if ($alat->foto) {
            Storage::disk('public')->delete('alat/' . $alat->foto);
            Storage::disk('public')->delete('alat/thumb_' . $alat->foto);
        }
    }


    /* ====================================================================
     * SCHEDULER: CEK ALAT EXPIRED
     * ==================================================================== */

    /**
     * Cek alat yang sudah / akan expired, kirim notifikasi ke admin.
     * Mekanisme anti-spam: tidak kirim ulang dalam 24 jam.
     */
    public function checkExpired()
    {
        $admins = User::whereIn('role', ['admin', 'super'])->get();

        if ($admins->isEmpty()) {
            Log::warning('[checkExpired] Tidak ada admin/super user.');
            return;
        }

        $today        = Carbon::today();
        $warningLimit = Carbon::today()->addDays(7)->endOfDay();

        $expired = Alat::whereNotNull('masa_berlaku')
            ->whereDate('masa_berlaku', '<', $today->toDateString())
            ->get();

        $warning = Alat::whereNotNull('masa_berlaku')
            ->whereBetween('masa_berlaku', [
                $today->toDateString(),
                $warningLimit->toDateString(),
            ])
            ->get();

        $alatsToProcess = $expired->merge($warning)->unique('id');

        $inserted = 0;
        $skipped  = 0;
        $cleared  = 0;
        $failed   = 0;

        foreach ($alatsToProcess as $alat) {
            // 1. Batas notifikasi 24 jam
            if ($alat->last_notified_at) {
                $lastNotified = Carbon::parse($alat->last_notified_at);

                if ($lastNotified->greaterThan(now()->subHours(24))) {
                    $skipped++;
                    continue;
                }
            }

            // 2. Tentukan status
            $masaBerlaku = Carbon::parse($alat->masa_berlaku)->startOfDay();
            $status      = $masaBerlaku->lt($today) ? 'expired' : 'warning';

            // 3. Hapus notifikasi lama
            $deletedCount = DB::table('notifications')
                ->where('type', AlatExpiredNotification::class)
                ->where(function ($query) use ($alat) {
                    $query->where('data->alat_id', $alat->id)
                          ->orWhere('data->alat_hashid', $alat->hashid);
                })
                ->delete();

            $cleared += $deletedCount;

            // 4. Kirim notifikasi baru ke semua admin
            $sentForAlat = 0;

            foreach ($admins as $admin) {
                try {
                    $admin->notify(new AlatExpiredNotification($alat, $status));
                    $inserted++;
                    $sentForAlat++;
                } catch (\Throwable $e) {
                    $failed++;
                    Log::error('[checkExpired] Gagal mengirim notifikasi.', [
                        'alat_id'   => $alat->id,
                        'nama_alat' => $alat->nama_alat,
                        'user_id'   => $admin->id,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }

            // 5. Update waktu notifikasi (hanya kalau minimal 1 berhasil)
            if ($sentForAlat > 0) {
                $alat->update(['last_notified_at' => now()]);
            }
        }

        Log::info('[checkExpired] Proses selesai.', [
            'total_alat'                => $alatsToProcess->count(),
            'notifikasi_terkirim'       => $inserted,
            'notifikasi_dilewati'       => $skipped,
            'notifikasi_lama_dihapus'   => $cleared,
            'notifikasi_gagal'          => $failed,
        ]);
    }


    /* ====================================================================
     * EXPORT (Excel & PDF)
     * ==================================================================== */

    /**
     * Export daftar alat ke file Excel dengan filter.
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['ruangan_id', 'kategori_id', 'kondisi']);

        return Excel::download(
            new AlatExport($filters),
            'Laporan_Alat_Sismalat.xlsx'
        );
    }

    /**
     * Export daftar alat ke file PDF dengan filter.
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->only(['ruangan_id', 'kategori_id', 'kondisi']);

        $query = Alat::with(['kategori', 'ruangan']);

        if (!empty($filters['ruangan_id'])) {
            $query->where('ruangan_id', $filters['ruangan_id']);
        }

        if (!empty($filters['kategori_id'])) {
            $query->where('kategori_id', $filters['kategori_id']);
        }

        if (!empty($filters['kondisi'])) {
            switch ($filters['kondisi']) {
                case 'baik':
                    $query->where('jumlah_baik', '>', 0);
                    break;
                case 'kurang_baik':
                    $query->where('jumlah_kurang_baik', '>', 0);
                    break;
                case 'rusak_berat':
                    $query->where('jumlah_rusak_berat', '>', 0);
                    break;
            }
        }

        $alats = $query->orderBy('ruangan_id')->orderBy('kategori_id')->get();

        // ── Info filter untuk header PDF ─────────────────────────────
        $filterInfo = [];

        if (!empty($filters['ruangan_id'])) {
            $ruangan = Ruangan::find($filters['ruangan_id']);
            $filterInfo[] = 'Ruangan: ' . ($ruangan
                ? $ruangan->kode_ruangan . ' - ' . $ruangan->nama_ruangan
                : 'Semua');
        }

        if (!empty($filters['kategori_id'])) {
            $kategori = \App\Models\Kategori::find($filters['kategori_id']);
            $filterInfo[] = 'Kategori: ' . ($kategori ? $kategori->nama : 'Semua');
        }

        $pdf = Pdf::loadView('alats.export-pdf', [
            'alats'      => $alats,
            'filterInfo' => $filterInfo,
            'date'       => now()->format('d F Y, H:i:s'),
        ]);

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('Laporan_Alat_Sismalat.pdf');
    }


    /* ====================================================================
     * PRIVATE: VALIDATION RULES
     * ==================================================================== */

    /**
     * Aturan validasi untuk create & update alat.
     * Dipisah agar tidak duplikasi di store() dan update().
     */
    private function validationRules(): array
    {
        return [
            // Data dasar alat
            'nama_alat'     => 'required|string|max:255',
            'kelas'         => 'nullable|string|max:100',
            'merk'          => 'required|string|max:255',
            'tipe'          => 'nullable|string|max:255',
            'no_seri'       => 'nullable|string|max:255',
            'no_identitas'  => 'nullable|string|max:255',
            'kapasitas'     => 'nullable|string|max:255',
            'daya_baca'     => 'nullable|string|max:255',
            'jumlah'        => 'required|integer|min:0',

            // Sertifikat & kategori
            'no_sertifikat' => 'nullable|string|max:255',
            'kategori_id'   => 'nullable|exists:kategoris,id',
            'masa_berlaku'  => 'nullable|date',

            // Foto
            'foto'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            // Kolom KIR (Kartu Inventaris Ruangan)
            'ruangan_id'         => 'nullable|exists:ruangans,id',
            'ukuran'             => 'nullable|string|max:100',
            'satuan'             => 'nullable|string|max:50',
            'jumlah_baik'        => 'nullable|integer|min:0',
            'jumlah_kurang_baik' => 'nullable|integer|min:0',
            'jumlah_rusak_berat' => 'nullable|integer|min:0',
            'tahun_pengadaan'    => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
        ];
    }
}
