@extends('layouts.app')

@section('title', 'Pengambilan Alat')

@section('content')
    @php
        $me = auth()->user();
        $isAdmin = $me->isAdminOrSuper();
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{-- ═══════════ HEADER ═══════════ --}}
        <div
            class="relative overflow-hidden bg-gradient-to-br from-amber-50 via-white to-orange-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900 rounded-3xl p-6 sm:p-8 mb-8 border border-amber-100/50 dark:border-gray-700 shadow-sm">
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-amber-200/20 dark:bg-amber-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl">
            </div>
            <div
                class="absolute bottom-0 left-0 w-48 h-48 bg-orange-200/20 dark:bg-orange-500/10 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl">
            </div>

            <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span
                            class="inline-flex items-center justify-center w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200 dark:shadow-none">
                            <i class="fas fa-hand-holding"></i>
                        </span>
                        <h2
                            class="text-3xl font-bold bg-gradient-to-r from-amber-700 to-orange-700 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent">
                            Pengambilan Alat
                        </h2>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">
                        Setiap peminjaman dapat berisi <strong>beberapa alat sekaligus</strong> dalam satu batch.
                    </p>
                </div>
                <div class="flex gap-3 flex-wrap">
                    <a href="{{ route('pengambilan_alat.create') }}"
                        class="group relative inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white px-5 py-3 rounded-2xl font-medium transition-all duration-300 shadow-lg shadow-amber-200 dark:shadow-none hover:shadow-xl hover:-translate-y-0.5">
                        <i class="fas fa-plus group-hover:rotate-90 transition-transform duration-300"></i>
                        <span>Ambil Alat Baru</span>
                    </a>
                    @if ($isAdmin)
                        <a href="{{ route('pengambilan_alat.export.pdf') }}"
                            class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800 hover:border-red-300 px-5 py-3 rounded-2xl font-medium transition-all duration-300 shadow-sm hover:shadow-md">
                            <i class="fas fa-file-pdf"></i>
                            <span>Export PDF</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══════════ STATISTIK (klik = filter) ═══════════ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            @foreach ([['key' => 'aktif', 'label' => 'Sedang Dipinjam', 'icon' => 'fa-clock', 'color' => 'red', 'url' => ['status' => 'dipinjam']], ['key' => 'terlambat', 'label' => 'Terlambat', 'icon' => 'fa-exclamation-circle', 'color' => 'rose', 'url' => ['status' => 'terlambat']], ['key' => 'warning', 'label' => 'Mendekati Jatuh Tempo', 'icon' => 'fa-bell', 'color' => 'orange', 'url' => ['status' => 'warning']], ['key' => 'critical', 'label' => 'Kritis', 'icon' => 'fa-fire', 'color' => 'purple', 'url' => ['critical' => 1]]] as $card)
                <a href="{{ route('pengambilan_alat.index', array_merge(request()->only(['search']), $card['url'])) }}"
                    class="group bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 hover:border-{{ $card['color'] }}-300 dark:hover:border-{{ $card['color'] }}-700 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-2xl font-extrabold text-{{ $card['color'] }}-600 dark:text-{{ $card['color'] }}-400">
                            {{ $stats[$card['key']] ?? 0 }}
                        </span>
                        <span
                            class="w-9 h-9 rounded-xl bg-{{ $card['color'] }}-50 dark:bg-{{ $card['color'] }}-900/30 text-{{ $card['color'] }}-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                            <i class="fas {{ $card['icon'] }} text-sm"></i>
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-medium">{{ $card['label'] }}</p>
                </a>
            @endforeach
        </div>

        {{-- ═══════════ ALERTS ═══════════ --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" x-init="setTimeout(() => show = false, 4000)"
                class="mb-6 bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <span
                    class="flex-shrink-0 w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-500"></i>
                </span>
                <span class="flex-1 font-medium">{{ session('success') }}</span>
                <button @click="show = false"
                    class="flex-shrink-0 w-8 h-8 rounded-xl hover:bg-emerald-100 dark:hover:bg-emerald-900/40 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-emerald-400"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-5 py-4 rounded-2xl flex items-center gap-3">
                <i class="fas fa-exclamation-circle text-red-500"></i>
                <span class="flex-1 font-medium">{{ session('error') }}</span>
                <button @click="show = false"><i class="fas fa-times text-red-400"></i></button>
            </div>
        @endif

        {{-- ═══════════ FILTER BAR (form GET agar benar-benar submit) ═══════════ --}}
        <form method="GET" action="{{ route('pengambilan_alat.index') }}" class="mb-8" x-data>
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl p-4 shadow-sm">
                <div class="flex flex-col lg:flex-row gap-3">

                    {{-- Search --}}
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari alat, peminjam, bagian, keperluan..."
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all">
                    </div>

                    {{-- Status --}}
                    <select name="status" onchange="this.form.submit()"
                        class="py-3 px-4 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="dipinjam" @selected(request('status') === 'dipinjam')>Dipinjam / Sebagian</option>
                        <option value="terlambat" @selected(request('status') === 'terlambat')>Terlambat</option>
                        <option value="warning" @selected(request('status') === 'warning')>Mendekati Jatuh Tempo</option>
                        <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Selesai</option>
                    </select>

                    {{-- Critical --}}
                    <label
                        class="inline-flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:border-amber-300 transition-colors select-none">
                        <input type="checkbox" name="critical" value="1" @checked(request()->boolean('critical'))
                            onchange="this.form.submit()"
                            class="w-4 h-4 text-amber-500 border-gray-300 rounded focus:ring-amber-400">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200 whitespace-nowrap">
                            <i class="fas fa-fire text-orange-500 mr-1"></i>Kritis
                        </span>
                    </label>

                    {{-- Tombol --}}
                    <div class="flex gap-2">
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-5 py-3 rounded-xl font-medium transition-colors">
                            <i class="fas fa-filter text-sm"></i>
                            <span class="hidden sm:inline">Filter</span>
                        </button>
                        @if (request()->hasAny(['search', 'status', 'critical', 'date_from', 'date_to']))
                            <a href="{{ route('pengambilan_alat.index') }}"
                                class="inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 px-4 py-3 rounded-xl font-medium transition-colors"
                                title="Reset filter">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Rentang tanggal (collapsible) --}}
                <details class="mt-3 group" @open(request()->filled('date_from') || request()->filled('date_to'))>
                    <summary
                        class="text-xs font-semibold text-amber-600 dark:text-amber-400 cursor-pointer list-none flex items-center gap-1.5 select-none">
                        <i class="fas fa-calendar-alt transition-transform group-open:rotate-180"></i>
                        Filter rentang tanggal pengambilan
                    </summary>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Dari</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}"
                                onchange="this.form.submit()"
                                class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 dark:text-gray-400 block mb-1">Sampai</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}"
                                onchange="this.form.submit()"
                                class="w-full py-2.5 px-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                    </div>
                </details>
            </div>
        </form>

        {{-- ═══════════ DAFTAR BATCH PEMINJAMAN ═══════════ --}}
        <div class="space-y-4">
            @forelse ($data as $d)
                @php
                    $items = $d->items;
                    $totalItems = $items->count();
                    $returned = $items->where('status', 'dikembalikan')->count();
                    $progress = $totalItems > 0 ? round(($returned / $totalItems) * 100) : 0;

                    $isAktif = $d->isAktif();
                    $isSelesai = $d->status === 'dikembalikan';
                    $isSebagian = $d->status === 'sebagian_dikembalikan';

                    $shouldWarn1Day = $isAktif && $d->should_warn_1day;
                    $shouldWarn15 = $isAktif && $d->should_warn_15;
                    $statusPinjam = $d->status_pinjam;

                    // Warna indikator kiri per status batch
                    $barColor = match (true) {
                        $shouldWarn1Day => 'from-red-400 to-red-600',
                        $shouldWarn15 => 'from-orange-400 to-orange-600',
                        $isSebagian => 'from-blue-400 to-indigo-500',
                        $isSelesai => 'from-emerald-400 to-emerald-600',
                        default => 'from-amber-400 to-amber-600',
                    };

                    // Badge status batch
                    $statusBadge = match (true) {
                        $isSelesai => [
                            'label' => 'Selesai',
                            'class' =>
                                'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-800',
                            'dot' => 'bg-emerald-500',
                        ],
                        $isSebagian => [
                            'label' => 'Sebagian Dikembalikan',
                            'class' =>
                                'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 ring-blue-200 dark:ring-blue-800',
                            'dot' => 'bg-blue-500',
                        ],
                        $shouldWarn1Day => [
                            'label' => 'Jatuh Tempo Besok!',
                            'class' =>
                                'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-red-200 dark:ring-red-800',
                            'dot' => 'bg-red-500 animate-pulse',
                        ],
                        $shouldWarn15 => [
                            'label' => 'Peringatan Pengembalian',
                            'class' =>
                                'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 ring-orange-200 dark:ring-orange-800',
                            'dot' => 'bg-orange-500 animate-pulse',
                        ],
                        $statusPinjam === 'terlambat' => [
                            'label' => 'Terlambat',
                            'class' =>
                                'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-red-200 dark:ring-red-800',
                            'dot' => 'bg-red-500 animate-pulse',
                        ],
                        default => [
                            'label' => 'Dipinjam',
                            'class' =>
                                'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 ring-amber-200 dark:ring-amber-800',
                            'dot' => 'bg-amber-500',
                        ],
                    };
                @endphp

                <div
                    class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-xl hover:border-amber-200 dark:hover:border-amber-800 overflow-hidden">

                    {{-- Indikator status kiri --}}
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b {{ $barColor }}"></div>

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start gap-5">

                            {{-- ═══ THUMBNAIL 2×2 MINI-GRID (visual batch) ═══ --}}
                            <div class="flex-shrink-0">
                                <div class="relative">
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 grid grid-cols-2 gap-0.5 p-0.5 overflow-hidden group-hover:border-amber-200 transition-colors">
                                        @foreach ($items->take(4) as $it)
                                            <div
                                                class="rounded-md bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                                                @if ($it->alat?->foto)
                                                    <img src="{{ $it->alat->foto_thumb }}" alt=""
                                                        class="w-full h-full object-cover" loading="lazy"
                                                        onerror="this.remove()">
                                                @else
                                                    <i class="fas fa-tools text-[10px] text-gray-400"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if ($totalItems === 1 && $items->first()?->alat?->foto)
                                            {{-- Single item: tampilkan penuh --}}
                                            <img src="{{ $items->first()->alat->foto_thumb }}" alt=""
                                                class="absolute inset-0 w-16 h-16 rounded-2xl object-cover"
                                                onerror="this.remove()">
                                        @endif
                                    </div>

                                    {{-- Badge jumlah alat --}}
                                    <div
                                        class="absolute -bottom-1.5 -right-1.5 min-w-[22px] h-[22px] px-1.5 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white text-[10px] font-extrabold flex items-center justify-center border-2 border-white dark:border-gray-800 shadow-sm">
                                        {{ $totalItems }}
                                    </div>
                                </div>
                            </div>

                            {{-- ═══ INFO UTAMA ═══ --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3 mb-1">
                                    <div class="min-w-0">
                                        <h3
                                            class="font-bold text-gray-800 dark:text-gray-100 text-lg truncate group-hover:text-amber-700 dark:group-hover:text-amber-400 transition-colors">
                                            {{ $d->nama_peminjam ?? ($d->user->name ?? 'Peminjam Tidak Diketahui') }}
                                        </h3>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                            Batch #{{ strtoupper(substr($d->hashid, 0, 6)) }}
                                            @if ($d->bagian)
                                                • {{ $d->bagian->nama }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                {{-- Baris badge --}}
                                <div class="flex items-center gap-2 mt-2 flex-wrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ring-1 {{ $statusBadge['class'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $statusBadge['dot'] }}"></span>
                                        {{ $statusBadge['label'] }}
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-boxes text-[10px]"></i>
                                        {{ $totalItems }} alat
                                    </span>

                                    @if ($isSebagian || ($isAktif && $returned > 0))
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                            <i class="fas fa-undo-alt text-[10px]"></i>
                                            {{ $returned }}/{{ $totalItems }} kembali
                                        </span>
                                    @endif

                                    @if ($isAktif && $d->lama_pinjam)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusPinjam === 'terlambat' ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 animate-pulse' : 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300' }}">
                                            <i class="fas fa-calendar-check text-[10px]"></i>
                                            {{ $d->sisa_hari_label }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Progress pengembalian (jika sebagian) --}}
                                @if ($isSebagian)
                                    <div class="mt-3">
                                        <div
                                            class="flex items-center justify-between text-[10px] text-gray-500 dark:text-gray-400 mb-1">
                                            <span>Progres pengembalian</span>
                                            <span class="font-semibold">{{ $progress }}%</span>
                                        </div>
                                        <div
                                            class="w-full h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-gradient-to-r from-blue-400 to-indigo-500 rounded-full transition-all duration-500"
                                                style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                @endif

                                {{-- ═══ DAFTAR ALAT DALAM BATCH ═══ --}}
                                <div
                                    class="mt-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700 p-3">
                                    <ul class="space-y-1.5">
                                        @foreach ($items->take(3) as $it)
                                            <li class="flex items-center gap-2 text-xs">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full flex-shrink-0 {{ $it->status === 'dikembalikan' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                                <span
                                                    class="truncate {{ $it->status === 'dikembalikan' ? 'text-gray-400 line-through' : 'text-gray-700 dark:text-gray-200 font-medium' }}">
                                                    {{ $it->alat->nama_alat ?? 'Alat terhapus' }}
                                                    @if ($it->alat?->merk)
                                                        <span
                                                            class="text-gray-400 font-normal">({{ $it->alat->merk }})</span>
                                                    @endif
                                                </span>
                                                <span
                                                    class="ml-auto flex-shrink-0 font-semibold text-gray-600 dark:text-gray-300">
                                                    ×{{ $it->jumlah }} <span
                                                        class="font-normal text-gray-400">{{ $it->satuan }}</span>
                                                </span>
                                            </li>
                                        @endforeach
                                        @if ($totalItems > 3)
                                            <li class="text-xs font-semibold text-amber-600 dark:text-amber-400 pl-3.5">
                                                +{{ $totalItems - 3 }} alat lainnya — lihat detail
                                            </li>
                                        @endif
                                    </ul>
                                </div>

                                {{-- ═══ DETAIL META ═══ --}}
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-3">
                                    <div class="text-xs">
                                        <p class="text-gray-400 dark:text-gray-500">Waktu Ambil</p>
                                        <p class="font-medium text-gray-700 dark:text-gray-200">
                                            {{ $d->waktu_pengambilan?->format('d M Y, H:i') ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="text-xs">
                                        <p class="text-gray-400 dark:text-gray-500">Lama Pinjam</p>
                                        <p class="font-medium text-gray-700 dark:text-gray-200">
                                            {{ $d->lama_pinjam ?? '-' }} hari</p>
                                    </div>
                                    <div class="text-xs">
                                        <p class="text-gray-400 dark:text-gray-500">Jatuh Tempo</p>
                                        <p class="font-medium text-gray-700 dark:text-gray-200">
                                            {{ $d->tanggal_jatuh_tempo?->format('d M Y') ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="text-xs min-w-0">
                                        <p class="text-gray-400 dark:text-gray-500">Keperluan</p>
                                        <p class="font-medium text-gray-700 dark:text-gray-200 truncate"
                                            title="{{ $d->keperluan }}">
                                            {{ $d->keperluan ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- ═══ ACTION BUTTONS ═══ --}}
                            <div class="flex sm:flex-col gap-2 justify-end flex-shrink-0">
                                <a href="{{ route('pengambilan_alat.show', $d->hashid) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 hover:bg-amber-100 hover:text-amber-700 transition-all duration-200 hover:scale-105"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if ($isAdmin)
                                    @if (!$isSelesai)
                                        <a href="{{ route('pengembalian_alat.create', $d->hashid) }}"
                                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 hover:text-emerald-700 transition-all duration-200 hover:scale-105"
                                            title="Kembalikan Alat">
                                            <i class="fas fa-undo-alt"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('pengambilan_alat.edit', $d->hashid) }}"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-100 hover:text-blue-700 transition-all duration-200 hover:scale-105"
                                        title="Edit Batch">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form method="POST" action="{{ route('pengambilan_alat.destroy', $d->hashid) }}"
                                        onsubmit="return confirm('Hapus batch peminjaman ini?\n\nStok untuk alat yang MASIH DIPINJAM akan dikembalikan. Alat yang sudah dikembalikan tidak terpengaruh.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-100 hover:text-red-700 transition-all duration-200 hover:scale-105"
                                            title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
                    <div
                        class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-50 dark:bg-amber-900/30 mb-6">
                        <i class="fas fa-hand-holding text-4xl text-amber-300"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-200 mb-2">
                        {{ request()->hasAny(['search', 'status', 'critical']) ? 'Tidak Ada Hasil' : 'Belum Ada Data' }}
                    </h3>
                    <p class="text-gray-400 dark:text-gray-500 mb-6">
                        @if (request()->hasAny(['search', 'status', 'critical']))
                            Coba ubah kata kunci atau reset filter.
                        @else
                            Belum ada riwayat peminjaman alat.
                        @endif
                    </p>
                    @if (request()->hasAny(['search', 'status', 'critical']))
                        <a href="{{ route('pengambilan_alat.index') }}"
                            class="inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-6 py-3 rounded-2xl font-medium transition-all">
                            <i class="fas fa-times"></i> Reset Filter
                        </a>
                    @else
                        <a href="{{ route('pengambilan_alat.create') }}"
                            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-2xl font-medium transition-all shadow-lg shadow-amber-200 dark:shadow-none">
                            <i class="fas fa-plus"></i> Ambil Alat Baru
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- ═══════════ PAGINATION ═══════════ --}}
        @if ($data->hasPages())
            <div class="mt-8 flex items-center justify-between gap-4 flex-wrap">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Menampilkan {{ $data->firstItem() ?? 0 }}–{{ $data->lastItem() ?? 0 }}
                    dari {{ $data->total() }} batch peminjaman
                </p>
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-3">
                    {{ $data->onEachSide(1)->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        @endif
    </div>
@endsection
