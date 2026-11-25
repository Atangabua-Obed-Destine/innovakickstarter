<?php

namespace App\Http\Controllers;

use App\Models\EmployerBrief;
use Illuminate\Http\Request;

/** A recruiter sees the shortlisted and winning fellows of briefs linked to them. */
class RecruiterBriefController extends Controller
{
    public function __invoke(Request $request)
    {
        $briefs = EmployerBrief::with(['track', 'submissions' => fn ($q) => $q->whereIn('status', ['shortlisted', 'winner'])->with('fellow')])
            ->where('recruiter_id', $request->user()->id)
            ->where('status', '!=', 'draft')
            ->latest()
            ->get();

        return view('recruiter.briefs', compact('briefs'));
    }
}
