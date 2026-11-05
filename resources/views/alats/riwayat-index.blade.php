@extends('layouts.app')

@section('title', 'Alat dengan Riwayat')

@section('content')
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8" x-data="{
        search: @js(request('search', '')),
        status: @js(request('status', '')),
        applyFilters() {
            const url = new URL(window.location.href);
            const searchValue = this.search.trim();
    
            searchValue ? url.searchParams.set('search', searchValue) : url.searchParams.delete('search');
            this.status ? url.searchParams.set('status', this.status) : url.searchParams.delete('status');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        },
        resetFilters() {
            window.location.href = window.location.pathname;
        }
    }">
        {{-- Header --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-2 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <i class="fas fa-toolbox text-amber-500" aria-hidden="true"></i>
                    <span>Sismalat</span>
                    <i class="fas fa-chevron-right text-[10px]" aria-hidden="true"></i>
                    <span class="font-medium text-gray-700 dark:text-gray-200">Riwayat alat</span>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                    Alat yang Memiliki Riwayat
                </h1>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    Telusuri alat yang pernah dipinjam, dikembalikan, atau menjalani proses kalibrasi.
                </p>
            </div>
            <div
                class="inline-flex w-fit items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-300">
                <i class="fas fa-history" aria-hidden="true"></i>
                <span>{{ $alats->total() }} alat tercatat</span>
            </div>
        </header>

        {{-- Search and filters --}}
        <section
            class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-5">
            <div class="mb-4">
                <h2 class="font-bold text-gray-900 dark:text-white">Cari dan filter riwayat</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cari berdasarkan nama, merk, tipe, atau nomor seri
                    alat.</p>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
                <label class="relative block">
                    <span class="sr-only">Cari nama alat</span>
                    <i class="fas fa-search pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"
                        aria-hidden="true"></i>
                    <input type="search" x-model="search" @keydown.enter.prevent="applyFilters()"
                        placeholder="Cari nama alat, merk, nomor seri..."
                        class="min-h-12 w-full rounded-xl border border-gray-300 bg-white py-3 pl-11 pr-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-amber-500 focus:ring-4 focus:ring-amber-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900/40">
                </label>

                <label>
                    <span class="sr-only">Filter status riwayat</span>
                    <select x-model="status" @change="applyFilters()"
                        class="min-h-12 w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900/40">
                        <option value="">Semua status</option>
                        <option value="dipinjam">Sedang dipinjam</option>
                        <option value="dikembalikan">Pernah dikembalikan</option>
                        <option value="dikalibrasi">Pernah dikalibrasi</option>
                    </select>
                </label>

                <div class="flex gap-2">
                    <button type="button" @click="applyFilters()"
                        class="inline-flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-amber-600 focus:outline-none focus:ring-4 focus:ring-amber-200 md:flex-none">
                        <i class="fas fa-filter" aria-hidden="true"></i>
                        Terapkan
                    </button>
                    <button type="button" @click="resetFilters()"
                        class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700"
                        title="Reset filter">
                        <i class="fas fa-undo" aria-hidden="true"></i>
                        <span class="hidden sm:inline">Reset</span>
                    </button>
                </div>
            </div>

            @if (request()->filled('search') || request()->filled('status'))
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                    <span>Filter aktif:</span>
                    @if (request()->filled('search'))
                        <span
                            class="rounded-full bg-gray-100 px-3 py-1 font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                            Pencarian: {{ request('search') }}
                        </span>
                    @endif
                    @if (request()->filled('status'))
                        <span
                            class="rounded-full bg-amber-100 px-3 py-1 font-medium text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                            Status: {{ ucfirst(request('status')) }}
                        </span>
                    @endif
                </div>
            @endif
        </section>

        {{-- Tool cards --}}
        @if ($alats->isEmpty())
            <section
                class="rounded-2xl border-2 border-dashed border-gray-300 bg-white px-6 py-16 text-center dark:border-gray-700 dark:bg-gray-800">
                <span
                    class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-300">
                    <i class="fas fa-box-open text-3xl" aria-hidden="true"></i>
                </span>
                <h2 class="mt-5 text-lg font-bold text-gray-900 dark:text-white">Data riwayat tidak ditemukan</h2>
                <p class="mx-auto mt-2 max-w-md text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                    Belum ada alat yang sesuai dengan pencarian atau filter yang dipilih. Coba ubah kata kunci atau reset
                    filter.
                </p>
                @if (request()->filled('search') || request()->filled('status'))
                    <button type="button" @click="resetFilters()"
                        class="mt-5 inline-flex min-h-10 items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-amber-600">
                        <i class="fas fa-undo" aria-hidden="true"></i> Reset pencarian
                    </button>
                @endif
            </section>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($alats as $alat)
                    @php
                        // Relasi ini berasal dari pengambilan_alat_items, bukan pengambilan_alats.
                        $itemRiwayat = $alat->pengambilanItems ?? collect();
                        $hasDipinjam = $itemRiwayat->contains('status', 'dipinjam');
                        $hasDikembalikan = $itemRiwayat->contains('status', 'dikembalikan');
                        $hasKalibrasi = ($alat->kalibrasis ?? collect())->isNotEmpty();
                        $jumlahTransaksi = $itemRiwayat->count();
                    @endphp

                    <a href="{{ route('alats.riwayat', $alat->hashid) }}"
                        class="group flex h-full flex-col rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-amber-700 dark:focus:ring-amber-900/40"
                        style="animation-delay: {{ min($loop->index * 60, 480) }}ms">
                        <div class="flex items-start gap-4">
                            <div
                                class="relative flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900">
                                <i class="fas fa-toolbox text-2xl" aria-hidden="true"></i>
                                @if ($alat->foto)
                                    <img src="{{ $alat->foto_url }}" alt="Foto {{ $alat->nama_alat }}" loading="lazy"
                                        class="absolute inset-0 h-full w-full object-cover"
                                        onerror="this.style.display='none'">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h2
                                    class="break-words text-base font-extrabold leading-snug text-gray-900 transition group-hover:text-amber-700 dark:text-white dark:group-hover:text-amber-300">
                                    {{ $alat->nama_alat }}
                                </h2>
                                <p class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">
                                    {{ $alat->merk ?: 'Merk belum diisi' }}</p>
                                @if ($alat->tipe)
                                    <p class="mt-1 truncate text-xs text-gray-400 dark:text-gray-500">{{ $alat->tipe }}
                                    </p>
                                @endif
                            </div>
                            <i class="fas fa-arrow-up-right-from-square mt-1 text-xs text-gray-300 transition group-hover:text-amber-500"
                                aria-hidden="true"></i>
                        </div>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @if ($hasDipinjam)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/50 dark:text-amber-200">
                                    <i class="fas fa-clock" aria-hidden="true"></i> Sedang dipinjam
                                </span>
                            @endif
                            @if ($hasDikembalikan)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                                    <i class="fas fa-check-circle" aria-hidden="true"></i> Pernah dikembalikan
                                </span>
                            @endif
                            @if ($hasKalibrasi)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">
                                    <i class="fas fa-microscope" aria-hidden="true"></i> Terkalibrasi
                                </span>
                            @endif
                        </div>

                        <div class="mt-auto grid grid-cols-2 gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat peminjaman</p>
                                <p class="mt-1 text-lg font-extrabold tabular-nums text-gray-900 dark:text-white">
                                    {{ $jumlahTransaksi }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat kalibrasi</p>
                                <p class="mt-1 text-lg font-extrabold tabular-nums text-gray-900 dark:text-white">
                                    {{ $alat->kalibrasis->count() }}</p>
                            </div>
                        </div>
                        <div
                            class="mt-4 flex items-center justify-between text-sm font-bold text-amber-700 dark:text-amber-300">
                            <span>Lihat seluruh riwayat</span>
                            <i class="fas fa-arrow-right transition group-hover:translate-x-1" aria-hidden="true"></i>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="pt-2">
                {{ $alats->withQueryString()->links() }}
            </div>
        @endif
    </div>
@endsection
