@extends('layouts.app')

@section('title', 'Dashboard SPV')

@section('content')
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 px-4 py-6 md:px-6" x-data="spvDashboard()">
        <div class="mx-auto max-w-7xl space-y-6">
            {{-- Header --}}
            <section class="relative overflow-hidden rounded-3xl bg-slate-900 p-6 text-white shadow-xl md:p-8">
                <div class="pointer-events-none absolute -right-12 -top-20 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl">
                </div>
                <div
                    class="pointer-events-none absolute -bottom-24 right-1/3 h-56 w-56 rounded-full bg-cyan-400/10 blur-3xl">
                </div>
                <div class="relative flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div
                            class="mb-3 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-indigo-100">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            SUPERVISOR CONTROL CENTER
                        </div>
                        <h1 class="text-2xl font-bold tracking-tight md:text-3xl">Dashboard SPV</h1>
                        <p class="mt-2 max-w-2xl text-sm text-slate-300 md:text-base">
                            Pantau pengajuan sparepart, kendalikan antrean persetujuan, dan evaluasi aktivitas keputusan
                            dalam satu tempat.
                        </p>
                        <p class="mt-4 text-sm text-slate-400">
                            Selamat datang, <span class="font-semibold text-white">{{ auth()->user()->name }}</span>.
                            <span class="mx-1">•</span>
                            <span x-text="today"></span>
                        </p>
                    </div>
                    <a href="{{ route('purchase_requests.index') }}"
                        class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-900 shadow-lg transition hover:-translate-y-0.5 hover:bg-indigo-50">
                        <i class="fas fa-clipboard-check"></i>
                        Buka Pengajuan Sparepart
                        <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                    </a>
                </div>
            </section>

            {{-- KPI --}}
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Pengajuan</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                                {{ number_format($totalRequests, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-400">Seluruh permintaan tercatat</p>
                        </div>
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300"><i
                                class="fas fa-layer-group"></i></span>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-amber-900/60 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Menunggu Review</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-amber-600">
                                {{ number_format($pendingRequestsCount, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-400">Perlu ditindaklanjuti</p>
                        </div>
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300"><i
                                class="fas fa-hourglass-half"></i></span>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-emerald-900/60 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Disetujui (PO)</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-emerald-600">
                                {{ number_format($approvedRequests, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-400">Pengajuan berstatus PO</p>
                        </div>
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300"><i
                                class="fas fa-check-circle"></i></span>
                    </div>
                </div>
                <div
                    class="rounded-2xl border border-rose-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-rose-900/60 dark:bg-slate-900">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Ditolak</p>
                            <p class="mt-2 text-3xl font-bold tracking-tight text-rose-600">
                                {{ number_format($rejectedRequests, 0, ',', '.') }}</p>
                            <p class="mt-1 text-xs text-slate-400">Berdasarkan log penolakan</p>
                        </div>
                        <span
                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300"><i
                                class="fas fa-ban"></i></span>
                    </div>
                </div>
            </section>

            {{-- Kanban --}}
            <section
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 md:p-6"
                x-data="{ search: '' }">
                <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Alur Pengajuan Sparepart</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Ringkasan antrean berdasarkan status
                            pengajuan.</p>
                    </div>
                    <div class="relative w-full lg:max-w-xs">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="search" x-model="search" placeholder="Cari nama sparepart..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-700 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:ring-indigo-900">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                    {{-- Pending --}}
                    <div class="min-w-0 rounded-2xl bg-amber-50/70 p-3 dark:bg-amber-500/5">
                        <div class="mb-3 flex items-center justify-between px-1">
                            <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Menunggu Review</h3>
                            </div>
                            <span
                                class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">{{ $pendingRequestsCount }}</span>
                        </div>
                        <div class="max-h-[27rem] space-y-3 overflow-y-auto pr-1">
                            @forelse ($pendingRequests as $request)
                                <article data-search="{{ \Illuminate\Support\Str::lower($request->nama_part ?? '') }}"
                                    x-show="!search || $el.dataset.search.includes(search.toLowerCase())" x-transition
                                    class="rounded-xl border border-amber-100 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-semibold text-slate-800 dark:text-white">
                                            {{ $request->nama_part ?? 'Nama sparepart tidak tersedia' }}</p>
                                        <span
                                            class="shrink-0 rounded-full bg-amber-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">PR</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"><i
                                            class="far fa-calendar mr-1"></i>{{ optional($request->created_at)->format('d M Y, H:i') ?? '-' }}
                                    </p>
                                    <a href="{{ route('purchase_requests.index') }}"
                                        class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-300">Tinjau
                                        pengajuan <i class="fas fa-arrow-right"></i></a>
                                </article>
                            @empty
                                <div
                                    class="rounded-xl border border-dashed border-amber-200 bg-white/70 px-4 py-8 text-center dark:border-slate-700 dark:bg-slate-900/50">
                                    <i class="fas fa-check-circle text-2xl text-emerald-500"></i>
                                    <p class="mt-2 text-sm font-semibold text-slate-700 dark:text-slate-200">Tidak ada
                                        antrean</p>
                                    <p class="mt-1 text-xs text-slate-400">Semua pengajuan PR sudah ditindaklanjuti.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Approved --}}
                    <div class="min-w-0 rounded-2xl bg-emerald-50/70 p-3 dark:bg-emerald-500/5">
                        <div class="mb-3 flex items-center justify-between px-1">
                            <div class="flex items-center gap-2"><span
                                    class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Disetujui</h3>
                            </div>
                            <span
                                class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">{{ $approvedRequests }}</span>
                        </div>
                        <div class="max-h-[27rem] space-y-3 overflow-y-auto pr-1">
                            @forelse ($approvedRecent as $request)
                                <article data-search="{{ \Illuminate\Support\Str::lower($request->nama_part ?? '') }}"
                                    x-show="!search || $el.dataset.search.includes(search.toLowerCase())" x-transition
                                    class="rounded-xl border border-emerald-100 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-semibold text-slate-800 dark:text-white">
                                            {{ $request->nama_part ?? 'Nama sparepart tidak tersedia' }}</p>
                                        <span
                                            class="shrink-0 rounded-full bg-emerald-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">PO</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"><i
                                            class="far fa-calendar mr-1"></i>{{ optional($request->created_at)->format('d M Y, H:i') ?? '-' }}
                                    </p>
                                    <a href="{{ route('purchase_requests.index') }}"
                                        class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-300">Lihat
                                        daftar <i class="fas fa-arrow-right"></i></a>
                                </article>
                            @empty
                                <div
                                    class="rounded-xl border border-dashed border-emerald-200 bg-white/70 px-4 py-8 text-center dark:border-slate-700 dark:bg-slate-900/50">
                                    <p class="text-sm text-slate-500">Belum ada pengajuan berstatus PO.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- Rejected --}}
                    <div class="min-w-0 rounded-2xl bg-rose-50/70 p-3 dark:bg-rose-500/5">
                        <div class="mb-3 flex items-center justify-between px-1">
                            <div class="flex items-center gap-2"><span
                                    class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">Ditolak</h3>
                            </div>
                            <span
                                class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">{{ $rejectedRequests }}</span>
                        </div>
                        <div class="max-h-[27rem] space-y-3 overflow-y-auto pr-1">
                            @forelse ($rejectedRecent as $log)
                                <article
                                    data-search="{{ \Illuminate\Support\Str::lower($log->purchaseRequest->nama_part ?? '') }}"
                                    x-show="!search || $el.dataset.search.includes(search.toLowerCase())" x-transition
                                    class="rounded-xl border border-rose-100 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-semibold text-slate-800 dark:text-white">
                                            {{ $log->purchaseRequest->nama_part ?? 'Nama sparepart tidak tersedia' }}</p>
                                        <span
                                            class="shrink-0 rounded-full bg-rose-100 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">REJECTED</span>
                                    </div>
                                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"><i
                                            class="far fa-calendar mr-1"></i>{{ optional($log->created_at)->format('d M Y, H:i') ?? '-' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">Oleh:
                                        {{ $log->approvedBy->name ?? 'Tidak diketahui' }}</p>
                                    <a href="{{ route('purchase_requests.index') }}"
                                        class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-300">Lihat
                                        daftar <i class="fas fa-arrow-right"></i></a>
                                </article>
                            @empty
                                <div
                                    class="rounded-xl border border-dashed border-rose-200 bg-white/70 px-4 py-8 text-center dark:border-slate-700 dark:bg-slate-900/50">
                                    <p class="text-sm text-slate-500">Belum ada log penolakan.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-400">Kanban ini merupakan ringkasan status. Keputusan approve/reject
                    tetap dilakukan melalui halaman pengajuan agar validasi dan audit log aplikasi tetap digunakan.</p>
            </section>

            {{-- Analytics + Activity --}}
            <section class="grid grid-cols-1 gap-6 xl:grid-cols-5">
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:col-span-3">
                    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="font-bold text-slate-900 dark:text-white">Tren Pengajuan</h2>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Jumlah pengajuan baru per bulan · 6
                                bulan terakhir</p>
                        </div>
                        <span
                            class="rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">Monthly
                            overview</span>
                    </div>
                    <div class="h-64"><canvas id="spvTrendChart"></canvas></div>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 xl:col-span-2">
                    <h2 class="font-bold text-slate-900 dark:text-white">Ringkasan Keputusan</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Perbandingan jumlah disetujui dan ditolak
                    </p>
                    <div class="relative mx-auto mt-3 h-48 max-w-xs"><canvas id="spvDecisionChart"></canvas></div>
                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-emerald-50 p-3 dark:bg-emerald-500/10">
                            <p class="text-xs text-emerald-700 dark:text-emerald-300">Disetujui</p>
                            <p class="mt-1 text-xl font-bold text-emerald-700 dark:text-emerald-300">
                                {{ number_format($approvedRequests, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl bg-rose-50 p-3 dark:bg-rose-500/10">
                            <p class="text-xs text-rose-700 dark:text-rose-300">Ditolak</p>
                            <p class="mt-1 text-xl font-bold text-rose-700 dark:text-rose-300">
                                {{ number_format($rejectedRequests, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div
                        class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-sm dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">Approval rate*</span><strong
                            class="text-slate-900 dark:text-white">{{ $approvalRate }}%</strong>
                    </div>
                    <p class="mt-1 text-[11px] text-slate-400">*Disetujui ÷ (disetujui + log penolakan).</p>
                </div>
            </section>

            {{-- Recent logs --}}
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div
                    class="flex flex-col gap-2 border-b border-slate-100 p-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-bold text-slate-900 dark:text-white">Aktivitas Persetujuan Terbaru</h2>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Jejak audit tindakan pada pengajuan
                            sparepart.</p>
                    </div>
                    <a href="{{ route('purchase_requests.index') }}"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-300">Buka
                        seluruh pengajuan <i class="fas fa-arrow-right ml-1"></i></a>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($recentLogs as $log)
                        @php
                            $action = strtolower((string) $log->action);
                            $isRejected = $action === 'rejected';
                            $isApproved = in_array($action, ['approved', 'approve'], true);
                        @endphp
                        <div class="flex items-start gap-3 p-4 sm:items-center sm:px-5">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $isRejected ? 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300' : ($isApproved ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300') }}">
                                <i
                                    class="fas {{ $isRejected ? 'fa-times' : ($isApproved ? 'fa-check' : 'fa-history') }}"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    {{ ucfirst($log->action ?? 'Aktivitas') }} ·
                                    {{ $log->purchaseRequest->nama_part ?? 'Pengajuan tidak tersedia' }}</p>
                                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Oleh
                                    {{ $log->approvedBy->name ?? 'Unknown' }}</p>
                            </div>
                            <time
                                class="shrink-0 text-right text-xs text-slate-400">{{ optional($log->created_at)->format('d M Y, H:i') ?? '-' }}</time>
                        </div>
                    @empty
                        <div class="p-10 text-center"><i class="fas fa-inbox text-3xl text-slate-300"></i>
                            <p class="mt-2 text-sm text-slate-500">Belum ada aktivitas persetujuan.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <p class="text-center text-xs text-slate-400">SPV Dashboard · Data ditampilkan berdasarkan status dan log
                pengajuan yang tersimpan.</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function spvDashboard() {
            return {
                today: new Intl.DateTimeFormat('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }).format(new Date())
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#64748b';
            const gridColor = isDark ? 'rgba(148,163,184,.12)' : 'rgba(148,163,184,.18)';

            const trendCanvas = document.getElementById('spvTrendChart');
            if (trendCanvas && typeof Chart !== 'undefined') {
                new Chart(trendCanvas, {
                    type: 'line',
                    data: {
                        labels: @json($trendLabels),
                        datasets: [{
                            label: 'Pengajuan',
                            data: @json($trendValues),
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99,102,241,.12)',
                            fill: true,
                            tension: .35,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#6366f1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            intersect: false,
                            mode: 'index'
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 12,
                                displayColors: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: textColor
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: textColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            }
                        }
                    }
                });
            }

            const decisionCanvas = document.getElementById('spvDecisionChart');
            if (decisionCanvas && typeof Chart !== 'undefined') {
                const approved = {{ (int) $approvedRequests }};
                const rejected = {{ (int) $rejectedRequests }};
                new Chart(decisionCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Disetujui', 'Ditolak'],
                        datasets: [{
                            data: [approved, rejected],
                            backgroundColor: ['#10b981', '#f43f5e'],
                            borderColor: isDark ? '#0f172a' : '#ffffff',
                            borderWidth: 4,
                            hoverOffset: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    color: textColor,
                                    usePointStyle: true,
                                    padding: 18
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
