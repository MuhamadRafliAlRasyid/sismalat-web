@extends('layouts.app')

@section('title', 'Pengembalian Alat')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Header --}}
        <div
            class="relative overflow-hidden bg-gradient-to-br from-amber-50 via-white to-orange-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-800 rounded-3xl p-6 sm:p-8 border border-amber-100 dark:border-gray-700 shadow-sm">
            <div
                class="absolute top-0 right-0 w-64 h-64 bg-amber-200/20 dark:bg-amber-900/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-3xl">
            </div>
            <div
                class="absolute bottom-0 left-0 w-48 h-48 bg-orange-200/20 dark:bg-orange-900/10 rounded-full translate-y-1/2 -translate-x-1/2 blur-3xl">
            </div>

            <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span
                            class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200/50 dark:shadow-none">
                            <i class="fas fa-undo-alt text-xl"></i>
                        </span>
                        <div>
                            <h2
                                class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-amber-700 to-orange-700 dark:from-amber-400 dark:to-orange-400 bg-clip-text text-transparent">
                                Pengembalian Alat
                            </h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Riwayat pengembalian alat yang telah
                                dipinjam</p>
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 flex-wrap w-full sm:w-auto">
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="inline-flex items-center justify-center gap-2 bg-white dark:bg-gray-700 hover:bg-amber-50 dark:hover:bg-gray-600 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-gray-600 px-4 py-2.5 rounded-xl font-medium transition-all duration-300 shadow-sm flex-1 sm:flex-none">
                        <i class="fas fa-list"></i> Data Peminjaman
                    </a>
                    @if (in_array(auth()->user()->role, ['admin', 'super']))
                        <a href="{{ route('pengembalian_alat.export') }}"
                            class="inline-flex items-center justify-center gap-2 bg-white dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-gray-600 text-red-600 dark:text-red-400 border border-red-200 dark:border-gray-600 px-4 py-2.5 rounded-xl font-medium transition-all duration-300 shadow-sm hover:shadow-md flex-1 sm:flex-none">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Alert Success --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" x-init="setTimeout(() => show = false, 4000)"
                class="bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <span
                    class="flex-shrink-0 w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-800 flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
                </span>
                <span class="flex-1 font-medium">{{ session('success') }}</span>
                <button @click="show = false"
                    class="flex-shrink-0 w-8 h-8 rounded-xl hover:bg-emerald-200 dark:hover:bg-emerald-800 flex items-center justify-center transition-colors">
                    <i class="fas fa-times text-emerald-500 dark:text-emerald-400"></i>
                </button>
            </div>
        @endif

        {{-- Filter Form --}}
        <form method="GET"
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 sm:p-6 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                {{-- Search --}}
                <div class="md:col-span-5 relative">
                    <label for="search"
                        class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 ml-1">Pencarian</label>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none mt-6">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama alat, merk, atau pengguna..."
                        class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all duration-200">
                </div>

                {{-- Date Filter --}}
                <div class="md:col-span-3">
                    <label for="tanggal"
                        class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 ml-1">Tanggal
                        Pengembalian</label>
                    <input type="date" id="tanggal" name="tanggal" value="{{ request('tanggal') }}"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all duration-200">
                </div>

                {{-- Status Filter (Dimanfaatkan dari logic controller) --}}
                <div class="md:col-span-3">
                    <label for="status"
                        class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 ml-1">Status
                        Keterlambatan</label>
                    <select id="status" name="status"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all duration-200 appearance-none">
                        <option value="">Semua Status</option>
                        <option value="ontime" {{ request('status') == 'ontime' ? 'selected' : '' }}>Tepat Waktu</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Terlambat</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="md:col-span-1 flex gap-2">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-medium px-4 py-3 rounded-xl transition-all duration-300 shadow-md shadow-amber-200/50 dark:shadow-none hover:-translate-y-0.5"
                        title="Terapkan Filter">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if (request('search') || request('tanggal') || request('status'))
                        <a href="{{ route('pengembalian_alat.index') }}"
                            class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-xl transition-all"
                            title="Reset Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- List --}}
        <div class="space-y-4">
            @forelse($data as $d)
                @php
                    $isAdmin = in_array(auth()->user()->role, ['admin', 'super']);
                    $statusAlat = $d->pengambilan->alat->status ?? 'ok';

                    // Cek keterlambatan berdasarkan logic di controller
                    $isLate =
                        $d->pengambilan &&
                        $d->pengambilan->tanggal_jatuh_tempo &&
                        \Carbon\Carbon::parse($d->tanggal_pengembalian)
                            ->startOfDay()
                            ->greaterThan(\Carbon\Carbon::parse($d->pengambilan->tanggal_jatuh_tempo)->startOfDay());

                    // Fallback nama alat dan foto untuk kompatibilitas batch/legacy
                    $alatName =
                        $d->pengambilan->alat->nama_alat ??
                        ($d->item && $d->item->alat ? $d->item->alat->nama_alat : 'Alat Tidak Diketahui');
                    $fotoAlat =
                        $d->pengambilan->alat->foto_thumb ??
                        ($d->item && $d->item->alat ? $d->item->alat->foto_thumb : null);
                @endphp

                <div
                    class="group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-lg hover:border-amber-200 dark:hover:border-amber-700/50 overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-gradient-to-b from-amber-400 to-orange-500"></div>

                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row gap-5">
                            {{-- Thumbnail --}}
                            <div class="flex-shrink-0">
                                <div
                                    class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center overflow-hidden border border-gray-100 dark:border-gray-700 group-hover:border-amber-200 dark:group-hover:border-amber-700/50 transition-colors">
                                    @if ($fotoAlat)
                                        <img src="{{ $fotoAlat }}" alt="{{ $alatName }}" loading="lazy"
                                            class="w-full h-full object-cover"
                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    @endif
                                    <div
                                        class="w-full h-full items-center justify-center text-gray-300 dark:text-gray-600 {{ $fotoAlat ? 'hidden' : 'flex' }}">
                                        <i class="fas fa-tools text-3xl"></i>
                                    </div>

                                    {{-- Status Indicator Dot --}}
                                    <div
                                        class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-900 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border-2 border-white dark:border-gray-800 shadow-sm">
                                        <i class="fas fa-check text-xs"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Info Utama --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                                    <div class="min-w-0">
                                        <h3
                                            class="font-bold text-gray-800 dark:text-gray-100 text-lg truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                            {{ $alatName }}
                                        </h3>
                                        <div class="flex items-center gap-2 mt-2 flex-wrap">
                                            <span
                                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-200 dark:ring-emerald-800">
                                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-emerald-500"></span>
                                                Dikembalikan
                                            </span>

                                            @if ($isLate)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-800">
                                                    <i class="fas fa-clock mr-1.5"></i> Terlambat
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 ring-1 ring-blue-200 dark:ring-blue-800">
                                                    <i class="fas fa-check-circle mr-1.5"></i> Tepat Waktu
                                                </span>
                                            @endif

                                            @if ($statusAlat == 'expired')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 ring-1 ring-red-200 dark:ring-red-800">
                                                    <i class="fas fa-exclamation-circle mr-1.5"></i> Kalibrasi Expired
                                                </span>
                                            @elseif ($statusAlat == 'warning')
                                                <span
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 ring-1 ring-yellow-200 dark:ring-yellow-800">
                                                    <i class="fas fa-exclamation-triangle mr-1.5"></i> Kalibrasi Warning
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                            <i class="fas fa-user text-sm"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p
                                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                                                Pengguna</p>
                                            <p class="font-semibold text-gray-700 dark:text-gray-200 truncate">
                                                {{ $d->pengambilan->user->name ?? ($d->user->name ?? '-') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                            <i class="fas fa-boxes text-sm"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p
                                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                                                Jumlah</p>
                                            <p class="font-semibold text-amber-600 dark:text-amber-400">
                                                {{ $d->jumlah }} Unit</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span
                                            class="flex-shrink-0 w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-amber-600 dark:text-amber-400">
                                            <i class="fas fa-calendar-check text-sm"></i>
                                        </span>
                                        <div class="min-w-0">
                                            <p
                                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wide">
                                                Tanggal Kembali</p>
                                            <p class="font-semibold text-gray-700 dark:text-gray-200">
                                                {{ \Carbon\Carbon::parse($d->tanggal_pengembalian)->translatedFormat('d M Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if ($d->keterangan)
                                    <div
                                        class="mt-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-gray-100 dark:border-gray-700/50">
                                        <div class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-300">
                                            <i class="fas fa-sticky-note text-amber-500 mt-0.5"></i>
                                            <span class="break-words">{{ $d->keterangan }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            <div
                                class="flex sm:flex-col gap-2 justify-end sm:justify-start border-t sm:border-t-0 sm:border-l border-gray-100 dark:border-gray-700 pt-4 sm:pt-0 sm:pl-4 mt-4 sm:mt-0">
                                <a href="{{ route('pengembalian_alat.show', $d->hashid) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-amber-100 dark:hover:bg-amber-900/30 hover:text-amber-600 dark:hover:text-amber-400 transition-all duration-200 hover:scale-105"
                                    title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if (in_array($statusAlat, ['expired', 'warning']) && $d->pengambilan && $d->pengambilan->alat)
                                    <a href="{{ route('kalibrasi.create', $d->pengambilan->alat->hashid) }}"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 dark:text-yellow-400 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 hover:text-yellow-700 dark:hover:text-yellow-300 transition-all duration-200 hover:scale-105"
                                        title="Kalibrasi Alat">
                                        <i class="fas fa-tools"></i>
                                    </a>
                                @endif

                                @if ($isAdmin)
                                    <a href="{{ route('pengembalian_alat.edit', $d->hashid) }}"
                                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 hover:text-blue-700 dark:hover:text-blue-300 transition-all duration-200 hover:scale-105"
                                        title="Edit Data">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('pengembalian_alat.destroy', $d->hashid) }}"
                                        onsubmit="return confirm('Yakin ingin menghapus data pengembalian ini? Stok alat akan dikurangi kembali.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 hover:text-red-700 dark:hover:text-red-300 transition-all duration-200 hover:scale-105"
                                            title="Hapus Data">
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
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-3xl border border-dashed border-gray-200 dark:border-gray-700">
                    <div
                        class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-amber-50 dark:bg-amber-900/20 mb-6">
                        <i class="fas fa-undo-alt text-4xl text-amber-300 dark:text-amber-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-2">Belum Ada Data Pengembalian</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">Data pengembalian akan muncul di sini
                        setelah alat yang dipinjam dikembalikan oleh pengguna.</p>
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="inline-flex items-center gap-2 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-400 px-6 py-3 rounded-xl font-semibold transition-all duration-300">
                        <i class="fas fa-list"></i> Lihat Data Peminjaman
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($data->hasPages())
            <div class="mt-8 flex justify-center">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-3">
                    {{ $data->onEachSide(1)->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        @endif
    </div>
@endsection
