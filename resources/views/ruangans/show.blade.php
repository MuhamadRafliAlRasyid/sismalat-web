@extends('layouts.app')

@section('title', 'Detail Ruangan ' . $ruangan->kode_ruangan)

@section('content')
    <div class="max-w-6xl mx-auto px-4 py-8">
        <a href="{{ route('ruangans.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali ke Daftar</span>
        </a>

        {{-- Header Card --}}
        <div
            class="relative bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 dark:from-amber-900/20 dark:via-orange-900/20 dark:to-yellow-900/20 rounded-3xl p-6 sm:p-8 mb-8 border border-amber-100 dark:border-gray-700 overflow-hidden">
            <div
                class="absolute top-0 right-0 w-40 h-40 bg-amber-200/30 rounded-full -translate-y-1/2 translate-x-1/2 blur-2xl">
            </div>
            <div class="relative flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    <span
                        class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-amber-100">
                        <i class="fas fa-door-open text-3xl text-amber-500"></i>
                    </span>
                    <div>
                        <h2 class="text-3xl font-bold text-gray-800 dark:text-gray-100">{{ $ruangan->kode_ruangan }}</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                            {{ $ruangan->nama_ruangan ?? 'Ruangan ' . $ruangan->kode_ruangan }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('ruangans.edit', $ruangan->hashid) }}"
                        class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 hover:bg-amber-50 text-amber-600 border border-amber-200 hover:border-amber-300 px-5 py-3 rounded-2xl font-medium transition-all duration-300 shadow-sm hover:shadow-md">
                        <i class="fas fa-edit"></i><span>Edit</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Info Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div
                class="group bg-white dark:bg-gray-800 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                            class="fas fa-user-tie text-amber-600 dark:text-amber-400"></i></span>
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Penanggung
                        Jawab</span>
                </div>
                <p class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $ruangan->penanggung_jawab ?? '-' }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $ruangan->nip ?? '-' }}</p>
            </div>

            <div
                class="group bg-white dark:bg-gray-800 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                            class="fas fa-boxes text-amber-600 dark:text-amber-400"></i></span>
                    <span class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total
                        Alat</span>
                </div>
                <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['total_items'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">jenis barang terdaftar</p>
            </div>

            <div
                class="group bg-white dark:bg-gray-800 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-2xl p-5 border border-gray-100 dark:border-gray-700 hover:border-amber-200 hover:shadow-md transition-all duration-300">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center"><i
                            class="fas fa-chart-pie text-amber-600 dark:text-amber-400"></i></span>
                    <span
                        class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Kondisi</span>
                </div>
                <div class="flex gap-3 text-sm">
                    <div><span
                            class="text-emerald-600 dark:text-emerald-400 font-bold">{{ $stats['total_baik'] }}</span><span
                            class="text-xs text-gray-500"> Baik</span></div>
                    <div><span
                            class="text-yellow-600 dark:text-yellow-400 font-bold">{{ $stats['total_kurang_baik'] }}</span><span
                            class="text-xs text-gray-500"> KB</span></div>
                    <div><span
                            class="text-red-600 dark:text-red-400 font-bold">{{ $stats['total_rusak_berat'] }}</span><span
                            class="text-xs text-gray-500"> RB</span></div>
                </div>
            </div>
        </div>

        {{-- Daftar Alat di Ruangan --}}
        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-6 sm:p-8 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                    <i class="fas fa-list text-amber-500"></i> Daftar Barang di Ruangan Ini
                </h3>
            </div>

            <div class="p-6 sm:p-8">
                @if ($ruangan->alats->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Jenis
                                        Barang</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Merk/Tipe
                                    </th>
                                    <th class="text-center py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Jumlah
                                    </th>
                                    <th class="text-center py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Baik
                                    </th>
                                    <th class="text-center py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">KB</th>
                                    <th class="text-center py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">RB</th>
                                    <th class="text-center py-3 px-4 font-semibold text-gray-600 dark:text-gray-300">Tahun
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($ruangan->alats as $alat)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="py-3 px-4">
                                            <div class="font-medium text-gray-800 dark:text-gray-100">
                                                {{ $alat->nama_alat }}</div>
                                            <div class="text-xs text-gray-500">{{ $alat->ukuran ?? '-' }}
                                                {{ $alat->satuan ?? '' }}</div>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600 dark:text-gray-300">{{ $alat->merk }}
                                            {{ $alat->tipe }}</td>
                                        <td class="py-3 px-4 text-center font-semibold">{{ $alat->jumlah }}</td>
                                        <td class="py-3 px-4 text-center"><span
                                                class="inline-flex items-center px-2 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-xs font-medium">{{ $alat->jumlah_baik }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center"><span
                                                class="inline-flex items-center px-2 py-1 rounded-lg bg-yellow-50 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300 text-xs font-medium">{{ $alat->jumlah_kurang_baik }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center"><span
                                                class="inline-flex items-center px-2 py-1 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs font-medium">{{ $alat->jumlah_rusak_berat }}</span>
                                        </td>
                                        <td class="py-3 px-4 text-center text-gray-600 dark:text-gray-300">
                                            {{ $alat->tahun_pengadaan ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div
                            class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                            <i class="fas fa-box-open text-3xl text-gray-400"></i>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-2">Belum Ada Barang</h4>
                        <p class="text-gray-400 dark:text-gray-500 mb-4">Ruangan ini belum memiliki barang terdaftar</p>
                        <a href="{{ route('alats.create') }}?ruangan_hashid={{ $ruangan->hashid }}"
                            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-2xl font-medium transition-all shadow-lg shadow-amber-200">
                            <i class="fas fa-plus"></i> Tambah Barang
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
