@extends('layouts.app')

@section('title', 'Detail Alat')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    @php
        $currentUser = auth()->user();
        $isAdmin = $currentUser && in_array($currentUser->role, ['admin', 'super'], true);

        // Null-safe access untuk kolom yang mungkin tidak ada
        $noSeri = $alat->no_seri ?? null;
        $noIdentitas =
            $alat->no_identitas ?? ($alat->nomor_identitas ?? ($alat->identitas ?? ($alat->kode_alat ?? null)));

        $statusMap = [
            'expired' => [
                'label' => 'Kedaluwarsa',
                'icon' => 'fa-exclamation-circle',
                'color' => 'red',
                'animate' => true,
            ],
            'warning' => ['label' => 'Segera Kalibrasi', 'icon' => 'fa-clock', 'color' => 'amber', 'animate' => false],
        ];
        $status = $statusMap[$alat->status] ?? [
            'label' => 'Status Baik',
            'icon' => 'fa-check-circle',
            'color' => 'emerald',
            'animate' => false,
        ];
    @endphp

    <div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 lg:px-8" x-data="{ imgModal: false, imgSrc: '', imgTitle: '' }">

        {{-- Back Button --}}
        <a href="{{ route('alats.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali ke Daftar</span>
        </a>

        {{-- ═══════ HEADER CARD ═══════ --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-6">
            <div
                class="bg-gradient-to-br from-amber-50 via-white to-yellow-50 dark:from-amber-900/20 dark:via-gray-800 dark:to-yellow-900/20 p-6 sm:p-8 border-b border-amber-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-start gap-5">
                    {{-- Icon / Foto kecil --}}
                    <div class="flex-shrink-0">
                        @if ($alat->foto)
                            <img src="{{ $alat->foto_thumb }}" alt="{{ $alat->nama_alat }}"
                                class="w-20 h-20 rounded-2xl object-cover border-2 border-white dark:border-gray-700 shadow-md cursor-pointer hover:scale-105 transition-transform"
                                @click="imgSrc = '{{ $alat->foto_url }}'; imgTitle = '{{ addslashes($alat->nama_alat) }}'; imgModal = true">
                        @else
                            <div
                                class="w-20 h-20 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center border-2 border-white dark:border-gray-700 shadow-md">
                                <i class="fas fa-tools text-3xl text-amber-500"></i>
                            </div>
                        @endif
                    </div>

                    {{-- Info utama --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div class="min-w-0">
                                <p class="text-xs font-mono text-gray-400 dark:text-gray-500">ID: {{ $alat->hashid }}</p>
                                <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white break-words">
                                    {{ $alat->nama_alat }}
                                </h1>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $alat->merk ?? '-' }}
                                    @if ($alat->tipe && $alat->tipe !== '-')
                                        • {{ $alat->tipe }}
                                    @endif
                                </p>
                            </div>

                            {{-- Status badge --}}
                            <span
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold ring-1
                                bg-{{ $status['color'] }}-50 dark:bg-{{ $status['color'] }}-900/30 text-{{ $status['color'] }}-700 dark:text-{{ $status['color'] }}-300 ring-{{ $status['color'] }}-200 dark:ring-{{ $status['color'] }}-800
                                {{ $status['animate'] ? 'animate-pulse' : '' }}">
                                <i class="fas {{ $status['icon'] }}"></i>
                                {{ $status['label'] }}
                            </span>
                        </div>

                        {{-- Badge No Identitas & No Seri --}}
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($noIdentitas)
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-indigo-50 dark:bg-indigo-950 px-3 py-1.5 text-xs font-bold text-indigo-800 dark:text-indigo-300">
                                    <i class="fas fa-id-card"></i>
                                    {{ $noIdentitas }}
                                </span>
                            @endif
                            @if ($noSeri)
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 font-mono">
                                    <i class="fas fa-barcode"></i>
                                    {{ $noSeri }}
                                </span>
                            @endif
                            @if ($alat->kategori)
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-amber-50 dark:bg-amber-950 px-3 py-1.5 text-xs font-bold text-amber-800 dark:text-amber-300">
                                    <i class="fas fa-tag"></i>
                                    {{ $alat->kategori->nama }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════ SPESIFIKASI TEKNIS ═══════ --}}
            <div class="p-6 sm:p-8">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <i class="fas fa-list-check text-amber-600 dark:text-amber-400 text-sm"></i>
                    </span>
                    Spesifikasi Teknis
                </h3>

                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @php
                        $specs = [
                            ['label' => 'Merk', 'icon' => 'fa-industry', 'value' => $alat->merk],
                            ['label' => 'Tipe', 'icon' => 'fa-cube', 'value' => $alat->tipe],
                            ['label' => 'No Seri', 'icon' => 'fa-barcode', 'value' => $noSeri, 'mono' => true],
                            [
                                'label' => 'No Identitas',
                                'icon' => 'fa-id-card',
                                'value' => $noIdentitas,
                                'mono' => true,
                                'accent' => 'indigo',
                            ],
                            ['label' => 'Kelas', 'icon' => 'fa-layer-group', 'value' => $alat->kelas ?? null],
                            [
                                'label' => 'Kapasitas',
                                'icon' => 'fa-weight-hanging',
                                'value' => $alat->kapasitas ?? null,
                                'accent' => 'amber',
                            ],
                            ['label' => 'Daya Baca', 'icon' => 'fa-eye', 'value' => $alat->daya_baca ?? null],
                            [
                                'label' => 'Jumlah',
                                'icon' => 'fa-boxes',
                                'value' => $alat->jumlah ?? 1,
                                'accent' => 'amber',
                            ],
                            ['label' => 'Kategori', 'icon' => 'fa-tag', 'value' => $alat->kategori->nama ?? null],
                        ];
                    @endphp

                    @foreach ($specs as $spec)
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                            <dt
                                class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <i class="fas {{ $spec['icon'] }} text-[10px] text-amber-500"></i>
                                {{ $spec['label'] }}
                            </dt>
                            @php
                                $accent = $spec['accent'] ?? null;
                                $mono = $spec['mono'] ?? false;
                            @endphp
                            <dd
                                class="mt-1.5 break-words text-base font-semibold
    {{ $accent === 'amber' ? 'text-amber-700 dark:text-amber-300' : ($accent === 'indigo' ? 'text-indigo-700 dark:text-indigo-300' : 'text-gray-900 dark:text-white') }}">
                                @if (!empty($spec['value']))
                                    @if ($mono)
                                        <span
                                            class="inline-flex rounded-md bg-white dark:bg-gray-800 px-2 py-0.5 font-mono text-sm border border-gray-200 dark:border-gray-700">
                                            {{ $spec['value'] }}
                                        </span>
                                    @else
                                        {{ $spec['value'] }}
                                    @endif
                                @else
                                    <span class="text-gray-400 dark:text-gray-500 font-normal">-</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- ═══════ SERTIFIKAT & MASA BERLAKU ═══════ --}}
            @if (($alat->no_sertifikat ?? null) || ($alat->masa_berlaku ?? null))
                <div class="px-6 sm:px-8 pb-6 sm:pb-8">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <i class="fas fa-file-certificate text-amber-600 dark:text-amber-400 text-sm"></i>
                        </span>
                        Sertifikat &amp; Masa Berlaku
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if ($alat->no_sertifikat ?? null)
                            <div
                                class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                                <dt
                                    class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-file-signature text-[10px] text-amber-500"></i>
                                    No Sertifikat
                                </dt>
                                <dd
                                    class="mt-1.5 break-words font-mono text-base font-semibold text-gray-900 dark:text-white">
                                    {{ $alat->no_sertifikat }}
                                </dd>
                            </div>
                        @endif

                        @if ($alat->masa_berlaku ?? null)
                            <div
                                class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 border border-gray-100 dark:border-gray-700">
                                <dt
                                    class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    <i class="fas fa-calendar-check text-[10px] text-amber-500"></i>
                                    Masa Berlaku
                                </dt>
                                <dd
                                    class="mt-1.5 text-base font-semibold
                                    {{ $status['color'] === 'red' ? 'text-red-600 dark:text-red-400' : ($status['color'] === 'amber' ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white') }}">
                                    @php
                                        try {
                                            $mb = \Carbon\Carbon::parse($alat->masa_berlaku);
                                            echo $mb->format('d M Y');
                                            if ($status['color'] === 'red') {
                                                echo ' <span class="ml-2 text-xs font-bold uppercase">(Terlewat)</span>';
                                            } elseif ($status['color'] === 'amber' && isset($alat->status_hari)) {
                                                echo ' <span class="ml-2 text-xs font-bold">(' .
                                                    $alat->status_hari .
                                                    ' hari lagi)</span>';
                                            }
                                        } catch (\Throwable $e) {
                                            echo e($alat->masa_berlaku);
                                        }
                                    @endphp
                                </dd>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- ═══════ FOTO & QR CODE ═══════ --}}
        @if (($alat->foto ?? null) || ($alat->qr_code ?? null))
            <div
                class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-6 p-6 sm:p-8">
                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <i class="fas fa-images text-amber-600 dark:text-amber-400 text-sm"></i>
                    </span>
                    Media
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Foto --}}
                    @if ($alat->foto ?? null)
                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                                <i class="fas fa-camera text-amber-500"></i> Foto Alat
                            </p>
                            <div class="flex justify-center">
                                <img src="{{ $alat->foto_url }}" alt="Foto {{ $alat->nama_alat }}"
                                    @click="imgSrc = '{{ $alat->foto_url }}'; imgTitle = '{{ addslashes($alat->nama_alat) }}'; imgModal = true"
                                    class="max-w-full max-h-80 object-contain rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm cursor-pointer hover:opacity-90 hover:scale-[1.02] transition-all">
                            </div>
                            <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-2">
                                <i class="fas fa-search-plus mr-1"></i>Klik untuk memperbesar
                            </p>
                        </div>
                    @endif

                    {{-- QR Code --}}
                    @if ($alat->qr_code ?? null)
                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2 flex items-center gap-2">
                                <i class="fas fa-qrcode text-amber-500"></i> QR Code
                            </p>
                            <div class="flex flex-col items-center gap-3">
                                <img src="{{ asset('storage/' . $alat->qr_code) }}" alt="QR Code {{ $alat->nama_alat }}"
                                    class="w-48 h-48 object-contain rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm bg-white p-2">
                                <a href="{{ asset('storage/' . $alat->qr_code) }}" download
                                    class="inline-flex items-center gap-2 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 transition">
                                    <i class="fas fa-download"></i> Unduh QR Code
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- ═══════ RIWAYAT KALIBRASI ═══════ --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden mb-6 p-6 sm:p-8">
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <i class="fas fa-history text-amber-600 dark:text-amber-400 text-sm"></i>
                    </span>
                    Riwayat Kalibrasi
                </h3>
                @if (isset($alat->kalibrasis) && $alat->kalibrasis->count() > 0)
                    <span
                        class="text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-3 py-1 rounded-full">
                        {{ $alat->kalibrasis->count() }} riwayat
                    </span>
                @endif
            </div>

            @if (isset($alat->kalibrasis) && $alat->kalibrasis->count() > 0)
                <ol class="relative border-l-2 border-amber-200 dark:border-amber-800 ml-3 space-y-6">
                    @foreach ($alat->kalibrasis as $idx => $k)
                        <li class="ml-6">
                            {{-- Dot --}}
                            <span
                                class="absolute -left-3 flex h-6 w-6 items-center justify-center rounded-full
                                {{ $idx === 0 ? 'bg-amber-500 ring-4 ring-amber-100 dark:ring-amber-900/30' : 'bg-amber-300 dark:bg-amber-700' }}">
                                <i class="fas fa-check text-white text-[10px]"></i>
                            </span>

                            <div
                                class="bg-gray-50 dark:bg-gray-900 p-4 rounded-2xl border border-gray-100 dark:border-gray-700">
                                <div class="flex items-start justify-between gap-3 flex-wrap">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Kalibrasi</p>
                                        <p class="font-bold text-gray-900 dark:text-white">
                                            @try
                                                {{ \Carbon\Carbon::parse($k->tanggal_kalibrasi)->format('d M Y') }}
                                                @catch (\Throwable $e)
                                                {{ $k->tanggal_kalibrasi }}
                                            @endtry
                                        </p>
                                    </div>
                                    @if ($k->no_sertifikat ?? null)
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 px-3 py-1 text-xs font-bold text-amber-800 dark:text-amber-300">
                                            <i class="fas fa-file-signature text-[10px]"></i>
                                            {{ $k->no_sertifikat }}
                                        </span>
                                    @endif
                                </div>

                                @if ($k->masa_berlaku_baru ?? null)
                                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Masa Berlaku Baru</p>
                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                            @try
                                                {{ \Carbon\Carbon::parse($k->masa_berlaku_baru)->format('d M Y') }}
                                                @catch (\Throwable $e)
                                                {{ $k->masa_berlaku_baru }}
                                            @endtry
                                        </p>
                                    </div>
                                @endif

                                @if ($k->catatan ?? null)
                                    <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Catatan</p>
                                        <p class="text-sm text-gray-700 dark:text-gray-200 italic">
                                            "{{ $k->catatan }}"
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            @else
                <div
                    class="text-center py-8 text-gray-400 dark:text-gray-500 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl">
                    <i class="fas fa-history text-3xl mb-2 block"></i>
                    <p class="text-sm italic">Belum ada riwayat kalibrasi untuk alat ini.</p>
                </div>
            @endif
        </div>

        {{-- ═══════ ACTION BUTTONS ═══════ --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden p-6 sm:p-8">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <i class="fas fa-bolt text-amber-600 dark:text-amber-400 text-sm"></i>
                </span>
                Aksi
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                {{-- Ambil Alat (untuk semua user terautentikasi) --}}
                @auth
                    <a href="{{ route('pengambilan_alat.create', ['alat_hashid' => $alat->hashid]) }}"
                        class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-semibold py-3.5 px-5 rounded-xl transition-all duration-300 shadow-lg shadow-emerald-200 dark:shadow-none hover:shadow-xl hover:-translate-y-0.5">
                        <i class="fas fa-hand-holding"></i>
                        <span>Ambil Alat</span>
                    </a>
                @endauth

                {{-- Kalibrasi jika expired/warning --}}
                @if (in_array($alat->status, ['expired', 'warning']))
                    <a href="{{ route('kalibrasis.create', $alat->hashid) }}"
                        class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-yellow-500 to-amber-500 hover:from-yellow-600 hover:to-amber-600 text-white font-semibold py-3.5 px-5 rounded-xl transition-all duration-300 shadow-lg shadow-yellow-200 dark:shadow-none hover:shadow-xl hover:-translate-y-0.5">
                        <i class="fas fa-tools"></i>
                        <span>Kalibrasi</span>
                    </a>
                @endif

                {{-- Edit & Hapus hanya untuk admin/super --}}
                @if ($isAdmin)
                    <a href="{{ route('alats.edit', $alat->hashid) }}"
                        class="inline-flex items-center justify-center gap-2 bg-blue-50 dark:bg-blue-900/30 border-2 border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-semibold py-3.5 px-5 rounded-xl transition-all duration-300">
                        <i class="fas fa-pen-to-square"></i>
                        <span>Edit</span>
                    </a>
                    <form method="POST" action="{{ route('alats.destroy', $alat->hashid) }}"
                        onsubmit="return confirm('Yakin ingin menghapus alat {{ addslashes($alat->nama_alat) }}?\n\nData tidak bisa dikembalikan.')"
                        class="contents">
                        @csrf @method('DELETE')
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 bg-red-50 dark:bg-red-900/30 border-2 border-red-200 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-300 font-semibold py-3.5 px-5 rounded-xl transition-all duration-300">
                            <i class="fas fa-trash-alt"></i>
                            <span>Hapus</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- ═══════ LIGHTBOX MODAL ═══════ --}}
        <div x-show="imgModal" x-cloak x-transition.opacity.duration.300ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
            @click="imgModal = false; imgSrc = ''" @keydown.escape.window="imgModal = false; imgSrc = ''">
            <div class="relative max-w-5xl w-full max-h-full flex flex-col items-center" @click.stop>
                <button @click="imgModal = false; imgSrc = ''"
                    class="absolute -top-3 -right-3 w-10 h-10 bg-white dark:bg-gray-800 rounded-full shadow-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition z-10">
                    <i class="fas fa-times"></i>
                </button>
                <img :src="imgSrc" :alt="imgTitle"
                    class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl">
                <p x-show="imgTitle" x-text="imgTitle" class="mt-3 text-sm text-white/80 font-medium"></p>
            </div>
        </div>
    </div>
@endsection
