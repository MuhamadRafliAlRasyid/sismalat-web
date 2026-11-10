@extends('layouts.app')

@section('title', 'Dashboard Karyawan')

@section('content')
    @include('components.onboarding')

    <div class="min-h-screen space-y-6 pb-8" x-data="employeeDashboard()">
        {{-- HEADER --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 p-6 text-white shadow-xl md:p-8">
            <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full bg-amber-400/15 blur-3xl">
            </div>
            <div class="pointer-events-none absolute -bottom-24 right-1/3 h-56 w-56 rounded-full bg-orange-500/10 blur-3xl">
            </div>

            <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    @php
                        $profilePhoto = Auth::user()->profile_photo_path;
                        $profilePhotoUrl = $profilePhoto
                            ? asset('images/profile/' . $profilePhoto)
                            : asset('images/avatar.png');
                    @endphp
                    <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ Auth::user()->name }}"
                        class="h-16 w-16 rounded-2xl border-2 border-white/30 object-cover shadow-lg"
                        onerror="this.onerror=null;this.src='{{ asset('images/avatar.png') }}'">
                    <div>
                        <p class="mb-1 text-sm font-medium text-amber-200">PORTAL PEMINJAMAN ALAT</p>
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Halo, {{ Auth::user()->name }} 👋</h1>
                        <p class="mt-1 text-sm text-slate-300">Pantau alat, ajukan peminjaman, dan periksa riwayat
                            aktivitasmu.</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <div class="rounded-xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-xs text-slate-300">Hari ini</p>
                        <p class="font-semibold" x-text="todayLabel"></p>
                    </div>
                    <a href="#form-peminjaman"
                        class="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-3 font-semibold text-slate-950 shadow-lg shadow-amber-950/20 transition hover:-translate-y-0.5 hover:bg-amber-300">
                        <i class="fas fa-plus-circle"></i>
                        Ajukan Peminjaman
                    </a>
                </div>
            </div>
        </section>

        {{-- KPI --}}
        <section class="grid grid-cols-2 gap-3 xl:grid-cols-4">
            <div
                class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 md:p-5">
                <div class="flex items-start justify-between">
                    <div class="rounded-xl bg-amber-100 p-3 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"><i
                            class="fas fa-toolbox text-lg"></i></div>
                    <span class="text-xs font-medium text-slate-400">Aktivitas</span>
                </div>
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Sedang dipinjam</p>
                <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">
                    {{ number_format($totalDipinjam, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-400">Transaksi berstatus dipinjam</p>
            </div>

            <div
                class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 md:p-5">
                <div class="flex items-start justify-between">
                    <div
                        class="rounded-xl bg-emerald-100 p-3 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                        <i class="fas fa-rotate-left text-lg"></i></div>
                    <span class="text-xs font-medium text-slate-400">Riwayat</span>
                </div>
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Pengembalian tercatat</p>
                <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">
                    {{ number_format($totalDikembalikan, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-400">Data pengembalian milikmu</p>
            </div>

            <div
                class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 md:p-5">
                <div class="flex items-start justify-between">
                    <div class="rounded-xl bg-blue-100 p-3 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"><i
                            class="fas fa-boxes-stacked text-lg"></i></div>
                    <span class="text-xs font-medium text-slate-400">Inventaris</span>
                </div>
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Jenis alat tersedia</p>
                <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">
                    {{ number_format($totalAlatTersedia, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-400">Alat yang dapat dipilih</p>
            </div>

            <div
                class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 md:p-5">
                <div class="flex items-start justify-between">
                    <div class="rounded-xl bg-violet-100 p-3 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300"><i
                            class="fas fa-clipboard-list text-lg"></i></div>
                    <span class="text-xs font-medium text-slate-400">Personal</span>
                </div>
                <p class="mt-4 text-sm text-slate-500 dark:text-slate-400">Total transaksi</p>
                <p class="mt-1 text-3xl font-bold text-slate-900 dark:text-white">
                    {{ number_format($totalRiwayat, 0, ',', '.') }}</p>
                <p class="mt-1 text-xs text-slate-400">Seluruh peminjamanmu</p>
            </div>
        </section>

        {{-- MAIN: FORM + CURRENT LOANS --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- FORM PEMINJAMAN --}}
            <div id="form-peminjaman"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 xl:col-span-3">
                <div
                    class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700 md:px-6">
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300"><i
                                    class="fas fa-file-circle-plus"></i></span>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Form Peminjaman Alat</h2>
                        </div>
                        <p class="mt-1 pl-11 text-xs text-slate-500 dark:text-slate-400">Lengkapi data berikut untuk
                            mengajukan peminjaman.</p>
                    </div>
                    <span
                        class="hidden rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 sm:inline-flex"><i
                            class="fas fa-lock mr-1.5 mt-0.5"></i> Akun terverifikasi</span>
                </div>

                <div class="p-5 md:p-6">
                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-900/20 dark:text-red-200"
                            role="alert">
                            <div class="mb-1 flex items-center gap-2 font-semibold"><i
                                    class="fas fa-circle-exclamation"></i> Pengajuan belum dapat dikirim</div>
                            <ul class="list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-900/20 dark:text-emerald-200"
                            role="status">
                            <i class="fas fa-circle-check mt-0.5 text-lg"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($alatTersedia->isEmpty())
                        <div
                            class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center dark:border-slate-600 dark:bg-slate-900/40">
                            <div
                                class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                <i class="fas fa-box-open text-2xl"></i></div>
                            <h3 class="font-semibold text-slate-800 dark:text-white">Belum ada alat yang tersedia</h3>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Silakan periksa kembali nanti atau
                                hubungi administrator inventaris.</p>
                        </div>
                    @else
                        <form method="POST" action="{{ route('pengambilan_alat.store') }}" x-data="loanRequest({ stockColumn: @js($hasJumlahColumn) })"
                            @submit="submitting = true" class="space-y-5">
                            @csrf

                            <div>
                                <label for="alat_id"
                                    class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Pilih alat
                                    <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select id="alat_id" name="alat_id" x-ref="alat" @change="updateStock()" required
                                        class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-4 py-3 pr-10 text-sm text-slate-800 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                                        <option value="">-- Pilih alat yang akan dipinjam --</option>
                                        @foreach ($alatTersedia as $alat)
                                            <option value="{{ $alat->id }}"
                                                data-stock="{{ $hasJumlahColumn ? (int) $alat->jumlah : 999999 }}"
                                                data-unit="{{ $alat->satuan ?? 'unit' }}" @selected(old('alat_id') == $alat->id)>
                                                {{ $alat->nama_alat }}
                                                @if ($hasJumlahColumn)
                                                    — stok {{ (int) $alat->jumlah }} {{ $alat->satuan ?? 'unit' }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <i
                                        class="fas fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                </div>
                                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400" x-show="selectedAlat">
                                    <i class="fas fa-boxes-stacked mr-1"></i>
                                    Stok tersedia: <strong x-text="stockLabel"></strong>
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="jumlah"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Jumlah
                                        <span class="text-red-500">*</span></label>
                                    <input id="jumlah" name="jumlah" type="number" min="1"
                                        :max="stockColumn && selectedAlat ? stock : null" value="{{ old('jumlah', 1) }}"
                                        required x-ref="jumlah"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                                    <p class="mt-1 text-xs text-slate-400">Masukkan jumlah sesuai kebutuhan.</p>
                                </div>
                                <div>
                                    <label for="lama_pinjam"
                                        class="mb-2 block text-sm font-semibold text-slate-700 dark:text-slate-200">Lama
                                        peminjaman (hari) <span class="text-red-500">*</span></label>
                                    <input id="lama_pinjam" name="lama_pinjam" type="number" min="1"
                                        max="365" value="{{ old('lama_pinjam', 3) }}" required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                                    <p class="mt-1 text-xs text-slate-400">Durasi dihitung dalam hari.</p>
                                </div>
                            </div>

                            <div
                                class="rounded-xl bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-900/20 dark:text-amber-200">
                                <div class="flex gap-3">
                                    <i class="fas fa-circle-info mt-0.5"></i>
                                    <p>Pastikan jenis alat, jumlah, dan durasi sudah benar. Pengajuan akan diproses
                                        mengikuti aturan persetujuan peminjaman yang berlaku di sistem.</p>
                                </div>
                            </div>

                            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <p class="text-xs text-slate-400"><span class="text-red-500">*</span> Wajib diisi</p>
                                <button type="submit" :disabled="submitting || !selectedAlat"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-amber-500/20 transition hover:-translate-y-0.5 hover:bg-amber-600 disabled:cursor-not-allowed disabled:opacity-50">
                                    <i class="fas" :class="submitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                                    <span x-text="submitting ? 'Mengirim pengajuan...' : 'Kirim Pengajuan' "></span>
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ALAT SEDANG DIPINJAM --}}
            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800 xl:col-span-2 md:p-6">
                <div class="mb-5 flex items-center justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white"><span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300"><i
                                    class="fas fa-hourglass-half"></i></span> Sedang Dipinjam</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Daftar alat yang masih menjadi tanggung
                            jawabmu.</p>
                    </div>
                    <span
                        class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-700 dark:bg-orange-900/40 dark:text-orange-300">{{ $alatDipinjam->count() }}
                        aktif</span>
                </div>

                @forelse ($alatDipinjam as $item)
                    <div
                        class="mb-3 rounded-xl border border-slate-100 p-3 transition hover:border-amber-200 hover:bg-amber-50/50 dark:border-slate-700 dark:hover:border-amber-800 dark:hover:bg-slate-700/40">
                        <div class="flex items-center gap-3">
                            @if ($item->alat && $item->alat->foto_thumb)
                                <img src="{{ $item->alat->foto_thumb }}" alt="{{ $item->alat->nama_alat }}"
                                    class="h-12 w-12 rounded-xl bg-slate-100 object-cover dark:bg-slate-700">
                            @else
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-400 dark:bg-slate-700">
                                    <i class="fas fa-screwdriver-wrench text-lg"></i></div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-800 dark:text-white">
                                    {{ $item->alat->nama_alat ?? 'Alat tidak tersedia' }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $item->jumlah ?? 1 }}
                                    {{ $item->satuan ?? 'unit' }} ·
                                    {{ $item->waktu_pengambilan ? \Carbon\Carbon::parse($item->waktu_pengambilan)->format('d M Y, H:i') : '-' }}
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('pengembalian_alat.create', $item->hashid) }}"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-300 dark:hover:bg-emerald-900/50">
                            <i class="fas fa-rotate-left"></i> Proses Pengembalian
                        </a>
                    </div>
                @empty
                    <div
                        class="rounded-xl border border-dashed border-slate-200 px-4 py-10 text-center dark:border-slate-700">
                        <div
                            class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-500 dark:bg-emerald-900/30">
                            <i class="fas fa-check text-xl"></i></div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">Tidak ada alat yang sedang
                            dipinjam</p>
                        <p class="mt-1 text-xs text-slate-400">Kamu sudah tidak memiliki pinjaman aktif.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- RIWAYAT PEMINJAMAN --}}
        <section id="riwayat-peminjaman"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div
                class="flex flex-col gap-4 border-b border-slate-100 p-5 dark:border-slate-700 md:flex-row md:items-center md:justify-between md:px-6">
                <div>
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900 dark:text-white"><span
                            class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"><i
                                class="fas fa-clock-rotate-left"></i></span> Riwayat Peminjaman Saya</h2>
                    <p class="mt-1 pl-11 text-xs text-slate-500 dark:text-slate-400">Seluruh transaksi peminjaman akun yang
                        sedang login.</p>
                </div>
                <form method="GET" action="{{ url()->current() }}#riwayat-peminjaman"
                    class="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
                    <div class="relative flex-1 sm:min-w-52">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        <input type="search" name="q" value="{{ $keyword }}"
                            placeholder="Cari nama alat..."
                            class="w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/10 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                    </div>
                    <select name="status"
                        class="rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 outline-none focus:border-amber-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                        <option value="">Semua status</option>
                        <option value="dipinjam" @selected($statusFilter === 'dipinjam')>Dipinjam</option>
                        <option value="dikembalikan" @selected($statusFilter === 'dikembalikan')>Dikembalikan</option>
                    </select>
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700 dark:bg-amber-500 dark:text-slate-950 dark:hover:bg-amber-400"><i
                            class="fas fa-filter mr-1"></i> Filter</button>
                    @if ($keyword !== '' || $statusFilter)
                        <a href="{{ url()->current() }}#riwayat-peminjaman"
                            class="rounded-xl border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-700">Reset</a>
                    @endif
                </form>
            </div>

            @if ($riwayatPeminjaman->count())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/50">
                            <tr
                                class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="whitespace-nowrap px-5 py-3 md:px-6">Alat</th>
                                <th class="whitespace-nowrap px-5 py-3">Jumlah</th>
                                <th class="whitespace-nowrap px-5 py-3">Waktu Pengambilan</th>
                                <th class="whitespace-nowrap px-5 py-3">Durasi</th>
                                <th class="whitespace-nowrap px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @foreach ($riwayatPeminjaman as $ambil)
                                @php $isDipinjam = $ambil->status === 'dipinjam'; @endphp
                                <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-700/30">
                                    <td class="px-5 py-4 md:px-6">
                                        <div class="flex min-w-44 items-center gap-3">
                                            <div
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                                <i class="fas fa-toolbox"></i></div>
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-semibold text-slate-800 dark:text-white">
                                                    {{ $ambil->alat->nama_alat ?? 'Alat tidak tersedia' }}</p>
                                                <p class="text-xs text-slate-400">ID transaksi:
                                                    {{ $ambil->hashid ?? $ambil->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $ambil->jumlah ?? 1 }} {{ $ambil->satuan ?? 'unit' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $ambil->waktu_pengambilan ? \Carbon\Carbon::parse($ambil->waktu_pengambilan)->format('d M Y, H:i') : $ambil->created_at?->format('d M Y, H:i') ?? '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600 dark:text-slate-300">
                                        {{ $ambil->lama_pinjam ? $ambil->lama_pinjam . ' hari' : '-' }}</td>
                                    <td class="whitespace-nowrap px-5 py-4">
                                        @if ($isDipinjam)
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-2.5 py-1 text-xs font-semibold text-orange-700 dark:bg-orange-900/30 dark:text-orange-300"><span
                                                    class="h-1.5 w-1.5 rounded-full bg-orange-500"></span> Dipinjam</span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"><i
                                                    class="fas fa-check text-[10px]"></i> Dikembalikan</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-700 md:px-6">
                    {{ $riwayatPeminjaman->links() }}
                </div>
            @else
                <div class="px-5 py-14 text-center">
                    <div
                        class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-700">
                        <i class="fas fa-clipboard-list text-2xl"></i></div>
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Belum ada riwayat peminjaman</p>
                    <p class="mt-1 text-sm text-slate-400">Transaksi yang kamu lakukan akan tampil di bagian ini.</p>
                </div>
            @endif
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        function employeeDashboard() {
            return {
                todayLabel: new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }).format(new Date())
            };
        }

        function loanRequest(config) {
            return {
                stockColumn: Boolean(config.stockColumn),
                stock: 0,
                selectedAlat: false,
                submitting: false,
                get stockLabel() {
                    if (!this.stockColumn) return 'Stok dikelola oleh administrator';
                    return `${new Intl.NumberFormat('id-ID').format(this.stock)} unit`;
                },
                updateStock() {
                    const option = this.$refs.alat?.selectedOptions?.[0];
                    this.selectedAlat = Boolean(option && option.value);
                    this.stock = this.selectedAlat ? Number(option.dataset.stock || 0) : 0;
                    if (this.stockColumn && this.$refs.jumlah) {
                        this.$refs.jumlah.max = this.selectedAlat ? this.stock : '';
                        if (Number(this.$refs.jumlah.value) > this.stock) {
                            this.$refs.jumlah.value = this.stock;
                        }
                    }
                }
            };
        }
    </script>
@endpush
