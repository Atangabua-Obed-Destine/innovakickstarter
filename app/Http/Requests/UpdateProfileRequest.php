<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Update Profile Request
 * 
 * Validates profile updates for all user types.
 * Different fields are validated based on user role.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermissionTo('edit-own-profile');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $userId = $user->id;

        $rules = [
            // Basic info
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'username' => ['sometimes', 'nullable', 'string', 'max:50', 'alpha_dash', "unique:users,username,{$userId}"],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'avatar' => ['sometimes', 'nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
            
            // Location
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'timezone' => ['sometimes', 'nullable', 'string', 'max:50'],
            
            // Social links
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'regex:/linkedin\.com/'],
            'github_url' => ['sometimes', 'nullable', 'url', 'regex:/github\.com/'],
            'twitter_url' => ['sometimes', 'nullable', 'url'],
            'portfolio_url' => ['sometimes', 'nullable', 'url'],
            'personal_website' => ['sometimes', 'nullable', 'url'],
        ];

        // Fellow-specific fields
        if ($user->hasRole('fellow')) {
            $rules = array_merge($rules, [
                'headline' => ['sometimes', 'nullable', 'string', 'max:150'],
                'skills' => ['sometimes', 'nullable', 'array', 'max:20'],
                'skills.*' => ['string', 'max:50'],
                'languages' => ['sometimes', 'nullable', 'array', 'max:10'],
                'languages.*' => ['string', 'max:50'],
                'availability' => ['sometimes', 'nullable', 'in:full-time,part-time,freelance,internship,not-looking'],
                'experience_level' => ['sometimes', 'nullable', 'in:student,junior,mid,senior,lead,executive'],
                'salary_min' => ['sometimes', 'nullable', 'integer', 'min:0'],
                'salary_max' => ['sometimes', 'nullable', 'integer', 'min:0', 'gte:salary_min'],
                'salary_currency' => ['sometimes', 'nullable', 'string', 'max:3'],
                'open_to_opportunities' => ['sometimes', 'boolean'],
                'is_public' => ['sometimes', 'boolean'],
                'resume' => ['sometimes', 'nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx'],
            ]);
        }

        // Recruiter-specific fields
        if ($user->hasRole('recruiter')) {
            $rules = array_merge($rules, [
                'company_name' => ['sometimes', 'required', 'string', 'max:255'],
                'company_website' => ['sometimes', 'nullable', 'url'],
                'company_size' => ['sometimes', 'nullable', 'in:1-10,11-50,51-200,201-500,501-1000,1000+'],
                'company_industry' => ['sometimes', 'nullable', 'string', 'max:100'],
                'company_description' => ['sometimes', 'nullable', 'string', 'max:2000'],
                'company_logo' => ['sometimes', 'nullable', 'image', 'max:1024', 'mimes:jpg,jpeg,png,webp'],
                'hiring_roles' => ['sometimes', 'nullable', 'array', 'max:10'],
                'hiring_roles.*' => ['string', 'max:100'],
            ]);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'linkedin_url.regex' => 'Please provide a valid LinkedIn URL.',
            'github_url.regex' => 'Please provide a valid GitHub URL.',
            'avatar.max' => 'Avatar image must be less than 2MB.',
            'resume.max' => 'Resume file must be less than 5MB.',
            'salary_max.gte' => 'Maximum salary must be greater than or equal to minimum salary.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean up skills if provided as comma-separated string
        if ($this->has('skills') && is_string($this->skills)) {
            $this->merge([
                'skills' => array_filter(array_map('trim', explode(',', $this->skills))),
            ]);
        }

        // Clean up languages if provided as comma-separated string
        if ($this->has('languages') && is_string($this->languages)) {
            $this->merge([
                'languages' => array_filter(array_map('trim', explode(',', $this->languages))),
            ]);
        }
    }
}
