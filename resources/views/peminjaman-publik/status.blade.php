<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Status Peminjaman - Sismalat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-amber-50 flex items-center justify-center p-4">
    <main class="w-full max-w-xl bg-white rounded-2xl shadow-xl p-6 sm:p-8">
        <p class="text-sm font-bold uppercase tracking-wider text-amber-700">
            Sismalat
        </p>

        <h1 class="text-2xl font-extrabold mt-2 text-gray-900">
            Status Peminjaman Alat
        </h1>

        <div class="mt-6 rounded-xl bg-amber-50 p-4">
            <p class="text-sm text-gray-600">Status saat ini</p>
            <p class="text-xl font-bold mt-1">
                {{ $peminjaman->status === 'kembali' ? 'Sudah Dikembalikan' : 'Sedang Dipinjam' }}
            </p>
        </div>

        <dl class="mt-6 space-y-4 text-sm">
            <div>
                <dt class="text-gray-500">Nama Peminjam</dt>
                <dd class="font-semibold">
                    {{ $peminjaman->nama_peminjam }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Alat</dt>
                <dd class="font-semibold">
                    {{ $peminjaman->alat?->nama_alat ?? 'Data alat tidak tersedia' }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Jumlah</dt>
                <dd class="font-semibold">
                    {{ $peminjaman->jumlah }} {{ $peminjaman->satuan }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Tanggal Pengambilan</dt>
                <dd class="font-semibold">
                    {{ $peminjaman->waktu_pengambilan?->format('d-m-Y H:i') ?? '-' }}
                </dd>
            </div>

            <div>
                <dt class="text-gray-500">Lama Peminjaman</dt>
                <dd class="font-semibold">
                    {{ $peminjaman->lama_pinjam }} hari
                </dd>
            </div>
        </dl>

        <p class="mt-8 text-xs text-gray-500">
            Tautan ini bersifat pribadi. Jangan bagikan kepada orang lain.
        </p>

        <a href="{{ route('landing') }}"
            class="inline-block mt-5 rounded-lg bg-amber-500
                  hover:bg-amber-600 text-white font-semibold
                  px-5 py-3 transition">
            Kembali ke Beranda
        </a>
    </main>
</body>

</html>
