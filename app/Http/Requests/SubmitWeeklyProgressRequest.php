<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Submit Weekly Progress Request
 * 
 * Validates the 4 pillars weekly accountability submission.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class SubmitWeeklyProgressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermissionTo('submit-weekly-progress');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Skill Development pillar
            'skill_development' => ['nullable', 'string', 'max:2000'],
            'skill_development_hours' => ['nullable', 'numeric', 'min:0', 'max:168'],
            'skill_development_resources' => ['nullable', 'array', 'max:5'],
            'skill_development_resources.*' => ['url'],

            // Portfolio Building pillar
            'portfolio_building' => ['nullable', 'string', 'max:2000'],
            'portfolio_item_url' => ['nullable', 'url'],
            'portfolio_item_type' => ['nullable', 'in:project,article,design,video,presentation,other'],

            // Network Growth pillar
            'network_growth' => ['nullable', 'string', 'max:2000'],
            'connections_made' => ['nullable', 'integer', 'min:0', 'max:100'],
            'events_attended' => ['nullable', 'integer', 'min:0', 'max:20'],

            // Personal Branding pillar
            'personal_branding' => ['nullable', 'string', 'max:2000'],
            'linkedin_post_url' => ['nullable', 'url', 'regex:/linkedin\.com/'],
            'content_published' => ['nullable', 'boolean'],

            // General
            'highlights' => ['nullable', 'string', 'max:1000'],
            'challenges' => ['nullable', 'string', 'max:1000'],
            'goals_next_week' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'skill_development_hours.max' => 'Hours cannot exceed 168 (total hours in a week).',
            'linkedin_post_url.regex' => 'Please provide a valid LinkedIn URL.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // At least one pillar should have content
            $hasContent = 
                !empty($this->input('skill_development')) ||
                !empty($this->input('portfolio_building')) ||
                !empty($this->input('network_growth')) ||
                !empty($this->input('personal_branding'));

            if (!$hasContent) {
                $validator->errors()->add(
                    'general',
                    'Please complete at least one pillar to submit your weekly progress.'
                );
            }
        });
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'skill_development' => 'Skill Development update',
            'portfolio_building' => 'Portfolio Building update',
            'network_growth' => 'Network Growth update',
            'personal_branding' => 'Personal Branding update',
            'skill_development_hours' => 'learning hours',
            'connections_made' => 'new connections',
        ];
    }
}
