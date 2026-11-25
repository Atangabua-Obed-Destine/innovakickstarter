<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\BriefSubmission;
use App\Models\EmployerBrief;
use Illuminate\Http\Request;

/** Employer mini-briefs for fellows: browse, submit, see the outcome. */
class BriefController extends Controller
{
    public function index(Request $request)
    {
        $briefs = EmployerBrief::with('track')->visibleTo($request->user())->orderByRaw("status = 'open' desc")->orderBy('deadline_on')->get();
        $mine = BriefSubmission::where('fellow_id', $request->user()->id)->get()->keyBy('brief_id');

        return view('fellow.briefs.index', compact('briefs', 'mine'));
    }

    public function show(Request $request, EmployerBrief $brief)
    {
        abort_unless(EmployerBrief::visibleTo($request->user())->whereKey($brief->id)->exists(), 404);
        $submission = BriefSubmission::where('brief_id', $brief->id)->where('fellow_id', $request->user()->id)->first();

        return view('fellow.briefs.show', [
            'brief' => $brief->load('track'),
            'submission' => $submission,
            'count' => $brief->submissions()->count(),
        ]);
    }

    public function submit(Request $request, EmployerBrief $brief)
    {
        abort_unless(EmployerBrief::visibleTo($request->user())->whereKey($brief->id)->exists(), 404);
        if (!$brief->acceptsSubmissions()) {
            return back()->with('error', 'This brief is no longer accepting submissions.');
        }
        $data = $request->validate([
            'url' => ['required', 'url', 'max:500'],
            'notes' => ['required', 'string', 'min:80', 'max:3000'],
        ], ['notes.min' => 'Explain your approach and main finding in a few sentences (at least 80 characters).']);

        $existing = BriefSubmission::where('brief_id', $brief->id)->where('fellow_id', $request->user()->id)->first();
        if ($existing && $existing->status !== 'submitted') {
            return back()->with('error', 'Your submission has already been reviewed.');
        }
        BriefSubmission::updateOrCreate(['brief_id' => $brief->id, 'fellow_id' => $request->user()->id], $data + ['status' => 'submitted']);

        return redirect()->route('briefs.show', $brief)->with('success', $existing ? 'Submission updated.' : 'Submitted! The I-NNOVA team will review it after the deadline.');
    }
}
