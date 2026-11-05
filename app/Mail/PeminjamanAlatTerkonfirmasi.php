<?php

namespace App\Mail;

use App\Models\PengambilanAlat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PeminjamanAlatTerkonfirmasi extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PengambilanAlat $peminjaman,
        public string $trackingUrl
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Peminjaman Alat - Sismalat'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.peminjaman-alat'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
