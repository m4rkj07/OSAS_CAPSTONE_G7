<?php

namespace App\Http\Controllers;

use App\Services\GeminiAnalyticsService;
use Illuminate\Http\Request;
use App\Models\Report;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard(GeminiAnalyticsService $geminiAnalytics)
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $statuses = ['completed', 'in progress', 'pending', 'deny'];
        $counts = [];
        $growths = [];

        foreach ($statuses as $status) {
            $current = Report::where('status', $status)
                ->whereBetween('created_at', [$startOfMonth, $now])
                ->count();

            $previous = Report::where('status', $status)
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->count();

            $growth = 0;
            if ($previous > 0) {
                $growth = (($current - $previous) / $previous) * 100;
            } elseif ($current > 0) {
                $growth = 100;
            }

            $counts[$status] = $current;
            $growths[$status] = $growth;
        }

        $totalCount = array_sum($counts);

        $prevTotal = Report::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();

        $totalGrowth = 0;
        if ($prevTotal > 0) {
            $totalGrowth = (($totalCount - $prevTotal) / $prevTotal) * 100;
        } elseif ($totalCount > 0) {
            $totalGrowth = 100;
        }

        // Latest reports (you can choose to include archived or not)
        $latestReports = Report::orderBy('created_at', 'desc')
            ->where('archived', false)
            ->take(5)
            ->get();

        // ESI counts (current month, includes archived)
        $esi_counts = Report::whereBetween('created_at', [$startOfMonth, $now])
            ->select('esi_level', \DB::raw('count(*) as count'))
            ->groupBy('esi_level')
            ->pluck('count', 'esi_level');

        // Previous month's ESI counts (includes archived)
        $esi_counts_last_month = Report::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->select('esi_level', \DB::raw('count(*) as count'))
            ->groupBy('esi_level')
            ->pluck('count', 'esi_level');

        // ESI growths
        $esiLevels = [1, 2, 3, 4];

        // Filter current month counts
        $esi_counts = array_intersect_key($esi_counts->toArray(), array_flip($esiLevels));

        // Filter last month counts
        $esi_counts_last_month = array_intersect_key($esi_counts_last_month->toArray(), array_flip($esiLevels));

        // Calculate growth
        $esi_growths = [];
        foreach ($esiLevels as $level) {
            $currentCount = $esi_counts[$level] ?? 0;
            $previousCount = $esi_counts_last_month[$level] ?? 0;
            if ($previousCount > 0) {
                $esi_growths[$level] = (($currentCount - $previousCount) / $previousCount) * 100;
            } elseif ($currentCount > 0) {
                $esi_growths[$level] = 100;
            } else {
                $esi_growths[$level] = 0;
            }
        }

        $incidentCounts = Report::select('incident_type', \DB::raw('count(*) as count'))
            ->groupBy('incident_type')
            ->pluck('count','incident_type')
            ->toArray();

        $overallTotal = Report::count();

        $allIncidentTypes = [
            'Medical / Health',
            'Behavioral / Disciplinary',
            'Safety / Security',
            'Environmental / Facility-Related Incident',
            'Natural Disasters & Emergency Events',
            'Technology / Cyber Incident',
            'Administrative / Policy Violations',
            'Lost & Found',
        ];

        $dataForAi = [
            'statusCounts' => $counts,
            'statusGrowths' => $growths,
            'totalCount' => $totalCount,
            'totalGrowth' => $totalGrowth,
            'esiCounts' => $esi_counts,
            'esiGrowths' => $esi_growths,
            'incidentCounts' => $incidentCounts,
        ];

        $aiInsights = $geminiAnalytics->analyzeReports($dataForAi);

        return view('dashboard', [
            'completeCount' => $counts['completed'],
            'completeGrowth' => $growths['completed'],
            'inProgressCount' => $counts['in progress'],
            'inProgressGrowth' => $growths['in progress'],
            'pendingCount' => $counts['pending'],
            'pendingGrowth' => $growths['pending'],
            'denyCount' => $counts['deny'],
            'denyGrowth' => $growths['deny'],
            'totalCount' => $totalCount,
            'totalGrowth' => $totalGrowth,
            'latestReports' => $latestReports,
            'esi_counts' => $esi_counts,
            'esi_growths' => $esi_growths,
            'overallTotal' => $overallTotal,
            'incidentCounts' => $incidentCounts,
            'allIncidentTypes' => $allIncidentTypes,
            'aiInsights' => $aiInsights,
        ]);
    }

/**
 * Calculate percentage growth
 */
    private function calculateGrowth($previous, $current)
    {
        if ($previous == 0 && $current == 0) {
            return 0;
        } elseif ($previous == 0) {
            return 100;
        }

        return round((($current - $previous) / $previous) * 100, 1); 
    }


    public function reportsChartData()
    {
        $reportsData = Report::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($reportsData);
    }

    public function incidentTypeChartData()
    {
        $incidentTypes = [
            'Medical / Health',
            'Behavioral / Disciplinary',
            'Safety / Security',
            'Environmental / Facility-Related Incident',
            'Natural Disasters & Emergency Events',
            'Technology / Cyber Incident',
            'Administrative / Policy Violations',
            'Lost & Found',
        ];

        $data = [];
        foreach ($incidentTypes as $type) {
            $count = Report::where('incident_type', $type)->count();
            $data[] = [
                'type' => $type,
                'count' => $count
            ];
        }

        return response()->json($data);
    }

}
