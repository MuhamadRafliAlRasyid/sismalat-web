<!DOCTYPE html>
<html lang="id">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Inventaris Alat Ukur</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            margin: 15mm 15mm 25mm 15mm;
            size: A4 landscape;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
        }

        /* ========== PAGE BREAK ========== */
        .page-break {
            page-break-after: always;
            page-break-inside: avoid;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* ========== HEADER (REPEAT DI SETIAP HALAMAN) ========== */
        .kop-header {
            position: fixed;
            top: -10mm;
            left: 0;
            right: 0;
            height: 20mm;
        }

        .kop-table {
            width: 100%;
            border-bottom: 3px double #b45309;
            padding-bottom: 5px;
        }

        .kop-table td {
            vertical-align: middle;
            padding: 0;
            border: none;
        }

        .kop-logo {
            width: 55px;
            height: 55px;
        }

        .kop-info h1 {
            font-size: 14px;
            color: #92400e;
            font-weight: 900;
            margin: 0;
        }

        .kop-info h2 {
            font-size: 11px;
            color: #1e293b;
            font-weight: 700;
            margin: 2px 0;
        }

        .kop-info p {
            font-size: 8px;
            color: #64748b;
            margin: 1px 0;
        }

        /* ========== FOOTER (NOMOR HALAMAN) ========== */
        .page-footer {
            position: fixed;
            bottom: -20mm;
            left: 0;
            right: 0;
            height: 15mm;
            text-align: center;
            font-size: 9px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 5px;
        }

        /* ========== COVER PAGE ========== */
        .cover-page {
            padding-top: 30mm;
            text-align: center;
        }

        .cover-logo {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
        }

        .cover-title {
            font-size: 28px;
            font-weight: 900;
            color: #92400e;
            margin: 30px 0 10px;
            letter-spacing: 2px;
        }

        .cover-subtitle {
            font-size: 16px;
            color: #b45309;
            margin-bottom: 40px;
            font-weight: 600;
        }

        .cover-line {
            width: 200px;
            height: 3px;
            background: linear-gradient(to right, #f59e0b, #f97316);
            margin: 30px auto;
        }

        .cover-info {
            margin-top: 50px;
            font-size: 11px;
            color: #475569;
        }

        .cover-info strong {
            color: #1e293b;
        }

        .cover-meta {
            margin-top: 80px;
            padding: 20px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        /* ========== SECTION RUANGAN ========== */
        .room-section {
            padding-top: 10mm;
        }

        .room-header {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #f59e0b;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 12px;
        }

        .room-header h2 {
            font-size: 16px;
            color: #92400e;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .room-header p {
            font-size: 10px;
            color: #78350f;
            margin-top: 3px;
        }

        .room-meta {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 10px;
            font-size: 9px;
        }

        .room-meta span {
            display: inline-block;
            margin-right: 20px;
        }

        .room-meta strong {
            color: #1e293b;
        }

        /* ========== TABLE ========== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-bottom: 10px;
        }

        .data-table thead th {
            background: #92400e;
            color: white;
            font-weight: 700;
            padding: 7px 5px;
            border: 1px solid #78350f;
            text-align: left;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.5px;
        }

        .data-table thead th.center {
            text-align: center;
        }

        .data-table tbody td {
            padding: 6px 5px;
            border: 1px solid #e2e8f0;
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
        }

        .data-table .tool-spec {
            font-size: 8px;
            color: #64748b;
            margin-top: 2px;
        }

        /* Subtotal */
        .subtotal-row td {
            background: #fef3c7 !important;
            font-weight: 800;
            color: #92400e;
            border-top: 2px solid #f59e0b;
            padding: 8px 5px;
        }

        /* ========== GRAND TOTAL PAGE ========== */
        .summary-page {
            padding-top: 20mm;
        }

        .summary-title {
            text-align: center;
            font-size: 20px;
            font-weight: 900;
            color: #92400e;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 3px double #f59e0b;
            padding-bottom: 10px;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 10px;
        }

        .summary-table th {
            background: #92400e;
            color: white;
            padding: 10px;
            border: 1px solid #78350f;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary-table td {
            padding: 8px 10px;
            border: 1px solid #e2e8f0;
        }

        .summary-table tr:nth-child(even) {
            background: #fffbeb;
        }

        .summary-table .total-row {
            background: #b45309 !important;
            color: white;
            font-weight: 900;
            font-size: 11px;
        }

        .summary-table .total-row td {
            border-color: #78350f;
        }

        .summary-stats {
            display: table;
            width: 100%;
            margin: 20px 0;
        }

        .stat-box {
            display: table-cell;
            width: 25%;
            padding: 15px 10px;
            text-align: center;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .stat-box .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-box .value {
            font-size: 22px;
            font-weight: 900;
            color: #1e293b;
        }

        .stat-box.total {
            background: #fef3c7;
        }

        .stat-box.total .value {
            color: #b45309;
        }

        .stat-box.good {
            background: #d1fae5;
        }

        .stat-box.good .value {
            color: #065f46;
        }

        .stat-box.warn {
            background: #fef3c7;
        }

        .stat-box.warn .value {
            color: #92400e;
        }

        .stat-box.bad {
            background: #fee2e2;
        }

        .stat-box.bad .value {
            color: #991b1b;
        }

        /* ========== SIGNATURE ========== */
        .signature-section {
            margin-top: 50px;
            width: 100%;
        }

        .signature-section td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px;
            font-size: 10px;
        }

        .signature-label {
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 60px;
        }

        .signature-line {
            border-top: 1px solid #1e293b;
            padding-top: 5px;
            font-weight: 600;
        }

        .footer-note {
            margin-top: 40px;
            font-size: 8px;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 10px;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>

<body>
    @php
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

        $groupedAlats = $alats->groupBy(function ($item) {
            return $item->ruangan
                ? $item->ruangan->kode_ruangan . ' - ' . $item->ruangan->nama_ruangan
                : 'TANPA RUANGAN';
        });

        $grandTotal = ['jumlah' => 0, 'baik' => 0, 'kb' => 0, 'rb' => 0, 'ruangan_count' => $groupedAlats->count()];
        $roomSummaries = [];

        foreach ($groupedAlats as $roomName => $items) {
            $roomTotal = ['jumlah' => 0, 'baik' => 0, 'kb' => 0, 'rb' => 0];
            foreach ($items as $alat) {
                $roomTotal['jumlah'] += $alat->jumlah;
                $roomTotal['baik'] += $alat->jumlah_baik;
                $roomTotal['kb'] += $alat->jumlah_kurang_baik;
                $roomTotal['rb'] += $alat->jumlah_rusak_berat;
            }
            $roomSummaries[$roomName] = $roomTotal;
            $grandTotal['jumlah'] += $roomTotal['jumlah'];
            $grandTotal['baik'] += $roomTotal['baik'];
            $grandTotal['kb'] += $roomTotal['kb'];
            $grandTotal['rb'] += $roomTotal['rb'];
        }
    @endphp

    <!-- HEADER FIX (TAMPIL DI SETIAP HALAMAN) -->
    <div class="kop-header">
        <table class="kop-table">
            <tr>
                <td style="width: 70px;">
                    @if ($logoBase64)
                        <img src="{{ $logoBase64 }}" class="kop-logo" alt="Logo">
                    @endif
                </td>
                <td class="kop-info" style="padding-left: 15px;">
                    <h1>DINAS PERINDUSTRIAN & PERDAGANGAN</h1>
                    <h2>KABUPATEN KARAWANG</h2>
                    <p>Jl. Contoh Alamat No. 123, Karawang, Jawa Barat</p>
                    <p>Telp. (0267) 123456 | Email: perindag@karawangkab.go.id</p>
                </td>
                <td style="text-align: right; font-size: 8px; color: #64748b; width: 150px;">
                    <strong>LAPORAN INVENTARIS</strong><br>
                    Dicetak: {{ $date }}
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER (NOMOR HALAMAN) -->
    <div class="page-footer">
        <strong>SISMALAT</strong> - Sistem Informasi Manajemen Alat Ukur &nbsp;&bull;&nbsp;
        Dokumen ini digenerate secara elektronik dan sah sebagai dokumen resmi
    </div>

    <!-- ═══════════════ HALAMAN 1: COVER ═══════════════ -->
    <div class="cover-page">
        @if ($logoBase64)
            <img src="{{ $logoBase64 }}" class="cover-logo" alt="Logo">
        @endif

        <div class="cover-title">LAPORAN INVENTARIS</div>
        <div class="cover-subtitle">ALAT UKUR & PERLENGKAPAN</div>
        <div class="cover-line"></div>

        <div class="cover-info">
            <p><strong>DINAS PERINDUSTRIAN & PERDAGANGAN</strong></p>
            <p><strong>KABUPATEN KARAWANG</strong></p>
            <p style="margin-top: 10px;">Tahun Anggaran {{ date('Y') }}</p>
        </div>

        <div class="cover-meta">
            <p style="font-size: 10px; margin-bottom: 8px;"><strong>INFORMASI LAPORAN</strong></p>
            <p style="font-size: 9px;">
                @if (count($filterInfo) > 0)
                    <strong>Filter:</strong> {{ implode(' | ', $filterInfo) }}<br>
                @else
                    <strong>Filter:</strong> Menampilkan Seluruh Data Alat<br>
                @endif
                <strong>Total Ruangan:</strong> {{ $grandTotal['ruangan_count'] }} ruangan<br>
                <strong>Total Unit Alat:</strong> {{ number_format($grandTotal['jumlah']) }} unit<br>
                <strong>Tanggal Cetak:</strong> {{ $date }}
            </p>
        </div>
    </div>

    <!-- PAGE BREAK SETELAH COVER -->
    <div class="page-break"></div>

    <!-- ═══════════════ HALAMAN PER RUANGAN ═══════════════ -->
    @foreach ($groupedAlats as $roomName => $items)
        @php
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
            <div class="room-header no-break">
                <h2>📍 RUANGAN: {{ strtoupper($roomName) }}</h2>
                <p>
                    @if ($roomData && $roomData->user)
                        Penanggung Jawab: <strong>{{ $roomData->user->name }}</strong>
                        @if ($roomData->user->nip)
                            &nbsp;|&nbsp; NIP: {{ $roomData->user->nip }}
                        @endif
                    @else
                        <em>Tanpa penanggung jawab</em>
                    @endif
                </p>
            </div>

            <div class="room-meta no-break">
                <span><strong>Total Item:</strong> {{ $items->count() }} jenis alat</span>
                <span><strong>Total Unit:</strong> {{ number_format($roomTotal['jumlah']) }} unit</span>
                <span style="color: #065f46;"><strong>Baik:</strong> {{ number_format($roomTotal['baik']) }}</span>
                <span style="color: #92400e;"><strong>K. Baik:</strong> {{ number_format($roomTotal['kb']) }}</span>
                <span style="color: #991b1b;"><strong>R. Berat:</strong> {{ number_format($roomTotal['rb']) }}</span>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 3%;" class="center">No</th>
                        <th style="width: 13%;">Kategori</th>
                        <th style="width: 25%;">Nama Alat & Spesifikasi</th>
                        <th style="width: 12%;">Merk / Tipe</th>
                        <th style="width: 10%;">No. Seri</th>
                        <th style="width: 8%;" class="center">Kapasitas</th>
                        <th style="width: 5%;" class="center">Total</th>
                        <th style="width: 5%;" class="center">Baik</th>
                        <th style="width: 6%;" class="center">K. Baik</th>
                        <th style="width: 6%;" class="center">R. Berat</th>
                        <th style="width: 7%;">No. Sertifikat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $index => $alat)
                        <tr class="no-break">
                            <td class="num">{{ $index + 1 }}</td>
                            <td>{{ $alat->kategori ? $alat->kategori->nama : '-' }}</td>
                            <td>
                                <div class="tool-name">{{ $alat->nama_alat }}</div>
                                @if ($alat->ukuran || $alat->daya_baca)
                                    <div class="tool-spec">
                                        {{ $alat->ukuran ? $alat->ukuran . ' ' . $alat->satuan : '' }}
                                        {{ $alat->daya_baca ? '| Daya baca: ' . $alat->daya_baca : '' }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ trim($alat->merk . ' ' . $alat->tipe) ?: '-' }}</td>
                            <td>{{ $alat->no_seri ?: ($alat->no_identitas ?: '-') }}</td>
                            <td class="num">{{ $alat->kapasitas ?: '-' }}</td>
                            <td class="num" style="font-size: 11px; font-weight: 900; color: #b45309;">
                                {{ $alat->jumlah }}</td>
                            <td class="num" style="color: #065f46;">{{ $alat->jumlah_baik }}</td>
                            <td class="num" style="color: #92400e;">{{ $alat->jumlah_kurang_baik }}</td>
                            <td class="num" style="color: #991b1b;">{{ $alat->jumlah_rusak_berat }}</td>
                            <td style="font-size: 8px;">{{ $alat->no_sertifikat ?: '-' }}</td>
                        </tr>
                    @endforeach
                    <tr class="subtotal-row">
                        <td colspan="6" style="text-align: right; padding-right: 15px;">SUBTOTAL RUANGAN
                            {{ strtoupper($roomName) }}:</td>
                        <td class="num">{{ number_format($roomTotal['jumlah']) }}</td>
                        <td class="num">{{ number_format($roomTotal['baik']) }}</td>
                        <td class="num">{{ number_format($roomTotal['kb']) }}</td>
                        <td class="num">{{ number_format($roomTotal['rb']) }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGE BREAK SETIAP AKHIR RUANGAN -->
        <div class="page-break"></div>
    @endforeach

    <!-- ═══════════════ HALAMAN TERAKHIR: GRAND SUMMARY ═══════════════ -->
    <div class="summary-page">
        <div class="summary-title">REKAPITULASI SELURUH RUANGAN</div>

        <!-- STATISTIK UTAMA -->
        <div class="summary-stats">
            <div class="stat-box total">
                <div class="label">Total Unit</div>
                <div class="value">{{ number_format($grandTotal['jumlah']) }}</div>
            </div>
            <div class="stat-box good">
                <div class="label">Kondisi Baik</div>
                <div class="value">{{ number_format($grandTotal['baik']) }}</div>
            </div>
            <div class="stat-box warn">
                <div class="label">Kurang Baik</div>
                <div class="value">{{ number_format($grandTotal['kb']) }}</div>
            </div>
            <div class="stat-box bad">
                <div class="label">Rusak Berat</div>
                <div class="value">{{ number_format($grandTotal['rb']) }}</div>
            </div>
        </div>

        <!-- TABEL REKAP PER RUANGAN -->
        <table class="summary-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Ruangan</th>
                    <th style="width: 12%;" class="center">Jumlah Item</th>
                    <th style="width: 12%;" class="center">Total Unit</th>
                    <th style="width: 12%;" class="center">Baik</th>
                    <th style="width: 12%;" class="center">Kurang Baik</th>
                    <th style="width: 12%;" class="center">Rusak Berat</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($roomSummaries as $roomName => $rTotal)
                    <tr>
                        <td class="center" style="text-align: center;">{{ $no++ }}</td>
                        <td><strong>{{ strtoupper($roomName) }}</strong></td>
                        <td style="text-align: center;">{{ $groupedAlats[$roomName]->count() }}</td>
                        <td style="text-align: center; font-weight: 700;">{{ number_format($rTotal['jumlah']) }}</td>
                        <td style="text-align: center; color: #065f46;">{{ number_format($rTotal['baik']) }}</td>
                        <td style="text-align: center; color: #92400e;">{{ number_format($rTotal['kb']) }}</td>
                        <td style="text-align: center; color: #991b1b;">{{ number_format($rTotal['rb']) }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="2" style="text-align: right; padding-right: 20px;">GRAND TOTAL:</td>
                    <td style="text-align: center;">{{ $alats->count() }}</td>
                    <td style="text-align: center;">{{ number_format($grandTotal['jumlah']) }}</td>
                    <td style="text-align: center;">{{ number_format($grandTotal['baik']) }}</td>
                    <td style="text-align: center;">{{ number_format($grandTotal['kb']) }}</td>
                    <td style="text-align: center;">{{ number_format($grandTotal['rb']) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- TANDA TANGAN -->
        <table class="signature-section">
            <tr>
                <td>
                    <div class="signature-label">Mengetahui,<br>Kepala Bidang Metrologi</div>
                    <div class="signature-line">( ........................................ )<br>NIP.</div>
                </td>
                <td>
                    <div class="signature-label">Karawang, {{ $date }}<br>Petugas Inventaris</div>
                    <div class="signature-line">( ........................................ )<br>NIP.</div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            * Dokumen ini digenerate secara elektronik oleh Sistem Informasi Manajemen Alat (SISMALAT)<br>
            dan sah sebagai dokumen inventaris resmi tanpa tanda tangan basah.
        </div>
    </div>
</body>

</html>
