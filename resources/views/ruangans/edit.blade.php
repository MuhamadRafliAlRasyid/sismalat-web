@extends('layouts.app')

@section('title', 'Edit Ruangan')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-8">
        {{-- Back Button --}}
        <a href="{{ route('ruangans.index') }}"
            class="inline-flex items-center gap-2 text-gray-500 dark:text-gray-400 hover:text-gray-700 mb-6 group transition-colors">
            <span
                class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 group-hover:bg-gray-200 flex items-center justify-center transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </span>
            <span class="font-medium">Kembali</span>
        </a>

        <div
            class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
            {{-- Header --}}
            <div
                class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 p-6 sm:p-8 border-b border-blue-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <span
                        class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-gray-800 shadow-lg shadow-blue-100">
                        <i class="fas fa-edit text-xl text-blue-500"></i>
                    </span>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Ruangan</h2>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-0.5">Perbarui informasi ruangan
                            {{ $ruangan->kode_ruangan }}</p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('ruangans.update', $ruangan->hashid) }}" class="p-6 sm:p-8">
                @csrf @method('PUT')

                <div class="space-y-6">
                    {{-- Kode Ruangan --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <i class="fas fa-hashtag text-blue-600 dark:text-blue-400 text-xs"></i>
                            </span>
                            Kode Ruangan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="kode_ruangan" value="{{ old('kode_ruangan', $ruangan->kode_ruangan) }}"
                            required
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400 text-gray-700 dark:text-gray-200">
                        @error('kode_ruangan')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nama Ruangan --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <i class="fas fa-sign text-blue-600 dark:text-blue-400 text-xs"></i>
                            </span>
                            Nama Ruangan
                        </label>
                        <input type="text" name="nama_ruangan" value="{{ old('nama_ruangan', $ruangan->nama_ruangan) }}"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400 text-gray-700 dark:text-gray-200">
                    </div>

                    {{-- Penanggung Jawab --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <i class="fas fa-user-tie text-blue-600 dark:text-blue-400 text-xs"></i>
                            </span>
                            Penanggung Jawab
                        </label>
                        <input type="text" name="penanggung_jawab"
                            value="{{ old('penanggung_jawab', $ruangan->penanggung_jawab) }}"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400 text-gray-700 dark:text-gray-200">
                    </div>

                    {{-- NIP --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <i class="fas fa-id-card text-blue-600 dark:text-blue-400 text-xs"></i>
                            </span>
                            NIP
                        </label>
                        <input type="text" name="nip" value="{{ old('nip', $ruangan->nip) }}"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400 text-gray-700 dark:text-gray-200">
                    </div>

                    {{-- User (Opsional) --}}
                    <div class="form-group">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                            <span
                                class="w-6 h-6 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <i class="fas fa-user-circle text-blue-600 dark:text-blue-400 text-xs"></i>
                            </span>
                            User Terkait
                        </label>
                        <div class="relative">
                            <select name="user_id"
                                class="w-full appearance-none px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:bg-white dark:focus:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-400 text-gray-700 dark:text-gray-200 pr-10">
                                <option value="">-- Tidak Ada --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ old('user_id', $ruangan->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div
                    class="mt-8 flex flex-col sm:flex-row gap-3 justify-end border-t border-gray-100 dark:border-gray-700 pt-8">
                    <a href="{{ route('ruangans.index') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3.5 border-2 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900 text-gray-700 dark:text-gray-200 font-medium rounded-2xl transition-all duration-300">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-8 py-3.5 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white font-semibold rounded-2xl transition-all duration-300 shadow-lg shadow-blue-200 hover:shadow-xl hover:-translate-y-0.5">
                        <i class="fas fa-save"></i> Update Ruangan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
