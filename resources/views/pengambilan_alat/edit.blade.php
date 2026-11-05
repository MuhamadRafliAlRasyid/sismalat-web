@extends('layouts.app')

@section('title', 'Edit Pengambilan Alat')

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

        $itemsAll = $data->items;
        $totalItems = $itemsAll->count();
        $returned = $itemsAll->where('status', 'dikembalikan')->count();
        $progress = $totalItems > 0 ? round(($returned / $totalItems) * 100) : 0;

        // Mode B: ada item yang sudah dikembalikan → kunci daftar alat
        $hasReturned = $returned > 0;
        $isSelesai = $data->status === 'dikembalikan';

        // Katalog datar → JSON (hanya dipakai MODE A)
        $alatsJson = $alats
            ->map(
                fn($a) => [
                    'hashid' => $a->hashid,
                    'nama_alat' => $a->nama_alat,
                    'merk' => $a->merk,
                    'tipe' => $a->tipe,
                    'no_seri' => $a->no_seri,
                    'satuan' => $a->satuan ?: 'unit',
                    'kelas' => $a->kelas,
                    'kapasitas' => $a->kapasitas,
                    'stok' => (int) $a->jumlah,
                ],
            )
            ->values();

        // Keranjang awal MODE A: old('items') saat validasi gagal, selain itu dari DB (item dipinjam)
        $initialCart = collect();
        if (!$hasReturned) {
            $rows = old('items');
            if ($rows === null) {
                $rows = $itemsAll
                    ->where('status', 'dipinjam')
                    ->map(fn($i) => ['alat_id' => $i->alat->hashid ?? null, 'jumlah' => $i->jumlah])
                    ->filter(fn($r) => $r['alat_id'])
                    ->values()
                    ->all();
            }
            $initialCart = collect($rows)
                ->map(function ($row) use ($alats) {
                    $a = $alats->firstWhere('hashid', $row['alat_id'] ?? null);
                    if (!$a) {
                        return null;
                    }
                    return [
                        'hashid' => $a->hashid,
                        'nama_alat' => $a->nama_alat,
                        'merk' => $a->merk,
                        'satuan' => $a->satuan ?: 'unit',
                        'stok' => (int) $a->jumlah,
                        'jumlah' => max(1, (int) ($row['jumlah'] ?? 1)),
                    ];
                })
                ->filter()
                ->values();
        }
    @endphp

    <div class="max-w-6xl mx-auto px-4 py-8">
        {{-- Back --}}
        <a href="{{ route('pengambilan_alat.show', $data->hashid) }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali ke Detail</span>
        </a>

        {{-- ═══════ BANNER STATUS BATCH ═══════ --}}
        @php
            $statusColor = match (true) {
                $isSelesai => 'emerald',
                $data->should_warn_1day && $data->isAktif() => 'red',
                $data->should_warn_15 && $data->isAktif() => 'orange',
                $data->status_pinjam === 'terlambat' => 'red',
                $hasReturned => 'blue',
                default => 'amber',
            };
        @endphp
        <div
            class="bg-{{ $statusColor }}-50 dark:bg-{{ $statusColor }}-900/20 border border-{{ $statusColor }}-200 dark:border-{{ $statusColor }}-800 rounded-2xl p-5 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span
                        class="w-11 h-11 rounded-2xl bg-{{ $statusColor }}-100 dark:bg-{{ $statusColor }}-900/40 text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400 flex items-center justify-center text-lg">
                        <i
                            class="fas {{ $isSelesai ? 'fa-check-circle' : ($hasReturned ? 'fa-undo-alt' : 'fa-hand-holding') }}"></i>
                    </span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Batch
                            #{{ strtoupper(substr($data->hashid, 0, 6)) }} •
                            {{ $data->nama_peminjam ?? ($data->user->name ?? '-') }}</p>
                        <h2 class="font-bold text-gray-800 dark:text-gray-100">{{ $data->status_label }}</h2>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    @if ($data->isAktif())
                        <div class="text-right">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Jatuh Tempo</p>
                            <p class="font-bold text-gray-800 dark:text-gray-100">
                                {{ $data->tanggal_jatuh_tempo?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold
                            {{ $data->status_pinjam === 'terlambat' ? 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-300 animate-pulse' : 'bg-white/70 dark:bg-gray-800 text-gray-700 dark:text-gray-200' }}">
                            <i class="fas fa-clock"></i>{{ $data->sisa_hari_label }}
                        </span>
                    @endif
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pengembalian</p>
                        <p class="font-bold text-gray-800 dark:text-gray-100">{{ $returned }}/{{ $totalItems }} alat
                        </p>
                    </div>
                </div>
            </div>
            @if ($hasReturned && !$isSelesai)
                <div class="mt-4">
                    <div class="w-full h-1.5 bg-white/60 dark:bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-blue-400 to-indigo-500 rounded-full"
                            style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ═══════ ALERT TERKUNCI (MODE B) ═══════ --}}
        @if ($hasReturned)
            <div
                class="mb-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 px-5 py-4 rounded-2xl flex items-start gap-3">
                <i class="fas fa-lock mt-0.5"></i>
                <div class="text-sm">
                    <p class="font-bold">Daftar alat terkunci</p>
                    <p class="mt-0.5">Batch ini sudah memiliki alat yang dikembalikan, sehingga <strong>daftar alat tidak
                            dapat diubah</strong> untuk menjaga integritas stok & riwayat. Anda tetap dapat memperbarui data
                        administrasi (keperluan, jadwal, foto). Untuk mengembalikan alat lain, gunakan menu
                        <em>Pengembalian</em>.</p>
                </div>
            </div>
        @endif

        {{-- Error global --}}
        @if ($errors->any())
            <div
                class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-5 py-4 rounded-2xl text-sm">
                <p class="font-bold mb-1"><i class="fas fa-triangle-exclamation mr-1"></i>Periksa kembali:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ═══════ FORM ═══════ --}}
        <div @if (!$hasReturned) x-data="alatCartEdit({{ \Illuminate\Support\Js::from($alatsJson) }}, {{ \Illuminate\Support\Js::from($initialCart) }}, {{ $MAX }})" @endif
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">

            <div
                class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 p-6 sm:p-8 border-b border-amber-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <span
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-amber-100 dark:shadow-none">
                        <i class="fas fa-pen-to-square text-xl text-amber-500"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Peminjaman</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
                            {{ $hasReturned ? 'Hanya data administrasi yang dapat diubah' : 'Ubah jadwal, keperluan, dan daftar alat' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Error Alpine (MODE A) --}}
            @if (!$hasReturned)
                <div x-show="error" x-cloak x-transition
                    class="mx-6 sm:mx-8 mt-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-2xl text-sm flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i><span x-text="error"></span>
                </div>
            @endif

            <form method="POST" action="{{ route('pengambilan_alat.update', $data->hashid) }}"
                enctype="multipart/form-data" @if (!$hasReturned) x-on:submit="guardSubmit($event)" @endif
                class="p-6 sm:p-8 space-y-8">
                @csrf @method('PUT')

                {{-- ═══ SECTION 1: DATA & JADWAL ═══ --}}
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
                                            <option value="{{ $u->id }}" @selected(old('user_id', $data->user_id) == $u->id)>
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
                                <input type="text" name="nama_peminjam"
                                    value="{{ old('nama_peminjam', $data->nama_peminjam) }}"
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
                                            <option value="{{ $b->id }}" @selected(old('bagian_id', $data->bagian_id) == $b->id)>
                                                {{ $b->nama }}</option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"><i
                                            class="fas fa-chevron-down text-gray-400"></i></div>
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="user_id" value="{{ $data->user_id }}">
                            <input type="hidden" name="bagian_id" value="{{ $data->bagian_id }}">
                            <div
                                class="sm:col-span-2 flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm text-gray-700 dark:text-gray-200">
                                <i class="fas fa-user text-amber-500"></i>{{ $data->user->name ?? $me->name }}
                                @if ($data->bagian)
                                    <span class="text-gray-400">•</span><i
                                        class="fas fa-building text-amber-500"></i>{{ $data->bagian->nama }}
                                @endif
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Lama Pinjam
                                (Hari) <span class="text-red-500">*</span></label>
                            <input type="number" name="lama_pinjam" value="{{ old('lama_pinjam', $data->lama_pinjam) }}"
                                min="1" max="365" required
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200">
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5"><i class="fas fa-info-circle"></i>
                                Mengubah lama pinjam akan menggeser tanggal jatuh tempo seluruh batch.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Waktu
                                Pengambilan <span class="text-red-500">*</span></label>
                            <input type="datetime-local" name="waktu_pengambilan"
                                value="{{ old('waktu_pengambilan', $data->waktu_pengambilan?->format('Y-m-d\TH:i')) }}"
                                required
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Keperluan
                                <span class="text-red-500">*</span></label>
                            <textarea name="keperluan" rows="3" required maxlength="255"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 resize-none"
                                placeholder="Jelaskan keperluan penggunaan alat...">{{ old('keperluan', $data->keperluan) }}</textarea>
                        </div>

                        {{-- Foto --}}
                        <div class="sm:col-span-2" x-data="{ preview: null }">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Foto
                                Bukti</label>
                            @if ($data->foto)
                                <div class="flex items-center gap-3 mb-3">
                                    <img src="{{ $data->foto_url }}" alt="Foto saat ini"
                                        class="h-20 w-20 rounded-xl object-cover border border-gray-200 dark:border-gray-700">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <p class="font-medium text-gray-700 dark:text-gray-200">Foto saat ini</p>
                                        <p>Biarkan kosong di bawah untuk mempertahankan foto ini.</p>
                                    </div>
                                </div>
                            @endif
                            <input type="file" name="foto" accept="image/*"
                                @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-amber-50 dark:file:bg-amber-900/30 file:text-amber-700 dark:file:text-amber-400 hover:file:bg-amber-100">
                            <template x-if="preview"><img :src="preview" alt="Preview baru"
                                    class="mt-3 h-20 w-auto rounded-xl border border-amber-300 object-cover"></template>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1.5"><i class="fas fa-info-circle"></i>
                                Mengunggah foto baru akan menggantikan foto lama. JPG/PNG/WebP, maks 2MB.</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                {{-- ═══ SECTION 2: ALAT ═══ --}}
                <div>
                    <h3
                        class="text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400 mb-4 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-xs font-extrabold">2</span>
                        Alat dalam Batch
                        @if (!$hasReturned)
                            <span class="ml-auto normal-case font-medium text-xs text-gray-400"><span
                                    x-text="cart.length"></span>/<span x-text="maxItems"></span> jenis</span>
                        @else
                            <span class="ml-auto normal-case font-medium text-xs text-gray-400">{{ $totalItems }} alat
                                (terkunci)</span>
                        @endif
                    </h3>

                    @if ($hasReturned)
                        {{-- ── MODE B: READ-ONLY ── --}}
                        <div class="space-y-2">
                            @foreach ($itemsAll as $it)
                                @php $kembali = $it->status === 'dikembalikan'; @endphp
                                <div
                                    class="flex items-center gap-3 p-4 rounded-2xl border
                                    {{ $kembali ? 'bg-emerald-50/50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800' : 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700' }}">
                                    <span
                                        class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0
                                        {{ $kembali ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400' }}">
                                        <i class="fas {{ $kembali ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate">
                                            {{ $it->alat->nama_alat ?? 'Alat terhapus' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            {{ $it->alat->merk ?? '-' }} • Seri: {{ $it->alat->no_seri ?? '-' }}
                                        </p>
                                    </div>
                                    <span
                                        class="text-sm font-bold text-gray-700 dark:text-gray-200 flex-shrink-0">×{{ $it->jumlah }}
                                        <span class="text-xs font-normal text-gray-400">{{ $it->satuan }}</span></span>
                                    <span
                                        class="text-[10px] font-bold px-2 py-1 rounded-full flex-shrink-0
                                        {{ $kembali ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' : 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' }}">
                                        {{ $it->status_label }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- ── MODE A: KATALOG FOLDER + KERANJANG (EDITABLE) ── --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                            {{-- Katalog --}}
                            <div class="order-2 lg:order-1">
                                <div class="relative mb-3">
                                    <input type="text" x-model.debounce.200ms="search"
                                        placeholder="Cari nama, merk, tipe, no seri, kelas, kapasitas..."
                                        class="w-full pl-10 pr-10 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-400 text-gray-700 dark:text-gray-200 text-sm">
                                    <i
                                        class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    <button type="button" x-show="search" @click="search=''"
                                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition"><i
                                            class="fas fa-times-circle"></i></button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 px-1">
                                    <span x-show="!search"
                                        x-text="alats.length + ' alat dalam ' + folders.length + ' kategori'"></span>
                                    <span x-show="search" x-text="resultCount + ' hasil ditemukan'"></span>
                                </p>
                                <div
                                    class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden bg-white dark:bg-gray-900">
                                    <div class="max-h-[440px] overflow-y-auto">
                                        <template x-for="folder in filteredFolders" :key="folder.nama">
                                            <div class="border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                                                <div @click="toggleFolder(folder.nama)"
                                                    class="flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors select-none">
                                                    <i class="fas text-amber-500 text-lg"
                                                        :class="(isOpen(folder.nama) || search) ? 'fa-folder-open' : 'fa-folder'"></i>
                                                    <span
                                                        class="font-semibold text-gray-800 dark:text-gray-200 flex-1 text-sm"
                                                        x-text="folder.display_nama"></span>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full"
                                                        x-text="folder.items.length + ' varian'"></span>
                                                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200"
                                                        :class="(isOpen(folder.nama) || search) ? 'rotate-180' : ''"></i>
                                                </div>
                                                <div x-show="isOpen(folder.nama) || search" x-transition
                                                    class="bg-gray-50/50 dark:bg-gray-800/30 pb-1">
                                                    <template x-for="a in folder.items" :key="a.hashid">
                                                        <div class="flex items-center gap-3 px-4 py-2.5 mx-2 my-0.5 rounded-r-lg border-l-2 transition-all"
                                                            :class="isInCart(a.hashid) ?
                                                                'bg-amber-50 dark:bg-amber-900/20 border-amber-500' :
                                                                'border-transparent hover:bg-gray-100 dark:hover:bg-gray-700/50'">
                                                            <div class="flex-1 min-w-0">
                                                                <div class="flex items-center gap-2 flex-wrap">
                                                                    <span
                                                                        class="text-sm font-medium text-gray-800 dark:text-gray-200"
                                                                        x-text="a.merk || '-'"></span>
                                                                    <span class="text-xs text-gray-400"
                                                                        x-show="a.tipe && a.tipe !== '-'"
                                                                        x-text="'• ' + a.tipe"></span>
                                                                    <span class="text-xs text-gray-400"
                                                                        x-text="'Seri: ' + (a.no_seri || '-')"></span>
                                                                </div>
                                                                <div
                                                                    class="flex items-center gap-3 mt-0.5 text-xs flex-wrap">
                                                                    <span class="text-gray-500 dark:text-gray-400">Kelas:
                                                                        <span
                                                                            class="font-semibold text-gray-700 dark:text-gray-300"
                                                                            x-text="a.kelas || '-'"></span></span>
                                                                    <span
                                                                        class="text-gray-500 dark:text-gray-400">Kapasitas:
                                                                        <span
                                                                            class="font-semibold text-amber-600 dark:text-amber-400"
                                                                            x-text="a.kapasitas || '-'"></span></span>
                                                                    <span class="text-gray-500 dark:text-gray-400">Stok:
                                                                        <span class="font-semibold"
                                                                            :class="a.stok > 0 ?
                                                                                'text-emerald-600 dark:text-emerald-400' :
                                                                                'text-red-500'"
                                                                            x-text="a.stok"></span></span>
                                                                </div>
                                                            </div>
                                                            <span x-show="isInCart(a.hashid)"
                                                                x-text="'×' + cartQty(a.hashid)"
                                                                class="flex-shrink-0 text-[10px] font-bold bg-amber-500 text-white px-1.5 py-0.5 rounded-full"></span>
                                                            <button type="button" @click="addToCart(a)"
                                                                :disabled="a.stok <= 0 || (isInCart(a.hashid) && cartQty(a
                                                                    .hashid) >= a.stok) || (!isInCart(a.hashid) &&
                                                                    cart.length >= maxItems)"
                                                                class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all"
                                                                :class="isInCart(a.hashid) ?
                                                                    'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300' :
                                                                    (a.stok <= 0 || cart.length >= maxItems) ?
                                                                    'bg-gray-100 dark:bg-gray-700 text-gray-400 cursor-not-allowed' :
                                                                    'bg-amber-500 hover:bg-amber-600 text-white shadow-sm'">
                                                                <i class="fas fa-plus"></i><span>Tambah</span>
                                                            </button>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="filteredFolders.length===0" class="p-8 text-center text-gray-400">
                                            <i class="fas fa-search text-3xl mb-2 block"></i>
                                            <p class="text-sm">Tidak ada alat yang ditemukan</p>
                                            <button type="button" x-show="search" @click="search=''"
                                                class="mt-2 text-amber-600 hover:text-amber-700 text-sm font-medium">Clear
                                                search</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Keranjang --}}
                            <div class="order-1 lg:order-2">
                                <div
                                    class="sticky top-24 border border-amber-200 dark:border-amber-800 rounded-2xl overflow-hidden bg-gradient-to-br from-amber-50/50 to-orange-50/50 dark:from-gray-900 dark:to-gray-900">
                                    <div
                                        class="px-4 py-3 bg-amber-100/60 dark:bg-amber-900/20 border-b border-amber-200 dark:border-amber-800 flex items-center justify-between">
                                        <span
                                            class="text-sm font-bold text-amber-800 dark:text-amber-300 flex items-center gap-2"><i
                                                class="fas fa-shopping-basket"></i> Keranjang Peminjaman</span>
                                        <span class="text-xs font-bold bg-amber-500 text-white px-2 py-0.5 rounded-full"
                                            x-text="cart.length"></span>
                                    </div>
                                    <div x-show="cart.length===0" class="p-8 text-center text-gray-400">
                                        <i class="fas fa-basket-shopping text-4xl mb-3 block opacity-40"></i>
                                        <p class="text-sm font-medium">Keranjang kosong</p>
                                        <p class="text-xs mt-1">Tambahkan alat dari katalog. Mengosongkan keranjang akan
                                            menghapus seluruh item batch ini.</p>
                                    </div>
                                    <div x-show="cart.length>0"
                                        class="divide-y divide-amber-100 dark:divide-gray-700 max-h-[380px] overflow-y-auto">
                                        <template x-for="(item, idx) in cart" :key="item.hashid">
                                            <div class="p-4">
                                                <input type="hidden" :name="`items[${idx}][alat_id]`"
                                                    :value="item.hashid">
                                                <input type="hidden" :name="`items[${idx}][jumlah]`"
                                                    :value="item.jumlah">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate"
                                                            x-text="item.nama_alat"></p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                                            x-text="(item.merk || '-') + ' • ' + item.satuan"></p>
                                                    </div>
                                                    <button type="button" @click="removeFromCart(idx)"
                                                        class="flex-shrink-0 w-7 h-7 rounded-lg text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center justify-center transition"
                                                        title="Hapus"><i class="fas fa-trash text-xs"></i></button>
                                                </div>
                                                <div class="mt-3 flex items-center gap-3">
                                                    <div
                                                        class="inline-flex items-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                                                        <button type="button" @click="dec(idx)"
                                                            :disabled="item.jumlah <= 1"
                                                            class="w-9 h-9 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition"><i
                                                                class="fas fa-minus text-xs"></i></button>
                                                        <input type="number" :value="item.jumlah"
                                                            @input="setQty(idx, $event.target.value)" min="1"
                                                            :max="item.stok"
                                                            class="w-12 h-9 text-center text-sm font-bold text-gray-800 dark:text-gray-100 bg-transparent border-x border-gray-200 dark:border-gray-700 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                                        <button type="button" @click="inc(idx)"
                                                            :disabled="item.jumlah >= item.stok"
                                                            class="w-9 h-9 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-40 disabled:cursor-not-allowed transition"><i
                                                                class="fas fa-plus text-xs"></i></button>
                                                    </div>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">dari stok
                                                        <strong x-text="item.stok"></strong></span>
                                                    <span x-show="item.jumlah>=item.stok"
                                                        class="ml-auto text-[10px] font-bold text-red-500 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">Maks
                                                        tercapai</span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <div x-show="cart.length>0"
                                        class="px-4 py-3 bg-white/60 dark:bg-gray-800/60 border-t border-amber-200 dark:border-gray-700 flex items-center justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-300"><strong
                                                x-text="cart.length"></strong> jenis • <strong
                                                x-text="totalUnit"></strong> unit</span>
                                        <button type="button" @click="clearCart()"
                                            class="text-xs font-semibold text-red-500 hover:text-red-700 transition"><i
                                                class="fas fa-times-circle mr-1"></i>Kosongkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p x-show="cart.length===0"
                            class="text-xs text-amber-600 dark:text-amber-400 flex items-center gap-1 mt-3"><i
                                class="fas fa-info-circle"></i> Mengosongkan keranjang akan menghapus semua alat dari batch
                            ini</p>
                    @endif
                </div>

                {{-- ═══ SUBMIT ═══ --}}
                <div
                    class="flex flex-col sm:flex-row gap-3 justify-between items-center border-t border-gray-100 dark:border-gray-700 pt-6">
                    <a href="{{ route('pengambilan_alat.show', $data->hashid) }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3.5 border-2 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 text-gray-700 dark:text-gray-200 font-medium rounded-2xl transition-all duration-300 order-2 sm:order-1">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit" @if (!$hasReturned) :disabled="cart.length===0" @endif
                        class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg shadow-amber-200 dark:shadow-none hover:shadow-xl hover:-translate-y-0.5 order-1 sm:order-2">
                        <i class="fas fa-floppy-disk"></i><span>Perbarui Peminjaman</span>
                        @if (!$hasReturned)
                            <span x-show="cart.length>0" class="bg-white/20 px-2 py-0.5 rounded-full text-xs font-bold"
                                x-text="cart.length + ' alat'"></span>
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if (!$hasReturned)
        @push('scripts')
            <script>
                function alatCartEdit(alats, initialCart, maxItems) {
                    return {
                        alats: alats,
                        cart: initialCart,
                        search: '',
                        openFolders: [],
                        maxItems: maxItems || 15,
                        error: '',

                        norm(s) {
                            return (s || '').trim().replace(/\s+/g, ' ').replace(/\d+$/, '').trim();
                        },
                        modeNama(items) {
                            const freq = {};
                            let best = items[0]?.nama_alat || '',
                                bc = 0;
                            for (const it of items) {
                                const n = it.nama_alat;
                                freq[n] = (freq[n] || 0) + 1;
                                if (freq[n] > bc) {
                                    bc = freq[n];
                                    best = n;
                                }
                            }
                            return best;
                        },
                        get folders() {
                            const map = new Map();
                            for (const a of this.alats) {
                                const k = this.norm(a.nama_alat) || 'Lainnya';
                                if (!map.has(k)) map.set(k, []);
                                map.get(k).push(a);
                            }
                            return [...map.entries()].map(([nama, items]) => ({
                                nama,
                                display_nama: this.modeNama(items),
                                items
                            })).sort((x, y) => x.display_nama.localeCompare(y.display_nama));
                        },
                        get filteredFolders() {
                            if (!this.search) return this.folders;
                            const q = this.search.toLowerCase().trim();
                            return this.folders.map(folder => {
                                const mf = (folder.display_nama || '').toLowerCase().includes(q);
                                const items = folder.items.filter(it => [it.nama_alat, it.merk, it.tipe, it.no_seri, it
                                    .kelas, it.kapasitas
                                ].some(v => v && v.toString().toLowerCase().includes(q)));
                                if (mf) return folder;
                                if (items.length) return {
                                    ...folder,
                                    items
                                };
                                return null;
                            }).filter(Boolean);
                        },
                        get resultCount() {
                            return this.filteredFolders.reduce((s, f) => s + f.items.length, 0);
                        },
                        get totalUnit() {
                            return this.cart.reduce((s, i) => s + Number(i.jumlah || 0), 0);
                        },

                        isInCart(h) {
                            return this.cart.some(i => i.hashid === h);
                        },
                        cartQty(h) {
                            const i = this.cart.find(x => x.hashid === h);
                            return i ? i.jumlah : 0;
                        },
                        addToCart(a) {
                            this.error = '';
                            if (a.stok <= 0) return;
                            const idx = this.cart.findIndex(i => i.hashid === a.hashid);
                            if (idx >= 0) {
                                if (this.cart[idx].jumlah < a.stok) this.cart[idx].jumlah++;
                                return;
                            }
                            if (this.cart.length >= this.maxItems) {
                                this.showError(`Maksimal ${this.maxItems} jenis alat per peminjaman.`);
                                return;
                            }
                            this.cart.push({
                                hashid: a.hashid,
                                nama_alat: a.nama_alat,
                                merk: a.merk,
                                satuan: a.satuan,
                                stok: a.stok,
                                jumlah: 1
                            });
                        },
                        removeFromCart(idx) {
                            this.cart.splice(idx, 1);
                        },
                        inc(idx) {
                            const it = this.cart[idx];
                            if (it.jumlah < it.stok) it.jumlah++;
                        },
                        dec(idx) {
                            const it = this.cart[idx];
                            if (it.jumlah > 1) it.jumlah--;
                        },
                        setQty(idx, val) {
                            const it = this.cart[idx];
                            let n = parseInt(val, 10);
                            if (isNaN(n) || n < 1) n = 1;
                            if (n > it.stok) {
                                n = it.stok;
                                this.showError(`Jumlah "${it.nama_alat}" tidak boleh melebihi stok (${it.stok}).`);
                            }
                            it.jumlah = n;
                        },
                        clearCart() {
                            if (confirm('Kosongkan seluruh keranjang? Ini akan menghapus semua alat dari batch.')) this.cart = [];
                        },
                        toggleFolder(name) {
                            const i = this.openFolders.indexOf(name);
                            i >= 0 ? this.openFolders.splice(i, 1) : this.openFolders.push(name);
                        },
                        isOpen(name) {
                            return this.openFolders.includes(name);
                        },
                        guardSubmit(e) {
                            this.error = '';
                            if (this.cart.length === 0) {
                                e.preventDefault();
                                this.showError('Keranjang kosong — simpan tidak akan mengubah daftar alat.');
                                return;
                            }
                            for (const it of this.cart) {
                                if (it.jumlah > it.stok) it.jumlah = it.stok;
                                if (it.jumlah < 1) it.jumlah = 1;
                            }
                        },
                        showError(msg) {
                            this.error = msg;
                            setTimeout(() => {
                                if (this.error === msg) this.error = '';
                            }, 4000);
                        }
                    };
                }
            </script>
        @endpush
    @endif
@endsection
