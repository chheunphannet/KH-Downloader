<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MetricsController extends Controller
{
    public function index()
    {
        // 1. Current Real-time Load (Redis)
        $activeDownloads = (int) Redis::get('active_downloads_count') ?: 0;

        // 2. Aggregate Stats by Site
        $siteStats = DB::table('site_metrics')
            ->select('site_name', 'total_processes as total_requests', 'updated_at')
            ->get();

        // 3. Recent Activity (Last 10 requests)
        $recentLogs = DB::table('user_logs')
            ->select('ip_address', 'site_requested as site', 'page_url', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 4. Calculate Grand Total
        $totalProcessed = $siteStats->sum('total_requests');

        return response()->json([
            'status' => 'success',
            'summary' => [
                'current_server_load' => "$activeDownloads / 5",
                'grand_total_processed' => $totalProcessed,
            ],
            'by_site' => $siteStats,
            'recent_activity' => $recentLogs
        ]);
    }
}
