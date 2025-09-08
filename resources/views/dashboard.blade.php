<x-admin-layout>
    <div class="bg-gray-50 shadow-sm sm:rounded-lg overflow-hidden">
        <div class="p-8 text-gray-900 space-y-8">
            <div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Dashboard</h2>

                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Overview of report statuses and growth trends.
                    </p>
                    <span class="px-3 py-1 text-xs font-semibold bg-blue-100 text-blue-700 rounded-full">
                        Updated: {{ now()->format('M d, Y') }}
                    </span>
                </div>
            </div>


            <!-- Top Row: Analytics (Left) and Risk Level (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Report Analytics -->
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Report Analytics</h2>
                            <p class="text-sm text-gray-500">Year {{ now()->year }} • Monthly report insights</p>
                        </div>
                    </div>

                    <div class="relative w-full h-72 mt-4">
                        <canvas id="reportsChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Report Types Pie Chart -->
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">Report Types</h2>
                            <p class="text-sm text-gray-500">Distribution of reports by type</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-[1fr_1fr] gap-6 items-start">
                        <!-- Chart container -->
                        <div class="h-48 relative flex justify-center items-center">
                            <div class="w-48 h-48">
                                <canvas id="incidentTypeChart" class="w-full h-full"></canvas>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @foreach ($allIncidentTypes as $type)
                                @php
                                    $colorMap = [
                                        'Medical / Health' => '#ef4444',
                                        'Behavioral / Disciplinary' => '#f59e0b', 
                                        'Safety / Security' => '#ea580c',
                                        'Environmental / Facility-Related Incident' => '#10b981',
                                        'Natural Disasters & Emergency Events' => '#8b5cf6',
                                        'Technology / Cyber Incident' => '#3b82f6',
                                        'Administrative / Policy Violations' => '#6b7280',
                                        'Lost & Found' => '#06b6d4',
                                    ];
                                    $hexColor = $colorMap[$type] ?? '#6b7280';

                                    $count = $incidentCounts[$type] ?? 0;
                                    $percentage = $overallTotal > 0 ? round(($count / $overallTotal) * 100, 1) : 0;
                                @endphp
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full" style="background-color: {{ $hexColor }};"></span>
                                        <span class="text-gray-700 font-medium text-sm">{{ $type }}</span>
                                    </div>
                                    <span class="text-gray-600 text-sm font-semibold">{{ $percentage }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔹 AI Insights Section -->
            <div>
                <!-- Header -->
                <!-- <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">AI Insights</h2>
                </div> -->

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- 📊 Trend Summary -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Trend Summary</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            {{ $aiInsights['trend_summary'] ?? 'No AI summary available.' }}
                        </p>
                    </div>

                    <!-- 🔮 Predictions -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Predictions</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            {{ $aiInsights['predictions'] ?? 'No AI prediction available.' }}
                        </p>
                    </div>

                    <!-- ✅ Recommendations -->
                    <div class="bg-white rounded-xl p-4 border border-gray-100 shadow-sm">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">Recommendations</h3>
                        <ul class="text-sm text-gray-600 space-y-2 list-disc list-inside">
                            @if(isset($aiInsights['recommendations']) && is_array($aiInsights['recommendations']))
                                @foreach($aiInsights['recommendations'] as $rec)
                                    <li>{{ $rec }}</li>
                                @endforeach
                            @else
                                <li>No recommendations available.</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Status Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                @foreach([
                    ['label' => 'Complete', 'icon' => 'check-circle', 'count' => $completeCount, 'growth' => $completeGrowth, 'color' => 'green', 'route' => null],
                    ['label' => 'In-Progress', 'icon' => 'spinner', 'count' => $inProgressCount, 'growth' => $inProgressGrowth, 'color' => 'blue', 'route' => null],
                    ['label' => 'Pending', 'icon' => 'clock', 'count' => $pendingCount, 'growth' => $pendingGrowth, 'color' => 'yellow', 'route' => null],
                    ['label' => 'Denied', 'icon' => 'times-circle', 'count' => $denyCount, 'growth' => $denyGrowth, 'color' => 'red', 'route' => null],
                    ['label' => 'Total Reports', 'icon' => 'file-alt', 'count' => $totalCount, 'growth' => $totalGrowth, 'color' => 'blue', 'route' => 'reports.index'],
                ] as $card)
                    @php
                        $baseClasses = 'bg-white p-5 rounded-xl border border-gray-200 shadow transition-all duration-200';
                    @endphp

                    @if ($card['route'])
                        <a href="{{ route($card['route']) }}" class="{{ $baseClasses }} hover:shadow-lg">
                    @else
                        <div class="{{ $baseClasses }} ">
                    @endif
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-500">{{ $card['label'] }}</span>
                            <i class="fas fa-{{ $card['icon'] }} text-lg text-{{ $card['color'] }}-500"></i>
                        </div>
                        <div class="text-3xl font-bold text-gray-800">{{ $card['count'] }}</div>
                        <div class="text-sm mt-1 {{ $card['growth'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $card['growth'] >= 0 ? '+' : '' }}{{ number_format($card['growth'], 1) }}% from last month
                        </div>
                    @if ($card['route'])
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Middle Row: Recent Reports (Left) and Pie Chart (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Reports Table -->
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Reports</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead class="text-xs uppercase font-semibold tracking-wider">
                                <tr>
                                    <th class="px-4 py-3">Title</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Risk</th>
                                    <th class="px-4 py-3">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($latestReports as $report)
                                    <tr 
                                        onclick="window.location='{{ route('reports.edit', $report->id) }}'" 
                                        class="hover:bg-gray-50 transition cursor-pointer"
                                    >
                                        <td class="px-4 py-3 text-blue-700 hover:text-blue-900 hover:underline max-w-xs break-words whitespace-normal text-sm">
                                            {{ Str::limit($report->description, 40) }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $icons = [
                                                    'completed' => ['icon' => 'check-circle', 'color' => 'green'],
                                                    'pending' => ['icon' => 'clock', 'color' => 'yellow'],
                                                    'deny' => ['icon' => 'times-circle', 'color' => 'red'],
                                                    'in progress' => ['icon' => 'spinner', 'color' => 'blue'],
                                                ];
                                                $status = strtolower($report->status);
                                                $icon = $icons[$status] ?? ['icon' => 'info-circle', 'color' => 'gray'];
                                            @endphp
                                            <span class="text-xs font-semibold text-gray-700">
                                                <i class="fas fa-{{ $icon['icon'] }} mr-1 text-{{ $icon['color'] }}-500"></i>
                                                {{ strtoupper(substr($status, 0, 8)) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @php
                                                $level = $report->esi_level;
                                                $esiLabels = [1 => 'Critical', 2 => 'High', 3 => 'Medium', 4 => 'Low'];
                                                $esiColors = [1 => 'text-red-600', 2 => 'text-orange-600', 3 => 'text-yellow-600', 4 => 'text-green-600'];
                                                $esiIcons = [1 => 'fa-exclamation-circle', 2 => 'fa-exclamation-triangle', 3 => 'fa-minus-circle', 4 => 'fa-check-circle'];
                                            @endphp
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold {{ $esiColors[$level] ?? 'text-gray-600' }}">
                                                <i class="fas {{ $esiIcons[$level] ?? 'fa-question-circle' }}"></i>
                                                {{ $esiLabels[$level] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600 text-xs">
                                            {{ \Carbon\Carbon::parse($report->created_at)->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">No reports available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Risk Level -->
                <div class="bg-white p-6 rounded-2xl shadow-lg">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Risk Assessment</h2>
                    <p class="text-sm text-gray-500">Distribution of reports by risk level</p>

                    @php
                        $esiLevels = [
                            1 => ['label' => '1 - Critical', 'color' => 'bg-red-500'],
                            2 => ['label' => '2 - High', 'color' => 'bg-orange-500'],
                            3 => ['label' => '3 - Medium', 'color' => 'bg-yellow-500'],
                            4 => ['label' => '4 - Low', 'color' => 'bg-green-500'],
                        ];
                    @endphp

                    <div class="space-y-4">
                        @foreach ($esiLevels as $level => $info)
                            @php
                                $count = $esi_counts[$level] ?? 0;
                                $growth = $esi_growths[$level] ?? 0;
                                $isPositive = $growth >= 0;
                            @endphp

                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="h-4 w-4 rounded-full {{ $info['color'] }}"></span>
                                    <span class="text-gray-700 font-medium text-lg">{{ $info['label'] }}</span>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <span class="text-gray-900 font-bold text-2xl">{{ $count }}</span>
                                    <span class="{{ $isPositive ? 'text-red-600' : 'text-green-600' }} text-sm font-medium">
                                        {{ $isPositive ? '+' : '' }}{{ number_format($growth, 1) }}%
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            fetch("/reports/chart-data")
                .then(response => response.json())
                .then(data => {
                    const ctx = document.getElementById('reportsChart').getContext('2d');
                    const monthNames = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
                    const labels = data.map(entry => monthNames[entry.month - 1]);
                    const counts = data.map(entry => entry.count);

                    // Build chart
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: "Monthly Reports",
                                data: counts,
                                backgroundColor: "rgba(59,130,246,0.2)",
                                borderColor: "rgba(59,130,246,1)",
                                borderWidth: 2,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { grid: { display: false } },
                                y: { beginAtZero: true, grid: { color: "#e5e7eb" } }
                            },
                            plugins: {
                                legend: { display: false }
                            }
                        }
                    });

                    // --- Details Section ---
                    if (counts.length > 0) {
                        const total = counts.reduce((a, b) => a + b, 0);
                        const maxVal = Math.max(...counts);
                        const minVal = Math.min(...counts);
                        const peakMonth = labels[counts.indexOf(maxVal)];
                        const lowMonth = labels[counts.indexOf(minVal)];

                        document.getElementById("totalReports").innerText = total;
                        document.getElementById("peakMonth").innerText = `${peakMonth} (${maxVal})`;
                        document.getElementById("lowMonth").innerText = `${lowMonth} (${minVal})`;
                    }
                })
                .catch(error => console.error("Chart load error:", error));
        });

        fetch("/reports/incident-type-chart-data")
            .then(res => res.json())
            .then(data => {
                const allTypes = [
                    'Medical / Health',
                    'Behavioral / Disciplinary',
                    'Safety / Security',
                    'Environmental / Facility-Related Incident',
                    'Natural Disasters & Emergency Events',
                    'Technology / Cyber Incident',
                    'Administrative / Policy Violations',
                    'Lost & Found',
                    'Others'
                ];

                const countsMap = {};
                data.forEach(item => { countsMap[item.type] = item.count; });

                const labels = allTypes;
                const counts = allTypes.map(type => countsMap[type] ?? 0);

                const colorMap = {
                    'Medical / Health': '#ef4444',
                    'Behavioral / Disciplinary': '#f59e0b',
                    'Safety / Security': '#ea580c',
                    'Environmental / Facility-Related Incident': '#10b981',
                    'Natural Disasters & Emergency Events': '#8b5cf6',
                    'Technology / Cyber Incident': '#3b82f6',
                    'Administrative / Policy Violations': '#6b7280',
                    'Lost & Found': '#06b6d4',
                };

                const borderColorMap = {
                    'Medical / Health': '#ef4444',
                    'Behavioral / Disciplinary': '#f59e0b',
                    'Safety / Security': '#ea580c',
                    'Environmental / Facility-Related Incident': '#10b981',
                    'Natural Disasters & Emergency Events': '#8b5cf6',
                    'Technology / Cyber Incident': '#3b82f6',
                    'Administrative / Policy Violations': '#6b7280',
                    'Lost & Found': '#06b6d4',
                };

                const bgColors = labels.map(l => colorMap[l]);
                const borderColors = labels.map(l => borderColorMap[l]);

                new Chart(document.getElementById('incidentTypeChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Reports',
                            data: counts,
                            backgroundColor: bgColors,
                            borderColor: borderColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        cutout: '65%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { backgroundColor: '#1E3A8A', titleColor: '#fff', bodyColor: '#fff' }
                        }
                    }
                });
            });
    </script>
</x-admin-layout>