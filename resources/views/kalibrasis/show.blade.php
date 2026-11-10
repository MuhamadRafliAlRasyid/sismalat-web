@extends('layouts.app')

@section('title', 'Detail Kalibrasi')

@section('content')
    @php
        $alat = $data->alat;
        $tanggalKalibrasi = $data->tanggal_kalibrasi
            ? \Carbon\Carbon::parse($data->tanggal_kalibrasi)->startOfDay()
            : null;
        $masaBerlaku = $data->masa_berlaku_baru ? \Carbon\Carbon::parse($data->masa_berlaku_baru)->startOfDay() : null;
        $today = now()->startOfDay();

        // Masa berlaku pada hari ini masih dianggap berlaku sampai hari tersebut selesai.
        $isExpired = $masaBerlaku ? $masaBerlaku->lt($today) : false;
        $daysRemaining = $masaBerlaku && !$isExpired ? (int) $today->diffInDays($masaBerlaku, false) : null;
        $isWarning = !$isExpired && $daysRemaining !== null && $daysRemaining <= 30;
        $isAdmin = auth()->user()?->isAdminOrSuper() ?? false;

        $statusLabel = $isExpired ? 'Kedaluwarsa' : ($isWarning ? 'Segera jatuh tempo' : 'Berlaku');
        $statusClasses = $isExpired
            ? 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-900/25 dark:text-red-300 dark:ring-red-800'
            : ($isWarning
                ? 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-900/25 dark:text-amber-300 dark:ring-amber-800'
                : 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/25 dark:text-emerald-300 dark:ring-emerald-800');
        $statusIcon = $isExpired ? 'fa-times-circle' : ($isWarning ? 'fa-clock' : 'fa-check-circle');

        $totalDays = $tanggalKalibrasi && $masaBerlaku ? max(1, $tanggalKalibrasi->diffInDays($masaBerlaku)) : 1;
        $elapsedDays = $tanggalKalibrasi ? max(0, $tanggalKalibrasi->diffInDays($today, false)) : 0;
        $progress = min(100, max(0, ($elapsedDays / $totalDays) * 100));
        $progressColor = $isExpired ? 'bg-red-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500');
    @endphp

    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
        {{-- Navigasi dan aksi --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('kalibrasis.index') }}"
                class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-gray-600 transition hover:bg-white hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
                <i class="fas fa-arrow-left"></i>
                Kembali ke daftar kalibrasi
            </a>

            @if ($isAdmin)
                <a href="{{ route('kalibrasis.edit', $data->hashid) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900">
                    <i class="fas fa-pen"></i>
                    Edit data kalibrasi
                </a>
            @endif
        </div>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="mb-5 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300"
                role="status">
                <i class="fas fa-check-circle mt-0.5 text-emerald-600 dark:text-emerald-400"></i>
                <p class="flex-1">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300"
                role="alert">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <p class="flex-1">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Ringkasan status --}}
        <section
            class="relative mb-6 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div
                class="absolute inset-x-0 top-0 h-1.5 {{ $isExpired ? 'bg-red-500' : ($isWarning ? 'bg-amber-500' : 'bg-emerald-500') }}">
            </div>
            <div class="p-5 sm:p-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                    <div class="flex min-w-0 items-start gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl {{ $isExpired ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-300' : ($isWarning ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300') }}">
                            <i class="fas fa-ruler-combined text-2xl"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-gray-400 dark:text-gray-500">Rekam
                                kalibrasi</p>
                            <h1 class="mt-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                                Detail Kalibrasi</h1>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Informasi alat, periode berlaku, dan
                                dokumen pendukung.</p>
                        </div>
                    </div>
                    <span
                        class="inline-flex w-fit shrink-0 items-center gap-2 rounded-full px-3.5 py-2 text-sm font-semibold ring-1 {{ $statusClasses }}">
                        <i class="fas {{ $statusIcon }}"></i>
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="mt-7 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/60">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Tanggal
                            kalibrasi</p>
                        <p class="mt-2 text-lg font-bold text-gray-900 dark:text-white">
                            {{ $tanggalKalibrasi?->translatedFormat('d M Y') ?? '-' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/60">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Masa
                            berlaku sampai</p>
                        <p
                            class="mt-2 text-lg font-bold {{ $isExpired ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $masaBerlaku?->translatedFormat('d M Y') ?? '-' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 dark:bg-gray-900/60">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Sisa masa
                            berlaku</p>
                        <p
                            class="mt-2 text-lg font-bold {{ $isExpired ? 'text-red-600 dark:text-red-400' : ($isWarning ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                            @if ($isExpired)
                                Lewat {{ abs((int) $today->diffInDays($masaBerlaku, false)) }} hari
                            @elseif ($daysRemaining === 0)
                                Berakhir hari ini
                            @elseif ($daysRemaining !== null)
                                {{ $daysRemaining }} hari lagi
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>

                @if ($tanggalKalibrasi && $masaBerlaku)
                    <div class="mt-5">
                        <div class="mb-2 flex items-center justify-between gap-3 text-xs">
                            <span class="font-medium text-gray-500 dark:text-gray-400">Periode masa berlaku</span>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ number_format($progress, 0) }}%
                                terpakai</span>
                        </div>
                        <div class="h-2.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                            <div class="h-full rounded-full {{ $progressColor }} transition-all"
                                style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
            {{-- Informasi alat dan kalibrasi --}}
            <div class="space-y-6 lg:col-span-3">
                <section
                    class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                        <h2 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300"><i
                                    class="fas fa-toolbox"></i></span>
                            Informasi alat
                        </h2>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                <i class="fas fa-tools text-xl"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">
                                    Nama alat</p>
                                <h3 class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $alat->nama_alat ?? 'Data alat tidak ditemukan' }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $alat?->merk ?: 'Merk tidak dicantumkan' }}
                                    @if ($alat?->tipe)
                                        <span class="mx-1">·</span>{{ $alat->tipe }}
                                    @endif
                                </p>
                            </div>
                        </div>

                        <dl
                            class="mt-6 grid grid-cols-1 gap-x-6 gap-y-4 border-t border-gray-100 pt-5 dark:border-gray-700 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">Nomor seri</dt>
                                <dd class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $alat?->no_seri ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">Kelas alat</dt>
                                <dd class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $alat?->kelas ?: '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">Nomor sertifikat</dt>
                                <dd class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    {{ $data->no_sertifikat ?: 'Belum dicantumkan' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">ID rekam kalibrasi</dt>
                                <dd class="mt-1 break-all font-mono text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    {{ strtoupper($data->hashid) }}</dd>
                            </div>
                        </dl>
                    </div>
                </section>

                <section
                    class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                        <h2 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300"><i
                                    class="fas fa-calendar-check"></i></span>
                            Rincian kalibrasi
                        </h2>
                    </div>
                    <div class="p-5 sm:p-6">
                        <dl class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">Tanggal pelaksanaan</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $tanggalKalibrasi?->translatedFormat('d F Y') ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">Berlaku sampai</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $masaBerlaku?->translatedFormat('d F Y') ?? '-' }}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-400 dark:text-gray-500">Keterangan</dt>
                                <dd
                                    class="mt-2 rounded-xl bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-700 dark:bg-gray-900/60 dark:text-gray-300">
                                    {{ filled($data->keterangan) ? $data->keterangan : 'Tidak ada keterangan tambahan.' }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </section>
            </div>

            {{-- Sertifikat --}}
            <aside class="space-y-6 lg:col-span-2">
                <section
                    class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-700">
                        <h2 class="flex items-center gap-2 text-base font-bold text-gray-900 dark:text-white">
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300"><i
                                    class="fas fa-file-certificate"></i></span>
                            Bukti sertifikat
                        </h2>
                        <p class="mt-1 pl-11 text-xs text-gray-500 dark:text-gray-400">Dokumen pendukung hasil kalibrasi</p>
                    </div>
                    <div class="p-5">
                        @if ($data->bukti_sertifikat)
                            @if ($isAdmin)
                                @if ($data->bukti_sertifikat_is_pdf)
                                    <div
                                        class="rounded-2xl border border-red-100 bg-red-50 p-5 text-center dark:border-red-900/50 dark:bg-red-900/10">
                                        <span
                                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-red-600 shadow-sm dark:bg-gray-800 dark:text-red-400"><i
                                                class="fas fa-file-pdf text-3xl"></i></span>
                                        <p class="mt-3 break-all text-sm font-semibold text-gray-800 dark:text-gray-100">
                                            {{ $data->bukti_sertifikat_name }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dokumen PDF sertifikat
                                            kalibrasi</p>
                                        <a href="{{ $data->bukti_sertifikat_url }}" target="_blank"
                                            rel="noopener noreferrer"
                                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">
                                            <i class="fas fa-external-link-alt"></i> Buka sertifikat PDF
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ $data->bukti_sertifikat_url }}" target="_blank"
                                        rel="noopener noreferrer" class="group block">
                                        <div
                                            class="overflow-hidden rounded-2xl border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-900">
                                            <img src="{{ $data->bukti_sertifikat_url }}" alt="Bukti sertifikat kalibrasi"
                                                class="max-h-[420px] w-full object-contain transition duration-300 group-hover:scale-[1.01]">
                                        </div>
                                        <span
                                            class="mt-3 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 group-hover:text-emerald-800 dark:text-emerald-400">
                                            <i class="fas fa-search-plus"></i> Lihat gambar ukuran penuh
                                        </span>
                                    </a>
                                @endif
                                <p class="mt-4 text-xs leading-5 text-gray-400 dark:text-gray-500">File sertifikat dibuka
                                    melalui akses terproteksi. Jangan membagikan dokumen kepada pihak yang tidak berwenang.
                                </p>
                            @else
                                <div
                                    class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-lock mt-0.5 text-emerald-700 dark:text-emerald-300"></i>
                                        <div>
                                            <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-200">
                                                Sertifikat tersedia</p>
                                            <p class="mt-1 text-xs leading-5 text-emerald-800 dark:text-emerald-300">
                                                Dokumen tersimpan di sistem. Akses file dikelola oleh Admin atau Super
                                                Admin.</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @else
                            <div
                                class="rounded-2xl border border-dashed border-amber-300 bg-amber-50/70 px-4 py-8 text-center dark:border-amber-800 dark:bg-amber-900/10">
                                <span
                                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300"><i
                                        class="fas fa-file-circle-question text-2xl"></i></span>
                                <p class="mt-3 text-sm font-semibold text-gray-800 dark:text-gray-100">Belum ada sertifikat
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Bukti sertifikat belum
                                    diunggah untuk catatan kalibrasi ini.</p>
                                @if ($isAdmin)
                                    <a href="{{ route('kalibrasis.edit', $data->hashid) }}"
                                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-700">
                                        <i class="fas fa-upload"></i> Unggah sertifikat
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </section>

                <section
                    class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Ringkasan status</h2>
                    <div class="mt-3 flex items-start gap-3">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $isExpired ? 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-300' : ($isWarning ? 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300') }}">
                            <i class="fas {{ $statusIcon }}"></i>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $statusLabel }}</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                                @if ($isExpired)
                                    Masa berlaku kalibrasi telah berakhir. Jadwalkan kalibrasi ulang.
                                @elseif ($isWarning)
                                    Masa berlaku akan berakhir dalam {{ $daysRemaining }} hari. Persiapkan jadwal kalibrasi
                                    ulang.
                                @else
                                    Kalibrasi masih berlaku berdasarkan tanggal masa berlaku yang tercatat.
                                @endif
                            </p>
                        </div>
                    </div>
                </section>
            </aside>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
            <a href="{{ route('kalibrasis.index') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <i class="fas fa-list"></i> Daftar kalibrasi
            </a>
            @if ($isAdmin)
                <a href="{{ route('kalibrasis.edit', $data->hashid) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700">
                    <i class="fas fa-pen"></i> Perbarui catatan kalibrasi
                </a>
            @endif
        </div>
    </div>
@endsection
