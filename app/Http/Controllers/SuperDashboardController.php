<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Routing\Controller as BaseController;
use Carbon\CarbonImmutable;
use Throwable;

class SuperDashboardController extends BaseController
{
    public function index()
    {
        $totalRequests = PurchaseRequest::count();
        $approvedRequests = PurchaseRequest::where('status', 'PO')->count();
        $pendingRequestsCount = PurchaseRequest::where('status', 'PR')->count();

        $rejectedRequests = RequestLog::where('action', 'rejected')
            ->whereNotNull('purchaserequest_id')
            ->distinct()
            ->count('purchaserequest_id');

        // Data Kanban: status mengikuti konvensi yang sudah digunakan aplikasi.
        $pendingRequests = PurchaseRequest::where('status', 'PR')
            ->latest()
            ->limit(8)
            ->get();

        $approvedRecent = PurchaseRequest::where('status', 'PO')
            ->latest()
            ->limit(8)
            ->get();

        $rejectedRecent = RequestLog::with(['purchaseRequest', 'approvedBy'])
            ->where('action', 'rejected')
            ->latest()
            ->limit(8)
            ->get()
            ->filter(fn ($log) => $log->purchaseRequest !== null)
            ->values();

        $recentLogs = RequestLog::with(['purchaseRequest', 'approvedBy'])
            ->latest()
            ->limit(8)
            ->get();

        // Tren jumlah pengajuan per bulan dalam enam bulan terakhir.
        $trendLabels = [];
        $trendValues = [];
        $monthStart = CarbonImmutable::now()->startOfMonth()->subMonths(5);

        for ($i = 0; $i < 6; $i++) {
            $start = $monthStart->addMonths($i);
            $end = $start->endOfMonth();

            $trendLabels[] = $start->translatedFormat('M Y');
            $trendValues[] = PurchaseRequest::whereBetween('created_at', [
                $start->startOfDay(),
                $end->endOfDay(),
            ])->count();
        }

        $decisionTotal = $approvedRequests + $rejectedRequests;
        $approvalRate = $decisionTotal > 0
            ? round(($approvedRequests / $decisionTotal) * 100)
            : 0;

        // Mempertahankan mekanisme notifikasi PR pending yang sudah ada.
        // Notifikasi dibatasi oleh last_notified_at di checkPendingRequests().
        $this->checkPendingRequests();

        return view('Super.dashboard', compact(
            'totalRequests',
            'approvedRequests',
            'rejectedRequests',
            'pendingRequestsCount',
            'pendingRequests',
            'approvedRecent',
            'rejectedRecent',
            'recentLogs',
            'trendLabels',
            'trendValues',
            'approvalRate'
        ));
    }

    /**
     * Kirim pengingat untuk pengajuan berstatus PR yang belum dinotifikasi
     * dalam satu jam terakhir. Method ini dipertahankan dari controller lama.
     */
    public function checkPendingRequests()
    {
        $superUsers = User::where('role', 'super')->get();

        if ($superUsers->isEmpty()) {
            Log::warning('No super users found for pending PR notification');
            return [
                'pending_count' => 0,
                'super_users' => 0,
            ];
        }

        $pendingRequests = PurchaseRequest::where('status', 'PR')
            ->where(function ($query) {
                $query->whereNull('last_notified_at')
                    ->orWhere('last_notified_at', '<', now()->subHour());
            })
            ->get();

        Log::info("Found {$pendingRequests->count()} pending purchase requests for notification");

        foreach ($pendingRequests as $purchaseRequest) {
            try {
                Notification::send(
                    $superUsers,
                    new \App\Notifications\PendingPurchaseRequestNotification($purchaseRequest)
                );

                $purchaseRequest->update(['last_notified_at' => now()]);

                Log::info("Pending PR notification sent for request ID: {$purchaseRequest->id}");
            } catch (Throwable $e) {
                Log::error(
                    "Failed to send pending PR notification for ID: {$purchaseRequest->id} - " . $e->getMessage()
                );
            }
        }

        return [
            'pending_count' => $pendingRequests->count(),
            'super_users' => $superUsers->count(),
        ];
    }
}
