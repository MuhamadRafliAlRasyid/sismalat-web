@extends('layouts.app')

@section('title', 'Edit Kalibrasi')

@section('content')
    @php
        $tanggalKalibrasiValue = old('tanggal_kalibrasi', $data->tanggal_kalibrasi?->format('Y-m-d'));
        $masaBerlakuValue = old('masa_berlaku_baru', $data->masa_berlaku_baru?->format('Y-m-d'));
        $expired = $data->masa_berlaku_baru ? $data->masa_berlaku_baru->lt(today()) : false;
        $warning =
            !$expired && $data->masa_berlaku_baru ? today()->diffInDays($data->masa_berlaku_baru, false) <= 30 : false;
    @endphp

    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <a href="{{ route('kalibrasis.show', $data->hashid) }}"
            class="mb-5 inline-flex items-center gap-2 text-sm font-medium text-gray-500 transition hover:text-amber-700 dark:text-gray-400 dark:hover:text-amber-400">
            <span
                class="flex h-9 w-9 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700"><i
                    class="fas fa-arrow-left"></i></span>
            Kembali ke detail kalibrasi
        </a>

        <div
            class="relative mb-7 overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50 via-white to-indigo-50 p-6 shadow-sm dark:border-gray-700 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900 sm:p-8">
            <div
                class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full bg-blue-200/30 blur-3xl dark:bg-blue-500/10">
            </div>
            <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center">
                <span
                    class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-2xl text-white shadow-lg shadow-blue-200 dark:shadow-none"><i
                        class="fas fa-edit"></i></span>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-400">Perbarui
                        catatan</p>
                    <h1 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">Edit Data Kalibrasi</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Perbarui tanggal, masa berlaku, informasi
                        sertifikat, atau keterangannya.</p>
                </div>
                <div class="sm:ml-auto">
                    @if ($expired)
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1.5 text-xs font-bold text-red-700 dark:bg-red-900/30 dark:text-red-300"><i
                                class="fas fa-exclamation-circle"></i> Kedaluwarsa</span>
                    @elseif ($warning)
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-bold text-amber-800 dark:bg-amber-900/30 dark:text-amber-300"><i
                                class="fas fa-clock"></i> Segera kedaluwarsa</span>
                    @else
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"><i
                                class="fas fa-check-circle"></i> Berlaku</span>
                    @endif
                </div>
            </div>
        </div>

        @if (session('error'))
            <div role="alert"
                class="mb-5 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                <i class="fas fa-exclamation-circle mt-0.5 text-lg"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div role="alert"
                class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-300">
                <div class="mb-2 flex items-center gap-2 font-semibold"><i class="fas fa-exclamation-triangle"></i> Periksa
                    kembali data berikut</div>
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
            <form method="POST" action="{{ route('kalibrasis.update', $data->hashid) }}" enctype="multipart/form-data"
                class="space-y-6">
                @csrf
                @method('PUT')

                <section
                    class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-700 sm:px-6">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-300"><i
                                class="fas fa-toolbox"></i></span>
                        <div>
                            <h2 class="font-bold text-gray-900 dark:text-white">Identitas alat</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Informasi alat yang sedang diperbarui.</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Nama alat</p>
                            <p class="mt-1 text-base font-bold text-gray-900 dark:text-white">
                                {{ $data->alat->nama_alat ?? 'Alat tidak ditemukan' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Merk / tipe</p>
                            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ $data->alat->merk ?? '-' }} / {{ $data->alat->tipe ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Nomor seri</p>
                            <p class="mt-1 text-sm font-medium text-gray-700 dark:text-gray-200">
                                {{ $data->alat->no_seri ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Status catatan</p>
                            <p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                {{ $data->alat?->masa_berlaku && $data->masa_berlaku_baru && $data->alat->masa_berlaku->toDateString() === $data->masa_berlaku_baru->toDateString() ? 'Masa berlaku aktif pada data alat' : 'Catatan riwayat kalibrasi' }}
                            </p>
                        </div>
                    </div>
                </section>

                <section
                    class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300"><i
                                class="fas fa-calendar-check"></i></span>
                        <div>
                            <h2 class="font-bold text-gray-900 dark:text-white">Informasi kalibrasi</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Periksa kembali tanggal dan masa berlaku.
                            </p>
                        </div>
                    </div>
                    <div
                        class="mb-5 flex items-start gap-2 rounded-xl border border-blue-100 bg-blue-50 p-3 text-xs text-blue-800 dark:border-blue-900/60 dark:bg-blue-900/20 dark:text-blue-300">
                        <i class="fas fa-info-circle mt-0.5"></i>
                        <p>Setelah disimpan, masa berlaku pada data alat akan disinkronkan berdasarkan catatan kalibrasi
                            terbaru.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="tanggal_kalibrasi"
                                class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Tanggal
                                kalibrasi <span class="text-red-500">*</span></label>
                            <input id="tanggal_kalibrasi" name="tanggal_kalibrasi" type="date" required
                                max="{{ now()->toDateString() }}" value="{{ $tanggalKalibrasiValue }}"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900">
                            @error('tanggal_kalibrasi')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="masa_berlaku_baru"
                                class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Berlaku sampai
                                <span class="text-red-500">*</span></label>
                            <input id="masa_berlaku_baru" name="masa_berlaku_baru" type="date" required
                                value="{{ $masaBerlakuValue }}"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900">
                            @error('masa_berlaku_baru')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="no_sertifikat"
                                class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Nomor
                                sertifikat</label>
                            <input id="no_sertifikat" name="no_sertifikat" type="text" maxlength="255"
                                value="{{ old('no_sertifikat', $data->no_sertifikat) }}"
                                placeholder="Nomor yang tercantum pada sertifikat"
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900">
                            @error('no_sertifikat')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="keterangan"
                                class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">Keterangan <span
                                    class="font-normal text-gray-400">(opsional)</span></label>
                            <textarea id="keterangan" name="keterangan" rows="4" maxlength="5000"
                                placeholder="Catatan hasil kalibrasi atau informasi tambahan..."
                                class="w-full resize-y rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-800 outline-none transition placeholder:text-gray-400 focus:border-amber-500 focus:ring-2 focus:ring-amber-200 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:ring-amber-900">{{ old('keterangan', $data->keterangan) }}</textarea>
                            @error('keterangan')
                                <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                @include('kalibrasis.partials.bukti-sertifikat-form')

                <div
                    class="flex flex-col-reverse gap-3 border-t border-gray-200 pt-5 dark:border-gray-700 sm:flex-row sm:justify-end">
                    <a href="{{ route('kalibrasis.show', $data->hashid) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"><i
                            class="fas fa-times"></i> Batal</a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-md shadow-blue-200 transition hover:-translate-y-0.5 hover:from-blue-600 hover:to-indigo-700 hover:shadow-lg dark:shadow-none"><i
                            class="fas fa-save"></i> Simpan perubahan</button>
                </div>
            </form>

            <aside class="space-y-4 lg:sticky lg:top-6">
                <div
                    class="rounded-3xl border border-blue-100 bg-blue-50/80 p-5 dark:border-blue-900/50 dark:bg-blue-900/15">
                    <div class="flex items-center gap-2 text-blue-800 dark:text-blue-300"><i
                            class="fas fa-info-circle"></i>
                        <h3 class="font-bold">Perhatikan perubahan</h3>
                    </div>
                    <ul class="mt-3 space-y-3 text-sm text-blue-900 dark:text-blue-200">
                        <li class="flex gap-2"><i class="fas fa-check-circle mt-0.5"></i><span>Pastikan data tanggal
                                sesuai dengan sertifikat.</span></li>
                        <li class="flex gap-2"><i class="fas fa-check-circle mt-0.5"></i><span>File lama tetap tersimpan
                                jika Anda tidak mengunggah file pengganti.</span></li>
                        <li class="flex gap-2"><i class="fas fa-check-circle mt-0.5"></i><span>Perubahan masa berlaku alat
                                mengikuti catatan kalibrasi terbaru.</span></li>
                    </ul>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Terakhir diperbarui</p>
                    <p class="mt-1 font-semibold text-gray-800 dark:text-gray-100">
                        {{ $data->updated_at?->format('d M Y, H:i') ?? '-' }}</p>
                </div>
            </aside>
        </div>
    </div>
@endsection
