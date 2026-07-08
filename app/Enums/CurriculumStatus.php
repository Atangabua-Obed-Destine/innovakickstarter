<?php

namespace App\Enums;

/**
 * Curriculum Status Enum
 * 
 * Defines the lifecycle states for a fellow's progress on a curriculum activity.
 * Tracks the full journey: locked → available → in_progress → submitted → 
 * peer_review → under_review → completed/rejected/overdue.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
enum CurriculumStatus: string
{
    case LOCKED = 'locked';
    case AVAILABLE = 'available';
    case IN_PROGRESS = 'in_progress';
    case SUBMITTED = 'submitted';
    case PEER_REVIEW = 'peer_review';
    case UNDER_REVIEW = 'under_review';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case OVERDUE = 'overdue';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::LOCKED => 'Locked',
            self::AVAILABLE => 'Available',
            self::IN_PROGRESS => 'In Progress',
            self::SUBMITTED => 'Submitted',
            self::PEER_REVIEW => 'Peer Review',
            self::UNDER_REVIEW => 'Under Review',
            self::COMPLETED => 'Completed',
            self::REJECTED => 'Rejected',
            self::OVERDUE => 'Overdue',
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match($this) {
            self::LOCKED => 'Complete prerequisite activities to unlock',
            self::AVAILABLE => 'Ready to start — begin when you\'re ready',
            self::IN_PROGRESS => 'Currently working on this activity',
            self::SUBMITTED => 'Awaiting review from mentor or admin',
            self::PEER_REVIEW => 'Being reviewed by an accountability partner',
            self::UNDER_REVIEW => 'Being reviewed by a mentor or admin',
            self::COMPLETED => 'Activity completed and approved',
            self::REJECTED => 'Submission was rejected — resubmit with changes',
            self::OVERDUE => 'Past the deadline — submit as soon as possible',
        };
    }

    /**
     * Get color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::LOCKED => 'gray',
            self::AVAILABLE => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::SUBMITTED => 'indigo',
            self::PEER_REVIEW => 'cyan',
            self::UNDER_REVIEW => 'purple',
            self::COMPLETED => 'green',
            self::REJECTED => 'red',
            self::OVERDUE => 'orange',
        };
    }

    /**
     * Get Tailwind badge classes
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::LOCKED => 'bg-dark-700 text-dark-400 ring-dark-600',
            self::AVAILABLE => 'bg-blue-500/20 text-blue-400 ring-blue-500/30',
            self::IN_PROGRESS => 'bg-yellow-500/20 text-yellow-400 ring-yellow-500/30',
            self::SUBMITTED => 'bg-indigo-500/20 text-indigo-400 ring-indigo-500/30',
            self::PEER_REVIEW => 'bg-cyan-500/20 text-cyan-400 ring-cyan-500/30',
            self::UNDER_REVIEW => 'bg-purple-500/20 text-purple-400 ring-purple-500/30',
            self::COMPLETED => 'bg-green-500/20 text-green-400 ring-green-500/30',
            self::REJECTED => 'bg-red-500/20 text-red-400 ring-red-500/30',
            self::OVERDUE => 'bg-orange-500/20 text-orange-400 ring-orange-500/30',
        };
    }

    /**
     * Get icon
     */
    public function icon(): string
    {
        return match($this) {
            self::LOCKED => '🔒',
            self::AVAILABLE => '🟢',
            self::IN_PROGRESS => '⏳',
            self::SUBMITTED => '📤',
            self::PEER_REVIEW => '👥',
            self::UNDER_REVIEW => '🔍',
            self::COMPLETED => '✅',
            self::REJECTED => '❌',
            self::OVERDUE => '⚠️',
        };
    }

    /**
     * Whether this status awards Career Capital points
     */
    public function awardsPoints(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Whether the fellow can submit/resubmit in this state
     */
    public function canSubmit(): bool
    {
        return in_array($this, [
            self::AVAILABLE,
            self::IN_PROGRESS,
            self::REJECTED,
            self::OVERDUE,
        ]);
    }

    /**
     * Whether this status allows the fellow to start working
     */
    public function canStart(): bool
    {
        return $this === self::AVAILABLE;
    }

    /**
     * Whether a reviewer can take action on this status
     */
    public function canReview(): bool
    {
        return in_array($this, [
            self::SUBMITTED,
            self::PEER_REVIEW,
            self::UNDER_REVIEW,
        ]);
    }

    /**
     * Whether this is a terminal state
     */
    public function isTerminal(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Whether this is an active/in-flight state
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::AVAILABLE,
            self::IN_PROGRESS,
            self::SUBMITTED,
            self::PEER_REVIEW,
            self::UNDER_REVIEW,
            self::OVERDUE,
        ]);
    }

    /**
     * Get statuses that need mentor/admin attention
     */
    public static function pendingReviewStatuses(): array
    {
        return [
            self::SUBMITTED,
            self::UNDER_REVIEW,
        ];
    }

    /**
     * Get statuses visible to fellows
     */
    public static function fellowVisible(): array
    {
        return self::cases();
    }
}
