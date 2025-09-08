<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Clinic;

class ClinicController extends Controller
{
    private function getClinicReports(Request $request)
    {
        $allowedSorts = ['esi_level', 'status', 'created_at'];

        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $direction = in_array($request->input('direction'), ['asc', 'desc']) ? $request->input('direction') : 'desc';

        return Report::where('transfer_report', 'Clinic')
            ->where('archived', false)
            ->orderBy($sort, $direction)
            ->get();
    }

    // Web view
    public function index(Request $request)
    {
        $reports = $this->getClinicReports($request);

        return view('clinic', compact('reports'));
    }

    // API endpoint (/api/clinic)
    public function apiClinicReports(Request $request)
    {
        $reports = $this->getClinicReports($request)
            ->load(['user']); 

        return response()->json([
            'status' => 'success',
            'data'   => $reports
        ]);
    }

}
