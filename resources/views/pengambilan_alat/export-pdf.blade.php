<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Pengambilan Alat</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f4;
            color: #202833;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 7.5pt;
            line-height: 1.25;
        }

        .document {
            width: 100%;
            max-width: 210mm;
            margin: 18px auto;
            padding: 0;
        }

        .copy {
            position: relative;
            background: #fff;
            padding: 0;
            page-break-after: always;
        }

        .copy:last-child {
            page-break-after: auto;
        }

        .copy-label {
            display: block;
            margin-bottom: 4px;
            padding: 3px 7px;
            border: 1px solid #8a949f;
            background: #f3f5f7;
            color: #293442;
            font-size: 8pt;
            font-weight: bold;
            letter-spacing: .7px;
            text-align: right;
            text-transform: uppercase;
        }

        .letterhead {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #263746;
            margin-bottom: 6px;
        }

        .letterhead td {
            border: none;
            padding: 0 0 5px;
            vertical-align: middle;
        }

        .logo-cell {
            width: 76px;
            padding-right: 12px !important;
        }

        .logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .organization {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.35;
            color: #172b3a;
        }

        .organization-subtitle {
            margin-top: 3px;
            font-size: 9pt;
            color: #3f4b57;
        }

        .system-name {
            margin-top: 3px;
            font-size: 8pt;
            color: #53606d;
        }

        .document-heading {
            margin: 7px 0 2px;
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .document-subheading {
            margin: 0 0 6px;
            text-align: center;
            font-size: 9pt;
            color: #46515d;
        }

        .number-line {
            margin: 0 auto 6px;
            text-align: center;
            font-size: 9pt;
        }

        .number-line strong {
            letter-spacing: .3px;
        }

        .section-heading {
            margin: 6px 0 3px;
            padding: 3px 6px;
            border-left: 3px solid #263746;
            background: #f1f4f6;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .info-table,
        .items-table,
        .signature-table,
        .declaration-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table {
            margin-bottom: 5px;
        }

        .info-table td {
            padding: 2.5px 4px;
            border: 1px solid #cbd2d9;
            vertical-align: top;
        }

        .info-table .label {
            width: 20%;
            background: #f7f8f9;
            font-weight: bold;
        }

        .info-table .value {
            width: 30%;
        }

        .items-table {
            margin-top: 5px;
        }

        .items-table th {
            padding: 4px 3px;
            border: 1px solid #5d6873;
            background: #e8edf1;
            color: #172b3a;
            font-size: 8pt;
            text-align: center;
            vertical-align: middle;
        }

        .items-table td {
            padding: 3px 3px;
            border: 1px solid #aeb7c0;
            font-size: 7.5pt;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .items-table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

        .items-table tr,
        .info-table tr,
        .signature-table tr {
            page-break-inside: avoid;
        }

        .center {
            text-align: center;
        }

        .status {
            display: inline-block;
            padding: 3px 7px;
            border: 1px solid #8b969f;
            font-size: 8pt;
            font-weight: bold;
        }

        .declaration {
            margin-top: 6px;
            text-align: justify;
            font-size: 9pt;
        }

        .declaration-table {
            margin-top: 4px;
        }

        .declaration-table td {
            padding: 2.5px 4px;
            border: 1px solid #cbd2d9;
            vertical-align: top;
        }

        .signature-intro {
            margin-top: 6px;
            font-size: 9pt;
            text-align: justify;
        }

        .signature-table {
            margin-top: 4px;
            page-break-inside: avoid;
        }

        .signature-table td {
            width: 50%;
            padding: 3px 8px;
            border: none;
            text-align: center;
            vertical-align: top;
        }

        .signature-role {
            font-weight: bold;
        }

        .signature-note {
            font-size: 8pt;
            color: #4b5563;
        }

        .signature-space {
            height: 35px;
        }

        .signature-name {
            min-height: 17px;
            padding-top: 4px;
            border-top: 1px solid #4b5563;
            font-weight: bold;
        }

        .signature-id {
            margin-top: 2px;
            font-size: 8pt;
        }

        .footer {
            margin-top: 6px;
            padding-top: 3px;
            border-top: 1px solid #cbd2d9;
            font-size: 7.5pt;
            color: #59636e;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            border: none;
            padding: 0;
        }

        .footer-table td:last-child {
            text-align: right;
        }

        .muted {
            color: #5b6570;
        }

        .no-wrap {
            white-space: nowrap;
        }

        .print-actions {
            max-width: 210mm;
            margin: 18px auto 0;
            text-align: right;
        }

        .print-button {
            display: inline-block;
            padding: 10px 16px;
            border: 0;
            border-radius: 5px;
            background: #263746;
            color: #fff;
            font-size: 7.5pt;
            font-weight: bold;
            cursor: pointer;
        }

        @media screen {
            .copy {
                margin: 0 0 18px;
                padding: 8mm 10mm;
                box-shadow: 0 2px 14px rgba(25, 35, 45, .12);
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .document {
                max-width: none;
                margin: 0;
            }

            .copy {
                padding: 0;
                margin: 0;
                box-shadow: none;
                page-break-after: always;
                break-after: page;
            }

            .copy:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            .print-actions {
                display: none !important;
            }

            .copy-label {
                background: #f3f5f7 !important;
            }

            .section-heading,
            .items-table th,
            .info-table .label {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    @php
        $single = $list->count() === 1;
        $first = $list->first();
        $logoPath = public_path('images/logo-metrologi.png');

        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logos.jpg');
        }
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo.jpg');
        }

        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        $logoMime = $logoBase64 ? (mime_content_type($logoPath) ?: 'image/png') : 'image/png';
        $documentNumber = $first->hashid ?? '-';
        $borrowerName = $first->nama_peminjam ?? ($first->user->name ?? '-');
        $departmentName = $first->bagian->nama ?? '-';
        $loanDate = $first->waktu_pengambilan ? \Carbon\Carbon::parse($first->waktu_pengambilan)->format('d F Y') : '-';
        $loanTime = $first->waktu_pengambilan ? \Carbon\Carbon::parse($first->waktu_pengambilan)->format('H:i') : '-';
        $loanDuration = $first->lama_pinjam ?? null;
        $loanStatus = $first->status ?? 'dipinjam';
        $printedAt = now()->format('d-m-Y H:i');
    @endphp

    <main class="document">
        @foreach (['PEMINJAM', 'ADMIN / ARSIP'] as $copyType)
            <section class="copy">
                <div class="copy-label">Salinan {{ $copyType }}</div>

                <table class="letterhead">
                    <tr>
                        @if ($logoBase64)
                            <td class="logo-cell">
                                <img class="logo" src="data:{{ $logoMime }};base64,{{ $logoBase64 }}"
                                    alt="Logo instansi">
                            </td>
                        @endif
                        <td>
                            <div class="organization">Dinas Perindustrian dan Perdagangan</div>
                            <div class="organization-subtitle">Kabupaten Karawang</div>
                            <div class="system-name">Sistem Manajemen Alat (Tanaoroshi)</div>
                        </td>
                    </tr>
                </table>

                <h1 class="document-heading">Bukti Pengambilan dan Serah Terima Alat</h1>
                <p class="document-subheading">Dokumen administrasi peminjaman alat</p>
                <p class="number-line">Nomor Bukti: <strong>{{ $documentNumber }}</strong></p>

                <h2 class="section-heading">A. Identitas Peminjaman</h2>
                <table class="info-table">
                    <tr>
                        <td class="label">Nama Peminjam</td>
                        <td class="value">{{ $borrowerName }}</td>
                        <td class="label">Bagian</td>
                        <td class="value">{{ $departmentName }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Pengambilan</td>
                        <td class="value">{{ $loanDate }}</td>
                        <td class="label">Waktu</td>
                        <td class="value">{{ $loanTime }} WIB</td>
                    </tr>
                    <tr>
                        <td class="label">Lama Peminjaman</td>
                        <td class="value">{{ filled($loanDuration) ? $loanDuration : 'Tidak dicantumkan' }}</td>
                        <td class="label">Status</td>
                        <td class="value">
                            @if ($loanStatus === 'dipinjam')
                                <span class="status">SEDANG DIPINJAM</span>
                            @else
                                <span class="status">SUDAH DIKEMBALIKAN</span>
                            @endif
                        </td>
                    </tr>
                </table>

                <h2 class="section-heading">B. Rincian Alat yang Diserahkan</h2>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 25%;">Nama Alat</th>
                            <th style="width: 17%;">Merk / Tipe</th>
                            <th style="width: 15%;">Nomor Seri</th>
                            <th style="width: 9%;">Jumlah</th>
                            <th style="width: 9%;">Satuan</th>
                            <th style="width: 20%;">Keperluan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($first->items ?? collect()) as $i => $detail)
                            <tr>
                                <td class="center">{{ $i + 1 }}</td>
                                <td><strong>{{ $detail->alat->nama_alat ?? 'Nama alat tidak tersedia' }}</strong></td>
                                <td>
                                    {{ $detail->alat->merk ?? '-' }}
                                    @if (filled($detail->alat->tipe ?? null))
                                        / {{ $detail->alat->tipe }}
                                    @endif
                                </td>
                                <td>{{ $detail->alat->no_seri ?? '-' }}</td>
                                <td class="center">{{ $detail->jumlah ?? 0 }}</td>
                                <td class="center">{{ $detail->satuan ?? ($detail->alat->satuan ?? 'unit') }}</td>
                                <td>{{ $first->keperluan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="center">Rincian alat tidak tersedia pada data peminjaman ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <h2 class="section-heading">C. Pernyataan dan Ketentuan</h2>
                <p class="declaration">
                    Dengan menandatangani dokumen ini, peminjam menyatakan bahwa alat yang tercantum di atas telah
                    diterima sesuai jumlah yang tertera. Peminjam bertanggung jawab menjaga, menggunakan, dan
                    mengembalikan alat sesuai ketentuan yang berlaku. Apabila terdapat kerusakan, kehilangan, atau
                    ketidaksesuaian, peminjam wajib melaporkannya kepada petugas yang berwenang.
                </p>

                <table class="declaration-table">
                    <tr>
                        <td style="width: 22%; font-weight: bold;">Catatan kondisi alat</td>
                        <td style="height: 30px;">Diisi apabila terdapat catatan saat serah terima:
                            ................................................................................</td>
                    </tr>
                </table>

                <p class="signature-intro">
                    Demikian bukti pengambilan dan serah terima alat ini dibuat untuk digunakan sebagaimana mestinya.
                    Masing-masing pihak menyimpan satu salinan sebagai bukti administrasi.
                </p>

                <table class="signature-table">
                    <tr>
                        <td>
                            <div class="signature-role">Peminjam</div>
                            <div class="signature-note">Tanda tangan dan nama jelas</div>
                            <div class="signature-space"></div><br><br><br><br>
                            <div class="signature-name">{{ $borrowerName }}</div>
                        </td>
                        <td>
                            <div class="signature-role">Petugas / Admin</div>
                            <div class="signature-note">Tanda tangan dan nama jelas</div>
                            <div class="signature-space"></div><br><br><br><br>
                            <div class="signature-name">................................................</div>
                        </td>
                    </tr>
                </table>

                <div class="footer">
                    <table class="footer-table">
                        <tr>
                            <td>Dokumen administrasi Sistem Manajemen Alat (Tanaoroshi).</td>
                            <td>Dicetak: {{ $printedAt }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="padding-top: 3px;">
                                Arsip:
                                {{ $copyType === 'PEMINJAM' ? 'Disimpan oleh peminjam.' : 'Disimpan oleh admin/petugas sebagai arsip.' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </section>
        @endforeach
    </main>
</body>

</html>
