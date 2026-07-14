@extends('layouts.app')

@section('title', "New Activity — {$milestone->title}")

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <div class="flex items-center gap-2 text-dark-400 text-sm mb-2">
            <a href="{{ route('admin.curriculum.index', $track) }}" class="hover:text-white transition">{{ $track->name }}</a>
            <span>/</span>
            <span>{{ $milestone->title }}</span>
            <span>/</span>
            <span class="text-primary-400">New Activity</span>
        </div>
        <h1 class="text-2xl font-bold text-white">Add Activity to "{{ $milestone->title }}"</h1>
    </div>

    @if($errors->any())
    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl text-red-400 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.curriculum.activities.store', [$track, $milestone]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Basic Info --}}
        <div class="card p-6 space-y-5">
            <h2 class="text-white font-semibold text-lg border-b border-dark-700 pb-3">Basic Information</h2>

            <div>
                <label for="title" class="block text-sm font-medium text-dark-300 mb-2">Activity Title *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                       placeholder="e.g. Build a REST API with Authentication"
                       class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-dark-300 mb-2">Description *</label>
                <textarea name="description" id="description" rows="3" required
                          placeholder="What will the fellow learn or build?"
                          class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="instructions" class="block text-sm font-medium text-dark-300 mb-2">Detailed Instructions</label>
                <textarea name="instructions" id="instructions" rows="5"
                          placeholder="Step-by-step instructions, requirements, success criteria..."
                          class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-dark-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">{{ old('instructions') }}</textarea>
            </div>
        </div>

        {{-- Type & Difficulty --}}
        <div class="card p-6 space-y-5">
            <h2 class="text-white font-semibold text-lg border-b border-dark-700 pb-3">Classification</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="type" class="block text-sm font-medium text-dark-300 mb-2">Activity Type *</label>
                    <select name="type" id="type" required
                            class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        <option value="">Select type...</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type->value }}" {{ old('type') === $type->value ? 'selected' : '' }}>
                                {{ $type->icon() }} {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="difficulty_level" class="block text-sm font-medium text-dark-300 mb-2">Difficulty Level *</label>
                    <select name="difficulty_level" id="difficulty_level" required
                            class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @foreach($difficultyLevels as $level)
                            <option value="{{ $level->value }}" {{ old('difficulty_level') === $level->value ? 'selected' : '' }}>
                                {{ $level->icon() }} {{ $level->label() }} ({{ $level->pointsMultiplier() }}x)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="sequence_order" class="block text-sm font-medium text-dark-300 mb-2">Order</label>
                    <input type="number" name="sequence_order" id="sequence_order" value="{{ old('sequence_order', $existingActivities->count() + 1) }}" min="1"
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
                <div>
                    <label for="chain_parent_id" class="block text-sm font-medium text-dark-300 mb-2">Chain Parent</label>
                    <select name="chain_parent_id" id="chain_parent_id"
                            class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        <option value="">None (standalone)</option>
                        @foreach($existingActivities as $act)
                            <option value="{{ $act->id }}" {{ old('chain_parent_id') == $act->id ? 'selected' : '' }}>
                                #{{ $act->sequence_order }}: {{ Str::limit($act->title, 40) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="prerequisites" class="block text-sm font-medium text-dark-300 mb-2">Prerequisites</label>
                <select name="prerequisites[]" id="prerequisites" multiple size="4"
                        class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    @foreach($existingActivities as $act)
                        <option value="{{ $act->id }}" {{ in_array($act->id, old('prerequisites', [])) ? 'selected' : '' }}>
                            #{{ $act->sequence_order }}: {{ Str::limit($act->title, 40) }}
                        </option>
                    @endforeach
                </select>
                <p class="text-dark-500 text-xs mt-1">Hold Ctrl/Cmd to select multiple activities that must be completed before this one unlocks.</p>
            </div>

            <label class="flex items-center gap-2 cursor-pointer">
                <input type="hidden" name="is_required" value="0">
                <input type="checkbox" name="is_required" value="1" {{ old('is_required', true) ? 'checked' : '' }}
                       class="w-4 h-4 rounded bg-dark-800 border-dark-600 text-primary-500 focus:ring-primary-500">
                <span class="text-dark-300 text-sm">Required activity</span>
            </label>
        </div>

        {{-- Points & Deadlines --}}
        <div class="card p-6 space-y-5">
            <h2 class="text-white font-semibold text-lg border-b border-dark-700 pb-3">Points & Deadlines</h2>

            <div>
                <label for="points" class="block text-sm font-medium text-dark-300 mb-2">Points *</label>
                <input type="number" name="points" id="points" value="{{ old('points', 100) }}" required min="1" max="1000"
                       class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <p class="text-dark-500 text-xs mt-1">Multiplied by difficulty level</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label for="deadline_days" class="block text-sm font-medium text-dark-300 mb-2">Deadline (days from start)</label>
                    <input type="number" name="deadline_days" id="deadline_days" value="{{ old('deadline_days', 7) }}" min="1" max="365"
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
                <div>
                    <label for="grace_period_days" class="block text-sm font-medium text-dark-300 mb-2">Grace Period (days)</label>
                    <input type="number" name="grace_period_days" id="grace_period_days" value="{{ old('grace_period_days', 2) }}" min="0" max="30"
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
                <div>
                    <label for="late_penalty_percent" class="block text-sm font-medium text-dark-300 mb-2">Late Penalty %</label>
                    <input type="number" name="late_penalty_percent" id="late_penalty_percent" value="{{ old('late_penalty_percent', 20) }}" min="0" max="100"
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
            </div>
        </div>

        {{-- Interview Configuration (shown only for mock_interview type) --}}
        <div id="interview-config-section" class="card p-6 space-y-5" style="display: none;">
            <h2 class="text-white font-semibold text-lg border-b border-dark-700 pb-3 flex items-center gap-2">
                <span class="text-purple-400">🎤</span> Interview Configuration
            </h2>
            <p class="text-dark-400 text-sm">Configure how mock interview sessions will work for this activity. Fellows will be launched directly into the interview system.</p>

            @php $interviewConfig = old('interview_config', []); @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="interview_config_type" class="block text-sm font-medium text-dark-300 mb-2">Interview Type *</label>
                    <select name="interview_config[type]" id="interview_config_type"
                            class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @foreach(\App\Enums\InterviewType::cases() as $iType)
                            <option value="{{ $iType->value }}" {{ ($interviewConfig['type'] ?? '') === $iType->value ? 'selected' : '' }}>
                                {{ $iType->label() }} ({{ $iType->defaultDuration() }} min)
                            </option>
                        @endforeach
                    </select>
                    <p class="text-dark-500 text-xs mt-1">The type of interview the fellow will take</p>
                </div>
                <div>
                    <label for="interview_config_mode" class="block text-sm font-medium text-dark-300 mb-2">Interview Mode</label>
                    <select name="interview_config[mode]" id="interview_config_mode"
                            class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @foreach(\App\Enums\InterviewMode::cases() as $iMode)
                            <option value="{{ $iMode->value }}" {{ ($interviewConfig['mode'] ?? 'ai') === $iMode->value ? 'selected' : '' }}>
                                {{ $iMode->icon() }} {{ $iMode->label() }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-dark-500 text-xs mt-1">AI interviews launch instantly; human/peer require scheduling</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <label for="interview_config_min_score" class="block text-sm font-medium text-dark-300 mb-2">Minimum Passing Score *</label>
                    <input type="number" name="interview_config[min_score]" id="interview_config_min_score"
                           value="{{ $interviewConfig['min_score'] ?? 70 }}" min="0" max="100"
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <p class="text-dark-500 text-xs mt-1">Fellow must score at least this to pass (0-100)</p>
                </div>
                <div>
                    <label for="interview_config_count" class="block text-sm font-medium text-dark-300 mb-2">Required Sessions</label>
                    <input type="number" name="interview_config[count]" id="interview_config_count"
                           value="{{ $interviewConfig['count'] ?? 1 }}" min="1" max="10"
                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <p class="text-dark-500 text-xs mt-1">Number of interviews to complete (must pass at least one)</p>
                </div>
                <div>
                    <label for="interview_config_difficulty" class="block text-sm font-medium text-dark-300 mb-2">Difficulty</label>
                    <select name="interview_config[difficulty]" id="interview_config_difficulty"
                            class="w-full bg-dark-800 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        <option value="beginner" {{ ($interviewConfig['difficulty'] ?? '') === 'beginner' ? 'selected' : '' }}>🟢 Beginner</option>
                        <option value="intermediate" {{ ($interviewConfig['difficulty'] ?? 'intermediate') === 'intermediate' ? 'selected' : '' }}>🟡 Intermediate</option>
                        <option value="advanced" {{ ($interviewConfig['difficulty'] ?? '') === 'advanced' ? 'selected' : '' }}>🔴 Advanced</option>
                    </select>
                </div>
            </div>

            <div class="p-4 bg-purple-500/5 border border-purple-500/20 rounded-lg">
                <p class="text-purple-400 text-sm font-medium mb-1">How Interview Activities Work</p>
                <ul class="text-dark-400 text-sm space-y-1 list-disc list-inside">
                    <li>When the fellow clicks "Start Activity," an interview session is automatically created</li>
                    <li>The fellow is redirected to the interview room (AI room for AI mode, scheduling for human/peer)</li>
                    <li>Once the interview is completed and scored, the curriculum activity auto-progresses</li>
                    <li>If the score meets the minimum, the activity is submitted for admin review with interview results attached</li>
                    <li>Multiple sessions allowed. The best score counts toward the activity</li>
                </ul>
            </div>
        </div>

        {{-- Evidence Requirements --}}
        <div class="card p-6 space-y-5">
            <h2 class="text-white font-semibold text-lg border-b border-dark-700 pb-3">Evidence Requirements</h2>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($evidenceTypes as $evType)
                <label class="flex items-center gap-2 cursor-pointer p-3 rounded-lg border border-dark-700 hover:border-dark-500 transition">
                    <input type="checkbox" name="evidence_requirements[]" value="{{ $evType->value }}"
                           {{ in_array($evType->value, old('evidence_requirements', [])) ? 'checked' : '' }}
                           class="w-4 h-4 rounded bg-dark-800 border-dark-600 text-primary-500 focus:ring-primary-500">
                    <span class="text-dark-300 text-sm">{{ $evType->icon() }} {{ $evType->label() }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Review Settings --}}
        <div class="card p-6 space-y-5">
            <h2 class="text-white font-semibold text-lg border-b border-dark-700 pb-3">Review & Collaboration</h2>

            <div class="flex flex-wrap gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="requires_peer_review" value="0">
                    <input type="checkbox" name="requires_peer_review" value="1" {{ old('requires_peer_review') ? 'checked' : '' }}
                           class="w-4 h-4 rounded bg-dark-800 border-dark-600 text-primary-500 focus:ring-primary-500">
                    <span class="text-dark-300 text-sm">Requires Peer Review</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_collaborative" value="0">
                    <input type="checkbox" name="is_collaborative" value="1" {{ old('is_collaborative') ? 'checked' : '' }}
                           class="w-4 h-4 rounded bg-dark-800 border-dark-600 text-primary-500 focus:ring-primary-500">
                    <span class="text-dark-300 text-sm">Collaborative Activity</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="requires_cross_track" value="0">
                    <input type="checkbox" name="requires_cross_track" value="1" {{ old('requires_cross_track') ? 'checked' : '' }}
                           class="w-4 h-4 rounded bg-dark-800 border-dark-600 text-primary-500 focus:ring-primary-500">
                    <span class="text-dark-300 text-sm">Cross-Track Collaboration</span>
                </label>
            </div>
        </div>

        {{-- Evaluation Rubric --}}
        <div class="card p-6 space-y-5">
            <h2 class="text-white font-semibold text-lg border-b border-dark-700 pb-3">Evaluation Rubric</h2>
            <p class="text-dark-400 text-sm">Add criteria to evaluate submissions against. Each criterion will be scored 0-100.</p>

            <div id="rubric-container" class="space-y-3">
                @php $oldRubric = old('evaluation_rubric', []); @endphp
                @forelse($oldRubric as $key => $criteria)
                <div class="rubric-row grid grid-cols-12 gap-3 items-start">
                    <div class="col-span-4">
                        <input type="text" name="evaluation_rubric[{{ $key }}][criterion]" value="{{ $criteria['criterion'] ?? '' }}"
                               placeholder="Criterion name"
                               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-5">
                        <input type="text" name="evaluation_rubric[{{ $key }}][description]" value="{{ $criteria['description'] ?? '' }}"
                               placeholder="Description of what to look for"
                               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-2">
                        <input type="number" name="evaluation_rubric[{{ $key }}][weight]" value="{{ $criteria['weight'] ?? 25 }}" min="1" max="100"
                               placeholder="Weight %"
                               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-1 flex items-center justify-center">
                        <button type="button" onclick="this.closest('.rubric-row').remove()" class="text-dark-500 hover:text-red-400 transition p-1">✕</button>
                    </div>
                </div>
                @empty
                <div class="rubric-row grid grid-cols-12 gap-3 items-start">
                    <div class="col-span-4">
                        <input type="text" name="evaluation_rubric[0][criterion]" placeholder="e.g. Code Quality"
                               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-5">
                        <input type="text" name="evaluation_rubric[0][description]" placeholder="Clean, well-structured, documented"
                               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-2">
                        <input type="number" name="evaluation_rubric[0][weight]" value="25" min="1" max="100" placeholder="Weight %"
                               class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-1 flex items-center justify-center">
                        <button type="button" onclick="this.closest('.rubric-row').remove()" class="text-dark-500 hover:text-red-400 transition p-1">✕</button>
                    </div>
                </div>
                @endforelse
            </div>

            <button type="button" onclick="addRubricRow()" class="text-primary-400 hover:text-primary-300 text-sm font-medium transition">
                + Add Criterion
            </button>
        </div>

        {{-- Resources & Tags --}}
        <div class="card p-6 space-y-5" x-data="resourceManager([])">
            <div class="flex items-center justify-between border-b border-dark-700 pb-3">
                <h2 class="text-white font-semibold text-lg">Resources & Tags</h2>
                <button type="button" @click="addResource()" class="text-primary-400 hover:text-primary-300 text-sm font-medium transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Resource
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(res, index) in resources" :key="res.id">
                    <div class="bg-dark-800/50 border border-dark-700 rounded-lg p-4 relative group">
                        <button type="button" @click="removeResource(index)" class="absolute top-3 right-3 text-dark-500 hover:text-red-400 transition" title="Remove Resource">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 pr-8">
                            <div>
                                <label class="block text-xs font-medium text-dark-300 mb-1">Resource Title *</label>
                                <input type="text" x-model="res.title" :name="`resources[${index}][title]`" required placeholder="e.g. Intro to Laravel"
                                       class="w-full bg-dark-900 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-dark-300 mb-1">Type *</label>
                                <select x-model="res.type" :name="`resources[${index}][type]`" @change="res.content = ''" required
                                        class="w-full bg-dark-900 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                    <option value="link">External Link</option>
                                    <option value="youtube">YouTube Embed</option>
                                    <option value="file">File Upload (PDF, Image, Doc)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <template x-if="res.type === 'youtube' || res.type === 'link'">
                                <div>
                                    <label class="block text-xs font-medium text-dark-300 mb-1">URL *</label>
                                    <input type="url" x-model="res.content" :name="`resources[${index}][content_url]`" required placeholder="https://..."
                                           class="w-full bg-dark-900 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                                </div>
                            </template>
                            <template x-if="res.type === 'file'">
                                <div>
                                    <label class="block text-xs font-medium text-dark-300 mb-1">Upload File *</label>
                                    <input type="file" :name="`resources[${index}][content_file]`" required accept=".pdf,image/*,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                                           class="w-full text-sm text-dark-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary-500/10 file:text-primary-400 hover:file:bg-primary-500/20 transition cursor-pointer">
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <div x-show="resources.length === 0" class="text-center py-6 text-dark-400 text-sm italic">
                    No resources added. Click "Add Resource" to attach files or links.
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.curriculum.index', $track) }}" class="btn-secondary">Cancel</a>
            <button type="submit" class="btn-primary">Create Activity</button>
        </div>
    </form>
</div>

<script>
// Interview config toggle
const typeSelect = document.getElementById('type');
const interviewSection = document.getElementById('interview-config-section');

function toggleInterviewConfig() {
    if (typeSelect.value === 'mock_interview') {
        interviewSection.style.display = '';
    } else {
        interviewSection.style.display = 'none';
    }
}

typeSelect.addEventListener('change', toggleInterviewConfig);
toggleInterviewConfig(); // Set initial state

let rubricIndex = {{ count(old('evaluation_rubric', [0])) }};

function addRubricRow() {
    const container = document.getElementById('rubric-container');
    const html = `
    <div class="rubric-row grid grid-cols-12 gap-3 items-start">
        <div class="col-span-4">
            <input type="text" name="evaluation_rubric[${rubricIndex}][criterion]" placeholder="Criterion name"
                   class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <div class="col-span-5">
            <input type="text" name="evaluation_rubric[${rubricIndex}][description]" placeholder="Description"
                   class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <div class="col-span-2">
            <input type="number" name="evaluation_rubric[${rubricIndex}][weight]" value="25" min="1" max="100" placeholder="Weight"
                   class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
        </div>
        <div class="col-span-1 flex items-center justify-center">
            <button type="button" onclick="this.closest('.rubric-row').remove()" class="text-dark-500 hover:text-red-400 transition p-1">✕</button>
        </div>
    </div>`;
    container.insertAdjacentHTML('beforeend', html);
    rubricIndex++;
}

document.addEventListener('alpine:init', () => {
    Alpine.data('resourceManager', (initialResources) => ({
        resources: initialResources,
        addResource() {
            this.resources.push({
                id: Date.now(),
                title: '',
                type: 'link',
                content: ''
            });
        },
        removeResource(index) {
            this.resources.splice(index, 1);
        }
    }));
});
</script>
@endsection
