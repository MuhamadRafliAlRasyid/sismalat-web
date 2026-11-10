<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Bukti Pengambilan - SISMALAT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            min-height: 100vh;
        }

        .glass-card {
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.85);
        }
    </style>
</head>

<body class="py-8 px-4">
    <div class="max-w-2xl mx-auto">

        {{-- Header Instansi --}}
        <div class="text-center mb-6">
            <h1 class="text-xl font-bold text-gray-900">DINAS PERINDUSTRIAN & PERDAGANGAN</h1>
            <p class="text-sm text-gray-600">Kabupaten Karawang</p>
            <p class="text-xs text-gray-500 mt-1">Sistem Informasi Manajemen Alat (SISMALAT)</p>
        </div>

        @php
            $isApproved = !is_null($data->approved_at);
            $approverName = $data->approved_by_name ?? ($data->approver ? $data->approver->name : null);
            $approvedAtStr = $data->approved_at ? $data->approved_at->translatedFormat('d M Y, H:i') : null;
        @endphp

        {{-- Status Card --}}
        @if ($isApproved)
            {{-- ✅ DOKUMEN VALID --}}
            <div class="glass-card rounded-3xl shadow-2xl border-2 border-emerald-300 overflow-hidden mb-6">
                <div class="bg-gradient-to-br from-emerald-500 to-green-600 p-6 text-center">
                    <div
                        class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm mx-auto flex items-center justify-center mb-3">
                        <i class="fas fa-shield-check text-white text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">DOKUMEN VALID</h2>
                    <p class="text-emerald-50 text-sm mt-1">Bukti pengambilan telah disetujui dan terverifikasi</p>
                </div>

                <div class="p-6 space-y-4">
                    <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-200">
                        <div class="flex items-start gap-3">
                            <span
                                class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-user-check text-emerald-600"></i>
                            </span>
                            <div>
                                <p class="text-xs text-emerald-700 uppercase font-semibold tracking-wide">Disetujui Oleh
                                </p>
                                <p class="font-bold text-gray-900">{{ $approverName ?? 'Admin' }}</p>
                                <p class="text-xs text-gray-600">{{ $data->approved_by_role ?? 'Admin' }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    {{ $approvedAtStr }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            {{-- ⚠️ DOKUMEN BELUM DISETUJUI --}}
            <div class="glass-card rounded-3xl shadow-2xl border-2 border-yellow-300 overflow-hidden mb-6">
                <div class="bg-gradient-to-br from-yellow-500 to-orange-500 p-6 text-center">
                    <div
                        class="w-20 h-20 rounded-full bg-white/20 backdrop-blur-sm mx-auto flex items-center justify-center mb-3">
                        <i class="fas fa-hourglass-half text-white text-4xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-white">MENUNGGU PERSETUJUAN</h2>
                    <p class="text-yellow-50 text-sm mt-1">Dokumen ini belum disetujui oleh admin</p>
                </div>
            </div>
        @endif

        {{-- Detail Transaksi --}}
        <div class="glass-card rounded-3xl shadow-xl border border-gray-200 overflow-hidden mb-6">
            <div class="bg-gray-50 border-b border-gray-200 px-6 py-4">
                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-file-invoice text-amber-600"></i>
                    Detail Pengambilan Alat
                </h3>
            </div>

            <div class="p-6 space-y-4">
                {{-- Info Peminjam --}}
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Nomor Bukti</p>
                        <p class="font-mono font-bold text-gray-900">{{ strtoupper(substr($data->hashid, 0, 12)) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Status</p>
                        <p class="font-semibold text-gray-900">{{ $data->status_label }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Peminjam</p>
                        <p class="font-semibold text-gray-900">
                            {{ $data->nama_peminjam ?? ($data->user->name ?? '-') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Bagian</p>
                        <p class="font-semibold text-gray-900">{{ $data->bagian->nama ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Tanggal Ambil</p>
                        <p class="font-semibold text-gray-900">
                            {{ $data->waktu_pengambilan?->format('d M Y, H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Lama Pinjam</p>
                        <p class="font-semibold text-gray-900">{{ $data->lama_pinjam ?? '-' }} hari</p>
                    </div>
                </div>

                {{-- Keperluan --}}
                @if ($data->keperluan)
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Keperluan</p>
                        <p class="text-sm text-gray-800">{{ $data->keperluan }}</p>
                    </div>
                @endif

                {{-- Daftar Alat --}}
                @if ($data->items->isNotEmpty())
                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-3">Daftar Alat
                            ({{ $data->items->count() }} item)</p>
                        <div class="space-y-2">
                            @foreach ($data->items as $item)
                                <div class="flex items-center gap-3 bg-gray-50 rounded-lg p-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-tools text-amber-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-gray-900 text-sm truncate">
                                            {{ $item->alat->nama_alat ?? 'Alat dihapus' }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $item->alat->merk ?? '-' }} {{ $item->alat->tipe ?? '' }}
                                        </p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="font-bold text-amber-600 text-sm">{{ $item->jumlah }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->satuan }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-600 space-y-2">
            <p>
                <i class="fas fa-link mr-1"></i>
                URL ini adalah bukti otentik dari sistem SISMALAT
            </p>
            <p class="text-gray-500">
                Dicetak pada: {{ now()->translatedFormat('d M Y, H:i') }} WIB
            </p>
            <p class="mt-4 pt-4 border-t border-amber-300/50">
                © {{ date('Y') }} Dinas Perindustrian & Perdagangan Kabupaten Karawang
            </p>
        </div>
    </div>
</body>

</html>
