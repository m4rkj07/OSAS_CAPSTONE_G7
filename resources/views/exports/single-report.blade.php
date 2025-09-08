<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="icon" type="image/png" href="{{ asset('logo/bcplogin.png') }}">
    <title>Incident Report | OSAS</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            color: #2c3e50;
            margin: 0;
            padding: 40px;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 20px;
        }

        .header img {
            width: 70px;
            height: auto;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 26px;
            color: #34495e;
            margin: 0;
            text-transform: uppercase;
        }

        .report-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #34495e;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid #bdc3c7;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .report-table th, .report-table td {
            padding: 12px 15px;
            border: 1px solid #e1e8ed;
            text-align: left;
            vertical-align: top;
        }

        .report-table th {
            width: 200px;
            background-color: #ecf0f1;
            font-weight: 600;
        }

        .description-box {
            /* Removed border, background-color, padding, and border-radius */
            min-height: 100px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }
        
        /* The following styles apply to the list of items
           as you can see, you can make it professional with these*/
        .report-item {
            margin-bottom: 10px;
        }

        .report-item h4 {
            font-size: 16px;
            margin: 0 0 5px;
            color: #34495e;
        }

        .report-item p {
            margin: 0;
            font-size: 14px;
        }
        /* End of the professional list styling */


        .evidence-image {
            margin-top: 25px;
            text-align: center;
        }

        .evidence-image img {
            max-width: 100%;
            width: 400px;
            height: auto;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .no-evidence-note {
            color: #7f8c8d;
            font-style: italic;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>

    <div class="container">

        <div class="header">
            <h1>Incident Report</h1>
        </div>

        <div class="report-section">
            <h2 class="section-title">Report Details</h2>
            <table class="report-table">
                <tbody>
                    <tr>
                        <th>Incident Title</th>
                        <td>{{ $report['description'] }}</td>
                    </tr>
                    <tr>
                        <th>Location</th>
                        <td>{{ $report['location'] }}</td>
                    </tr>
                    <tr>
                        <th>Date & Time Reported</th>
                        <td>{{ \Carbon\Carbon::parse($report['created_at'])->format('F d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Reported By</th>
                        <td>{{ ucwords(strtolower($report['reported_by'])) }}</td>
                    </tr>
                    <tr>
                        <th>Contact Information</th>
                        <td>{{ $report['contact_info'] ?? 'Not Provided' }}</td>
                    </tr>
                    <tr>
                        <th>Severity Level (ISI)</th>
                        <td>{{ $report['esi_level'] }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ ucfirst($report['status']) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="report-section">
            <h2 class="section-title">Full Description of Incident</h2>
            <div class="description-box">
                @if (!empty($report['full_description']))
                    {!! nl2br(e($report['full_description'])) !!}
                @else
                    <p>No detailed description was provided for this incident.</p>
                @endif
            </div>

        </div>

        <div class="report-section">
            <h2 class="section-title">Evidence</h2>
            @if (!empty($report['evidence_image']))
                @php
                    $filename = basename($report['evidence_image']);
                @endphp

                @if (file_exists(public_path('storage/evidence_images/' . $filename)))
                    <div class="evidence-image">
                        <img src="{{ 'file://' . public_path('storage/evidence_images/' . $filename) }}" alt="Evidence Image">
                    </div>
                @else
                    <p class="no-evidence-note">Evidence image not found in storage.</p>
                @endif
            @else
                <p class="no-evidence-note">No evidence image was attached to this report.</p>
            @endif

        </div>

    </div>

</body>
</html>