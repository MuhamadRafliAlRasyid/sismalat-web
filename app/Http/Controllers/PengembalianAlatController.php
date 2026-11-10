<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\PengambilanAlat;
use App\Models\PengambilanAlatItem;
use App\Models\PengembalianAlat;
use App\Services\HashIdService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class PengembalianAlatController extends Controller
{
    /**
     * Decode Hash ID menjadi primary key.
     */
    protected function resolveHashid(string $hashid): int
    {
        $id = app(HashIdService::class)->decode($hashid);

        if (!$id) {
            abort(404, 'Data tidak ditemukan.');
        }

        return (int) $id;
    }

    /**
     * Hak akses Admin dan Super Admin.
     */
    protected function isAdminOrSuper(): bool
    {
        return Auth::check()
            && Auth::user()->hasRole(['admin', 'super']);
    }

    protected function ensureAdminOrSuper(): void
    {
        abort_unless($this->isAdminOrSuper(), 403);
    }

    /**
     * Tentukan apakah tanggal pengembalian melewati tanggal jatuh tempo.
     * Tanggal yang sama dengan jatuh tempo masih dianggap tepat waktu.
     */
    protected function isLateForDate(PengambilanAlat $pengambilan, string $returnDate): bool
    {
        $dueDate = $pengambilan->tanggal_jatuh_tempo;

        if (!$dueDate) {
            return false;
        }

        return Carbon::parse($returnDate)->startOfDay()
            ->greaterThan(Carbon::parse($dueDate)->startOfDay());
    }

    /**
     * Hitung total jumlah yang telah dikembalikan untuk satu item batch.
     */
    protected function returnedQuantityForItem(int $itemId, ?int $exceptReturnId = null): int
    {
        $query = PengembalianAlat::query()
            ->where('pengambilan_alat_item_id', $itemId);

        if ($exceptReturnId) {
            $query->where('id', '!=', $exceptReturnId);
        }

        return (int) $query->sum('jumlah');
    }

    /**
     * Hitung jumlah pengembalian transaksi lama yang belum terhubung ke item.
     */
    protected function returnedQuantityForLegacy(int $pengambilanId, ?int $exceptReturnId = null): int
    {
        $query = PengembalianAlat::query()
            ->where('pengambilan_alat_id', $pengambilanId)
            ->whereNull('pengambilan_alat_item_id');

        if ($exceptReturnId) {
            $query->where('id', '!=', $exceptReturnId);
        }

        return (int) $query->sum('jumlah');
    }

    /**
     * Sinkronkan status setiap item dan status batch/header.
     *
     * Item dianggap selesai hanya jika jumlah pengembalian sudah mencapai
     * jumlah awal item tersebut. Batch menjadi sebagian_dikembalikan jika
     * sudah ada pengembalian tetapi masih ada alat/unit yang belum kembali.
     */
    protected function syncPengambilanStatus(PengambilanAlat $pengambilan): void
    {
        $items = PengambilanAlatItem::query()
            ->where('pengambilan_alat_id', $pengambilan->id)
            ->get();

        if ($items->isNotEmpty()) {
            $anyReturned = false;
            $allReturned = true;

            foreach ($items as $item) {
                $returned = $this->returnedQuantityForItem((int) $item->id);
                $ordered = (int) $item->jumlah;
                $isComplete = $ordered > 0 && $returned >= $ordered;

                if ($returned > 0) {
                    $anyReturned = true;
                }

                if (!$isComplete) {
                    $allReturned = false;
                }

                // Status item yang dikenal aplikasi: dipinjam/dikembalikan.
                $newItemStatus = $isComplete ? 'dikembalikan' : 'dipinjam';

                if ($item->status !== $newItemStatus) {
                    $item->update(['status' => $newItemStatus]);
                }
            }

            $newStatus = $allReturned
                ? 'dikembalikan'
                : ($anyReturned ? 'sebagian_dikembalikan' : 'dipinjam');

            if ($pengambilan->status !== $newStatus) {
                $pengambilan->update(['status' => $newStatus]);
            }

            return;
        }

        // Kompatibilitas transaksi lama yang masih memakai alat_id/jumlah.
        $ordered = (int) ($pengambilan->jumlah ?? 0);
        $returned = $this->returnedQuantityForLegacy((int) $pengambilan->id);

        $newStatus = $ordered > 0 && $returned >= $ordered
            ? 'dikembalikan'
            : ($returned > 0 ? 'sebagian_dikembalikan' : 'dipinjam');

        if ($pengambilan->status !== $newStatus) {
            $pengambilan->update(['status' => $newStatus]);
        }
    }

    /* ================= INDEX ================= */
        /* ================= INDEX ================= */
    public function index(Request $request)
    {
        $query = PengembalianAlat::with([
            'pengambilan.alat',
            'pengambilan.user',
            'pengambilan.bagian',
            'pengambilan.items.alat',
            'item.alat',
            'user',
        ]);

        // User biasa hanya melihat pengembalian miliknya sendiri.
        if (!$this->isAdminOrSuper()) {
            $query->where('user_id', Auth::id());
        }

        // Pencarian
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('pengambilan.alat', function ($alatQuery) use ($search) {
                    $alatQuery->where('nama_alat', 'like', "%{$search}%")
                        ->orWhere('merk', 'like', "%{$search}%")
                        ->orWhere('no_seri', 'like', "%{$search}%");
                })->orWhereHas('pengambilan.items.alat', function ($alatQuery) use ($search) {
                    $alatQuery->where('nama_alat', 'like', "%{$search}%")
                        ->orWhere('merk', 'like', "%{$search}%")
                        ->orWhere('no_seri', 'like', "%{$search}%");
                })->orWhereHas('pengambilan', function ($pQuery) use ($search) {
                    $pQuery->where('nama_peminjam', 'like', "%{$search}%");
                })->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter Tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_pengembalian', $request->input('tanggal'));
        }

        // Filter Status Keterlambatan (Pill: Tepat Waktu / Terlambat)
        if ($request->filled('status') && in_array($request->status, ['ontime', 'late'])) {
            $query->whereHas('pengambilan', function ($p) {
                $p->whereNotNull('tanggal_jatuh_tempo');
            });

            if ($request->status === 'late') {
                $query->whereRaw('DATE(tanggal_pengembalian) > (SELECT DATE(tanggal_jatuh_tempo) FROM pengambilan_alats WHERE pengambilan_alats.id = pengembalian_alats.pengambilan_alat_id)');
            } else {
                $query->whereRaw('DATE(tanggal_pengembalian) <= (SELECT DATE(tanggal_jatuh_tempo) FROM pengambilan_alats WHERE pengambilan_alats.id = pengembalian_alats.pengambilan_alat_id)');
            }
        }

        $data = $query->latest('tanggal_pengembalian')->paginate(10)->withQueryString();

        return view('pengembalian_alat.index', compact('data'));
    }
    /* ================= CREATE ================= */
    public function create(Request $request, string $hashid)
    {
        $this->ensureAdminOrSuper();

        $pengambilan = PengambilanAlat::with([
            'user',
            'bagian',
            'alat',
            'items.alat',
        ])->findOrFail($this->resolveHashid($hashid));

        $items = $pengambilan->items;
        $isLegacy = $items->isEmpty();
        $selectedItem = null;
        $legacyRemaining = 0;

        if ($isLegacy) {
            $legacyRemaining = max(
                0,
                (int) ($pengambilan->jumlah ?? 0)
                    - $this->returnedQuantityForLegacy((int) $pengambilan->id)
            );
        } else {
            foreach ($items as $item) {
                $returned = $this->returnedQuantityForItem((int) $item->id);
                $item->setAttribute('jumlah_dikembalikan', $returned);
                $item->setAttribute(
                    'sisa_pengembalian',
                    max(0, (int) $item->jumlah - $returned)
                );
            }

            if ($request->filled('item')) {
                $itemId = $this->resolveHashid((string) $request->query('item'));
                $selectedItem = $items->firstWhere('id', $itemId);

                abort_unless($selectedItem, 404, 'Item tidak ditemukan pada batch ini.');
                abort_if((int) $selectedItem->sisa_pengembalian <= 0, 422, 'Item ini sudah dikembalikan seluruhnya.');
            }
        }

        return view('pengembalian_alat.create', compact(
            'pengambilan',
            'items',
            'selectedItem',
            'isLegacy',
            'legacyRemaining'
        ));
    }

    /* ================= STORE ================= */
        /* ================= STORE ================= */
    public function store(Request $request, string $hashid)
    {
        $this->ensureAdminOrSuper();

        $pengambilanId = $this->resolveHashid($hashid);
        $pengambilan = PengambilanAlat::with('items')->findOrFail($pengambilanId);
        $isBatch = $pengambilan->items->isNotEmpty();

        $returnDate = (string) $request->input('tanggal_pengembalian');
        $isLate = $request->filled('tanggal_pengembalian')
            ? $this->isLateForDate($pengambilan, $returnDate)
            : false;

        $rules = [
            'tanggal_pengembalian' => ['required', 'date'],
            'keterangan' => $isLate
                ? ['required', 'string', 'min:10']
                : ['nullable', 'string'],
            'foto' => $isLate
                ? ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048']
                : ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];

        if ($isBatch) {
            $rules['items'] = ['required', 'array'];
            $rules['items.*.jumlah'] = ['nullable', 'integer', 'min:1'];
        } else {
            $rules['jumlah'] = ['required', 'integer', 'min:1'];
        }

        $validated = $request->validate($rules, [
            'tanggal_pengembalian.required' => 'Tanggal pengembalian wajib diisi.',
            'items.required' => 'Pilih minimal satu alat yang akan dikembalikan.',
            'items.*.jumlah.min' => 'Jumlah pengembalian minimal 1.',
            'jumlah.required' => 'Jumlah pengembalian wajib diisi.',
            'jumlah.min' => 'Jumlah pengembalian minimal 1.',
            'keterangan.required' => 'Keterangan wajib diisi karena pengembalian melewati tenggat waktu.',
            'keterangan.min' => 'Keterangan minimal 10 karakter.',
            'foto.required' => 'Foto bukti wajib diunggah karena pengembalian melewati tenggat waktu.',
        ]);

        $fotoPath = null;

        try {
            if ($request->hasFile('foto')) {
                $fotoPath = $this->uploadFotoTransaksi(
                    $request->file('foto'),
                    'pengembalian'
                );
            }

            DB::transaction(function () use (
                $pengambilanId,
                $isBatch,
                $validated,
                $request,
                &$fotoPath
            ) {
                $pengambilan = PengambilanAlat::query()
                    ->lockForUpdate()
                    ->findOrFail($pengambilanId);

                if ($isBatch) {
                    $submittedItems = $request->input('items', []);
                    $itemsToProcess = [];

                    // Filter hanya item yang dicentang (selected == 1) dan memiliki jumlah > 0
                    foreach ($submittedItems as $itemId => $data) {
                        if (isset($data['selected']) && $data['selected'] == 1 && isset($data['jumlah']) && (int)$data['jumlah'] > 0) {
                            $itemsToProcess[] = [
                                'item_id' => (int)$itemId,
                                'jumlah' => (int)$data['jumlah']
                            ];
                        }
                    }

                    if (empty($itemsToProcess)) {
                        throw ValidationException::withMessages([
                            'items' => 'Pilih minimal satu alat yang akan dikembalikan.'
                        ]);
                    }

                    // Proses pengembalian untuk setiap alat yang dipilih
                    foreach ($itemsToProcess as $processItem) {
                        $item = PengambilanAlatItem::query()
                            ->where('pengambilan_alat_id', $pengambilan->id)
                            ->whereKey($processItem['item_id'])
                            ->lockForUpdate()
                            ->firstOrFail();

                        $totalReturned = $this->returnedQuantityForItem((int) $item->id);
                        $remaining = (int) $item->jumlah - $totalReturned;
                        $quantity = $processItem['jumlah'];

                        if ($quantity > $remaining) {
                            throw ValidationException::withMessages([
                                "items.{$item->id}.jumlah" => "Jumlah untuk {$item->alat->nama_alat} melebihi sisa. Sisa: {$remaining}.",
                            ]);
                        }

                        $alatId = (int) $item->alat_id;
                        $alat = Alat::query()->lockForUpdate()->findOrFail($alatId);

                        PengembalianAlat::create([
                            'pengambilan_alat_id' => $pengambilan->id,
                            'pengambilan_alat_item_id' => $item->id,
                            'user_id' => Auth::id(),
                            'jumlah' => $quantity,
                            'tanggal_pengembalian' => $validated['tanggal_pengembalian'],
                            'keterangan' => $validated['keterangan'] ?? null,
                            'foto' => $fotoPath,
                        ]);

                        // Stok ditambahkan untuk alat yang benar-benar dikembalikan
                        $alat->increment('jumlah', $quantity);
                    }
                } else {
                    // Logika untuk transaksi lama (Legacy)
                    $totalReturned = $this->returnedQuantityForLegacy((int) $pengambilan->id);
                    $remaining = (int) $pengambilan->jumlah - $totalReturned;
                    $quantity = (int) $validated['jumlah'];

                    if ($quantity > $remaining) {
                        throw ValidationException::withMessages([
                            'jumlah' => "Jumlah melebihi sisa pinjaman. Sisa yang dapat dikembalikan: {$remaining}.",
                        ]);
                    }

                    if (!$pengambilan->alat_id || $remaining <= 0) {
                        throw ValidationException::withMessages([
                            'jumlah' => 'Tidak ada alat yang masih dapat dikembalikan.',
                        ]);
                    }

                    $alatId = (int) $pengambilan->alat_id;
                    $alat = Alat::query()->lockForUpdate()->findOrFail($alatId);

                    PengembalianAlat::create([
                        'pengambilan_alat_id' => $pengambilan->id,
                        'pengambilan_alat_item_id' => null,
                        'user_id' => Auth::id(),
                        'jumlah' => $quantity,
                        'tanggal_pengembalian' => $validated['tanggal_pengembalian'],
                        'keterangan' => $validated['keterangan'] ?? null,
                        'foto' => $fotoPath,
                    ]);

                    $alat->increment('jumlah', $quantity);
                }

                // Sinkronkan status batch setelah semua item diproses
                $this->syncPengambilanStatus($pengambilan);
            });

            $pengambilan = PengambilanAlat::findOrFail($pengambilanId);

            return redirect()
                ->route('pengambilan_alat.show', $pengambilan->hashid)
                ->with('success', 'Pengembalian alat berhasil disimpan.');

        } catch (ValidationException $e) {
            if ($fotoPath) {
                $this->deleteFotoTransaksi($fotoPath, 'pengembalian');
            }
            throw $e;
        } catch (Throwable $e) {
            if ($fotoPath) {
                $this->deleteFotoTransaksi($fotoPath, 'pengembalian');
            }

            Log::error('Gagal menyimpan pengembalian alat', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'pengambilan_id' => $pengambilanId,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan pengembalian alat. Silakan periksa log aplikasi.');
        }
    }
    /* ================= SHOW ================= */
    public function show(string $hashid)
    {
        $data = PengembalianAlat::with([
            'pengambilan.alat',
            'pengambilan.items.alat',
            'item.alat',
            'user',
        ])->findOrFail($this->resolveHashid($hashid));

        if (!$this->isAdminOrSuper() && (int) Auth::id() !== (int) $data->user_id) {
            abort(403);
        }

        return view('pengembalian_alat.show', compact('data'));
    }

    /* ================= EDIT ================= */
    public function edit(string $hashid)
    {
        $this->ensureAdminOrSuper();

        $data = PengembalianAlat::with([
            'pengambilan.alat',
            'pengambilan.items.alat',
            'item.alat',
        ])->findOrFail($this->resolveHashid($hashid));

        return view('pengembalian_alat.edit', compact('data'));
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, string $hashid)
    {
        $this->ensureAdminOrSuper();

        $id = $this->resolveHashid($hashid);
        $data = PengembalianAlat::with('pengambilan')
            ->findOrFail($id);

        $pengambilan = $data->pengambilan;
        abort_unless($pengambilan, 404, 'Transaksi pengambilan tidak ditemukan.');

        $returnDate = (string) $request->input('tanggal_pengembalian');
        $isLate = $request->filled('tanggal_pengembalian')
            ? $this->isLateForDate($pengambilan, $returnDate)
            : false;

        $rules = [
            'tanggal_pengembalian' => ['required', 'date'],
            'keterangan' => $isLate
                ? ['required', 'string', 'min:10']
                : ['nullable', 'string'],
            'foto' => $isLate && !$data->foto
                ? ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048']
                : ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];

        $validated = $request->validate($rules, [
            'keterangan.required' => 'Keterangan wajib diisi karena pengembalian terlambat.',
            'keterangan.min' => 'Keterangan minimal 10 karakter.',
            'foto.required' => 'Foto bukti wajib diunggah karena pengembalian terlambat.',
        ]);

        $newFotoPath = null;
        $oldFotoPath = $data->foto;

        try {
            if ($request->hasFile('foto')) {
                $newFotoPath = $this->uploadFotoTransaksi(
                    $request->file('foto'),
                    'pengembalian'
                );
            }

            DB::transaction(function () use ($id, $validated, $newFotoPath) {
                $return = PengembalianAlat::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                $return->update([
                    'tanggal_pengembalian' => $validated['tanggal_pengembalian'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'foto' => $newFotoPath ?: $return->foto,
                ]);

                if ($return->pengambilan) {
                    $this->syncPengambilanStatus($return->pengambilan);
                }
            });

            if ($newFotoPath && $oldFotoPath) {
                $this->deleteFotoTransaksi($oldFotoPath, 'pengembalian');
            }

            return redirect()
                ->route('pengembalian_alat.index')
                ->with('success', 'Data pengembalian berhasil diperbarui.');
        } catch (Throwable $e) {
            if ($newFotoPath) {
                $this->deleteFotoTransaksi($newFotoPath, 'pengembalian');
            }

            Log::error('Gagal memperbarui pengembalian alat', [
                'message' => $e->getMessage(),
                'return_id' => $id,
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui pengembalian alat. Silakan periksa log aplikasi.');
        }
    }

    /* ================= DESTROY ================= */
    public function destroy(string $hashid)
    {
        $this->ensureAdminOrSuper();

        $id = $this->resolveHashid($hashid);
        $return = PengembalianAlat::with(['pengambilan', 'item'])
            ->findOrFail($id);

        $fotoPath = $return->foto;

        try {
            DB::transaction(function () use ($id) {
                $return = PengembalianAlat::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                $pengambilan = PengambilanAlat::query()
                    ->lockForUpdate()
                    ->findOrFail($return->pengambilan_alat_id);

                // Tentukan alat yang stoknya harus dikurangi kembali.
                if ($return->pengambilan_alat_item_id) {
                    $item = PengambilanAlatItem::query()
                        ->where('pengambilan_alat_id', $pengambilan->id)
                        ->lockForUpdate()
                        ->findOrFail($return->pengambilan_alat_item_id);

                    $alatId = (int) $item->alat_id;
                } else {
                    // Kompatibilitas riwayat transaksi model lama.
                    $alatId = (int) $pengambilan->alat_id;
                }

                $alat = Alat::query()
                    ->lockForUpdate()
                    ->findOrFail($alatId);

                if ((int) $alat->jumlah < (int) $return->jumlah) {
                    throw ValidationException::withMessages([
                        'jumlah' => 'Pengembalian tidak dapat dihapus karena stok alat sudah digunakan kembali dan tidak mencukupi untuk pembatalan.',
                    ]);
                }

                // Batalkan penambahan stok dari transaksi pengembalian ini.
                $alat->decrement('jumlah', (int) $return->jumlah);

                $return->delete();

                $this->syncPengambilanStatus($pengambilan);
            });

            if ($fotoPath) {
                $this->deleteFotoTransaksi($fotoPath, 'pengembalian');
            }

            return back()->with('success', 'Data pengembalian berhasil dihapus dan stok disesuaikan.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Gagal menghapus pengembalian alat', [
                'message' => $e->getMessage(),
                'return_id' => $id,
            ]);

            return back()->with('error', 'Gagal menghapus pengembalian alat. Silakan periksa log aplikasi.');
        }
    }

    /* ================= EXPORT PDF ================= */
    public function exportPdf(?string $hashid = null)
    {
        $this->ensureAdminOrSuper();

        $relations = [
            'user',
            'pengambilan.alat',
            'pengambilan.items.alat',
            'item.alat',
        ];

        if ($hashid) {
            $data = PengembalianAlat::with($relations)
                ->findOrFail($this->resolveHashid($hashid));
            $list = collect([$data]);
        } else {
            $list = PengembalianAlat::with($relations)
                ->latest('tanggal_pengembalian')
                ->get();
        }

        $pdf = Pdf::loadView('pengembalian_alat.export-pdf', compact('list'));

        return $pdf->download('pengembalian_alat.pdf');
    }

    /* ================= FOTO HELPER ================= */

    /**
     * Simpan foto bukti dan salinan thumbnail.
     * Thumbnail di sini berupa salinan file original untuk kompatibilitas
     * dengan accessor foto_thumb yang sudah digunakan pada aplikasi.
     */
    protected function uploadFotoTransaksi($file, string $folder): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $filename = now()->format('YmdHis') . '_' . Str::random(12) . '.' . $extension;
        $disk = Storage::disk('public');
        $path = $folder . '/' . $filename;
        $thumbPath = $folder . '/thumb_' . $filename;

        try {
            $storedPath = $disk->putFileAs($folder, $file, $filename);

            if (!$storedPath) {
                throw new \RuntimeException('File foto gagal disimpan.');
            }

            if (!$disk->copy($path, $thumbPath)) {
                Log::warning('Salinan thumbnail foto gagal dibuat.', [
                    'path' => $path,
                    'thumb' => $thumbPath,
                ]);
            }

            return $filename;
        } catch (Throwable $e) {
            $disk->delete([$path, $thumbPath]);
            Log::error('Gagal upload foto pengembalian.', [
                'message' => $e->getMessage(),
                'folder' => $folder,
            ]);

            throw $e;
        }
    }

    protected function deleteFotoTransaksi(?string $filename, string $folder): void
    {
        if (!$filename) {
            return;
        }

        Storage::disk('public')->delete([
            $folder . '/' . $filename,
            $folder . '/thumb_' . $filename,
        ]);
    }
}
