@extends('layouts.app')

@section('title', 'Daftar Ruangan')

@push('styles')
    <style>
        @keyframes float-slow {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(230, 168, 23, 0.5);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(230, 168, 23, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(230, 168, 23, 0);
            }
        }

        .animate-fade-in-up {
            animation: fade-in-up 0.5s ease-out forwards;
        }

        .animate-float-slow {
            animation: float-slow 6s ease-in-out infinite;
        }

        .glass-card {
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            background: rgba(255, 255, 255, 0.7);
        }

        .dark .glass-card {
            background: rgba(30, 41, 59, 0.7);
        }

        .ruangan-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ruangan-card:hover {
            transform: translateY(-6px);
        }

        .ruangan-card:hover .card-icon {
            transform: rotate(-8deg) scale(1.1);
        }

        .ruangan-card:hover .view-btn {
            background: linear-gradient(135deg, #e6a817, #f59e0b);
            color: white;
        }

        .card-icon {
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-ring {
            animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .progress-bar {
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="ruanganDashboard()">

        {{-- ═══════════════ HERO HEADER ═══════════════ --}}
        <div class="relative overflow-hidden rounded-3xl mb-8">
            <div class="absolute inset-0 bg-gradient-to-br from-amber-500 via-orange-500 to-yellow-600"></div>
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-0 right-0 w-96 h-96 bg-amber-300 rounded-full blur-3xl animate-float-slow"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-yellow-300 rounded-full blur-3xl animate-float-slow"
                    style="animation-delay: -3s;"></div>
            </div>
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 24px 24px;">
            </div>

            <div class="relative p-6 sm:p-10">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="text-white">
                        <div class="flex items-center gap-3 mb-3">
                            <div
                                class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center shadow-xl">
                                <i class="fas fa-building text-2xl"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl sm:text-4xl font-bold tracking-tight">Daftar Ruangan</h1>
                                <p class="text-amber-100 text-sm mt-1">Kartu Inventaris Ruangan (KIR) • Sistem Metrologi</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-3 flex-wrap">
                        <a href="{{ route('ruangans.create') }}"
                            class="group inline-flex items-center gap-2.5 bg-white text-amber-700 hover:bg-amber-50 px-6 py-3.5 rounded-2xl font-semibold transition-all duration-300 shadow-xl hover:shadow-2xl hover:-translate-y-1">
                            <i class="fas fa-plus group-hover:rotate-90 transition-transform duration-300"></i>
                            <span>Tambah Ruangan</span>
                        </a>
                    </div>
                </div>

                {{-- ═══════════════ STATISTICS CARDS ═══════════════ --}}
                @php
                    $totalRuangan = $ruangans->total();
                    $totalAlats = $ruangans->sum('alats_count');
                    // Hitung total kondisi dari koleksi yang sedang ditampilkan
                    $totalBaik = 0;
                    $totalKb = 0;
                    $totalRb = 0;
                    foreach ($ruangans as $r) {
                        $totalBaik += $r->alats->sum('jumlah_baik');
                        $totalKb += $r->alats->sum('jumlah_kurang_baik');
                        $totalRb += $r->alats->sum('jumlah_rusak_berat');
                    }
                @endphp
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-8">
                    <div class="glass-card rounded-2xl p-4 border border-white/20">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center shadow-lg stat-ring">
                                <i class="fas fa-door-open text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-amber-100 text-xs font-medium uppercase tracking-wider">Total Ruangan</p>
                                <p class="text-2xl font-bold text-white">{{ $totalRuangan }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl p-4 border border-white/20">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center shadow-lg">
                                <i class="fas fa-boxes text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-amber-100 text-xs font-medium uppercase tracking-wider">Total Alat</p>
                                <p class="text-2xl font-bold text-white">{{ $totalAlats }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl p-4 border border-white/20">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-lg">
                                <i class="fas fa-check-circle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-amber-100 text-xs font-medium uppercase tracking-wider">Kondisi Baik</p>
                                <p class="text-2xl font-bold text-white">{{ $totalBaik }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="glass-card rounded-2xl p-4 border border-white/20">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-400 to-red-600 flex items-center justify-center shadow-lg">
                                <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                            </div>
                            <div>
                                <p class="text-amber-100 text-xs font-medium uppercase tracking-wider">Perlu Perhatian</p>
                                <p class="text-2xl font-bold text-white">{{ $totalKb + $totalRb }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════ ALERTS ═══════════════ --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0" x-init="setTimeout(() => show = false, 4000)"
                class="mb-6 bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-lg">
                <span
                    class="flex-shrink-0 w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center shadow-lg"><i
                        class="fas fa-check text-white"></i></span>
                <span class="flex-1 font-semibold">{{ session('success') }}</span>
                <button @click="show = false"
                    class="w-8 h-8 rounded-lg hover:bg-emerald-100 flex items-center justify-center transition-colors"><i
                        class="fas fa-times text-emerald-500"></i></button>
            </div>
        @endif

        @if (session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0" x-init="setTimeout(() => show = false, 4000)"
                class="mb-6 bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-lg">
                <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center shadow-lg"><i
                        class="fas fa-exclamation text-white"></i></span>
                <span class="flex-1 font-semibold">{{ session('error') }}</span>
                <button @click="show = false"
                    class="w-8 h-8 rounded-lg hover:bg-red-100 flex items-center justify-center transition-colors"><i
                        class="fas fa-times text-red-500"></i></button>
            </div>
        @endif

        {{-- ═══════════════ SEARCH & FILTER ═══════════════ --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-amber-100/50 dark:border-gray-700 p-4 mb-6">
            <form method="GET" action="{{ route('ruangans.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i
                            class="fas fa-search text-amber-400"></i></div>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari kode, nama, atau penanggung jawab..."
                        class="w-full pl-11 pr-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-gray-700 dark:text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent transition-all">
                </div>
                @if (request('search'))
                    <a href="{{ route('ruangans.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-xl font-medium transition-all"><i
                            class="fas fa-times"></i><span>Reset</span></a>
                @endif
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl font-semibold transition-all shadow-md hover:shadow-lg shadow-amber-200 dark:shadow-amber-900/30"><i
                        class="fas fa-search"></i><span>Cari</span></button>
            </form>
        </div>

        {{-- ═══════════════ RUANGAN GRID ═══════════════ --}}
        @if ($ruangans->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($ruangans as $index => $ruangan)
                    @php
                        $total = $ruangan->alats_count;
                        $baik = $ruangan->alats->sum('jumlah_baik') ?? 0;
                        $kb = $ruangan->alats->sum('jumlah_kurang_baik') ?? 0;
                        $rb = $ruangan->alats->sum('jumlah_rusak_berat') ?? 0;
                        $totalUnit = $baik + $kb + $rb;
                        $persenBaik = $totalUnit > 0 ? round(($baik / $totalUnit) * 100) : 0;

                        if ($totalUnit == 0) {
                            $health = [
                                'label' => 'Kosong',
                                'color' => 'gray',
                                'icon' => 'fa-inbox',
                                'bg' => 'from-gray-400 to-gray-500',
                            ];
                        } elseif ($persenBaik >= 90) {
                            $health = [
                                'label' => 'Sangat Baik',
                                'color' => 'emerald',
                                'icon' => 'fa-shield-check',
                                'bg' => 'from-emerald-400 to-emerald-600',
                            ];
                        } elseif ($persenBaik >= 70) {
                            $health = [
                                'label' => 'Baik',
                                'color' => 'amber',
                                'icon' => 'fa-thumbs-up',
                                'bg' => 'from-amber-400 to-amber-600',
                            ];
                        } elseif ($persenBaik >= 50) {
                            $health = [
                                'label' => 'Cukup',
                                'color' => 'orange',
                                'icon' => 'fa-exclamation-circle',
                                'bg' => 'from-orange-400 to-orange-600',
                            ];
                        } else {
                            $health = [
                                'label' => 'Perhatian',
                                'color' => 'red',
                                'icon' => 'fa-triangle-exclamation',
                                'bg' => 'from-red-400 to-red-600',
                            ];
                        }

                        $pjName = $ruangan->user->name ?? 'Unknown';
                        $words = explode(' ', trim($pjName));
                        $initials = strtoupper(substr($words[0] ?? '', 0, 1) . substr($words[1] ?? '', 0, 1));
                    @endphp

                    <div class="ruangan-card group relative bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm hover:shadow-2xl hover:border-amber-200 dark:hover:border-amber-700"
                        style="animation: fade-in-up 0.5s ease-out {{ $index * 0.05 }}s both;">
                        <div class="h-1.5 bg-gradient-to-r {{ $health['bg'] }}"></div>
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="card-icon w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-100 dark:from-amber-900/40 dark:to-orange-900/40 flex items-center justify-center border-2 border-amber-100 dark:border-amber-800 shadow-md">
                                        <i class="fas fa-door-open text-2xl text-amber-600 dark:text-amber-400"></i>
                                    </div>
                                    <div>
                                        <h3
                                            class="font-bold text-gray-800 dark:text-gray-100 text-xl group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                            {{ $ruangan->kode_ruangan }}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate max-w-[180px]">
                                            {{ $ruangan->nama_ruangan ?? 'Ruangan ' . $ruangan->kode_ruangan }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-{{ $health['color'] }}-100 dark:bg-{{ $health['color'] }}-900/30 text-{{ $health['color'] }}-700 dark:text-{{ $health['color'] }}-300 ring-1 ring-{{ $health['color'] }}-200 dark:ring-{{ $health['color'] }}-800">
                                        <i class="fas {{ $health['icon'] }} text-[10px]"></i> {{ $health['label'] }}
                                    </span>
                                    <span
                                        class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">{{ $total }}
                                        jenis alat</span>
                                </div>
                            </div>

                            <div
                                class="bg-gradient-to-br from-amber-50 to-orange-50/30 dark:from-amber-900/10 dark:to-orange-900/10 rounded-xl p-3 mb-4 border border-amber-100/50 dark:border-amber-900/30">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white font-bold text-sm shadow-md flex-shrink-0 shadow-amber-200 dark:shadow-amber-900/30">
                                        {{ $initials }}</div>
                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="text-[10px] text-amber-600 dark:text-amber-400 uppercase font-semibold tracking-wider">
                                            Penanggung Jawab</p>
                                        <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm truncate">
                                            {{ $pjName }}</p>
                                        @if ($ruangan->user && $ruangan->user->nip)
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 font-mono truncate">NIP.
                                                {{ substr($ruangan->user->nip, 0, 8) . ' ' . substr($ruangan->user->nip, 8, 6) . ' ' . substr($ruangan->user->nip, 14, 1) . ' ' . substr($ruangan->user->nip, 15, 3) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if ($totalUnit > 0)
                                <div class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span
                                            class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">Kondisi
                                            Aset</span>
                                        <span
                                            class="text-xs font-bold text-amber-600 dark:text-amber-400">{{ $persenBaik }}%
                                            Baik</span>
                                    </div>
                                    <div
                                        class="flex gap-0.5 h-2 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                                        @if ($baik > 0)
                                            <div class="bg-gradient-to-r from-emerald-400 to-emerald-500 progress-bar"
                                                style="width: {{ ($baik / $totalUnit) * 100 }}%"></div>
                                        @endif
                                        @if ($kb > 0)
                                            <div class="bg-gradient-to-r from-amber-400 to-amber-500 progress-bar"
                                                style="width: {{ ($kb / $totalUnit) * 100 }}%"></div>
                                        @endif
                                        @if ($rb > 0)
                                            <div class="bg-gradient-to-r from-red-400 to-red-500 progress-bar"
                                                style="width: {{ ($rb / $totalUnit) * 100 }}%"></div>
                                        @endif
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 mt-3">
                                        <div class="text-center">
                                            <div class="flex items-center justify-center gap-1.5 mb-0.5"><span
                                                    class="w-2 h-2 rounded-full bg-emerald-500"></span><span
                                                    class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Baik</span>
                                            </div>
                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                                {{ $baik }}</p>
                                        </div>
                                        <div class="text-center">
                                            <div class="flex items-center justify-center gap-1.5 mb-0.5"><span
                                                    class="w-2 h-2 rounded-full bg-amber-500"></span><span
                                                    class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">K.Baik</span>
                                            </div>
                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                                {{ $kb }}</p>
                                        </div>
                                        <div class="text-center">
                                            <div class="flex items-center justify-center gap-1.5 mb-0.5"><span
                                                    class="w-2 h-2 rounded-full bg-red-500"></span><span
                                                    class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Rusak</span>
                                            </div>
                                            <p class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                                {{ $rb }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div
                                    class="mb-4 py-6 text-center bg-amber-50/50 dark:bg-amber-900/10 rounded-xl border border-dashed border-amber-200 dark:border-amber-800/50">
                                    <i class="fas fa-inbox text-2xl text-amber-300 dark:text-amber-700 mb-2"></i>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">Belum ada alat terdaftar</p>
                                </div>
                            @endif

                            <div class="flex gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                                {{-- PERBAIKAN: Menggunakan $ruangan->hashid --}}
                                <a href="{{ route('ruangans.show', $ruangan->hashid) }}"
                                    class="view-btn flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 hover:bg-gradient-to-r hover:from-amber-500 hover:to-orange-600 hover:text-white rounded-xl font-semibold text-sm transition-all duration-300 shadow-sm hover:shadow-lg shadow-amber-100 dark:shadow-amber-900/20">
                                    <i class="fas fa-eye"></i><span>Lihat Detail</span>
                                </a>
                                <a href="{{ route('ruangans.edit', $ruangan->hashid) }}"
                                    class="inline-flex items-center justify-center w-10 h-10 bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-amber-100 hover:text-amber-700 dark:hover:bg-amber-900/30 dark:hover:text-amber-400 rounded-xl transition-all duration-200 hover:scale-105"
                                    title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('ruangans.destroy', $ruangan->hashid) }}"
                                    onsubmit="return confirm('Yakin ingin menghapus ruangan {{ $ruangan->kode_ruangan }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center justify-center w-10 h-10 bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-red-100 hover:text-red-700 dark:hover:bg-red-900/30 dark:hover:text-red-400 rounded-xl transition-all duration-200 hover:scale-105"
                                        title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty State --}}
            <div
                class="relative bg-gradient-to-br from-amber-50 via-white to-orange-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-800 rounded-3xl border border-amber-100 dark:border-gray-700 overflow-hidden">
                <div class="relative py-20 px-6 text-center">
                    <div class="relative inline-block mb-6">
                        <div class="absolute inset-0 bg-amber-400 rounded-full blur-2xl opacity-20 animate-pulse"></div>
                        <div
                            class="relative w-28 h-28 rounded-full bg-gradient-to-br from-amber-400 to-orange-600 flex items-center justify-center shadow-2xl shadow-amber-300 dark:shadow-amber-900">
                            <i class="fas fa-building text-5xl text-white"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">
                        {{ request('search') ? 'Tidak Ditemukan' : 'Belum Ada Ruangan' }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">
                        {{ request('search') ? 'Tidak ada ruangan yang cocok dengan pencarian "' . request('search') . '"' : 'Mulai tambahkan ruangan untuk mengelola inventaris alat di setiap lokasi' }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        @if (request('search'))
                            <a href="{{ route('ruangans.index') }}"
                                class="inline-flex items-center justify-center gap-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600 px-6 py-3 rounded-2xl font-medium transition-all"><i
                                    class="fas fa-times"></i> Reset Pencarian</a>
                        @endif
                        <a href="{{ route('ruangans.create') }}"
                            class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white px-8 py-3 rounded-2xl font-semibold transition-all shadow-xl shadow-amber-200 dark:shadow-amber-900 hover:shadow-2xl hover:-translate-y-0.5"><i
                                class="fas fa-plus"></i> Tambah Ruangan Pertama</a>
                    </div>
                </div>
            </div>
        @endif

        @if ($ruangans->hasPages())
            <div class="mt-10 flex justify-center">
                <div
                    class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-amber-100/50 dark:border-gray-700 px-5 py-4">
                    {{ $ruangans->onEachSide(1)->links('vendor.pagination.tailwind') }}
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            function ruanganDashboard() {
                return {
                    viewMode: 'grid',
                    showViewOptions: false
                }
            }
        </script>
    @endpush
@endsection
