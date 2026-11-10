<!DOCTYPE html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Inventaris Alat Ukur - {{ $date }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 15mm 12mm 20mm 12mm;
            size: A4 landscape;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            line-height: 1.4;
        }

        /* ============ PAGE BREAK ============ */
        .page-break {
            page-break-after: always;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* ============ COVER PAGE ============ */
        .cover-page {
            min-height: 100%;
            padding: 15mm 10mm;
            text-align: center;
            position: relative;
        }

        .cover-kop {
            border-bottom: 4px double #92400e;
            padding-bottom: 12px;
            margin-bottom: 25px;
        }

        .cover-kop-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cover-kop-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .cover-kop-logo {
            width: 70px;
            height: 70px;
        }

        .cover-kop h1 {
            font-size: 16px;
            color: #92400e;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .cover-kop h2 {
            font-size: 13px;
            color: #1e293b;
            font-weight: 700;
            margin: 2px 0;
        }

        .cover-kop p {
            font-size: 9px;
            color: #64748b;
            margin: 1px 0;
        }

        .cover-badge {
            display: inline-block;
            padding: 4px 14px;
            background: #dc2626;
            color: white;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: 2px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .cover-logo-main {
            width: 130px;
            height: 130px;
            margin: 20px auto;
            display: block;
        }

        .cover-title {
            font-size: 26px;
            font-weight: 900;
            color: #92400e;
            margin: 20px 0 8px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .cover-subtitle {
            font-size: 15px;
            color: #b45309;
            font-weight: 700;
            margin-bottom: 15px;
            letter-spacing: 1px;
        }

        .cover-line {
            width: 180px;
            height: 3px;
            background: linear-gradient(to right, #f59e0b, #f97316, #f59e0b);
            margin: 15px auto;
        }

        .cover-meta-box {
            max-width: 520px;
            margin: 25px auto 20px;
            background: #fffbeb;
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 15px 20px;
            text-align: left;
        }

        .cover-meta-box table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .cover-meta-box td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .cover-meta-box .label {
            font-weight: 700;
            color: #78350f;
            width: 35%;
        }

        .cover-meta-box .value {
            color: #1e293b;
        }

        .cover-distribution {
            max-width: 520px;
            margin: 15px auto;
            font-size: 9px;
            text-align: left;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px 15px;
            border-radius: 6px;
        }

        .cover-distribution strong {
            color: #78350f;
            display: block;
            margin-bottom: 5px;
            font-size: 10px;
        }

        .cover-distribution ol {
            margin-left: 20px;
            color: #475569;
        }

        .cover-distribution li {
            margin: 2px 0;
        }

        .cover-verification {
            max-width: 520px;
            margin: 15px auto;
            display: table;
            width: 100%;
            border: 1px dashed #f59e0b;
            padding: 10px;
            border-radius: 6px;
            background: #fffbeb;
        }

        .cover-verification td {
            vertical-align: middle;
            padding: 0;
        }

        .cover-verification .qr-cell {
            width: 90px;
            text-align: center;
            padding-right: 10px;
        }

        .cover-verification .qr-img {
            width: 80px;
            height: 80px;
            border: 1px solid #f59e0b;
            padding: 3px;
            background: white;
        }

        .cover-verification .info-cell {
            font-size: 9px;
            color: #78350f;
        }

        .cover-verification .info-cell strong {
            font-size: 10px;
            display: block;
            margin-bottom: 3px;
        }

        .cover-verification .doc-number {
            font-family: 'Courier New', monospace;
            font-weight: 800;
            font-size: 11px;
            color: #92400e;
            letter-spacing: 1px;
        }

        .cover-footer {
            position: absolute;
            bottom: 15mm;
            left: 12mm;
            right: 12mm;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px solid #cbd5e1;
            padding-top: 6px;
            text-align: center;
            font-style: italic;
        }

        /* ============ SECTION HEADER (MINI KOP) ============ */
        .section-header {
            border-bottom: 2px solid #92400e;
            padding-bottom: 6px;
            margin-bottom: 12px;
            display: table;
            width: 100%;
        }

        .section-header table {
            width: 100%;
            border-collapse: collapse;
        }

        .section-header td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }

        .section-header .mini-logo {
            width: 38px;
            height: 38px;
        }

        .section-header h3 {
            font-size: 11px;
            color: #92400e;
            font-weight: 800;
            margin: 0;
        }

        .section-header p {
            font-size: 7.5px;
            color: #64748b;
            margin: 0;
        }

        .section-header .right-info {
            text-align: right;
            font-size: 7.5px;
            color: #64748b;
        }

        .section-header .right-info strong {
            color: #92400e;
            font-size: 8.5px;
            display: block;
        }

        /* ============ SECTION FOOTER ============ */
        .section-footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px solid #e2e8f0;
            font-size: 7.5px;
            color: #94a3b8;
            display: table;
            width: 100%;
        }

        .section-footer td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .section-footer .right {
            text-align: right;
        }

        /* ============ ROOM SECTION ============ */
        .room-section {
            padding-top: 5mm;
        }

        .room-header {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 8px;
            display: table;
            width: 100%;
        }

        .room-header .left-col {
            display: table-cell;
            vertical-align: middle;
        }

        .room-header .right-col {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
        }

        .room-header h2 {
            font-size: 14px;
            color: #92400e;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .room-header p {
            font-size: 9px;
            color: #78350f;
            margin-top: 2px;
        }

        .room-stats {
            display: inline-block;
            background: white;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 8.5px;
        }

        .room-stats span {
            display: inline-block;
            margin-left: 10px;
            font-weight: 600;
        }

        .room-stats .label {
            color: #64748b;
            font-weight: 500;
            margin-right: 3px;
        }

        .room-stats .good {
            color: #065f46;
        }

        .room-stats .warn {
            color: #92400e;
        }

        .room-stats .bad {
            color: #991b1b;
        }

        /* ============ TABLE ============ */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 8px;
        }

        .data-table thead th {
            background: #92400e;
            color: white;
            font-weight: 700;
            padding: 6px 4px;
            border: 1px solid #78350f;
            text-align: left;
            text-transform: uppercase;
            font-size: 7.5px;
            letter-spacing: 0.3px;
        }

        .data-table thead th.center {
            text-align: center;
        }

        .data-table tbody td {
            padding: 5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background: #fffbeb;
        }

        .data-table tbody tr:nth-child(odd) {
            background: white;
        }

        .data-table .num {
            text-align: center;
            font-weight: 600;
        }

        .data-table .tool-name {
            font-weight: 700;
            color: #1e293b;
            font-size: 9px;
        }

        .data-table .tool-spec {
            font-size: 7.5px;
            color: #64748b;
            margin-top: 2px;
        }

        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .badge-active {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #86efac;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .badge-expired {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .badge-na {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }

        .subtotal-row td {
            background: #fef3c7 !important;
            font-weight: 800;
            color: #92400e;
            border-top: 2px solid #f59e0b;
            padding: 7px 4px;
            font-size: 9px;
        }

        /* ============ SUMMARY PAGE ============ */
        .summary-page {
            padding-top: 5mm;
        }

        .summary-title {
            text-align: center;
            font-size: 18px;
            font-weight: 900;
            color: #92400e;
            margin: 10px 0 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .summary-subtitle {
            text-align: center;
            font-size: 10px;
            color: #64748b;
            margin-bottom: 15px;
            font-style: italic;
        }

        .summary-title-line {
            width: 120px;
            height: 3px;
            background: linear-gradient(to right, #f59e0b, #f97316);
            margin: 0 auto 15px;
        }

        .stat-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 12px 8px;
            text-align: center;
            border-radius: 8px;
            border: 2px solid;
            vertical-align: middle;
        }

        .stat-box .icon {
            font-size: 18px;
            margin-bottom: 4px;
            display: block;
        }

        .stat-box .label {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .stat-box .value {
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
        }

        .stat-box .pct {
            font-size: 9px;
            margin-top: 3px;
            font-weight: 600;
        }

        .stat-box.total {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .stat-box.total .icon,
        .stat-box.total .label {
            color: #92400e;
        }

        .stat-box.total .value {
            color: #b45309;
        }

        .stat-box.good {
            background: #d1fae5;
            border-color: #10b981;
        }

        .stat-box.good .icon,
        .stat-box.good .label {
            color: #065f46;
        }

        .stat-box.good .value {
            color: #065f46;
        }

        .stat-box.good .pct {
            color: #065f46;
        }

        .stat-box.warn {
            background: #fef3c7;
            border-color: #f59e0b;
        }

        .stat-box.warn .icon,
        .stat-box.warn .label {
            color: #92400e;
        }

        .stat-box.warn .value {
            color: #92400e;
        }

        .stat-box.warn .pct {
            color: #92400e;
        }

        .stat-box.bad {
            background: #fee2e2;
            border-color: #ef4444;
        }

        .stat-box.bad .icon,
        .stat-box.bad .label {
            color: #991b1b;
        }

        .stat-box.bad .value {
            color: #991b1b;
        }

        .stat-box.bad .pct {
            color: #991b1b;
        }

        .progress-bar-wrap {
            height: 12px;
            background: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            margin: 8px 0;
            display: table;
            width: 100%;
        }

        .progress-bar {
            display: table;
            width: 100%;
            height: 12px;
        }

        .progress-bar span {
            display: table-cell;
            height: 12px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 9px;
        }

        .summary-table th {
            background: #92400e;
            color: white;
            padding: 8px;
            border: 1px solid #78350f;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
        }

        .summary-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
        }

        .summary-table tr:nth-child(even) {
            background: #fffbeb;
        }

        .summary-table .total-row {
            background: #b45309 !important;
            color: white;
            font-weight: 900;
            font-size: 10px;
        }

        .summary-table .total-row td {
            border-color: #78350f;
        }

        .alert-box {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            padding: 10px 14px;
            margin: 15px 0;
            border-radius: 0 6px 6px 0;
            font-size: 9px;
        }

        .alert-box strong {
            color: #991b1b;
            display: block;
            margin-bottom: 4px;
            font-size: 10px;
        }

        .alert-box ul {
            margin-left: 18px;
            color: #7f1d1d;
        }

        .alert-box li {
            margin: 2px 0;
        }

        /* ============ SIGNATURE PAGE ============ */
        .signature-page {
            padding-top: 5mm;
        }

        .signature-title {
            text-align: center;
            font-size: 14px;
            font-weight: 900;
            color: #92400e;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .signature-subtitle {
            text-align: center;
            font-size: 9px;
            color: #64748b;
            margin-bottom: 25px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 15px 20px;
            border: none;
        }

        .signature-role {
            font-weight: 700;
            color: #1e293b;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .signature-note {
            font-size: 8px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .signature-space {
            height: 55px;
        }

        .signature-name {
            min-height: 17px;
            padding-top: 5px;
            border-top: 1px solid #4b5563;
            font-weight: 700;
            font-size: 10px;
            text-decoration: underline;
        }

        .signature-nip {
            font-size: 8px;
            color: #4b5563;
            margin-top: 2px;
        }

        .ttd-card {
            width: 100%;
            border: 1.5px solid #065f46;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }

        .ttd-card-header {
            background: #065f46;
            color: white;
            padding: 4px 8px;
            font-size: 7.5px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .ttd-card-body {
            padding: 10px;
            background: #f0fdf4;
            display: table;
            width: 100%;
        }

        .ttd-card-body td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }

        .ttd-qr {
            width: 75px;
        }

        .ttd-qr img {
            width: 70px;
            height: 70px;
            border: 1px solid #065f46;
            padding: 2px;
            background: white;
        }

        .ttd-info {
            padding-left: 10px;
            font-size: 8.5px;
            color: #065f46;
        }

        .ttd-info strong {
            display: block;
            font-size: 10px;
            margin-bottom: 2px;
        }

        .ttd-info .code {
            font-family: 'Courier New', monospace;
            font-weight: 700;
            font-size: 9px;
            letter-spacing: 0.5px;
        }

        .ttd-card-footer {
            border-top: 1px solid #86efac;
            padding: 4px 8px;
            font-size: 7px;
            color: #065f46;
            text-align: center;
            background: #dcfce7;
            font-style: italic;
        }

        .footer-legal {
            margin-top: 30px;
            padding: 10px 15px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 8px;
            color: #475569;
            line-height: 1.5;
        }

        .footer-legal strong {
            color: #1e293b;
            display: block;
            margin-bottom: 4px;
        }

        /* ============ PRINT ADJUSTMENTS ============ */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .section-header,
            .data-table thead th,
            .stat-box,
            .ttd-card-header,
            .ttd-card-footer,
            .subtotal-row td,
            .badge,
            .summary-table .total-row td {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    @php
        // ============ LOGO ============
        $logoPath = public_path('images/logo-metrologi.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logos.jpg');
        }
        if (!file_exists($logoPath)) {
            $logoPath = public_path('images/logo.jpg');
        }

        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $imageData = file_get_contents($logoPath);
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
            $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }

        // ============ DATA ============
        $groupedAlats = $alats->groupBy(function ($item) {
            return $item->ruangan
                ? $item->ruangan->kode_ruangan . ' - ' . $item->ruangan->nama_ruangan
                : 'TANPA RUANGAN';
        });

        $grandTotal = ['jumlah' => 0, 'baik' => 0, 'kb' => 0, 'rb' => 0, 'ruangan_count' => $groupedAlats->count()];
        $roomSummaries = [];
        $kategoriCount = [];
        $alatPerluKalibrasi = [];
        $totalHalaman = $groupedAlats->count() + 3; // cover + pengesahan + per-ruangan + summary

        foreach ($groupedAlats as $roomName => $items) {
            $roomTotal = ['jumlah' => 0, 'baik' => 0, 'kb' => 0, 'rb' => 0];
            foreach ($items as $alat) {
                $roomTotal['jumlah'] += $alat->jumlah;
                $roomTotal['baik'] += $alat->jumlah_baik;
                $roomTotal['kb'] += $alat->jumlah_kurang_baik;
                $roomTotal['rb'] += $alat->jumlah_rusak_berat;

                // Hitung kategori
                $katName = $alat->kategori ? $alat->kategori->nama : 'Lainnya';
                $kategoriCount[$katName] = ($kategoriCount[$katName] ?? 0) + $alat->jumlah;

                // Cek status kalibrasi
                if ($alat->masa_berlaku) {
                    $diff = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($alat->masa_berlaku), false);
                    if ($diff < 0) {
                        $alatPerluKalibrasi[] = [
                            'nama' => $alat->nama_alat,
                            'ruangan' => $roomName,
                            'status' => 'EXPIRED',
                            'tgl' => $alat->masa_berlaku,
                        ];
                    } elseif ($diff <= 30) {
                        $alatPerluKalibrasi[] = [
                            'nama' => $alat->nama_alat,
                            'ruangan' => $roomName,
                            'status' => 'WARNING',
                            'tgl' => $alat->masa_berlaku,
                        ];
                    }
                }
            }
            $roomSummaries[$roomName] = $roomTotal;
            $grandTotal['jumlah'] += $roomTotal['jumlah'];
            $grandTotal['baik'] += $roomTotal['baik'];
            $grandTotal['kb'] += $roomTotal['kb'];
            $grandTotal['rb'] += $roomTotal['rb'];
        }

        // Persentase kondisi
        $pctBaik = $grandTotal['jumlah'] > 0 ? round(($grandTotal['baik'] / $grandTotal['jumlah']) * 100, 1) : 0;
        $pctKb = $grandTotal['jumlah'] > 0 ? round(($grandTotal['kb'] / $grandTotal['jumlah']) * 100, 1) : 0;
        $pctRb = $grandTotal['jumlah'] > 0 ? round(($grandTotal['rb'] / $grandTotal['jumlah']) * 100, 1) : 0;

        // Nomor dokumen resmi
        $docNumber = 'INV/DISPERINDAG/' . date('Y') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $halamanSekarang = 0;

        // QR Code untuk verifikasi (jika BaconQrCode tersedia)
        $qrSvg = null;
        $verifyUrl = url('/');
        if (class_exists('\BaconQrCode\Writer')) {
            try {
                $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200, 2);
                $imageBackEnd = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
                $qrRenderer = new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $imageBackEnd);
                $writer = new \BaconQrCode\Writer($qrRenderer);
                $qrSvg = $writer->writeString($verifyUrl . '?doc=' . $docNumber);
            } catch (\Throwable $e) {
                $qrSvg = null;
            }
        }
    @endphp

    <!-- ═════════════════════════════════════════════════════════════ -->
    <!-- HALAMAN 1: COVER PAGE                                        -->
    <!-- ═════════════════════════════════════════════════════════════ -->
    <div class="cover-page">
        {{-- Kop Surat --}}
        <div class="cover-kop">
            <table class="cover-kop-table">
                <tr>
                    <td style="width: 80px;">
                        @if ($logoBase64)
                            <img src="{{ $logoBase64 }}" class="cover-kop-logo" alt="Logo">
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <h1>PEMERINTAH KABUPATEN KARAWANG</h1>
                        <h2>DINAS PERINDUSTRIAN DAN PERDAGANGAN</h2>
                        <p>Jl. Jend. Ahmad Yani No. 1, Karawang 41312, Jawa Barat</p>
                        <p>Telp. (0267) 401234 | Fax. (0267) 401235 | Email: disperindag@karawangkab.go.id</p>
                        <p>Website: www.disperindag.karawangkab.go.id</p>
                    </td>
                    <td style="width: 80px;"></td>
                </tr>
            </table>
        </div>

        {{-- Badge Klasifikasi --}}
        <div class="cover-badge">DOKUMEN RESMI</div>

        {{-- Logo Besar --}}
        @if ($logoBase64)
            <img src="{{ $logoBase64 }}" class="cover-logo-main" alt="Logo">
        @endif

        {{-- Judul --}}
        <div class="cover-title">LAPORAN INVENTARIS</div>
        <div class="cover-subtitle">ALAT UKUR, TAKAR, TIMBANG, DAN PERLENGKAPAN (UTTP)</div>
        <div class="cover-line"></div>

        {{-- Meta Box --}}
        <div class="cover-meta-box">
            <table>
                <tr>
                    <td class="label">Nomor Dokumen</td>
                    <td class="value">: <strong
                            style="font-family: 'Courier New', monospace; letter-spacing: 1px;">{{ $docNumber }}</strong>
                    </td>
                </tr>
                <tr>
                    <td class="label">Tahun Anggaran</td>
                    <td class="value">: {{ date('Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Periode Data</td>
                    <td class="value">: s.d. {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Total Ruangan</td>
                    <td class="value">: {{ $grandTotal['ruangan_count'] }} ruangan</td>
                </tr>
                <tr>
                    <td class="label">Total Unit Alat</td>
                    <td class="value">: <strong>{{ number_format($grandTotal['jumlah']) }} unit</strong></td>
                </tr>
                @if (count($filterInfo) > 0)
                    <tr>
                        <td class="label">Filter Laporan</td>
                        <td class="value">: {{ implode(' | ', $filterInfo) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="label">Tanggal Cetak</td>
                    <td class="value">: {{ $date }}</td>
                </tr>
            </table>
        </div>

        {{-- Distribusi --}}
        <div class="cover-distribution">
            <strong>DISTRIBUSI DOKUMEN:</strong>
            <ol>
                <li>Kepala Dinas Perindustrian dan Perdagangan (Asli)</li>
                <li>Kepala Bidang Metrologi (Salinan 1)</li>
                <li>Kasubag Perencanaan & Pelaporan (Salinan 2)</li>
                <li>Arsip Inventaris SISMALAT (Digital)</li>
            </ol>
        </div>

        {{-- QR Code Verifikasi --}}
        <div class="cover-verification">
            <table>
                <tr>
                    <td class="qr-cell">
                        @if ($qrSvg)
                            <img src="data:image/svg+xml;base64,{{ base64_encode($qrSvg) }}" class="qr-img"
                                alt="QR Verifikasi">
                        @else
                            <div
                                style="width: 80px; height: 80px; border: 1px dashed #f59e0b; display: flex; align-items: center; justify-content: center; font-size: 7px; color: #92400e;">
                                QR<br>Verifikasi</div>
                        @endif
                    </td>
                    <td class="info-cell">
                        <strong>🔐 DOKUMEN TERVERIFIKASI ELEKTRONIK</strong>
                        Scan QR Code di samping untuk memverifikasi keaslian dokumen ini melalui Sistem Informasi
                        Manajemen Alat (SISMALAT).<br>
                        <span class="doc-number">{{ $docNumber }}</span>
                    </td>
                </tr>
            </table>
        </div>

        <div class="cover-footer">
            Dokumen ini dicetak secara otomatis oleh sistem SISMALAT dan sah sebagai dokumen inventaris resmi pemerintah
            daerah
        </div>
    </div>

    <div class="page-break"></div>

    <!-- ═════════════════════════════════════════════════════════════ -->
    <!-- HALAMAN PER RUANGAN                                          -->
    <!-- ═════════════════════════════════════════════════════════════ -->
    @foreach ($groupedAlats as $roomName => $items)
        @php
            $halamanSekarang++;
            $roomTotal = $roomSummaries[$roomName];
            $roomData = null;
            foreach ($items as $it) {
                if ($it->ruangan && $it->ruangan->kode_ruangan . ' - ' . $it->ruangan->nama_ruangan === $roomName) {
                    $roomData = $it->ruangan;
                    break;
                }
            }
        @endphp

        <div class="room-section">
            {{-- Mini Header (menggantikan fixed header) --}}
            <div class="section-header">
                <table>
                    <tr>
                        <td style="width: 45px;">
                            @if ($logoBase64)
                                <img src="{{ $logoBase64 }}" class="mini-logo" alt="Logo">
                            @endif
                        </td>
                        <td style="padding-left: 8px;">
                            <h3>DINAS PERINDUSTRIAN DAN PERDAGANGAN</h3>
                            <p>Kabupaten Karawang • Laporan Inventaris {{ date('Y') }}</p>
                        </td>
                        <td class="right-info" style="width: 160px;">
                            <strong>{{ $docNumber }}</strong>
                            Halaman {{ $halamanSekarang + 2 }} dari {{ $totalHalaman }}
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Room Header --}}
            <div class="room-header no-break">
                <div class="left-col">
                    <h2>📍 {{ strtoupper($roomName) }}</h2>
                    <p>
                        @if ($roomData && isset($roomData->user))
                            Penanggung Jawab: <strong>{{ $roomData->user->name }}</strong>
                            @if (isset($roomData->user->nip) && $roomData->user->nip)
                                &nbsp;•&nbsp; NIP: {{ $roomData->user->nip }}
                            @endif
                        @else
                            <em>Tanpa penanggung jawab tercatat</em>
                        @endif
                    </p>
                </div>
                <div class="right-col">
                    <div class="room-stats">
                        <span><span class="label">Item:</span> <strong>{{ $items->count() }}</strong></span>
                        <span><span class="label">Unit:</span>
                            <strong>{{ number_format($roomTotal['jumlah']) }}</strong></span>
                        <span class="good"><span class="label">Baik:</span>
                            {{ number_format($roomTotal['baik']) }}</span>
                        <span class="warn"><span class="label">KB:</span>
                            {{ number_format($roomTotal['kb']) }}</span>
                        <span class="bad"><span class="label">RB:</span>
                            {{ number_format($roomTotal['rb']) }}</span>
                    </div>
                </div>
            </div>

            {{-- Data Table --}}
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 2.5%;" class="center">No</th>
                        <th style="width: 10%;">Kategori</th>
                        <th style="width: 20%;">Nama Alat & Spesifikasi</th>
                        <th style="width: 10%;">Merk / Tipe</th>
                        <th style="width: 8%;">No. Seri</th>
                        <th style="width: 7%;" class="center">Kapasitas</th>
                        <th style="width: 4%;" class="center">Σ</th>
                        <th style="width: 4%;" class="center">B</th>
                        <th style="width: 4%;" class="center">KB</th>
                        <th style="width: 4%;" class="center">RB</th>
                        <th style="width: 4%;" class="center">Thn</th>
                        <th style="width: 12%;">No. Sertifikat</th>
                        <th style="width: 7%;" class="center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $alat)
                        @php
                            $statusKalibrasi = 'N/A';
                            $badgeClass = 'badge-na';
                            if ($alat->masa_berlaku) {
                                $diff = \Carbon\Carbon::now()->diffInDays(
                                    \Carbon\Carbon::parse($alat->masa_berlaku),
                                    false,
                                );
                                if ($diff < 0) {
                                    $statusKalibrasi = 'EXPIRED';
                                    $badgeClass = 'badge-expired';
                                } elseif ($diff <= 30) {
                                    $statusKalibrasi = 'WARN';
                                    $badgeClass = 'badge-warning';
                                } else {
                                    $statusKalibrasi = 'AKTIF';
                                    $badgeClass = 'badge-active';
                                }
                            }
                        @endphp
                        <tr class="no-break">
                            <td class="num">{{ $index + 1 }}</td>
                            <td>{{ $alat->kategori ? $alat->kategori->nama : '-' }}</td>
                            <td>
                                <div class="tool-name">{{ $alat->nama_alat }}</div>
                                @if ($alat->ukuran || $alat->daya_baca)
                                    <div class="tool-spec">
                                        {{ $alat->ukuran ? $alat->ukuran . ' ' . $alat->satuan : '' }}
                                        {{ $alat->daya_baca ? '| d=' . $alat->daya_baca : '' }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ trim($alat->merk . ' ' . $alat->tipe) ?: '-' }}</td>
                            <td style="font-size: 7.5px;">{{ $alat->no_seri ?: ($alat->no_identitas ?: '-') }}</td>
                            <td class="num">{{ $alat->kapasitas ?: '-' }}</td>
                            <td class="num" style="font-size: 10px; font-weight: 900; color: #b45309;">
                                {{ $alat->jumlah }}</td>
                            <td class="num" style="color: #065f46; font-weight: 700;">{{ $alat->jumlah_baik }}</td>
                            <td class="num" style="color: #92400e; font-weight: 700;">
                                {{ $alat->jumlah_kurang_baik }}</td>
                            <td class="num" style="color: #991b1b; font-weight: 700;">
                                {{ $alat->jumlah_rusak_berat }}</td>
                            <td class="num">{{ $alat->tahun_pengadaan ?? '-' }}</td>
                            <td style="font-size: 7.5px;">{{ $alat->no_sertifikat ?: '-' }}</td>
                            <td class="center">
                                <span class="badge {{ $badgeClass }}">{{ $statusKalibrasi }}</span>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="subtotal-row">
                        <td colspan="6" style="text-align: right; padding-right: 10px;">SUBTOTAL
                            {{ strtoupper($roomName) }}:</td>
                        <td class="num">{{ number_format($roomTotal['jumlah']) }}</td>
                        <td class="num">{{ number_format($roomTotal['baik']) }}</td>
                        <td class="num">{{ number_format($roomTotal['kb']) }}</td>
                        <td class="num">{{ number_format($roomTotal['rb']) }}</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            {{-- Section Footer --}}
            <div class="section-footer">
                <table>
                    <tr>
                        <td>
                            <em>* Σ=Total, B=Baik, KB=Kurang Baik, RB=Rusak Berat, Thn=Tahun Pengadaan</em>
                        </td>
                        <td class="right">
                            Dicetak: {{ $date }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="page-break"></div>
    @endforeach

    <!-- ═════════════════════════════════════════════════════════════ -->
    <!-- HALAMAN REKAPITULASI                                         -->
    <!-- ═════════════════════════════════════════════════════════════ -->
    <div class="summary-page">
        {{-- Mini Header --}}
        <div class="section-header">
            <table>
                <tr>
                    <td style="width: 45px;">
                        @if ($logoBase64)
                            <img src="{{ $logoBase64 }}" class="mini-logo" alt="Logo">
                        @endif
                    </td>
                    <td style="padding-left: 8px;">
                        <h3>DINAS PERINDUSTRIAN DAN PERDAGANGAN</h3>
                        <p>Kabupaten Karawang • Laporan Inventaris {{ date('Y') }}</p>
                    </td>
                    <td class="right-info" style="width: 160px;">
                        <strong>{{ $docNumber }}</strong>
                        Halaman {{ $totalHalaman - 1 }} dari {{ $totalHalaman }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="summary-title">REKAPITULASI INVENTARIS</div>
        <div class="summary-subtitle">Seluruh Ruangan Dinas Perindustrian dan Perdagangan Kabupaten Karawang</div>
        <div class="summary-title-line"></div>

        {{-- Statistik Grid --}}
        <div class="stat-grid">
            <div class="stat-box total">
                <span class="icon">📦</span>
                <div class="label">Total Unit</div>
                <div class="value">{{ number_format($grandTotal['jumlah']) }}</div>
                <div class="pct">{{ $grandTotal['ruangan_count'] }} ruangan</div>
            </div>
            <div class="stat-box good">
                <span class="icon">✓</span>
                <div class="label">Kondisi Baik</div>
                <div class="value">{{ number_format($grandTotal['baik']) }}</div>
                <div class="pct">{{ $pctBaik }}%</div>
            </div>
            <div class="stat-box warn">
                <span class="icon">⚠</span>
                <div class="label">Kurang Baik</div>
                <div class="value">{{ number_format($grandTotal['kb']) }}</div>
                <div class="pct">{{ $pctKb }}%</div>
            </div>
            <div class="stat-box bad">
                <span class="icon">✗</span>
                <div class="label">Rusak Berat</div>
                <div class="value">{{ number_format($grandTotal['rb']) }}</div>
                <div class="pct">{{ $pctRb }}%</div>
            </div>
        </div>

        {{-- Visual Progress Bar --}}
        <table class="progress-bar">
            <tr>
                @if ($grandTotal['baik'] > 0)
                    <td style="background: #10b981; width: {{ $pctBaik }}%;"></td>
                @endif
                @if ($grandTotal['kb'] > 0)
                    <td style="background: #f59e0b; width: {{ $pctKb }}%;"></td>
                @endif
                @if ($grandTotal['rb'] > 0)
                    <td style="background: #ef4444; width: {{ $pctRb }}%;"></td>
                @endif
            </tr>
        </table>
        <div style="display: table; width: 100%; font-size: 8px; color: #475569; margin-top: 3px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="border: none; padding: 0;"><span
                            style="display: inline-block; width: 8px; height: 8px; background: #10b981; margin-right: 3px;"></span>Baik
                    </td>
                    <td style="border: none; padding: 0; text-align: center;"><span
                            style="display: inline-block; width: 8px; height: 8px; background: #f59e0b; margin-right: 3px;"></span>Kurang
                        Baik</td>
                    <td style="border: none; padding: 0; text-align: right;"><span
                            style="display: inline-block; width: 8px; height: 8px; background: #ef4444; margin-right: 3px;"></span>Rusak
                        Berat</td>
                </tr>
            </table>
        </div>

        {{-- Tabel Rekap --}}
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 30%;">Ruangan</th>
                    <th style="width: 10%;" class="center">Σ Item</th>
                    <th style="width: 10%;" class="center">Σ Unit</th>
                    <th style="width: 10%;" class="center">Baik</th>
                    <th style="width: 10%;" class="center">K. Baik</th>
                    <th style="width: 10%;" class="center">R. Berat</th>
                    <th style="width: 16%;">Kondisi</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($roomSummaries as $roomName => $rTotal)
                    @php
                        $kondisiPct = $rTotal['jumlah'] > 0 ? round(($rTotal['baik'] / $rTotal['jumlah']) * 100) : 0;
                        $kondisiLabel = $kondisiPct >= 80 ? 'BAIK' : ($kondisiPct >= 50 ? 'CUKUP' : 'BURUK');
                        $kondisiColor = $kondisiPct >= 80 ? '#10b981' : ($kondisiPct >= 50 ? '#f59e0b' : '#ef4444');
                    @endphp
                    <tr>
                        <td class="center" style="text-align: center;">{{ $no++ }}</td>
                        <td><strong>{{ strtoupper($roomName) }}</strong></td>
                        <td class="center" style="text-align: center;">{{ $groupedAlats[$roomName]->count() }}</td>
                        <td class="center" style="text-align: center; font-weight: 700;">
                            {{ number_format($rTotal['jumlah']) }}</td>
                        <td class="center" style="text-align: center; color: #065f46; font-weight: 600;">
                            {{ number_format($rTotal['baik']) }}</td>
                        <td class="center" style="text-align: center; color: #92400e; font-weight: 600;">
                            {{ number_format($rTotal['kb']) }}</td>
                        <td class="center" style="text-align: center; color: #991b1b; font-weight: 600;">
                            {{ number_format($rTotal['rb']) }}</td>
                        <td>
                            <div style="display: table; width: 100%;">
                                <div
                                    style="display: table-cell; width: 30px; font-weight: 800; color: {{ $kondisiColor }}; font-size: 10px;">
                                    {{ $kondisiPct }}%</div>
                                <div style="display: table-cell;">
                                    <div
                                        style="height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                        <div
                                            style="height: 6px; width: {{ $kondisiPct }}%; background: {{ $kondisiColor }};">
                                        </div>
                                    </div>
                                    <div
                                        style="font-size: 7px; color: {{ $kondisiColor }}; margin-top: 1px; font-weight: 700;">
                                        {{ $kondisiLabel }}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" style="text-align: right; padding-right: 10px;">GRAND TOTAL:</td>
                    <td class="center" style="text-align: center;">{{ $alats->count() }}</td>
                    <td class="center" style="text-align: center;">{{ number_format($grandTotal['jumlah']) }}</td>
                    <td class="center" style="text-align: center;">{{ number_format($grandTotal['baik']) }}</td>
                    <td class="center" style="text-align: center;">{{ number_format($grandTotal['kb']) }}</td>
                    <td class="center" style="text-align: center;">{{ number_format($grandTotal['rb']) }}</td>
                    <td class="center" style="text-align: center;"><strong>{{ $pctBaik }}%</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- Alert Kalibrasi --}}
        @if (count($alatPerluKalibrasi) > 0)
            <div class="alert-box">
                <strong>⚠ PERHATIAN: {{ count($alatPerluKalibrasi) }} Alat Memerlukan Tindak Lanjut Kalibrasi</strong>
                <ul>
                    @foreach (array_slice($alatPerluKalibrasi, 0, 5) as $alert)
                        <li>
                            <strong>{{ $alert['nama'] }}</strong> ({{ $alert['ruangan'] }}) —
                            @if ($alert['status'] === 'EXPIRED')
                                <span style="color: #991b1b;">Telah EXPIRED sejak
                                    {{ \Carbon\Carbon::parse($alert['tgl'])->format('d M Y') }}</span>
                            @else
                                <span style="color: #92400e;">Segera expired pada
                                    {{ \Carbon\Carbon::parse($alert['tgl'])->format('d M Y') }}</span>
                            @endif
                        </li>
                    @endforeach
                    @if (count($alatPerluKalibrasi) > 5)
                        <li><em>...dan {{ count($alatPerluKalibrasi) - 5 }} alat lainnya. Lihat detail di halaman per
                                ruangan.</em></li>
                    @endif
                </ul>
            </div>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- ═════════════════════════════════════════════════════════════ -->
    <!-- HALAMAN TANDA TANGAN                                         -->
    <!-- ═════════════════════════════════════════════════════════════ -->
    <div class="signature-page">
        {{-- Mini Header --}}
        <div class="section-header">
            <table>
                <tr>
                    <td style="width: 45px;">
                        @if ($logoBase64)
                            <img src="{{ $logoBase64 }}" class="mini-logo" alt="Logo">
                        @endif
                    </td>
                    <td style="padding-left: 8px;">
                        <h3>DINAS PERINDUSTRIAN DAN PERDAGANGAN</h3>
                        <p>Kabupaten Karawang • Laporan Inventaris {{ date('Y') }}</p>
                    </td>
                    <td class="right-info" style="width: 160px;">
                        <strong>{{ $docNumber }}</strong>
                        Halaman {{ $totalHalaman }} dari {{ $totalHalaman }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="signature-title">LEMBAR PENGESAHAN</div>
        <div class="signature-subtitle">
            Laporan Inventaris ini telah diperiksa dan disahkan sebagai dokumen resmi inventaris aset daerah
        </div>

        {{-- Tabel TTD --}}
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-role">Mengetahui,</div>
                    <div class="signature-role" style="font-size: 11px;">KEPALA BIDANG METROLOGI</div>
                    <div class="signature-note">Penanggung Jawab Inventaris</div>
                    <div class="signature-space"></div>
                    <div class="signature-name">( ..................................................... )</div>
                    <div class="signature-nip">NIP. ...........................................</div>
                </td>
                <td>
                    <div class="signature-role">Karawang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                    <div class="signature-role" style="font-size: 11px;">PETUGAS INVENTARIS</div>
                    <div class="signature-note">Pencatat & Pelapor</div>

                    {{-- Kartu TTE Digital --}}
                    <div class="ttd-card"
                        style="margin-top: 10px; max-width: 280px; margin-left: auto; margin-right: auto;">
                        <div class="ttd-card-header">✓ DITANDATANGANI SECARA ELEKTRONIK</div>
                        <div class="ttd-card-body">
                            <table>
                                <tr>
                                    <td class="ttd-qr">
                                        @if ($qrSvg)
                                            <img src="data:image/svg+xml;base64,{{ base64_encode($qrSvg) }}"
                                                alt="QR TTE">
                                        @else
                                            <div
                                                style="width: 70px; height: 70px; border: 1px dashed #065f46; display: flex; align-items: center; justify-content: center; font-size: 7px; color: #065f46;">
                                                QR</div>
                                        @endif
                                    </td>
                                    <td class="ttd-info">
                                        <strong>SISMALAT v1.0</strong>
                                        Sistem Inventaris Digital<br>
                                        <span class="code">{{ $docNumber }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="ttd-card-footer">
                            Scan untuk verifikasi keaslian dokumen
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Footer Legal --}}
        <div class="footer-legal">
            <strong>📜 KETERANGAN DOKUMEN</strong>
            Dokumen ini digenerate secara otomatis oleh <strong>Sistem Informasi Manajemen Alat (SISMALAT)</strong> —
            aplikasi resmi inventaris alat ukur, takar, timbang, dan perlengkapan (UTTP) Dinas Perindustrian dan
            Perdagangan Kabupaten Karawang.
            Berdasarkan <strong>UU No. 11 Tahun 2008 tentang ITE</strong> dan <strong>PP No. 71 Tahun 2019</strong>,
            dokumen elektronik yang ditandatangani secara elektronik memiliki kekuatan hukum yang sama dengan dokumen
            bertanda tangan basah.
            <br><br>
            <strong>🔐 KEAMANAN &amp; VERIFIKASI</strong>
            Setiap dokumen dilengkapi dengan QR Code unik yang terhubung langsung ke database SISMALAT.
            Pihak ketiga dapat memverifikasi keaslian dokumen dengan memindai QR Code atau mengakses URL verifikasi
            resmi.
            Dokumen yang telah dimodifikasi atau dipalsukan tidak akan lolos verifikasi sistem.
        </div>
    </div>
</body>

</html>
