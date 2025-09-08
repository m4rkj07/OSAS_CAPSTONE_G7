<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Store a new comment.
     */
    public function store(Request $request, $reportId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $report = Report::findOrFail($reportId);

        Comment::create([
            'report_id' => $report->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        return back()->with('comment_added', true);
    }

    /**
     * Get latest comments for a report.
     */
    public function getComments($reportId)
    {
        $comments = Comment::with('user') // eager load user info
            ->where('report_id', $reportId)
            ->orderBy('created_at', 'desc') // latest first
            ->get();

        return response()->json($comments);
    }
}
