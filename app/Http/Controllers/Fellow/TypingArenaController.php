<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use App\Models\TypingRace;
use App\Models\TypingRaceEntry;
use App\Services\TypingArenaService;
use App\Services\TypingService;
use Illuminate\Http\Request;

/** Typing Arena: live races by polling. */
class TypingArenaController extends Controller
{
    public function __construct(protected TypingArenaService $arena) {}

    public function index(Request $request, TypingService $typing)
    {
        return view('fellow.typing.arena', [
            'lobby' => $this->arena->lobby($request->user()),
            'leagues' => TypingService::LEAGUES,
            'stats' => $typing->stats($request->user()),
            'boards' => $typing->leaderboards(),
        ]);
    }

    public function join(Request $request)
    {
        $race = $this->arena->join($request->user());

        return response()->json($this->arena->state($race, $request->user()));
    }

    public function state(Request $request, TypingRace $race)
    {
        abort_unless(TypingRaceEntry::where('race_id', $race->id)->where('fellow_id', $request->user()->id)->exists(), 403);

        return response()->json($this->arena->state($race, $request->user()));
    }

    public function progress(Request $request, TypingRace $race)
    {
        $data = $request->validate(['typed' => ['required', 'integer', 'min:0', 'max:5000'], 'errors' => ['nullable', 'integer', 'min:0', 'max:5000'],
            'finished' => ['nullable', 'boolean'], 'accuracy' => ['nullable', 'numeric', 'min:0', 'max:100']]);

        return response()->json($this->arena->progress($race, $request->user(), $data));
    }
}
