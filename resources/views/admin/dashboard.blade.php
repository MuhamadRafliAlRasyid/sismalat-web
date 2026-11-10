@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 px-4 py-6 md:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl" x-data="adminDashboard()" x-init="init()">
            {{-- Header --}}
            <section class="relative mb-6 overflow-hidden rounded-3xl bg-slate-900 px-6 py-7 text-white shadow-xl md:px-8">
                <div class="pointer-events-none absolute -right-12 -top-24 h-72 w-72 rounded-full bg-amber-400/20 blur-3xl">
                </div>
                <div
                    class="pointer-events-none absolute bottom-[-110px] right-1/3 h-64 w-64 rounded-full bg-cyan-400/10 blur-3xl">
                </div>
                <div class="relative flex flex-col justify-between gap-6 md:flex-row md:items-center">
                    <div>
                        <div
                            class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1 text-xs font-semibold tracking-wide text-amber-200">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_0_4px_rgba(52,211,153,.15)]"></span>
                            OPERATIONAL OVERVIEW
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Dashboard Admin</h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300">
                            Selamat datang, <span class="font-semibold text-white">{{ Auth::user()->name }}</span>.
                            Pantau inventaris, alur peminjaman, dan aktivitas operasional dari satu tempat.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                            <p class="text-[10px] font-semibold uppercase tracking-[.18em] text-slate-400">Hari ini</p>
                            <p class="mt-1 text-sm font-semibold" x-text="todayLabel"></p>
                        </div>
                        <a href="{{ route('pengambilan_alat.create') }}"
                            class="inline-flex items-center gap-2 rounded-2xl bg-amber-400 px-4 py-3 text-sm font-bold text-slate-950 shadow-lg shadow-amber-950/20 transition hover:-translate-y-0.5 hover:bg-amber-300">
                            <i class="fas fa-plus"></i> Peminjaman Baru
                        </a>
                    </div>
                </div>
            </section>

            {{-- Quick actions --}}
            <section class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                <a href="{{ route('alats.create') }}"
                    class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300"><i
                            class="fas fa-box-open"></i></span>
                    <span class="min-w-0"><span class="block text-sm font-bold text-slate-800 dark:text-white">Tambah
                            Alat</span><span class="mt-0.5 block text-xs text-slate-500">Input inventaris baru</span></span>
                    <i
                        class="fas fa-arrow-up-right-from-square ml-auto text-xs text-slate-300 transition group-hover:text-amber-500"></i>
                </a>
                <a href="{{ route('pengambilan_alat.index') }}"
                    class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-400/10 dark:text-orange-300"><i
                            class="fas fa-clipboard-list"></i></span>
                    <span class="min-w-0"><span class="block text-sm font-bold text-slate-800 dark:text-white">Data
                            Peminjaman</span><span class="mt-0.5 block text-xs text-slate-500">Kelola semua
                            batch</span></span>
                    <i
                        class="fas fa-arrow-up-right-from-square ml-auto text-xs text-slate-300 transition group-hover:text-orange-500"></i>
                </a>
                <a href="{{ route('kalibrasis.index') }}"
                    class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 dark:bg-cyan-400/10 dark:text-cyan-300"><i
                            class="fas fa-ruler-combined"></i></span>
                    <span class="min-w-0"><span
                            class="block text-sm font-bold text-slate-800 dark:text-white">Kalibrasi</span><span
                            class="mt-0.5 block text-xs text-slate-500">Jadwal dan status alat</span></span>
                    <i
                        class="fas fa-arrow-up-right-from-square ml-auto text-xs text-slate-300 transition group-hover:text-cyan-500"></i>
                </a>
                <a href="{{ route('alats.export.pdf') }}"
                    class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300"><i
                            class="fas fa-file-pdf"></i></span>
                    <span class="min-w-0"><span
                            class="block text-sm font-bold text-slate-800 dark:text-white">Laporan</span><span
                            class="mt-0.5 block text-xs text-slate-500">Ekspor data inventaris</span></span>
                    <i
                        class="fas fa-arrow-up-right-from-square ml-auto text-xs text-slate-300 transition group-hover:text-violet-500"></i>
                </a>
            </section>

            {{-- KPI --}}
            <section class="mb-7 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Alat</span>
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300"><i
                                class="fas fa-boxes-stacked"></i></span>
                    </div>
                    <div class="mt-4 flex items-end justify-between">
                        <p class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white"
                            x-text="formatNumber({{ (int) $totalAlat }})">{{ number_format($totalAlat, 0, ',', '.') }}</p>
                        <span class="text-xs font-medium text-slate-500">{{ number_format($stokTersedia, 0, ',', '.') }}
                            unit stok</span>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Batch Aktif</span>
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-400/10 dark:text-orange-300"><i
                                class="fas fa-arrow-right-arrow-left"></i></span>
                    </div>
                    <div class="mt-4 flex items-end justify-between">
                        <p class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white"
                            x-text="formatNumber({{ (int) $batchAktif }})">{{ number_format($batchAktif, 0, ',', '.') }}
                        </p>
                        <span class="text-xs font-medium text-slate-500">{{ $jenisDipinjam }} jenis alat</span>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Terlambat</span>
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-400/10 dark:text-rose-300"><i
                                class="fas fa-clock"></i></span>
                    </div>
                    <div class="mt-4 flex items-end justify-between">
                        <p class="text-3xl font-extrabold tracking-tight text-rose-600 dark:text-rose-400"
                            x-text="formatNumber({{ (int) $batchTerlambat }})">
                            {{ number_format($batchTerlambat, 0, ',', '.') }}</p>
                        <span class="text-xs font-medium text-slate-500">{{ $batchWarning }} segera jatuh tempo</span>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Rasio
                            Pengembalian</span>
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"><i
                                class="fas fa-chart-pie"></i></span>
                    </div>
                    <div class="mt-4 flex items-end justify-between">
                        <p class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {{ $rasioPengembalian }}<span class="text-lg">%</span></p>
                        <span
                            class="text-xs font-medium text-slate-500">{{ number_format($unitDikembalikan, 0, ',', '.') }}
                            unit kembali</span>
                    </div>
                    <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-emerald-500 transition-all"
                            style="width: {{ $rasioPengembalian }}%"></div>
                    </div>
                </div>
            </section>

            {{-- Kanban --}}
            <section
                class="mb-7 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-6">
                <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-300"><i
                                    class="fas fa-table-columns"></i></span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Kanban Peminjaman</h2>
                        </div>
                        <p class="mt-2 text-sm text-slate-500">Pantau posisi setiap batch dan prioritaskan pengembalian
                            yang mendesak.</p>
                    </div>
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="search" x-model.debounce.200ms="kanbanSearch"
                                placeholder="Cari peminjam / alat / bagian..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-amber-400 focus:ring-2 focus:ring-amber-100 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:ring-amber-900/30 sm:w-72">
                        </div>
                        <select x-model="kanbanFilter"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-amber-400 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                            <option value="all">Semua status</option>
                            <option value="attention">Perlu perhatian</option>
                            <option value="active">Aktif</option>
                            <option value="done">Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-4">
                    @php
                        $lanes = [
                            'terlambat' => [
                                'title' => 'Terlambat',
                                'subtitle' => 'Lewat jatuh tempo',
                                'color' => 'rose',
                                'icon' => 'fa-triangle-exclamation',
                                'filter' => 'attention',
                            ],
                            'warning' => [
                                'title' => 'Segera Jatuh Tempo',
                                'subtitle' => 'Dalam 3 hari',
                                'color' => 'amber',
                                'icon' => 'fa-hourglass-half',
                                'filter' => 'attention',
                            ],
                            'aktif' => [
                                'title' => 'Sedang Dipinjam',
                                'subtitle' => 'Masih dalam periode',
                                'color' => 'blue',
                                'icon' => 'fa-arrow-right-arrow-left',
                                'filter' => 'active',
                            ],
                            'selesai' => [
                                'title' => 'Selesai',
                                'subtitle' => 'Batch dikembalikan',
                                'color' => 'emerald',
                                'icon' => 'fa-circle-check',
                                'filter' => 'done',
                            ],
                        ];
                    @endphp

                    @foreach ($lanes as $key => $lane)
                        @php
                            $laneClasses = [
                                'terlambat' =>
                                    'border-rose-200 bg-rose-50/70 dark:border-rose-900/50 dark:bg-rose-950/20',
                                'warning' =>
                                    'border-amber-200 bg-amber-50/70 dark:border-amber-900/50 dark:bg-amber-950/20',
                                'aktif' => 'border-blue-200 bg-blue-50/70 dark:border-blue-900/50 dark:bg-blue-950/20',
                                'selesai' =>
                                    'border-emerald-200 bg-emerald-50/70 dark:border-emerald-900/50 dark:bg-emerald-950/20',
                            ];
                            $countClasses = [
                                'terlambat' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/50 dark:text-rose-300',
                                'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
                                'aktif' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                                'selesai' =>
                                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300',
                            ];
                        @endphp
                        <div class="min-w-0 rounded-2xl border p-3 {{ $laneClasses[$key] }}"
                            x-show="kanbanFilter === 'all' || kanbanFilter === '{{ $lane['filter'] }}'">
                            <div class="mb-3 flex items-center justify-between gap-2 px-1">
                                <div class="min-w-0">
                                    <h3 class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">
                                        {{ $lane['title'] }}</h3>
                                    <p class="mt-0.5 text-[11px] text-slate-500">{{ $lane['subtitle'] }}</p>
                                </div>
                                <span
                                    class="rounded-lg px-2.5 py-1 text-xs font-extrabold {{ $countClasses[$key] }}">{{ $kanbanCounts[$key] ?? 0 }}</span>
                            </div>

                            <div class="max-h-[440px] space-y-2.5 overflow-y-auto pr-1">
                                @forelse ($kanbanColumns[$key] as $batch)
                                    @php
                                        $peminjam =
                                            $batch->nama_peminjam ?? ($batch->user->name ?? 'Peminjam tidak diketahui');
                                        $bagianNama = $batch->bagian->nama ?? '-';
                                        $namaAlat = $batch->items
                                            ->pluck('alat.nama_alat')
                                            ->filter()
                                            ->unique()
                                            ->take(2)
                                            ->implode(', ');
                                        $jumlahItem = $batch->items->sum('jumlah');
                                        $sisa = $batch->sisa_hari;
                                        if ($key === 'terlambat') {
                                            $deadlineText = 'Terlambat ' . abs((int) $sisa) . ' hari';
                                        } elseif ($key === 'warning' && (int) $sisa === 0) {
                                            $deadlineText = 'Jatuh tempo hari ini';
                                        } elseif ($key === 'warning') {
                                            $deadlineText = 'Sisa ' . (int) $sisa . ' hari';
                                        } elseif ($key === 'selesai') {
                                            $deadlineText = 'Sudah dikembalikan';
                                        } else {
                                            $deadlineText =
                                                $sisa === null
                                                    ? 'Durasi belum ditentukan'
                                                    : 'Sisa ' . (int) $sisa . ' hari';
                                        }
                                        $searchText = strtolower($peminjam . ' ' . $bagianNama . ' ' . $namaAlat);
                                    @endphp
                                    <a href="{{ route('pengambilan_alat.show', $batch->hashid) }}"
                                        data-search="{{ $searchText }}" data-lane="{{ $key }}"
                                        x-show="isKanbanVisible($el.dataset.search, $el.dataset.lane)"
                                        class="block rounded-xl border border-white/80 bg-white p-3.5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-700 dark:bg-slate-900">
                                        <div class="mb-2 flex items-start justify-between gap-2">
                                            <p class="min-w-0 truncate text-sm font-bold text-slate-800 dark:text-white">
                                                {{ $peminjam }}</p>
                                            <span
                                                class="shrink-0 rounded-md px-2 py-1 text-[10px] font-bold {{ $key === 'terlambat' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300' : ($key === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : ($key === 'selesai' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300')) }}">{{ strtoupper($key === 'warning' ? 'WARNING' : $key) }}</span>
                                        </div>
                                        <p class="text-xs text-slate-500"><i
                                                class="fas fa-building mr-1.5 opacity-60"></i>{{ $bagianNama }}</p>
                                        <p class="mt-2 line-clamp-2 text-xs leading-5 text-slate-600 dark:text-slate-300">
                                            {{ $namaAlat ?: 'Detail alat tidak tersedia' }}</p>
                                        <div
                                            class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5 text-[11px] dark:border-slate-800">
                                            <span class="font-medium text-slate-500"><i
                                                    class="fas fa-cubes mr-1"></i>{{ $jumlahItem }} unit</span>
                                            <span
                                                class="font-bold {{ $key === 'terlambat' ? 'text-rose-600 dark:text-rose-400' : ($key === 'warning' ? 'text-amber-600 dark:text-amber-400' : ($key === 'selesai' ? 'text-emerald-600 dark:text-emerald-400' : 'text-blue-600 dark:text-blue-400')) }}">{{ $deadlineText }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div
                                        class="rounded-xl border border-dashed border-slate-300/80 bg-white/50 px-3 py-8 text-center dark:border-slate-700 dark:bg-slate-900/40">
                                        <i class="fas fa-inbox text-xl text-slate-300 dark:text-slate-600"></i>
                                        <p class="mt-2 text-xs font-medium text-slate-500">Tidak ada batch</p>
                                    </div>
                                @endforelse
                                <p x-show="kanbanSearch.length > 0" x-cloak
                                    class="hidden px-2 py-3 text-center text-xs text-slate-400">Gunakan kata kunci lain
                                    bila kartu tidak terlihat.</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div
                    class="mt-4 flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <p class="text-xs text-slate-500">Maksimal 8 kartu per kolom ditampilkan pada dashboard.</p>
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="text-xs font-bold text-amber-600 transition hover:text-amber-700 dark:text-amber-400">Buka
                        seluruh peminjaman <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
            </section>

            {{-- SPC and status charts --}}
            <section class="mb-7 grid grid-cols-1 gap-5 xl:grid-cols-3">
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:col-span-2 md:p-6">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                        <div>
                            <div class="flex items-center gap-2">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-300"><i
                                        class="fas fa-chart-line"></i></span>
                                <h2 class="text-lg font-bold text-slate-900 dark:text-white">SPC · Volume Peminjaman</h2>
                            </div>
                            <p class="mt-2 text-xs text-slate-500">C-chart jumlah batch peminjaman per hari. Garis batas
                                dihitung dari periode yang dipilih.</p>
                        </div>
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
                            <button type="button" @click="setSpcRange(7)"
                                :class="spcRange === 7 ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' :
                                    'text-slate-500'"
                                class="rounded-lg px-3 py-1.5 text-xs font-bold transition">7 hari</button>
                            <button type="button" @click="setSpcRange(14)"
                                :class="spcRange === 14 ?
                                    'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' :
                                    'text-slate-500'"
                                class="rounded-lg px-3 py-1.5 text-xs font-bold transition">14 hari</button>
                            <button type="button" @click="setSpcRange(30)"
                                :class="spcRange === 30 ?
                                    'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' :
                                    'text-slate-500'"
                                class="rounded-lg px-3 py-1.5 text-xs font-bold transition">30 hari</button>
                        </div>
                    </div>
                    <div class="mt-5 grid grid-cols-3 gap-2">
                        <div class="rounded-xl bg-slate-50 p-3 dark:bg-slate-800/70">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-500">Rata-rata / hari
                            </p>
                            <p class="mt-1 text-lg font-extrabold text-slate-900 dark:text-white"
                                x-text="spcStats.mean.toFixed(2)">{{ number_format($spcSummary['mean'], 2, ',', '.') }}
                            </p>
                        </div>
                        <div class="rounded-xl bg-rose-50 p-3 dark:bg-rose-950/30">
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-rose-600 dark:text-rose-300">
                                Batas atas (UCL)</p>
                            <p class="mt-1 text-lg font-extrabold text-rose-700 dark:text-rose-300"
                                x-text="spcStats.ucl.toFixed(2)">{{ number_format($spcSummary['ucl'], 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/30">
                            <p
                                class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">
                                Di luar batas</p>
                            <p class="mt-1 text-lg font-extrabold text-emerald-700 dark:text-emerald-300"
                                x-text="spcStats.outliers">{{ $spcSummary['out_of_control'] }}</p>
                        </div>
                    </div>
                    <div class="relative mt-4 h-72"><canvas id="spcChart"></canvas></div>
                    <p class="mt-2 text-[11px] leading-5 text-slate-400">Interpretasi: titik di atas UCL atau di bawah LCL
                        adalah sinyal variasi khusus yang perlu diperiksa. Batas ini adalah batas kendali statistik, bukan
                        target atau batas spesifikasi.</p>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-6">
                    <div class="flex items-center gap-2">
                        <span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-50 text-orange-600 dark:bg-orange-400/10 dark:text-orange-300"><i
                                class="fas fa-chart-doughnut"></i></span>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Status Batch</h2>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">Komposisi status peminjaman saat ini.</p>
                    <div class="relative mx-auto mt-5 h-56 max-w-xs"><canvas id="statusChart"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center"><span
                                class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ number_format($totalBatch, 0, ',', '.') }}</span><span
                                class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Total
                                batch</span></div>
                    </div>
                    <div class="mt-4 space-y-3">
                        @php
                            $statusItems = [
                                ['label' => 'Aktif', 'value' => $batchAktif, 'dot' => 'bg-blue-500'],
                                ['label' => 'Terlambat', 'value' => $batchTerlambat, 'dot' => 'bg-rose-500'],
                                ['label' => 'Selesai', 'value' => $batchSelesai, 'dot' => 'bg-emerald-500'],
                            ];
                            $statusChartTotal = $batchAktif + $batchTerlambat + $batchSelesai;
                        @endphp
                        @foreach ($statusItems as $item)
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300"><span
                                        class="h-2.5 w-2.5 rounded-full {{ $item['dot'] }}"></span>{{ $item['label'] }}</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $item['value'] }} <span
                                        class="ml-1 text-xs font-medium text-slate-400">{{ $statusChartTotal > 0 ? round(($item['value'] / $statusChartTotal) * 100) : 0 }}%</span></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Recent activity and alerts --}}
            <section class="mb-7 grid grid-cols-1 gap-5 lg:grid-cols-5">
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-3 md:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Aktivitas Terbaru</h2>
                            <p class="mt-1 text-xs text-slate-500">Perubahan dan transaksi peminjaman terakhir.</p>
                        </div>
                        <a href="{{ route('pengambilan_alat.index') }}"
                            class="shrink-0 text-xs font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400">Lihat
                            semua <i class="fas fa-arrow-right ml-1"></i></a>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($recentBatches as $batch)
                            @php
                                $isSelesai = $batch->status === 'dikembalikan';
                                $peminjam = $batch->nama_peminjam ?? ($batch->user->name ?? '-');
                                $bagianNama = $batch->bagian->nama ?? '-';
                                $alatNames = $batch->items
                                    ->pluck('alat.nama_alat')
                                    ->filter()
                                    ->unique()
                                    ->take(2)
                                    ->implode(', ');
                            @endphp
                            <a href="{{ route('pengambilan_alat.show', $batch->hashid) }}"
                                class="flex items-center gap-3 py-3 transition hover:px-2">
                                <span
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $isSelesai ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300' : 'bg-blue-50 text-blue-600 dark:bg-blue-400/10 dark:text-blue-300' }}"><i
                                        class="fas {{ $isSelesai ? 'fa-check' : 'fa-arrow-right-arrow-left' }}"></i></span>
                                <span class="min-w-0 flex-1"><span
                                        class="block truncate text-sm font-bold text-slate-800 dark:text-white">{{ $peminjam }}
                                        <span class="font-normal text-slate-400">· {{ $bagianNama }}</span></span><span
                                        class="mt-1 block truncate text-xs text-slate-500">{{ $alatNames ?: 'Detail alat tidak tersedia' }}</span></span>
                                <span class="shrink-0 text-right"><span
                                        class="block text-[11px] text-slate-400">{{ $batch->waktu_pengambilan ? $batch->waktu_pengambilan->diffForHumans() : '-' }}</span><span
                                        class="mt-1 inline-block rounded-md px-2 py-0.5 text-[10px] font-bold {{ $isSelesai ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' }}">{{ $isSelesai ? 'SELESAI' : 'AKTIF' }}</span></span>
                            </a>
                        @empty
                            <div class="py-10 text-center"><i class="fas fa-inbox text-2xl text-slate-300"></i>
                                <p class="mt-2 text-sm font-semibold text-slate-600 dark:text-slate-300">Belum ada
                                    aktivitas</p>
                                <p class="mt-1 text-xs text-slate-400">Data peminjaman akan tampil di sini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div
                    class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2 md:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Pusat Perhatian</h2>
                            <p class="mt-1 text-xs text-slate-500">Hal yang perlu ditindaklanjuti.</p>
                        </div><span
                            class="rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-extrabold text-rose-600 dark:bg-rose-400/10 dark:text-rose-300">{{ $perhatian->count() + $alatPerluKalibrasi }}</span>
                    </div>
                    <div class="space-y-3">
                        @if ($alatPerluKalibrasi > 0)
                            <a href="{{ route('alats.index', ['status' => 'expired']) }}"
                                class="flex items-start gap-3 rounded-xl border border-rose-100 bg-rose-50/70 p-3 transition hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/20 dark:hover:bg-rose-950/40">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-300"><i
                                        class="fas fa-screwdriver-wrench"></i></span>
                                <span class="min-w-0 flex-1"><span
                                        class="block text-sm font-bold text-rose-800 dark:text-rose-200">Kalibrasi perlu
                                        tindakan</span><span
                                        class="mt-1 block text-xs text-rose-700 dark:text-rose-300">{{ $alatPerluKalibrasi }}
                                        alat berstatus warning / expired.</span></span>
                                <i class="fas fa-chevron-right mt-1 text-xs text-rose-400"></i>
                            </a>
                        @endif
                        @if ($batchTerlambat > 0)
                            <a href="{{ route('pengambilan_alat.index', ['status' => 'terlambat']) }}"
                                class="flex items-start gap-3 rounded-xl border border-orange-100 bg-orange-50/70 p-3 transition hover:bg-orange-100 dark:border-orange-900/50 dark:bg-orange-950/20 dark:hover:bg-orange-950/40">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-900/50 dark:text-orange-300"><i
                                        class="fas fa-clock"></i></span>
                                <span class="min-w-0 flex-1"><span
                                        class="block text-sm font-bold text-orange-800 dark:text-orange-200">Pengembalian
                                        terlambat</span><span
                                        class="mt-1 block text-xs text-orange-700 dark:text-orange-300">{{ $batchTerlambat }}
                                        batch melewati jatuh tempo.</span></span>
                                <i class="fas fa-chevron-right mt-1 text-xs text-orange-400"></i>
                            </a>
                        @endif
                        @forelse ($perhatian->take(4) as $item)
                            @php
                                $namaPeminjam = $item->nama_peminjam ?? ($item->user->name ?? 'Peminjam');
                                $sisaHari = (int) $item->sisa_hari;
                            @endphp
                            <a href="{{ route('pengambilan_alat.show', $item->hashid) }}"
                                class="flex items-start gap-3 rounded-xl border border-amber-100 bg-amber-50/70 p-3 transition hover:bg-amber-100 dark:border-amber-900/50 dark:bg-amber-950/20 dark:hover:bg-amber-950/40">
                                <span
                                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300"><i
                                        class="fas fa-bell"></i></span>
                                <span class="min-w-0 flex-1"><span
                                        class="block truncate text-sm font-bold text-amber-900 dark:text-amber-200">{{ $namaPeminjam }}</span><span
                                        class="mt-1 block text-xs text-amber-700 dark:text-amber-300">{{ $sisaHari < 0 ? 'Terlambat ' . abs($sisaHari) . ' hari' : ($sisaHari === 0 ? 'Jatuh tempo hari ini' : 'Jatuh tempo dalam ' . $sisaHari . ' hari') }}</span></span>
                                <i class="fas fa-chevron-right mt-1 text-xs text-amber-400"></i>
                            </a>
                        @empty
                            @if ($alatPerluKalibrasi === 0 && $batchTerlambat === 0)
                                <div class="py-8 text-center"><span
                                        class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-300"><i
                                            class="fas fa-check"></i></span>
                                    <p class="mt-3 text-sm font-bold text-slate-700 dark:text-slate-200">Semua terkendali
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">Tidak ada tindak lanjut mendesak.</p>
                                </div>
                            @endif
                        @endforelse
                        @if ($perhatian->count() > 4)
                            <a href="{{ route('pengambilan_alat.index', ['status' => 'warning']) }}"
                                class="block pt-1 text-center text-xs font-bold text-amber-600 hover:text-amber-700 dark:text-amber-400">Lihat
                                {{ $perhatian->count() - 4 }} perhatian lainnya <i
                                    class="fas fa-arrow-right ml-1"></i></a>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Top alat & summary --}}
            <section class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2 md:p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Alat Paling Sering Dipinjam</h2>
                            <p class="mt-1 text-xs text-slate-500">Akumulasi jumlah unit berdasarkan transaksi.</p>
                        </div><i class="fas fa-ranking-star text-amber-500"></i>
                    </div>
                    <div class="relative h-64"><canvas id="topAlatChart"></canvas></div>
                </div>
                <div
                    class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-6">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Ringkasan Operasional</h2>
                    <p class="mt-1 text-xs text-slate-500">Indikator tambahan inventaris dan peminjaman.</p>
                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between"><span class="text-sm text-slate-500">Total batch
                                tercatat</span><span
                                class="text-sm font-extrabold text-slate-900 dark:text-white">{{ number_format($totalBatch, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between"><span class="text-sm text-slate-500">Batch
                                warning</span><span
                                class="text-sm font-extrabold text-amber-600">{{ number_format($batchWarning, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between"><span class="text-sm text-slate-500">Unit masih
                                dipinjam</span><span
                                class="text-sm font-extrabold text-blue-600">{{ number_format($unitDipinjam, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between"><span class="text-sm text-slate-500">Jenis alat
                                dipinjam</span><span
                                class="text-sm font-extrabold text-violet-600">{{ number_format($jenisDipinjam, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between"><span class="text-sm text-slate-500">Unit
                                dikembalikan</span><span
                                class="text-sm font-extrabold text-emerald-600">{{ number_format($unitDikembalikan, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function adminDashboard() {
            return {
                todayLabel: new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(new Date()),
                kanbanSearch: '',
                kanbanFilter: 'all',
                spcRange: 30,
                spcChart: null,
                statusChart: null,
                topAlatChart: null,
                spcAllLabels: @json($spcLabels),
                spcAllValues: @json($spcValues),
                spcStats: {
                    mean: {{ $spcSummary['mean'] }},
                    ucl: {{ $spcSummary['ucl'] }},
                    lcl: {{ $spcSummary['lcl'] }},
                    outliers: {{ $spcSummary['out_of_control'] }}
                },

                init() {
                    this.initSpcChart();
                    this.initStatusChart();
                    this.initTopAlatChart();
                },

                formatNumber(value) {
                    return new Intl.NumberFormat('id-ID').format(value || 0);
                },

                isKanbanVisible(searchText, lane) {
                    const query = (this.kanbanSearch || '').trim().toLocaleLowerCase('id-ID');
                    const matchesText = !query || (searchText || '').toLocaleLowerCase('id-ID').includes(query);
                    let matchesFilter = true;
                    if (this.kanbanFilter === 'attention') matchesFilter = ['terlambat', 'warning'].includes(lane);
                    if (this.kanbanFilter === 'active') matchesFilter = lane === 'aktif';
                    if (this.kanbanFilter === 'done') matchesFilter = lane === 'selesai';
                    return matchesText && matchesFilter;
                },

                selectedSpcData() {
                    const length = Math.min(this.spcRange, this.spcAllValues.length);
                    return {
                        labels: this.spcAllLabels.slice(-length),
                        values: this.spcAllValues.slice(-length)
                    };
                },

                calculateSpc(values) {
                    const count = values.length;
                    const mean = count ? values.reduce((sum, value) => sum + value, 0) / count : 0;
                    // C-chart untuk jumlah kejadian pada unit observasi dengan ukuran sama (1 hari).
                    const distance = 3 * Math.sqrt(Math.max(mean, 0));
                    const ucl = mean + distance;
                    const lcl = Math.max(0, mean - distance);
                    const outliers = values.filter(value => value > ucl || value < lcl).length;
                    return {
                        mean,
                        ucl,
                        lcl,
                        outliers
                    };
                },

                setSpcRange(days) {
                    this.spcRange = days;
                    this.refreshSpcChart();
                },

                initSpcChart() {
                    const canvas = document.getElementById('spcChart');
                    if (!canvas || typeof Chart === 'undefined') return;
                    const isDark = document.documentElement.classList.contains('dark');
                    const data = this.selectedSpcData();
                    this.spcStats = this.calculateSpc(data.values);
                    const repeated = value => data.values.map(() => value);

                    this.spcChart = new Chart(canvas.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                    label: 'Batch / hari',
                                    data: data.values,
                                    borderColor: '#f59e0b',
                                    backgroundColor: 'rgba(245, 158, 11, .12)',
                                    fill: true,
                                    tension: .28,
                                    borderWidth: 2.5,
                                    pointRadius: 3,
                                    pointHoverRadius: 6,
                                    pointBackgroundColor: data.values.map(value => value > this.spcStats.ucl ||
                                        value < this.spcStats.lcl ? '#ef4444' : '#f59e0b'),
                                },
                                {
                                    label: 'CL (rata-rata)',
                                    data: repeated(this.spcStats.mean),
                                    borderColor: '#64748b',
                                    borderDash: [5, 5],
                                    borderWidth: 1.5,
                                    pointRadius: 0,
                                    fill: false
                                },
                                {
                                    label: 'UCL',
                                    data: repeated(this.spcStats.ucl),
                                    borderColor: '#ef4444',
                                    borderDash: [5, 4],
                                    borderWidth: 1.5,
                                    pointRadius: 0,
                                    fill: false
                                },
                                {
                                    label: 'LCL',
                                    data: repeated(this.spcStats.lcl),
                                    borderColor: '#10b981',
                                    borderDash: [5, 4],
                                    borderWidth: 1.5,
                                    pointRadius: 0,
                                    fill: false
                                },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index'
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 7,
                                        color: isDark ? '#cbd5e1' : '#64748b',
                                        font: {
                                            size: 10
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: isDark ? '#0f172a' : '#fff',
                                    titleColor: isDark ? '#fff' : '#0f172a',
                                    bodyColor: isDark ? '#cbd5e1' : '#334155',
                                    borderColor: isDark ? '#334155' : '#e2e8f0',
                                    borderWidth: 1,
                                    padding: 10,
                                    callbacks: {
                                        label: context =>
                                            `${context.dataset.label}: ${Number(context.parsed.y).toFixed(2)}`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: isDark ? '#94a3b8' : '#64748b',
                                        maxRotation: 0,
                                        autoSkip: true,
                                        maxTicksLimit: 10
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: isDark ? 'rgba(148,163,184,.12)' : 'rgba(148,163,184,.18)'
                                    },
                                    ticks: {
                                        precision: 0,
                                        color: isDark ? '#94a3b8' : '#64748b'
                                    }
                                }
                            }
                        }
                    });
                },

                refreshSpcChart() {
                    if (!this.spcChart) return;
                    const data = this.selectedSpcData();
                    this.spcStats = this.calculateSpc(data.values);
                    const repeated = value => data.values.map(() => value);
                    this.spcChart.data.labels = data.labels;
                    this.spcChart.data.datasets[0].data = data.values;
                    this.spcChart.data.datasets[0].pointBackgroundColor = data.values.map(value => value > this.spcStats
                        .ucl || value < this.spcStats.lcl ? '#ef4444' : '#f59e0b');
                    this.spcChart.data.datasets[1].data = repeated(this.spcStats.mean);
                    this.spcChart.data.datasets[2].data = repeated(this.spcStats.ucl);
                    this.spcChart.data.datasets[3].data = repeated(this.spcStats.lcl);
                    this.spcChart.update();
                },

                initStatusChart() {
                    const canvas = document.getElementById('statusChart');
                    if (!canvas || typeof Chart === 'undefined') return;
                    const isDark = document.documentElement.classList.contains('dark');
                    this.statusChart = new Chart(canvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Aktif', 'Terlambat', 'Selesai'],
                            datasets: [{
                                data: [{{ (int) $batchAktif }}, {{ (int) $batchTerlambat }},
                                    {{ (int) $batchSelesai }}
                                ],
                                backgroundColor: ['#3b82f6', '#f43f5e', '#10b981'],
                                borderColor: isDark ? '#0f172a' : '#fff',
                                borderWidth: 4,
                                hoverOffset: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '76%',
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: context => `${context.label}: ${context.parsed} batch`
                                    }
                                }
                            }
                        }
                    });
                },

                initTopAlatChart() {
                    const canvas = document.getElementById('topAlatChart');
                    if (!canvas || typeof Chart === 'undefined') return;
                    const isDark = document.documentElement.classList.contains('dark');
                    const labels = @json($topAlatLabels);
                    const values = @json($topAlatValues);
                    if (!labels.length) {
                        const context = canvas.getContext('2d');
                        context.font = '13px sans-serif';
                        context.fillStyle = isDark ? '#94a3b8' : '#64748b';
                        context.textAlign = 'center';
                        context.fillText('Belum ada data peminjaman', canvas.width / 2, 80);
                        return;
                    }
                    this.topAlatChart = new Chart(canvas.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Total unit dipinjam',
                                data: values,
                                backgroundColor: ['rgba(245,158,11,.9)', 'rgba(249,115,22,.82)',
                                    'rgba(59,130,246,.78)', 'rgba(16,185,129,.75)',
                                    'rgba(139,92,246,.72)'
                                ],
                                borderRadius: 8,
                                borderSkipped: false,
                                maxBarThickness: 28
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: context => ` ${context.parsed.x} unit`
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    grid: {
                                        color: isDark ? 'rgba(148,163,184,.12)' : 'rgba(148,163,184,.18)'
                                    },
                                    ticks: {
                                        precision: 0,
                                        color: isDark ? '#94a3b8' : '#64748b'
                                    }
                                },
                                y: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        color: isDark ? '#cbd5e1' : '#475569',
                                        font: {
                                            size: 11
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            };
        }
    </script>
@endpush
