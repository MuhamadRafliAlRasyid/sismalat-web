<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ChecksSchemaColumns;
use App\Models\Alat;
use App\Models\Bagian;
use App\Models\PengambilanAlat;
use App\Models\PengambilanAlatItem;
use App\Models\User;
use App\Notifications\PeminjamanWarningNotification;
use App\Services\HashIdService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use Carbon\Carbon;

class PengambilanAlatController extends Controller
{
    use ChecksSchemaColumns;
    /** Batas maksimum jumlah alat dalam satu batch peminjaman */
    protected const MAX_ITEMS = 15;

    /* ================= HELPERS ================= */

    protected function resolveHashid(?string $hashid): int
    {
        $id = app(HashIdService::class)->decode($hashid ?? '');
        if (!$id) abort(404);
        return $id;
    }

    protected function resolveHashidOrNull(?string $hashid): ?int
    {
        return app(HashIdService::class)->decode($hashid ?? '') ?: null;
    }

    /**
     * Decode & validasi items: kembalikan map [alat_id => jumlah].
     * Menolak duplikat alat. Tidak menyentuh stok (stok dicek di transaction).
     *
     * @return array<int,int>|null  null jika ada error (error sudah di-push ke validator)
     */
    protected function resolveItems(array $items): ?array
    {
        $resolved = [];

        foreach ($items as $idx => $item) {
            $alatId = $this->resolveHashidOrNull($item['alat_id'] ?? null);

            if (!$alatId) {
                throw ValidationException::withMessages([
                    "items.{$idx}.alat_id" => 'Alat yang dipilih tidak valid.',
                ]);
            }

            if (isset($resolved[$alatId])) {
                throw ValidationException::withMessages([
                    'items' => 'Ada alat yang dipilih lebih dari satu kali. Gabungkan jumlahnya dalam satu baris.',
                ]);
            }

            $resolved[$alatId] = (int) ($item['jumlah'] ?? 0);
        }

        return $resolved;
    }

    /* ================= INDEX ================= */

    public function index(Request $request)
    {
        $query = PengambilanAlat::with(['user', 'bagian', 'items.alat'])
            ->latest('waktu_pengambilan');

        // Non-admin/super hanya melihat batch miliknya sendiri
        if (Auth::check() && !Auth::user()->hasRole(['admin', 'super'])) {
            $query->where('user_id', Auth::id());
        }

        // Filter status
        if ($request->filled('status')) {
            match ($request->status) {
                'dipinjam'                => $query->whereIn('status', ['dipinjam', 'sebagian_dikembalikan']),
                'dikembalikan'            => $query->selesai(),
                'terlambat'               => $query->terlambat(),
                'warning'                 => $query->mendekatiJatuhTempo(3),
                default                   => null,
            };
        }

        if ($request->boolean('critical')) {
            $query->where('is_critical', true);
        }

        // Pencarian (termasuk lewat items.alat, karena header tidak lagi punya alat_id)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_peminjam', 'like', "%{$search}%")
                  ->orWhere('keperluan', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('bagian', fn($bq) => $bq->where('nama', 'like', "%{$search}%"))
                  ->orWhereHas('items.alat', function ($aq) use ($search) {
                      $aq->where('nama_alat', 'like', "%{$search}%")
                         ->orWhere('merk', 'like', "%{$search}%")
                         ->orWhere('no_seri', 'like', "%{$search}%");
                  });
            });
        }

        // Filter rentang tanggal
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('waktu_pengambilan', [
                $request->date_from . ' 00:00:00',
                $request->date_to . ' 23:59:59',
            ]);
        }

        // Sorting
        $sortBy    = $request->input('sort_by', 'waktu_pengambilan');
        $sortOrder = $request->input('sort_order', 'desc');
        if (in_array($sortBy, ['waktu_pengambilan', 'nama_peminjam', 'status', 'created_at'], true)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $data = $query->paginate(15)->withQueryString();

        $stats = [
            'total_aktif'     => PengambilanAlat::aktif()->count(),
            'total_terlambat' => PengambilanAlat::terlambat()->count(),
            'total_warning'   => PengambilanAlat::mendekatiJatuhTempo(3)->count(),
            'total_critical'  => PengambilanAlat::where('is_critical', true)->count(),
        ];

        $filters = $request->only(['search', 'status', 'critical', 'date_from', 'date_to']);

        return view('pengambilan_alat.index', compact('data', 'stats', 'filters'));
    }

    /* ================= CREATE ================= */

    public function create(Request $request)
    {
        // Katalog alat — SEMUA klausul kolom di-guard, SELECT * (aman terhadap skema)
        $q = Alat::query();
        if ($this->hasCol('alats', 'deleted_at')) $q->whereNull('deleted_at');
        if ($this->hasCol('alats', 'jumlah'))     $q->where('jumlah', '>', 0);
        if ($this->hasCol('alats', 'nama_alat'))  $q->orderBy('nama_alat');
        $alats = $q->get(); // tanpa select-list → tidak ada "Unknown column"

        $users   = User::all();
        $bagians = Bagian::all();

        if (Auth::user()->role !== 'admin') {
            $users   = User::where('id', Auth::id())->get();
            $bagians = Auth::user()->bagian
                ? Bagian::where('id', Auth::user()->bagian->id)->get()
                : collect();
        }

        $prefill = null;
        if ($request->filled('alat_hashid')) {
            $id = $this->resolveHashidOrNull($request->alat_hashid);
            if ($id && $alats->contains('id', $id)) {
                $prefill = $request->alat_hashid;
            }
        }

        return view('pengambilan_alat.create', compact('alats', 'users', 'bagians', 'prefill'));
    }

    /* ================= STORE ================= */

    public function store(Request $request)
    {
        Log::info('START STORE PENGAMBILAN ALAT (multi-item)', ['request' => $request->all()]);

        // Non-admin dipaksa ke data dirinya sendiri
        if (Auth::user()->role !== 'admin') {
            $request->merge([
                'user_id'   => Auth::id(),
                'bagian_id' => Auth::user()->bagian_id ?? null,
            ]);
        }

        $validated = $request->validate([
            'user_id'           => 'required|exists:users,id',
            'bagian_id'         => 'nullable|exists:bagian,id',
            'nama_peminjam'     => 'nullable|string|max:255',
            'keperluan'         => 'required|string|max:255',
            'waktu_pengambilan' => 'required|date',
            'lama_pinjam'       => 'required|integer|min:1|max:365',
            'foto'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',

            'items'             => 'required|array|min:1|max:' . self::MAX_ITEMS,
            'items.*.alat_id'   => 'required|string',
            'items.*.jumlah'    => 'required|integer|min:1',
        ]);

        // 1) Decode + cek duplikat (di luar transaction, untuk validasi awal)
        $resolved = $this->resolveItems($validated['items']);

        // 2) Upload foto (di luar transaction agar tidak mengunci DB saat I/O)
        if ($request->hasFile('foto')) {
            $validated['foto'] = $this->uploadFotoTransaksi($request->file('foto'), 'pengambilan');
        }

        try {
            $pengambilan = DB::transaction(function () use ($validated, $resolved) {
                // Buat header (tanpa alat)
                $headerData = collect($validated)->except('items')->all();
                $headerData['status'] = 'dipinjam';
                $headerData['tanggal_jatuh_tempo'] = Carbon::parse(
                    $validated['waktu_pengambilan']
                )->addDays((int) $validated['lama_pinjam'])->toDateString();

                $pengambilan = PengambilanAlat::create($headerData);

                // Kunci & validasi stok per alat, lalu decrement + buat item
                foreach ($resolved as $alatId => $jumlah) {
                    $alat = Alat::whereNull('deleted_at')
                        ->whereKey($alatId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($alat->jumlah < $jumlah) {
                        throw ValidationException::withMessages([
                            'items' => "Stok \"{$alat->nama_alat}\" tidak mencukupi. Tersedia: {$alat->jumlah}, diminta: {$jumlah}.",
                        ]);
                    }

                    $alat->decrement('jumlah', $jumlah);

                    $pengambilan->items()->create([
                        'alat_id' => $alat->id,
                        'jumlah'  => $jumlah,
                        'satuan'  => $alat->satuan ?: 'unit',
                        'status'  => 'dipinjam',
                    ]);
                }

                return $pengambilan;
            });
        } catch (ValidationException $e) {
            // Transaction otomatis rollback (stok tidak berubah)
            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            Log::error('ERROR STORE PENGAMBILAN ALAT', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);
            return back()->withInput()->with('error', 'Terjadi error: ' . $e->getMessage());
        }

        return redirect()
            ->route('pengambilan_alat.show', $pengambilan->hashid)
            ->with('success', 'Peminjaman berhasil dicatat (' . $pengambilan->items()->count() . ' alat).');
    }

    /* ================= SHOW ================= */

    public function show($hashid)
    {
        $data = PengambilanAlat::with(['user', 'bagian', 'items.alat', 'items'])
            ->findOrFail($this->resolveHashid($hashid));

        if (!Auth::user()->hasRole(['admin', 'super']) && Auth::id() !== $data->user_id) {
            abort(403);
        }

        return view('pengambilan_alat.show', compact('data'));
    }

    /* ================= EDIT ================= */

    public function edit($hashid)
    {
        $data = PengambilanAlat::with('items.alat')
            ->findOrFail($this->resolveHashid($hashid));

        if (!Auth::user()->hasRole(['admin', 'super']) && Auth::id() !== $data->user_id) {
            abort(403);
        }

        // Alat yang bisa dipilih: yang tersedia + yang sudah ada di batch ini
        $alatIdsInBatch = $data->items->pluck('alat_id')->all();
        $alats = Alat::whereNull('deleted_at')
            ->where(function ($q) use ($alatIdsInBatch) {
                $q->where('jumlah', '>', 0)->orWhereIn('id', $alatIdsInBatch);
            })
            ->orderBy('nama_alat')
            ->get(['id', 'nama_alat', 'merk', 'tipe', 'no_seri', 'jumlah', 'satuan']);

        $users   = User::all();
        $bagians = Bagian::all();

        return view('pengambilan_alat.edit', compact('data', 'alats', 'users', 'bagians'));
    }

    /* ================= UPDATE ================= */

    public function update(Request $request, $hashid)
    {
        $data = PengambilanAlat::with('items')->findOrFail(
            $this->resolveHashid($hashid)
        );

        if (!Auth::user()->hasRole(['admin', 'super']) && Auth::id() !== $data->user_id) {
            abort(403);
        }

        $rules = [
            'user_id'           => 'sometimes|required|exists:users,id',
            'bagian_id'         => 'nullable|exists:bagian,id',
            'nama_peminjam'     => 'nullable|string|max:255',
            'keperluan'         => 'required|string|max:255',
            'waktu_pengambilan' => 'required|date',
            'lama_pinjam'       => 'required|integer|min:1|max:365',
            'foto'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];

        // Item tidak boleh disinkronkan ulang jika sudah ada pengembalian.
        $adaPengembalian = $data->items()->dikembalikan()->exists();

        if (!$adaPengembalian) {
            $rules['items'] = 'required|array|min:1|max:' . self::MAX_ITEMS;
            $rules['items.*.alat_id'] = 'required|string';
            $rules['items.*.jumlah'] = 'required|integer|min:1';
        }

        $validated = $request->validate($rules);

        // Hitung jatuh tempo dari waktu pengambilan + lama pinjam.
        $newDueDate = Carbon::parse($validated['waktu_pengambilan'])
            ->addDays((int) $validated['lama_pinjam'])
            ->toDateString();

        $oldDueDate = $data->tanggal_jatuh_tempo
            ? Carbon::parse($data->tanggal_jatuh_tempo)->toDateString()
            : null;

        $dueDateChanged = $oldDueDate !== $newDueDate;

        // Upload foto baru. Foto lama baru dihapus setelah update berhasil.
        $oldFoto = $data->foto;
        $newFoto = null;
        if ($request->hasFile('foto')) {
            $newFoto = $this->uploadFotoTransaksi(
                $request->file('foto'),
                'pengambilan'
            );
            $validated['foto'] = $newFoto;
        }

        try {
            DB::transaction(function () use (
                $data,
                $validated,
                $adaPengembalian,
                $request,
                $newDueDate,
                $dueDateChanged
            ) {
                $updateData = collect($validated)->except('items')->all();
                $updateData['tanggal_jatuh_tempo'] = $newDueDate;

                // Jika tanggal jatuh tempo berubah, peringatan lama tidak lagi relevan.
                if ($dueDateChanged) {
                    $resetWarningData = [
                        'notified_15_percent' => false,
                        'notified_1_day' => false,
                        'notified_overdue' => false,
                        'warning_15_sent_at' => null,
                        'warning_1day_sent_at' => null,
                        'last_warned_at' => null,
                        'is_critical' => false,
                    ];

                    // Kolom overdue merupakan tambahan migration; cek agar update
                    // transaksi lama tetap aman sebelum migration dijalankan.
                    if (Schema::hasColumn('pengambilan_alats', 'warning_overdue_sent_at')) {
                        $resetWarningData['warning_overdue_sent_at'] = null;
                    }

                    $updateData = array_merge($updateData, $resetWarningData);
                }

                $data->update($updateData);

                if ($dueDateChanged) {
                    $this->deleteWarningNotificationsForBatch((int) $data->id);
                }

                // Sinkronkan item hanya bila seluruh item belum pernah dikembalikan.
                if (!$adaPengembalian && $request->has('items')) {
                    $newResolved = $this->resolveItems($validated['items']);
                    $this->syncItemsWithStock($data, $newResolved);
                }
            });
        } catch (ValidationException $e) {
            if ($newFoto) {
                $this->deleteFotoTransaksi($newFoto, 'pengambilan');
            }

            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            if ($newFoto) {
                $this->deleteFotoTransaksi($newFoto, 'pengambilan');
            }

            Log::error('ERROR UPDATE PENGAMBILAN ALAT', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return back()->withInput()->with(
                'error',
                'Gagal memperbarui: ' . $e->getMessage()
            );
        }

        // Hapus foto lama hanya setelah update database berhasil.
        if ($newFoto && $oldFoto && $oldFoto !== $newFoto) {
            $this->deleteFotoTransaksi($oldFoto, 'pengambilan');
        }

        return redirect()
            ->route('pengambilan_alat.show', $data->hashid)
            ->with('success', 'Data peminjaman berhasil diperbarui.');
    }

    /**
     * Hapus notifikasi peringatan batch saat tanggal jatuh tempo berubah.
     * Data notifikasi lama tidak boleh menampilkan tombol/email dengan tenggat yang sudah usang.
     */
    protected function deleteWarningNotificationsForBatch(int $batchId): int
    {
        return DB::table('notifications')
            ->where('type', PeminjamanWarningNotification::class)
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data, '$.pengambilan_id')) = ?",
                [(string) $batchId]
            )
            ->delete();
    }

    /**
     * Sinkronkan items batch dengan map [alat_id => jumlah] baru,
     * sambil menyesuaikan stok (delta). Hanya untuk item berstatus 'dipinjam'.
     */
    protected function syncItemsWithStock(PengambilanAlat $data, array $newResolved): void
    {
        // Peta jumlah lama per alat (hanya yang masih dipinjam)
        $oldMap = $data->items()
            ->dipinjam()
            ->get()
            ->mapWithKeys(fn($i) => [$i->alat_id => $i->jumlah])
            ->all();

        // Alat yang dihapus dari form -> kembalikan stok
        foreach ($oldMap as $alatId => $oldQty) {
            if (!isset($newResolved[$alatId])) {
                Alat::whereKey($alatId)->lockForUpdate()->increment('jumlah', $oldQty);
                $data->items()->dipinjam()->where('alat_id', $alatId)->delete();
            }
        }

        // Proses alat baru / yang berubah jumlahnya
        foreach ($newResolved as $alatId => $newQty) {
            $alat = Alat::whereNull('deleted_at')->whereKey($alatId)->lockForUpdate()->firstOrFail();
            $oldQty = $oldMap[$alatId] ?? 0;
            $delta  = $newQty - $oldQty;

            if ($delta > 0) {
                if ($alat->jumlah < $delta) {
                    throw ValidationException::withMessages([
                        'items' => "Stok \"{$alat->nama_alat}\" tidak mencukupi untuk penambahan. Tersedia: {$alat->jumlah}.",
                    ]);
                }
                $alat->decrement('jumlah', $delta);
            } elseif ($delta < 0) {
                $alat->increment('jumlah', abs($delta));
            }

            if ($oldQty > 0) {
                // Update jumlah item yang ada
                $data->items()->dipinjam()->where('alat_id', $alatId)->update([
                    'jumlah' => $newQty,
                    'satuan' => $alat->satuan ?: 'unit',
                ]);
            } else {
                // Item baru
                $data->items()->create([
                    'alat_id' => $alat->id,
                    'jumlah'  => $newQty,
                    'satuan'  => $alat->satuan ?: 'unit',
                    'status'  => 'dipinjam',
                ]);
            }
        }
    }

    /* ================= DESTROY ================= */

    public function destroy($hashid)
    {
        $data = PengambilanAlat::with('items')->findOrFail($this->resolveHashid($hashid));

        if (!Auth::user()->hasRole(['admin', 'super']) && Auth::id() !== $data->user_id) {
            abort(403);
        }

        DB::transaction(function () use ($data) {
            // Kembalikan stok HANYA untuk item yang masih dipinjam
            foreach ($data->items()->dipinjam()->get() as $item) {
                Alat::whereKey($item->alat_id)->increment('jumlah', $item->jumlah);
            }

            if ($data->foto) {
                $this->deleteFotoTransaksi($data->foto, 'pengambilan');
            }

            $data->delete(); // items ikut terhapus via cascade FK
        });

        return redirect()
            ->route('pengambilan_alat.index')
            ->with('success', 'Data peminjaman berhasil dihapus dan stok dikembalikan.');
    }

    /* ================= EXPORT PDF ================= */


/* ================= EXPORT PDF ================= */

public function exportPdf($hashid = null)
{
    if ($hashid) {
        // Ekspor satu transaksi berdasarkan hashid
        $pengambilan = PengambilanAlat::with([
            'user',
            'bagian',
            'items.alat',
        ])->findOrFail($this->resolveHashid($hashid));

        $list = collect([$pengambilan]);

        // Nama file unik berdasarkan nomor bukti peminjaman
        $filename = 'Bukti_Peminjaman_'
            . $pengambilan->hashid
            . '_'
            . now()->format('Y-m-d')
            . '.pdf';
    } else {
        // Ekspor seluruh transaksi
        $list = PengambilanAlat::with([
            'user',
            'bagian',
            'items.alat',
        ])->get();

        $filename = 'Laporan_Peminjaman_'
            . now()->format('Y-m-d_His')
            . '.pdf';
    }

    $pdf = Pdf::loadView(
        'pengambilan_alat.export-pdf',
        compact('list')
    )->setPaper('a4', 'portrait');

    return $pdf->download($filename);
}


    /* ================= SCHEDULER: WARNING ================= */

    /**
     * Pemeriksaan berkala hanya membuat notifikasi database untuk admin/super.
     * Email peminjam TIDAK dikirim otomatis; admin mengirimnya melalui tombol.
     */
    public function checkPeminjamanWarning(): void
    {
        try {
            Log::info('[PeminjamanWarning] Pemeriksaan dimulai.', [
                'time' => now()->toDateTimeString(),
            ]);

            $admins = User::query()
                ->whereIn('role', ['admin', 'super'])
                ->get();

            if ($admins->isEmpty()) {
                Log::warning('[PeminjamanWarning] Admin/super tidak ditemukan.');
                return;
            }

            $pengambilans = PengambilanAlat::with(['items.alat'])
                ->whereIn('status', ['dipinjam', 'sebagian_dikembalikan'])
                ->whereNotNull('waktu_pengambilan')
                ->whereNotNull('lama_pinjam')
                ->where('lama_pinjam', '>', 0)
                ->get();

            $created = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($pengambilans as $p) {
                try {
                    // Perbaiki data lama yang belum memiliki tanggal jatuh tempo.
                    if (!$p->tanggal_jatuh_tempo) {
                        $computedDueDate = Carbon::parse($p->waktu_pengambilan)
                            ->addDays((int) $p->lama_pinjam)
                            ->toDateString();

                        $p->forceFill([
                            'tanggal_jatuh_tempo' => $computedDueDate,
                        ])->save();
                    }

                    $dueDate = Carbon::parse($p->tanggal_jatuh_tempo)->startOfDay();
                    $daysLeft = (int) Carbon::today()->diffInDays($dueDate, false);

                    // Prioritas: overdue > jatuh tempo besok > sisa 15 persen.
                    if ($daysLeft < 0) {
                        $warningType = 'overdue';
                        $flagField = 'notified_overdue';
                    } elseif ($daysLeft === 1) {
                        $warningType = '1_day';
                        $flagField = 'notified_1_day';
                    } elseif ($daysLeft > 1 && $p->should_warn_15) {
                        $warningType = '15_percent';
                        $flagField = 'notified_15_percent';
                    } else {
                        $skipped++;
                        continue;
                    }

                    $allAdminsHaveNotification = true;

                    foreach ($admins as $admin) {
                        $alreadyExists = $admin->notifications()
                            ->where('type', PeminjamanWarningNotification::class)
                            ->whereRaw(
                                "JSON_UNQUOTE(JSON_EXTRACT(data, '$.pengambilan_id')) = ?",
                                [(string) $p->id]
                            )
                            ->whereRaw(
                                "JSON_UNQUOTE(JSON_EXTRACT(data, '$.warning_type')) = ?",
                                [$warningType]
                            )
                            ->exists();

                        if ($alreadyExists) {
                            continue;
                        }

                        try {
                            $admin->notify(
                                new PeminjamanWarningNotification($p, $warningType)
                            );
                            $created++;
                        } catch (\Throwable $e) {
                            $allAdminsHaveNotification = false;
                            $failed++;

                            Log::error('[PeminjamanWarning] Gagal membuat notifikasi admin.', [
                                'batch_id' => $p->id,
                                'admin_id' => $admin->id,
                                'warning_type' => $warningType,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    // Flag berikut menandai notifikasi admin sudah dibuat,
                    // bukan berarti email ke peminjam sudah dikirim.
                    if ($allAdminsHaveNotification) {
                        $p->forceFill([
                            $flagField => true,
                            'is_critical' => in_array(
                                $warningType,
                                ['1_day', 'overdue'],
                                true
                            ),
                        ])->save();
                    }
                } catch (\Throwable $e) {
                    $failed++;

                    Log::error('[PeminjamanWarning] Gagal memproses peminjaman.', [
                        'batch_id' => $p->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('[PeminjamanWarning] Pemeriksaan selesai.', [
                'total_batch_aktif' => $pengambilans->count(),
                'notifikasi_admin_baru' => $created,
                'dilewati' => $skipped,
                'gagal' => $failed,
            ]);
        } catch (\Throwable $e) {
            Log::error('[PeminjamanWarning] Pemeriksaan gagal.', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }

    /**
     * Mengembalikan kolom timestamp berdasarkan jenis peringatan.
     */
    protected function warningSentColumn(string $warningType): string
    {
        return match ($warningType) {
            '15_percent' => 'warning_15_sent_at',
            '1_day' => 'warning_1day_sent_at',
            'overdue' => 'warning_overdue_sent_at',
            default => throw new \InvalidArgumentException('Jenis peringatan tidak valid.'),
        };
    }

    /**
     * Mengirim email peringatan hanya atas tindakan admin/super.
     * Route POST harus memakai middleware auth.
     */
    public function sendWarningEmail(
        Request $request,
        string $hashid,
        string $warningType
    ) {
        abort_unless(
            Auth::check() && Auth::user()->hasRole(['admin', 'super']),
            403
        );

        abort_unless(
            in_array($warningType, ['15_percent', '1_day', 'overdue'], true),
            404
        );

        $batchId = $this->resolveHashid($hashid);

        try {
            // Lock row selama validasi dan pengiriman untuk mencegah dua admin
            // mengirim peringatan yang sama secara bersamaan.
            $result = DB::transaction(function () use ($batchId, $warningType) {
                $p = PengambilanAlat::with(['items.alat'])
                    ->whereKey($batchId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array($p->status, ['dipinjam', 'sebagian_dikembalikan'], true)) {
                    throw new \DomainException('Peminjaman sudah selesai atau tidak aktif.');
                }

                if (!filter_var($p->email_peminjam, FILTER_VALIDATE_EMAIL)) {
                    throw new \DomainException('Alamat email peminjam belum tersedia atau tidak valid.');
                }

                // Lengkapi tenggat bila transaksi lama masih NULL.
                if (!$p->tanggal_jatuh_tempo) {
                    if (!$p->waktu_pengambilan || !$p->lama_pinjam) {
                        throw new \DomainException('Tanggal jatuh tempo tidak dapat dihitung.');
                    }

                    $p->tanggal_jatuh_tempo = Carbon::parse($p->waktu_pengambilan)
                        ->addDays((int) $p->lama_pinjam)
                        ->toDateString();
                    $p->save();
                }

                $dueDate = Carbon::parse($p->tanggal_jatuh_tempo)->startOfDay();
                $daysLeft = (int) Carbon::today()->diffInDays($dueDate, false);

                // Pastikan tombol yang ditekan masih sesuai keadaan peminjaman.
                if ($warningType === 'overdue' && $daysLeft >= 0) {
                    throw new \DomainException('Peminjaman ini belum melewati tanggal jatuh tempo.');
                }

                if ($warningType === '1_day' && $daysLeft !== 1) {
                    throw new \DomainException('Peringatan satu hari sudah tidak sesuai dengan tanggal jatuh tempo.');
                }

                if (
                    $warningType === '15_percent'
                    && ($daysLeft <= 1 || !$p->should_warn_15)
                ) {
                    throw new \DomainException('Peringatan 15% sudah tidak sesuai dengan kondisi peminjaman saat ini.');
                }

                $sentColumn = $this->warningSentColumn($warningType);

                if (!Schema::hasColumn('pengambilan_alats', $sentColumn)) {
                    throw new \DomainException(
                        "Kolom {$sentColumn} belum tersedia. Jalankan migration timestamp peringatan terlebih dahulu."
                    );
                }

                if ($p->getAttribute($sentColumn)) {
                    throw new \DomainException('Email untuk jenis peringatan ini sudah pernah dikirim.');
                }

                // Notification::route mengirim ke email eksternal tanpa membutuhkan user_id.
                Notification::route('mail', [
                    $p->email_peminjam => $p->nama_peminjam ?: 'Peminjam',
                ])->notify(
                    new PeminjamanWarningNotification($p, $warningType)
                );

                $sentAt = now();

                $p->forceFill([
                    $sentColumn => $sentAt,
                    'last_warned_at' => $sentAt,
                ])->save();

                return [
                    'batch_id' => $p->id,
                    'email' => $p->email_peminjam,
                    'sent_at' => $sentAt,
                ];
            });

            // Perbarui payload notification admin agar tombol berubah menjadi
            // "Email terkirim" saat dropdown dimuat ulang.
            $this->markWarningNotificationEmailSent(
                $result['batch_id'],
                $warningType,
                $result['email'],
                $result['sent_at']
            );

            return back()->with(
                'success',
                'Email peringatan berhasil diserahkan kepada mailer untuk ' . $result['email'] . '.'
            );
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('[PeminjamanWarning] Pengiriman email gagal.', [
                'batch_id' => $batchId,
                'warning_type' => $warningType,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return back()->with(
                'error',
                'Email gagal dikirim. Periksa konfigurasi mail dan laravel.log.'
            );
        }
    }

    /**
     * Tandai payload notifikasi database admin sebagai email terkirim.
     */
    protected function markWarningNotificationEmailSent(
        int $batchId,
        string $warningType,
        string $email,
        Carbon $sentAt
    ): void {
        $rows = DB::table('notifications')
            ->where('type', PeminjamanWarningNotification::class)
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data, '$.pengambilan_id')) = ?",
                [(string) $batchId]
            )
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data, '$.warning_type')) = ?",
                [$warningType]
            )
            ->get(['id', 'data']);

        foreach ($rows as $row) {
            $payload = json_decode($row->data, true);

            if (!is_array($payload)) {
                continue;
            }

            $payload['email_sent_at'] = $sentAt->toIso8601String();
            $payload['email_sent_to'] = $email;

            DB::table('notifications')
                ->where('id', $row->id)
                ->update([
                    'data' => json_encode(
                        $payload,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                    'updated_at' => $sentAt,
                ]);
        }
    }

    /* ================= FOTO ================= */

    protected function uploadFotoTransaksi($file, string $folder): string
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory($folder);

        // Fallback bila ekstensi GD tidak tersedia: simpan file asli dan duplikasi
        // sebagai thumbnail agar view tetap memiliki file yang dapat ditampilkan.
        if (!extension_loaded('gd') || !class_exists(GdDriver::class)) {
            $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $extension;

            $disk->putFileAs($folder, $file, $filename);
            $disk->copy("{$folder}/{$filename}", "{$folder}/thumb_{$filename}");

            return $filename;
        }

        // Intervention Image 4.x: gunakan decodeSplFileInfo(), bukan read().
        $manager = new ImageManager(new GdDriver());
        $useWebp = function_exists('imagewebp');
        $extension = $useWebp ? 'webp' : 'jpg';
        $filename = date('YmdHis') . '_' . Str::random(10) . '.' . $extension;

        $originalRelativePath = "{$folder}/{$filename}";
        $thumbnailRelativePath = "{$folder}/thumb_{$filename}";

        $originalFullPath = $disk->path($originalRelativePath);
        $thumbnailFullPath = $disk->path($thumbnailRelativePath);

        $image = $manager->decodeSplFileInfo($file);
        $image->scaleDown(width: 1200)->save($originalFullPath, quality: 80);

        // Decode ulang agar operasi cover thumbnail tidak memengaruhi gambar utama.
        $thumbnail = $manager->decodeSplFileInfo($file);
        $thumbnail->cover(200, 200)->save($thumbnailFullPath, quality: 60);

        return $filename;
    }

    protected function deleteFotoTransaksi($filename, string $folder): void
    {
        if ($filename) {
            Storage::disk('public')->delete("$folder/$filename");
            Storage::disk('public')->delete("$folder/thumb_$filename");
        }
    }
}
