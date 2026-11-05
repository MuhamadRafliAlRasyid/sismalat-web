@extends('layouts.app')

@section('title', 'Detail Pengambilan Alat')

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

        $items = $data->items;
        $totalItems = $items->count();
        $returned = $items->where('status', 'dikembalikan')->count();
        $dipinjam = $totalItems - $returned;
        $totalUnit = $items->sum('jumlah');
        $progress = $totalItems > 0 ? round(($returned / $totalItems) * 100) : 0;

        $isSelesai = $data->status === 'dikembalikan';
        $isSebagian = $data->status === 'sebagian_dikembalikan';
        $isAktif = $data->isAktif();
        $statusPinjam = $data->status_pinjam;

        // Warna aksen banner
        $accent = match (true) {
            $isSelesai => 'emerald',
            $isSebagian => 'blue',
            $statusPinjam === 'terlambat' => 'red',
            $data->should_warn_1day && $isAktif => 'red',
            $data->should_warn_15 && $isAktif => 'orange',
            default => 'amber',
        };

        // Alat dalam batch yang perlu kalibrasi (untuk tombol global)
        $alatsPerluKalibrasi = $items
            ->filter(fn($i) => $i->alat && in_array($i->alat->status, ['expired', 'warning']))
            ->pluck('alat')
            ->unique('id');
    @endphp

    <div class="max-w-5xl mx-auto px-4 py-8" x-data="{ imgModal: false, imgSrc: '', imgTitle: '', openImg(src, title = '') { this.imgSrc = src;
            this.imgTitle = title;
            this.imgModal = true }, copied: false, copyTracking() { navigator.clipboard.writeText('{{ $data->tracking_token_hash ? url('/peminjaman/status/' . $data->tracking_token_hash) : '' }}').then(() => { this.copied = true;
                setTimeout(() => this.copied = false, 2000) }) } }">

        {{-- Back --}}
        <a href="{{ route('pengambilan_alat.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 dark:group-hover:bg-gray-700 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali ke Daftar</span>
        </a>

        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">

            {{-- ═══════ HEADER ═══════ --}}
            <div
                class="relative bg-gradient-to-br from-{{ $accent }}-50 via-white to-{{ $accent }}-50/40 dark:from-{{ $accent }}-900/20 dark:via-gray-800 dark:to-{{ $accent }}-900/10 p-6 sm:p-8 border-b border-{{ $accent }}-100 dark:border-gray-700">
                <div
                    class="absolute top-0 right-0 w-40 h-40 bg-{{ $accent }}-200/30 dark:bg-{{ $accent }}-500/10 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl">
                </div>

                <div class="relative flex items-start gap-4">
                    <span
                        class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-{{ $accent }}-100 dark:shadow-none flex-shrink-0">
                        <i
                            class="fas {{ $isSelesai ? 'fa-check-circle' : ($isSebagian ? 'fa-undo-alt' : 'fa-hand-holding') }} text-2xl text-{{ $accent }}-500"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-mono text-gray-400 dark:text-gray-500">BATCH
                            #{{ strtoupper(substr($data->hashid, 0, 8)) }}</p>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 truncate">
                            {{ $data->nama_peminjam ?? ($data->user->name ?? 'Peminjam Tidak Diketahui') }}
                        </h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">
                            {{ $data->bagian->nama ?? '-' }}
                            @if ($data->user && $data->user->email)
                                • {{ $data->user->email }}
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Badge status --}}
                <div class="relative mt-4 flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 px-4 py-2 bg-{{ $accent }}-50 dark:bg-{{ $accent }}-900/30 text-{{ $accent }}-700 dark:text-{{ $accent }}-300 rounded-xl text-sm font-semibold ring-1 ring-{{ $accent }}-200 dark:ring-{{ $accent }}-800">
                        @if ($isAktif)
                        <span class="w-2 h-2 rounded-full bg-{{ $accent }}-500 animate-pulse"></span>@else<span
                                class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        @endif
                        {{ $data->status_label }}
                    </span>

                    @if ($isAktif && $data->lama_pinjam)
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold ring-1
                            {{ $statusPinjam === 'terlambat'
                                ? 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 ring-red-200 dark:ring-red-800 animate-pulse'
                                : ($statusPinjam === 'warning'
                                    ? 'bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 ring-yellow-200 dark:ring-yellow-800'
                                    : 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 ring-emerald-200 dark:ring-emerald-800') }}">
                            <i
                                class="fas {{ $statusPinjam === 'terlambat' ? 'fa-times-circle' : ($statusPinjam === 'warning' ? 'fa-exclamation-triangle' : 'fa-check-circle') }}"></i>
                            {{ $data->sisa_hari_label }}
                        </span>
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-xl text-sm font-semibold ring-1 ring-blue-200 dark:ring-blue-800">
                            <i class="fas fa-calendar-check"></i>
                            Jatuh Tempo: {{ $data->tanggal_jatuh_tempo?->format('d M Y') ?? '-' }}
                        </span>
                    @endif

                    {{-- Peringatan kalibrasi ringkas (jika ada) --}}
                    @if ($alatsPerluKalibrasi->isNotEmpty())
                        <span
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-xl text-sm font-semibold ring-1 ring-red-200 dark:ring-red-800">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $alatsPerluKalibrasi->count() }} alat perlu kalibrasi
                        </span>
                    @endif
                </div>

                {{-- Progress pengembalian --}}
                @if ($isSebagian || ($isAktif && $returned > 0))
                    <div class="relative mt-5">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                            <span class="font-medium">Progres pengembalian</span>
                            <span
                                class="font-bold text-{{ $accent }}-600 dark:text-{{ $accent }}-400">{{ $returned }}/{{ $totalItems }}
                                alat ({{ $progress }}%)</span>
                        </div>
                        <div
                            class="w-full h-2 bg-white/70 dark:bg-gray-900 rounded-full overflow-hidden ring-1 ring-{{ $accent }}-100 dark:ring-gray-700">
                            <div class="h-full bg-gradient-to-r from-{{ $accent }}-400 to-{{ $accent }}-600 rounded-full transition-all duration-700"
                                style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ═══════ CONTENT ═══════ --}}
            <div class="p-6 sm:p-8 space-y-8">

                {{-- ── INFO UTAMA ── --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                                    class="fas fa-calendar-alt text-amber-600 dark:text-amber-400 text-sm"></i></span>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Waktu
                                Pengambilan</span>
                        </div>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">
                            {{ $data->waktu_pengambilan?->format('d M Y, H:i') ?? '-' }}</p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                                    class="fas fa-hourglass-half text-amber-600 dark:text-amber-400 text-sm"></i></span>
                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Lama
                                Pinjam</span>
                        </div>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $data->lama_pinjam ?? '-' }} hari</p>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                                    class="fas fa-boxes text-amber-600 dark:text-amber-400 text-sm"></i></span>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total</span>
                        </div>
                        <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $totalItems }} jenis •
                            {{ $totalUnit }} unit</p>
                    </div>

                    <div
                        class="md:col-span-2 lg:col-span-3 bg-gray-50 dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-3 mb-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                                    class="fas fa-clipboard-list text-amber-600 dark:text-amber-400 text-sm"></i></span>
                            <span
                                class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Keperluan</span>
                        </div>
                        <p class="text-gray-800 dark:text-gray-100 leading-relaxed">{{ $data->keperluan ?? '-' }}</p>
                    </div>
                </div>

                {{-- ── DAFTAR ALAT (INTI) ── --}}
                <div class="border-t border-gray-100 dark:border-gray-700 pt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                                    class="fas fa-list-check text-amber-600 dark:text-amber-400 text-sm"></i></span>
                            Daftar Alat dalam Batch
                        </h3>
                        <span
                            class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2.5 py-1 rounded-full">
                            {{ $returned }} kembali / {{ $totalItems }}
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse ($items as $it)
                            @php
                                $kembali = $it->status === 'dikembalikan';
                                $alat = $it->alat;
                                $perluKal = $alat && in_array($alat->status, ['expired', 'warning']);
                            @endphp

                            <div
                                class="rounded-2xl border overflow-hidden transition-all
                                {{ $kembali
                                    ? 'bg-emerald-50/40 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800'
                                    : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 hover:border-amber-300 dark:hover:border-amber-700 hover:shadow-md' }}">

                                {{-- Baris utama item --}}
                                <div class="flex items-start gap-4 p-4">
                                    {{-- Thumbnail --}}
                                    <div
                                        class="w-14 h-14 rounded-xl bg-gray-100 dark:bg-gray-800 flex-shrink-0 overflow-hidden border border-gray-200 dark:border-gray-700 flex items-center justify-center">
                                        @if ($alat?->foto)
                                            <img src="{{ $alat->foto_thumb }}" alt="{{ $alat->nama_alat }}" loading="lazy"
                                                class="w-full h-full object-cover cursor-pointer"
                                                @click="openImg('{{ $alat->foto_url }}', '{{ addslashes($alat->nama_alat) }}')"
                                                onerror="this.remove(); this.parentElement.innerHTML='<i class=\'fas fa-tools text-gray-400 text-xl\'></i>'">
                                        @else
                                            <i class="fas fa-tools text-gray-400 text-xl"></i>
                                        @endif
                                    </div>

                                    {{-- Detail alat --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-bold text-gray-800 dark:text-gray-100">
                                                {{ $alat->nama_alat ?? 'Alat telah dihapus' }}</p>
                                            <span
                                                class="text-sm font-extrabold text-amber-600 dark:text-amber-400">×{{ $it->jumlah }}
                                                <span
                                                    class="text-xs font-normal text-gray-400">{{ $it->satuan }}</span></span>
                                        </div>
                                        @if ($alat)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                                {{ $alat->merk ?? '-' }}
                                                @if ($alat->tipe && $alat->tipe !== '-')
                                                    • {{ $alat->tipe }}
                                                @endif
                                                • Seri: {{ $alat->no_seri ?? '-' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                                                @if ($alat->kelas && $alat->kelas !== '-')
                                                    <span
                                                        class="text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">Kelas
                                                        {{ $alat->kelas }}</span>
                                                @endif
                                                @if ($alat->kapasitas && $alat->kapasitas !== '-')
                                                    <span
                                                        class="text-[10px] font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">{{ $alat->kapasitas }}</span>
                                                @endif
                                                @if ($perluKal)
                                                    <span
                                                        class="text-[10px] font-bold bg-{{ $alat->status === 'expired' ? 'red' : 'yellow' }}-100 dark:bg-{{ $alat->status === 'expired' ? 'red-900/40' : 'yellow-900/40' }} text-{{ $alat->status === 'expired' ? 'red' : 'yellow' }}-700 dark:text-{{ $alat->status === 'expired' ? 'red' : 'yellow' }}-300 px-2 py-0.5 rounded-full">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        {{ $alat->status === 'expired' ? 'Kalibrasi Expired' : 'Kalibrasi Warning' }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Status + aksi --}}
                                    <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold
                                            {{ $kembali
                                                ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300'
                                                : 'bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300' }}">
                                            <i class="fas {{ $kembali ? 'fa-check-circle' : 'fa-clock' }} text-[10px]"></i>
                                            {{ $it->status_label }}
                                        </span>

                                        @if (!$kembali && $isAdmin)
                                            <a href="{{ route('pengembalian_alat.create', $data->hashid) }}?item={{ $it->hashid }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-white transition shadow-sm">
                                                <i class="fas fa-undo-alt text-[10px]"></i> Kembalikan
                                            </a>
                                        @endif

                                        @if ($perluKal && $alat && $isAdmin)
                                            <a href="{{ route('kalibrasis.create', $alat->hashid) }}"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-yellow-500 hover:bg-yellow-600 text-white transition shadow-sm">
                                                <i class="fas fa-wrench text-[10px]"></i> Kalibrasi
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- Detail pengembalian (jika sudah kembali) --}}
                                @if ($kembali)
                                    <div class="px-4 pb-4 pt-0">
                                        <div
                                            class="ml-18 rounded-xl bg-emerald-50/60 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800/50 p-3.5">
                                            <div class="flex items-start gap-4 flex-wrap">
                                                <div
                                                    class="flex items-center gap-2 text-xs text-emerald-700 dark:text-emerald-300">
                                                    <i class="fas fa-rotate-left"></i>
                                                    <span>Dikembalikan:
                                                        <strong>{{ $it->waktu_pengembalian?->format('d M Y, H:i') ?? '-' }}</strong></span>
                                                </div>
                                                @if ($it->catatan_pengembalian)
                                                    <div
                                                        class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                        <i class="fas fa-note-sticky text-amber-500"></i>
                                                        <span>Catatan: <em>"{{ $it->catatan_pengembalian }}"</em></span>
                                                    </div>
                                                @endif
                                                @if ($it->foto_pengembalian)
                                                    <button type="button"
                                                        @click="openImg('{{ asset('storage/pengembalian/' . $it->foto_pengembalian) }}', 'Foto pengembalian {{ addslashes($alat->nama_alat ?? '') }}')"
                                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-800 transition">
                                                        <i class="fas fa-camera"></i> Lihat foto bukti
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div
                                class="text-center py-10 text-gray-400 border border-dashed border-gray-200 dark:border-gray-700 rounded-2xl">
                                <i class="fas fa-box-open text-3xl mb-2 block"></i>
                                <p class="text-sm">Batch ini tidak memiliki item alat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ── FOTO BUKTI PENGAMBILAN (BATCH) ── --}}
                @if ($data->foto)
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-8">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                                    class="fas fa-camera text-amber-600 dark:text-amber-400 text-sm"></i></span>
                            Foto Bukti Pengambilan
                        </h3>
                        <div class="flex justify-center">
                            <div class="relative group cursor-pointer"
                                @click="openImg('{{ $data->foto_url }}', 'Foto bukti pengambilan')">
                                <img src="{{ $data->foto_url }}" alt="Foto bukti"
                                    class="max-w-full max-h-80 object-contain rounded-2xl border shadow-md transition-transform duration-300 group-hover:scale-105">
                                <div
                                    class="absolute inset-0 bg-black/0 group-hover:bg-black/5 rounded-2xl transition-colors flex items-center justify-center">
                                    <span
                                        class="text-white bg-black/50 px-3 py-1 rounded-full text-xs opacity-0 group-hover:opacity-100 transition-opacity">Klik
                                        untuk memperbesar</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── KONTAK & TRACKING (eksternal / publik) ── --}}
                @if ($data->tipe_peminjam === 'eksternal' || $data->tracking_token_hash)
                    <div class="border-t border-gray-100 dark:border-gray-700 pt-8">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4 flex items-center gap-2">
                            <span
                                class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                                    class="fas fa-satellite-dish text-amber-600 dark:text-amber-400 text-sm"></i></span>
                            Kontak &amp; Pelacakan
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                class="bg-gray-50 dark:bg-gray-900 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 space-y-2 text-sm">
                                <p><span class="text-gray-400">Jenis:</span> <strong
                                        class="text-gray-700 dark:text-gray-200">{{ ucfirst($data->tipe_peminjam ?? 'internal') }}</strong>
                                </p>
                                @if ($data->instansi_peminjam)
                                    <p><span class="text-gray-400">Instansi:</span> <strong
                                            class="text-gray-700 dark:text-gray-200">{{ $data->instansi_peminjam }}</strong>
                                    </p>
                                @endif
                                @if ($data->email_peminjam)
                                    <p><span class="text-gray-400">Email:</span> <a
                                            href="mailto:{{ $data->email_peminjam }}"
                                            class="text-amber-600 dark:text-amber-400 hover:underline">{{ $data->email_peminjam }}</a>
                                    </p>
                                @endif
                                @if ($data->telepon_peminjam)
                                    <p><span class="text-gray-400">Telepon:</span> <a
                                            href="tel:{{ $data->telepon_peminjam }}"
                                            class="text-amber-600 dark:text-amber-400 hover:underline">{{ $data->telepon_peminjam }}</a>
                                    </p>
                                @endif
                                @if ($data->kanal_notifikasi)
                                    <p><span class="text-gray-400">Kanal notif:</span> <strong
                                            class="text-gray-700 dark:text-gray-200 capitalize">{{ $data->kanal_notifikasi }}</strong>
                                    </p>
                                @endif
                            </div>

                            @if ($data->tracking_token_hash)
                                <div
                                    class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl p-5 border border-amber-200 dark:border-amber-800 flex flex-col justify-center gap-3">
                                    <p
                                        class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">
                                        <i class="fas fa-link mr-1"></i> Link Pelacakan Publik
                                    </p>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 break-all font-mono">
                                        {{ url('/peminjaman/status/' . $data->tracking_token_hash) }}
                                    </p>
                                    <button type="button" @click="copyTracking()"
                                        class="self-start inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition
                                            {{ $errors->any() ? '' : '' }}
                                            bg-white dark:bg-gray-800 border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-gray-700">
                                        <i class="fas" :class="copied ? 'fa-check' : 'fa-copy'"></i>
                                        <span x-text="copied ? 'Tersalin!' : 'Salin Link'"></span>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- ── ACTION BUTTONS ── --}}
                <div class="border-t border-gray-100 dark:border-gray-700 pt-8 flex flex-col sm:flex-row gap-3">
                    @if ($isAktif && $dipinjam > 0)
                        <a href="{{ route('pengembalian_alat.create', $data->hashid) }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-emerald-200 dark:shadow-none hover:shadow-xl hover:-translate-y-0.5">
                            <i class="fas fa-undo-alt"></i>
                            <span>Kembalikan Alat</span>
                            <span class="bg-white/20 px-2 py-0.5 rounded-full text-xs font-bold">{{ $dipinjam }}
                                tersisa</span>
                        </a>
                    @endif

                    @if ($alatsPerluKalibrasi->isNotEmpty() && $isAdmin)
                        <a href="{{ route('kalibrasis.create', $alatsPerluKalibrasi->first()->hashid) }}"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-300 shadow-lg shadow-yellow-200 dark:shadow-none hover:shadow-xl hover:-translate-y-0.5">
                            <i class="fas fa-wrench"></i>
                            Kalibrasi {{ $alatsPerluKalibrasi->count() }} Alat
                        </a>
                    @endif

                    <a href="{{ route('pengambilan_alat.export.single', $data->hashid) }}" target="_blank"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-white dark:bg-gray-800 border-2 border-amber-200 dark:border-amber-700 hover:bg-amber-50 dark:hover:bg-gray-700 text-amber-700 dark:text-amber-400 font-semibold py-4 px-6 rounded-2xl transition-all duration-300 hover:border-amber-300">
                        <i class="fas fa-print"></i>
                        Cetak Bukti
                    </a>

                    @if ($isAdmin && !$isSelesai)
                        <a href="{{ route('pengambilan_alat.edit', $data->hashid) }}"
                            class="inline-flex items-center justify-center gap-2 bg-blue-50 dark:bg-blue-900/30 border-2 border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-300 font-semibold py-4 px-6 rounded-2xl transition-all duration-300">
                            <i class="fas fa-pen-to-square"></i>
                            Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ═══════ LIGHTBOX ═══════ --}}
        <div x-show="imgModal" x-cloak x-transition.opacity.duration.300ms
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4"
            @click.away="imgModal = false; imgSrc = ''" @keydown.escape.window="imgModal = false; imgSrc = ''">
            <div class="relative max-w-5xl w-full max-h-full flex flex-col items-center">
                <button @click="imgModal = false; imgSrc = ''"
                    class="absolute -top-3 -right-3 w-10 h-10 bg-white dark:bg-gray-800 rounded-full shadow-lg flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white transition z-10">
                    <i class="fas fa-times"></i>
                </button>
                <img :src="imgSrc" :alt="imgTitle"
                    class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl">
                <p x-show="imgTitle" x-text="imgTitle" class="mt-3 text-sm text-white/80 font-medium"></p>
            </div>
        </div>
    </div>
@endsection
