<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
    <div class="mb-4 flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
            <i class="fas fa-file-certificate"></i>
        </span>
        <div>
            <h3 class="font-semibold text-gray-900 dark:text-white">Bukti Sertifikat Kalibrasi</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Dokumen pendukung hasil kalibrasi</p>
        </div>
    </div>

    @if ($data->bukti_sertifikat)
        @if ($data->bukti_sertifikat_is_pdf)
            <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900 sm:flex-row sm:items-center">
                <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-300">
                    <i class="fas fa-file-pdf text-3xl"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <p class="break-all text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $data->bukti_sertifikat_name }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Dokumen PDF sertifikat kalibrasi</p>
                </div>
                <a href="{{ $data->bukti_sertifikat_url }}" target="_blank" rel="noopener"
                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                    <i class="fas fa-eye"></i> Buka sertifikat
                </a>
            </div>
        @else
            <a href="{{ $data->bukti_sertifikat_url }}" target="_blank" rel="noopener" class="group block">
                <img src="{{ $data->bukti_sertifikat_url }}" alt="Foto sertifikat kalibrasi"
                    class="max-h-[520px] w-full rounded-xl border border-gray-200 bg-gray-50 object-contain dark:border-gray-700 dark:bg-gray-900">
                <span class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 group-hover:text-emerald-800 dark:text-emerald-400">
                    <i class="fas fa-search-plus"></i> Buka gambar ukuran penuh
                </span>
            </a>
        @endif
    @else
        <div class="rounded-xl border border-dashed border-amber-300 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
            <i class="fas fa-info-circle mr-1"></i>
            Belum ada bukti sertifikat yang diunggah untuk catatan kalibrasi ini.
        </div>
    @endif
</div>
