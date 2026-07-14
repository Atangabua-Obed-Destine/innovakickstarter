<?php

namespace App\Enums;

/**
 * Activity Type Enum
 * 
 * Defines all types of activities that fellows can submit
 * to earn Career Capital points. Types are organized by weekly pillar:
 * - BUILD: Technical creation (projects, prototypes, hackathons)
 * - BRAND: Public presence (posts, articles, videos)
 * - INTERVIEW: Interview readiness (mock interviews, certifications)
 * - COLLABORATE: Community & teamwork (mentoring, peer teaching)
 * - META: Reflection & growth (reflections, presentations, documentation)
 * 
 * @author IKS Engineering Team
 * @version 1.1
 */
enum ActivityType: string
{
    // BUILD Pillar — Technical Creation
    case PROJECT = 'project';
    case HACKATHON = 'hackathon';
    case OPEN_SOURCE = 'open_source';
    case FREELANCE = 'freelance';
    case COMPETITION = 'competition';
    case PROTOTYPE = 'prototype';
    case DEBUG_CHALLENGE = 'debug_challenge';

    // BRAND Pillar — Public Presence & Thought Leadership
    case BLOG_POST = 'blog_post';
    case SPEAKING = 'speaking';
    case PUBLICATION = 'publication';
    case CONFERENCE = 'conference';
    case LINKEDIN_POST = 'linkedin_post';
    case TWITTER_THREAD = 'twitter_thread';
    case VIDEO_CONTENT = 'video_content';
    case CASE_STUDY = 'case_study';

    // INTERVIEW Pillar — Interview Readiness
    case MOCK_INTERVIEW = 'mock_interview';
    case CERTIFICATION = 'certification';
    case RESEARCH = 'research';

    // COLLABORATE Pillar — Community & Teamwork
    case MENTORING = 'mentoring';
    case CODE_REVIEW = 'code_review';
    case PAIR_PROGRAMMING = 'pair_programming';
    case NETWORKING = 'networking';
    case WEBINAR = 'webinar';
    case PEER_TEACHING = 'peer_teaching';
    case COMMUNITY_CONTRIBUTION = 'community_contribution';
    case CROSS_TRACK_COLLAB = 'cross_track_collab';

    // META — Reflection & Growth
    case WORKSHOP = 'workshop';
    case REFLECTION = 'reflection';
    case PRESENTATION = 'presentation';
    case DOCUMENTATION = 'documentation';
    case OTHER = 'other';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            // Build
            self::PROJECT => 'Project',
            self::HACKATHON => 'Hackathon',
            self::OPEN_SOURCE => 'Open Source Contribution',
            self::FREELANCE => 'Freelance Work',
            self::COMPETITION => 'Competition',
            self::PROTOTYPE => 'Rapid Prototype',
            self::DEBUG_CHALLENGE => 'Debug Challenge',
            // Brand
            self::BLOG_POST => 'Blog Post',
            self::SPEAKING => 'Speaking Engagement',
            self::PUBLICATION => 'Publication',
            self::CONFERENCE => 'Conference Talk',
            self::LINKEDIN_POST => 'LinkedIn Post',
            self::TWITTER_THREAD => 'Twitter/X Thread',
            self::VIDEO_CONTENT => 'Video Content',
            self::CASE_STUDY => 'Case Study',
            // Interview
            self::MOCK_INTERVIEW => 'Mock Interview',
            self::CERTIFICATION => 'Certification',
            self::RESEARCH => 'Technical Research',
            // Collaborate
            self::MENTORING => 'Mentoring Session',
            self::CODE_REVIEW => 'Code Review',
            self::PAIR_PROGRAMMING => 'Pair Programming',
            self::NETWORKING => 'Networking Event',
            self::WEBINAR => 'Webinar Attendance',
            self::PEER_TEACHING => 'Peer Teaching',
            self::COMMUNITY_CONTRIBUTION => 'Community Contribution',
            self::CROSS_TRACK_COLLAB => 'Cross-Track Collaboration',
            // Meta
            self::WORKSHOP => 'Workshop',
            self::REFLECTION => 'Reflection',
            self::PRESENTATION => 'Presentation',
            self::DOCUMENTATION => 'Documentation',
            self::OTHER => 'Other',
        };
    }

    /**
     * Get Career Capital category this activity contributes to
     */
    public function category(): CareerCapitalCategory
    {
        return match($this) {
            self::PROJECT, self::HACKATHON, self::FREELANCE, self::OPEN_SOURCE, self::COMPETITION,
            self::PROTOTYPE, self::DEBUG_CHALLENGE => CareerCapitalCategory::TECHNICAL,
            self::BLOG_POST, self::CONFERENCE, self::SPEAKING, self::PUBLICATION,
            self::LINKEDIN_POST, self::TWITTER_THREAD, self::VIDEO_CONTENT, self::CASE_STUDY => CareerCapitalCategory::PORTFOLIO,
            self::MOCK_INTERVIEW, self::CERTIFICATION, self::RESEARCH => CareerCapitalCategory::INTERVIEW,
            self::MENTORING, self::CODE_REVIEW, self::PAIR_PROGRAMMING, self::NETWORKING,
            self::PEER_TEACHING, self::COMMUNITY_CONTRIBUTION, self::CROSS_TRACK_COLLAB => CareerCapitalCategory::COLLABORATION,
            self::WORKSHOP, self::WEBINAR, self::REFLECTION, self::PRESENTATION, self::DOCUMENTATION => CareerCapitalCategory::LEARNING,
            self::OTHER => CareerCapitalCategory::LEARNING,
        };
    }

    /**
     * Get weekly pillar this activity contributes to
     */
    public function pillar(): ?string
    {
        return match($this) {
            self::PROJECT, self::HACKATHON, self::FREELANCE, self::OPEN_SOURCE, self::COMPETITION,
            self::PROTOTYPE, self::DEBUG_CHALLENGE => 'build',
            self::BLOG_POST, self::CONFERENCE, self::SPEAKING, self::PUBLICATION,
            self::LINKEDIN_POST, self::TWITTER_THREAD, self::VIDEO_CONTENT, self::CASE_STUDY => 'brand',
            self::MOCK_INTERVIEW, self::CERTIFICATION, self::RESEARCH => 'interview',
            self::MENTORING, self::CODE_REVIEW, self::PAIR_PROGRAMMING, self::NETWORKING,
            self::PEER_TEACHING, self::COMMUNITY_CONTRIBUTION, self::CROSS_TRACK_COLLAB => 'collaborate',
            self::WORKSHOP, self::WEBINAR, self::REFLECTION, self::PRESENTATION, self::DOCUMENTATION => null,
            self::OTHER => null,
        };
    }

    /**
     * Get all activity type values belonging to a specific career capital category
     */
    public static function getValuesByCategory(CareerCapitalCategory $category): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            if ($case->category() === $category) {
                $values[] = $case->value;
            }
        }
        return $values;
    }

    /**
     * Get default points for this activity type
     * Note: Actual points come from admin_settings
     */
    public function defaultPoints(): int
    {
        return match($this) {
            // Build
            self::PROJECT => 25,
            self::HACKATHON => 30,
            self::OPEN_SOURCE => 20,
            self::FREELANCE => 25,
            self::COMPETITION => 35,
            self::PROTOTYPE => 20,
            self::DEBUG_CHALLENGE => 15,
            // Brand
            self::BLOG_POST => 15,
            self::SPEAKING => 30,
            self::PUBLICATION => 25,
            self::CONFERENCE => 25,
            self::LINKEDIN_POST => 10,
            self::TWITTER_THREAD => 10,
            self::VIDEO_CONTENT => 20,
            self::CASE_STUDY => 25,
            // Interview
            self::MOCK_INTERVIEW => 15,
            self::CERTIFICATION => 20,
            self::RESEARCH => 20,
            // Collaborate
            self::MENTORING => 15,
            self::CODE_REVIEW => 10,
            self::PAIR_PROGRAMMING => 10,
            self::NETWORKING => 10,
            self::WEBINAR => 5,
            self::PEER_TEACHING => 15,
            self::COMMUNITY_CONTRIBUTION => 10,
            self::CROSS_TRACK_COLLAB => 20,
            // Meta
            self::WORKSHOP => 10,
            self::REFLECTION => 10,
            self::PRESENTATION => 20,
            self::DOCUMENTATION => 15,
            self::OTHER => 5,
        };
    }

    /**
     * Get icon for UI
     */
    public function icon(): string
    {
        return match($this) {
            // Build
            self::PROJECT => '🚀',
            self::HACKATHON => '🏆',
            self::OPEN_SOURCE => '🌐',
            self::FREELANCE => '💼',
            self::COMPETITION => '🥇',
            self::PROTOTYPE => '⚡',
            self::DEBUG_CHALLENGE => '🐛',
            // Brand
            self::BLOG_POST => '📝',
            self::SPEAKING => '🎙️',
            self::PUBLICATION => '📰',
            self::CONFERENCE => '🎤',
            self::LINKEDIN_POST => '💼',
            self::TWITTER_THREAD => '🐦',
            self::VIDEO_CONTENT => '🎬',
            self::CASE_STUDY => '🔬',
            // Interview
            self::MOCK_INTERVIEW => '🎯',
            self::CERTIFICATION => '📜',
            self::RESEARCH => '🔍',
            // Collaborate
            self::MENTORING => '🎓',
            self::CODE_REVIEW => '👀',
            self::PAIR_PROGRAMMING => '👥',
            self::NETWORKING => '🤝',
            self::WEBINAR => '📺',
            self::PEER_TEACHING => '🏫',
            self::COMMUNITY_CONTRIBUTION => '💬',
            self::CROSS_TRACK_COLLAB => '🔄',
            // Meta
            self::WORKSHOP => '🛠️',
            self::REFLECTION => '📓',
            self::PRESENTATION => '📊',
            self::DOCUMENTATION => '📋',
            self::OTHER => '📌',
        };
    }

    /**
     * Get color for UI badges
     */
    public function color(): string
    {
        return match($this) {
            // Build
            self::PROJECT => 'purple',
            self::HACKATHON => 'amber',
            self::OPEN_SOURCE => 'cyan',
            self::FREELANCE => 'emerald',
            self::COMPETITION => 'red',
            self::PROTOTYPE => 'violet',
            self::DEBUG_CHALLENGE => 'rose',
            // Brand
            self::BLOG_POST => 'blue',
            self::SPEAKING => 'pink',
            self::PUBLICATION => 'orange',
            self::CONFERENCE => 'rose',
            self::LINKEDIN_POST => 'sky',
            self::TWITTER_THREAD => 'cyan',
            self::VIDEO_CONTENT => 'fuchsia',
            self::CASE_STUDY => 'indigo',
            // Interview
            self::MOCK_INTERVIEW => 'blue',
            self::CERTIFICATION => 'teal',
            self::RESEARCH => 'slate',
            // Collaborate
            self::MENTORING => 'green',
            self::CODE_REVIEW => 'yellow',
            self::PAIR_PROGRAMMING => 'violet',
            self::NETWORKING => 'lime',
            self::WEBINAR => 'sky',
            self::PEER_TEACHING => 'emerald',
            self::COMMUNITY_CONTRIBUTION => 'teal',
            self::CROSS_TRACK_COLLAB => 'amber',
            // Meta
            self::WORKSHOP => 'indigo',
            self::REFLECTION => 'stone',
            self::PRESENTATION => 'purple',
            self::DOCUMENTATION => 'gray',
            self::OTHER => 'gray',
        };
    }

    /**
     * Check if this activity type requires URL
     */
    public function requiresUrl(): bool
    {
        return in_array($this, [
            self::PROJECT,
            self::BLOG_POST,
            self::OPEN_SOURCE,
            self::FREELANCE,
            self::PUBLICATION,
            self::LINKEDIN_POST,
            self::TWITTER_THREAD,
            self::VIDEO_CONTENT,
            self::COMMUNITY_CONTRIBUTION,
        ]);
    }

    /**
     * Get activities that count towards Build pillar
     */
    public static function buildPillar(): array
    {
        return [
            self::PROJECT,
            self::HACKATHON,
            self::FREELANCE,
            self::OPEN_SOURCE,
            self::COMPETITION,
            self::PROTOTYPE,
            self::DEBUG_CHALLENGE,
        ];
    }

    /**
     * Get activities that count towards Brand pillar
     */
    public static function brandPillar(): array
    {
        return [
            self::BLOG_POST,
            self::CONFERENCE,
            self::SPEAKING,
            self::PUBLICATION,
            self::LINKEDIN_POST,
            self::TWITTER_THREAD,
            self::VIDEO_CONTENT,
            self::CASE_STUDY,
        ];
    }

    /**
     * Get activities that count towards Interview pillar
     */
    public static function interviewPillar(): array
    {
        return [
            self::MOCK_INTERVIEW,
            self::CERTIFICATION,
            self::RESEARCH,
        ];
    }

    /**
     * Get activities that count towards Collaborate pillar
     */
    public static function collaboratePillar(): array
    {
        return [
            self::MENTORING,
            self::CODE_REVIEW,
            self::PAIR_PROGRAMMING,
            self::NETWORKING,
            self::PEER_TEACHING,
            self::COMMUNITY_CONTRIBUTION,
            self::CROSS_TRACK_COLLAB,
        ];
    }

    /**
     * Get activities that are meta/reflection types
     */
    public static function metaTypes(): array
    {
        return [
            self::WORKSHOP,
            self::WEBINAR,
            self::REFLECTION,
            self::PRESENTATION,
            self::DOCUMENTATION,
            self::OTHER,
        ];
    }

    /**
     * Whether this type integrates with the interview module
     */
    public function requiresInterviewSession(): bool
    {
        return $this === self::MOCK_INTERVIEW;
    }

    /**
     * Whether this type requires a partner from a different track
     */
    public function requiresCrossTrack(): bool
    {
        return $this === self::CROSS_TRACK_COLLAB;
    }
}
