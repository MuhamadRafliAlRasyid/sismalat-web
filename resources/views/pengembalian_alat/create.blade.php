@extends('layouts.app')

@section('title', 'Pengembalian Alat')

@section('content')
    @php
        $jatuhTempo = $pengambilan->tanggal_jatuh_tempo
            ? \Carbon\Carbon::parse($pengambilan->tanggal_jatuh_tempo)->translatedFormat('d M Y')
            : 'tidak ditentukan';
        $tanggalAmbil = $pengambilan->waktu_pengambilan
            ? \Carbon\Carbon::parse($pengambilan->waktu_pengambilan)->translatedFormat('d M Y')
            : '-';
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('pengambilan_alat.show', $pengambilan->hashid) }}"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400">
                <i class="fas fa-arrow-left"></i> Kembali ke detail pengambilan
            </a>
        </div>

        {{-- Header Batch --}}
        <div
            class="rounded-3xl border border-amber-100 dark:border-gray-700 bg-gradient-to-br from-amber-50 via-white to-orange-50 dark:from-gray-800 dark:via-gray-800 dark:to-gray-900 p-6 sm:p-8 mb-6">
            <div class="flex items-start gap-4">
                <span
                    class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-lg shrink-0">
                    <i class="fas fa-undo-alt"></i>
                </span>
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Pengembalian Alat</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                        Batch #{{ strtoupper(substr($pengambilan->hashid, 0, 8)) }}
                        · {{ $pengambilan->nama_peminjam ?? ($pengambilan->user->name ?? 'Peminjam') }}
                    </p>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                        <span><i class="fas fa-calendar-day mr-1"></i> Dipinjam: {{ $tanggalAmbil }}</span>
                        <span><i class="fas fa-hourglass-end mr-1"></i> Jatuh tempo: {{ $jatuhTempo }}</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Centang alat yang dikembalikan dan sesuaikan jumlahnya. Anda dapat mengembalikan beberapa alat
                        sekaligus.
                    </p>
                </div>
            </div>
        </div>

        @if (session('error'))
            <div
                class="mb-5 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="mb-5 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
                <p class="font-semibold mb-1">Periksa kembali isian berikut:</p>
                <ul class="list-disc pl-5 text-sm space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('pengembalian_alat.store', $pengambilan->hashid) }}"
            enctype="multipart/form-data"
            class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-5 sm:p-7 space-y-6">
            @csrf

            @if ($isLegacy)
                {{-- ============ TRANSAKSI LAMA (LEGACY) ============ --}}
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-1">Alat</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ $pengambilan->alat->nama_alat ?? 'Alat tidak tersedia' }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Sisa yang belum dikembalikan: <strong>{{ $legacyRemaining }}</strong>
                        {{ $pengambilan->satuan ?? ($pengambilan->alat->satuan ?? 'unit') }}
                    </p>
                </div>
                <div>
                    <label for="jumlah_legacy"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Jumlah yang
                        dikembalikan</label>
                    <input id="jumlah_legacy" name="jumlah" type="number" min="1" max="{{ $legacyRemaining }}"
                        value="{{ old('jumlah', $legacyRemaining) }}" required
                        class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-amber-500 focus:ring-amber-500">
                    @error('jumlah')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @else
                {{-- ============ TRANSAKSI BATCH (MULTI-ITEM) ============ --}}
                @php
                    $availableItems = $items->filter(fn($i) => (int) $i->sisa_pengembalian > 0);
                @endphp

                @if ($availableItems->isNotEmpty())
                    <div>
                        <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">
                                Pilih alat yang akan dikembalikan <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" id="select-all"
                                    class="text-xs font-semibold text-amber-600 hover:text-amber-700 dark:text-amber-400 px-2 py-1 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20 transition">
                                    <i class="fas fa-check-double mr-1"></i> Pilih Semua
                                </button>
                                <span class="text-gray-300 dark:text-gray-600">|</span>
                                <button type="button" id="clear-all"
                                    class="text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                    <i class="fas fa-eraser mr-1"></i> Kosongkan
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach ($availableItems as $item)
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 transition item-row opacity-60"
                                    data-item-id="{{ $item->id }}">
                                    <div class="flex items-start gap-3">
                                        <input type="checkbox" name="items[{{ $item->id }}][selected]" value="1"
                                            id="item_{{ $item->id }}"
                                            class="mt-1 h-5 w-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500 item-checkbox shrink-0"
                                            @checked(old('items.' . $item->id . '.selected'))>

                                        <div class="flex-1 min-w-0">
                                            <label for="item_{{ $item->id }}"
                                                class="block font-semibold text-gray-900 dark:text-white cursor-pointer item-label">
                                                {{ $item->alat->nama_alat ?? 'Alat telah dihapus' }}
                                                @if ($item->alat->merk ?? null)
                                                    <span class="text-gray-500 font-normal">·
                                                        {{ $item->alat->merk }} {{ $item->alat->tipe }}</span>
                                                @endif
                                            </label>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Sisa yang belum dikembalikan: <strong
                                                    class="text-amber-600 dark:text-amber-400">{{ $item->sisa_pengembalian }}</strong>
                                                {{ $item->satuan ?? ($item->alat->satuan ?? 'unit') }}
                                            </p>

                                            <div class="mt-3 flex items-center gap-3 flex-wrap">
                                                <label for="qty_{{ $item->id }}"
                                                    class="text-sm text-gray-600 dark:text-gray-300">Jumlah:</label>
                                                <input type="number" id="qty_{{ $item->id }}"
                                                    name="items[{{ $item->id }}][jumlah]" min="1"
                                                    max="{{ $item->sisa_pengembalian }}"
                                                    value="{{ old('items.' . $item->id . '.jumlah', $item->sisa_pengembalian) }}"
                                                    class="w-24 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-amber-500 focus:ring-amber-500 text-sm item-qty"
                                                    disabled>
                                                <span
                                                    class="text-sm text-gray-500">{{ $item->satuan ?? ($item->alat->satuan ?? 'unit') }}</span>
                                            </div>

                                            @error('items.' . $item->id . '.jumlah')
                                                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('items')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div
                        class="text-center py-8 text-gray-500 dark:text-gray-400 border border-dashed border-gray-300 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                        <i class="fas fa-check-circle text-4xl text-emerald-500 mb-3"></i>
                        <p class="font-semibold text-gray-700 dark:text-gray-200">Semua alat pada batch ini sudah
                            dikembalikan seluruhnya.</p>
                    </div>
                @endif
            @endif

            <div>
                <label for="tanggal_pengembalian"
                    class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Tanggal pengembalian <span
                        class="text-red-500">*</span></label>
                <input id="tanggal_pengembalian" name="tanggal_pengembalian" type="date" required
                    value="{{ old('tanggal_pengembalian', now()->toDateString()) }}"
                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-amber-500 focus:ring-amber-500">
                @error('tanggal_pengembalian')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="keterangan"
                    class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Keterangan</label>
                <textarea id="keterangan" name="keterangan" rows="3"
                    placeholder="Tuliskan kondisi alat atau catatan pengembalian. Wajib diisi minimal 10 karakter jika terlambat."
                    class="w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:border-amber-500 focus:ring-amber-500">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="foto" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Foto bukti
                    pengembalian</label>
                <input id="foto" name="foto" type="file" accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:font-semibold file:text-amber-700 hover:file:bg-amber-100">
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format JPG, PNG, atau WEBP. Maksimal 2 MB. Wajib
                    jika pengembalian terlambat. (Foto ini berlaku untuk semua alat yang dipilih).</p>
                @error('foto')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div
                class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-gray-100 dark:border-gray-700 pt-5">
                <p id="selection-summary" class="text-xs text-gray-500 dark:text-gray-400 order-last sm:order-first">
                    Belum ada alat dipilih
                </p>
                <div class="flex gap-3">
                    <a href="{{ route('pengambilan_alat.show', $pengambilan->hashid) }}"
                        class="inline-flex justify-center items-center px-5 py-3 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 font-semibold hover:bg-gray-50 dark:hover:bg-gray-700">Batal</a>
                    <button type="submit" id="submit-return"
                        class="inline-flex justify-center items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-semibold shadow-sm disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:from-amber-500 disabled:hover:to-orange-500 transition">
                        <i class="fas fa-check"></i> Simpan Pengembalian
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    @unless ($isLegacy)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const checkboxes = document.querySelectorAll('.item-checkbox');
                const summaryEl = document.getElementById('selection-summary');
                const submitBtn = document.getElementById('submit-return');
                const selectAllBtn = document.getElementById('select-all');
                const clearAllBtn = document.getElementById('clear-all');

                if (!checkboxes.length) return;

                function refreshRow(cb) {
                    const row = cb.closest('.item-row');
                    const qty = row.querySelector('.item-qty');
                    const label = row.querySelector('.item-label');
                    if (cb.checked) {
                        qty.disabled = false;
                        row.classList.add('bg-amber-50', 'dark:bg-amber-900/10', 'border-amber-300',
                            'dark:border-amber-700');
                        row.classList.remove('opacity-60');
                        if (label) label.classList.add('text-amber-800', 'dark:text-amber-200');
                    } else {
                        qty.disabled = true;
                        row.classList.remove('bg-amber-50', 'dark:bg-amber-900/10', 'border-amber-300',
                            'dark:border-amber-700');
                        row.classList.add('opacity-60');
                        if (label) label.classList.remove('text-amber-800', 'dark:text-amber-200');
                    }
                }

                function updateSummary() {
                    const checked = Array.from(checkboxes).filter(c => c.checked);
                    let totalUnit = 0;
                    checked.forEach(c => {
                        const qty = c.closest('.item-row').querySelector('.item-qty');
                        totalUnit += parseInt(qty.value || '0', 10);
                    });
                    if (summaryEl) {
                        summaryEl.textContent = checked.length === 0 ?
                            'Belum ada alat dipilih' :
                            checked.length + ' alat dipilih · total ' + totalUnit + ' unit';
                    }
                    if (submitBtn) submitBtn.disabled = checked.length === 0;
                }

                checkboxes.forEach(cb => {
                    const qty = cb.closest('.item-row').querySelector('.item-qty');
                    cb.addEventListener('change', function() {
                        refreshRow(cb);
                        updateSummary();
                    });
                    qty.addEventListener('input', updateSummary);
                    refreshRow(cb); // sinkronkan state awal (termasuk dari old())
                });

                if (selectAllBtn) {
                    selectAllBtn.addEventListener('click', function() {
                        checkboxes.forEach(cb => {
                            cb.checked = true;
                            refreshRow(cb);
                        });
                        updateSummary();
                    });
                }
                if (clearAllBtn) {
                    clearAllBtn.addEventListener('click', function() {
                        checkboxes.forEach(cb => {
                            cb.checked = false;
                            refreshRow(cb);
                        });
                        updateSummary();
                    });
                }

                updateSummary();
            });
        </script>
    @endunless
@endpush
