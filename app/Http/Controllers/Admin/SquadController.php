<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Squad;
use App\Models\SquadMember;
use App\Models\TrackMilestone;
use App\Models\User;
use App\Services\SquadService;
use Illuminate\Http\Request;

/** Admin: form and adjust squads for squad chapters (e.g. Software Engineering's Squad Sprint). */
class SquadController extends Controller
{
    public function __construct(protected SquadService $squads) {}

    public function index(Request $request)
    {
        $chapters = TrackMilestone::with('track:id,name')->whereNotNull('squad_size')->where('squad_size', '>', 0)->where('is_active', true)->orderBy('track_id')->orderBy('sequence_order')->get();
        $chapter = $chapters->firstWhere('id', $request->query('chapter')) ?? $chapters->first();
        $squads = $chapter ? Squad::where('milestone_id', $chapter->id)->with('members.fellow:id,name,email,github_url')->orderBy('name')->get() : collect();
        $waiting = $chapter ? $this->squads->waiting($chapter) : collect();

        return view('admin.squads.index', compact('chapters', 'chapter', 'squads', 'waiting'));
    }

    public function form(Request $request, TrackMilestone $chapter)
    {
        abort_unless($this->squads->isSquadChapter($chapter), 404);
        $size = (int) $request->validate(['size' => ['nullable', 'integer', 'min:2', 'max:6']])['size'] ?: null;
        $made = $this->squads->form($chapter, $size, $request->user());

        return back()->with($made->isEmpty() ? 'error' : 'success', $made->isEmpty() ? 'At least 2 fellows must be waiting to form a squad.' : "{$made->count()} squad(s) formed; members were notified.");
    }

    public function assign(Request $request, Squad $squad)
    {
        $data = $request->validate(['fellow_id' => ['required', 'integer', 'exists:users,id']]);
        $this->squads->assign(User::findOrFail($data['fellow_id']), $squad);

        return back()->with('success', 'Fellow added to ' . $squad->name . '.');
    }

    public function remove(SquadMember $member)
    {
        $member->delete();

        return back()->with('success', 'Fellow removed from the squad. They are back in the waiting list.');
    }

    public function destroy(Squad $squad)
    {
        $name = $squad->name;
        $this->squads->dissolve($squad);

        return back()->with('success', "{$name} dissolved. Its members are back in the waiting list.");
    }
}
