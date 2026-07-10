<?php

namespace App\Models;

use App\Enums\FellowType;
use App\Enums\UserRole;
use App\Enums\Tier;
use App\Models\AdminSetting;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 * 
 * Base user model for all IKS platform users.
 * Supports roles: fellow, admin, mentor, recruiter.
 * 
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $email
 * @property string|null $username
 * @property UserRole $role
 * @property string|null $location
 * @property string|null $availability
 * @property bool $is_public
 * @property bool $is_active
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'username',
        'password',
        'role',
        'phone',
        'avatar_url',
        'bio',
        'location',
        'availability',
        'linkedin_url',
        'github_url',
        'portfolio_url',
        'resume_url',
        'salary_min',
        'salary_max',
        'salary_currency',
        'is_public',
        'is_active',
        'open_to_opportunities',
        'last_login_at',
        'last_login_ip',
        'profile_completed_at',
        'current_program_id',
        'suspended_at',
        'suspension_reason',
        'company_name',
        'company_website',
        'headline',
        'skills',
        'mentor_availability',
        'mentor_specializations',
        'fellow_type',
        'onboarding_completed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'last_login_ip',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_public' => 'boolean',
            'is_active' => 'boolean',
            'open_to_opportunities' => 'boolean',
            'last_login_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'fellow_type' => FellowType::class,
            'suspended_at' => 'datetime',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'skills' => 'array',
            'mentor_availability' => 'array',
            'mentor_specializations' => 'array',
        ];
    }

    /**
     * Determine if the user has verified their email address.
     *
     * Returns true unconditionally when the admin has disabled the
     * `email_verification_required` platform setting.
     */
    public function hasVerifiedEmail(): bool
    {
        if (!AdminSetting::get('email_verification_required', true)) {
            return true;
        }

        return !is_null($this->email_verified_at);
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (User $user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
            if (empty($user->username) && !empty($user->name)) {
                $user->username = static::generateUsername($user->name);
            }
        });
    }

    /**
     * Generate a unique username from name.
     */
    public static function generateUsername(string $name): string
    {
        $base = Str::slug($name, '-');
        $username = $base;
        $counter = 1;

        while (static::where('username', $username)->exists()) {
            $username = $base . '-' . $counter;
            $counter++;
        }

        return $username;
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get all tracks the user is enrolled in (fellows only).
     */
    public function fellowTracks(): HasMany
    {
        return $this->hasMany(FellowTrack::class, 'fellow_id');
    }

    /**
     * Get all tracks via the pivot table.
     */
    public function tracks()
    {
        return $this->belongsToMany(Track::class, 'fellow_tracks', 'fellow_id', 'track_id')
            ->withPivot(['score', 'tier', 'is_primary', 'effort_allocation'])
            ->withTimestamps();
    }

    /**
     * Get all cohorts the user is enrolled in (fellows only).
     */
    public function cohorts()
    {
        return $this->belongsToMany(Cohort::class, 'cohort_fellows', 'fellow_id', 'cohort_id')
            ->using(CohortFellow::class)
            ->withPivot([
                'id', 'status', 'enrolled_at', 'completed_at', 'dropped_at',
                'drop_reason', 'cohort_score', 'activities_completed',
                'interviews_completed', 'weeks_active', 'rank', 'enrolled_by', 'notes'
            ])
            ->withTimestamps();
    }

    /**
     * Get all programs the user is enrolled in (fellows only).
     */
    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_fellows', 'fellow_id', 'program_id')
            ->using(ProgramFellow::class)
            ->withPivot([
                'id', 'status', 'enrolled_at', 'activated_at', 'completed_at', 
                'dropped_at', 'drop_reason', 'certificate_issued', 'certificate_number',
                'milestones_completed', 'employment_status', 'employer_name', 
                'job_title', 'enrolled_by', 'notes'
            ])
            ->withTimestamps();
    }

    /**
     * Get the current/active program for this fellow.
     */
    public function currentProgram()
    {
        return $this->belongsTo(Program::class, 'current_program_id');
    }

    /**
     * Get program enrollment record for current program.
     */
    public function getProgramEnrollment(): ?ProgramFellow
    {
        if (!$this->current_program_id) {
            return null;
        }

        return ProgramFellow::where('program_id', $this->current_program_id)
            ->where('fellow_id', $this->id)
            ->first();
    }

    /**
     * Get the primary track for a fellow (DB-based, permanent).
     * Used for recruiter-facing views and external profiles.
     */
    public function primaryTrack(): HasOne
    {
        return $this->hasOne(FellowTrack::class, 'fellow_id')
            ->where('is_primary', true);
    }

    /**
     * Get the active track for the current session.
     * 
     * This checks the session for a fellow-selected active track first,
     * falling back to the DB primary track. Used throughout the fellow portal
     * so switching tracks in the header immediately changes all pages.
     *
     * @return FellowTrack|null
     */
    public function activeTrack(): ?FellowTrack
    {
        $sessionTrackId = session('active_track_id');

        if ($sessionTrackId) {
            $fellowTrack = FellowTrack::where('fellow_id', $this->id)
                ->where('track_id', $sessionTrackId)
                ->approved()
                ->with('track')
                ->first();

            if ($fellowTrack) {
                return $fellowTrack;
            }

            // Session had a stale or unapproved track_id — clear it
            session()->forget('active_track_id');
        }

        $primary = $this->primaryTrack?->load('track');
        if ($primary && $primary->isApproved()) {
            return $primary;
        }

        // Fall back to the first approved track (in case primary is pending / rejected)
        return FellowTrack::where('fellow_id', $this->id)
            ->approved()
            ->with('track')
            ->orderByDesc('is_primary')
            ->orderByDesc('score')
            ->first();
    }

    /**
     * Get all activities submitted by this user.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'fellow_id');
    }

    /**
     * Get all interview sessions for this user.
     */
    public function interviewSessions(): HasMany
    {
        return $this->hasMany(InterviewSession::class, 'fellow_id');
    }

    /**
     * Get interviews conducted by this user (mentors).
     */
    public function conductedInterviews(): HasMany
    {
        return $this->hasMany(InterviewSession::class, 'interviewer_id');
    }

    /**
     * Get recruiter actions (for recruiters).
     */
    public function recruiterActions(): HasMany
    {
        return $this->hasMany(RecruiterAction::class, 'recruiter_id');
    }

    /**
     * Get actions taken on this user by recruiters (for fellows).
     */
    public function receivedRecruiterActions(): HasMany
    {
        return $this->hasMany(RecruiterAction::class, 'fellow_id');
    }

    /**
     * Get internship profile (for academic/corporate fellows).
     */
    public function internshipProfile(): HasOne
    {
        return $this->hasOne(InternshipProfile::class);
    }

    /**
     * Get subscription (for recruiters).
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'recruiter_id');
    }

    /**
     * Get audit logs for this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'fellow_id');
    }

    /**
     * Get audit logs created by this admin.
     */
    public function createdAuditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'admin_id');
    }

    /**
     * Get weekly progress records.
     */
    public function weeklyProgress(): HasMany
    {
        return $this->hasMany(WeeklyProgress::class, 'fellow_id');
    }

    /**
     * Get notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    // --- Curriculum System Relationships ---

    /**
     * Get all curriculum progress records for this fellow.
     */
    public function curriculumProgress(): HasMany
    {
        return $this->hasMany(FellowCurriculumProgress::class, 'fellow_id');
    }

    /**
     * Get curriculum progress reviewed by this user (mentor/admin).
     */
    public function reviewedCurriculumProgress(): HasMany
    {
        return $this->hasMany(FellowCurriculumProgress::class, 'reviewer_id');
    }

    /**
     * Get all streaks for this fellow.
     */
    public function streaks(): HasMany
    {
        return $this->hasMany(FellowStreak::class, 'fellow_id');
    }

    /**
     * Get all badges earned by this fellow.
     */
    public function badges(): HasMany
    {
        return $this->hasMany(FellowBadge::class, 'fellow_id');
    }

    /**
     * Get accountability pairs where this user is fellow A.
     */
    public function accountabilityPairsAsA(): HasMany
    {
        return $this->hasMany(AccountabilityPair::class, 'fellow_a_id');
    }

    /**
     * Get accountability pairs where this user is fellow B.
     */
    public function accountabilityPairsAsB(): HasMany
    {
        return $this->hasMany(AccountabilityPair::class, 'fellow_b_id');
    }

    /**
     * Get all accountability pairs for this fellow (both sides).
     */
    public function allAccountabilityPairs()
    {
        return AccountabilityPair::where('fellow_a_id', $this->id)
            ->orWhere('fellow_b_id', $this->id);
    }

    // --- Mentorship Pod Relationships ---

    /**
     * Get the fellow's active mentorship pod membership.
     */
    public function mentorshipPodMembership(): HasOne
    {
        return $this->hasOne(MentorshipPodMember::class, 'fellow_id')
            ->where('is_active', true);
    }

    /**
     * Get the fellow's active mentorship pod (via membership).
     */
    public function activeMentorshipPod(): ?MentorshipPod
    {
        $membership = $this->mentorshipPodMembership;
        return $membership?->pod;
    }

    /**
     * Check if this fellow is a Pod Lead of any active pod.
     */
    public function isPodLead(): bool
    {
        return MentorshipPod::where('lead_id', $this->id)
            ->where('is_active', true)
            ->exists();
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get user's first name.
     */
    public function getFirstNameAttribute(): string
    {
        return explode(' ', $this->name)[0];
    }

    /**
     * Get user's initials.
     */
    public function getInitialsAttribute(): string
    {
        $parts = explode(' ', $this->name);
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }
        return $initials;
    }

    /**
     * Get avatar URL or generate placeholder.
     */
    public function getAvatarAttribute(): string
    {
        if ($this->avatar_url) {
            return $this->avatar_url;
        }
        // Generate UI Avatars placeholder
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . 
               '&background=7C3AED&color=fff&size=128';
    }

    /**
     * Get Career Capital score for primary track.
     */
    public function getPrimaryScoreAttribute(): float
    {
        return $this->primaryTrack?->score ?? 0.00;
    }

    /**
     * Get tier for primary track.
     */
    public function getPrimaryTierAttribute(): ?Tier
    {
        $tier = $this->primaryTrack?->tier;
        return $tier ? Tier::from($tier) : null;
    }

    /**
     * Get formatted salary range.
     */
    public function getSalaryRangeAttribute(): ?string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return null;
        }

        $currency = $this->salary_currency ?? 'XAF';
        $min = $this->salary_min ? number_format($this->salary_min) : null;
        $max = $this->salary_max ? number_format($this->salary_max) : null;

        if ($min && $max) {
            return "{$currency} {$min} - {$max}";
        }
        if ($min) {
            return "{$currency} {$min}+";
        }
        return "Up to {$currency} {$max}";
    }

    /**
     * Get public profile URL.
     */
    public function getProfileUrlAttribute(): string
    {
        $track = $this->primaryTrack?->track;
        $trackSlug = $track?->slug ?? 'general';
        return route('public.profile', ['username' => $this->username, 'track' => $trackSlug]);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope to fellows only.
     */
    public function scopeFellows($query)
    {
        return $query->where('role', UserRole::FELLOW);
    }

    /**
     * Scope to admins only.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', UserRole::ADMIN);
    }

    /**
     * Scope to mentors only.
     */
    public function scopeMentors($query)
    {
        return $query->where('role', UserRole::MENTOR);
    }

    /**
     * Scope to recruiters only.
     */
    public function scopeRecruiters($query)
    {
        return $query->where('role', UserRole::RECRUITER);
    }

    /**
     * Scope to active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to public profiles.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to users open to opportunities.
     */
    public function scopeOpenToOpportunities($query)
    {
        return $query->where('open_to_opportunities', true);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check if user is a fellow.
     */
    public function isFellow(): bool
    {
        return $this->role === UserRole::FELLOW;
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Check if user is a mentor.
     */
    public function isMentor(): bool
    {
        return $this->role === UserRole::MENTOR;
    }

    /**
     * Check if user is a recruiter.
     */
    public function isRecruiter(): bool
    {
        return $this->role === UserRole::RECRUITER;
    }

    /**
     * Check if profile is complete.
     */
    public function hasCompletedProfile(): bool
    {
        return !is_null($this->profile_completed_at);
    }

    /**
     * Get total Career Capital score (weighted across tracks).
     */
    public function getTotalCareerCapital(): float
    {
        $tracks = $this->fellowTracks;
        
        if ($tracks->isEmpty()) {
            return 0.00;
        }

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($tracks as $fellowTrack) {
            $weight = $fellowTrack->effort_allocation;
            $weightedSum += $fellowTrack->score * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : 0.00;
    }

    /**
     * Get total interviews completed.
     */
    public function getTotalInterviewsCompleted(): int
    {
        return $this->interviewSessions()
            ->where('status', 'completed')
            ->count();
    }

    /**
     * Check if user can access admin panel.
     */
    public function canAccessAdmin(): bool
    {
        return $this->role->canAccessAdmin();
    }

    /**
     * Check if the fellow is enrolled in a specific track.
     */
    public function isEnrolledIn($track): bool
    {
        $trackId = $track instanceof \App\Models\Track ? $track->id : $track;

        return $this->fellowTracks()
            ->where('track_id', $trackId)
            ->exists();
    }

    /**
     * Get the active subscription for this recruiter.
     * Returns the current active or trial subscription, or null.
     */
    public function activeSubscription(): ?Subscription
    {
        return $this->subscription()
            ->whereIn('status', [
                \App\Enums\SubscriptionStatus::ACTIVE,
                \App\Enums\SubscriptionStatus::TRIAL,
            ])
            ->first();
    }

    /**
     * Check if the user has an active subscription.
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Check if the user's profile is complete enough to be considered "complete".
     */
    public function isProfileComplete(): bool
    {
        return !empty($this->headline)
            && !empty($this->linkedin_url)
            && !empty($this->skills)
            && $this->fellowTracks()->exists();
    }
}
