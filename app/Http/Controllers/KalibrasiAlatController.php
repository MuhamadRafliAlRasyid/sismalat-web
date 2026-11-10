<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\KalibrasiAlat;
use App\Services\HashIdService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class KalibrasiAlatController extends Controller
{
    private const CERTIFICATE_DIRECTORY = 'kalibrasi/sertifikat';
    private const CERTIFICATE_MAX_KB = 5120;

    /** Decode hashid menjadi primary key. */
    protected function decode(string $hashid): int
    {
        $id = app(HashIdService::class)->decode($hashid);
        abort_if(!$id, 404);

        return (int) $id;
    }

    /**
     * FIX: Hanya Admin dan Super Admin yang dapat mengelola kalibrasi.
     * Menggunakan pola konsisten dengan controller lain (in_array role).
     */
    protected function ensureAdminOrSuper(): void
    {
        abort_unless(
            Auth::check() && in_array(Auth::user()->role ?? '', ['admin', 'super'], true),
            403,
            'Anda tidak memiliki izin untuk mengelola data kalibrasi.'
        );
    }

    /** Query riwayat kalibrasi terbaru untuk satu alat. */
    protected function latestCalibrationQuery(int $alatId): Builder
    {
        return KalibrasiAlat::query()
            ->where('alat_id', $alatId)
            ->orderByDesc('tanggal_kalibrasi')
            ->orderByDesc('id');
    }

    /** Validasi bersama untuk data kalibrasi dan lampiran sertifikat. */
    protected function validateCalibration(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'tanggal_kalibrasi' => ['required', 'date', 'before_or_equal:today'],
            'masa_berlaku_baru' => ['required', 'date', 'after:tanggal_kalibrasi'],
            'no_sertifikat' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'bukti_sertifikat' => [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'mimes:jpg,jpeg,png,webp,pdf',
                'max:' . self::CERTIFICATE_MAX_KB,
            ],
        ];

        return $request->validate($rules, [
            'tanggal_kalibrasi.required' => 'Tanggal kalibrasi wajib diisi.',
            'tanggal_kalibrasi.date' => 'Tanggal kalibrasi tidak valid.',
            'tanggal_kalibrasi.before_or_equal' => 'Tanggal kalibrasi tidak boleh melewati hari ini.',
            'masa_berlaku_baru.required' => 'Tanggal masa berlaku wajib diisi.',
            'masa_berlaku_baru.after' => 'Masa berlaku harus setelah tanggal kalibrasi.',
            'no_sertifikat.max' => 'Nomor sertifikat maksimal 255 karakter.',
            'keterangan.max' => 'Keterangan maksimal 5.000 karakter.',
            'bukti_sertifikat.required' => 'Bukti sertifikat kalibrasi wajib diunggah.',
            'bukti_sertifikat.file' => 'Bukti sertifikat harus berupa file yang valid.',
            'bukti_sertifikat.mimes' => 'Bukti sertifikat harus berupa JPG, JPEG, PNG, WEBP, atau PDF.',
            'bukti_sertifikat.max' => 'Ukuran bukti sertifikat maksimal 5 MB.',
        ]);
    }

    /** Simpan bukti sertifikat pada disk local (private). */
    protected function storeCertificate(Request $request): ?string
    {
        if (!$request->hasFile('bukti_sertifikat')) {
            return null;
        }

        return $request->file('bukti_sertifikat')
            ->store(self::CERTIFICATE_DIRECTORY, 'local');
    }

    /** Hapus file sertifikat dari disk local. */
    protected function deleteCertificate(?string $path): void
    {
        if ($path && str_starts_with($path, self::CERTIFICATE_DIRECTORY . '/')) {
            Storage::disk('local')->delete($path);
        }
    }

    /** Sinkronkan masa berlaku alat berdasarkan catatan kalibrasi terbaru. */
    protected function syncMasaBerlaku(Alat $alat): bool
    {
        $latest = $this->latestCalibrationQuery($alat->id)->first();
        $newExpiry = $latest?->masa_berlaku_baru?->toDateString();
        $currentExpiry = $alat->masa_berlaku
            ? Carbon::parse($alat->masa_berlaku)->toDateString()
            : null;

        if ($currentExpiry === $newExpiry) {
            return false;
        }

        $alat->update([
            'masa_berlaku' => $newExpiry,
            'last_notified_at' => null,
        ]);

        return true;
    }

    /**
     * FIX: Tandai notifikasi kalibrasi lama sebagai sudah dibaca.
     * Menggunakan JSON operator `->` untuk kompatibilitas lintas DB driver.
     */
    protected function markCalibrationNotificationsAsRead(int $alatId): void
    {
        DB::table('notifications')
            ->where('type', 'App\\Notifications\\AlatExpiredNotification')
            ->whereNull('read_at')
            ->where(function ($q) use ($alatId) {
                // Coba 2 pola: data->alat_id dan data->alat_hashid
                $q->where(DB::raw("JSON_EXTRACT(data, '$.alat_id')"), $alatId)
                  ->orWhere(DB::raw("JSON_EXTRACT(data, '$.alat_id')"), (string) $alatId);
            })
            ->update(['read_at' => now()]);
    }

    /** Pastikan tidak ada dua catatan pada tanggal kalibrasi yang sama untuk satu alat. */
    protected function ensureUniqueCalibrationDate(
        int $alatId,
        string $tanggalKalibrasi,
        ?int $exceptId = null
    ): void {
        $query = KalibrasiAlat::query()
            ->where('alat_id', $alatId)
            ->whereDate('tanggal_kalibrasi', $tanggalKalibrasi);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'tanggal_kalibrasi' => 'Sudah ada catatan kalibrasi untuk alat ini pada tanggal tersebut.',
            ]);
        }
    }

    /** Masa berlaku harus lebih akhir daripada catatan kalibrasi sebelumnya. */
    protected function ensureExpiryAfterPreviousCalibration(
        int $alatId,
        string $tanggalKalibrasi,
        string $masaBerlakuBaru,
        ?int $exceptId = null
    ): void {
        $query = KalibrasiAlat::query()
            ->where('alat_id', $alatId)
            ->whereDate('tanggal_kalibrasi', '<', $tanggalKalibrasi)
            ->orderByDesc('tanggal_kalibrasi')
            ->orderByDesc('id');

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $previous = $query->first();

        if (
            $previous
            && $previous->masa_berlaku_baru
            && Carbon::parse($masaBerlakuBaru)->lessThanOrEqualTo(
                Carbon::parse($previous->masa_berlaku_baru)
            )
        ) {
            throw ValidationException::withMessages([
                'masa_berlaku_baru' => 'Masa berlaku harus lebih akhir daripada masa berlaku kalibrasi sebelumnya.',
            ]);
        }
    }

    /* ================= INDEX ================= */

    public function index(Request $request)
    {
        // FIX: Tambahkan eager load ruangan agar kolom ruangan di view terisi
        $query = KalibrasiAlat::query()->with(['alat.ruangan', 'alat.kategori']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function (Builder $builder) use ($search) {
                $builder
                    ->whereHas('alat', function (Builder $alatQuery) use ($search) {
                        $alatQuery
                            ->where('nama_alat', 'like', "%{$search}%")
                            ->orWhere('merk', 'like', "%{$search}%")
                            ->orWhere('tipe', 'like', "%{$search}%")
                            ->orWhere('no_seri', 'like', "%{$search}%")
                            ->orWhere('no_identitas', 'like', "%{$search}%");
                    })
                    ->orWhere('no_sertifikat', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('tanggal_kalibrasi', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('tanggal_kalibrasi', '<=', $request->date_to);
        }

        $data = $query
            ->orderByDesc('tanggal_kalibrasi')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $filters = $request->only(['search', 'date_from', 'date_to']);

        return view('kalibrasis.index', compact('data', 'filters'));
    }

    /* ================= CREATE ================= */

    public function create(string $hashid)
    {
        $this->ensureAdminOrSuper();

        // FIX: Eager load relasi yang diperlukan di form
        $alat = Alat::with(['kategori', 'ruangan'])->findOrFail($this->decode($hashid));
        $lastKalibrasi = $this->latestCalibrationQuery($alat->id)->first();

        return view('kalibrasis.create', compact('alat', 'lastKalibrasi'));
    }

    /* ================= STORE ================= */

    public function store(Request $request, string $hashid)
    {
        $this->ensureAdminOrSuper();

        $validated = $this->validateCalibration($request);
        $alatId = $this->decode($hashid);
        $certificatePath = $this->storeCertificate($request);

        try {
            $kalibrasi = DB::transaction(function () use ($alatId, $validated, $certificatePath) {
                $alat = Alat::query()->lockForUpdate()->findOrFail($alatId);

                $tanggal = Carbon::parse($validated['tanggal_kalibrasi'])->toDateString();
                $masaBerlaku = Carbon::parse($validated['masa_berlaku_baru'])->toDateString();

                $latest = $this->latestCalibrationQuery($alat->id)->lockForUpdate()->first();

                if (
                    $latest
                    && Carbon::parse($tanggal)->lessThan(
                        Carbon::parse($latest->tanggal_kalibrasi)->toDateString()
                    )
                ) {
                    throw ValidationException::withMessages([
                        'tanggal_kalibrasi' => 'Tanggal kalibrasi baru tidak boleh lebih lama daripada catatan kalibrasi terakhir.',
                    ]);
                }

                $this->ensureUniqueCalibrationDate($alat->id, $tanggal);
                $this->ensureExpiryAfterPreviousCalibration($alat->id, $tanggal, $masaBerlaku);

                $record = KalibrasiAlat::create([
                    'alat_id' => $alat->id,
                    'tanggal_kalibrasi' => $tanggal,
                    'masa_berlaku_baru' => $masaBerlaku,
                    'no_sertifikat' => $validated['no_sertifikat'] ?? null,
                    'keterangan' => $validated['keterangan'] ?? null,
                    'bukti_sertifikat' => $certificatePath,
                ]);

                if ($this->syncMasaBerlaku($alat)) {
                    $this->markCalibrationNotificationsAsRead($alat->id);
                }

                return $record;
            });

            return redirect()
                ->route('kalibrasis.show', $kalibrasi->hashid)
                ->with('success', 'Data kalibrasi dan bukti sertifikat berhasil disimpan. Masa berlaku alat telah diperbarui.');
        } catch (ValidationException $exception) {
            $this->deleteCertificate($certificatePath);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteCertificate($certificatePath);

            Log::error('Gagal menyimpan kalibrasi alat.', [
                'alat_id' => $alatId,
                'user_id' => Auth::id(),
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Kalibrasi belum dapat disimpan. Silakan periksa kembali data atau hubungi administrator.');
        }
    }

    /* ================= SHOW ================= */

    public function show(string $hashid)
    {
        // FIX: Eager load semua relasi yang dibutuhkan view detail
        $data = KalibrasiAlat::with(['alat.kategori', 'alat.ruangan'])
            ->findOrFail($this->decode($hashid));

        // Attach helper accessor untuk view (bukti_sertifikat_is_pdf & bukti_sertifikat_name)
        if ($data->bukti_sertifikat) {
            $ext = strtolower(pathinfo($data->bukti_sertifikat, PATHINFO_EXTENSION));
            $data->setAttribute('bukti_sertifikat_is_pdf', $ext === 'pdf');
            $data->setAttribute('bukti_sertifikat_name', basename($data->bukti_sertifikat));
        }

        return view('kalibrasis.show', compact('data'));
    }

    /**
     * Tampilkan/unduh bukti sertifikat melalui route terproteksi.
     * File tidak diletakkan pada direktori publik.
     */
    public function sertifikat(string $hashid)
    {
        $this->ensureAdminOrSuper();

        $data = KalibrasiAlat::query()->findOrFail($this->decode($hashid));
        abort_unless($data->bukti_sertifikat, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($data->bukti_sertifikat), 404);

        $path = $disk->path($data->bukti_sertifikat);
        $mimeType = $disk->mimeType($data->bukti_sertifikat) ?: 'application/octet-stream';
        $filename = basename($data->bukti_sertifikat);
        $fileSize = $disk->size($data->bukti_sertifikat);

        // FIX: Tambahkan Content-Length agar download tidak corrupt
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Content-Disposition' => 'inline; filename="' . addslashes($filename) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    /* ================= EDIT ================= */

    public function edit(string $hashid)
    {
        $this->ensureAdminOrSuper();

        // FIX: Eager load relasi yang diperlukan di form edit
        $data = KalibrasiAlat::with(['alat.kategori', 'alat.ruangan'])
            ->findOrFail($this->decode($hashid));

        return view('kalibrasis.edit', compact('data'));
    }

    /* ================= UPDATE ================= */

    public function update(Request $request, string $hashid)
    {
        $this->ensureAdminOrSuper();

        $validated = $this->validateCalibration($request, true);
        $id = $this->decode($hashid);
        $newCertificatePath = $this->storeCertificate($request);

        try {
            $oldCertificatePath = null;

            DB::transaction(function () use ($id, $validated, $newCertificatePath, &$oldCertificatePath) {
                $data = KalibrasiAlat::query()->lockForUpdate()->findOrFail($id);
                $alat = Alat::query()->lockForUpdate()->findOrFail($data->alat_id);

                $tanggal = Carbon::parse($validated['tanggal_kalibrasi'])->toDateString();
                $masaBerlaku = Carbon::parse($validated['masa_berlaku_baru'])->toDateString();

                $this->ensureUniqueCalibrationDate($alat->id, $tanggal, $data->id);
                $this->ensureExpiryAfterPreviousCalibration($alat->id, $tanggal, $masaBerlaku, $data->id);

                $changes = [
                    'tanggal_kalibrasi' => $tanggal,
                    'masa_berlaku_baru' => $masaBerlaku,
                    'no_sertifikat' => $validated['no_sertifikat'] ?? null,
                    'keterangan' => $validated['keterangan'] ?? null,
                ];

                if ($newCertificatePath !== null) {
                    $oldCertificatePath = $data->bukti_sertifikat;
                    $changes['bukti_sertifikat'] = $newCertificatePath;
                }

                $data->update($changes);

                if ($this->syncMasaBerlaku($alat)) {
                    $this->markCalibrationNotificationsAsRead($alat->id);
                }
            });

            if ($newCertificatePath !== null && $oldCertificatePath) {
                $this->deleteCertificate($oldCertificatePath);
            }

            $updated = KalibrasiAlat::findOrFail($id);

            return redirect()
                ->route('kalibrasis.show', $updated->hashid)
                ->with('success', 'Data kalibrasi berhasil diperbarui.');
        } catch (ValidationException $exception) {
            $this->deleteCertificate($newCertificatePath);
            throw $exception;
        } catch (Throwable $exception) {
            $this->deleteCertificate($newCertificatePath);

            Log::error('Gagal memperbarui kalibrasi alat.', [
                'kalibrasi_id' => $id,
                'user_id' => Auth::id(),
                'message' => $exception->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Perubahan kalibrasi belum dapat disimpan. File sertifikat sebelumnya tetap dipertahankan.');
        }
    }

    /* ================= DELETE ================= */

    public function destroy(string $hashid)
    {
        $this->ensureAdminOrSuper();

        $id = $this->decode($hashid);

        try {
            $certificatePath = null;

            DB::transaction(function () use ($id, &$certificatePath) {
                $data = KalibrasiAlat::query()->lockForUpdate()->findOrFail($id);
                $alat = Alat::query()->lockForUpdate()->findOrFail($data->alat_id);

                $certificatePath = $data->bukti_sertifikat;
                $data->delete();

                if ($this->syncMasaBerlaku($alat)) {
                    $this->markCalibrationNotificationsAsRead($alat->id);
                }
            });

            $this->deleteCertificate($certificatePath);

            return redirect()
                ->route('kalibrasis.index')
                ->with('success', 'Catatan kalibrasi dan file bukti sertifikat berhasil dihapus. Masa berlaku alat telah diperbarui.');
        } catch (Throwable $exception) {
            Log::error('Gagal menghapus kalibrasi alat.', [
                'kalibrasi_id' => $id,
                'user_id' => Auth::id(),
                'message' => $exception->getMessage(),
            ]);

            return back()->with(
                'error',
                'Catatan kalibrasi belum dapat dihapus. Silakan coba kembali atau hubungi administrator.'
            );
        }
    }
}
