{{-- Partial upload sertifikat untuk form create dan edit. --}}
<div x-data="{
    selectedName: '',
    selectedUrl: '',
    selectedIsPdf: false,
    onCertificateChange(event) {
        const file = event.target.files && event.target.files[0];
        if (this.selectedUrl && this.selectedUrl.startsWith('blob:')) URL.revokeObjectURL(this.selectedUrl);
        this.selectedName = '';
        this.selectedUrl = '';
        this.selectedIsPdf = false;
        if (!file) return;
        this.selectedName = file.name;
        this.selectedIsPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        if (!this.selectedIsPdf && file.type.startsWith('image/')) this.selectedUrl = URL.createObjectURL(file);
    }
}"
    class="rounded-3xl border border-emerald-200 bg-white p-5 shadow-sm dark:border-emerald-900/60 dark:bg-gray-800 sm:p-6">
    <div class="mb-4 flex items-start gap-3">
        <span
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300"><i
                class="fas fa-file-certificate text-lg"></i></span>
        <div>
            <h2 class="font-bold text-gray-900 dark:text-white">Bukti sertifikat kalibrasi</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Lampirkan sertifikat resmi dari laboratorium agar
                riwayat kalibrasi memiliki dokumen pendukung.</p>
        </div>
    </div>

    @if (isset($data) && $data->bukti_sertifikat)
        <div
            class="mb-4 flex flex-col gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-3 dark:border-emerald-800 dark:bg-emerald-900/15 sm:flex-row sm:items-center">
            @if ($data->bukti_sertifikat_is_pdf)
                <span
                    class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-300"><i
                        class="fas fa-file-pdf text-3xl"></i></span>
            @else
                <img src="{{ $data->bukti_sertifikat_url }}" alt="Sertifikat saat ini"
                    class="h-20 w-20 shrink-0 rounded-xl border border-gray-200 bg-white object-cover dark:border-gray-700">
            @endif
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Bukti sertifikat saat ini</p>
                <p class="mt-1 break-all text-xs text-gray-600 dark:text-gray-400">{{ $data->bukti_sertifikat_name }}
                </p>
                <a href="{{ $data->bukti_sertifikat_url }}" target="_blank" rel="noopener"
                    class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:underline dark:text-emerald-400"><i
                        class="fas fa-external-link-alt"></i> Buka file tersimpan</a>
            </div>
            <span
                class="inline-flex items-center gap-1 self-start rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300"><i
                    class="fas fa-check-circle"></i> Tersimpan</span>
        </div>
    @endif

    <label for="bukti_sertifikat" class="mb-1.5 block text-sm font-semibold text-gray-700 dark:text-gray-200">
        {{ isset($data) && $data->bukti_sertifikat ? 'Ganti file sertifikat (opsional)' : 'Pilih file sertifikat' }}
        @if (!isset($data) || !$data->bukti_sertifikat)
            <span class="text-red-500">*</span>
        @endif
    </label>
    <input id="bukti_sertifikat" name="bukti_sertifikat" type="file"
        accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf"
        @change="onCertificateChange($event)" @if (!isset($data) || !$data->bukti_sertifikat) required @endif
        class="block w-full cursor-pointer rounded-xl border border-gray-300 bg-white text-sm text-gray-700 file:mr-4 file:cursor-pointer file:border-0 file:bg-emerald-50 file:px-4 file:py-3 file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-200 dark:file:bg-emerald-900/30 dark:file:text-emerald-300">
    @error('bukti_sertifikat')
        <p class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400">Format JPG, JPEG, PNG, WEBP, atau PDF. Ukuran maksimal 5
        MB.</p>

    <div x-show="selectedName" x-cloak
        class="mt-4 rounded-2xl border border-blue-200 bg-blue-50/70 p-3 dark:border-blue-800 dark:bg-blue-900/15">
        <p class="mb-2 text-xs font-bold uppercase tracking-wide text-blue-800 dark:text-blue-300">Pratinjau file yang
            dipilih</p>
        <template x-if="selectedUrl && !selectedIsPdf">
            <img :src="selectedUrl" alt="Pratinjau sertifikat"
                class="max-h-64 w-full rounded-xl bg-white object-contain dark:bg-gray-900">
        </template>
        <template x-if="selectedIsPdf">
            <div class="flex items-center gap-3 rounded-xl bg-white p-3 dark:bg-gray-800">
                <i class="fas fa-file-pdf text-3xl text-red-500"></i>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Dokumen PDF dipilih</p>
                    <p class="break-all text-xs text-gray-500" x-text="selectedName"></p>
                </div>
            </div>
        </template>
        <p x-show="selectedUrl && !selectedIsPdf" class="mt-2 break-all text-xs text-gray-500 dark:text-gray-400"
            x-text="selectedName"></p>
    </div>

    @if (isset($data) && $data->bukti_sertifikat)
        <p
            class="mt-3 flex items-start gap-2 rounded-xl bg-gray-50 p-3 text-xs text-gray-500 dark:bg-gray-900 dark:text-gray-400">
            <i class="fas fa-info-circle mt-0.5"></i><span>Kosongkan pilihan file jika ingin mempertahankan sertifikat
                yang sekarang. File lama akan diganti setelah perubahan berhasil disimpan.</span>
        </p>
    @else
        <p
            class="mt-3 flex items-start gap-2 rounded-xl bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
            <i class="fas fa-info-circle mt-0.5"></i><span>Bukti sertifikat wajib dilampirkan pada pencatatan kalibrasi
                baru.</span>
        </p>
    @endif
</div>
