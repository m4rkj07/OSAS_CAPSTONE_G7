<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportApiController extends Controller
{
    public function index()
    {
        return response()->json(Report::all());
    }
}
