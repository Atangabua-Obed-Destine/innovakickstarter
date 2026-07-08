<?php

namespace App\Http\Requests;

use App\Enums\InterviewMode;
use App\Enums\InterviewType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Schedule Interview Request
 * 
 * Validates interview scheduling by fellows.
 * Enforces weekly limits and scheduling rules.
 * 
 * @author IKS Engineering Team
 * @version 1.0
 */
class ScheduleInterviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermissionTo('schedule-interviews');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'track_id' => ['required', 'exists:tracks,id'],
            'type' => ['required', Rule::enum(InterviewType::class)],
            'mode' => ['required', Rule::enum(InterviewMode::class)],
            'scheduled_at' => [
                'required',
                'date',
                'after:now',
                'before:' . now()->addMonths(2)->toDateString(),
            ],
            'duration_minutes' => ['required', 'integer', 'in:15,30,45,60'],
            'focus_areas' => ['nullable', 'array', 'max:5'],
            'focus_areas.*' => ['string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'preferred_mentor_id' => ['nullable', 'exists:users,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'scheduled_at.after' => 'Interview must be scheduled for a future date and time.',
            'scheduled_at.before' => 'Interviews can only be scheduled up to 2 months in advance.',
            'duration_minutes.in' => 'Interview duration must be 15, 30, 45, or 60 minutes.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->validateWeeklyLimits($validator);
            $this->validateSchedulingConflicts($validator);
        });
    }

    /**
     * Validate weekly interview limits.
     */
    protected function validateWeeklyLimits($validator): void
    {
        $user = $this->user();
        $mode = InterviewMode::tryFrom($this->input('mode'));
        
        if (!$mode) {
            return;
        }

        $weekStart = now()->startOfWeek();
        
        // Count interviews this week for this mode
        $count = \App\Models\InterviewSession::where('fellow_id', $user->id)
            ->where('mode', $mode)
            ->where('scheduled_at', '>=', $weekStart)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        // Get limits from settings based on mode
        $limit = match ($mode) {
            InterviewMode::AI => (int) \App\Models\AdminSetting::get('ai_interview_weekly_limit', 0),
            InterviewMode::HUMAN => (int) \App\Models\AdminSetting::get('human_interview_weekly_limit', 2),
        };

        // 0 means unlimited for AI interviews
        if ($limit > 0 && $count >= $limit) {
            $validator->errors()->add(
                'mode',
                "You have reached your weekly limit of {$limit} {$mode->label()} sessions."
            );
        }
    }

    /**
     * Validate scheduling conflicts.
     */
    protected function validateSchedulingConflicts($validator): void
    {
        $user = $this->user();
        $scheduledAt = $this->input('scheduled_at');
        $duration = (int) $this->input('duration_minutes', 30);

        if (!$scheduledAt) {
            return;
        }

        $startTime = \Carbon\Carbon::parse($scheduledAt);
        $endTime = $startTime->copy()->addMinutes($duration);

        // Check for overlapping interviews
        $conflict = \App\Models\InterviewSession::where('fellow_id', $user->id)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // New interview starts during existing interview
                    $q->where('scheduled_at', '<=', $startTime)
                        ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$startTime]);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New interview ends during existing interview
                    $q->where('scheduled_at', '<', $endTime)
                        ->where('scheduled_at', '>=', $startTime);
                });
            })
            ->exists();

        if ($conflict) {
            $validator->errors()->add(
                'scheduled_at',
                'You already have an interview scheduled at this time. Please choose a different time slot.'
            );
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'track_id' => 'career track',
            'scheduled_at' => 'interview time',
            'duration_minutes' => 'duration',
            'focus_areas' => 'focus areas',
        ];
    }
}
