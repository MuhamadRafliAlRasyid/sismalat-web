<!DOCTYPE html>
<html lang="id" class="scroll-smooth" x-data="{ darkMode: $persist(false).as('darkMode') }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sistem Manajemen Alat Metrologi – Tanaoroshi</title>
    <meta name="description"
        content="Sistem informasi manajemen alat ukur dan kalibrasi milik Dinas Perindustrian dan Perdagangan Kabupaten Karawang." />
    <link rel="icon" href="{{ asset('images/logos.jpg') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f'
                        },
                        dark: {
                            bg: '#09090b',
                            surface: '#18181b',
                            border: '#27272a'
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(0, 0, 0, 0.05)',
                        'glow': '0 0 20px rgba(245, 158, 11, 0.15)',
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #3f3f46;
        }

        .text-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body
    class="bg-gray-50 text-gray-800 dark:bg-dark-bg dark:text-gray-100 font-sans antialiased transition-colors duration-300">

    {{-- Tombol Dark Mode --}}
    <div class="fixed top-5 right-5 z-50">
        <button @click="darkMode = !darkMode"
            class="p-2.5 rounded-full bg-white/80 dark:bg-dark-surface/80 backdrop-blur-md border border-gray-200 dark:border-dark-border text-gray-600 dark:text-gray-300 hover:text-brand-500 transition-all shadow-soft">
            <i class="fas" :class="darkMode ? 'fa-sun' : 'fa-moon'"></i>
        </button>
    </div>

    <!-- Navbar Glassmorphism -->
    <header x-data="{ open: false, scrolled: false }" x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)" class="fixed w-full z-40 transition-all duration-300"
        :class="scrolled ?
            'bg-white/80 dark:bg-dark-bg/80 backdrop-blur-xl shadow-soft border-b border-gray-200/50 dark:border-dark-border' :
            'bg-transparent'">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logos.jpg') }}" alt="Tanaoroshi" class="h-10 w-auto rounded-lg" />
                    <span class="text-xl font-bold text-gray-900 dark:text-white">Sismalat</span>
                </div>

                <div class="hidden md:flex items-center gap-1 text-sm font-medium">
                    <a href="#hero"
                        class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-dark-surface transition">Beranda</a>
                    <a href="#fitur"
                        class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-dark-surface transition">Fitur</a>
                    <a href="#alur"
                        class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-dark-surface transition">Alur</a>
                    <a href="#alat"
                        class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-dark-surface transition">Alat</a>
                    <a href="#peminjaman"
                        class="inline-flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-full font-semibold transition shadow-soft hover:shadow-glow">
                        <i class="fas fa-hand-holding text-xs"></i> Pinjam Alat
                    </a>
                    <a href="#tentang"
                        class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-dark-surface transition">Tentang</a>
                    <a href="#statistik"
                        class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-dark-surface transition">Statistik</a>
                    <a href="#kontak"
                        class="px-3 py-2 rounded-lg text-gray-600 dark:text-gray-300 hover:text-brand-600 hover:bg-gray-100 dark:hover:bg-dark-surface transition">Kontak</a>

                    @auth
                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}"
                                class="ml-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-full font-semibold transition hover:opacity-90">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        @elseif(auth()->user()->role === 'karyawan')
                            <a href="{{ route('karyawan.dashboard') }}"
                                class="ml-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-full font-semibold transition hover:opacity-90">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        @else
                            <a href="{{ route('super.dashboard') }}"
                                class="ml-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-full font-semibold transition hover:opacity-90">
                                <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline ml-2">
                            @csrf
                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="ml-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-full font-semibold transition hover:opacity-90">
                            <i class="fas fa-sign-in-alt mr-1"></i> Masuk
                        </a>
                    @endauth
                </div>

                <button @click="open = !open" class="md:hidden p-2 text-gray-700 dark:text-gray-300">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>

            <div x-show="open" x-transition
                class="md:hidden py-4 space-y-2 bg-white dark:bg-dark-surface rounded-b-2xl shadow-lg px-4 border-t border-gray-100 dark:border-dark-border">
                <a href="#hero" @click="open = false"
                    class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300">Beranda</a>
                <a href="#fitur" @click="open = false"
                    class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300">Fitur</a>
                <a href="#alur" @click="open = false"
                    class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300">Alur</a>
                <a href="#alat" @click="open = false"
                    class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300">Alat</a>
                <a href="#peminjaman" @click="open = false"
                    class="block rounded-xl bg-brand-500 text-white px-4 py-3 font-semibold text-center">
                    <i class="fas fa-hand-holding mr-2"></i> Pinjam Alat
                </a>
                <a href="#tentang" @click="open = false"
                    class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300">Tentang</a>
                <a href="#statistik" @click="open = false"
                    class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300">Statistik</a>
                <a href="#kontak" @click="open = false"
                    class="block px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300">Kontak</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}"
                        class="block bg-gray-900 text-white px-4 py-2 rounded-full text-center">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-red-500 font-medium">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                        class="block bg-gray-900 text-white px-4 py-2 rounded-full text-center">Masuk</a>
                @endauth
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="relative pt-28 pb-16 overflow-hidden">
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-300/30 dark:bg-brand-500/20 rounded-full blur-3xl">
            </div>
            <div
                class="absolute bottom-0 right-1/4 w-96 h-96 bg-orange-300/30 dark:bg-orange-500/20 rounded-full blur-3xl">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row items-center gap-12">
            <div class="lg:w-1/2 space-y-6" data-aos="fade-right">
                <span
                    class="inline-flex items-center gap-2 bg-brand-100/50 dark:bg-brand-900/30 border border-brand-200 dark:border-brand-800 text-brand-700 dark:text-brand-300 text-sm font-semibold px-4 py-1.5 rounded-full">
                    <span class="w-2 h-2 bg-brand-500 rounded-full animate-pulse"></span>
                    Unit Metrologi Disperindag Karawang
                </span>
                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 dark:text-white leading-tight tracking-tight">
                    Kelola Alat Ukur <br />
                    <span class="text-gradient">Lebih Cerdas & Akurat</span>
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 max-w-xl leading-relaxed">
                    Tanaoroshi adalah sistem manajemen alat metrologi modern. Catat, pantau, dan kembalikan alat ukur
                    dengan QR Code dalam satu platform terintegrasi.
                </p>
                <div class="flex flex-col sm:flex-row gap-3">
                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                            class="inline-flex items-center justify-center gap-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-semibold py-3.5 px-7 rounded-full transition hover:opacity-90">
                            <i class="fas fa-tachometer-alt"></i> Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center gap-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-semibold py-3.5 px-7 rounded-full transition hover:opacity-90">
                            <i class="fas fa-arrow-right"></i> Masuk ke Aplikasi
                        </a>
                        <a href="#peminjaman"
                            class="inline-flex items-center justify-center gap-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold py-3.5 px-7 rounded-full transition shadow-soft hover:shadow-glow">
                            <i class="fas fa-hand-holding"></i> Pinjam Alat Tanpa Login
                        </a>
                    @endauth
                    <a href="#fitur"
                        class="inline-flex items-center justify-center gap-2 border border-gray-300 dark:border-dark-border hover:bg-white dark:hover:bg-dark-surface text-gray-800 dark:text-gray-200 font-semibold py-3.5 px-7 rounded-full transition">
                        <i class="fas fa-star"></i> Pelajari Fitur
                    </a>
                </div>
            </div>
            <div class="lg:w-1/2 flex justify-center" data-aos="fade-left">
                <div class="relative w-full max-w-md">
                    <div
                        class="bg-white/80 dark:bg-dark-surface/80 backdrop-blur-xl rounded-3xl shadow-soft p-6 border border-gray-200 dark:border-dark-border">
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="w-12 h-12 bg-brand-100 dark:bg-brand-900/40 rounded-xl flex items-center justify-center text-brand-600 dark:text-brand-400 text-2xl">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total alat terdaftar
                                </div>
                                <div class="text-2xl font-bold text-gray-800 dark:text-white" x-data="{ count: 0, target: 102 }"
                                    x-init="let s = setInterval(() => { count < target ? count += Math.ceil(target / 30) : (count = target, clearInterval(s)) }, 40)" x-text="count">0</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 mb-4">
                            <div
                                class="w-12 h-12 bg-green-100 dark:bg-green-900/40 rounded-xl flex items-center justify-center text-green-600 dark:text-green-400 text-2xl">
                                <i class="fas fa-hand-holding"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Pengambilan bulan ini
                                </div>
                                <div class="text-2xl font-bold text-gray-800 dark:text-white" x-data="{ count: 0, target: 28 }"
                                    x-init="let s = setInterval(() => { count < target ? count += Math.ceil(target / 30) : (count = target, clearInterval(s)) }, 40)" x-text="count">0</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div
                                class="w-12 h-12 bg-blue-100 dark:bg-blue-900/40 rounded-xl flex items-center justify-center text-blue-600 dark:text-blue-400 text-2xl">
                                <i class="fas fa-qrcode"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">QR Code Tergenerate
                                </div>
                                <div class="text-2xl font-bold text-gray-800 dark:text-white" x-data="{ count: 0, target: 102 }"
                                    x-init="let s = setInterval(() => { count < target ? count += Math.ceil(target / 30) : (count = target, clearInterval(s)) }, 40)" x-text="count">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fitur Unggulan -->
    <section id="fitur" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900 dark:text-white" data-aos="fade-up">Fitur
                <span class="text-gradient">Unggulan</span>
            </h2>
            <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-12" data-aos="fade-up">Semua yang Anda
                butuhkan untuk mengelola alat ukur dan kalibrasi dalam satu sistem.</p>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach ([['icon' => 'fa-boxes', 'color' => 'amber', 'title' => 'Inventaris Alat', 'desc' => 'Data lengkap alat ukur, kategori, sertifikat, dan masa berlaku.'], ['icon' => 'fa-calendar-check', 'color' => 'green', 'title' => 'Kalibrasi Terjadwal', 'desc' => 'Pantau jadwal kalibrasi, notifikasi otomatis.'], ['icon' => 'fa-qrcode', 'color' => 'blue', 'title' => 'QR Code & Mobile', 'desc' => 'Scan QR Code untuk pengambilan/pengembalian cepat.'], ['icon' => 'fa-file-pdf', 'color' => 'purple', 'title' => 'Laporan & Cetak Bukti', 'desc' => 'Unduh laporan PDF dan cetak bukti resmi.'], ['icon' => 'fa-users', 'color' => 'orange', 'title' => 'Multi‑Pengguna', 'desc' => 'Akses berbasis peran (admin, karyawan, super).'], ['icon' => 'fa-bell', 'color' => 'red', 'title' => 'Notifikasi Cerdas', 'desc' => 'Peringatan stok kritis, kalibrasi habis, dll.']] as $feature)
                    <div class="bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border rounded-2xl p-8 shadow-soft hover:shadow-xl transition transform hover:-translate-y-1 text-left"
                        data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div
                            class="w-14 h-14 bg-{{ $feature['color'] }}-100 dark:bg-{{ $feature['color'] }}-900/40 text-{{ $feature['color'] }}-600 dark:text-{{ $feature['color'] }}-400 rounded-2xl flex items-center justify-center text-2xl mb-5">
                            <i class="fas {{ $feature['icon'] }}"></i>
                        </div>
                        <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Alur Penggunaan -->
    <section id="alur" class="py-20 bg-gray-100/50 dark:bg-dark-surface/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center" x-data="{ activeStep: 1 }">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900 dark:text-white" data-aos="fade-up">Bagaimana
                <span class="text-gradient">Cara Kerjanya?</span>
            </h2>
            <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-16" data-aos="fade-up">Tiga langkah mudah
                menggunakan Tanaoroshi.</p>
            <div class="grid md:grid-cols-3 gap-6 relative mb-12">
                <div
                    class="hidden md:block absolute top-12 left-[15%] right-[15%] h-0.5 bg-gray-200 dark:bg-dark-border z-0">
                </div>

                <div class="relative z-10 cursor-pointer" @click="activeStep = 1">
                    <div class="bg-white dark:bg-dark-surface rounded-2xl shadow-soft border-2 transition-all p-6 flex flex-col items-center"
                        :class="activeStep === 1 ? 'border-brand-500 shadow-glow' :
                            'border-gray-200 dark:border-dark-border hover:border-brand-300'">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl mb-4"
                            :class="activeStep === 1 ? 'bg-brand-100 dark:bg-brand-900/40 text-brand-600' :
                                'bg-gray-100 dark:bg-dark-border text-gray-500'">
                            <i class="fas fa-search"></i>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full mb-2"
                            :class="activeStep === 1 ? 'bg-brand-500 text-white' :
                                'bg-gray-100 dark:bg-dark-border text-gray-600 dark:text-gray-300'">Langkah
                            1</span>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Cari & Lihat Alat</h3>
                    </div>
                </div>
                <div class="relative z-10 cursor-pointer" @click="activeStep = 2">
                    <div class="bg-white dark:bg-dark-surface rounded-2xl shadow-soft border-2 transition-all p-6 flex flex-col items-center"
                        :class="activeStep === 2 ? 'border-brand-500 shadow-glow' :
                            'border-gray-200 dark:border-dark-border hover:border-brand-300'">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl mb-4"
                            :class="activeStep === 2 ? 'bg-brand-100 dark:bg-brand-900/40 text-brand-600' :
                                'bg-gray-100 dark:bg-dark-border text-gray-500'">
                            <i class="fas fa-hand-holding"></i>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full mb-2"
                            :class="activeStep === 2 ? 'bg-brand-500 text-white' :
                                'bg-gray-100 dark:bg-dark-border text-gray-600 dark:text-gray-300'">Langkah
                            2</span>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Ambil Alat</h3>
                    </div>
                </div>
                <div class="relative z-10 cursor-pointer" @click="activeStep = 3">
                    <div class="bg-white dark:bg-dark-surface rounded-2xl shadow-soft border-2 transition-all p-6 flex flex-col items-center"
                        :class="activeStep === 3 ? 'border-brand-500 shadow-glow' :
                            'border-gray-200 dark:border-dark-border hover:border-brand-300'">
                        <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl mb-4"
                            :class="activeStep === 3 ? 'bg-brand-100 dark:bg-brand-900/40 text-brand-600' :
                                'bg-gray-100 dark:bg-dark-border text-gray-500'">
                            <i class="fas fa-undo-alt"></i>
                        </div>
                        <span class="text-xs font-bold px-3 py-1 rounded-full mb-2"
                            :class="activeStep === 3 ? 'bg-brand-500 text-white' :
                                'bg-gray-100 dark:bg-dark-border text-gray-600 dark:text-gray-300'">Langkah
                            3</span>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Kembalikan Alat</h3>
                    </div>
                </div>
            </div>

            <div
                class="bg-white dark:bg-dark-surface rounded-3xl shadow-soft border border-gray-200 dark:border-dark-border p-8 md:p-10 text-left max-w-4xl mx-auto">
                <div x-show="activeStep === 1" x-transition>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                        <span
                            class="w-10 h-10 bg-brand-100 dark:bg-brand-900/40 text-brand-600 rounded-xl flex items-center justify-center"><i
                                class="fas fa-search"></i></span>
                        1. Mencari & Melihat Detail Alat
                    </h3>
                    <div class="space-y-3 text-gray-700 dark:text-gray-300">
                        <p>• <strong>Buka Katalog Alat:</strong> Lihat daftar alat ukur yang terdaftar.</p>
                        <p>• <strong>Filter & Cari:</strong> Gunakan pencarian berdasarkan nama, merk, atau tipe.</p>
                        <p>• <strong>QR Code:</strong> Setiap alat memiliki QR Code unik untuk identifikasi.</p>
                    </div>
                </div>
                <div x-show="activeStep === 2" x-transition>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                        <span
                            class="w-10 h-10 bg-brand-100 dark:bg-brand-900/40 text-brand-600 rounded-xl flex items-center justify-center"><i
                                class="fas fa-hand-holding"></i></span>
                        2. Mengambil Alat
                    </h3>
                    <div class="space-y-3 text-gray-700 dark:text-gray-300">
                        <p>• <strong>Dua Cara:</strong> Scan QR Code atau manual dari detail alat.</p>
                        <p>• <strong>Isi Formulir:</strong> Jumlah, keperluan, dan nama pengambil.</p>
                        <p>• <strong>Stok Otomatis:</strong> Berkurang dan admin mendapat notifikasi.</p>
                    </div>
                </div>
                <div x-show="activeStep === 3" x-transition>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-3">
                        <span
                            class="w-10 h-10 bg-brand-100 dark:bg-brand-900/40 text-brand-600 rounded-xl flex items-center justify-center"><i
                                class="fas fa-undo-alt"></i></span>
                        3. Mengembalikan Alat
                    </h3>
                    <div class="space-y-3 text-gray-700 dark:text-gray-300">
                        <p>• <strong>Scan/Manual:</strong> Proses pengembalian cepat.</p>
                        <p>• <strong>Catat Kondisi:</strong> Baik, Rusak Ringan, dll.</p>
                        <p>• <strong>Audit Trail:</strong> Semua transaksi tercatat.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Alat Unggulan -->
    <section id="alat" class="py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4 text-gray-900 dark:text-white" data-aos="fade-up">Alat yang
                <span class="text-gradient">Kami Kelola</span>
            </h2>
            <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto mb-12" data-aos="fade-up">Berbagai alat ukur
                dan instrumen yang terdata dalam sistem.</p>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @php
                    $sampleTools = [
                        ['name' => 'Multimeter Digital', 'icon' => 'fa-bolt', 'color' => 'blue'],
                        ['name' => 'Timbangan Elektronik', 'icon' => 'fa-weight-scale', 'color' => 'green'],
                        ['name' => 'Termometer Infrared', 'icon' => 'fa-temperature-high', 'color' => 'red'],
                        ['name' => 'Jangka Sorong', 'icon' => 'fa-ruler', 'color' => 'purple'],
                        ['name' => 'Pressure Gauge', 'icon' => 'fa-gauge', 'color' => 'orange'],
                        ['name' => 'Stopwatch Kalibrasi', 'icon' => 'fa-stopwatch', 'color' => 'teal'],
                    ];
                @endphp
                @foreach ($sampleTools as $tool)
                    <div class="bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border rounded-2xl p-6 shadow-soft hover:shadow-xl transition transform hover:-translate-y-1"
                        data-aos="zoom-in">
                        <div
                            class="w-14 h-14 bg-{{ $tool['color'] }}-100 dark:bg-{{ $tool['color'] }}-900/40 text-{{ $tool['color'] }}-600 dark:text-{{ $tool['color'] }}-400 rounded-2xl flex items-center justify-center text-2xl mx-auto mb-4">
                            <i class="fas {{ $tool['icon'] }}"></i>
                        </div>
                        <h4 class="font-semibold text-gray-800 dark:text-white text-sm">{{ $tool['name'] }}</h4>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- SECTION PEMINJAMAN PUBLIK --}}
    @php
        $alatsList = $alats ?? collect();
        $bagiansList = $bagians ?? collect();
        $oldCartItems = collect(old('items', []))
            ->map(
                fn($item) => [
                    'id' => (string) ($item['alat_id'] ?? ''),
                    'quantity' => max(1, (int) ($item['jumlah'] ?? 1)),
                ],
            )
            ->values();
    @endphp
    <section id="peminjaman"
        class="relative overflow-hidden py-20 lg:py-28 bg-gradient-to-br from-brand-50 via-white to-orange-50 dark:from-dark-bg dark:via-dark-bg dark:to-dark-surface"
        x-data="{
            channel: @js(old('kanal_notifikasi', 'email')),
            borrower: @js(old('tipe_peminjam', 'internal')),
            toolSearch: '',
            selectedTool: '',
            selectedQuantity: 1,
            maxItems: 15,
            submitting: false,
            tools: [
                @foreach ($alatsList as $alat)
                    {
                        id: @js((string) $alat->hashid),
                        name: @js($alat->nama_alat),
                        brand: @js($alat->merk ?? ''),
                        type: @js($alat->tipe ?? ''),
                        serial: @js($alat->no_seri ?? ''),
                        capacity: @js($alat->kapasitas ?? ''),
                        category: @js($alat->kelas ?? ''),
                        stock: {{ (int) $alat->jumlah }},
                        unit: @js($alat->satuan ?? 'unit')
                    }, @endforeach
            ],
            cart: [],
            oldCart: @js($oldCartItems),
            init() {
                this.oldCart.forEach(oldItem => {
                    const tool = this.tools.find(t => t.id === oldItem.id);
                    if (tool && tool.stock > 0 && !this.cart.some(i => i.id === tool.id)) {
                        this.cart.push({ id: tool.id, quantity: Number(oldItem.quantity) });
                    }
                });
                if (!this.cart.length) {
                    this.selectedTool = '';
                }
            },
            get filteredTools() {
                const q = this.toolSearch.trim().toLowerCase();
                return this.tools.filter(t => t.stock > 0 && !this.cart.some(i => i.id === t.id) &&
                    (!q || [t.name, t.brand, t.type, t.serial].join(' ').toLowerCase().includes(q)));
            },
            get currentTool() { return this.tools.find(t => t.id === this.selectedTool); },
            get cartCount() { return this.cart.length; },
            get totalQuantity() { return this.cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0); },
            get cartValid() {
                return this.cart.length >= 1 && this.cart.length <= this.maxItems && this.cart.every(item => {
                    const tool = this.tools.find(t => t.id === item.id);
                    return tool && Number.isInteger(Number(item.quantity)) && item.quantity >= 1 && item.quantity <= tool.stock;
                });
            },
            get canAdd() {
                return !!this.currentTool && Number.isInteger(Number(this.selectedQuantity)) && Number(this.selectedQuantity) >= 1 && Number(this.selectedQuantity) <= this.currentTool.stock && this.cart.length < this.maxItems;
            },
            addToCart() {
                if (!this.canAdd) return;
                if (this.cart.some(i => i.id === this.selectedTool)) return;
                this.cart.push({ id: this.selectedTool, quantity: Number(this.selectedQuantity) });
                this.selectedTool = '';
                this.selectedQuantity = 1;
                this.toolSearch = '';
            },
            removeFromCart(id) {
                this.cart = this.cart.filter(item => item.id !== id);
            },
            toolFor(id) { return this.tools.find(t => t.id === id); },
            quantityError(item) {
                const tool = this.toolFor(item.id);
                return !tool || !Number.isInteger(Number(item.quantity)) || item.quantity < 1 || item.quantity > tool.stock;
            },
            submitForm(event) {
                if (!this.cartValid) {
                    event.preventDefault();
                    document.getElementById('keranjang-alat')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                this.submitting = true;
            }
        }">
        <div
            class="pointer-events-none absolute -top-24 -right-24 w-96 h-96 rounded-full bg-brand-200/40 dark:bg-brand-500/10 blur-3xl">
        </div>
        <div
            class="pointer-events-none absolute bottom-0 -left-24 w-80 h-80 rounded-full bg-orange-200/40 dark:bg-orange-500/10 blur-3xl">
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center mb-12 lg:mb-16" data-aos="fade-up">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-brand-100/60 dark:bg-brand-900/30 border border-brand-200 dark:border-brand-800 px-4 py-2 text-sm font-bold text-brand-700 dark:text-brand-300">
                    <i class="fas fa-bolt"></i> Layanan Peminjaman Digital
                </span>
                <h2
                    class="mt-6 text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                    Butuh Alat Ukur? <span class="block mt-2 text-gradient">Ajukan Peminjaman di Sini</span>
                </h2>
                <p class="mt-5 text-base sm:text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                    Tambahkan beberapa jenis alat dalam satu transaksi, atur jumlah setiap alat, lalu pantau status
                    melalui email atau WhatsApp.
                </p>
            </div>

            <div class="grid lg:grid-cols-5 gap-8 lg:gap-10 items-start">
                <aside class="lg:col-span-2 space-y-6" data-aos="fade-right">
                    <div
                        class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-gray-900 to-gray-800 text-white p-7 sm:p-9 shadow-xl">
                        <div
                            class="absolute -right-10 -top-10 w-44 h-44 rounded-full border-[28px] border-brand-400/10">
                        </div>
                        <div class="relative">
                            <div
                                class="w-14 h-14 rounded-2xl bg-brand-500 text-white flex items-center justify-center text-2xl shadow-glow">
                                <i class="fas fa-tools"></i></div>
                            <h3 class="mt-6 text-2xl font-extrabold">Peminjaman Lebih Praktis</h3>
                            <p class="mt-3 text-gray-300 leading-relaxed text-sm">Pilih beberapa alat sekaligus. Sistem
                                memeriksa stok yang tersedia sebelum formulir dikirim.</p>
                            <div class="mt-8 space-y-5">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 shrink-0 rounded-xl bg-white/10 flex items-center justify-center text-brand-400">
                                        <i class="fas fa-user-check"></i></div>
                                    <div>
                                        <h4 class="font-bold">Tanpa Login</h4>
                                        <p class="mt-1 text-sm text-gray-400">Tersedia bagi pegawai internal maupun
                                            peminjam eksternal.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 shrink-0 rounded-xl bg-white/10 flex items-center justify-center text-brand-400">
                                        <i class="fas fa-cart-shopping"></i></div>
                                    <div>
                                        <h4 class="font-bold">Keranjang Multi-Alat</h4>
                                        <p class="mt-1 text-sm text-gray-400">Maksimal 15 jenis alat dalam satu
                                            transaksi.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 shrink-0 rounded-xl bg-white/10 flex items-center justify-center text-brand-400">
                                        <i class="fas fa-bell"></i></div>
                                    <div>
                                        <h4 class="font-bold">Pelacakan Status</h4>
                                        <p class="mt-1 text-sm text-gray-400">Tautan pelacakan dikirim melalui kanal
                                            kontak pilihan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="rounded-3xl bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border p-6 sm:p-7 shadow-soft">
                        <h3 class="font-extrabold text-gray-900 dark:text-white flex items-center gap-2"><i
                                class="fas fa-route text-brand-500"></i> Cara Mengajukan</h3>
                        <div class="mt-6 space-y-5">
                            @foreach ([['number' => '01', 'title' => 'Isi identitas', 'desc' => 'Masukkan nama dan kontak yang aktif.'], ['number' => '02', 'title' => 'Susun keranjang alat', 'desc' => 'Pilih alat dan jumlah sesuai stok.'], ['number' => '03', 'title' => 'Kirim dan lacak', 'desc' => 'Periksa email atau WhatsApp untuk tautan pelacakan.']] as $step)
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-11 h-11 shrink-0 rounded-xl bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 flex items-center justify-center font-extrabold text-sm">
                                        {{ $step['number'] }}</div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $step['title'] }}</p>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $step['desc'] }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </aside>

                <div class="lg:col-span-3 min-w-0" data-aos="fade-left">
                    <div
                        class="overflow-hidden rounded-3xl bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border shadow-xl">
                        <div class="h-2 bg-gradient-to-r from-brand-400 via-brand-500 to-orange-500"></div>
                        <div class="p-6 sm:p-8 lg:p-9">
                            <div class="flex items-start justify-between gap-4 mb-8">
                                <div>
                                    <p class="text-sm font-bold uppercase tracking-wider text-brand-600">Formulir
                                        Online</p>
                                    <h3 class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">Data
                                        Peminjaman</h3>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Kolom bertanda * wajib
                                        diisi.</p>
                                </div>
                                <div
                                    class="hidden sm:flex w-12 h-12 rounded-2xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 items-center justify-center text-xl">
                                    <i class="fas fa-clipboard-list"></i></div>
                            </div>

                            @if (session('success'))
                                <div
                                    class="mb-6 rounded-2xl border border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800 p-4 text-green-800 dark:text-green-300">
                                    <div class="flex gap-3"><i class="fas fa-circle-check mt-0.5"></i>
                                        <p>{{ session('success') }}</p>
                                    </div>
                                </div>
                            @endif
                            @if (session('error'))
                                <div
                                    class="mb-6 rounded-2xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 p-4 text-red-800 dark:text-red-300">
                                    <p>{{ session('error') }}</p>
                                </div>
                            @endif
                            @if ($errors->any())
                                <div
                                    class="mb-6 rounded-2xl border border-red-200 bg-red-50 dark:bg-red-900/20 dark:border-red-800 p-4 text-red-800 dark:text-red-300">
                                    <p class="font-bold mb-2">Ada data yang perlu diperbaiki:</p>
                                    <ul class="list-disc pl-5 space-y-1 text-sm">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('peminjaman-publik.store') }}" method="POST" class="space-y-8"
                                @submit="submitForm($event)">
                                @csrf
                                {{-- BAGIAN 1: IDENTITAS --}}
                                <section>
                                    <div class="flex items-center gap-3 mb-5"><span
                                            class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 flex items-center justify-center text-sm font-extrabold">1</span>
                                        <h4 class="font-extrabold text-gray-900 dark:text-white">Identitas Peminjam
                                        </h4>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-5">
                                        <div class="sm:col-span-2"><label for="tipe_peminjam"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Jenis
                                                Peminjam *</label><select id="tipe_peminjam" name="tipe_peminjam"
                                                x-model="borrower" required
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                                <option value="internal">Pegawai Internal</option>
                                                <option value="eksternal">Peminjam Eksternal</option>
                                            </select></div>
                                        <div class="sm:col-span-2"><label for="nama_peminjam"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Nama
                                                Lengkap *</label><input id="nama_peminjam" name="nama_peminjam"
                                                type="text" autocomplete="name"
                                                value="{{ old('nama_peminjam', auth()->user()?->name) }}"
                                                maxlength="255" required placeholder="Nama lengkap peminjam"
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                        </div>
                                        <div class="sm:col-span-2"><label for="instansi_peminjam"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Instansi
                                                / Bagian</label><input id="instansi_peminjam" name="instansi_peminjam"
                                                value="{{ old('instansi_peminjam') }}" maxlength="255"
                                                placeholder="Nama instansi atau bagian"
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                        </div>
                                        <div class="sm:col-span-2" x-show="borrower === 'internal'" x-transition>
                                            <label for="bagian_id"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Bagian
                                                Internal</label><select id="bagian_id" name="bagian_id"
                                                :disabled="borrower !== 'internal'"
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                                <option value="">Pilih bagian (opsional)</option>
                                                @foreach ($bagiansList as $bagian)
                                                    <option value="{{ $bagian->id }}" @selected((string) old('bagian_id') === (string) $bagian->id)>
                                                        {{ $bagian->nama }}</option>
                                                @endforeach
                                            </select></div>
                                    </div>
                                </section>

                                <div class="border-t border-gray-100 dark:border-dark-border"></div>
                                {{-- BAGIAN 2: PILIH ALAT + KERANJANG --}}
                                <section>
                                    <div class="flex items-center gap-3 mb-5"><span
                                            class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 flex items-center justify-center text-sm font-extrabold">2</span>
                                        <h4 class="font-extrabold text-gray-900 dark:text-white">Pilih Alat dan Susun
                                            Keranjang</h4>
                                    </div>
                                    <div
                                        class="rounded-2xl border border-gray-200 dark:border-dark-border p-4 sm:p-5 space-y-4">
                                        <div><label for="cari_alat"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Cari
                                                alat berdasarkan nama, merek, tipe, atau nomor seri</label>
                                            <div class="relative"><i
                                                    class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i><input
                                                    id="cari_alat" type="search" x-model="toolSearch"
                                                    autocomplete="off"
                                                    placeholder="Contoh: multimeter, jangka sorong..."
                                                    class="w-full rounded-xl border border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white pl-11 pr-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                            </div>
                                        </div>
                                        <div class="max-h-64 overflow-y-auto rounded-xl border border-gray-200 dark:border-dark-border divide-y divide-gray-100 dark:divide-dark-border"
                                            x-show="toolSearch.length > 0 || !selectedTool" x-cloak>
                                            <template x-for="tool in filteredTools.slice(0, 40)"
                                                :key="tool.id">
                                                <button type="button"
                                                    @click="selectedTool = tool.id; selectedQuantity = 1"
                                                    class="w-full p-4 sm:p-5 text-left bg-white dark:bg-dark-surface hover:bg-brand-50 dark:hover:bg-brand-900/20 transition border-b border-gray-100 dark:border-dark-border last:border-b-0 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-400">
                                                    <span class="flex items-start justify-between gap-3">
                                                        <span class="min-w-0 flex-1">
                                                            <span
                                                                class="block font-extrabold text-base text-gray-900 dark:text-white"
                                                                x-text="tool.name"></span>
                                                            <span class="mt-2 flex flex-wrap gap-2">
                                                                <span
                                                                    class="inline-flex items-center gap-1 rounded-md bg-gray-100 dark:bg-dark-bg px-2 py-1 text-xs font-bold text-gray-700 dark:text-gray-200"><i
                                                                        class="fas fa-barcode text-brand-500"></i> No.
                                                                    Seri: <span
                                                                        x-text="tool.serial || 'Belum diisi'"></span></span>
                                                                <span
                                                                    class="inline-flex items-center gap-1 rounded-md bg-blue-50 dark:bg-blue-900/20 px-2 py-1 text-xs font-semibold text-blue-700 dark:text-blue-300"
                                                                    x-show="tool.capacity"><i
                                                                        class="fas fa-ruler-combined"></i><span
                                                                        x-text="'Kapasitas: ' + tool.capacity"></span></span>
                                                            </span>
                                                            <span
                                                                class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-600 dark:text-gray-300">
                                                                <span><i class="fas fa-tag w-4 text-gray-400"></i>
                                                                    Merek: <strong
                                                                        x-text="tool.brand || '—'"></strong></span>
                                                                <span><i class="fas fa-toolbox w-4 text-gray-400"></i>
                                                                    Tipe: <strong
                                                                        x-text="tool.type || '—'"></strong></span>
                                                                <span x-show="tool.category"><i
                                                                        class="fas fa-layer-group w-4 text-gray-400"></i>
                                                                    Kelas: <strong
                                                                        x-text="tool.category"></strong></span>
                                                                <span><i
                                                                        class="fas fa-boxes-stacked w-4 text-gray-400"></i>
                                                                    Stok: <strong x-text="tool.stock"></strong> <span
                                                                        x-text="tool.unit"></span></span>
                                                            </span>
                                                        </span>
                                                        <span
                                                            class="shrink-0 rounded-full px-3 py-2 text-xs font-extrabold bg-brand-100 text-brand-700 dark:bg-brand-900/40 dark:text-brand-300"><i
                                                                class="fas fa-plus mr-1"></i>Pilih</span>
                                                    </span>
                                                </button>
                                            </template>
                                            <div x-show="filteredTools.length === 0"
                                                class="p-4 text-sm text-gray-500 dark:text-gray-300">Tidak ada alat
                                                tersedia yang cocok atau semua hasil sudah ada di keranjang.</div>
                                        </div>
                                        <div x-show="currentTool" x-cloak x-transition
                                            class="rounded-xl bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 p-4">
                                            <div class="flex flex-col sm:flex-row sm:items-end gap-3">
                                                <div class="flex-1 min-w-0">
                                                    <p
                                                        class="text-xs font-bold uppercase tracking-wide text-brand-700 dark:text-brand-300">
                                                        Alat dipilih</p>
                                                    <p class="mt-1 font-extrabold text-gray-900 dark:text-white"
                                                        x-text="currentTool?.name"></p>
                                                    <div class="mt-2 flex flex-wrap gap-2 text-xs"><span
                                                            class="rounded-md bg-white dark:bg-dark-surface border border-brand-200 dark:border-brand-800 px-2 py-1 text-gray-700 dark:text-gray-200"><i
                                                                class="fas fa-barcode mr-1 text-brand-500"></i>No.
                                                            Seri: <strong
                                                                x-text="currentTool?.serial || 'Belum diisi'"></strong></span><span
                                                            x-show="currentTool?.capacity"
                                                            class="rounded-md bg-white dark:bg-dark-surface border border-brand-200 dark:border-brand-800 px-2 py-1 text-gray-700 dark:text-gray-200"><i
                                                                class="fas fa-ruler-combined mr-1 text-brand-500"></i>Kapasitas:
                                                            <strong
                                                                x-text="currentTool?.capacity"></strong></span><span
                                                            class="rounded-md bg-white dark:bg-dark-surface border border-brand-200 dark:border-brand-800 px-2 py-1 text-gray-700 dark:text-gray-200">Merek:
                                                            <strong
                                                                x-text="currentTool?.brand || '—'"></strong></span><span
                                                            class="rounded-md bg-white dark:bg-dark-surface border border-brand-200 dark:border-brand-800 px-2 py-1 text-gray-700 dark:text-gray-200">Tipe:
                                                            <strong x-text="currentTool?.type || '—'"></strong></span>
                                                    </div>
                                                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-300">Stok
                                                        tersedia: <strong x-text="currentTool?.stock"></strong> <span
                                                            x-text="currentTool?.unit"></span></p>
                                                </div>
                                                <div class="w-full sm:w-28"><label for="jumlah-pilihan"
                                                        class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1">Jumlah</label><input
                                                        id="jumlah-pilihan" type="number"
                                                        x-model.number="selectedQuantity" min="1"
                                                        :max="currentTool?.stock || 1"
                                                        class="w-full rounded-lg border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-3 py-2.5"
                                                        @keydown.enter.prevent></div><button type="button"
                                                    @click="addToCart()" :disabled="!canAdd"
                                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-brand-600 hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold px-4 py-3 transition"><i
                                                        class="fas fa-plus"></i> Tambah</button>
                                            </div>
                                            <p x-show="selectedQuantity < 1 || selectedQuantity > (currentTool?.stock || 0)"
                                                class="mt-2 text-sm text-red-600">Jumlah harus antara 1 dan stok yang
                                                tersedia.</p>
                                            <p x-show="cart.length >= maxItems" class="mt-2 text-xs text-amber-700">
                                                Keranjang mencapai batas 15 jenis alat.</p>
                                        </div>
                                    </div>

                                    <div id="keranjang-alat"
                                        class="mt-5 rounded-2xl border border-gray-200 dark:border-dark-border overflow-hidden">
                                        <div
                                            class="flex flex-wrap items-center justify-between gap-3 bg-gray-50 dark:bg-dark-bg px-4 sm:px-5 py-4 border-b border-gray-200 dark:border-dark-border">
                                            <div>
                                                <h5 class="font-extrabold text-gray-900 dark:text-white"><i
                                                        class="fas fa-cart-shopping text-brand-500 mr-2"></i>Keranjang
                                                    Peminjaman</h5>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Maksimal 15
                                                    jenis alat per transaksi.</p>
                                            </div>
                                            <div class="flex gap-2"><span
                                                    class="rounded-full bg-brand-100 dark:bg-brand-900/40 px-3 py-1.5 text-xs font-bold text-brand-700 dark:text-brand-300"><span
                                                        x-text="cartCount"></span> jenis</span><span
                                                    class="rounded-full bg-gray-200 dark:bg-dark-border px-3 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200"><span
                                                        x-text="totalQuantity"></span> total unit</span></div>
                                        </div>
                                        <div x-show="cart.length === 0" class="px-5 py-10 text-center">
                                            <div
                                                class="mx-auto w-14 h-14 rounded-2xl bg-gray-100 dark:bg-dark-bg text-gray-400 flex items-center justify-center text-2xl">
                                                <i class="fas fa-basket-shopping"></i></div>
                                            <p class="mt-3 font-bold text-gray-700 dark:text-gray-200">Keranjang masih
                                                kosong</p>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cari alat di atas,
                                                tentukan jumlah, lalu tekan Tambah.</p>
                                        </div>
                                        <div x-show="cart.length > 0"
                                            class="divide-y divide-gray-100 dark:divide-dark-border">
                                            <template x-for="(item, index) in cart" :key="item.id">
                                                <div
                                                    class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                                                    <div
                                                        class="w-10 h-10 shrink-0 rounded-xl bg-brand-50 dark:bg-brand-900/30 text-brand-600 flex items-center justify-center">
                                                        <i class="fas fa-toolbox"></i></div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-bold text-gray-900 dark:text-white"
                                                            x-text="toolFor(item.id)?.name || 'Alat tidak ditemukan'">
                                                        </p>
                                                        <div
                                                            class="mt-2 flex flex-wrap gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                            <span
                                                                class="rounded-md bg-gray-100 dark:bg-dark-bg px-2 py-1"><i
                                                                    class="fas fa-barcode mr-1 text-brand-500"></i>No.
                                                                Seri: <strong
                                                                    x-text="toolFor(item.id)?.serial || 'Belum diisi'"></strong></span><span
                                                                x-show="toolFor(item.id)?.capacity"
                                                                class="rounded-md bg-gray-100 dark:bg-dark-bg px-2 py-1"><i
                                                                    class="fas fa-ruler-combined mr-1 text-brand-500"></i>Kapasitas:
                                                                <strong
                                                                    x-text="toolFor(item.id)?.capacity"></strong></span><span
                                                                class="rounded-md bg-gray-100 dark:bg-dark-bg px-2 py-1">Merek:
                                                                <strong
                                                                    x-text="toolFor(item.id)?.brand || '—'"></strong></span><span
                                                                class="rounded-md bg-gray-100 dark:bg-dark-bg px-2 py-1">Tipe:
                                                                <strong
                                                                    x-text="toolFor(item.id)?.type || '—'"></strong></span>
                                                        </div>
                                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Stok:
                                                            <span x-text="toolFor(item.id)?.stock ?? 0"></span> <span
                                                                x-text="toolFor(item.id)?.unit || 'unit'"></span></p>
                                                        <p x-show="quantityError(item)"
                                                            class="mt-1 text-xs font-semibold text-red-600">Jumlah
                                                            melebihi stok atau tidak valid.</p>
                                                    </div>
                                                    <div class="flex items-center gap-2"><label class="sr-only"
                                                            :for="'cart-quantity-' + index">Jumlah</label><input
                                                            :id="'cart-quantity-' + index" type="number"
                                                            min="1" :max="toolFor(item.id)?.stock || 1"
                                                            x-model.number="item.quantity"
                                                            class="w-24 rounded-lg border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-3 py-2.5"
                                                            :aria-invalid="quantityError(item)"><span
                                                            class="text-sm text-gray-500 dark:text-gray-300"
                                                            x-text="toolFor(item.id)?.unit || 'unit'"></span><button
                                                            type="button" @click="removeFromCart(item.id)"
                                                            class="w-10 h-10 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition"
                                                            :aria-label="'Hapus ' + (toolFor(item.id)?.name || 'alat')"><i
                                                                class="fas fa-trash"></i></button></div><input
                                                        type="hidden" :name="`items[${index}][alat_id]`"
                                                        :value="item.id"><input type="hidden"
                                                        :name="`items[${index}][jumlah]`" :value="item.quantity">
                                                </div>
                                            </template>
                                        </div>
                                        <div x-show="cart.length > 0"
                                            class="bg-gray-50 dark:bg-dark-bg border-t border-gray-200 dark:border-dark-border px-4 sm:px-5 py-3 flex items-start gap-2">
                                            <i class="fas fa-shield-halved text-green-600 mt-0.5"></i>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">Stok dicek kembali di
                                                server saat dikirim. Jika stok berubah, sistem akan meminta Anda
                                                memperbaiki jumlah.</p>
                                        </div>
                                    </div>
                                    <p x-show="!cartValid && cart.length > 0"
                                        class="mt-3 text-sm font-semibold text-red-600">Periksa kembali jumlah alat.
                                        Setiap jumlah harus minimal 1 dan tidak boleh melebihi stok.</p>
                                    @error('items')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('items.*.alat_id')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    @error('items.*.jumlah')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </section>

                                <div class="border-t border-gray-100 dark:border-dark-border"></div>
                                {{-- BAGIAN 3: JADWAL --}}
                                <section>
                                    <div class="flex items-center gap-3 mb-5"><span
                                            class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 flex items-center justify-center text-sm font-extrabold">3</span>
                                        <h4 class="font-extrabold text-gray-900 dark:text-white">Jadwal dan Keperluan
                                        </h4>
                                    </div>
                                    <div class="space-y-5">
                                        <div><label for="lama_pinjam"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Lama
                                                Pinjam (hari) *</label><input type="number" id="lama_pinjam"
                                                name="lama_pinjam" min="1" max="365"
                                                value="{{ old('lama_pinjam', 7) }}" required
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Masukkan 1 sampai
                                                365 hari.</p>
                                        </div>
                                        <div><label for="waktu_pengambilan"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Tanggal
                                                dan Waktu Pengambilan *</label><input type="datetime-local"
                                                id="waktu_pengambilan" name="waktu_pengambilan"
                                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                                value="{{ old('waktu_pengambilan', now()->format('Y-m-d\TH:i')) }}"
                                                required
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                        </div>
                                        <div><label for="keperluan"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Keperluan
                                                Peminjaman *</label>
                                            <textarea id="keperluan" name="keperluan" rows="3" maxlength="2000" required
                                                placeholder="Jelaskan tujuan penggunaan alat..."
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">{{ old('keperluan') }}</textarea>
                                        </div>
                                    </div>
                                </section>

                                <div class="border-t border-gray-100 dark:border-dark-border"></div>
                                {{-- BAGIAN 4: NOTIFIKASI --}}
                                <section>
                                    <div class="flex items-center gap-3 mb-5"><span
                                            class="w-8 h-8 rounded-lg bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 flex items-center justify-center text-sm font-extrabold">4</span>
                                        <h4 class="font-extrabold text-gray-900 dark:text-white">Kontak dan Notifikasi
                                        </h4>
                                    </div>
                                    <div class="space-y-5">
                                        <div><label
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-3">Pilih
                                                Kanal Notifikasi *</label>
                                            <div class="grid grid-cols-2 gap-3"><label
                                                    class="relative cursor-pointer"><input type="radio"
                                                        name="kanal_notifikasi" value="email" x-model="channel"
                                                        class="peer sr-only" required>
                                                    <div
                                                        class="rounded-2xl border-2 border-gray-200 dark:border-dark-border p-4 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 dark:peer-checked:bg-brand-900/20 hover:border-brand-300">
                                                        <i class="fas fa-envelope text-xl text-brand-600"></i>
                                                        <p
                                                            class="mt-2 font-bold text-sm text-gray-900 dark:text-white">
                                                            Email</p>
                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tautan
                                                            status via email</p>
                                                    </div>
                                                </label><label class="relative cursor-pointer"><input type="radio"
                                                        name="kanal_notifikasi" value="whatsapp" x-model="channel"
                                                        class="peer sr-only">
                                                    <div
                                                        class="rounded-2xl border-2 border-gray-200 dark:border-dark-border p-4 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 dark:peer-checked:bg-brand-900/20 hover:border-brand-300">
                                                        <i class="fab fa-whatsapp text-xl text-green-600"></i>
                                                        <p
                                                            class="mt-2 font-bold text-sm text-gray-900 dark:text-white">
                                                            WhatsApp</p>
                                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tautan
                                                            status via WA</p>
                                                    </div>
                                                </label></div>
                                        </div>
                                        <div x-show="channel === 'email'" x-transition><label for="email_peminjam"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Alamat
                                                Email *</label><input type="email" id="email_peminjam"
                                                name="email_peminjam" autocomplete="email"
                                                value="{{ old('email_peminjam', auth()->user()?->email) }}"
                                                :required="channel === 'email'"
                                                :disabled="channel !== 'email'" placeholder="nama@email.com"
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                        </div>
                                        <div x-show="channel === 'whatsapp'" x-transition><label
                                                for="telepon_peminjam"
                                                class="block text-sm font-bold text-gray-700 dark:text-gray-200 mb-2">Nomor
                                                WhatsApp *</label><input type="tel" id="telepon_peminjam"
                                                name="telepon_peminjam" autocomplete="tel"
                                                value="{{ old('telepon_peminjam') }}"
                                                :required="channel === 'whatsapp'"
                                                :disabled="channel !== 'whatsapp'" placeholder="6281234567890"
                                                class="w-full rounded-xl border-gray-200 dark:border-dark-border dark:bg-dark-bg dark:text-white px-4 py-3.5 focus:ring-2 focus:ring-brand-400">
                                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Gunakan kode
                                                negara, contoh 62 untuk Indonesia.</p>
                                        </div>
                                    </div>
                                </section>

                                <div
                                    class="rounded-2xl border border-brand-200 dark:border-brand-800 bg-brand-50 dark:bg-brand-900/20 p-4">
                                    <div class="flex items-start gap-3"><i
                                            class="fas fa-info-circle mt-0.5 text-brand-600"></i>
                                        <p class="text-sm leading-relaxed text-brand-900 dark:text-brand-200">Dengan
                                            mengirim formulir, Anda mengonfirmasi informasi yang diberikan benar.
                                            Peminjaman langsung tercatat di sistem dan stok akan disesuaikan.</p>
                                    </div>
                                </div>
                                <button type="submit" :disabled="!cartValid || submitting"
                                    class="group w-full inline-flex items-center justify-center gap-3 rounded-2xl bg-gradient-to-r from-brand-500 to-orange-500 hover:from-brand-600 hover:to-orange-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-extrabold text-base py-4 px-6 shadow-xl shadow-brand-500/20 transition duration-300 hover:-translate-y-0.5"><i
                                        class="fas"
                                        :class="submitting ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i><span
                                        x-text="submitting ? 'Mengirim...' : 'Kirim Peminjaman (' + cartCount + ' jenis alat)'"></span><i
                                        x-show="!submitting"
                                        class="fas fa-arrow-right transition group-hover:translate-x-1"></i></button>
                                <p class="text-center text-xs text-gray-500 dark:text-gray-400"><i
                                        class="fas fa-lock mr-1"></i> Informasi kontak digunakan untuk notifikasi
                                    peminjaman.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tentang Metrologi -->
    <section id="tentang" class="py-20 bg-gray-100/50 dark:bg-dark-surface/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="lg:w-1/2" data-aos="fade-right">
                    <img src="https://perindag.slemankab.go.id/wp-content/uploads/2025/09/Logo-Metrologi-Diedit.png"
                        alt="Logo Metrologi" class="w-48 h-48 object-contain mx-auto lg:mx-0" />
                </div>
                <div class="lg:w-1/2 space-y-4" data-aos="fade-left">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white">Tentang <span
                            class="text-gradient">Unit Metrologi</span></h2>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">Unit Metrologi Legal Dinas
                        Perindustrian dan Perdagangan Kabupaten Karawang bertugas melaksanakan pelayanan tera, tera
                        ulang, dan pengawasan alat ukur sesuai peraturan perundangan.</p>
                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed">Tanaoroshi hadir sebagai sistem digital
                        terintegrasi untuk mendukung operasional unit metrologi secara transparan dan akuntabel.</p>
                    @guest
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 font-semibold py-3 px-8 rounded-full mt-4 transition hover:opacity-90">
                            <i class="fas fa-sign-in-alt"></i> Akses Sistem
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <!-- Statistik -->
    <section id="statistik" class="py-20">
        <div class="max-w-5xl mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-12 text-gray-900 dark:text-white" data-aos="fade-up">Dalam
                <span class="text-gradient">Angka</span>
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div
                    class="p-6 bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border rounded-2xl shadow-soft">
                    <div class="text-4xl font-extrabold text-gradient" x-data="{ count: 0, target: 102 }"
                        x-init="let s = setInterval(() => { count < target ? count += Math.ceil(target / 30) : (count = target, clearInterval(s)) }, 30)" x-text="count">0</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Alat Terdaftar</div>
                </div>
                <div
                    class="p-6 bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border rounded-2xl shadow-soft">
                    <div class="text-4xl font-extrabold text-gradient" x-data="{ count: 0, target: 15 }"
                        x-init="let s = setInterval(() => { count < target ? count += Math.ceil(target / 30) : (count = target, clearInterval(s)) }, 40)" x-text="count">0</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Kalibrasi Bulan Ini</div>
                </div>
                <div
                    class="p-6 bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border rounded-2xl shadow-soft">
                    <div class="text-4xl font-extrabold text-gradient" x-data="{ count: 0, target: 28 }"
                        x-init="let s = setInterval(() => { count < target ? count += Math.ceil(target / 30) : (count = target, clearInterval(s)) }, 40)" x-text="count">0</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Pengambilan Bulan Ini</div>
                </div>
                <div
                    class="p-6 bg-white dark:bg-dark-surface border border-gray-200 dark:border-dark-border rounded-2xl shadow-soft">
                    <div class="text-4xl font-extrabold text-gradient" x-data="{ count: 0, target: 0 }"
                        x-init="let s = setInterval(() => { count < target ? count += Math.ceil(target / 30) : (count = target, clearInterval(s)) }, 40)" x-text="count">0</div>
                    <div class="text-gray-600 dark:text-gray-400 mt-2 text-sm">Pelanggaran</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-20">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <div class="bg-gradient-to-br from-brand-500 to-orange-600 rounded-3xl p-10 shadow-xl text-white">
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Siap Mengelola Alat Metrologi dengan Lebih Modern?</h2>
                <p class="text-xl opacity-90 mb-8">Tingkatkan akurasi pencatatan dan transparansi di unit metrologi
                    Anda.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-3">
                    @auth
                        <a href="{{ route('admin.dashboard') }}"
                            class="bg-white text-brand-700 font-bold px-8 py-4 rounded-full text-lg shadow-xl hover:bg-gray-100 transition">
                            Buka Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                            class="bg-white text-brand-700 font-bold px-8 py-4 rounded-full text-lg shadow-xl hover:bg-gray-100 transition">
                            Masuk ke Aplikasi
                        </a>
                    @endauth
                    <a href="#peminjaman"
                        class="border-2 border-white text-white font-bold px-8 py-4 rounded-full text-lg hover:bg-white hover:text-brand-700 transition">Pinjam
                        Alat</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="kontak" class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <img src="{{ asset('images/logos.jpg') }}" alt="Tanaoroshi" class="h-8 w-auto rounded" />
                    <span class="text-lg font-bold text-white">Tanaoroshi</span>
                </div>
                <p class="text-sm">Sistem Manajemen Alat Metrologi<br />Dinas Perindag Kabupaten Karawang.</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Kontak</h4>
                <ul class="space-y-2 text-sm">
                    <li><i class="fas fa-map-marker-alt w-5 text-brand-400"></i> Jl. Contoh No.123, Karawang</li>
                    <li><i class="fas fa-phone w-5 text-brand-400"></i> (0267) 123456</li>
                    <li><i class="fas fa-envelope w-5 text-brand-400"></i> perindag@karawangkab.go.id</li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3">Navigasi</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#hero" class="hover:text-brand-400">Beranda</a></li>
                    <li><a href="#fitur" class="hover:text-brand-400">Fitur</a></li>
                    <li><a href="#alat" class="hover:text-brand-400">Alat</a></li>
                    <li><a href="#peminjaman" class="hover:text-brand-400">Pinjam Alat</a></li>
                    <li><a href="#tentang" class="hover:text-brand-400">Tentang</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-6 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} Dinas Perindustrian dan Perdagangan Kabupaten Karawang.
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>

</html>
