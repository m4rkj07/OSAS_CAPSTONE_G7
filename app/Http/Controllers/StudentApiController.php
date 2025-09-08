<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StudentApiController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        $program = $request->input('program');

        $response = Http::withToken(env('REMOTE_API_TOKEN'))
            ->acceptJson()
            ->get(env('REMOTE_API_URL') . '/students');

        if ($response->successful()) {
            $students = $response->json();

            if ($query) {
                $students = collect($students)->filter(function ($student) use ($query) {
                    return str_contains(strtolower($student['name'] ?? ''), strtolower($query)) ||
                        str_contains(strtolower($student['student_number'] ?? ''), strtolower($query)) ||
                        str_contains(strtolower($student['email'] ?? ''), strtolower($query));
                })->values()->all();
            }

            if ($program) {
                $students = collect($students)->filter(function ($student) use ($program) {
                    return isset($student['program']) && $student['program'] === $program;
                })->values()->all();
            }

            return view('student', compact('students', 'query', 'program'));
        }

        abort(500, 'Failed to fetch students from API');
    }
}
