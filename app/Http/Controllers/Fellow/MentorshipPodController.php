<?php

namespace App\Http\Controllers\Fellow;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class MentorshipPodController extends Controller
{
    /**
     * Show the fellow's mentorship pod.
     */
    public function show()
    {
        $user = auth()->user();
        $pod = $user->activeMentorshipPod();

        if (!$pod) {
            return redirect()->route('dashboard')->with('error', 'You are not currently assigned to an active Mentorship Pod.');
        }

        $pod->load(['track', 'lead', 'activeMembers.fellow']);
        $isLead = $pod->isLead($user);

        // Map and rank members
        $members = $pod->activeMembers->map(function ($member) use ($pod) {
            $fellow = $member->fellow;
            // Get the specific track enrollment for this pod
            $track = \App\Models\FellowTrack::where('fellow_id', $fellow->id)
                ->where('track_id', $pod->track_id)
                ->first();
            
            return (object) [
                'id' => $fellow->id,
                'name' => $fellow->name,
                'avatar' => $fellow->avatar,
                'is_lead' => $pod->isLead($fellow),
                'joined_at' => $member->joined_at,
                'days_in_program' => $track ? $track->days_in_track : 0,
                'score' => $track ? $track->score : 0,
                'formatted_score' => $track ? $track->formatted_score : '0.0%',
                'tier' => $track && $track->tierEnum ? [
                    'label' => $track->tierEnum->label(),
                    'color' => $track->tierEnum->hexColor(),
                    'icon' => $track->tierEnum->icon(),
                ] : null,
                'score_breakdown' => $track ? $track->score_breakdown : null,
            ];
        })->sortByDesc('score')->values();

        // Calculate rankings
        $members = $members->map(function ($member, $index) {
            $member->rank = $index + 1;
            return $member;
        });

        return view('fellow.mentorship-pod.show', compact('pod', 'isLead', 'members'));
    }

    /**
     * Update the pod's branding (Lead only).
     */
    public function updateBranding(Request $request)
    {
        $user = auth()->user();
        $pod = $user->activeMentorshipPod();

        if (!$pod || !$pod->isLead($user)) {
            return response()->json(['message' => 'Unauthorized. Only the Pod Lead can update branding.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'nickname' => ['nullable', 'string', 'max:50'],
            'emoji' => ['nullable', 'string', 'max:20'],
            'color' => ['nullable', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $isFirstTimeNaming = empty($pod->name);

        $pod->update($validated);

        // Notify other members if it's the first time naming
        if ($isFirstTimeNaming) {
            $otherMemberIds = $pod->activeMembers()
                ->where('fellow_id', '!=', $user->id)
                ->pluck('fellow_id');
                
            foreach ($otherMemberIds as $memberId) {
                Notification::create([
                    'user_id' => $memberId,
                    'type' => 'pod_named',
                    'title' => 'Your Pod has a name! 🎉',
                    'message' => "Your Pod Lead has named your pod: {$pod->display_name}. Go check it out!",
                    'icon' => 'sparkles',
                    'color' => 'primary',
                    'action_url' => route('mentorship-pod.show'),
                    'action_text' => 'View Pod',
                ]);
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pod branding updated successfully.', 'pod' => $pod]);
        }

        return back()->with('success', 'Pod branding updated successfully.');
    }
}
