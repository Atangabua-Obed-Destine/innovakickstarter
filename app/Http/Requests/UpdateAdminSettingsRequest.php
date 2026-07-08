<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Admin Settings Request
 * 
 * Validates admin settings updates.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class UpdateAdminSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $group = $this->route('group', 'general');

        return match ($group) {
            'tiers' => $this->tierRules(),
            'weights' => $this->weightRules(),
            'interviews' => $this->interviewRules(),
            'subscriptions' => $this->subscriptionRules(),
            'points' => $this->pointsRules(),
            'penalties' => $this->penaltyRules(),
            'email' => $this->emailRules(),
            default => $this->generalRules(),
        };
    }

    /**
     * Tier threshold rules.
     */
    protected function tierRules(): array
    {
        return [
            'tier_elite_min' => ['required', 'numeric', 'min:0', 'max:100'],
            'tier_professional_min' => ['required', 'numeric', 'min:0', 'max:100', 'lt:tier_elite_min'],
            'tier_intern_min' => ['required', 'numeric', 'min:0', 'max:100', 'lt:tier_professional_min'],
            'tier_rookie_min' => ['required', 'numeric', 'min:0', 'max:100', 'lt:tier_intern_min'],
        ];
    }

    /**
     * Category weight rules.
     */
    protected function weightRules(): array
    {
        return [
            'weight_technical' => ['required', 'numeric', 'min:0', 'max:100'],
            'weight_interview' => ['required', 'numeric', 'min:0', 'max:100'],
            'weight_portfolio' => ['required', 'numeric', 'min:0', 'max:100'],
            'weight_collaboration' => ['required', 'numeric', 'min:0', 'max:100'],
            'weight_learning' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    /**
     * Interview limit rules.
     */
    protected function interviewRules(): array
    {
        return [
            'ai_interview_weekly_limit' => ['required', 'integer', 'min:0', 'max:100'],
            'human_interview_weekly_limit' => ['required', 'integer', 'min:0', 'max:20'],
            'daily_interview_limit' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    /**
     * Subscription pricing rules.
     */
    protected function subscriptionRules(): array
    {
        return [
            'starter_monthly_price' => ['required', 'numeric', 'min:0'],
            'starter_profile_views' => ['required', 'integer', 'min:1'],
            'professional_monthly_price' => ['required', 'numeric', 'min:0'],
            'professional_profile_views' => ['required', 'integer', 'min:1'],
            'professional_contacts' => ['required', 'integer', 'min:0'],
            'enterprise_monthly_price' => ['required', 'numeric', 'min:0'],
            'enterprise_profile_views' => ['required', 'integer', 'min:1'],
            'enterprise_contacts' => ['required', 'integer', 'min:0'],
            'recruiter_trial_days' => ['required', 'integer', 'min:0', 'max:90'],
        ];
    }

    /**
     * Activity points rules.
     */
    protected function pointsRules(): array
    {
        return [
            'points_learning' => ['required', 'integer', 'min:1', 'max:100'],
            'points_project' => ['required', 'integer', 'min:1', 'max:100'],
            'points_certification' => ['required', 'integer', 'min:1', 'max:100'],
            'points_networking' => ['required', 'integer', 'min:1', 'max:100'],
            'points_content_creation' => ['required', 'integer', 'min:1', 'max:100'],
            'points_mentorship' => ['required', 'integer', 'min:1', 'max:100'],
            'points_competition' => ['required', 'integer', 'min:1', 'max:100'],
            'points_speaking' => ['required', 'integer', 'min:1', 'max:100'],
            'points_publication' => ['required', 'integer', 'min:1', 'max:100'],
            'points_workshop' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Penalty settings rules.
     */
    protected function penaltyRules(): array
    {
        return [
            'penalty_weekly_freeze' => ['required', 'boolean'],
            'penalty_inactivity_days' => ['required', 'integer', 'min:7', 'max:365'],
            'penalty_inactivity_percent' => ['required', 'numeric', 'min:0', 'max:50'],
        ];
    }

    /**
     * Email settings rules.
     */
    protected function emailRules(): array
    {
        return [
            'email_verification_required' => ['required', 'boolean'],
            'email_sender_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email_sender_address' => ['sometimes', 'nullable', 'email', 'max:255'],
            'email_reply_to' => ['sometimes', 'nullable', 'email', 'max:255'],
            'email_weekly_digest_day' => ['sometimes', 'required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'email_activity_approved' => ['sometimes', 'boolean'],
            'email_interview_reminder' => ['sometimes', 'boolean'],
            'email_weekly_progress' => ['sometimes', 'boolean'],
            'email_tier_change' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * General platform rules.
     */
    protected function generalRules(): array
    {
        return [
            'site_name' => ['sometimes', 'required', 'string', 'max:255'],
            'site_tagline' => ['sometimes', 'nullable', 'string', 'max:500'],
            'contact_email' => ['sometimes', 'required', 'email'],
            'max_tracks_per_fellow' => ['sometimes', 'required', 'integer', 'min:1', 'max:10'],
            'activity_approval_sla_hours' => ['sometimes', 'required', 'integer', 'min:1', 'max:168'],
            'weekly_reminder_day' => ['sometimes', 'required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'platform_timezone' => ['sometimes', 'required', 'string', 'max:50'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate weights sum to 100
            if ($this->route('group') === 'weights') {
                $sum = 
                    (float) $this->input('weight_technical', 0) +
                    (float) $this->input('weight_interview', 0) +
                    (float) $this->input('weight_portfolio', 0) +
                    (float) $this->input('weight_collaboration', 0) +
                    (float) $this->input('weight_learning', 0);

                if (abs($sum - 100) > 0.01) {
                    $validator->errors()->add(
                        'weights',
                        "Category weights must sum to 100%. Current total: {$sum}%"
                    );
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'tier_professional_min.lt' => 'Professional threshold must be less than Elite threshold.',
            'tier_intern_min.lt' => 'Intern threshold must be less than Professional threshold.',
            'tier_rookie_min.lt' => 'Rookie threshold must be less than Intern threshold.',
        ];
    }
}
