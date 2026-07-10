<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MentorshipPod;
use App\Models\MentorshipPodMember;
use App\Models\Track;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MentorshipPodController extends Controller
{
    /**
     * Display a listing of mentorship pods.
     */
    public function index(Request $request)
    {
        $query = MentorshipPod::with(['track', 'lead', 'activeMembers'])
            ->withCount('activeMembers');

        // Apply filters
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'closed') {
                $query->where('is_active', false);
            }
        } else {
            $query->active(); // Default to active
        }

        if ($request->has('track_id') && $request->track_id !== 'all') {
            $query->where('track_id', $request->track_id);
        }

        $pods = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();
        $tracks = Track::active()->ordered()->get();

        return view('admin.mentorship-pods.index', compact('pods', 'tracks'));
    }

    /**
     * Show the form for creating a new pod.
     */
    public function create(Request $request)
    {
        $tracks = Track::active()->ordered()->get();
        $selectedTrackId = $request->query('track_id');

        return view('admin.mentorship-pods.create', compact('tracks', 'selectedTrackId'));
    }

    /**
     * Store a newly created pod in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'track_id' => ['required', 'exists:tracks,id'],
            'lead_id' => ['required', 'exists:users,id'],
            'member_ids' => ['required', 'array', 'min:1', 'max:3'], // Lead + 1-3 members = 2-4 max
            'member_ids.*' => ['exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $trackId = $validated['track_id'];
        $leadId = $validated['lead_id'];
        $memberIds = $validated['member_ids'];

        // Make sure lead is in the members list
        if (!in_array($leadId, $memberIds)) {
            $memberIds[] = $leadId;
        }

        // Validate max members (2-4 size requirement)
        if (count($memberIds) < 2 || count($memberIds) > 4) {
            return back()->withInput()->with('error', 'A mentorship pod must have between 2 and 4 members (including the lead).');
        }

        // Validate all members are eligible
        $eligibleFellows = $this->getEligibleFellowsQuery($trackId)->pluck('id')->toArray();
        foreach ($memberIds as $id) {
            if (!in_array($id, $eligibleFellows)) {
                return back()->withInput()->with('error', 'One or more selected fellows are not eligible for this track or are already in a pod.');
            }
        }

        try {
            DB::beginTransaction();

            $pod = MentorshipPod::create([
                'track_id' => $trackId,
                'lead_id' => $leadId,
                'name' => $validated['name'] ?? null,
                'max_members' => 4,
                'created_by' => auth()->id(),
            ]);

            // Add members
            foreach ($memberIds as $fellowId) {
                MentorshipPodMember::create([
                    'pod_id' => $pod->id,
                    'fellow_id' => $fellowId,
                ]);

                // Notify member
                if ($fellowId == $leadId) {
                    Notification::create([
                        'user_id' => $fellowId,
                        'type' => 'pod_lead_assigned',
                        'title' => 'You are a Pod Lead! 👑',
                        'message' => 'You have been assigned to lead a new Mentorship Pod. Go to your pod page to set up your team\'s name and emoji.',
                        'icon' => 'crown',
                        'color' => 'accent',
                        'action_url' => route('mentorship-pod.show'),
                        'action_text' => 'Set Up Pod',
                    ]);
                } else {
                    Notification::create([
                        'user_id' => $fellowId,
                        'type' => 'pod_assigned',
                        'title' => 'You\'ve been added to a Mentorship Pod! 🫂',
                        'message' => 'You are now part of a pod. Meet your team and start collaborating!',
                        'icon' => 'users',
                        'color' => 'primary',
                        'action_url' => route('mentorship-pod.show'),
                        'action_text' => 'Meet Your Pod',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.mentorship-pods.show', $pod)
                ->with('success', 'Mentorship pod created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'An error occurred while creating the pod: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified pod.
     */
    public function show(MentorshipPod $pod)
    {
        $pod->load(['track', 'lead', 'activeMembers.fellow']);
        
        // Enhance members with current stats
        $members = $pod->activeMembers->map(function ($member) use ($pod) {
            $fellow = $member->fellow;
            // Get the specific track enrollment for this pod
            $track = \App\Models\FellowTrack::where('fellow_id', $fellow->id)
                ->where('track_id', $pod->track_id)
                ->first();
            
            return (object) [
                'id' => $fellow->id,
                'member_id' => $member->id,
                'name' => $fellow->name,
                'avatar' => $fellow->avatar,
                'is_lead' => $pod->isLead($fellow),
                'joined_at' => $member->joined_at,
                'score' => $track ? $track->score : 0,
                'tier' => $track && $track->tierEnum ? [
                    'label' => $track->tierEnum->label(),
                    'color' => $track->tierEnum->hexColor(),
                    'icon' => $track->tierEnum->icon(),
                ] : null,
                'score_breakdown' => $track ? $track->score_breakdown : null,
            ];
        })->sortByDesc('score')->values();

        return view('admin.mentorship-pods.show', compact('pod', 'members'));
    }

    /**
     * Close a pod manually.
     */
    public function close(MentorshipPod $pod)
    {
        if (!$pod->is_active) {
            return back()->with('error', 'This pod is already closed.');
        }

        try {
            DB::beginTransaction();
            $pod->close();
            DB::commit();

            return redirect()->route('admin.mentorship-pods.index')
                ->with('success', 'Mentorship pod has been closed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error closing pod: ' . $e->getMessage());
        }
    }

    /**
     * Remove a member from a pod.
     */
    public function removeMember(MentorshipPod $pod, User $fellow)
    {
        if (!$pod->is_active) {
            return back()->with('error', 'Cannot remove members from a closed pod.');
        }

        if ($pod->isLead($fellow)) {
            return back()->with('error', 'Cannot remove the Pod Lead. You must close the pod or reassign the lead first.');
        }

        $membership = $pod->activeMembers()->where('fellow_id', $fellow->id)->first();
        
        if (!$membership) {
            return back()->with('error', 'Fellow is not an active member of this pod.');
        }

        $membership->update([
            'is_active' => false,
            'left_at' => now(),
        ]);

        return back()->with('success', 'Member removed successfully.');
    }

    /**
     * API endpoint to get eligible fellows for a track.
     */
    public function eligibleFellows(Request $request)
    {
        $trackId = $request->query('track_id');
        
        if (!$trackId) {
            return response()->json([]);
        }

        $fellows = $this->getEligibleFellowsQuery($trackId)
            ->select('users.id', 'users.name')
            // Get their track score and tier for display
            ->with(['fellowTracks' => function ($query) use ($trackId) {
                $query->where('track_id', $trackId)->select('fellow_id', 'score', 'tier');
            }])
            ->get()
            ->map(function ($user) {
                $track = $user->fellowTracks->first();
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'score' => $track ? number_format($track->score, 1) : '0.0',
                    'tier' => $track ? \App\Enums\Tier::from($track->tier)->label() : 'Rookie',
                ];
            })
            ->sortByDesc('score')
            ->values();

        return response()->json($fellows);
    }

    /**
     * Helper to get base query for eligible fellows.
     * Rules:
     * 1. Approved enrollment in the track
     * 2. Active internship profile
     * 3. Not currently in an active pod
     */
    private function getEligibleFellowsQuery(string $trackId)
    {
        return User::whereHas('roles', function ($query) {
                $query->where('name', 'fellow');
            })
            ->whereHas('fellowTracks', function ($query) use ($trackId) {
                $query->where('track_id', $trackId)
                      ->where('status', 'approved');
            })
            ->whereHas('internshipProfile', function ($query) {
                $query->where('status', 'active');
            })
            ->whereDoesntHave('mentorshipPodMembership');
    }
}
