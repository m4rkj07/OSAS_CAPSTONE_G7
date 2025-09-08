<?php

namespace App\Http\Controllers;

use App\Models\Prefect;
use App\Models\Report;
use Illuminate\Http\Request;

class PrefectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedSorts = ['esi_level', 'status', 'created_at'];

        $sort = in_array($request->input('sort'), $allowedSorts) ? $request->input('sort') : 'created_at';
        $direction = in_array($request->input('direction'), ['asc', 'desc']) ? $request->input('direction') : 'desc';

        $reports = Report::where('transfer_report', 'Prefect')
            ->where('archived', false)
            ->orderBy($sort, $direction)
            ->get();

        return view('prefect', compact('reports'));
    }
}
