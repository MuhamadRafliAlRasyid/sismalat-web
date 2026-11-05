@extends('layouts.app')

@section('title', 'Riwayat Alat: ' . $alat->nama_alat)

@section('content')
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8 lg:py-8" x-data="{
        tab: 'pengambilan',
        modalOpen: false,
        modalData: {},
        modalType: '',
        openModal(type, data) {
            this.modalType = type;
            this.modalData = data || {};
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeModal() {
            this.modalOpen = false;
            this.modalData = {};
            this.modalType = '';
            document.body.classList.remove('overflow-hidden');
        },
        formatDate(value, withTime = true) {
            if (!value) return '-';
            let date;
            // Parse date-only values locally to avoid timezone shifting by one day.
            if (/^\d{4}-\d{2}-\d{2}$/.test(String(value))) {
                const [year, month, day] = String(value).split('-').map(Number);
                date = new Date(year, month - 1, day);
            } else {
                const normalized = String(value).includes(' ') && !String(value).includes('T') ?
                    String(value).replace(' ', 'T') :
                    String(value);
                date = new Date(normalized);
            }
            if (Number.isNaN(date.getTime())) return '-';
            return new Intl.DateTimeFormat('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                ...(withTime ? { hour: '2-digit', minute: '2-digit' } : {})
            }).format(date);
        }
    }"
        @keydown.escape.window="closeModal()">
        {{-- Breadcrumb and heading --}}
        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <nav class="mb-3 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400"
                    aria-label="Breadcrumb">
                    <a href="{{ route('alats.index') }}"
                        class="transition hover:text-amber-600 dark:hover:text-amber-400">Data Alat</a>
                    <i class="fas fa-chevron-right text-[10px]" aria-hidden="true"></i>
                    <a href="{{ route('alats.show', $alat->hashid) }}"
                        class="transition hover:text-amber-600 dark:hover:text-amber-400">Detail Alat</a>
                    <i class="fas fa-chevron-right text-[10px]" aria-hidden="true"></i>
                    <span class="font-semibold text-gray-700 dark:text-gray-200">Riwayat</span>
                </nav>
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-amber-600 dark:text-amber-400">Sismalat ·
                    Riwayat aktivitas</p>
                <h1
                    class="mt-1 break-words text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                    {{ $alat->nama_alat }}
                </h1>
                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                    Riwayat pengambilan, pengembalian, dan kalibrasi alat dalam satu halaman.
                </p>
            </div>
            <a href="{{ route('alats.show', $alat->hashid) }}"
                class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800 focus:outline-none focus:ring-4 focus:ring-amber-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-amber-700 dark:hover:bg-gray-700 dark:focus:ring-amber-900/40">
                <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke Detail Alat
            </a>
        </header>

        {{-- Tool summary --}}
        <section
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-5 p-5 sm:grid-cols-[112px_minmax(0,1fr)] sm:items-center sm:p-6">
                <div
                    class="relative mx-auto flex h-28 w-28 items-center justify-center overflow-hidden rounded-2xl bg-amber-50 text-amber-600 ring-1 ring-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900 sm:mx-0">
                    <i class="fas fa-toolbox text-4xl" aria-hidden="true"></i>
                    @if ($alat->foto)
                        <img src="{{ $alat->foto_url }}" alt="Foto {{ $alat->nama_alat }}" loading="lazy"
                            class="absolute inset-0 h-full w-full object-cover" onerror="this.style.display='none'">
                    @endif
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($alat->kategori?->nama)
                            <span
                                class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/50 dark:text-amber-300">
                                <i class="fas fa-layer-group mr-1.5" aria-hidden="true"></i>{{ $alat->kategori->nama }}
                            </span>
                        @endif
                        @if ($alat->ruangan)
                            <span
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                <i class="fas fa-map-marker-alt mr-1.5"
                                    aria-hidden="true"></i>{{ $alat->ruangan->kode_ruangan }}
                                {{ $alat->ruangan->nama_ruangan }}
                            </span>
                        @endif
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-x-5 gap-y-4 sm:grid-cols-4">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Merk</p>
                            <p class="mt-1 break-words text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $alat->merk ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe</p>
                            <p class="mt-1 break-words text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $alat->tipe ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">No. Seri
                            </p>
                            <p class="mt-1 break-words font-mono text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $alat->no_seri ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">Stok</p>
                            <p class="mt-1 text-sm font-bold text-amber-700 dark:text-amber-300">{{ $alat->jumlah ?? 0 }}
                                {{ $alat->satuan ?? 'unit' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="grid grid-cols-1 border-t border-gray-100 bg-gray-50/80 dark:border-gray-700 dark:bg-gray-900/40 sm:grid-cols-3">
                <div class="flex items-center gap-3 px-5 py-4 sm:px-6">
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300"><i
                            class="fas fa-hand-holding" aria-hidden="true"></i></span>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total pengambilan</p>
                        <p class="text-xl font-extrabold tabular-nums text-gray-900 dark:text-white">
                            {{ $pengambilan->count() }}</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-700 sm:border-l sm:border-t-0 sm:px-6">
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300"><i
                            class="fas fa-undo-alt" aria-hidden="true"></i></span>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total pengembalian</p>
                        <p class="text-xl font-extrabold tabular-nums text-gray-900 dark:text-white">
                            {{ $pengembalian->count() }}</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 border-t border-gray-200 px-5 py-4 dark:border-gray-700 sm:border-l sm:px-6">
                    <span
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300"><i
                            class="fas fa-microscope" aria-hidden="true"></i></span>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total kalibrasi</p>
                        <p class="text-xl font-extrabold tabular-nums text-gray-900 dark:text-white">
                            {{ $kalibrasis->count() }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Tabs --}}
        <section
            class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-200 px-3 dark:border-gray-700 sm:px-6">
                <div class="flex gap-2 overflow-x-auto" role="tablist" aria-label="Jenis riwayat alat">
                    <button type="button" role="tab" id="tab-pengambilan" :aria-selected="tab === 'pengambilan'"
                        aria-controls="panel-pengambilan" @click="tab = 'pengambilan'"
                        :class="tab === 'pengambilan' ? 'border-amber-500 text-amber-700 dark:text-amber-300' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="inline-flex min-h-14 shrink-0 items-center gap-2 border-b-2 px-3 text-sm font-bold transition sm:px-4">
                        <i class="fas fa-hand-holding" aria-hidden="true"></i> Pengambilan <span
                            class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800 dark:bg-amber-900/60 dark:text-amber-200">{{ $pengambilan->count() }}</span>
                    </button>
                    <button type="button" role="tab" id="tab-pengembalian" :aria-selected="tab === 'pengembalian'"
                        aria-controls="panel-pengembalian" @click="tab = 'pengembalian'"
                        :class="tab === 'pengembalian' ? 'border-emerald-500 text-emerald-700 dark:text-emerald-300' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="inline-flex min-h-14 shrink-0 items-center gap-2 border-b-2 px-3 text-sm font-bold transition sm:px-4">
                        <i class="fas fa-undo-alt" aria-hidden="true"></i> Pengembalian <span
                            class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200">{{ $pengembalian->count() }}</span>
                    </button>
                    <button type="button" role="tab" id="tab-kalibrasi" :aria-selected="tab === 'kalibrasi'"
                        aria-controls="panel-kalibrasi" @click="tab = 'kalibrasi'"
                        :class="tab === 'kalibrasi' ? 'border-blue-500 text-blue-700 dark:text-blue-300' :
                            'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                        class="inline-flex min-h-14 shrink-0 items-center gap-2 border-b-2 px-3 text-sm font-bold transition sm:px-4">
                        <i class="fas fa-calendar-check" aria-hidden="true"></i> Kalibrasi <span
                            class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">{{ $kalibrasis->count() }}</span>
                    </button>
                </div>
            </div>

            {{-- Pengambilan panel --}}
            <div id="panel-pengambilan" role="tabpanel" aria-labelledby="tab-pengambilan" x-show="tab === 'pengambilan'"
                x-transition.opacity class="p-4 sm:p-6">
                @if ($pengambilan->isEmpty())
                    <div
                        class="rounded-2xl border border-dashed border-gray-300 px-5 py-14 text-center dark:border-gray-600">
                        <span
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-500 dark:bg-amber-950/40 dark:text-amber-300"><i
                                class="fas fa-hand-holding text-2xl" aria-hidden="true"></i></span>
                        <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">Belum ada pengambilan</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Riwayat pengambilan untuk alat ini akan
                            muncul di sini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @foreach ($pengambilan as $item)
                            @php
                                $namaPeminjam = $item->nama_peminjam ?? ($item->user->name ?? '-');
                                $fotoPengambilan = $item->foto ? asset('storage/pengambilan/' . $item->foto) : null;
                                $modalData = [
                                    'nama_peminjam' => $item->nama_peminjam,
                                    'user' => ['name' => $item->user->name ?? null],
                                    'bagian' => ['nama' => $item->bagian->nama ?? null],
                                    'jumlah' => $item->jumlah,
                                    'satuan' => $item->satuan,
                                    'status' => $item->status,
                                    'keperluan' => $item->keperluan,
                                    'waktu_pengambilan' => $item->waktu_pengambilan,
                                    'foto_url' => $fotoPengambilan,
                                ];
                            @endphp
                            <article
                                class="rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-amber-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-amber-700">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300"><i
                                                class="fas fa-user" aria-hidden="true"></i></span>
                                        <div class="min-w-0">
                                            <h3 class="truncate font-bold text-gray-900 dark:text-white">
                                                {{ $namaPeminjam }}</h3>
                                            <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">
                                                {{ $item->bagian->nama ?? 'Bagian tidak diketahui' }}</p>
                                        </div>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $item->status === 'dipinjam' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-200' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200' }}">{{ ucfirst($item->status ?: 'Tidak diketahui') }}</span>
                                </div>
                                <div class="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah diambil</p>
                                        <p class="mt-1 text-lg font-extrabold text-amber-700 dark:text-amber-300">
                                            {{ $item->jumlah ?? 0 }} <span
                                                class="text-xs font-semibold">{{ $item->satuan ?? 'unit' }}</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Waktu pengambilan</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $item->waktu_pengambilan ? \Carbon\Carbon::parse($item->waktu_pengambilan)->translatedFormat('d M Y, H:i') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Keperluan</p>
                                    <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                                        {{ $item->keperluan ?: 'Tidak ada keterangan.' }}</p>
                                </div>
                                <div
                                    class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-700">
                                    @if ($fotoPengambilan)
                                        <button type="button" @click="openModal('foto', @js($modalData))"
                                            class="inline-flex min-h-9 items-center gap-2 rounded-lg px-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-950/40"><i
                                                class="fas fa-camera" aria-hidden="true"></i> Foto dokumentasi</button>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Tidak ada foto</span>
                                    @endif
                                    <button type="button" @click="openModal('pengambilan', @js($modalData))"
                                        class="inline-flex min-h-9 items-center gap-2 rounded-lg px-3 text-sm font-bold text-amber-700 transition hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-950/40">Detail
                                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Pengembalian panel --}}
            <div id="panel-pengembalian" role="tabpanel" aria-labelledby="tab-pengembalian"
                x-show="tab === 'pengembalian'" x-transition.opacity class="p-4 sm:p-6">
                @if ($pengembalian->isEmpty())
                    <div
                        class="rounded-2xl border border-dashed border-gray-300 px-5 py-14 text-center dark:border-gray-600">
                        <span
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-300"><i
                                class="fas fa-undo-alt text-2xl" aria-hidden="true"></i></span>
                        <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">Belum ada pengembalian</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Riwayat pengembalian alat akan muncul di
                            sini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @foreach ($pengembalian as $item)
                            @php
                                $namaPengembali = $item->nama_peminjam ?? ($item->user->name ?? '-');
                                $fotoPengembalian = $item->foto ? asset('storage/pengembalian/' . $item->foto) : null;
                                $modalData = [
                                    'nama_peminjam' => $item->nama_peminjam,
                                    'user' => ['name' => $item->user->name ?? null],
                                    'jumlah' => $item->jumlah,
                                    'tanggal_pengembalian' => $item->tanggal_pengembalian,
                                    'keterangan' => $item->keterangan,
                                    'foto_url' => $fotoPengembalian,
                                ];
                            @endphp
                            <article
                                class="rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-emerald-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-emerald-700">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300"><i
                                                class="fas fa-user-check" aria-hidden="true"></i></span>
                                        <div class="min-w-0">
                                            <h3 class="truncate font-bold text-gray-900 dark:text-white">
                                                {{ $namaPengembali }}</h3>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pengembalian alat
                                            </p>
                                        </div>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">Dikembalikan</span>
                                </div>
                                <div class="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah kembali</p>
                                        <p class="mt-1 text-lg font-extrabold text-emerald-700 dark:text-emerald-300">
                                            {{ $item->jumlah ?? 0 }} <span class="text-xs font-semibold">unit</span></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal pengembalian</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $item->tanggal_pengembalian ? \Carbon\Carbon::parse($item->tanggal_pengembalian)->translatedFormat('d M Y, H:i') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Keterangan</p>
                                    <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                                        {{ $item->keterangan ?: 'Tidak ada keterangan.' }}</p>
                                </div>
                                <div
                                    class="mt-5 flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-700">
                                    @if ($fotoPengembalian)
                                        <button type="button" @click="openModal('foto', @js($modalData))"
                                            class="inline-flex min-h-9 items-center gap-2 rounded-lg px-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-950/40"><i
                                                class="fas fa-camera" aria-hidden="true"></i> Foto dokumentasi</button>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Tidak ada foto</span>
                                    @endif
                                    <button type="button"
                                        @click="openModal('pengembalian', @js($modalData))"
                                        class="inline-flex min-h-9 items-center gap-2 rounded-lg px-3 text-sm font-bold text-emerald-700 transition hover:bg-emerald-50 dark:text-emerald-300 dark:hover:bg-emerald-950/40">Detail
                                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Kalibrasi panel --}}
            <div id="panel-kalibrasi" role="tabpanel" aria-labelledby="tab-kalibrasi" x-show="tab === 'kalibrasi'"
                x-transition.opacity class="p-4 sm:p-6">
                @if ($kalibrasis->isEmpty())
                    <div
                        class="rounded-2xl border border-dashed border-gray-300 px-5 py-14 text-center dark:border-gray-600">
                        <span
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300"><i
                                class="fas fa-microscope text-2xl" aria-hidden="true"></i></span>
                        <h3 class="mt-4 text-base font-bold text-gray-900 dark:text-white">Belum ada riwayat kalibrasi</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Catatan kalibrasi alat akan muncul di
                            sini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        @foreach ($kalibrasis as $item)
                            @php
                                $modalData = [
                                    'no_sertifikat' => $item->no_sertifikat,
                                    'tanggal_kalibrasi' => $item->tanggal_kalibrasi,
                                    'masa_berlaku_baru' => $item->masa_berlaku_baru,
                                    'keterangan' => $item->keterangan,
                                ];
                            @endphp
                            <article
                                class="rounded-2xl border border-gray-200 bg-white p-5 transition hover:border-blue-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-700">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"><i
                                                class="fas fa-microscope" aria-hidden="true"></i></span>
                                        <div class="min-w-0">
                                            <h3 class="break-words font-bold text-gray-900 dark:text-white">
                                                {{ $item->no_sertifikat ?: 'Sertifikat tidak dicantumkan' }}</h3>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Nomor sertifikat</p>
                                        </div>
                                    </div>
                                    <span
                                        class="shrink-0 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-800 dark:bg-blue-900/50 dark:text-blue-200">Kalibrasi</span>
                                </div>
                                <div class="mt-5 grid grid-cols-2 gap-3 rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal kalibrasi</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $item->tanggal_kalibrasi ? \Carbon\Carbon::parse($item->tanggal_kalibrasi)->translatedFormat('d M Y') : '-' }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Berlaku hingga</p>
                                        <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $item->masa_berlaku_baru ? \Carbon\Carbon::parse($item->masa_berlaku_baru)->translatedFormat('d M Y') : '-' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <p
                                        class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                        Keterangan</p>
                                    <p class="mt-1 line-clamp-2 text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                                        {{ $item->keterangan ?: 'Tidak ada keterangan.' }}</p>
                                </div>
                                <div class="mt-5 flex justify-end border-t border-gray-100 pt-4 dark:border-gray-700">
                                    <button type="button" @click="openModal('kalibrasi', @js($modalData))"
                                        class="inline-flex min-h-9 items-center gap-2 rounded-lg px-3 text-sm font-bold text-blue-700 transition hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-950/40">Detail
                                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i></button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    {{-- Detail modal --}}
    <div x-cloak x-show="modalOpen" x-transition.opacity
        class="fixed inset-0 z-[70] flex items-center justify-center overflow-y-auto bg-gray-950/70 p-4 backdrop-blur-sm"
        role="dialog" aria-modal="true" aria-label="Detail riwayat alat" @click.self="closeModal()">
        <div x-show="modalOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-3 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            class="my-auto max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800"
            @click.stop>
            <div
                class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white/95 px-5 py-4 backdrop-blur dark:border-gray-700 dark:bg-gray-800/95 sm:px-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl"
                        :class="modalType === 'pengambilan' ?
                            'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300' : (
                                modalType === 'pengembalian' ?
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300' :
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300')">
                        <i class="fas"
                            :class="modalType === 'pengambilan' ? 'fa-hand-holding' : (modalType === 'pengembalian' ?
                                'fa-undo-alt' : (modalType === 'foto' ? 'fa-camera' : 'fa-microscope'))"
                            aria-hidden="true"></i>
                    </span>
                    <div>
                        <h2 class="font-extrabold text-gray-900 dark:text-white"
                            x-text="modalType === 'pengambilan' ? 'Detail Pengambilan' : (modalType === 'pengembalian' ? 'Detail Pengembalian' : (modalType === 'foto' ? 'Foto Dokumentasi' : 'Detail Kalibrasi'))">
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Informasi riwayat alat</p>
                    </div>
                </div>
                <button type="button" @click="closeModal()" aria-label="Tutup dialog"
                    class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"><i
                        class="fas fa-times" aria-hidden="true"></i></button>
            </div>

            <div class="space-y-4 p-5 sm:p-6">
                <template x-if="modalType === 'pengambilan'">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Peminjam</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                    x-text="modalData.nama_peminjam || modalData.user?.name || '-'"></p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Bagian</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                    x-text="modalData.bagian?.nama || '-'"></p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                    x-text="(modalData.jumlah ?? '-') + ' ' + (modalData.satuan || '')"></p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                    x-text="modalData.status || '-'"></p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60 sm:col-span-2">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Waktu pengambilan</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                    x-text="formatDate(modalData.waktu_pengambilan)"></p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Keperluan</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-gray-800 dark:text-gray-200"
                                x-text="modalData.keperluan || '-'"></p>
                        </div>
                        <template x-if="modalData.foto_url"><img :src="modalData.foto_url" alt="Foto pengambilan"
                                class="max-h-80 w-full rounded-xl bg-gray-100 object-contain dark:bg-gray-900"></template>
                    </div>
                </template>

                <template x-if="modalType === 'pengembalian'">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Pengembali</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                    x-text="modalData.nama_peminjam || modalData.user?.name || '-'"></p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Jumlah dikembalikan</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                    x-text="(modalData.jumlah ?? '-') + ' unit'"></p>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60 sm:col-span-2">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal pengembalian</p>
                                <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                    x-text="formatDate(modalData.tanggal_pengembalian)"></p>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                Keterangan</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-gray-800 dark:text-gray-200"
                                x-text="modalData.keterangan || '-'"></p>
                        </div>
                        <template x-if="modalData.foto_url"><img :src="modalData.foto_url" alt="Foto pengembalian"
                                class="max-h-80 w-full rounded-xl bg-gray-100 object-contain dark:bg-gray-900"></template>
                    </div>
                </template>

                <template x-if="modalType === 'foto'">
                    <div class="space-y-3"><img :src="modalData.foto_url" alt="Foto dokumentasi"
                            class="max-h-[65vh] w-full rounded-xl bg-gray-100 object-contain dark:bg-gray-900">
                        <p class="text-center text-xs text-gray-500 dark:text-gray-400"
                            x-text="'Dokumentasi ' + (modalData.nama_peminjam || modalData.user?.name || '')"></p>
                    </div>
                </template>

                <template x-if="modalType === 'kalibrasi'">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Nomor sertifikat</p>
                            <p class="mt-1 break-words font-semibold text-gray-900 dark:text-white"
                                x-text="modalData.no_sertifikat || '-'"></p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal kalibrasi</p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                x-text="formatDate(modalData.tanggal_kalibrasi, false)"></p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Berlaku hingga</p>
                            <p class="mt-1 font-semibold text-gray-900 dark:text-white"
                                x-text="formatDate(modalData.masa_berlaku_baru, false)"></p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-900/60 sm:col-span-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Keterangan</p>
                            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-gray-800 dark:text-gray-200"
                                x-text="modalData.keterangan || '-'"></p>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end border-t border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                <button type="button" @click="closeModal()"
                    class="inline-flex min-h-10 items-center justify-center rounded-xl bg-gray-900 px-5 py-2 text-sm font-bold text-white transition hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-300 dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white dark:focus:ring-gray-600">Tutup</button>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endsection

{{-- Alpine.js diasumsikan sudah dimuat oleh layouts.app / Livewire. --}}
