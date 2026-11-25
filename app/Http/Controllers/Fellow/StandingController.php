<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\WeeklyStanding;
use App\Services\StandingService;
use Illuminate\Http\Request;

/** The fellow's weekly bar: this week's meter, past weeks, holds and penalties. */
class StandingController extends Controller
{
    public function show(Request $request, StandingService $standing)
    {
        $fellow = $request->user();

        return view('fellow.standing.show', [
            'week' => $standing->thisWeek($fellow),
            'settings' => $standing->settings(),
            'state' => $standing->state($fellow),
            'penalties' => $standing->unpaidPenalties($fellow),
            'history' => WeeklyStanding::where('fellow_id', $fellow->id)->orderByDesc('week_start')->limit(12)->get(),
        ]);
    }
}
