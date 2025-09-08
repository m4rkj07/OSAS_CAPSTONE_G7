<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Mpdf\Mpdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ReportExportController extends Controller
{
    // Export to PDF
    public function exportPdf(Request $request)
    {
        $query = Report::query();
        if ($request->filled('archived') && $request->archived === 'true') {
            $query->where('archived', true);
        } else {
            $query->where('archived', false);
        }

        if ($request->has('status') && $request->status !== '') {
            $statuses = explode(',', $request->status);
            $query->whereIn('status', $statuses);
        }

        if ($request->has('esi') && $request->esi !== '') {
            $esiLevels = explode(',', $request->esi);
            $query->whereIn('esi_level', $esiLevels);
        }

        if ($request->filled('ids')) {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }

        if ($request->filled('month')) {
            $months = explode(',', $request->month);
            $query->whereIn(\DB::raw('MONTH(created_at)'), $months);
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('reports.pdf', [
            'reports' => $reports,
            'filters' => [
                'status' => $request->status,
                'esi' => $request->esi,
                'month'  => $request->month,
            ]
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('report-summary.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = Report::query();
        if ($request->filled('archived') && $request->archived === 'true') {
            $query->where('archived', true);
        } else {
            $query->where('archived', false);
        }

        if ($request->filled('status')) {
            $query->whereIn('status', explode(',', $request->status));
        }

        if ($request->filled('esi')) {
            $query->whereIn('esi_level', explode(',', $request->esi));
        }

        if ($request->filled('ids')) {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }

        if ($request->filled('month')) {
            $months = explode(',', $request->month);
            $query->whereIn(\DB::raw('MONTH(created_at)'), $months);
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        $writer = SimpleExcelWriter::streamDownload('report-summary.csv');

        $writer->addRow([
            'ID',
            'Title',
            'ISI Level',
            'Status',
            'Created At',
        ]);

        // ISI Level Map
        $esiLevels = [
            1 => 'Immediate',
            2 => 'Emergency',
            3 => 'Urgent',
            4 => 'Semi-Urgent',
            5 => 'Non-Urgent',
        ];

        foreach ($reports as $report) {
            $writer->addRow([
                ' ' . $report->id . ' ',
                ' ' . $report->description . ' ',
                ' ' . ($esiLevels[$report->esi_level] ?? 'Unknown') . ' ',
                ' ' . ucwords(str_replace('_', ' ', $report->status)) . ' ',
                ' ' . \Carbon\Carbon::parse($report->created_at)->format('Y-m-d H:i') . ' ',
            ]);
        }

        $writer->close();
        exit;
    }

    public function exportXls(Request $request)
    {
        $query = Report::query();
        if ($request->filled('archived') && $request->archived === 'true') {
            $query->where('archived', true);
        } else {
            $query->where('archived', false);
        }

        if ($request->filled('status')) {
            $query->whereIn('status', explode(',', $request->status));
        }

        if ($request->filled('esi')) {
            $query->whereIn('esi_level', explode(',', $request->esi));
        }

        if ($request->filled('ids')) {
            $ids = explode(',', $request->ids);
            $query->whereIn('id', $ids);
        }

        if ($request->filled('month')) {
            $months = explode(',', $request->month);
            $query->whereIn(\DB::raw('MONTH(created_at)'), $months);
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        $esiLevels = [
            1 => 'Immediate',
            2 => 'Emergency',
            3 => 'Urgent',
            4 => 'Semi-Urgent',
            5 => 'Non-Urgent',
        ];

        $headers = [
            "Content-type" => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=report-summary.xls",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $html = '<table border="1">';
        $html .= '
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>ISI Level</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>';

        foreach ($reports as $report) {
            $html .= '<tr>';
            $html .= '<td>' . $report->id . '</td>';
            $html .= '<td>' . htmlspecialchars($report->description) . '</td>';
            $html .= '<td>' . ($esiLevels[$report->esi_level] ?? 'Unknown') . '</td>';
            $html .= '<td>' . ucwords(str_replace('_', ' ', $report->status)) . '</td>';
            $html .= '<td style="mso-number-format:\'@\';">' . \Carbon\Carbon::parse($report->created_at)->format('F d, Y h:i A') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return response($html, 200, $headers);
    }

    public function viewPdf(Request $request)
    {
        $report = $request->input('report');

        if (is_string($report)) {
            $report = json_decode($report, true);
        }

        $mpdf = new Mpdf();

        $mpdf->SetProtection(['copy', 'print'], '1234', 'adminpass');

        $html = view('exports.single-report', [
            'report' => $report
        ])->render();

        $mpdf->WriteHTML($html);

        return response($mpdf->Output('report.pdf', 'S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="report.pdf"');
    }

}