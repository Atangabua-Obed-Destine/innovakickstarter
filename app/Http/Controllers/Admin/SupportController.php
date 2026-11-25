<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FellowTrack;
use App\Models\HelpRequest;
use App\Models\Notification;
use App\Models\Track;
use App\Models\User;
use App\Models\WeeklyStanding;
use App\Services\StandingService;
use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Support: fellows who need help (early warning), the weekly bar, and "Ask a human" questions. */
class SupportController extends Controller
{
    public function index(Request $request, SupportService $support, StandingService $standing)
    {
        $trackId = $request->query('track');
        $lastWeek = $standing->weekStart(now()->subWeek());
        $atRisk = $support->atRisk($trackId);
        $trackOf = FellowTrack::where('is_primary', true)->pluck('track_id', 'fellow_id');
        $byTrack = User::role('fellow')->whereIn('id', FellowTrack::where('is_primary', true)->select('fellow_id'))->orderBy('name')->get(['id', 'name'])
            ->groupBy(fn ($u) => $trackOf[$u->id] ?? '');

        return view('admin.support.index', [
            'tab' => $request->query('tab', 'risk'),
            'tracks' => Track::orderBy('name')->get(['id', 'name']),
            'trackId' => $trackId,
            'atRisk' => $atRisk,
            'classmates' => fn (User $u) => ($byTrack[$trackOf[$u->id] ?? ''] ?? collect())->where('id', '!=', $u->id),
            'settings' => $standing->settings(),
            'lastWeek' => $lastWeek,
            'standings' => WeeklyStanding::whereDate('week_start', $lastWeek->toDateString())->with('fellow:id,name,email', 'fee')
                ->when($trackId, fn ($q) => $q->where('track_id', $trackId))->orderBy('met')->orderByDesc('consecutive_misses')->get(),
            'recentMisses' => WeeklyStanding::where('met', false)->where('week_start', '>=', now()->subWeeks(6)->toDateString())->with('fellow:id,name', 'fee')->orderByDesc('week_start')->limit(50)->get(),
            'help' => HelpRequest::with('fellow:id,name', 'activity:id,title', 'answerer:id,name')->orderByRaw("status = 'open' DESC")->latest()->limit(60)->get(),
        ]);
    }

    public function excuse(Request $request, WeeklyStanding $standing, StandingService $service)
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:10', 'max:500']]);
        $service->excuse($standing, $request->user(), $data['reason']);

        return back()->with('success', "Week excused for {$standing->fellow->name}" . ($standing->fee_id ? ' and its penalty waived.' : '.'));
    }

    public function buddy(Request $request, User $fellow, SupportService $support)
    {
        $data = $request->validate(['buddy_id' => ['required', 'exists:users,id'], 'note' => ['nullable', 'string', 'max:500']]);
        abort_if((int) $data['buddy_id'] === $fellow->id, 422);
        $support->assignBuddy($fellow, User::findOrFail($data['buddy_id']), $request->user(), $data['note'] ?? null);

        return back()->with('success', "Support buddy assigned to {$fellow->name}. Both were notified.");
    }

    public function nudge(Request $request, User $fellow)
    {
        $data = $request->validate(['message' => ['required', 'string', 'min:10', 'max:1000']]);
        Notification::send($fellow, Notification::TYPE_SYSTEM_ANNOUNCEMENT, '💬 A message from the I-NNOVA team', $data['message'],
            ['action_url' => route('curriculum.index'), 'action_label' => 'Open my curriculum']);

        return back()->with('success', "Message sent to {$fellow->name}.");
    }

    public function answer(Request $request, HelpRequest $help)
    {
        $data = $request->validate(['answer' => ['required', 'string', 'min:5', 'max:3000']]);
        $help->update(['answer' => $data['answer'], 'status' => 'answered', 'answered_by' => $request->user()->id, 'answered_at' => now()]);
        Notification::send($help->fellow, Notification::TYPE_NEW_MESSAGE, '🙋 The team answered your question',
            Str::limit($data['answer'], 200), ['action_url' => route('help.index'), 'action_label' => 'Read the answer']);

        return back()->with('success', 'Answer sent.');
    }
}
