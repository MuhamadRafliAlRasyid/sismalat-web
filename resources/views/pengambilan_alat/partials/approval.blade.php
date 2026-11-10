@php
    $approvalName = $data->approved_by_name ?: $data->approver?->name ?? 'Admin';
    $approvalRole = $data->approved_by_role ?: 'Admin / Super Admin';
@endphp

<section class="mb-6 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm">
    @if ($data->approved_at)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-semibold">
                    <i class="fas fa-check-circle"></i>
                    <span>Pengambilan alat telah disetujui</span>
                </div>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Disetujui oleh {{ $approvalName }} ({{ $approvalRole }}) pada
                    {{ $data->approved_at->format('d/m/Y H:i') }}.
                </p>
                @if ($data->verification_token)
                    <a href="{{ route('pengambilan_alat.verification', ['token' => $data->verification_token]) }}"
                        target="_blank" rel="noopener"
                        class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-emerald-700 hover:text-emerald-800 dark:text-emerald-400">
                        <i class="fas fa-qrcode"></i> Buka verifikasi QR
                    </a>
                @endif
            </div>

            <div
                class="min-w-56 rounded-xl border border-emerald-200 dark:border-emerald-900 bg-emerald-50/70 dark:bg-emerald-900/10 px-5 py-3 text-center">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanda tangan persetujuan
                </p>
                <p class="my-1 text-3xl leading-tight text-emerald-800 dark:text-emerald-300"
                    style="font-family: 'Segoe Script', 'Brush Script MT', cursive; font-style: italic;">
                    {{ $approvalName }}
                </p>
                <div class="mx-auto my-2 w-4/5 border-t border-emerald-400/70"></div>
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $approvalName }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $approvalRole }}</p>
            </div>
        </div>
    @elseif ($isAktif)
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold text-gray-800 dark:text-gray-100">Persetujuan admin</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada admin yang menyetujui pengambilan alat
                    ini.</p>
            </div>

            @if ($isAdmin)
                <form method="POST" action="{{ route('pengambilan_alat.approve', $data->hashid) }}"
                    onsubmit="return confirm('Setujui pengambilan alat ini atas nama akun admin Anda?');">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        <i class="fas fa-check"></i>
                        <span>Setujui Pengambilan</span>
                    </button>
                </form>
            @else
                <span
                    class="inline-flex items-center rounded-lg bg-amber-50 px-3 py-2 text-sm font-medium text-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                    Menunggu persetujuan admin
                </span>
            @endif
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">Transaksi telah selesai dan belum memiliki catatan
            persetujuan admin.</p>
    @endif
</section>
