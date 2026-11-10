<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bukti Pengambilan Alat</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm;
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

        .approval-status {
            display: inline-block;
            padding: 2px 6px;
            border: 1px solid #78a88b;
            background: #edf7f0;
            color: #17613a;
            font-size: 7.5pt;
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

        /* ============ BLOK TANDA TANGAN ============ */
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
            height: 55px;
        }

        /* Nama peminjam: garis tanda tangan di ATAS nama */
        .signature-name {
            min-height: 17px;
            padding-top: 4px;
            border-top: 1px solid #4b5563;
            font-weight: bold;
        }

        /* Nama penyetuju: teks tebal dengan garis bawah (seperti contoh) */
        .signature-plain-name {
            min-height: 17px;
            font-weight: bold;
            text-decoration: underline;
        }

        .signature-id {
            margin-top: 2px;
            font-size: 7.5pt;
            color: #4b5563;
        }

        /* QR sebagai tanda tangan elektronik */
        .signature-qr-wrap {
            margin: 8px auto 7px;
            text-align: center;
        }

        .signature-qr {
            display: inline-block;
            width: 92px;
            height: 92px;
        }

        .qr-unavailable {
            display: inline-block;
            padding: 7px;
            border: 1px dashed #b7791f;
            color: #8a5a12;
            font-size: 7pt;
            line-height: 1.3;
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

            .copy-label {
                background: #f3f5f7 !important;
            }
        }
    </style>
</head>

<body>

    @php
        // ===== PREPARE DATA DI AWAL (AMAN DARI ERROR PARSING) =====
        $single = isset($list) && $list->count() === 1;
        $first = isset($list) ? $list->first() : null;

        // Logo
        $logoPath = public_path('images/logo-metrologi.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logos.jpg');
        }
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo.jpg');
        }
        $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;
        $logoMime = $logoBase64 ? 'image/png' : 'image/png';

        // Data utama - fallback aman
        $documentNumber = '-';
        $borrowerName = '-';
        $departmentName = '-';
        $loanDate = '-';
        $loanTime = '-';
        $loanDuration = null;
        $loanStatus = 'dipinjam';
        $keperluan = '-';
        $items = collect();

        if ($first) {
            $documentNumber = !empty($first->hashid) ? $first->hashid : '-';

            if (!empty($first->nama_peminjam)) {
                $borrowerName = $first->nama_peminjam;
            } elseif (isset($first->user) && !empty($first->user->name)) {
                $borrowerName = $first->user->name;
            }

            $departmentName = isset($first->bagian) && !empty($first->bagian->nama) ? $first->bagian->nama : '-';

            if (!empty($first->waktu_pengambilan)) {
                $loanDate = \Carbon\Carbon::parse($first->waktu_pengambilan)->format('d F Y');
                $loanTime = \Carbon\Carbon::parse($first->waktu_pengambilan)->format('H:i');
            }

            $loanDuration = !empty($first->lama_pinjam) ? $first->lama_pinjam : null;
            $loanStatus = !empty($first->status) ? $first->status : 'dipinjam';
            $keperluan = !empty($first->keperluan) ? $first->keperluan : '-';

            if (isset($first->items) && $first->items) {
                $items = $first->items;
            }
        }

        // Data approval - cek manual tanpa nullsafe
        $approvalName = null;
        if ($first && !empty($first->approved_by_name)) {
            $approvalName = $first->approved_by_name;
        } elseif ($first && isset($first->approver) && !empty($first->approver->name)) {
            $approvalName = $first->approver->name;
        }

        $approvalAt = null;
        if ($first && !empty($first->approved_at)) {
            $approvalAt = \Carbon\Carbon::parse($first->approved_at)->format('d/m/Y H:i');
        }

        $isApproved = !empty($approvalAt) && !empty($approvalName);
        $approvalRole = '-';
        if ($first && !empty($first->approved_by_role)) {
            $approvalRole = $first->approved_by_role;
        } else {
            $approvalRole = 'Admin / Super Admin';
        }

        // Verification URL
        $verificationUrl = null;
        if ($isApproved && $first && !empty($first->verification_token)) {
            try {
                $verificationUrl = route('pengambilan_alat.verification', ['token' => $first->verification_token]);
            } catch (\Exception $e) {
                $verificationUrl = null;
            }
        }

        // QR Code generation (SVG agar tajam saat dicetak)
        $qrSvg = null;
        if ($verificationUrl && class_exists('\BaconQrCode\Writer')) {
            try {
                $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(220, 2);
                $imageBackEnd = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
                $qrRenderer = new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $imageBackEnd);
                $writer = new \BaconQrCode\Writer($qrRenderer);
                $qrSvg = $writer->writeString($verificationUrl);
            } catch (\Throwable $e) {
                $qrSvg = null;
            }
        }

        $printedAt = \Carbon\Carbon::now()->format('d-m-Y H:i');
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
                                    alt="Logo">
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
                        <td class="value">
                            @if ($loanDuration)
                                {{ $loanDuration }} hari
                            @else
                                Tidak dicantumkan
                            @endif
                        </td>
                        <td class="label">Status</td>
                        <td class="value">
                            @if ($loanStatus === 'dipinjam')
                                <span class="status">SEDANG DIPINJAM</span>
                            @else
                                <span class="status">SUDAH DIKEMBALIKAN</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Persetujuan Admin</td>
                        <td class="value" colspan="3">
                            @if ($isApproved)
                                <span class="approval-status">DISETUJUI</span>
                                oleh {{ $approvalName }} ({{ $approvalRole }}) pada {{ $approvalAt }} WIB
                            @else
                                <span class="status">BELUM DISETUJUI</span>
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
                        @php
                            $itemIndex = 0;
                        @endphp
                        @foreach ($items as $detail)
                            @php
                                $itemIndex++;
                                $alatName = 'Nama alat tidak tersedia';
                                $alatMerk = '-';
                                $alatTipe = '';
                                $alatSeri = '-';
                                $alatSatuan = 'unit';

                                if (isset($detail->alat) && $detail->alat) {
                                    $alatName = !empty($detail->alat->nama_alat)
                                        ? $detail->alat->nama_alat
                                        : 'Nama alat tidak tersedia';
                                    $alatMerk = !empty($detail->alat->merk) ? $detail->alat->merk : '-';
                                    $alatTipe = !empty($detail->alat->tipe) ? $detail->alat->tipe : '';
                                    $alatSeri = !empty($detail->alat->no_seri) ? $detail->alat->no_seri : '-';
                                    $alatSatuan = !empty($detail->alat->satuan) ? $detail->alat->satuan : 'unit';
                                }

                                $jumlah = isset($detail->jumlah) ? $detail->jumlah : 0;
                                $satuan = !empty($detail->satuan) ? $detail->satuan : $alatSatuan;
                            @endphp
                            <tr>
                                <td class="center">{{ $itemIndex }}</td>
                                <td><strong>{{ $alatName }}</strong></td>
                                <td>
                                    {{ $alatMerk }}
                                    @if ($alatTipe)
                                        / {{ $alatTipe }}
                                    @endif
                                </td>
                                <td>{{ $alatSeri }}</td>
                                <td class="center">{{ $jumlah }}</td>
                                <td class="center">{{ $satuan }}</td>
                                <td>{{ $keperluan }}</td>
                            </tr>
                        @endforeach

                        @if ($items->isEmpty())
                            <tr>
                                <td colspan="7" class="center">Rincian alat tidak tersedia.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>

                <h2 class="section-heading">C. Pernyataan dan Ketentuan</h2>
                <p class="declaration">
                    Dengan menandatangani dokumen ini, peminjam menyatakan bahwa alat yang tercantum di atas telah
                    diterima sesuai jumlah yang tertera. Peminjam bertanggung jawab menjaga, menggunakan, dan
                    mengembalikan alat sesuai ketentuan yang berlaku.
                </p>

                <table class="declaration-table">
                    <tr>
                        <td style="width: 22%; font-weight: bold;">Catatan kondisi alat</td>
                        <td style="height: 30px;">
                            ................................................................................</td>
                    </tr>
                </table>

                <p class="signature-intro">
                    Demikian bukti pengambilan dan serah terima alat ini dibuat untuk digunakan sebagaimana mestinya.
                </p>

                {{-- ═════════════ BLOK TANDA TANGAN ═════════════ --}}
                <table class="signature-table">
                    <tr>
                        {{-- KOLOM KIRI: PEMINJAM (tanda tangan basah) --}}
                        <td>
                            <div class="signature-role">Peminjam</div>
                            <div class="signature-note">Tanda tangan dan nama jelas</div>
                            <div class="signature-space"></div><br><br><br>
                            <div class="signature-name">{{ $borrowerName }}</div>
                        </td>

                        {{-- KOLOM KANAN: PENYETUJU (QR = tanda tangan elektronik) --}}
                        <td>
                            @if ($isApproved)
                                <div class="signature-role">{{ $approvalRole }} Penyetuju</div>

                                <div class="signature-qr-wrap">
                                    @if ($qrSvg)
                                        <img class="signature-qr"
                                            src="data:image/svg+xml;base64,{{ base64_encode($qrSvg) }}"
                                            alt="QR Tanda Tangan Elektronik">
                                    @else
                                        <div class="qr-unavailable">QR tidak tersedia</div>
                                    @endif
                                </div>

                                <div class="signature-plain-name">{{ $approvalName }}</div>
                                <div class="signature-id">
                                    Ditandatangani secara elektronik pada {{ $approvalAt }} WIB
                                </div>
                            @else
                                <div class="signature-role">Petugas / Admin</div>
                                <div class="signature-note">Tanda tangan dan nama jelas</div>
                                <div class="signature-space"></div>
                                <div class="signature-name">................................................</div>
                            @endif
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
                                @if ($copyType === 'PEMINJAM')
                                    Disimpan oleh peminjam.
                                @else
                                    Disimpan oleh admin/petugas sebagai arsip.
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </section>
        @endforeach
    </main>
</body>

</html>
