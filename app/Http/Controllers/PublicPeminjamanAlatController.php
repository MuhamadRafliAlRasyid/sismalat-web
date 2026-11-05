<?php

namespace App\Http\Controllers;

use App\Mail\PeminjamanAlatTerkonfirmasi;
use App\Models\Alat;
use App\Models\PengambilanAlat;
use App\Models\PengambilanAlatItem;
use App\Services\HashIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PublicPeminjamanAlatController extends Controller
{
    private const MAX_ITEMS = 15;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipe_peminjam' => [
                'required',
                Rule::in(['internal', 'eksternal']),
            ],
            'nama_peminjam' => [
                'required',
                'string',
                'max:255',
            ],
            'email_peminjam' => [
                'nullable',
                'email',
                'max:255',
                'required_if:kanal_notifikasi,email',
            ],
            'telepon_peminjam' => [
                'nullable',
                'string',
                'max:30',
                'required_if:kanal_notifikasi,whatsapp',
            ],
            'instansi_peminjam' => [
                'nullable',
                'string',
                'max:255',
            ],
            'bagian_id' => [
                'nullable',
                'integer',
                'exists:bagians,id',
            ],
            'kanal_notifikasi' => [
                'required',
                Rule::in(['email', 'whatsapp']),
            ],
            'items' => [
                'required',
                'array',
                'min:1',
                'max:' . self::MAX_ITEMS,
            ],
            'items.*.alat_id' => [
                'required',
                'string',
                'distinct',
            ],
            'items.*.jumlah' => [
                'required',
                'integer',
                'min:1',
            ],
            'keperluan' => [
                'required',
                'string',
                'max:2000',
            ],
            'waktu_pengambilan' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'lama_pinjam' => [
                'required',
                'integer',
                'min:1',
                'max:365',
            ],
        ], [
            'items.required' => 'Pilih minimal satu alat.',
            'items.min' => 'Pilih minimal satu alat.',
            'items.max' => 'Maksimal 15 jenis alat per transaksi.',
            'items.*.alat_id.distinct' =>
                'Alat yang sama tidak boleh ditambahkan dua kali. Silakan ubah jumlahnya di keranjang.',
            'items.*.jumlah.min' =>
                'Jumlah setiap alat minimal satu.',
        ]);

        /*
         * Decode HashID dari form menjadi ID database.
         */
        $hashService = app(HashIdService::class);
        $resolvedItems = [];

        foreach ($validated['items'] as $index => $item) {
            $alatId = $hashService->decode($item['alat_id']);

            if (!$alatId) {
                return back()
                    ->withInput()
                    ->withErrors([
                        "items.$index.alat_id" =>
                            'Salah satu alat yang dipilih tidak valid.',
                    ]);
            }

            $resolvedItems[] = [
                'alat_id' => $alatId,
                'jumlah' => (int) $item['jumlah'],
            ];
        }

        $token = Str::random(64);
        $trackingHash = hash('sha256', $token);

        try {
            $peminjaman = DB::transaction(function () use (
                $validated,
                $resolvedItems,
                $trackingHash
            ) {
                /*
                 * Kunci baris alat untuk mencegah stok digunakan
                 * secara bersamaan oleh transaksi lain.
                 */
                $ids = array_values(array_unique(
                    array_column($resolvedItems, 'alat_id')
                ));

                sort($ids);

                $alats = Alat::query()
                    ->whereNull('deleted_at')
                    ->whereIn('id', $ids)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($alats->count() !== count($ids)) {
                    throw new \DomainException(
                        'Salah satu alat tidak ditemukan atau sudah tidak tersedia.'
                    );
                }

                /*
                 * Periksa seluruh stok sebelum menyimpan peminjaman
                 * atau mengurangi stok alat.
                 */
                foreach ($resolvedItems as $item) {
                    $alat = $alats->get($item['alat_id']);

                    if (!$alat || $alat->jumlah < $item['jumlah']) {
                        $nama = $alat?->nama_alat ?? 'Alat yang dipilih';
                        $stok = $alat?->jumlah ?? 0;

                        throw new \DomainException(
                            "Stok {$nama} tidak mencukupi. Stok tersedia: {$stok}."
                        );
                    }
                }

                $user = Auth::user();

                $userId = (
                    $validated['tipe_peminjam'] === 'internal'
                    && $user
                ) ? $user->id : null;

                $bagianId = null;

                if ($validated['tipe_peminjam'] === 'internal') {
                    $bagianId = $user?->bagian_id
                        ?? ($validated['bagian_id'] ?? null);
                }

                /*
                 * Simpan informasi transaksi utama.
                 *
                 * PENTING:
                 * Jangan menyimpan alat_id, jumlah, atau satuan
                 * di tabel pengambilan_alats karena kolom tersebut
                 * tidak tersedia pada skema database saat ini.
                 *
                 * Daftar alat disimpan pada tabel detail.
                 */
                $peminjaman = PengambilanAlat::create([
                    'user_id' => $userId,
                    'bagian_id' => $bagianId,
                    'nama_peminjam' => $validated['nama_peminjam'],
                    'tipe_peminjam' => $validated['tipe_peminjam'],
                    'email_peminjam' =>
                        $validated['email_peminjam'] ?? null,
                    'telepon_peminjam' =>
                        $validated['telepon_peminjam'] ?? null,
                    'instansi_peminjam' =>
                        $validated['instansi_peminjam'] ?? null,
                    'kanal_notifikasi' => $validated['kanal_notifikasi'],
                    'keperluan' => $validated['keperluan'],
                    'waktu_pengambilan' =>
                        $validated['waktu_pengambilan'],
                    'lama_pinjam' => $validated['lama_pinjam'],
                    'status' => 'dipinjam',
                    'tracking_token_hash' => $trackingHash,
                ]);

                /*
                 * Simpan seluruh alat ke tabel detail,
                 * kemudian kurangi stoknya.
                 */
                foreach ($resolvedItems as $item) {
                    $alat = $alats->get($item['alat_id']);

                    PengambilanAlatItem::create([
                        'pengambilan_alat_id' => $peminjaman->id,
                        'alat_id' => $alat->id,
                        'jumlah' => $item['jumlah'],
                        'satuan' => $alat->satuan ?: 'unit',
                        'status' => 'dipinjam',
                    ]);

                    $alat->decrement('jumlah', $item['jumlah']);
                }

                return $peminjaman;
            });
        } catch (\DomainException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'items' => $e->getMessage(),
                ]);
        } catch (\Throwable $e) {
            Log::error('Peminjaman publik gagal', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Peminjaman gagal diproses. Silakan coba kembali.'
                );
        }

        /*
         * Kirim notifikasi setelah transaksi database berhasil.
         * Kegagalan email/WhatsApp tidak membatalkan peminjaman.
         */
        $trackingUrl = route(
            'peminjaman-publik.track',
            ['token' => $token]
        );

        try {
            $this->sendNotification($peminjaman, $trackingUrl);
        } catch (\Throwable $e) {
            Log::warning(
                'Peminjaman tersimpan, notifikasi gagal',
                [
                    'peminjaman_id' => $peminjaman->id,
                    'message' => $e->getMessage(),
                ]
            );
        }

        return redirect()
            ->route('landing')
            ->with(
                'success',
                'Peminjaman tercatat. Periksa kanal kontak Anda untuk tautan pelacakan.'
            );
    }

    public function track(string $token)
    {
        abort_unless(
            preg_match('/^[A-Za-z0-9]{64}$/', $token),
            404
        );

        $peminjaman = PengambilanAlat::query()
            ->with(['items.alat'])
            ->where(
                'tracking_token_hash',
                hash('sha256', $token)
            )
            ->firstOrFail();

        return view(
            'peminjaman-publik.status',
            compact('peminjaman')
        );
    }

    private function sendNotification(
        PengambilanAlat $peminjaman,
        string $trackingUrl
    ): void {
        $peminjaman->loadMissing(['items.alat']);

        $detailAlat = $peminjaman->items
            ->map(function ($item) {
                $nama = $item->alat?->nama_alat ?? 'Alat';
                $satuan = $item->satuan ?: 'unit';

                return "- {$nama}: {$item->jumlah} {$satuan}";
            })
            ->implode("\n");

        if ($detailAlat === '') {
            $detailAlat = '- Detail alat tidak tersedia.';
        }

        if ($peminjaman->kanal_notifikasi === 'email') {
            if (!$peminjaman->email_peminjam) {
                throw new \RuntimeException(
                    'Email peminjam belum tersedia.'
                );
            }

            Mail::to($peminjaman->email_peminjam)->send(
                new PeminjamanAlatTerkonfirmasi(
                    $peminjaman,
                    $trackingUrl
                )
            );

            return;
        }

        /*
         * Konfigurasi WhatsApp di config/services.php
         * harus sesuai dengan variabel environment.
         */
        $phoneId = config('services.whatsapp.phone_number_id');
        $accessToken = config('services.whatsapp.access_token');
        $version = config('services.whatsapp.graph_version');
        $template = config('services.whatsapp.template_name');

        if (!$phoneId || !$accessToken || !$version || !$template) {
            throw new \RuntimeException(
                'WhatsApp API belum dikonfigurasi.'
            );
        }

        $phone = preg_replace(
            '/\D+/',
            '',
            $peminjaman->telepon_peminjam ?? ''
        );

        if (!$phone) {
            throw new \RuntimeException(
                'Nomor WhatsApp tidak valid.'
            );
        }

        /*
         * Template WhatsApp harus mempunyai empat parameter body:
         * nama peminjam, daftar alat, status, dan URL pelacakan.
         */
        $response = Http::withToken($accessToken)
            ->post(
                "https://graph.facebook.com/{$version}/{$phoneId}/messages",
                [
                    'messaging_product' => 'whatsapp',
                    'to' => $phone,
                    'type' => 'template',
                    'template' => [
                        'name' => $template,
                        'language' => [
                            'code' => 'id',
                        ],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $peminjaman->nama_peminjam,
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $detailAlat,
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $peminjaman->status,
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => $trackingUrl,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            );

        $response->throw();
    }
}
