<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Peminjaman Sismalat</title>
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">

    @php
        $tanggalAmbil = $peminjaman->waktu_pengambilan;
        $jatuhTempo = $peminjaman->tanggal_jatuh_tempo;
        $items = $peminjaman->items;
    @endphp

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f4f6;padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                    style="max-width:680px;background:#ffffff;border-radius:14px;overflow:hidden;">

                    <tr>
                        <td style="background:#b45309;padding:28px 32px;color:#ffffff;">
                            <div style="font-size:13px;letter-spacing:2px;font-weight:bold;">
                                SISTEM MANAJEMEN ALAT
                            </div>
                            <h1 style="font-size:25px;margin:12px 0 8px;">
                                Peminjaman Berhasil Dicatat
                            </h1>
                            <p style="font-size:14px;line-height:1.7;margin:0;color:#fef3c7;">
                                Simpan email ini sebagai bukti dan gunakan tautan
                                di bawah untuk memantau status peminjaman.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px;">
                            <p style="font-size:15px;line-height:1.8;margin-top:0;">
                                Halo <strong>{{ $peminjaman->nama_peminjam }}</strong>,
                            </p>

                            <p style="font-size:14px;line-height:1.8;color:#4b5563;">
                                Permohonan peminjaman alat Anda telah tercatat
                                di sistem Sismalat. Berikut ringkasan transaksi Anda.
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;">
                                <tr>
                                    <td style="padding:16px;">
                                        <div style="font-size:12px;color:#92400e;">
                                            STATUS PEMINJAMAN
                                        </div>
                                        <div style="font-size:18px;font-weight:bold;color:#92400e;margin-top:6px;">
                                            {{ $peminjaman->status_label }}
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <h2 style="font-size:17px;margin:28px 0 12px;">
                                Informasi Peminjam
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="8"
                                style="font-size:13px;border-collapse:collapse;">
                                <tr>
                                    <td style="width:40%;color:#6b7280;border-bottom:1px solid #e5e7eb;">
                                        Nama
                                    </td>
                                    <td style="border-bottom:1px solid #e5e7eb;font-weight:bold;">
                                        {{ $peminjaman->nama_peminjam }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:#6b7280;border-bottom:1px solid #e5e7eb;">
                                        Tipe peminjam
                                    </td>
                                    <td style="border-bottom:1px solid #e5e7eb;">
                                        {{ ucfirst($peminjaman->tipe_peminjam ?? '-') }}
                                    </td>
                                </tr>
                                @if ($peminjaman->instansi_peminjam)
                                    <tr>
                                        <td style="color:#6b7280;border-bottom:1px solid #e5e7eb;">
                                            Instansi
                                        </td>
                                        <td style="border-bottom:1px solid #e5e7eb;">
                                            {{ $peminjaman->instansi_peminjam }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="color:#6b7280;border-bottom:1px solid #e5e7eb;">
                                        Tanggal pengambilan
                                    </td>
                                    <td style="border-bottom:1px solid #e5e7eb;">
                                        {{ $tanggalAmbil ? $tanggalAmbil->format('d/m/Y H:i') : '-' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:#6b7280;border-bottom:1px solid #e5e7eb;">
                                        Durasi pinjam
                                    </td>
                                    <td style="border-bottom:1px solid #e5e7eb;">
                                        {{ $peminjaman->lama_pinjam }} hari
                                    </td>
                                </tr>
                                <tr>
                                    <td style="color:#6b7280;">
                                        Batas pengembalian
                                    </td>
                                    <td style="font-weight:bold;">
                                        {{ $jatuhTempo ? $jatuhTempo->format('d/m/Y H:i') : '-' }}
                                    </td>
                                </tr>
                            </table>

                            <h2 style="font-size:17px;margin:28px 0 12px;">
                                Daftar Alat
                            </h2>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="10"
                                style="border-collapse:collapse;font-size:13px;">
                                <thead>
                                    <tr style="background:#f9fafb;">
                                        <th align="left" style="border:1px solid #e5e7eb;">Nama alat</th>
                                        <th align="center" style="border:1px solid #e5e7eb;">Jumlah</th>
                                        <th align="left" style="border:1px solid #e5e7eb;">Identitas alat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr>
                                            <td style="border:1px solid #e5e7eb;">
                                                <strong>{{ $item->alat?->nama_alat ?? 'Alat tidak tersedia' }}</strong>
                                                @if ($item->alat?->merk)
                                                    <div style="font-size:12px;color:#6b7280;margin-top:4px;">
                                                        {{ $item->alat->merk }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td align="center" style="border:1px solid #e5e7eb;white-space:nowrap;">
                                                {{ $item->jumlah }} {{ $item->satuan ?: 'unit' }}
                                            </td>
                                            <td style="border:1px solid #e5e7eb;color:#4b5563;">
                                                @if ($item->alat?->no_seri)
                                                    No. seri: {{ $item->alat->no_seri }}<br>
                                                @endif
                                                @if ($item->alat?->tipe)
                                                    Tipe: {{ $item->alat->tipe }}<br>
                                                @endif
                                                @if ($item->alat?->kapasitas)
                                                    Kapasitas: {{ $item->alat->kapasitas }}
                                                @endif
                                                @if (!$item->alat?->no_seri && !$item->alat?->tipe && !$item->alat?->kapasitas)
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" style="border:1px solid #e5e7eb;">
                                                Detail alat tidak tersedia.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <h2 style="font-size:17px;margin:28px 0 8px;">
                                Keperluan Peminjaman
                            </h2>
                            <p style="font-size:14px;line-height:1.8;color:#4b5563;white-space:pre-line;">
                                {{ $peminjaman->keperluan }}</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
                                style="margin:28px 0;">
                                <tr>
                                    <td align="center" style="background:#b45309;border-radius:8px;">
                                        <a href="{{ $trackingUrl }}"
                                            style="display:inline-block;padding:15px 24px;color:#ffffff;text-decoration:none;font-weight:bold;font-size:14px;">
                                            Lacak Status Peminjaman
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size:12px;line-height:1.8;color:#6b7280;">
                                Jika tombol tidak dapat dibuka, salin tautan berikut
                                ke browser Anda:<br>
                                <a href="{{ $trackingUrl }}" style="color:#b45309;word-break:break-all;">
                                    {{ $trackingUrl }}
                                </a>
                            </p>

                            <p style="font-size:13px;line-height:1.8;color:#4b5563;margin-top:28px;">
                                Email ini dikirim secara otomatis oleh Sismalat.
                                Mohon tidak membalas email ini.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:18px 32px;text-align:center;">
                            <p style="margin:0;font-size:11px;color:#9ca3af;">
                                © {{ date('Y') }} Sismalat — Sistem Manajemen Alat
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>

</html>
