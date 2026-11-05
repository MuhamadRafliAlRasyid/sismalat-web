<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>{{ $statusTitle }} | {{ $appName }}</title>
</head>

<body
    style="
    margin: 0;
    padding: 0;
    background-color: #f1f5f9;
    font-family: Arial, Helvetica, sans-serif;
    color: #1e293b;
">

    {{-- Preheader --}}
    <div
        style="
        display: none;
        font-size: 1px;
        color: #f1f5f9;
        line-height: 1px;
        max-height: 0;
        max-width: 0;
        opacity: 0;
        overflow: hidden;
    ">
        {{ $statusDescription }}
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background-color: #f1f5f9; padding: 30px 12px;">

        <tr>
            <td align="center">

                <table width="100%" cellpadding="0" cellspacing="0" border="0"
                    style="
                        max-width: 640px;
                        background-color: #ffffff;
                        border-radius: 14px;
                        overflow: hidden;
                        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.07);
                    ">

                    {{-- HEADER --}}
                    <tr>
                        <td
                            style="
                            background-color: #172033;
                            padding: 26px 32px;
                            border-bottom: 4px solid #e6a817;
                        ">

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="48" valign="middle">
                                        <div
                                            style="
                                            width: 42px;
                                            height: 42px;
                                            line-height: 42px;
                                            text-align: center;
                                            background-color: #e6a817;
                                            color: #172033;
                                            border-radius: 10px;
                                            font-size: 25px;
                                            font-weight: bold;
                                        ">
                                            S
                                        </div>
                                    </td>

                                    <td valign="middle" style="padding-left: 12px;">
                                        <div
                                            style="
                                            font-size: 20px;
                                            font-weight: bold;
                                            color: #ffffff;
                                            letter-spacing: 1px;
                                        ">
                                            SISMALAT
                                        </div>

                                        <div
                                            style="
                                            font-size: 11px;
                                            color: #cbd5e1;
                                            margin-top: 4px;
                                            letter-spacing: 0.5px;
                                        ">
                                            SISTEM MANAJEMEN ALAT
                                        </div>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- CONTENT --}}
                    <tr>
                        <td style="padding: 34px 32px 20px;">

                            <div
                                style="
                                font-size: 11px;
                                font-weight: bold;
                                color: #b45309;
                                letter-spacing: 1.5px;
                                margin-bottom: 12px;
                            ">
                                PEMBERITAHUAN PEMINJAMAN
                            </div>

                            <h1
                                style="
                                font-size: 25px;
                                line-height: 1.35;
                                color: #172033;
                                margin: 0 0 18px;
                            ">
                                {{ $statusTitle }}
                            </h1>

                            <p
                                style="
                                font-size: 14px;
                                line-height: 1.8;
                                color: #475569;
                                margin: 0 0 14px;
                            ">
                                Yth. <strong style="color: #172033;">
                                    {{ $borrowerName }}
                                </strong>,
                            </p>

                            <p
                                style="
                                font-size: 14px;
                                line-height: 1.8;
                                color: #475569;
                                margin: 0 0 24px;
                            ">
                                {{ $statusDescription }}
                            </p>

                            {{-- STATUS CARD --}}
                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="
                                    background-color: {{ $isOverdue ? '#fef2f2' : '#fffbeb' }};
                                    border: 1px solid {{ $isOverdue ? '#fecaca' : '#fde68a' }};
                                    border-radius: 10px;
                                    margin-bottom: 28px;
                                ">
                                <tr>
                                    <td style="padding: 20px 22px;">

                                        <div
                                            style="
                                            font-size: 10px;
                                            font-weight: bold;
                                            letter-spacing: 1px;
                                            color: {{ $isOverdue ? '#991b1b' : '#92400e' }};
                                            margin-bottom: 8px;
                                        ">
                                            STATUS PEMINJAMAN
                                        </div>

                                        <div
                                            style="
                                            font-size: 24px;
                                            font-weight: bold;
                                            color: {{ $isOverdue ? '#b91c1c' : '#b45309' }};
                                            margin-bottom: 7px;
                                        ">
                                            {{ $statusLabel }}
                                        </div>

                                        <div
                                            style="
                                            font-size: 12px;
                                            color: {{ $isOverdue ? '#7f1d1d' : '#78350f' }};
                                        ">
                                            Tanggal jatuh tempo:
                                            <strong>{{ $dueDate }}</strong>
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            {{-- LOAN DETAILS --}}
                            <h2
                                style="
                                font-size: 16px;
                                color: #172033;
                                margin: 0 0 14px;
                            ">
                                Ringkasan Peminjaman
                            </h2>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="
                                    border: 1px solid #e2e8f0;
                                    border-radius: 9px;
                                    margin-bottom: 28px;
                                ">

                                @php
                                    $details = [
                                        'Nama Peminjam' => $borrowerName,
                                        'Tanggal Pengambilan' => $borrowedAt,
                                        'Lama Peminjaman' => $loanDays . ' hari',
                                        'Jumlah Jenis Alat' => $totalJenis . ' jenis',
                                        'Total Jumlah Alat' => $totalQuantity . ' unit',
                                        'Tanggal Jatuh Tempo' => $dueDate,
                                    ];
                                @endphp

                                @foreach ($details as $label => $value)
                                    <tr>
                                        <td width="43%"
                                            style="
                                            padding: 12px 14px;
                                            font-size: 12px;
                                            color: #64748b;
                                            border-bottom: 1px solid #f1f5f9;
                                        ">
                                            {{ $label }}
                                        </td>

                                        <td
                                            style="
                                            padding: 12px 14px;
                                            font-size: 12px;
                                            font-weight: bold;
                                            color: #334155;
                                            border-bottom: 1px solid #f1f5f9;
                                        ">
                                            {{ $value }}
                                        </td>
                                    </tr>
                                @endforeach

                            </table>

                            {{-- ITEM LIST --}}
                            <h2
                                style="
                                font-size: 16px;
                                color: #172033;
                                margin: 0 0 14px;
                            ">
                                Daftar Alat yang Dipinjam
                            </h2>

                            <table width="100%" cellpadding="0" cellspacing="0" border="0"
                                style="
                                    border: 1px solid #e2e8f0;
                                    border-radius: 9px;
                                    margin-bottom: 30px;
                                ">

                                <tr style="background-color: #f8fafc;">
                                    <th align="left"
                                        style="
                                        padding: 12px 14px;
                                        font-size: 11px;
                                        color: #475569;
                                    ">
                                        Nama Alat
                                    </th>

                                    <th align="center"
                                        style="
                                        padding: 12px 14px;
                                        font-size: 11px;
                                        color: #475569;
                                    ">
                                        Jumlah
                                    </th>
                                </tr>

                                @forelse ($items as $item)
                                    <tr>
                                        <td
                                            style="
                                            padding: 13px 14px;
                                            font-size: 12px;
                                            color: #334155;
                                            border-top: 1px solid #f1f5f9;
                                        ">
                                            {{ $item['name'] }}
                                        </td>

                                        <td align="center"
                                            style="
                                            padding: 13px 14px;
                                            font-size: 12px;
                                            color: #334155;
                                            font-weight: bold;
                                            border-top: 1px solid #f1f5f9;
                                        ">
                                            {{ $item['quantity'] }} {{ $item['unit'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2"
                                            style="
                                            padding: 14px;
                                            font-size: 12px;
                                            color: #64748b;
                                        ">
                                            Informasi alat tidak tersedia.
                                        </td>
                                    </tr>
                                @endforelse

                            </table>

                            {{-- ACTION BUTTON --}}
                            @if ($detailUrl)
                                <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td align="center">

                                            <table cellpadding="0" cellspacing="0" border="0">
                                                <tr>
                                                    <td align="center" bgcolor="#e6a817" style="border-radius: 8px;">

                                                        <a href="{{ $detailUrl }}" target="_blank"
                                                            style="
                                                                display: inline-block;
                                                                padding: 15px 27px;
                                                                color: #172033;
                                                                font-size: 13px;
                                                                font-weight: bold;
                                                                text-decoration: none;
                                                                border-radius: 8px;
                                                            ">
                                                            Lihat Status Peminjaman
                                                        </a>

                                                    </td>
                                                </tr>
                                            </table>

                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p
                                style="
                                font-size: 12px;
                                line-height: 1.7;
                                color: #64748b;
                                margin: 26px 0 10px;
                            ">
                                Apabila Anda telah melakukan pengembalian alat,
                                mohon abaikan pemberitahuan ini apabila status
                                pengembalian sudah diperbarui oleh petugas.
                            </p>

                        </td>
                    </tr>

                    {{-- FOOTER --}}
                    <tr>
                        <td
                            style="
                            padding: 24px 32px;
                            background-color: #f8fafc;
                            border-top: 1px solid #e2e8f0;
                        ">

                            <div
                                style="
                                font-size: 12px;
                                font-weight: bold;
                                color: #334155;
                                margin-bottom: 7px;
                            ">
                                {{ $appName }} — Sistem Manajemen Alat
                            </div>

                            <div
                                style="
                                font-size: 11px;
                                line-height: 1.7;
                                color: #64748b;
                            ">
                                Email ini dikirim secara otomatis oleh sistem
                                sebagai pemberitahuan terkait aktivitas peminjaman alat.
                                Mohon tidak membalas email ini.
                            </div>

                            <div
                                style="
                                font-size: 10px;
                                color: #94a3b8;
                                margin-top: 16px;
                            ">
                                Dibuat pada {{ $generatedAt }}
                            </div>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
