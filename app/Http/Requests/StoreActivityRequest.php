<?php

namespace App\Http\Requests;

use App\Enums\ActivityType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Store Activity Request
 * 
 * Validates activity submission from fellows.
 * Different activity types have different required fields.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class StoreActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermissionTo('submit-activities');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'track_id' => ['required', 'exists:tracks,id'],
            'type' => ['required', Rule::enum(ActivityType::class)],
            'title' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'completed_at' => ['required', 'date', 'before_or_equal:today'],
            'hours_spent' => ['nullable', 'numeric', 'min:0.5', 'max:1000'],
            'evidence_url' => ['nullable', 'url', 'max:500'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png,gif,mp4,webm'],
            'tags' => ['nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:50'],
        ];

        // Type-specific validation
        $type = $this->input('type');

        if ($type === ActivityType::CERTIFICATION->value) {
            $rules['certification_name'] = ['required', 'string', 'max:255'];
            $rules['certification_issuer'] = ['required', 'string', 'max:255'];
            $rules['certification_url'] = ['nullable', 'url'];
        }

        if ($type === ActivityType::PROJECT->value) {
            $rules['project_url'] = ['nullable', 'url'];
            $rules['github_url'] = ['nullable', 'url', 'regex:/github\.com/'];
        }

        if (in_array($type, [ActivityType::SPEAKING->value, ActivityType::WORKSHOP->value])) {
            $rules['event_name'] = ['required', 'string', 'max:255'];
            $rules['audience_size'] = ['nullable', 'integer', 'min:1'];
        }

        if ($type === ActivityType::PUBLICATION->value) {
            $rules['publication_url'] = ['required', 'url'];
            $rules['publication_type'] = ['required', 'in:blog,article,paper,book,whitepaper'];
        }

        if ($type === ActivityType::NETWORKING->value) {
            $rules['connection_count'] = ['nullable', 'integer', 'min:1'];
            $rules['event_type'] = ['nullable', 'in:conference,meetup,online,coffee_chat'];
        }

        if ($type === ActivityType::COMPETITION->value) {
            $rules['competition_name'] = ['required', 'string', 'max:255'];
            $rules['placement'] = ['nullable', 'string', 'max:50'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'description.min' => 'Please provide a detailed description of at least 20 characters.',
            'evidence_url.url' => 'Please provide a valid URL for your evidence.',
            'completed_at.before_or_equal' => 'Activity date cannot be in the future.',
            'attachments.max' => 'You can upload a maximum of 5 attachments.',
            'attachments.*.max' => 'Each file must be less than 10MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'track_id' => 'career track',
            'completed_at' => 'completion date',
            'hours_spent' => 'time spent',
            'evidence_url' => 'evidence link',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up tags
        if ($this->has('tags') && is_string($this->tags)) {
            $this->merge([
                'tags' => array_filter(array_map('trim', explode(',', $this->tags))),
            ]);
        }
    }
}
