@extends('layouts.app')

@section('title', 'Detail Pengembalian Alat')

@section('content')
    @php
        $me = auth()->user();
        $isAdmin = $me && in_array($me->role ?? null, ['admin', 'super'], true);
        $pengambilan = $data->pengambilan;
        $item = $data->item;
        $alat = $item->alat ?? ($pengambilan->alat ?? null);
        $peminjam = $pengambilan->nama_peminjam ?? ($pengambilan->user->name ?? '-');
        $bagian = $pengambilan->bagian->nama ?? '-';
        $unit = $item->satuan ?? ($pengambilan->satuan ?? ($alat->satuan ?? 'unit'));
        $returnDate = $data->tanggal_pengembalian
            ? \Carbon\Carbon::parse($data->tanggal_pengembalian)->startOfDay()
            : null;
        $dueDate =
            $pengambilan && $pengambilan->tanggal_jatuh_tempo
                ? \Carbon\Carbon::parse($pengambilan->tanggal_jatuh_tempo)->startOfDay()
                : null;
        $isLate = $returnDate && $dueDate && $returnDate->greaterThan($dueDate);
        $lateDays = $isLate ? (int) $dueDate->diffInDays($returnDate) : 0;
        $batchCode = $pengambilan ? strtoupper(substr($pengambilan->hashid ?? '', 0, 8)) : '-';
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-7 sm:py-8" x-data="{
        photoModal: false,
        photoSrc: '',
        photoTitle: '',
        openPhoto(src, title) {
            this.photoSrc = src;
            this.photoTitle = title;
            this.photoModal = true;
        }
    }">

        <a href="{{ route('pengembalian_alat.index') }}"
            class="mb-5 inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-emerald-600 dark:text-gray-400 dark:hover:text-emerald-400">
            <span
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                <i class="fas fa-arrow-left"></i>
            </span>
            Kembali ke riwayat pengembalian
        </a>

        <section
            class="relative mb-6 overflow-hidden rounded-3xl border border-emerald-100 dark:border-gray-700 bg-gradient-to-br from-emerald-50 via-white to-teal-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900 p-6 sm:p-8">
            <div
                class="pointer-events-none absolute -right-10 -top-16 h-52 w-52 rounded-full bg-emerald-200/30 dark:bg-emerald-500/10 blur-3xl">
            </div>
            <div class="relative flex flex-col sm:flex-row sm:items-start gap-4">
                <span
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg"><i
                        class="fas fa-clipboard-check text-xl"></i></span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold uppercase tracking-[.16em] text-emerald-700 dark:text-emerald-400">Bukti
                        transaksi pengembalian</p>
                    <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">Detail Pengembalian
                        Alat</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Catatan pengembalian untuk batch <span
                            class="font-mono font-semibold">#{{ $batchCode }}</span>.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 px-3 py-1.5 text-xs font-bold text-emerald-800 dark:text-emerald-300"><i
                                class="fas fa-check-circle"></i> Sudah dikembalikan</span>
                        @if ($isLate)
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-red-100 dark:bg-red-900/40 px-3 py-1.5 text-xs font-bold text-red-800 dark:text-red-300"><i
                                    class="fas fa-clock"></i> Terlambat {{ $lateDays }} hari</span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 dark:bg-blue-900/40 px-3 py-1.5 text-xs font-bold text-blue-800 dark:text-blue-300"><i
                                    class="fas fa-calendar-check"></i> Tepat waktu</span>
                        @endif
                        @if ($alat && $alat->status === 'expired')
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-red-100 dark:bg-red-900/40 px-3 py-1.5 text-xs font-bold text-red-800 dark:text-red-300"><i
                                    class="fas fa-triangle-exclamation"></i> Kalibrasi expired</span>
                        @elseif ($alat && $alat->status === 'warning')
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-yellow-100 dark:bg-yellow-900/40 px-3 py-1.5 text-xs font-bold text-yellow-800 dark:text-yellow-300"><i
                                    class="fas fa-exclamation-triangle"></i> Kalibrasi warning</span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <section
                class="lg:col-span-2 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 sm:p-6 shadow-sm">
                <div class="mb-5 flex items-center gap-3">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-900/25 text-amber-600"><i
                            class="fas fa-toolbox"></i></span>
                    <div>
                        <h2 class="font-bold text-gray-900 dark:text-white">Informasi alat</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Alat dan jumlah yang dicatat pada transaksi ini.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-4">
                    @if ($alat && $alat->foto_url)
                        <button type="button"
                            @click="openPhoto(@js($alat->foto_url), @js($alat->nama_alat ?? 'Foto alat'))"
                            class="group relative h-44 w-full sm:w-48 shrink-0 overflow-hidden rounded-2xl bg-gray-100 dark:bg-gray-900">
                            <img src="{{ $alat->foto_thumb ?? $alat->foto_url }}" alt="{{ $alat->nama_alat }}"
                                class="h-full w-full object-cover transition group-hover:scale-105">
                            <span
                                class="absolute inset-0 flex items-center justify-center bg-black/0 text-white opacity-0 transition group-hover:bg-black/30 group-hover:opacity-100"><i
                                    class="fas fa-magnifying-glass-plus"></i></span>
                        </button>
                    @else
                        <div
                            class="flex h-36 w-full sm:w-40 shrink-0 items-center justify-center rounded-2xl bg-gray-50 dark:bg-gray-900 text-gray-300 dark:text-gray-600">
                            <i class="fas fa-tools text-4xl"></i>
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Nama alat</p>
                        <h3 class="mt-1 text-xl font-extrabold text-gray-900 dark:text-white">
                            {{ $alat->nama_alat ?? 'Alat tidak tersedia' }}</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $alat->merk ?? '-' }}
                            @if ($alat && $alat->tipe)
                                · {{ $alat->tipe }}
                            @endif
                        </p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-gray-50 dark:bg-gray-900 p-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Nomor seri</p>
                                <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $alat->no_seri ?? '-' }}</p>
                            </div>
                            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 p-3">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:text-emerald-400">
                                    Jumlah dikembalikan</p>
                                <p class="mt-1 text-xl font-extrabold text-emerald-700 dark:text-emerald-300">
                                    {{ number_format($data->jumlah) }}
                                    <span class="text-xs font-semibold">{{ $unit }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($data->keterangan)
                    <div
                        class="mt-6 rounded-2xl border border-amber-100 dark:border-amber-900/40 bg-amber-50/70 dark:bg-amber-900/10 p-4">
                        <p class="mb-1 text-xs font-bold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                            <i class="fas fa-note-sticky mr-1"></i> Keterangan pengembalian
                        </p>
                        <p class="whitespace-pre-line text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                            {{ $data->keterangan }}</p>
                    </div>
                @endif
            </section>

            <aside class="space-y-5">
                <section
                    class="rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-900/25 text-blue-600 dark:text-blue-300"><i
                                class="fas fa-user"></i></span>
                        <h2 class="font-bold text-gray-900 dark:text-white">Informasi peminjam</h2>
                    </div>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Nama peminjam</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $peminjam }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Bagian</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $bagian }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Dicatat oleh</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $data->user->name ?? '-' }}</dd>
                        </div>
                    </dl>
                </section>

                <section
                    class="rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
                    <div class="mb-4 flex items-center gap-3">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-900/25 text-violet-600 dark:text-violet-300"><i
                                class="fas fa-calendar-check"></i></span>
                        <h2 class="font-bold text-gray-900 dark:text-white">Waktu transaksi</h2>
                    </div>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Tanggal pengembalian
                            </dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $data->tanggal_pengembalian ? $data->tanggal_pengembalian->translatedFormat('d F Y') : '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Tanggal jatuh tempo</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $dueDate ? $dueDate->translatedFormat('d F Y') : '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Nomor batch</dt>
                            <dd class="mt-0.5 font-mono text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ $batchCode }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Nomor catatan</dt>
                            <dd class="mt-0.5 font-mono text-sm font-semibold text-gray-800 dark:text-gray-200">
                                {{ strtoupper(substr($data->hashid, 0, 10)) }}</dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>

        @if ($data->foto)
            <section
                class="mt-5 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 sm:p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-900/25 text-emerald-600"><i
                            class="fas fa-camera"></i></span>
                    <div>
                        <h2 class="font-bold text-gray-900 dark:text-white">Foto bukti pengembalian</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Klik foto untuk melihat ukuran penuh.</p>
                    </div>
                </div>
                <button type="button" @click="openPhoto(@js($data->foto_url), 'Foto bukti pengembalian')"
                    class="group relative block w-full overflow-hidden rounded-2xl bg-gray-50 dark:bg-gray-900">
                    <img src="{{ $data->foto_url }}" alt="Foto bukti pengembalian"
                        class="mx-auto max-h-96 max-w-full object-contain transition group-hover:opacity-90">
                    <span
                        class="absolute bottom-3 right-3 rounded-lg bg-black/60 px-3 py-1.5 text-xs font-semibold text-white"><i
                            class="fas fa-expand mr-1"></i> Perbesar</span>
                </button>
            </section>
        @endif

        <section class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
            <a href="{{ route('pengembalian_alat.index') }}"
                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-5 py-3 font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700"><i
                    class="fas fa-list"></i> Kembali ke riwayat</a>
            @if ($pengambilan)
                <a href="{{ route('pengambilan_alat.show', $pengambilan->hashid) }}"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 px-5 py-3 font-semibold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40"><i
                        class="fas fa-box-open"></i> Lihat batch peminjaman</a>
            @endif
            @if ($isAdmin)
                <a href="{{ route('pengembalian_alat.edit', $data->hashid) }}"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white hover:bg-emerald-700"><i
                        class="fas fa-pen"></i> Edit catatan</a>
            @endif
            @if ($alat && in_array($alat->status, ['expired', 'warning'], true))
                <a href="{{ route('kalibrasis.create', $alat->hashid) }}"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-yellow-500 px-5 py-3 font-semibold text-white hover:bg-yellow-600"><i
                        class="fas fa-wrench"></i> Kalibrasi alat</a>
            @endif
        </section>

        <div x-show="photoModal" x-cloak x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4 backdrop-blur-sm"
            @click.self="photoModal = false" @keydown.escape.window="photoModal = false">
            <div class="relative w-full max-w-5xl">
                <button type="button" @click="photoModal = false"
                    class="absolute -right-2 -top-12 flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-800 shadow-lg"
                    aria-label="Tutup foto"><i class="fas fa-times"></i></button>
                <img :src="photoSrc" :alt="photoTitle"
                    class="mx-auto max-h-[85vh] max-w-full rounded-2xl object-contain shadow-2xl">
                <p x-text="photoTitle" class="mt-3 text-center text-sm text-white"></p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush
