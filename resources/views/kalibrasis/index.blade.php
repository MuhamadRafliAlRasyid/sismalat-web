@extends('layouts.app')

@section('title', 'Riwayat Kalibrasi Alat')

@section('content')
    @php
        $me = auth()->user();
        $isAdmin = $me && in_array($me->role ?? '', ['admin', 'super'], true);

        // Fallback aman: controller kirim $data, view pakai $kalibrasis
        $kalibrasis = $kalibrasis ?? ($data ?? collect());
        $filters = $filters ?? request()->only(['search', 'date_from', 'date_to']);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        {{-- ═══════════ HEADER ═══════════ --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <span
                    class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 text-white shadow-lg shadow-violet-500/30">
                    <i class="fas fa-certificate text-lg"></i>
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                        Riwayat Kalibrasi
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Pantau riwayat kalibrasi dan masa berlaku sertifikat alat
                    </p>
                </div>
            </div>

            @if ($isAdmin)
                <a href="{{ route('alats.index') }}"
                    class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 hover:bg-violet-50 dark:hover:bg-violet-900/20 text-violet-700 dark:text-violet-400 border border-violet-200 dark:border-violet-800 px-4 py-2 rounded-xl text-sm font-medium transition-all shadow-sm hover:shadow hover:-translate-y-0.5">
                    <i class="fas fa-tools text-xs"></i>
                    <span>Kalibrasi Alat Baru</span>
                </a>
            @endif
        </div>

        {{-- ═══════════ FILTER ═══════════ --}}
        <section
            class="mb-6 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5 dark:border-gray-700 dark:bg-gray-800">
            <form method="GET" action="{{ route('kalibrasis.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                    <div class="lg:col-span-6">
                        <div class="relative">
                            <span
                                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                <i class="fas fa-search text-sm"></i>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Cari nama alat, merk, seri, atau no. sertifikat..."
                                class="min-h-11 w-full rounded-xl border border-gray-300 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder:text-gray-400 focus:border-violet-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <label
                            class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="min-h-11 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-violet-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="lg:col-span-2">
                        <label
                            class="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="min-h-11 w-full rounded-xl border border-gray-300 bg-gray-50 px-3 py-2.5 text-sm text-gray-900 focus:border-violet-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-100 dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    </div>

                    <div class="lg:col-span-2 flex gap-2">
                        <button type="submit"
                            class="flex-1 inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-violet-700">
                            <i class="fas fa-filter text-xs"></i> Filter
                        </button>
                        @if (request('search') || request('date_from') || request('date_to'))
                            <a href="{{ route('kalibrasis.index') }}"
                                class="inline-flex min-h-11 w-11 items-center justify-center rounded-xl border border-gray-300 bg-white text-gray-600 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
                                title="Reset">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </section>

        {{-- ═══════════ LIST KALIBRASI ═══════════ --}}
        <div class="space-y-3">
            @forelse ($kalibrasis as $kalibrasi)
                @php
                    $alat = $kalibrasi->alat;
                    $tglKalibrasi = \Carbon\Carbon::parse($kalibrasi->tanggal_kalibrasi);
                    $masaBerlaku = \Carbon\Carbon::parse($kalibrasi->masa_berlaku_baru);
                    $today = \Carbon\Carbon::today();

                    $isExpired = $masaBerlaku->lessThan($today);
                    $isWarning = !$isExpired && $masaBerlaku->diffInDays($today) <= 30;

                    if ($isExpired) {
                        $statusLabel = 'Expired';
                        $statusClass = 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300';
                        $statusIcon = 'fa-times-circle';
                        $daysText = 'Lewat ' . abs($masaBerlaku->diffInDays($today)) . ' hari';
                    } elseif ($isWarning) {
                        $statusLabel = 'Segera Expired';
                        $statusClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300';
                        $statusIcon = 'fa-exclamation-triangle';
                        $daysText = $masaBerlaku->diffInDays($today) . ' hari lagi';
                    } else {
                        $statusLabel = 'Aktif';
                        $statusClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300';
                        $statusIcon = 'fa-check-circle';
                        $daysText = $today->diffInDays($masaBerlaku) . ' hari lagi';
                    }
                @endphp

                <div
                    class="group bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 hover:border-violet-200 dark:hover:border-violet-800 transition-all duration-200 hover:shadow-md overflow-hidden">
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-col sm:flex-row gap-4">

                            {{-- Thumbnail --}}
                            <div class="flex-shrink-0">
                                @if ($alat && $alat->foto)
                                    <a href="{{ route('alats.show', $alat->hashid) }}"
                                        class="relative block w-full sm:w-24 h-24 rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-900 ring-1 ring-gray-200 dark:ring-gray-700 transition-all">
                                        <img src="{{ asset('storage/alat/thumb_' . $alat->foto) }}"
                                            alt="{{ $alat->nama_alat }}" loading="lazy"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    </a>
                                @else
                                    <div
                                        class="w-full sm:w-24 h-24 rounded-xl bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800 flex flex-col items-center justify-center text-gray-400 dark:text-gray-600 ring-1 ring-gray-200 dark:ring-gray-700">
                                        <i class="fas fa-toolbox text-2xl"></i>
                                    </div>
                                @endif
                            </div>

                            {{-- Konten --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ $alat ? route('alats.show', $alat->hashid) : '#' }}"
                                            class="block font-bold text-gray-900 dark:text-white text-base sm:text-lg hover:text-violet-600 dark:hover:text-violet-400 transition-colors truncate">
                                            {{ $alat->nama_alat ?? 'Alat telah dihapus' }}
                                        </a>
                                        <div
                                            class="flex items-center gap-2 flex-wrap mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            @if ($alat && $alat->merk)
                                                <span>
                                                    <i class="fas fa-tag text-[10px] mr-1"></i>
                                                    {{ $alat->merk }}
                                                    @if ($alat->tipe)
                                                        · {{ $alat->tipe }}
                                                    @endif
                                                </span>
                                            @endif
                                            @if ($alat && $alat->no_seri)
                                                <span>
                                                    <i class="fas fa-barcode text-[10px] mr-1"></i>
                                                    <span class="font-mono">{{ $alat->no_seri }}</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-end gap-1 shrink-0">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $statusClass }}">
                                            <i class="fas {{ $statusIcon }} text-[10px]"></i>
                                            {{ $statusLabel }}
                                        </span>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400">
                                            {{ $daysText }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i class="fas fa-file-certificate text-violet-500 text-xs w-4 text-center"></i>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold">No.
                                                Sertifikat</p>
                                            <p
                                                class="text-xs font-mono font-bold text-gray-700 dark:text-gray-200 truncate">
                                                {{ $kalibrasi->no_sertifikat ?: '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i class="fas fa-calendar-plus text-blue-500 text-xs w-4 text-center"></i>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold">Tgl
                                                Kalibrasi</p>
                                            <p class="text-xs font-medium text-gray-700 dark:text-gray-200 truncate">
                                                {{ $tglKalibrasi->locale('id')->translatedFormat('d M Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i class="fas fa-hourglass-end text-amber-500 text-xs w-4 text-center"></i>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold">
                                                Berlaku s.d.</p>
                                            <p class="text-xs font-bold text-gray-700 dark:text-gray-200 truncate">
                                                {{ $masaBerlaku->locale('id')->translatedFormat('d M Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 min-w-0">
                                        <i class="fas fa-building text-pink-500 text-xs w-4 text-center"></i>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase tracking-wide font-semibold">
                                                Ruangan</p>
                                            <p class="text-xs font-medium text-gray-700 dark:text-gray-200 truncate">
                                                {{ $alat && $alat->ruangan ? $alat->ruangan->kode_ruangan : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if ($kalibrasi->keterangan)
                                    <div
                                        class="mt-3 px-3 py-2 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 rounded-lg flex items-start gap-2">
                                        <i class="fas fa-sticky-note text-gray-400 mt-0.5 text-xs flex-shrink-0"></i>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                            {{ \Illuminate\Support\Str::limit($kalibrasi->keterangan, 120) }}
                                        </p>
                                    </div>
                                @endif

                                <div class="mt-3 flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('kalibrasis.show', $kalibrasi->hashid) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-violet-50 dark:bg-violet-900/30 text-violet-700 dark:text-violet-300 text-xs font-semibold hover:bg-violet-100 dark:hover:bg-violet-900/50 transition">
                                        <i class="fas fa-eye text-[10px]"></i> Detail
                                    </a>

                                    @if ($isAdmin && $alat)
                                        <a href="{{ route('kalibrasis.create', $alat->hashid) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-xs font-semibold hover:bg-emerald-100 dark:hover:bg-emerald-900/50 transition">
                                            <i class="fas fa-rotate text-[10px]"></i> Kalibrasi Ulang
                                        </a>
                                        <a href="{{ route('kalibrasis.edit', $kalibrasi->hashid) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 text-xs font-semibold hover:bg-amber-100 dark:hover:bg-amber-900/50 transition">
                                            <i class="fas fa-pen text-[10px]"></i> Edit
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="text-center py-16 bg-white dark:bg-gray-800 rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700">
                    <div
                        class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-violet-50 to-indigo-50 dark:from-violet-900/20 dark:to-indigo-900/20 mb-4">
                        <i class="fas fa-certificate text-3xl text-violet-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-1">
                        @if (request('search') || request('date_from') || request('date_to'))
                            Tidak ada data yang cocok
                        @else
                            Belum Ada Riwayat Kalibrasi
                        @endif
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6 max-w-md mx-auto px-4">
                        @if (request('search') || request('date_from') || request('date_to'))
                            Coba ubah filter pencarian Anda.
                        @else
                            Riwayat kalibrasi akan muncul setelah Anda melakukan kalibrasi pada alat.
                        @endif
                    </p>
                    <div class="flex items-center justify-center gap-3 flex-wrap">
                        @if (request('search') || request('date_from') || request('date_to'))
                            <a href="{{ route('kalibrasis.index') }}"
                                class="inline-flex items-center gap-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 px-5 py-2.5 rounded-xl font-medium transition-all text-sm">
                                <i class="fas fa-times"></i> Reset Filter
                            </a>
                        @endif
                        @if ($isAdmin)
                            <a href="{{ route('alats.index') }}"
                                class="inline-flex items-center gap-2 bg-gradient-to-r from-violet-500 to-indigo-600 hover:from-violet-600 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-sm text-sm">
                                <i class="fas fa-tools"></i> Kalibrasi Alat Sekarang
                            </a>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        {{-- ═══════════ PAGINATION ═══════════ --}}
        @if ($kalibrasis instanceof \Illuminate\Pagination\LengthAwarePaginator && $kalibrasis->hasPages())
            <div class="mt-8 flex justify-center">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 px-4 py-3">
                    {{ $kalibrasis->onEachSide(1)->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        @endif
    </div>
@endsection
