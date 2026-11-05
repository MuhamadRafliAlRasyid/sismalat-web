SISMALAT
Sistem Manajemen Alat

{{ $statusTitle }}

Yth. {{ $borrowerName }},

{{ $statusDescription }}

========================================
STATUS PEMINJAMAN
========================================

{{ $statusLabel }}

Tanggal jatuh tempo: {{ $dueDate }}

========================================
RINGKASAN PEMINJAMAN
========================================

Nama Peminjam : {{ $borrowerName }}
Tanggal Pengambilan : {{ $borrowedAt }}
Lama Peminjaman : {{ $loanDays }} hari
Jumlah Jenis Alat : {{ $totalJenis }} jenis
Total Jumlah Alat : {{ $totalQuantity }} unit
Tanggal Jatuh Tempo : {{ $dueDate }}

========================================
DAFTAR ALAT
========================================

@foreach ($items as $item)
    - {{ $item['name'] }}: {{ $item['quantity'] }} {{ $item['unit'] }}
@endforeach

@if ($detailUrl)
    Lihat Status Peminjaman:
    {{ $detailUrl }}
@endif

Mohon segera melakukan pengembalian alat sesuai ketentuan.

Apabila Anda telah mengembalikan alat dan statusnya sudah diperbarui oleh petugas,
abaikan pemberitahuan ini.

----------------------------------------
SISMALAT - Sistem Manajemen Alat

Email ini dikirim secara otomatis oleh sistem.
Mohon tidak membalas email ini.

Dikirim pada: {{ $generatedAt }}
