@extends('layouts.app')

@section('title', 'Data Alat')

@section('content')
    @php
        $currentUser = auth()->user();
        $isAdmin = $currentUser && in_array($currentUser->role, ['admin', 'super'], true);
        $hasFilters =
            request()->filled('search') || request()->filled('kategori_id') || request()->filled('ruangan_id');
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        {{-- Page heading --}}
        <header
            class="mb-6 overflow-hidden rounded-2xl border border-amber-200/70 bg-gradient-to-br from-amber-50 via-white to-white shadow-sm dark:border-gray-700 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900">
            <div class="flex flex-col gap-5 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <div
                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white shadow-lg shadow-amber-500/20 sm:h-14 sm:w-14">
                        <i class="fas fa-toolbox text-xl sm:text-2xl" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <span
                                class="text-xs font-bold uppercase tracking-[0.16em] text-amber-700 dark:text-amber-400">Inventaris</span>
                            @if ($hasFilters)
                                <span
                                    class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                                    Filter aktif
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl dark:text-white">Data
                            Alat</h1>
                        <p class="mt-1.5 max-w-2xl text-sm leading-relaxed text-gray-600 sm:text-base dark:text-gray-300">
                            Temukan informasi alat, periksa status kalibrasi, dan kelola data inventaris dalam satu tempat.
                        </p>
                    </div>
                </div>

                @if ($isAdmin)
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:flex lg:shrink-0">
                        <a href="{{ route('alats.create') }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-amber-600 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-amber-200 dark:focus:ring-amber-900">
                            <i class="fas fa-plus" aria-hidden="true"></i>
                            Tambah Alat
                        </a>
                        <a href="{{ route('alats.trashed') }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-100 dark:border-red-900 dark:bg-gray-800 dark:text-red-300 dark:hover:bg-red-950/50">
                            <i class="fas fa-trash-restore" aria-hidden="true"></i>
                            Data Terhapus
                        </a>
                    </div>
                @endif
            </div>
        </header>

        {{-- Search and filters --}}
        <section
            class="mb-7 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-6 dark:border-gray-700 dark:bg-gray-800"
            aria-labelledby="filter-heading">
            <div class="mb-5 flex items-start gap-3">
                <span
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </span>
                <div>
                    <h2 id="filter-heading" class="text-base font-bold text-gray-900 sm:text-lg dark:text-white">Cari dan
                        filter alat</h2>
                    <p class="mt-1 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                        Cari berdasarkan nama, merk, nomor seri, atau nomor identitas. Gunakan kategori dan ruangan untuk
                        mempersempit hasil.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('alats.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <label for="search"
                            class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Kata kunci</label>
                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                <i class="fas fa-search" aria-hidden="true"></i>
                            </span>
                            <input id="search" type="search" name="search" value="{{ request('search') }}"
                                placeholder="Contoh: mikrometer, Mitutoyo, SN-12345..."
                                class="min-h-11 w-full rounded-xl border border-gray-300 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder:text-gray-400 transition focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-amber-500 dark:focus:ring-amber-900/40">
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="kategori_id"
                            class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Kategori</label>
                        <select id="kategori_id" name="kategori_id"
                            class="min-h-11 w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900/40">
                            <option value="">Semua kategori</option>
                            @foreach ($kategoris ?? [] as $kategori)
                                <option value="{{ $kategori->id }}" @selected((string) request('kategori_id') === (string) $kategori->id)>
                                    {{ $kategori->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-4">
                        <label for="ruangan_id"
                            class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Ruangan</label>
                        <select id="ruangan_id" name="ruangan_id"
                            class="min-h-11 w-full rounded-xl border border-gray-300 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 transition focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900/40">
                            <option value="">Semua ruangan</option>
                            @foreach ($ruangans ?? [] as $ruangan)
                                <option value="{{ $ruangan->id }}" @selected((string) request('ruangan_id') === (string) $ruangan->id)>
                                    {{ $ruangan->kode_ruangan }} — {{ $ruangan->nama_ruangan }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div
                    class="flex flex-col gap-2 border-t border-gray-100 pt-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <button type="submit"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-200 dark:focus:ring-amber-900">
                        <i class="fas fa-filter" aria-hidden="true"></i>
                        Terapkan Filter
                    </button>
                    @if ($hasFilters)
                        <a href="{{ route('alats.index') }}"
                            class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            <i class="fas fa-times" aria-hidden="true"></i>
                            Hapus Filter
                        </a>
                    @endif
                    <p class="text-xs text-gray-500 sm:ml-auto dark:text-gray-400">
                        <i class="fas fa-lightbulb mr-1 text-amber-500" aria-hidden="true"></i>
                        Gunakan nomor seri atau identitas untuk pencarian spesifik.
                    </p>
                </div>
            </form>
        </section>

        {{-- List heading and export actions --}}
        <section class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-extrabold tracking-tight text-gray-900 dark:text-white">Daftar Alat</h2>
                    @if (method_exists($alats, 'total'))
                        <span
                            class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            {{ number_format($alats->total(), 0, ',', '.') }} data
                        </span>
                    @endif
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Pilih nama alat untuk melihat seluruh variannya.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:flex">
                <a href="{{ route('alats.export.excel', request()->query()) }}"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-green-200 bg-green-50 px-3.5 py-2 text-sm font-semibold text-green-800 transition hover:bg-green-100 focus:outline-none focus:ring-4 focus:ring-green-100 dark:border-green-900 dark:bg-green-950/50 dark:text-green-300 dark:hover:bg-green-900/60">
                    <i class="fas fa-file-excel" aria-hidden="true"></i>
                    Export Excel
                </a>
                <a href="{{ route('alats.export.pdf', request()->query()) }}"
                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-sm font-semibold text-red-800 transition hover:bg-red-100 focus:outline-none focus:ring-4 focus:ring-red-100 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300 dark:hover:bg-red-900/60">
                    <i class="fas fa-file-pdf" aria-hidden="true"></i>
                    Export PDF
                </a>
            </div>
        </section>

        {{-- Tool groups --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 xl:gap-5">
            @forelse ($alats as $namaAlatNormalized => $alatItems)
                @php
                    $namaAlatDisplay =
                        $alatItems->pluck('nama_alat')->countBy()->sortDesc()->keys()->first() ?? 'Alat tanpa nama';
                    $hasExpired = $alatItems->contains(fn($alat) => $alat->status === 'expired');
                    $hasWarning = $alatItems->contains(fn($alat) => $alat->status === 'warning');
                    $statusAlat = $hasExpired ? 'expired' : ($hasWarning ? 'warning' : 'active');

                    $statusLabel =
                        $statusAlat === 'expired'
                            ? 'Ada yang kedaluwarsa'
                            : ($statusAlat === 'warning'
                                ? 'Perlu perhatian'
                                : 'Status baik');

                    $statusClass =
                        $statusAlat === 'expired'
                            ? 'border-red-200 bg-red-50 text-red-700 dark:border-red-900 dark:bg-red-950/40 dark:text-red-300'
                            : ($statusAlat === 'warning'
                                ? 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300'
                                : 'border-green-200 bg-green-50 text-green-700 dark:border-green-900 dark:bg-green-950/40 dark:text-green-300');

                    $folderId =
                        'folder-' . \Illuminate\Support\Str::slug((string) $namaAlatNormalized) . '-' . $loop->index;
                @endphp

                <section x-data="{ open: false }"
                    class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition duration-200 hover:border-amber-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-amber-700">
                    <button type="button"
                        class="flex w-full items-center gap-3 p-4 text-left transition hover:bg-amber-50/60 focus:outline-none focus:ring-4 focus:ring-inset focus:ring-amber-100 sm:gap-4 sm:p-5 dark:hover:bg-gray-700/50 dark:focus:ring-amber-900/30"
                        @click="open = !open" :aria-expanded="open.toString()" aria-controls="{{ $folderId }}">
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 transition group-hover:bg-amber-500 group-hover:text-white dark:bg-amber-900/50 dark:text-amber-300">
                            <i class="fas text-lg" :class="open ? 'fa-folder-open' : 'fa-folder'" aria-hidden="true"></i>
                        </span>

                        <span class="min-w-0 flex-1">
                            <span
                                class="block break-words text-base font-bold leading-snug text-gray-900 sm:text-lg dark:text-white">{{ $namaAlatDisplay }}</span>
                            <span class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span><i class="fas fa-cubes mr-1" aria-hidden="true"></i>{{ $alatItems->count() }}
                                    varian</span>
                                <span aria-hidden="true">•</span>
                                <span
                                    class="inline-flex items-center rounded-full border px-2 py-0.5 font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                            </span>
                        </span>

                        <span class="flex shrink-0 items-center gap-2 text-gray-400 dark:text-gray-300">
                            <span class="hidden text-xs font-semibold sm:inline"
                                x-text="open ? 'Tutup' : 'Lihat'">Lihat</span>
                            <i class="fas fa-chevron-down text-sm transition-transform duration-200"
                                :class="open ? 'rotate-180 text-amber-600' : ''" aria-hidden="true"></i>
                        </span>
                    </button>

                    <div id="{{ $folderId }}" x-cloak x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <div
                            class="border-t border-gray-100 bg-gray-50/80 p-3 sm:p-4 dark:border-gray-700 dark:bg-gray-900/40">
                            <div class="mb-3 flex items-center justify-between gap-3 px-1">
                                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Daftar varian</h3>
                                <span
                                    class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $alatItems->count() }}
                                    item</span>
                            </div>

                            <div class="space-y-3">
                                @foreach ($alatItems as $alat)
                                    @php
                                        $itemStatus = $alat->status;
                                        $itemStatusLabel =
                                            $itemStatus === 'expired'
                                                ? 'Kedaluwarsa'
                                                : ($itemStatus === 'warning'
                                                    ? 'Segera kalibrasi'
                                                    : 'Status baik');
                                        $itemStatusClass =
                                            $itemStatus === 'expired'
                                                ? 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300'
                                                : ($itemStatus === 'warning'
                                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'
                                                    : 'bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300');

                                        $alatLabel =
                                            $alat->nama_alat .
                                            ' (' .
                                            ($alat->kapasitas ?: '-') .
                                            ' / ' .
                                            ($alat->kelas ?: '-') .
                                            ')';
                                        $noSeri = $alat->no_seri ?? null;
                                        $noIdentitas =
                                            $alat->no_identitas ??
                                            ($alat->nomor_identitas ??
                                                ($alat->identitas ?? ($alat->kode_alat ?? null)));
                                    @endphp

                                    <article
                                        class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm transition hover:border-gray-300 sm:p-4 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-gray-600">
                                        <div class="flex flex-col gap-4 sm:flex-row">
                                            {{-- Photo --}}
                                            <button type="button"
                                                class="group/photo relative h-40 w-full shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-100 focus:outline-none focus:ring-4 focus:ring-amber-200 sm:h-28 sm:w-28 dark:border-gray-700 dark:bg-gray-700"
                                                @click="$dispatch('quick-view', { url: @js($alat->foto ? $alat->foto_url : ''), name: @js($alat->nama_alat) })"
                                                aria-label="Lihat foto {{ $alat->nama_alat }}">
                                                @if ($alat->foto)
                                                    <img src="{{ $alat->foto_thumb }}" alt="Foto {{ $alat->nama_alat }}"
                                                        loading="lazy"
                                                        class="h-full w-full object-cover transition duration-300 group-hover/photo:scale-105"
                                                        onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');">
                                                    <span
                                                        class="hidden h-full w-full items-center justify-center text-gray-400">
                                                        <i class="fas fa-toolbox text-3xl" aria-hidden="true"></i>
                                                    </span>
                                                @else
                                                    <span
                                                        class="flex h-full w-full items-center justify-center text-gray-400 dark:text-gray-500">
                                                        <i class="fas fa-toolbox text-3xl" aria-hidden="true"></i>
                                                    </span>
                                                @endif
                                                <span
                                                    class="absolute inset-x-0 bottom-0 bg-gray-950/60 px-2 py-1.5 text-center text-xs font-semibold text-white opacity-100 sm:opacity-0 sm:transition group-hover/photo:opacity-100">Lihat
                                                    foto</span>
                                            </button>

                                            {{-- Tool information --}}
                                            <div class="min-w-0 flex-1">
                                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold {{ $itemStatusClass }}">
                                                        @if ($itemStatus === 'expired')
                                                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                                                        @elseif ($itemStatus === 'warning')
                                                            <i class="fas fa-clock" aria-hidden="true"></i>
                                                        @else
                                                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                                                        @endif
                                                        {{ $itemStatusLabel }}
                                                    </span>

                                                    @if ($alat->status_hari !== null && in_array($itemStatus, ['warning', 'expired'], true))
                                                        @if ($itemStatus === 'warning')
                                                            <span
                                                                class="text-xs font-semibold text-amber-700 dark:text-amber-300">{{ $alat->status_hari }}
                                                                hari lagi</span>
                                                        @else
                                                            <span
                                                                class="text-xs font-semibold text-red-700 dark:text-red-300">Terlambat
                                                                {{ abs($alat->status_hari) }} hari</span>
                                                        @endif
                                                    @endif
                                                </div>

                                                @if ($noIdentitas)
                                                    <div class="mb-3">
                                                        <span
                                                            class="inline-flex max-w-full items-center gap-1.5 break-all rounded-lg bg-indigo-50 px-2.5 py-1.5 font-mono text-xs font-bold text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300">
                                                            <i class="fas fa-id-card shrink-0" aria-hidden="true"></i>
                                                            {{ $noIdentitas }}
                                                        </span>
                                                    </div>
                                                @endif

                                                <dl class="grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-3">
                                                    <div class="min-w-0">
                                                        <dt
                                                            class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                            Merk</dt>
                                                        <dd
                                                            class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                            {{ $alat->merk ?: 'Belum diisi' }}</dd>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <dt
                                                            class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                            Tipe</dt>
                                                        <dd
                                                            class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                            {{ $alat->tipe ?: '-' }}</dd>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <dt
                                                            class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                            No. Seri</dt>
                                                        <dd
                                                            class="mt-1 break-all font-mono text-xs font-semibold text-gray-800 dark:text-gray-100">
                                                            {{ $noSeri ?: '-' }}</dd>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <dt
                                                            class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                            No. Identitas</dt>
                                                        <dd
                                                            class="mt-1 break-all font-mono text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                                                            {{ $noIdentitas ?: '-' }}</dd>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <dt
                                                            class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                            Kapasitas</dt>
                                                        <dd
                                                            class="mt-1 break-words text-sm font-bold text-amber-700 dark:text-amber-300">
                                                            {{ $alat->kapasitas ?: '-' }}</dd>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <dt
                                                            class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                                            Kelas</dt>
                                                        <dd
                                                            class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100">
                                                            {{ $alat->kelas ?: '-' }}</dd>
                                                    </div>
                                                </dl>
                                            </div>
                                        </div>

                                        {{-- Actions --}}
                                        <div
                                            class="mt-4 grid grid-cols-2 gap-2 border-t border-gray-100 pt-3 sm:flex sm:flex-wrap sm:justify-end dark:border-gray-700">
                                            <a href="{{ route('alats.show', $alat->hashid) }}"
                                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800 transition hover:bg-amber-100 focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-amber-900 dark:bg-amber-950/50 dark:text-amber-300 dark:hover:bg-amber-900/60">
                                                <i class="fas fa-eye" aria-hidden="true"></i> Detail
                                            </a>

                                            @if (in_array($itemStatus, ['expired', 'warning'], true))
                                                <a href="{{ route('kalibrasis.create', $alat->hashid) }}"
                                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-yellow-200 bg-yellow-50 px-3 py-2 text-xs font-bold text-yellow-800 transition hover:bg-yellow-100 focus:outline-none focus:ring-4 focus:ring-yellow-100 dark:border-yellow-900 dark:bg-yellow-950/50 dark:text-yellow-300 dark:hover:bg-yellow-900/60">
                                                    <i class="fas fa-tools" aria-hidden="true"></i> Kalibrasi
                                                </a>
                                            @endif

                                            @if ($isAdmin)
                                                <a href="{{ route('alats.edit', $alat->hashid) }}"
                                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-bold text-blue-800 transition hover:bg-blue-100 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-300 dark:hover:bg-blue-900/60">
                                                    <i class="fas fa-edit" aria-hidden="true"></i> Edit
                                                </a>
                                                <button type="button"
                                                    @click="$dispatch('open-delete-modal', { id: @js($alat->hashid), name: @js($alatLabel) })"
                                                    class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-bold text-red-800 transition hover:bg-red-100 focus:outline-none focus:ring-4 focus:ring-red-100 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300 dark:hover:bg-red-900/60">
                                                    <i class="fas fa-trash-alt" aria-hidden="true"></i> Hapus
                                                </button>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @empty
                <div
                    class="col-span-full rounded-2xl border-2 border-dashed border-gray-300 bg-white px-5 py-14 text-center dark:border-gray-700 dark:bg-gray-800">
                    <span
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-300">
                        <i class="fas fa-box-open text-3xl" aria-hidden="true"></i>
                    </span>
                    <h3 class="mt-5 text-lg font-bold text-gray-900 dark:text-white">Data alat tidak ditemukan</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                        Tidak ada data yang sesuai dengan pencarian atau filter saat ini. Coba kata kunci lain atau hapus
                        filter yang digunakan.
                    </p>
                    <div class="mt-5 flex flex-col justify-center gap-2 sm:flex-row">
                        @if ($hasFilters)
                            <a href="{{ route('alats.index') }}"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                <i class="fas fa-times" aria-hidden="true"></i> Hapus Filter
                            </a>
                        @endif
                        @if ($isAdmin)
                            <a href="{{ route('alats.create') }}"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-amber-600">
                                <i class="fas fa-plus" aria-hidden="true"></i> Tambah Alat
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($alats->hasPages())
            <nav class="mt-8 flex justify-center" aria-label="Navigasi halaman data alat">
                {{ $alats->withQueryString()->links('vendor.pagination.alat-nama') }}
            </nav>
        @endif
    </div>

    {{-- Delete confirmation modal --}}
    <div x-data="{ show: false, id: '', name: '' }" x-cloak
        @open-delete-modal.window="show = true; id = $event.detail.id; name = $event.detail.name; $nextTick(() => $refs.cancelButton.focus())"
        @keydown.escape.window="show = false" x-show="show" x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-950/60 p-4" role="dialog"
        aria-modal="true" aria-labelledby="delete-modal-title">
        <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl sm:p-6 dark:bg-gray-800"
            @click.outside="show = false" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-2 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100">
            <div
                class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300">
                <i class="fas fa-exclamation-triangle text-xl" aria-hidden="true"></i>
            </div>
            <h2 id="delete-modal-title" class="mt-4 text-xl font-extrabold text-gray-900 dark:text-white">Hapus data alat?
            </h2>
            <p class="mt-2 text-sm leading-relaxed text-gray-600 dark:text-gray-300">Anda akan menghapus data berikut.
                Periksa kembali sebelum melanjutkan.</p>
            <p class="mt-3 break-words rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm font-bold text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                x-text="name"></p>
            <div class="mt-6 grid grid-cols-1 gap-2 sm:grid-cols-2">
                <button x-ref="cancelButton" type="button" @click="show = false"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                    Batal
                </button>
                <form :action="'{{ route('alats.destroy', '__ID__') }}'.replace('__ID__', encodeURIComponent(id))"
                    method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200">
                        <i class="fas fa-trash-alt" aria-hidden="true"></i>
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Photo preview modal --}}
    <div x-data="{ show: false, url: '', name: '' }" x-cloak
        @quick-view.window="url = $event.detail.url || ''; name = $event.detail.name || ''; show = !!url"
        @keydown.escape.window="show = false" x-show="show" x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center overflow-y-auto bg-gray-950/90 p-4" role="dialog"
        aria-modal="true" aria-label="Pratinjau foto alat">
        <div class="relative w-full max-w-4xl" @click.outside="show = false">
            <button type="button" @click="show = false"
                class="absolute -right-1 -top-14 inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-800 shadow-lg transition hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-white/50 sm:-right-3"
                aria-label="Tutup pratinjau foto">
                <i class="fas fa-times text-lg" aria-hidden="true"></i>
            </button>
            <div class="overflow-hidden rounded-2xl bg-gray-900 p-2 shadow-2xl sm:p-4">
                <img :src="url" :alt="name"
                    class="mx-auto max-h-[75vh] w-auto max-w-full rounded-xl object-contain" x-show="url">
            </div>
            <p class="mt-4 break-words text-center text-base font-bold text-white" x-text="name"></p>
            <p class="mt-1 text-center text-xs text-gray-300">Tekan Escape atau tombol tutup untuk kembali.</p>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection
