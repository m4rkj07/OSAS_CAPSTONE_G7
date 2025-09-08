<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class IncidentMapController extends Controller
{
    public function index()
    {
        return view('incidents.map');
    }

    public function getActiveIncidents()
    {
        try {
            $incidents = Report::where('status', '!=', 'completed')
                             ->where('status', '!=', 'deny')
                             ->where('archived', 0)
                             ->orderBy('created_at', 'desc')
                             ->get();
            
            return response()->json($incidents);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getIncident($id)
    {
        try {
            $incident = Report::findOrFail($id);
            return response()->json($incident);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Incident not found'], 404);
        }
    }
}