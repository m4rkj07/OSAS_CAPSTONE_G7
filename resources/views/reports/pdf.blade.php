<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" href="{{ asset('logo/bcplogin.png') }}">
    <title>Reports PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937; /* Tailwind text-gray-800 */
            padding: 24px;
        }

        h2 {
            font-size: 20px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 12px;
            color: #111827; /* Tailwind gray-900 */
            border-bottom: 2px solid #d1d5db; /* gray-300 */
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        th {
            background-color: #f3f4f6; /* gray-100 */
            color: #111827; /* gray-900 */
            text-align: left;
            padding: 8px;
            border: 1px solid #e5e7eb; /* gray-200 */
            font-weight: 600;
        }

        td {
            padding: 8px;
            border: 1px solid #e5e7eb; /* gray-200 */
            color: #374151; /* gray-700 */
        }

        tr:nth-child(even) {
            background-color: #f9fafb; /* gray-50 */
        }
    </style>
</head>
<body>
    <h2>Report Summary</h2>

    @if (!empty($filters['status']) || !empty($filters['esi']) || !empty($filters['month']))
        <p><strong>Filters:</strong></p>
        <ul style="margin: 0 0 10px 15px; padding: 0;">
            @if (!empty($filters['status']))
                <li>Status: {{ ucfirst($filters['status']) }}</li>
            @endif

            @if (!empty($filters['esi']))
                <li>ISI Level: {{ $filters['esi'] }}</li>
            @endif

            @if (!empty($filters['month']))
                @php
                    $monthNames = collect(explode(',', $filters['month']))
                        ->map(fn($m) => \Carbon\Carbon::create()->month((int) $m)->format('F'))
                        ->implode(', ');
                @endphp
                <li>Month: {{ $monthNames }}</li>
            @endif
        </ul>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Descriptive Title</th>
                <th>Reporter</th>
                <th>ISI Level</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reports as $report)
                <tr>
                    <td>{{ $report->id }}</td>
                    <td>{{ $report->description }}</td>
                    <td>{{ $report->reported_by }}</td>
                    <td>{{ $report->esi_level }}</td>
                    <td>{{ ucfirst($report->status) }}</td>
                    <td>{{ $report->created_at->format('F d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
