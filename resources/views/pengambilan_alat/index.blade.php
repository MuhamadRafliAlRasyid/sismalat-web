@extends('layouts.app')

@section('title', 'Pengambilan Alat')

@section('content')
    @php
        $me = auth()->user();
        $isAdmin = $me && $me->isAdminOrSuper();

        $hasFilters = request()->hasAny(['search', 'status', 'critical', 'date_from', 'date_to']);
        $baseQuery = request()->except(['status', 'critical', 'page']);

        $statCards = [
            [
                'key' => 'aktif',
                'label' => 'Sedang Dipinjam',
                'description' => 'Transaksi yang masih berjalan',
                'icon' => 'fa-clock',
                'iconClass' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300',
                'valueClass' => 'text-amber-700 dark:text-amber-300',
                'query' => ['status' => 'dipinjam'],
            ],
            [
                'key' => 'terlambat',
                'label' => 'Terlambat',
                'description' => 'Melewati tanggal pengembalian',
                'icon' => 'fa-triangle-exclamation',
                'iconClass' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-300',
                'valueClass' => 'text-rose-700 dark:text-rose-300',
                'query' => ['status' => 'terlambat'],
            ],
            [
                'key' => 'warning',
                'label' => 'Mendekati Jatuh Tempo',
                'description' => 'Perlu segera ditindaklanjuti',
                'icon' => 'fa-bell',
                'iconClass' => 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-300',
                'valueClass' => 'text-orange-700 dark:text-orange-300',
                'query' => ['status' => 'warning'],
            ],
            [
                'key' => 'critical',
                'label' => 'Kritis',
                'description' => 'Transaksi dengan kondisi prioritas',
                'icon' => 'fa-fire',
                'iconClass' => 'bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-300',
                'valueClass' => 'text-purple-700 dark:text-purple-300',
                'query' => ['critical' => 1],
            ],
        ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-7">
        {{-- HEADER --}}
        <section
            class="relative overflow-hidden rounded-3xl border border-amber-100 dark:border-gray-700 bg-gradient-to-br from-amber-50 via-white to-orange-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900 shadow-sm">
            <div
                class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-amber-200/30 dark:bg-amber-500/10 blur-3xl">
            </div>
            <div
                class="pointer-events-none absolute -bottom-20 -left-16 h-52 w-52 rounded-full bg-orange-200/30 dark:bg-orange-500/10 blur-3xl">
            </div>

            <div class="relative flex flex-col gap-5 p-5 sm:p-7 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="mb-3 flex items-center gap-3">
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shadow-amber-200/70 dark:shadow-none">
                            <i class="fas fa-hand-holding text-lg"></i>
                        </span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[.16em] text-amber-700 dark:text-amber-400">
                                Manajemen inventaris</p>
                            <h1 class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                Pengambilan Alat</h1>
                        </div>
                    </div>
                    <p class="max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-gray-300 sm:text-base">
                        Kelola transaksi peminjaman alat, pantau pengembalian, dan periksa persetujuan admin dalam satu
                        halaman.
                        Satu batch dapat berisi beberapa alat sekaligus.
                    </p>
                    <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5"><i class="fas fa-layer-group text-amber-500"></i>
                            Peminjaman per batch</span>
                        <span class="inline-flex items-center gap-1.5"><i class="fas fa-qrcode text-amber-500"></i> Bukti
                            dengan verifikasi QR</span>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
                    <a href="{{ route('pengambilan_alat.create') }}"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-5 py-3 text-sm font-semibold text-white shadow-md shadow-amber-200/70 transition hover:-translate-y-0.5 hover:from-amber-600 hover:to-orange-600 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 dark:shadow-none">
                        <i class="fas fa-plus"></i>
                        <span>Ambil Alat Baru</span>
                    </a>
                    @if ($isAdmin)
                        <div class="grid grid-cols-2 gap-2 sm:flex">
                            {{-- Export Excel --}}
                            <button type="submit" form="filter-pinjam-form" formmethod="GET"
                                formaction="{{ route('pengambilan_alat.export.excel') }}"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-green-200 bg-green-50 px-3.5 py-2 text-sm font-semibold text-green-800 transition hover:bg-green-100 focus:outline-none focus:ring-4 focus:ring-green-100 dark:border-green-900 dark:bg-green-950/50 dark:text-green-300 dark:hover:bg-green-900/60">
                                <i class="fas fa-file-excel" aria-hidden="true"></i> Export Excel
                            </button>

                            {{-- Export PDF --}}
                            <a href="{{ route('pengambilan_alat.export.pdf', request()->query()) }}"
                                class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-sm font-semibold text-red-800 transition hover:bg-red-100 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300">
                                <i class="fas fa-file-pdf" aria-hidden="true"></i> Export PDF
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- STATISTIK --}}
        <section aria-label="Ringkasan pengambilan alat">
            <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Ringkasan transaksi</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pilih salah satu ringkasan untuk memfilter daftar.
                    </p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($statCards as $card)
                    <a href="{{ route('pengambilan_alat.index', array_merge($baseQuery, $card['query'])) }}"
                        class="group rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-amber-400 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-amber-700">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                                <p class="mt-1 text-3xl font-extrabold tabular-nums {{ $card['valueClass'] }}">
                                    {{ $stats[$card['key']] ?? 0 }}</p>
                            </div>
                            <span
                                class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl {{ $card['iconClass'] }} transition group-hover:scale-105">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </span>
                        </div>
                        <div class="mt-3 flex items-center justify-between gap-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $card['description'] }}</p>
                            <i
                                class="fas fa-arrow-right text-xs text-gray-300 transition group-hover:translate-x-1 group-hover:text-amber-500"></i>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- NOTIFIKASI --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 5000)" role="status"
                class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
                <i class="fas fa-circle-check mt-0.5 text-lg text-emerald-600 dark:text-emerald-400"></i>
                <p class="flex-1 text-sm font-medium">{{ session('success') }}</p>
                <button type="button" @click="show = false" aria-label="Tutup notifikasi"
                    class="rounded-lg p-1 text-emerald-600 hover:bg-emerald-100 dark:hover:bg-emerald-900/50"><i
                        class="fas fa-times"></i></button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition role="alert"
                class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3.5 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                <i class="fas fa-circle-exclamation mt-0.5 text-lg text-red-600 dark:text-red-400"></i>
                <p class="flex-1 text-sm font-medium">{{ session('error') }}</p>
                <button type="button" @click="show = false" aria-label="Tutup notifikasi"
                    class="rounded-lg p-1 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/50"><i
                        class="fas fa-times"></i></button>
            </div>
        @endif

        {{-- FILTER --}}
        <section
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div
                class="flex flex-col gap-1 border-b border-gray-100 px-4 py-4 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <div>
                    <h2 class="font-bold text-gray-900 dark:text-white"><i
                            class="fas fa-sliders-h mr-2 text-amber-500"></i>Filter dan pencarian</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Atur kata kunci, status, dan tanggal untuk
                        menemukan transaksi.</p>
                </div>
                @if ($hasFilters)
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="inline-flex items-center gap-1.5 self-start text-xs font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 dark:hover:text-amber-300 sm:self-auto">
                        <i class="fas fa-rotate-left"></i> Hapus semua filter
                    </a>
                @endif
            </div>

            <form id="filter-pinjam-form" method="GET" action="{{ route('pengambilan_alat.index') }}" ...>
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                    <div class="lg:col-span-5">
                        <label for="search"
                            class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">Kata kunci</label>
                        <div class="relative">
                            <i
                                class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input id="search" type="search" name="search" value="{{ request('search') }}"
                                placeholder="Nama peminjam, alat, bagian, keperluan..."
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-10 pr-10 text-sm text-gray-800 placeholder:text-gray-400 focus:border-amber-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:bg-gray-900 dark:focus:ring-amber-900/30">
                            @if (request()->filled('search'))
                                <a href="{{ route('pengambilan_alat.index', request()->except(['search', 'page'])) }}"
                                    aria-label="Hapus kata kunci"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"><i
                                        class="fas fa-times-circle"></i></a>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-3">
                        <label for="status"
                            class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">Status
                            peminjaman</label>
                        <select id="status" name="status"
                            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-3 text-sm text-gray-800 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:focus:ring-amber-900/30">
                            <option value="">Semua status</option>
                            <option value="dipinjam" @selected(request('status') === 'dipinjam')>Dipinjam / Sebagian</option>
                            <option value="terlambat" @selected(request('status') === 'terlambat')>Terlambat</option>
                            <option value="warning" @selected(request('status') === 'warning')>Mendekati jatuh tempo</option>
                            <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Selesai dikembalikan</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2 lg:col-span-4">
                        <label
                            class="flex min-h-[46px] flex-1 cursor-pointer items-center gap-2.5 rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-3 transition hover:border-orange-300 dark:border-gray-700 dark:bg-gray-900">
                            <input type="checkbox" name="critical" value="1" @checked(request()->boolean('critical'))
                                class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200"><i
                                    class="fas fa-fire mr-1 text-orange-500"></i>Prioritas kritis</span>
                        </label>
                        <button type="submit"
                            class="inline-flex min-h-[46px] items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 text-sm font-semibold text-white transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2">
                            <i class="fas fa-filter"></i><span>Filter</span>
                        </button>
                    </div>
                </div>

                <details class="group mt-4 rounded-xl border border-gray-200 dark:border-gray-700"
                    @if (request()->filled('date_from') || request()->filled('date_to')) open @endif>
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 text-sm font-semibold text-gray-700 outline-none transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-700/40">
                        <span class="inline-flex items-center gap-2"><i class="fas fa-calendar-alt text-amber-500"></i>
                            Rentang tanggal pengambilan <span
                                class="text-xs font-normal text-gray-400">(opsional)</span></span>
                        <i class="fas fa-chevron-down text-xs text-gray-400 transition group-open:rotate-180"></i>
                    </summary>
                    <div class="grid grid-cols-1 gap-3 border-t border-gray-100 p-4 dark:border-gray-700 sm:grid-cols-2">
                        <div>
                            <label for="date_from"
                                class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">Dari
                                tanggal</label>
                            <input id="date_from" type="date" name="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        </div>
                        <div>
                            <label for="date_to"
                                class="mb-1.5 block text-xs font-semibold text-gray-600 dark:text-gray-300">Sampai
                                tanggal</label>
                            <input id="date_to" type="date" name="date_to" value="{{ request('date_to') }}"
                                min="{{ request('date_from') }}"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        </div>
                        <p class="sm:col-span-2 text-xs text-gray-500 dark:text-gray-400">Isi kedua tanggal bila ingin
                            membatasi hasil pada periode tertentu, lalu tekan tombol Filter.</p>
                    </div>
                </details>
            </form>

            @if ($hasFilters)
                <div
                    class="flex flex-wrap items-center gap-2 border-t border-gray-100 bg-gray-50/70 px-4 py-3 dark:border-gray-700 dark:bg-gray-900/30 sm:px-5">
                    <span class="mr-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Filter aktif:</span>
                    @if (request()->filled('search'))
                        <span
                            class="rounded-full bg-white px-3 py-1 text-xs text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">Cari:
                            {{ request('search') }}</span>
                    @endif
                    @if (request()->filled('status'))
                        <span
                            class="rounded-full bg-white px-3 py-1 text-xs text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">Status:
                            {{ request('status') }}</span>
                    @endif
                    @if (request()->boolean('critical'))
                        <span
                            class="rounded-full bg-orange-50 px-3 py-1 text-xs text-orange-700 ring-1 ring-orange-200 dark:bg-orange-900/20 dark:text-orange-300 dark:ring-orange-800">Prioritas
                            kritis</span>
                    @endif
                    @if (request()->filled('date_from') || request()->filled('date_to'))
                        <span
                            class="rounded-full bg-white px-3 py-1 text-xs text-gray-700 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700">Tanggal:
                            {{ request('date_from') ?: 'awal' }} – {{ request('date_to') ?: 'sekarang' }}</span>
                    @endif
                </div>
            @endif
        </section>

        {{-- DAFTAR TRANSAKSI --}}
        <section>
            <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Daftar pengambilan</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Buka detail untuk melihat alat, persetujuan,
                        dan bukti transaksi.</p>
                </div>
                @if (method_exists($data, 'total'))
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        {{ number_format($data->total(), 0, ',', '.') }} transaksi</p>
                @endif
            </div>

            <div class="space-y-3">
                @forelse ($data as $d)
                    @php
                        $items = $d->items ?? collect();
                        $totalItems = $items->count();
                        $returned = $items->where('status', 'dikembalikan')->count();
                        $progress = $totalItems > 0 ? (int) round(($returned / $totalItems) * 100) : 0;

                        $isAktif = $d->isAktif();
                        $isSelesai = $d->status === 'dikembalikan';
                        $isSebagian = $d->status === 'sebagian_dikembalikan';
                        $shouldWarn1Day = $isAktif && $d->should_warn_1day;
                        $shouldWarn15 = $isAktif && $d->should_warn_15;
                        $statusPinjam = $d->status_pinjam;
                        $isApproved = !empty($d->approved_at);

                        $statusBadge = match (true) {
                            $isSelesai => [
                                'label' => 'Selesai',
                                'class' =>
                                    'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800',
                                'dot' => 'bg-emerald-500',
                            ],
                            $isSebagian => [
                                'label' => 'Sebagian dikembalikan',
                                'class' =>
                                    'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800',
                                'dot' => 'bg-blue-500',
                            ],
                            $statusPinjam === 'terlambat' => [
                                'label' => 'Terlambat',
                                'class' =>
                                    'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:ring-rose-800',
                                'dot' => 'bg-rose-500',
                            ],
                            $shouldWarn1Day => [
                                'label' => 'Jatuh tempo besok',
                                'class' =>
                                    'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:ring-rose-800',
                                'dot' => 'bg-rose-500',
                            ],
                            $shouldWarn15 => [
                                'label' => 'Mendekati jatuh tempo',
                                'class' =>
                                    'bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-900/30 dark:text-orange-300 dark:ring-orange-800',
                                'dot' => 'bg-orange-500',
                            ],
                            default => [
                                'label' => 'Sedang dipinjam',
                                'class' =>
                                    'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800',
                                'dot' => 'bg-amber-500',
                            ],
                        };

                        $barColor = match (true) {
                            $statusPinjam === 'terlambat' || $shouldWarn1Day => 'bg-rose-500',
                            $shouldWarn15 => 'bg-orange-500',
                            $isSebagian => 'bg-blue-500',
                            $isSelesai => 'bg-emerald-500',
                            default => 'bg-amber-500',
                        };
                        $approverName = $d->approved_by_name ?? null;
                    @endphp

                    <article
                        class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-amber-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-amber-800">
                        <div class="flex">
                            <div class="w-1.5 flex-shrink-0 {{ $barColor }}"></div>
                            <div class="min-w-0 flex-1 p-4 sm:p-5">
                                <div class="flex flex-col gap-4 xl:flex-row xl:items-start">
                                    {{-- Ikon batch --}}
                                    <div class="flex flex-shrink-0 items-center gap-3 xl:block">
                                        <div
                                            class="relative grid h-14 w-14 grid-cols-2 gap-0.5 overflow-hidden rounded-xl border border-gray-200 bg-gray-100 p-0.5 dark:border-gray-700 dark:bg-gray-900">
                                            @forelse ($items->take(4) as $it)
                                                <div
                                                    class="flex items-center justify-center overflow-hidden rounded-md bg-white dark:bg-gray-800">
                                                    @if ($it->alat?->foto)
                                                        <img src="{{ $it->alat->foto_thumb }}" alt=""
                                                            loading="lazy" class="h-full w-full object-cover"
                                                            onerror="this.style.display='none'">
                                                    @else
                                                        <i class="fas fa-toolbox text-[10px] text-gray-400"></i>
                                                    @endif
                                                </div>
                                            @empty
                                                <div class="col-span-2 flex items-center justify-center rounded-md"><i
                                                        class="fas fa-box-open text-gray-400"></i></div>
                                            @endforelse
                                            @if ($totalItems === 1 && $items->first()?->alat?->foto)
                                                <img src="{{ $items->first()->alat->foto_thumb }}" alt=""
                                                    loading="lazy"
                                                    class="absolute inset-0 h-full w-full rounded-xl object-cover"
                                                    onerror="this.style.display='none'">
                                            @endif
                                        </div>
                                        <span
                                            class="inline-flex min-w-7 items-center justify-center rounded-full bg-amber-100 px-2 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 xl:mx-auto xl:mt-2">{{ $totalItems }}
                                            alat</span>
                                    </div>

                                    {{-- Informasi batch --}}
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                            <div class="min-w-0">
                                                <h3
                                                    class="truncate text-base font-bold text-gray-900 dark:text-white sm:text-lg">
                                                    {{ $d->nama_peminjam ?? ($d->user->name ?? 'Peminjam tidak diketahui') }}
                                                </h3>
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    Batch #{{ strtoupper(substr($d->hashid, 0, 8)) }}
                                                    @if ($d->bagian)
                                                        <span class="mx-1">·</span>{{ $d->bagian->nama }}
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusBadge['class'] }}">
                                                    <span
                                                        class="h-1.5 w-1.5 rounded-full {{ $statusBadge['dot'] }}"></span>{{ $statusBadge['label'] }}
                                                </span>
                                                @if ($isApproved)
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800"
                                                        title="Persetujuan tercatat">
                                                        <i class="fas fa-check-circle"></i> Disetujui
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center gap-1.5 rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-semibold text-yellow-700 ring-1 ring-yellow-200 dark:bg-yellow-900/20 dark:text-yellow-300 dark:ring-yellow-800"
                                                        title="Belum ada persetujuan admin">
                                                        <i class="fas fa-hourglass-half"></i> Belum disetujui
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($isApproved && $approverName)
                                            <p class="mt-2 text-xs text-emerald-700 dark:text-emerald-300"><i
                                                    class="fas fa-user-check mr-1"></i> Disetujui oleh {{ $approverName }}
                                                @if ($d->approved_at)
                                                    · {{ $d->approved_at->format('d M Y, H:i') }}
                                                @endif
                                            </p>
                                        @endif

                                        {{-- Ringkasan item --}}
                                        <div
                                            class="mt-3 rounded-xl border border-gray-100 bg-gray-50/80 p-3 dark:border-gray-700 dark:bg-gray-900/40">
                                            <div class="mb-2 flex items-center justify-between gap-2">
                                                <p
                                                    class="text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                                    Ringkasan alat</p>
                                                @if ($totalItems > 0)
                                                    <span class="text-[11px] text-gray-400">{{ $returned }} dari
                                                        {{ $totalItems }} jenis dikembalikan</span>
                                                @endif
                                            </div>
                                            <ul class="space-y-2">
                                                @foreach ($items->take(3) as $it)
                                                    <li class="flex min-w-0 items-center gap-2 text-xs">
                                                        <span
                                                            class="h-2 w-2 flex-shrink-0 rounded-full {{ $it->status === 'dikembalikan' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                                        <span
                                                            class="min-w-0 flex-1 truncate {{ $it->status === 'dikembalikan' ? 'text-gray-400 line-through' : 'font-medium text-gray-700 dark:text-gray-200' }}">{{ $it->alat->nama_alat ?? 'Alat tidak tersedia' }}
                                                            @if ($it->alat?->merk)
                                                                <span
                                                                    class="font-normal text-gray-400">({{ $it->alat->merk }})</span>
                                                            @endif
                                                        </span>
                                                        <span
                                                            class="flex-shrink-0 font-semibold text-gray-600 dark:text-gray-300">×{{ $it->jumlah }}
                                                            <span
                                                                class="font-normal text-gray-400">{{ $it->satuan }}</span></span>
                                                    </li>
                                                @endforeach
                                                @if ($totalItems > 3)
                                                    <li
                                                        class="pl-4 text-xs font-semibold text-amber-700 dark:text-amber-400">
                                                        +{{ $totalItems - 3 }} alat lainnya. <a
                                                            href="{{ route('pengambilan_alat.show', $d->hashid) }}"
                                                            class="underline underline-offset-2 hover:text-amber-900 dark:hover:text-amber-200">Lihat
                                                            semua</a></li>
                                                @elseif ($totalItems === 0)
                                                    <li class="text-xs text-gray-400">Belum ada rincian alat.</li>
                                                @endif
                                            </ul>
                                        </div>

                                        {{-- Progres pengembalian --}}
                                        @if ($isSebagian || ($isAktif && $returned > 0))
                                            <div class="mt-3">
                                                <div
                                                    class="mb-1.5 flex justify-between text-xs text-gray-500 dark:text-gray-400">
                                                    <span>Progres pengembalian</span><span
                                                        class="font-semibold">{{ $progress }}%</span>
                                                </div>
                                                <div class="h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                                    <div class="h-full rounded-full bg-blue-500 transition-all"
                                                        style="width: {{ $progress }}%"></div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Metadata --}}
                                        <div class="mt-3 grid grid-cols-2 gap-x-4 gap-y-3 sm:grid-cols-4">
                                            <div class="min-w-0">
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Waktu pengambilan
                                                </p>
                                                <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-200">
                                                    {{ $d->waktu_pengambilan?->format('d M Y, H:i') ?? '-' }}</p>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Lama pinjam</p>
                                                <p class="mt-0.5 text-xs font-semibold text-gray-700 dark:text-gray-200">
                                                    {{ $d->lama_pinjam ?? '-' }} hari</p>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Jatuh tempo</p>
                                                <p
                                                    class="mt-0.5 text-xs font-semibold {{ $statusPinjam === 'terlambat' ? 'text-rose-600 dark:text-rose-400' : 'text-gray-700 dark:text-gray-200' }}">
                                                    {{ $d->tanggal_jatuh_tempo?->format('d M Y') ?? '-' }}</p>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Keperluan</p>
                                                <p class="mt-0.5 truncate text-xs font-semibold text-gray-700 dark:text-gray-200"
                                                    title="{{ $d->keperluan }}">{{ $d->keperluan ?? '-' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Aksi --}}
                                    <div
                                        class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-3 xl:w-36 xl:flex-shrink-0 xl:flex-col xl:items-stretch xl:border-l xl:border-t-0 xl:pl-4 xl:pt-0 dark:border-gray-700">
                                        <a href="{{ route('pengambilan_alat.show', $d->hashid) }}"
                                            class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-amber-500 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2 xl:w-full"
                                            title="Lihat detail transaksi">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>

                                        @if ($isAdmin)
                                            @if (!$isSelesai)
                                                <a href="{{ route('pengembalian_alat.create', $d->hashid) }}"
                                                    class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-300 xl:w-full dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300 dark:hover:bg-emerald-900/40"
                                                    title="Proses pengembalian alat">
                                                    <i class="fas fa-rotate-left"></i> Kembalikan
                                                </a>
                                            @endif

                                            <a href="{{ route('pengambilan_alat.edit', $d->hashid) }}"
                                                class="inline-flex min-h-10 flex-1 items-center justify-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-300 xl:w-full dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/40"
                                                title="Edit batch peminjaman">
                                                <i class="fas fa-pen"></i> Edit
                                            </a>

                                            <form method="POST"
                                                action="{{ route('pengambilan_alat.destroy', $d->hashid) }}"
                                                class="flex-1 xl:w-full"
                                                onsubmit="return confirm('Hapus batch peminjaman ini?\n\nStok untuk alat yang masih dipinjam akan dikembalikan. Alat yang sudah dikembalikan tidak terpengaruh.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-300 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/40"
                                                    title="Hapus batch peminjaman">
                                                    <i class="fas fa-trash-alt"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div
                        class="rounded-2xl border border-dashed border-gray-300 bg-white px-5 py-14 text-center dark:border-gray-700 dark:bg-gray-800">
                        <div
                            class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 dark:bg-amber-900/30 dark:text-amber-300">
                            <i class="fas fa-box-open text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $hasFilters ? 'Transaksi tidak ditemukan' : 'Belum ada pengambilan alat' }}</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm text-gray-500 dark:text-gray-400">
                            {{ $hasFilters ? 'Tidak ada transaksi yang cocok dengan filter saat ini. Coba ubah kata kunci atau hapus filter.' : 'Transaksi pengambilan alat yang dibuat akan muncul di halaman ini.' }}
                        </p>
                        <div class="mt-5 flex flex-col justify-center gap-2 sm:flex-row">
                            @if ($hasFilters)
                                <a href="{{ route('pengambilan_alat.index') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700"><i
                                        class="fas fa-rotate-left"></i> Hapus filter</a>
                            @else
                                <a href="{{ route('pengambilan_alat.create') }}"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600"><i
                                        class="fas fa-plus"></i> Buat pengambilan pertama</a>
                            @endif
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- PAGINATION --}}
        @if (method_exists($data, 'hasPages') && $data->hasPages())
            <div
                class="flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-4 dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan <span
                        class="font-semibold text-gray-700 dark:text-gray-200">{{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}</span>
                    dari <span
                        class="font-semibold text-gray-700 dark:text-gray-200">{{ number_format($data->total(), 0, ',', '.') }}</span>
                    transaksi
                </p>
                <div>{{ $data->onEachSide(1)->withQueryString()->links('vendor.pagination.tailwind') }}</div>
            </div>
        @endif
    </div>
@endsection
