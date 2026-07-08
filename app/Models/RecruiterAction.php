<?php

namespace App\Models;

use App\Enums\SubscriptionTier;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RecruiterAction Model
 * 
 * Tracks all recruiter interactions with fellows in the marketplace.
 * Actions include: profile views, contact requests, shortlisting, messaging.
 * 
 * Action Types:
 * - view: Viewed fellow profile
 * - contact_request: Requested contact details (uses credits for Free tier)
 * - shortlist: Added fellow to shortlist
 * - message: Sent message to fellow
 * - download_resume: Downloaded fellow's resume
 * - interview_invite: Sent interview invitation
 * 
 * @property string $id UUID
 * @property int $recruiter_id
 * @property int $fellow_id
 * @property string $action_type
 * @property array|null $metadata
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class RecruiterAction extends Model
{
    use HasFactory, HasUuids;

    /**
     * The primary key type.
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'recruiter_id',
        'fellow_id',
        'track_id',
        'action',
        'credits_used',
        'metadata',
        'ip_address',
        'user_agent',
        'notes',
        'source',
        'search_filters',
        'intro_status',
        'intro_message',
        'pipeline_stage',
        'pipeline_folder',
        'company_name',
        'job_title',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'credits_used' => 'integer',
            'metadata' => 'array',
            'search_filters' => 'array',
        ];
    }

    // ==========================================
    // BACKWARD COMPATIBILITY
    // ==========================================

    /**
     * Alias: 'action_type' reads from 'action' column.
     */
    public function getActionTypeAttribute(): ?string
    {
        return $this->attributes['action'] ?? null;
    }

    /**
     * Alias: setting 'action_type' writes to 'action' column.
     */
    public function setActionTypeAttribute($value): void
    {
        $this->attributes['action'] = $value;
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (RecruiterAction $action) {
            // Capture IP and user agent
            if (request()) {
                $action->ip_address = $action->ip_address ?? request()->ip();
                $action->user_agent = $action->user_agent ?? substr(request()->userAgent(), 0, 500);
            }
        });
    }

    // ==========================================
    // CONSTANTS
    // ==========================================

    public const ACTION_VIEW = 'view';
    public const ACTION_CONTACT_REQUEST = 'contact_request';
    public const ACTION_SHORTLIST = 'shortlist';
    public const ACTION_REMOVE_SHORTLIST = 'remove_shortlist';
    public const ACTION_MESSAGE = 'message';
    public const ACTION_DOWNLOAD_RESUME = 'download_resume';
    public const ACTION_INTERVIEW_INVITE = 'interview_invite';
    public const ACTION_SAVE_SEARCH = 'save_search';

    // Credit costs for Free tier recruiters
    public const CREDITS_CONTACT_REQUEST = 1;
    public const CREDITS_DOWNLOAD_RESUME = 2;
    public const CREDITS_INTERVIEW_INVITE = 3;

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the recruiter who performed the action.
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    /**
     * Get the fellow who was the target.
     */
    public function fellow(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fellow_id');
    }

    /**
     * Get the track context (if applicable).
     */
    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get action type label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_VIEW => 'Viewed Profile',
            self::ACTION_CONTACT_REQUEST => 'Requested Contact',
            self::ACTION_SHORTLIST => 'Added to Shortlist',
            self::ACTION_REMOVE_SHORTLIST => 'Removed from Shortlist',
            self::ACTION_MESSAGE => 'Sent Message',
            self::ACTION_DOWNLOAD_RESUME => 'Downloaded Resume',
            self::ACTION_INTERVIEW_INVITE => 'Sent Interview Invite',
            self::ACTION_SAVE_SEARCH => 'Saved Search',
            default => ucfirst(str_replace('_', ' ', $this->action ?? '')),
        };
    }

    /**
     * Get action icon.
     */
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            self::ACTION_VIEW => 'eye',
            self::ACTION_CONTACT_REQUEST => 'phone',
            self::ACTION_SHORTLIST => 'star',
            self::ACTION_REMOVE_SHORTLIST => 'star-off',
            self::ACTION_MESSAGE => 'mail',
            self::ACTION_DOWNLOAD_RESUME => 'download',
            self::ACTION_INTERVIEW_INVITE => 'calendar',
            self::ACTION_SAVE_SEARCH => 'bookmark',
            default => 'activity',
        };
    }

    /**
     * Get action color.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            self::ACTION_VIEW => 'blue',
            self::ACTION_CONTACT_REQUEST => 'purple',
            self::ACTION_SHORTLIST => 'yellow',
            self::ACTION_MESSAGE => 'teal',
            self::ACTION_DOWNLOAD_RESUME => 'green',
            self::ACTION_INTERVIEW_INVITE => 'indigo',
            default => 'gray',
        };
    }

    /**
     * Check if action uses credits.
     */
    public function getUsesCreditsAttribute(): bool
    {
        return in_array($this->action, [
            self::ACTION_CONTACT_REQUEST,
            self::ACTION_DOWNLOAD_RESUME,
            self::ACTION_INTERVIEW_INVITE,
        ]);
    }

    /**
     * Get time ago.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope by action type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('action', $type);
    }

    /**
     * Scope to views.
     */
    public function scopeViews($query)
    {
        return $query->where('action', self::ACTION_VIEW);
    }

    /**
     * Scope to contact requests.
     */
    public function scopeContactRequests($query)
    {
        return $query->where('action', self::ACTION_CONTACT_REQUEST);
    }

    /**
     * Scope to shortlists.
     */
    public function scopeShortlists($query)
    {
        return $query->where('action', self::ACTION_SHORTLIST);
    }

    /**
     * Scope to messages.
     */
    public function scopeMessages($query)
    {
        return $query->where('action', self::ACTION_MESSAGE);
    }

    /**
     * Scope to actions that used credits.
     */
    public function scopeUsedCredits($query)
    {
        return $query->where('credits_used', '>', 0);
    }

    /**
     * Scope to today's actions.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope to this week's actions.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope to this month's actions.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Order by most recent.
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Get credit cost for an action.
     */
    public static function getCreditCost(string $actionType): int
    {
        return match($actionType) {
            self::ACTION_CONTACT_REQUEST => self::CREDITS_CONTACT_REQUEST,
            self::ACTION_DOWNLOAD_RESUME => self::CREDITS_DOWNLOAD_RESUME,
            self::ACTION_INTERVIEW_INVITE => self::CREDITS_INTERVIEW_INVITE,
            default => 0,
        };
    }

    /**
     * Record a profile view.
     */
    public static function recordView(
        User $recruiter,
        User $fellow,
        ?Track $track = null
    ): self {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'track_id' => $track?->id,
            'action_type' => self::ACTION_VIEW,
        ]);
    }

    /**
     * Record a contact request.
     */
    public static function recordContactRequest(
        User $recruiter,
        User $fellow,
        ?Track $track = null,
        int $creditsUsed = 0
    ): self {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'track_id' => $track?->id,
            'action_type' => self::ACTION_CONTACT_REQUEST,
            'credits_used' => $creditsUsed,
        ]);
    }

    /**
     * Record a shortlist action.
     */
    public static function recordShortlist(
        User $recruiter,
        User $fellow,
        ?Track $track = null,
        bool $added = true
    ): self {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'track_id' => $track?->id,
            'action_type' => $added ? self::ACTION_SHORTLIST : self::ACTION_REMOVE_SHORTLIST,
        ]);
    }

    /**
     * Record a message sent.
     */
    public static function recordMessage(
        User $recruiter,
        User $fellow,
        array $metadata = []
    ): self {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'action_type' => self::ACTION_MESSAGE,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Record resume download.
     */
    public static function recordResumeDownload(
        User $recruiter,
        User $fellow,
        int $creditsUsed = 0
    ): self {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'action_type' => self::ACTION_DOWNLOAD_RESUME,
            'credits_used' => $creditsUsed,
        ]);
    }

    /**
     * Record interview invitation.
     */
    public static function recordInterviewInvite(
        User $recruiter,
        User $fellow,
        array $metadata = [],
        int $creditsUsed = 0
    ): self {
        return static::create([
            'recruiter_id' => $recruiter->id,
            'fellow_id' => $fellow->id,
            'action_type' => self::ACTION_INTERVIEW_INVITE,
            'metadata' => $metadata,
            'credits_used' => $creditsUsed,
        ]);
    }

    /**
     * Check if recruiter has already performed action on fellow today.
     */
    public static function hasPerformedToday(
        User $recruiter,
        User $fellow,
        string $actionType
    ): bool {
        return static::where('recruiter_id', $recruiter->id)
            ->where('fellow_id', $fellow->id)
            ->where('action', $actionType)
            ->today()
            ->exists();
    }

    /**
     * Check if fellow is shortlisted by recruiter.
     */
    public static function isShortlisted(User $recruiter, User $fellow): bool
    {
        $lastAction = static::where('recruiter_id', $recruiter->id)
            ->where('fellow_id', $fellow->id)
            ->whereIn('action', [self::ACTION_SHORTLIST, self::ACTION_REMOVE_SHORTLIST])
            ->latest()
            ->first();

        return $lastAction?->action === self::ACTION_SHORTLIST;
    }
}
