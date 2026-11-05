<?php

namespace App\Notifications;

use App\Models\PengambilanAlat;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;


class PeminjamanWarningNotification extends Notification
{
    use Queueable;

    protected PengambilanAlat $pengambilan;
    protected string $warningType;

    public function __construct(
        PengambilanAlat $pengambilan,
        string $warningType = '15_percent'
    ) {
        $this->pengambilan = $pengambilan;
        $this->warningType = $warningType;
    }

    /**
     * Admin menerima database notification.
     * Email hanya dikirim ketika Notification::route('mail', ...)
     * dipanggil secara eksplisit oleh admin.
     */
    public function via(object $notifiable): array
    {
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['mail'];
        }

        return ['database'];
    }

    /**
     * Menentukan field timestamp email berdasarkan jenis peringatan.
     */
    protected function getEmailSentField(): string
    {
        return match ($this->warningType) {
            '15_percent' => 'warning_15_sent_at',
            '1_day'      => 'warning_1day_sent_at',
            'overdue'    => 'warning_overdue_sent_at',
            default      => 'last_warned_at',
        };
    }

    /**
     * Data notifikasi.
     */
    protected function getNotificationData(): array
    {
        $p = $this->pengambilan;

        $p->loadMissing(['items.alat']);

        // Fallback untuk data lama yang belum memiliki tanggal jatuh tempo.
        $tanggalJatuhTempo = $p->tanggal_jatuh_tempo
            ? Carbon::parse($p->tanggal_jatuh_tempo)->startOfDay()
            : (
                $p->waktu_pengambilan && $p->lama_pinjam
                    ? Carbon::parse($p->waktu_pengambilan)
                        ->startOfDay()
                        ->addDays((int) $p->lama_pinjam)
                    : null
            );

        $sisaHari = $tanggalJatuhTempo
            ? (int) Carbon::today()->diffInDays($tanggalJatuhTempo, false)
            : null;

        $persentase = $p->getAttribute('persentase_sisa')
            ?? $p->getAttribute('percentase_sisa');

        $totalItem = $p->items->count();

        $ringkasan = $p->ringkasan_alat
            ?: "Peminjaman {$totalItem} jenis alat";

        $isOverdue = $this->warningType === 'overdue'
            || ($sisaHari !== null && $sisaHari < 0);

        $isOneDay = !$isOverdue && (
            $this->warningType === '1_day'
            || $sisaHari === 1
        );

        if ($isOverdue) {
            $title = 'Peminjaman Alat Terlambat';

            $message = "Peminjaman {$ringkasan} telah melewati "
                . "tanggal jatuh tempo selama "
                . abs($sisaHari ?? 0)
                . " hari.";

            $icon = 'exclamation-circle';
            $color = 'red';
            $priority = 'high';
            $type = 'peminjaman_overdue';

        } elseif ($isOneDay) {
            $title = 'Pengembalian Alat Besok';

            $message = "Peminjaman {$ringkasan} akan jatuh tempo BESOK. "
                . "Segera lakukan pengembalian.";

            $icon = 'clock';
            $color = 'red';
            $priority = 'high';
            $type = 'peminjaman_warning_1day';

        } else {
            $title = 'Peminjaman Alat Segera Berakhir';

            $message = "Peminjaman {$ringkasan} akan segera berakhir. "
                . "Sisa waktu: {$sisaHari} hari.";

            $icon = 'exclamation-triangle';
            $color = 'orange';
            $priority = 'normal';
            $type = 'peminjaman_warning';
        }

        $sentField = $this->getEmailSentField();
        $emailSentAt = $p->getAttribute($sentField);

        return [
            'type' => $type,
            'warning_type' => $this->warningType,

            'title' => $title,
            'pengambilan_id' => $p->id,
            'hashid' => $p->hashid,

            'nama_alat' => $ringkasan,
            'total_item' => $totalItem,

            'nama_peminjam' => $p->nama_peminjam,
            'email_peminjam' => $p->email_peminjam,

            'sisa_hari' => $sisaHari,
            'persentase_sisa' => $persentase,
            'jatuh_tempo' => $tanggalJatuhTempo?->format('d M Y'),

            'message' => $message,

            // Detail transaksi untuk admin.
            'action_url' => route(
                'pengambilan_alat.show',
                $p->hashid
            ),

            'action_label' => 'Lihat Peminjaman',

            // Endpoint khusus pengiriman email manual.
            'send_email_url' => filled($p->email_peminjam)
                ? route('pengambilan_alat.send-warning-email', [
                    'hashid' => $p->hashid,
                    'warningType' => $this->warningType,
                ])
                : null,

            'email_sent_at' => $emailSentAt
                ? Carbon::parse($emailSentAt)->toIso8601String()
                : null,

            'icon' => $icon,
            'color' => $color,
            'priority' => $priority,
        ];
    }

    /**
     * Email hanya diproses melalui pengiriman manual admin.
     */
    public function toMail($notifiable): \Illuminate\Notifications\Messages\MailMessage
{
    $p = $this->pengambilan;
    $data = $this->getNotificationData();

    // Ambil detail item peminjaman
    $p->loadMissing('items');

    // Hitung tanggal jatuh tempo
    if ($p->tanggal_jatuh_tempo) {
        $dueDate = \Carbon\Carbon::parse($p->tanggal_jatuh_tempo);
    } else {
        $dueDate = \Carbon\Carbon::parse($p->waktu_pengambilan)
            ->addDays((int) $p->lama_pinjam);
    }

    $borrowedAt = $p->waktu_pengambilan
        ? \Carbon\Carbon::parse($p->waktu_pengambilan)
        : null;

    // Hitung sisa hari
    $daysLeft = isset($data['sisa_hari'])
        ? (int) $data['sisa_hari']
        : (int) now()->startOfDay()
            ->diff($dueDate->copy()->startOfDay())
            ->format('%r%a');

    $warningType = strtolower((string) ($data['warning_type'] ?? ''));

    $isOverdue = $daysLeft < 0
        || str_contains($warningType, 'overdue')
        || str_contains($warningType, 'terlambat');

    $isTomorrow = !$isOverdue && (
        $daysLeft === 1
        || str_contains($warningType, '1_day')
    );

    // Tentukan judul dan isi berdasarkan jenis peringatan
    if ($isOverdue) {
        $lateDays = max(1, abs($daysLeft));

        $statusTitle = 'Pengembalian Alat Terlambat';
        $statusLabel = 'TERLAMBAT ' . $lateDays . ' HARI';

        $statusDescription =
            "Berdasarkan catatan sistem Sismalat, peminjaman alat Anda "
            . "telah melewati batas waktu pengembalian selama {$lateDays} hari. "
            . "Mohon segera melakukan pengembalian alat kepada petugas terkait.";

        $subject = "Tindakan Diperlukan: Pengembalian Alat Terlambat | Sismalat";

    } elseif ($isTomorrow) {
        $lateDays = 0;

        $statusTitle = 'Pengingat Pengembalian Alat';
        $statusLabel = 'JATUH TEMPO BESOK';

        $statusDescription =
            'Peminjaman alat Anda akan mencapai batas waktu pengembalian besok. '
            . 'Mohon mempersiapkan pengembalian agar tidak melewati tanggal jatuh tempo.';

        $subject = 'Pengingat: Batas Pengembalian Alat Besok | Sismalat';

    } else {
        $lateDays = 0;

        $statusTitle = 'Pengingat Masa Peminjaman Alat';
        $statusLabel = 'PENGINGAT PENGEMBALIAN';

        $statusDescription =
            'Kami mengingatkan bahwa masa peminjaman alat Anda akan segera berakhir. '
            . 'Mohon memperhatikan tanggal jatuh tempo dan melakukan pengembalian sesuai ketentuan.';

        $subject = 'Pengingat Masa Peminjaman Alat | Sismalat';
    }

    // Gabungkan nama alat yang sama agar tidak tampil berulang
    $items = collect($p->items ?? [])
        ->map(function ($item) {
            return [
                'name' => data_get($item, 'alat.nama_alat')
                    ?? data_get($item, 'nama_alat')
                    ?? 'Alat',

                'quantity' => (int) ($item->jumlah ?? 1),

                'unit' => data_get($item, 'satuan')
                    ?? data_get($item, 'alat.satuan')
                    ?? 'unit',
            ];
        })
        ->groupBy('name')
        ->map(function ($group, $name) {
            return [
                'name' => $name,
                'quantity' => $group->sum('quantity'),
                'unit' => $group->first()['unit'],
            ];
        })
        ->values()
        ->all();

    $totalJenis = count($items);

    $totalQuantity = collect($items)->sum('quantity');

    $viewData = [
        'appName' => 'Sismalat',
        'borrowerName' => $p->nama_peminjam
            ?? $data['nama_peminjam']
            ?? 'Peminjam',

        'statusTitle' => $statusTitle,
        'statusLabel' => $statusLabel,
        'statusDescription' => $statusDescription,

        'isOverdue' => $isOverdue,
        'lateDays' => $lateDays,

        'items' => $items,
        'totalJenis' => $totalJenis,
        'totalQuantity' => $totalQuantity,

        'loanDays' => $p->lama_pinjam ?? '-',

        'borrowedAt' => $borrowedAt
            ? $borrowedAt->locale('id')->translatedFormat('d F Y')
            : '-',

        'dueDate' => $dueDate->locale('id')->translatedFormat('d F Y'),

        // Pastikan URL ini dapat diakses oleh penerima email
        'detailUrl' => $data['action_url'] ?? null,

        'generatedAt' => now()->locale('id')->translatedFormat('d F Y, H:i'),
    ];

    return (new \Illuminate\Notifications\Messages\MailMessage)
        ->subject($subject)
        ->view('emails.peminjaman-warning', $viewData)
        ->text('emails.peminjaman-warning-text', $viewData);
}

    /**
     * Disimpan ke tabel notifications untuk admin/super.
     */
    public function toArray(object $notifiable): array
    {
        return $this->getNotificationData();
    }
}
