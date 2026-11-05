@extends('layouts.app')

@section('title', 'Ambil Alat')

@push('styles')
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
@endpush

@section('content')
    @php
        $me = auth()->user();
        $isAdmin = $me->isAdminOrSuper();
        $MAX = 15;

        /*
        |--------------------------------------------------------------------------
        | KATALOG ALAT → JSON (NULL-SAFE + NO SERI & IDENTITAS)
        |--------------------------------------------------------------------------
        */
        $alatsJson = $alats
            ->map(function ($a) {
                // Fallback untuk beberapa kemungkinan nama kolom identitas
                $noIdentitas = $a->no_identitas ?? ($a->nomor_identitas ?? ($a->identitas ?? ($a->kode_alat ?? null)));

                return [
                    'hashid' => $a->hashid ?? '',
                    'nama_alat' => $a->nama_alat ?? 'Alat',
                    'merk' => $a->merk ?? null,
                    'tipe' => $a->tipe ?? null,
                    'no_seri' => $a->no_seri ?? null,
                    'no_identitas' => $noIdentitas,
                    'satuan' => $a->satuan ?? null ?: 'unit',
                    'kelas' => $a->kelas ?? null,
                    'kapasitas' => $a->kapasitas ?? null,
                    'stok' => (int) ($a->jumlah ?? 0),
                ];
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | RESTORE KERANJANG DARI old('items')
        |--------------------------------------------------------------------------
        */
        $initialCart = collect(old('items', []))
            ->map(function ($row) use ($alats) {
                $alat = $alats->firstWhere('hashid', $row['alat_id'] ?? null);
                if (!$alat) {
                    return null;
                }

                $noIdt =
                    $alat->no_identitas ?? ($alat->nomor_identitas ?? ($alat->identitas ?? ($alat->kode_alat ?? null)));

                return [
                    'hashid' => $alat->hashid ?? '',
                    'nama_alat' => $alat->nama_alat ?? 'Alat',
                    'merk' => $alat->merk ?? null,
                    'no_seri' => $alat->no_seri ?? null,
                    'no_identitas' => $noIdt,
                    'satuan' => $alat->satuan ?? null ?: 'unit',
                    'stok' => (int) ($alat->jumlah ?? 0),
                    'jumlah' => max(1, (int) ($row['jumlah'] ?? 1)),
                ];
            })
            ->filter()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | PREFILL DARI ?alat_hashid=...
        |--------------------------------------------------------------------------
        */
        if (!empty($prefill) && !$initialCart->contains('hashid', $prefill)) {
            $a = $alats->firstWhere('hashid', $prefill);
            if ($a && (int) ($a->jumlah ?? 0) > 0) {
                $noIdt2 = $a->no_identitas ?? ($a->nomor_identitas ?? ($a->identitas ?? ($a->kode_alat ?? null)));

                $initialCart->push([
                    'hashid' => $a->hashid ?? '',
                    'nama_alat' => $a->nama_alat ?? 'Alat',
                    'merk' => $a->merk ?? null,
                    'no_seri' => $a->no_seri ?? null,
                    'no_identitas' => $noIdt2,
                    'satuan' => $a->satuan ?? null ?: 'unit',
                    'stok' => (int) ($a->jumlah ?? 0),
                    'jumlah' => 1,
                ]);
            }
        }
    @endphp

    <div class="max-w-6xl mx-auto px-4 py-8">
        <a href="{{ route('pengambilan_alat.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali</span>
        </a>

        <div x-data="alatCart(
            {{ \Illuminate\Support\Js::from($alatsJson) }},
            {{ \Illuminate\Support\Js::from($initialCart) }},
            {{ $MAX }}
        )"
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">

            {{-- Header --}}
            <div
                class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-6 sm:p-8 border-b border-amber-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <span
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-amber-100 dark:shadow-none">
                        <i class="fas fa-hand-holding text-xl text-amber-500"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Ambil Alat Baru</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
                            Satu peminjaman bisa berisi <strong>banyak alat</strong> sekaligus dalam satu batch.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Error server per-field --}}
            @if ($errors->any())
                <div
                    class="mx-6 sm:mx-8 mt-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-2xl text-sm">
                    <p class="font-bold mb-1"><i class="fas fa-triangle-exclamation mr-1"></i>Periksa kembali:</p>
                    <ul class="list-disc pl-5 space-y-0.5">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('pengambilan_alat.store') }}" enctype="multipart/form-data"
                x-on:submit="guardSubmit($event)" class="p-6 sm:p-8 space-y-8">
                @csrf

                {{-- ═══════ SECTION 1: DATA & JADWAL ═══════ --}}
                <div>
                    <h3
                        class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-xs font-extrabold">1</span>
                        Data Peminjam &amp; Jadwal
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if ($isAdmin)
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">User
                                    Peminjam <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="user_id" required
                                        class="w-full appearance-none px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 pr-10">
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}" @selected(old('user_id', $me->id) == $u->id)>
                                                {{ $u->name }} ({{ $u->email }})</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><i
                                            class="fas fa-chevron-down text-gray-400"></i></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Nama
                                    Peminjam</label>
                                <input type="text" name="nama_peminjam" value="{{ old('nama_peminjam') }}"
                                    class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200"
                                    placeholder="Kosongkan jika sama dengan user">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Bagian
                                    <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="bagian_id" required
                                        class="w-full appearance-none px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 pr-10">
                                        <option value="">— Pilih bagian —</option>
                                        @foreach ($bagians as $b)
                                            <option value="{{ $b->id }}" @selected(old('bagian_id') == $b->id)>
                                                {{ $b->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><i
                                            class="fas fa-chevron-down text-gray-400"></i></div>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="user_id" value="{{ $me->id }}">
                            <input type="hidden" name="bagian_id" value="{{ $me->bagian_id }}">
                            <div
                                class="sm:col-span-2 flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm text-gray-700 dark:text-gray-200">
                                <i class="fas fa-user text-amber-500"></i>{{ $me->name }}
                                @if ($me->bagian)
                                    <span class="text-gray-400">•</span><i
                                        class="fas fa-building text-amber-500"></i>{{ $me->bagian->nama }}
                                @endif
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Lama Pinjam
                                (Hari) <span class="text-red-500">*</span></label>
                            <input type="number" name="lama_pinjam" value="{{ old('lama_pinjam', 7) }}" min="1"
                                max="365" required
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200">
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5"><i class="fas fa-info-circle"></i>
                                Berlaku untuk <strong>semua alat</strong> di batch ini.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Waktu
                                Pengambilan <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="waktu_pengambilan"
                                value="{{ old('waktu_pengambilan', now()->format('Y-m-d\TH:i')) }}" required
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Keperluan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="keperluan" rows="3" required maxlength="255"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 resize-none"
                                placeholder="Jelaskan keperluan penggunaan alat...">{{ old('keperluan') }}</textarea>
                        </div>

                        <div class="sm:col-span-2" x-data="{ preview: null }">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Foto Bukti
                                (opsional)</label>
                            <input type="file" name="foto" accept="image/*"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-amber-50 dark:file:bg-amber-900/30 file:text-amber-700 dark:file:text-amber-400 hover:file:bg-amber-100">
                            <template x-if="preview"><img :src="preview" alt="Preview"
                                    class="mt-3 h-24 w-auto rounded-xl border border-gray-200 dark:border-gray-700 object-cover"></template>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5"><i class="fas fa-info-circle"></i>
                                JPG/PNG/WebP, maks 2MB.</p>
                        </div>
                    </div>
                </div>

                {{-- ═══════════════════════════════════════════════
                     SECTION 2: PILIH ALAT — UI/UX RAMAH PENGGUNA
                ═══════════════════════════════════════════════ --}}
                <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                    <div class="mb-6">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                <i class="fas fa-toolbox text-lg"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                    Pilih Alat yang Akan Dipinjam
                                </h3>
                                <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-300">
                                    Cari alat yang diperlukan, kemudian tekan tombol
                                    <strong>Tambah ke daftar</strong>. Anda dapat memilih beberapa jenis alat sekaligus.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div
                                class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                    <i class="fas fa-search"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">1. Cari alat</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Gunakan kolom pencarian</p>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                    <i class="fas fa-cart-plus"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">2. Tambahkan</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Pilih alat yang diperlukan</p>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3 dark:border-gray-700 dark:bg-gray-900">
                                <span
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                    <i class="fas fa-clipboard-check"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">3. Periksa daftar</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Atur jumlah sebelum disimpan</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-5">
                        {{-- KATALOG --}}
                        <div class="min-w-0 lg:col-span-3">
                            <div
                                class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                                <div class="border-b border-gray-200 p-4 dark:border-gray-700 sm:p-5">
                                    <label for="cari-alat"
                                        class="mb-2 block text-sm font-bold text-gray-800 dark:text-gray-100">
                                        Cari alat berdasarkan nama, <strong>no seri</strong>, atau <strong>no
                                            identitas</strong>
                                    </label>
                                    <div class="relative">
                                        <i
                                            class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                        <input id="cari-alat" type="search" x-model.debounce.200ms="search"
                                            placeholder="Contoh: multimeter, SN-12345, ID-001..." autocomplete="off"
                                            class="w-full rounded-xl border border-gray-300 bg-white py-3.5 pl-11 pr-11 text-base text-gray-900 placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-200 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900">
                                        <button type="button" x-show="search"
                                            @click="search = ''; $nextTick(() => document.getElementById('cari-alat').focus())"
                                            aria-label="Hapus pencarian"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-700">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-sm">
                                        <span class="text-gray-600 dark:text-gray-300">
                                            <i class="fas fa-boxes-stacked mr-1 text-amber-500"></i>
                                            <span x-text="resultCount"></span> alat ditemukan
                                        </span>
                                        <span
                                            class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                            Maksimal <span x-text="maxItems"></span> jenis
                                        </span>
                                    </div>
                                </div>

                                <div class="max-h-[620px] overflow-y-auto">
                                    <template x-for="folder in filteredFolders" :key="folder.nama">
                                        <section class="border-b border-gray-100 last:border-b-0 dark:border-gray-700">
                                            <button type="button" @click="toggleFolder(folder.nama)"
                                                :aria-expanded="isOpen(folder.nama) || !!search"
                                                class="flex min-h-[76px] w-full items-center gap-3 px-4 py-4 text-left transition hover:bg-amber-50/70 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-amber-500 dark:hover:bg-gray-700/60 sm:px-5">
                                                <span
                                                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                                    <i class="fas text-lg"
                                                        :class="(isOpen(folder.nama) || search) ? 'fa-folder-open' : 'fa-folder'"></i>
                                                </span>
                                                <span class="min-w-0 flex-1">
                                                    <span
                                                        class="block break-words text-sm font-bold text-gray-900 dark:text-white"
                                                        x-text="folder.display_nama"></span>
                                                    <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">
                                                        <span x-text="folder.items.length"></span> varian alat
                                                    </span>
                                                </span>
                                                <i class="fas fa-chevron-down shrink-0 text-sm text-gray-400 transition-transform"
                                                    :class="(isOpen(folder.nama) || search) ? 'rotate-180' : ''"></i>
                                            </button>

                                            <div x-show="isOpen(folder.nama) || !!search" x-transition>
                                                <div class="space-y-3 bg-gray-50 p-3 dark:bg-gray-900/50 sm:p-4">
                                                    <template x-for="a in folder.items" :key="a.hashid">
                                                        <article
                                                            class="rounded-xl border bg-white p-4 transition dark:bg-gray-800"
                                                            :class="isInCart(a.hashid) ?
                                                                'border-amber-400 ring-1 ring-amber-200 dark:border-amber-600 dark:ring-amber-900' :
                                                                'border-gray-200 dark:border-gray-700'">
                                                            <div class="flex items-start gap-3">
                                                                <span
                                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                                                    :class="a.stok > 0 ?
                                                                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-200' :
                                                                        'bg-red-50 text-red-500 dark:bg-red-900/20'">
                                                                    <i class="fas fa-toolbox"></i>
                                                                </span>

                                                                <div class="min-w-0 flex-1">
                                                                    <h4 class="break-words text-base font-bold leading-6 text-gray-900 dark:text-white"
                                                                        x-text="a.nama_alat"></h4>
                                                                    <p
                                                                        class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                                        <span
                                                                            x-text="a.merk || 'Merk tidak tersedia'"></span>
                                                                        <span x-show="a.tipe"> · <span
                                                                                x-text="a.tipe"></span></span>
                                                                    </p>

                                                                    <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                                                        <span
                                                                            class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                                            Kelas: <strong
                                                                                x-text="a.kelas || '-'"></strong>
                                                                        </span>
                                                                        <span
                                                                            class="rounded-lg bg-gray-100 px-2.5 py-1.5 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                                                            Kapasitas: <strong
                                                                                x-text="a.kapasitas || '-'"></strong>
                                                                        </span>
                                                                        <span class="rounded-lg px-2.5 py-1.5 font-bold"
                                                                            :class="a.stok > 0 ?
                                                                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' :
                                                                                'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300'">
                                                                            <span
                                                                                x-text="a.stok > 0 ? 'Stok tersedia: ' + a.stok : 'Stok habis'"></span>
                                                                        </span>
                                                                    </div>

                                                                    {{-- ✨ Badge No Identitas + No Seri ✨ --}}
                                                                    <div x-show="a.no_identitas || a.no_seri"
                                                                        class="mt-3 flex flex-wrap gap-2">
                                                                        <span x-show="a.no_identitas"
                                                                            class="inline-flex items-center gap-1.5 rounded-md bg-indigo-50 dark:bg-indigo-950 px-2 py-1 font-mono text-xs font-bold text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                                            <i class="fas fa-id-card text-[10px]"></i>
                                                                            <span x-text="a.no_identitas"></span>
                                                                        </span>
                                                                        <span x-show="a.no_seri"
                                                                            class="inline-flex items-center gap-1.5 rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-1 font-mono text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                                                            <i class="fas fa-barcode text-[10px]"></i>
                                                                            <span x-text="a.no_seri"></span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div
                                                                class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-700">
                                                                <button type="button" @click="addToCart(a)"
                                                                    :disabled="a.stok <= 0 || (isInCart(a.hashid) && cartQty(a
                                                                        .hashid) >= a.stok) || (!isInCart(a
                                                                        .hashid) && cart.length >= maxItems)"
                                                                    class="flex min-h-[46px] w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                                                                    :class="a.stok <= 0 || (isInCart(a.hashid) && cartQty(a
                                                                            .hashid) >= a.stok) || (!isInCart(a
                                                                            .hashid) && cart.length >= maxItems) ?
                                                                        'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' :
                                                                        isInCart(a.hashid) ?
                                                                        'bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-900/40 dark:text-amber-200' :
                                                                        'bg-amber-500 text-white hover:bg-amber-600'">
                                                                    <i class="fas"
                                                                        :class="a.stok <= 0 ? 'fa-ban' : (isInCart(a.hashid) &&
                                                                                cartQty(a.hashid) >= a.stok) ?
                                                                            'fa-check-circle' : 'fa-plus-circle'"></i>
                                                                    <span
                                                                        x-text="a.stok <= 0 ? 'Stok tidak tersedia' : (isInCart(a.hashid) && cartQty(a.hashid) >= a.stok) ? 'Jumlah maksimum tercapai' : isInCart(a.hashid) ? 'Tambah 1 unit lagi' : 'Tambah ke daftar pinjaman'"></span>
                                                                </button>
                                                            </div>
                                                        </article>
                                                    </template>
                                                </div>
                                            </div>
                                        </section>
                                    </template>

                                    <div x-show="filteredFolders.length === 0" x-cloak class="px-6 py-12 text-center">
                                        <span
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700">
                                            <i class="fas fa-search text-xl"></i>
                                        </span>
                                        <p class="mt-4 font-bold text-gray-800 dark:text-gray-100">Alat tidak ditemukan</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Coba gunakan kata kunci
                                            yang lebih singkat.</p>
                                        <button type="button" @click="search = ''"
                                            class="mt-4 rounded-xl px-4 py-2 text-sm font-bold text-amber-700 hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-gray-700">
                                            Tampilkan semua alat
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DAFTAR PEMINJAMAN --}}
                        <div class="min-w-0 lg:col-span-2">
                            <div
                                class="overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-sm dark:border-amber-900 dark:bg-gray-800 lg:sticky lg:top-24">
                                <div class="bg-amber-50 p-4 dark:bg-amber-900/20 sm:p-5">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500 text-white">
                                            <i class="fas fa-clipboard-list text-lg"></i>
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <h4 class="text-base font-bold text-gray-900 dark:text-white">Daftar Alat
                                                Pilihan</h4>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                <span x-text="cart.length"></span> dari <span x-text="maxItems"></span>
                                                jenis alat
                                            </p>
                                        </div>
                                        <span
                                            class="flex h-9 min-w-9 items-center justify-center rounded-full bg-amber-500 px-2 text-sm font-bold text-white"
                                            x-text="cart.length"></span>
                                    </div>
                                </div>

                                <div x-show="cart.length === 0" x-cloak class="px-5 py-10 text-center">
                                    <span
                                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-gray-700">
                                        <i class="fas fa-cart-shopping text-2xl"></i>
                                    </span>
                                    <p class="mt-4 font-bold text-gray-800 dark:text-gray-100">Belum ada alat dipilih</p>
                                    <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-gray-500 dark:text-gray-400">
                                        Pilih alat dari katalog, lalu tekan tombol "Tambah ke daftar pinjaman".
                                    </p>
                                </div>

                                <div x-show="cart.length > 0" class="space-y-3 p-3 sm:p-4">
                                    <template x-for="(item, idx) in cart" :key="item.hashid">
                                        <article class="rounded-xl border border-gray-200 p-3 sm:p-4 dark:border-gray-700">
                                            <input type="hidden" :name="`items[${idx}][alat_id]`"
                                                :value="item.hashid">
                                            <input type="hidden" :name="`items[${idx}][jumlah]`" :value="item.jumlah">

                                            <div class="flex items-start gap-3">
                                                <span
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                                    <i class="fas fa-toolbox"></i>
                                                </span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="break-words text-sm font-bold leading-5 text-gray-900 dark:text-white"
                                                        x-text="item.nama_alat"></p>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        <span x-text="item.merk || 'Merk tidak tersedia'"></span> · Satuan:
                                                        <span x-text="item.satuan"></span>
                                                    </p>

                                                    {{-- ✨ Badge No Identitas + No Seri (mini di keranjang) ✨ --}}
                                                    <div x-show="item.no_identitas || item.no_seri"
                                                        class="mt-2 flex flex-wrap gap-1.5">
                                                        <span x-show="item.no_identitas"
                                                            class="inline-flex items-center gap-1 rounded bg-indigo-50 dark:bg-indigo-950 px-1.5 py-0.5 font-mono text-[10px] font-bold text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                                                            <i class="fas fa-id-card"></i>
                                                            <span x-text="item.no_identitas"></span>
                                                        </span>
                                                        <span x-show="item.no_seri"
                                                            class="inline-flex items-center gap-1 rounded bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 font-mono text-[10px] font-semibold text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                                            <i class="fas fa-barcode"></i>
                                                            <span x-text="item.no_seri"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                                <button type="button" @click="removeFromCart(idx)"
                                                    :aria-label="'Hapus ' + item.nama_alat + ' dari daftar'"
                                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-red-600 transition hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-red-400 dark:hover:bg-red-900/30"
                                                    title="Hapus dari daftar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>

                                            <div class="mt-4 flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                        Jumlah pinjam</p>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        Stok tersedia: <strong x-text="item.stok"></strong>
                                                    </p>
                                                </div>

                                                <div
                                                    class="flex items-center rounded-xl border border-gray-300 dark:border-gray-600">
                                                    <button type="button" @click="dec(idx)" :disabled="item.jumlah <= 1"
                                                        :aria-label="'Kurangi jumlah ' + item.nama_alat"
                                                        class="h-11 w-11 rounded-l-xl text-lg font-bold text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-100 dark:hover:bg-gray-700">−</button>
                                                    <span
                                                        class="flex h-11 min-w-[48px] items-center justify-center border-x border-gray-300 px-2 text-base font-bold text-gray-900 dark:border-gray-600 dark:text-white"
                                                        x-text="item.jumlah"></span>
                                                    <button type="button" @click="inc(idx)"
                                                        :disabled="item.jumlah >= item.stok"
                                                        :aria-label="'Tambah jumlah ' + item.nama_alat"
                                                        class="h-11 w-11 rounded-r-xl text-lg font-bold text-gray-700 hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:text-gray-100 dark:hover:bg-gray-700">+</button>
                                                </div>
                                            </div>
                                        </article>
                                    </template>

                                    <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-900">
                                        <div class="flex items-center justify-between gap-3 text-sm">
                                            <span class="text-gray-600 dark:text-gray-300">Total jenis alat</span>
                                            <strong class="text-gray-900 dark:text-white"
                                                x-text="cart.length + ' jenis'"></strong>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between gap-3 text-sm">
                                            <span class="text-gray-600 dark:text-gray-300">Total seluruh unit</span>
                                            <strong class="text-lg text-amber-700 dark:text-amber-300"
                                                x-text="totalUnit + ' unit'"></strong>
                                        </div>
                                    </div>

                                    <button type="button" @click="clearCart()"
                                        class="flex min-h-[44px] w-full items-center justify-center gap-2 rounded-xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-red-900/20 dark:hover:text-red-300">
                                        <i class="fas fa-trash-alt"></i> Kosongkan daftar alat
                                    </button>
                                </div>

                                <div
                                    class="border-t border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900">
                                    <p class="text-xs leading-5 text-gray-600 dark:text-gray-300">
                                        <i class="fas fa-info-circle mr-1 text-amber-500"></i>
                                        Pastikan nama alat, nomor identitas, dan jumlahnya sudah benar sebelum menyimpan
                                        peminjaman.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Error Alpine --}}
                    <div x-show="error" x-cloak x-transition
                        class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200"
                        role="alert">
                        <i class="fas fa-exclamation-circle mt-0.5"></i>
                        <p x-text="error"></p>
                    </div>
                </div>

                {{-- ═══════ SUBMIT ═══════ --}}
                <div
                    class="flex flex-col sm:flex-row gap-3 justify-between items-center border-t border-gray-100 dark:border-gray-700 pt-6">
                    <a href="{{ route('pengambilan_alat.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3.5 border-2 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 text-gray-700 dark:text-gray-200 font-medium rounded-2xl transition-all duration-300 order-2 sm:order-1">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" :disabled="cart.length === 0"
                        class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg shadow-amber-200 dark:shadow-none hover:shadow-xl hover:-translate-y-0.5 order-1 sm:order-2">
                        <i class="fas fa-save"></i><span>Simpan Peminjaman</span>
                        <span x-show="cart.length>0" class="bg-white/20 px-2 py-0.5 rounded-full text-xs font-bold"
                            x-text="cart.length + ' alat'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function alatCart(alats, initialCart, maxItems) {
                return {
                    alats: alats || [],
                    cart: initialCart || [],
                    search: '',
                    openFolders: [],
                    maxItems: maxItems || 15,
                    error: '',
                    errorTimer: null,

                    norm(s) {
                        return (s || '')
                            .trim()
                            .replace(/\s+/g, ' ')
                            .replace(/\d+$/, '')
                            .trim();
                    },

                    modeNama(items) {
                        const freq = {};
                        let best = items[0]?.nama_alat || '';
                        let bestCount = 0;

                        for (const item of items) {
                            const name = item.nama_alat || '';
                            freq[name] = (freq[name] || 0) + 1;

                            if (freq[name] > bestCount) {
                                bestCount = freq[name];
                                best = name;
                            }
                        }

                        return best;
                    },

                    get folders() {
                        const map = new Map();

                        for (const alat of this.alats) {
                            const key = this.norm(alat.nama_alat) || 'Lainnya';

                            if (!map.has(key)) {
                                map.set(key, []);
                            }

                            map.get(key).push(alat);
                        }

                        return [...map.entries()]
                            .map(([nama, items]) => ({
                                nama,
                                display_nama: this.modeNama(items),
                                items
                            }))
                            .sort((a, b) =>
                                a.display_nama.localeCompare(b.display_nama, 'id')
                            );
                    },

                    get filteredFolders() {
                        const query = this.search.toLowerCase().trim();

                        if (!query) {
                            return this.folders;
                        }

                        return this.folders
                            .map(folder => {
                                const folderMatches =
                                    (folder.display_nama || '')
                                    .toLowerCase()
                                    .includes(query);

                                const matchingItems = folder.items.filter(item => {
                                    const fields = [
                                        item.nama_alat,
                                        item.merk,
                                        item.tipe,
                                        item.no_seri,
                                        item.no_identitas,
                                        item.kelas,
                                        item.kapasitas
                                    ];

                                    return fields.some(value =>
                                        String(value || '')
                                        .toLowerCase()
                                        .includes(query)
                                    );
                                });

                                if (folderMatches) {
                                    return folder;
                                }

                                if (matchingItems.length > 0) {
                                    return {
                                        ...folder,
                                        items: matchingItems
                                    };
                                }

                                return null;
                            })
                            .filter(Boolean);
                    },

                    get resultCount() {
                        return this.filteredFolders.reduce(
                            (total, folder) => total + folder.items.length,
                            0
                        );
                    },

                    get totalUnit() {
                        return this.cart.reduce(
                            (total, item) => total + Number(item.jumlah || 0),
                            0
                        );
                    },

                    isInCart(hashid) {
                        return this.cart.some(item => item.hashid === hashid);
                    },

                    cartQty(hashid) {
                        const item = this.cart.find(
                            row => row.hashid === hashid
                        );

                        return item ? Number(item.jumlah) : 0;
                    },

                    indexOf(hashid) {
                        return this.cart.findIndex(
                            item => item.hashid === hashid
                        );
                    },

                    addToCart(alat) {
                        this.clearError();

                        if (Number(alat.stok) <= 0) {
                            this.showError('Stok alat ini sedang tidak tersedia.');
                            return;
                        }

                        const index = this.indexOf(alat.hashid);

                        if (index >= 0) {
                            const item = this.cart[index];

                            if (Number(item.jumlah) >= Number(item.stok)) {
                                this.showError(
                                    `Jumlah "${item.nama_alat}" sudah mencapai stok maksimum.`
                                );
                                return;
                            }

                            item.jumlah = Number(item.jumlah) + 1;
                            return;
                        }

                        if (this.cart.length >= this.maxItems) {
                            this.showError(
                                `Anda dapat memilih maksimal ${this.maxItems} jenis alat dalam satu peminjaman.`
                            );
                            return;
                        }

                        this.cart.push({
                            hashid: alat.hashid,
                            nama_alat: alat.nama_alat,
                            merk: alat.merk || '',
                            no_seri: alat.no_seri || null,
                            no_identitas: alat.no_identitas || null,
                            satuan: alat.satuan || 'unit',
                            stok: Number(alat.stok),
                            jumlah: 1
                        });
                    },

                    removeFromCart(index) {
                        this.cart.splice(index, 1);
                        this.clearError();
                    },

                    inc(index) {
                        const item = this.cart[index];

                        if (!item) return;

                        if (Number(item.jumlah) < Number(item.stok)) {
                            item.jumlah = Number(item.jumlah) + 1;
                            this.clearError();
                        } else {
                            this.showError(
                                `Jumlah "${item.nama_alat}" tidak boleh melebihi stok tersedia.`
                            );
                        }
                    },

                    dec(index) {
                        const item = this.cart[index];

                        if (item && Number(item.jumlah) > 1) {
                            item.jumlah = Number(item.jumlah) - 1;
                            this.clearError();
                        }
                    },

                    clearCart() {
                        if (this.cart.length === 0) return;

                        if (confirm('Apakah Anda yakin ingin mengosongkan semua alat pilihan?')) {
                            this.cart = [];
                            this.clearError();
                        }
                    },

                    toggleFolder(name) {
                        const index = this.openFolders.indexOf(name);

                        if (index >= 0) {
                            this.openFolders.splice(index, 1);
                        } else {
                            this.openFolders.push(name);
                        }
                    },

                    isOpen(name) {
                        return this.openFolders.includes(name);
                    },

                    guardSubmit(event) {
                        this.clearError();

                        if (this.cart.length === 0) {
                            event.preventDefault();
                            this.showError(
                                'Daftar alat masih kosong. Pilih minimal satu alat terlebih dahulu.'
                            );
                            return;
                        }

                        if (this.cart.length > this.maxItems) {
                            event.preventDefault();
                            this.showError(
                                `Maksimal ${this.maxItems} jenis alat dalam satu peminjaman.`
                            );
                            return;
                        }

                        for (const item of this.cart) {
                            if (
                                !Number.isInteger(Number(item.jumlah)) ||
                                Number(item.jumlah) < 1 ||
                                Number(item.jumlah) > Number(item.stok)
                            ) {
                                event.preventDefault();
                                this.showError(
                                    `Periksa kembali jumlah alat "${item.nama_alat}".`
                                );
                                return;
                            }
                        }
                    },

                    showError(message) {
                        this.error = message;

                        if (this.errorTimer) {
                            clearTimeout(this.errorTimer);
                        }

                        this.errorTimer = setTimeout(() => {
                            if (this.error === message) {
                                this.error = '';
                            }
                        }, 5000);
                    },

                    clearError() {
                        this.error = '';

                        if (this.errorTimer) {
                            clearTimeout(this.errorTimer);
                            this.errorTimer = null;
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
