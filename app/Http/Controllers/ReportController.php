<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{

    public function apiReports()
    {
        $reports = Report::with(['user', 'assignedOfficer'])
            ->where('transfer_report', 'Clinic')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($report) {
                return [
                    'id' => $report->id,
                    'description' => $report->description,
                    'full_description' => $report->full_description,
                    'status' => $report->status,
                    'esi_level' => $report->esi_level,
                    'incident_type' => $report->incident_type,
                    'location' => $report->location,
                    'created_at' => $report->created_at->toDateTimeString(),

                    'reported_by' => $report->reported_by,
                    'contact_info' => $report->contact_info,

                    'user' => $report->user ? [
                        'id' => $report->user->id,
                        'name' => $report->user->name,
                        'email' => $report->user->email,
                        'role' => $report->user->role ?? null, 
                        'contact_info' => $report->user->contact_info ?? null, 
                    ] : null,

                    'assigned_officer' => $report->assignedOfficer ? [
                        'id' => $report->assignedOfficer->id,
                        'name' => $report->assignedOfficer->name,
                        'email' => $report->assignedOfficer->email,
                        'role' => $report->assignedOfficer->role ?? null, 
                    ] : null,

                    'evidence_image' => $report->evidence_image
                        ? asset('storage/' . $report->evidence_image)
                        : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $reports,
        ]);
    }

   public function index(Request $request)
    {
        $query = Report::active()->with('user', 'assignedOfficer');


        if ($esi = $request->get('esi')) {
            $query->where('esi_level', $esi);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($incidentTypes = $request->get('incident_type')) {
            $query->whereIn('incident_type', (array)$incidentTypes);
        }

        $sortableColumns = ['esi_level', 'status', 'created_at', 'assigned_officer'];
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        if (!in_array($sort, $sortableColumns)) {
            $sort = 'created_at';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        if ($sort === 'status') {
            $query->orderBy('status', $direction)
                ->orderBy('created_at', 'desc');
        } elseif ($sort === 'assigned_officer') {
            $currentOfficerId = auth()->id(); 

            $query->leftJoin('users as officers', 'reports.assigned_officer_id', '=', 'officers.id')
                ->select('reports.*')
                ->orderByRaw("CASE WHEN reports.assigned_officer_id = ? THEN 0 ELSE 1 END", [$currentOfficerId])
                ->orderByRaw("CASE WHEN reports.assigned_officer_id IS NULL THEN 1 ELSE 0 END ASC")
                ->orderBy('officers.last_name', $direction)
                ->orderBy('officers.name', $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $reports = $query->get();

        return view('reports.index', [
            'reports' => $reports,
            'moduleLocked' => $request->get('module_locked') === 'reports',
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'incident_type' => 'required|string|max:255',
            'full_description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'reported_by' => 'required|string|max:255',
            'contact_info' => 'required|string|max:255',
            'esi_level' => 'required|integer|min:1|max:5',
            'evidence_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        if ($request->hasFile('evidence_image')) {
            $validatedData['evidence_image'] = $request->file('evidence_image')->store('evidence_images', 'public');
        }

        $validatedData['user_id'] = auth()->id();
        $validatedData['status'] = 'pending';

        $report = Report::create($validatedData);

        return redirect()->back()->with('success', 'Report created successfully!');
    }

    public function archived() {
        $reports = Report::where('archived', true)->orderBy('created_at', 'desc')->get();
        return view('reports.archived', compact('reports'));
    }
   
    public function completed() {
        $reports = Report::where('status', 'completed')->orderBy('created_at', 'desc')->get();
        return view('reports.completed', compact('reports'));
    }
    
    public function inProgress() {
        $reports = Report::where('status', 'in progress')->orderBy('created_at', 'desc')->get();
        return view('reports.in_progress', compact('reports'));
    }
    
    public function pending() {
        $reports = Report::where('status', 'pending')->orderBy('created_at', 'desc')->get();
        return view('reports.pending', compact('reports'));
    }
    
    public function deny() {
        $reports = Report::where('status', 'deny')->orderBy('created_at', 'desc')->get();
        return view('reports.deny', compact('reports'));
    }

    public function edit(Report $report)
    {
        $officers = User::where('role', 'officer')->get();

        return view('reports.edit', compact('report', 'officers'));
    }


    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Validate all inputs together
        $validatedData = $request->validate([
            'transfer_report' => 'nullable|string|max:50',
            'status' => 'required|string',
            'esi_level' => 'required|integer',
            'assigned_officer_id' => 'nullable|exists:users,id',
        ]);

        if (
            $report->transfer_report === ($validatedData['transfer_report'] ?? null) &&
            $report->status === $validatedData['status'] &&
            $report->esi_level == $validatedData['esi_level'] &&
            $report->assigned_officer_id == ($validatedData['assigned_officer_id'] ?? null)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'No changes detected.',
            ]);
        }

        // Update with validated data
        $report->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Report updated successfully.',
        ]);
    }

    // **Updated dashboard
    


    public function destroy(Report $report)
    {
        try {
            if ($report->evidence_image) {
                Storage::disk('public')->delete($report->evidence_image);
            }
            $report->delete();

            return response()->json(['success' => true, 'message' => 'Report deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete the report.']);
        }
    }

    public function destroyUser(Report $report)
    {
        if ($report->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            if ($report->evidence_image) {
                Storage::disk('public')->delete($report->evidence_image);
            }

            $report->delete();

            return response()->json(['success' => true, 'message' => 'Report deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete the report.']);
        }
    }

    public function userReport()
    {
        $reports = Report::where('user_id', auth()->id())->orderBy('created_at', 'desc')->get();

        return view('user.report', compact('reports'));
    }

    public function archive(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        $report->archived = true;
        $report->save();

        if ($request->ajax()) {
            return response()->json(['message' => 'Report archived successfully']);
        }

        return redirect()->back()->with('success', 'Report archived successfully');
    }

    public function bulkDelete(Request $request)
    {
        Report::whereIn('id', $request->ids)->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:reports,id',
        ]);

        Report::whereIn('id', $request->ids)->update(['archived' => true]);

        return response()->json(['message' => 'Selected reports have been archived.']);
    }

    public function bulkUnarchive(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || !is_array($ids)) {
            return response()->json(['message' => 'No reports selected.'], 400);
        }

        Report::whereIn('id', $ids)->update(['archived' => false]);

        return response()->json(['message' => 'Reports successfully unarchived.']);
    }

    public function show(Report $report)
    {
        return view('user.show', compact('report'));
    }

}