@extends('layouts.app')

@section('title', 'Edit Catatan Pengembalian')

@section('content')
    @php
        $pengambilan = $data->pengambilan;
        $item = $data->item;
        $alat = $item->alat ?? ($pengambilan->alat ?? null);
        $peminjam = $pengambilan->nama_peminjam ?? ($pengambilan->user->name ?? '-');
        $unit = $item->satuan ?? ($pengambilan->satuan ?? ($alat->satuan ?? 'unit'));
        $dueDate = $pengambilan->tanggal_jatuh_tempo ? $pengambilan->tanggal_jatuh_tempo->toDateString() : null;
        $returnDate = $data->tanggal_pengembalian ? $data->tanggal_pengembalian->toDateString() : now()->toDateString();
    @endphp

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-7 sm:py-8" x-data="returnEditForm({ dueDate: @js($dueDate), returnDate: @js(old('tanggal_pengembalian', $returnDate)), hasPhoto: @js((bool) $data->foto) })">

        <a href="{{ route('pengembalian_alat.show', $data->hashid) }}"
            class="mb-5 inline-flex items-center gap-2 text-sm font-semibold text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400">
            <span
                class="flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <i class="fas fa-arrow-left"></i>
            </span>
            Kembali ke detail pengembalian
        </a>

        <section
            class="mb-6 rounded-3xl border border-blue-100 dark:border-gray-700 bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900 p-5 sm:p-7">
            <div class="flex items-start gap-4">
                <span
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-lg"><i
                        class="fas fa-pen-to-square text-lg"></i></span>
                <div class="min-w-0">
                    <p class="text-xs font-bold uppercase tracking-[.15em] text-blue-700 dark:text-blue-400">Koreksi data
                    </p>
                    <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">Edit Pengembalian
                        Alat</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Perbarui tanggal, keterangan, atau foto bukti.
                        Jumlah yang tercatat tidak diubah pada halaman ini.</p>
                </div>
            </div>
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="rounded-xl border border-white dark:border-gray-700 bg-white/80 dark:bg-gray-900/50 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Alat</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                        {{ $alat->nama_alat ?? 'Alat tidak tersedia' }}</p>
                </div>
                <div class="rounded-xl border border-white dark:border-gray-700 bg-white/80 dark:bg-gray-900/50 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Peminjam</p>
                    <p class="mt-1 truncate font-semibold text-gray-900 dark:text-white">{{ $peminjam }}</p>
                </div>
                <div class="rounded-xl border border-white dark:border-gray-700 bg-white/80 dark:bg-gray-900/50 p-3">
                    <p class="text-[10px] font-bold uppercase tracking-wide text-gray-400">Jumlah dicatat</p>
                    <p class="mt-1 font-extrabold text-gray-900 dark:text-white">{{ number_format($data->jumlah) }}
                        {{ $unit }}</p>
                </div>
            </div>
        </section>

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-sm text-red-800 dark:text-red-200"
                role="alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 px-4 py-3 text-red-800 dark:text-red-200"
                role="alert">
                <p class="font-semibold text-sm">Periksa kembali data berikut:</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pengembalian_alat.update', $data->hashid) }}" enctype="multipart/form-data"
            class="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
            @csrf
            @method('PUT')
            <div class="space-y-6 p-5 sm:p-7">
                <div>
                    <label for="tanggal_pengembalian"
                        class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">Tanggal pengembalian <span
                            class="text-red-500">*</span></label>
                    <input id="tanggal_pengembalian" name="tanggal_pengembalian" type="date" required
                        x-model="returnDate" value="{{ old('tanggal_pengembalian', $returnDate) }}"
                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500">
                    @error('tanggal_pengembalian')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="isLate" x-transition
                    class="rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 p-4"
                    role="alert">
                    <div class="flex items-start gap-3"><i class="fas fa-triangle-exclamation mt-0.5 text-red-600"></i>
                        <div>
                            <p class="text-sm font-bold text-red-800 dark:text-red-300">Tanggal pengembalian melewati jatuh
                                tempo</p>
                            <p class="mt-1 text-xs text-red-700 dark:text-red-400">Keterangan minimal 10 karakter dan foto
                                bukti wajib tersedia. Foto lama tetap digunakan apabila tidak diganti.</p>
                        </div>
                    </div>
                </div>
                <div x-show="!isLate" x-transition
                    class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/15 p-4">
                    <p class="text-xs text-emerald-800 dark:text-emerald-300"><i class="fas fa-circle-check mr-1"></i>
                        Tanggal yang dipilih tidak melewati jatuh tempo.</p>
                </div>

                <div>
                    <label for="keterangan" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                        Keterangan
                        <span x-show="isLate" class="text-red-500">*</span>
                        <span x-show="!isLate" class="text-xs font-normal text-gray-400">(opsional)</span>
                    </label>
                    <textarea id="keterangan" name="keterangan" rows="4" :required="isLate" :minlength="isLate ? 10 : 0"
                        placeholder="Catatan kondisi alat atau alasan keterlambatan..."
                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-blue-500 focus:ring-blue-500">{{ old('keterangan', $data->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="foto" class="mb-2 block text-sm font-semibold text-gray-800 dark:text-gray-200">
                        Foto bukti
                        <span x-show="isLate && !hasPhoto" class="text-red-500">*</span>
                        <span x-show="!isLate || hasPhoto" class="text-xs font-normal text-gray-400">(opsional bila foto
                            lama tersedia)</span>
                    </label>
                    @if ($data->foto)
                        <div
                            class="mb-3 flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 p-3">
                            <img src="{{ $data->foto_thumb ?? $data->foto_url }}" alt="Foto bukti saat ini"
                                class="h-20 w-20 rounded-lg object-cover">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Foto bukti saat ini</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Pilih file baru hanya jika ingin
                                    mengganti foto.</p>
                            </div>
                        </div>
                    @endif
                    <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp"
                        :required="isLate && !hasPhoto"
                        class="block w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-3 text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:rounded-lg file:border-0 file:bg-blue-100 file:px-3 file:py-2 file:font-semibold file:text-blue-800 hover:file:bg-blue-200">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    @error('foto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div
                class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 border-t border-gray-100 dark:border-gray-700 p-5 sm:px-7">
                <a href="{{ route('pengembalian_alat.show', $data->hashid) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 dark:border-gray-700 px-5 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Batal</a>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"><i
                        class="fas fa-save"></i> Simpan perubahan</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function returnEditForm(config) {
            return {
                dueDate: config.dueDate,
                returnDate: config.returnDate,
                hasPhoto: config.hasPhoto,
                get isLate() {
                    return !!(this.dueDate && this.returnDate && this.returnDate > this.dueDate);
                }
            };
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
@endpush
