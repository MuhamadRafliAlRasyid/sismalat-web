<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Mailable percobaan untuk testing konfigurasi SMTP/email Sismalat.
 *
 * Contoh penggunaan:
 *
 *   // Cara 1: Default (tanpa parameter)
 *   Mail::to('test@example.com')->send(new EmailPercobaan());
 *
 *   // Cara 2: Dengan data kustom
 *   Mail::to('admin@sismalat.id')->send(new EmailPercobaan(
 *       namaPenerima: 'Budi Santoso',
 *       pesanCustom: 'Server backup selesai!'
 *   ));
 *
 *   // Cara 3: Dengan user object (otomatis pakai nama user)
 *   Mail::to($user)->send(new EmailPercobaan(user: $user));
 */
class EmailPercobaan extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Data yang otomatis tersedia di view `emails.percobaan`.
     * Properti public di Mailable otomatis di-expose ke view.
     */
    public string $namaPenerima;
    public string $pesan;
    public string $waktuKirim;
    public ?string $infoTambahan;

    /**
     * Create a new message instance.
     *
     * @param string|null $namaPenerima  Nama yang akan disapa di email (default: 'Pengguna Sismalat')
     * @param string|null $pesanCustom   Pesan tambahan (default: pesan standar percobaan)
     * @param User|null   $user          Jika diberikan, nama diambil dari $user->name
     * @param string|null $infoTambahan  Informasi opsional untuk debug
     */
    public function __construct(
        ?string $namaPenerima = null,
        ?string $pesanCustom = null,
        ?User $user = null,
        ?string $infoTambahan = null
    ) {
        // Tentukan nama penerima
        $this->namaPenerima = $user?->name
            ?? $namaPenerima
            ?? 'Pengguna Sismalat';

        // Tentukan pesan
        $this->pesan = $pesanCustom ?? $this->pesanDefault();

        // Waktu kirim untuk verifikasi delay/queue
        $this->waktuKirim = now()->format('d M Y, H:i:s');

        // Info tambahan untuk debug
        $this->infoTambahan = $infoTambahan;

        // Log percobaan pengiriman (berguna saat troubleshooting SMTP)
        Log::info('📧 EmailPercobaan di-instantiate', [
            'penerima' => $this->namaPenerima,
            'waktu'    => $this->waktuKirim,
            'env'      => config('app.env'),
            'mailer'   => config('mail.default'),
        ]);
    }

    /**
     * Pesan default untuk percobaan.
     */
    protected function pesanDefault(): string
    {
        return 'Ini adalah email percobaan dari sistem Sismalat. '
            . 'Jika Anda menerima email ini, berarti konfigurasi SMTP sudah berjalan dengan baik. '
            . 'Silakan abaikan pesan ini jika tidak melakukan pengujian.';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // Subject dengan prefix [TEST] agar jelas ini bukan email produksi
            subject: '[TEST] Tes Email Sismalat — ' . now()->format('d/m/Y H:i'),

            // From custom (opsional — kalau tidak diisi, pakai default MAIL_FROM_ADDRESS)
            // from: new Address('noreply@sismalat.id', 'Sismalat System'),

            // Reply-to custom (opsional)
            // replyTo: [new Address('support@sismalat.id', 'Support Sismalat')],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // View HTML
            view: 'emails.percobaan',

            // View text/plain (opsional — untuk email client yang tidak support HTML)
            // text: 'emails.percobaan-text',

            // Data tambahan yang bisa diakses di view
            // (properti public di atas sudah otomatis available, ini untuk data ekstra)
            with: [
                'appName'    => config('app.name', 'Sismalat'),
                'appUrl'     => config('app.url'),
                'appEnv'     => config('app.env'),
                'mailerName' => config('mail.default'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];

        // Contoh kalau mau test lampiran:
        // return [
        //     Attachment::fromPath(public_path('images/logo.png'))
        //         ->as('logo-sismalat.png')
        //         ->withMime('image/png'),
        //     Attachment::fromStorageDisk('public', 'dokumen/sample.pdf'),
        // ];
    }

    /**
     * Build the message (method klasik — tetap berguna untuk customize lanjutan).
     * Method ini dipanggil setelah envelope() dan content().
     */
    public function build(): self
    {
        return $this
            // Priority: 1 = highest, 3 = normal, 5 = lowest
            ->priority(3)

            // Header custom (bisa dilihat di email client, berguna untuk tracking)
            ->withSymfonyMessage(function ($message) {
                $message->getHeaders()->addTextHeader(
                    'X-Sismalat-Test-Email',
                    'true'
                );
                $message->getHeaders()->addTextHeader(
                    'X-Sismalat-Timestamp',
                    $this->waktuKirim
                );
            });
    }
}
