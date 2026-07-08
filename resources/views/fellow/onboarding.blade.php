@extends('layouts.app')

@section('title', 'Welcome to InnovaKickstarter')

@section('content')
@php
    $toneMap = [
        'amber'   => ['bg' => 'bg-amber-500/10',   'border' => 'border-amber-500/30',   'text' => 'text-amber-300',   'accent' => 'text-amber-400'],
        'orange'  => ['bg' => 'bg-orange-500/10',  'border' => 'border-orange-500/30',  'text' => 'text-orange-300',  'accent' => 'text-orange-400'],
        'red'     => ['bg' => 'bg-red-500/10',     'border' => 'border-red-500/30',     'text' => 'text-red-300',     'accent' => 'text-red-400'],
        'blue'    => ['bg' => 'bg-blue-500/10',    'border' => 'border-blue-500/30',    'text' => 'text-blue-300',    'accent' => 'text-blue-400'],
        'emerald' => ['bg' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/30', 'text' => 'text-emerald-300', 'accent' => 'text-emerald-400'],
        'dark'    => ['bg' => 'bg-dark-700/40',    'border' => 'border-dark-600',       'text' => 'text-dark-300',    'accent' => 'text-dark-400'],
    ];
@endphp

@isset($internshipStatusBanner)
    @php $tone = $toneMap[$internshipStatusBanner['tone']] ?? $toneMap['blue']; @endphp
    <div class="max-w-4xl mx-auto mb-6 rounded-2xl border {{ $tone['bg'] }} {{ $tone['border'] }} p-5">
        <div class="flex items-start gap-4">
            <div class="text-3xl leading-none">{{ $internshipStatusBanner['icon'] }}</div>
            <div class="flex-1">
                <h2 class="{{ $tone['accent'] }} font-semibold">{{ $internshipStatusBanner['title'] }}</h2>
                <p class="{{ $tone['text'] }} text-sm mt-1 whitespace-pre-line">{{ $internshipStatusBanner['message'] }}</p>

                @if($internshipProfile && $internshipProfile->approved_start_date && $internshipProfile->approved_end_date)
                    @php
                        $progress = $internshipProfile->progress_percent ?? 0;
                        $bar = $internshipProfile->is_expired ? 'from-dark-500 to-dark-600'
                             : ($progress >= 80 ? 'from-amber-500 to-red-500'
                             : 'from-primary-500 to-teal-500');
                    @endphp
                    <div class="mt-4">
                        <div class="flex justify-between text-xs {{ $tone['text'] }} mb-1">
                            <span>{{ $internshipProfile->approved_start_date->format('M j, Y') }}</span>
                            <span>{{ $internshipProfile->approved_end_date->format('M j, Y') }}</span>
                        </div>
                        <div class="h-2 bg-dark-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r {{ $bar }}" style="width: {{ $progress }}%"></div>
                        </div>
                        <div class="flex justify-between text-xs mt-1 {{ $tone['text'] }}">
                            <span>{{ $internshipProfile->days_elapsed ?? 0 }} / {{ $internshipProfile->total_days ?? 0 }} days</span>
                            <span>
                                @if($internshipProfile->is_expired)
                                    Ended {{ $internshipProfile->approved_end_date->diffForHumans() }}
                                @else
                                    {{ $internshipProfile->days_remaining ?? 0 }} days left
                                @endif
                            </span>
                        </div>
                    </div>
                @endif

                @if(in_array($internshipProfile?->status, ['approved', 'active']))
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 mt-4 btn btn-primary">
                        Go to dashboard →
                    </a>
                @endif
            </div>
        </div>
    </div>
@endisset

<div class="max-w-4xl mx-auto" x-data="onboardingWizard()" x-cloak>
    <!-- Progress Bar -->
    <div class="mb-10">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-dark-400">Getting Started</span>
            <span class="text-sm text-dark-500" x-text="`Step ${currentStepNumber} of ${totalSteps}`"></span>
        </div>
        <div class="w-full bg-dark-700 rounded-full h-2">
            <div class="bg-gradient-to-r from-primary-600 to-accent-600 h-2 rounded-full transition-all duration-500" 
                 :style="`width: ${(currentStepNumber / totalSteps) * 100}%`"></div>
        </div>
        <!-- Step Labels -->
        <div class="flex justify-between mt-3">
            <template x-for="(stepName, index) in activeSteps" :key="stepName">
                <div class="flex flex-col items-center" :class="{ 'opacity-50': activeSteps.indexOf(currentStep) < index }">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold transition-all"
                         :class="activeSteps.indexOf(currentStep) >= index ? 'bg-primary-600 text-white' : 'bg-dark-700 text-dark-500'">
                        <span x-text="index + 1"></span>
                    </div>
                    <span class="text-xs text-dark-500 mt-1 hidden sm:block" x-text="stepLabels[stepName]"></span>
                </div>
            </template>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div x-show="saving" class="fixed inset-0 bg-dark-900/50 z-50 flex items-center justify-center">
        <div class="bg-dark-800 rounded-xl p-6 flex items-center gap-3">
            <svg class="animate-spin w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-white">Saving...</span>
        </div>
    </div>

    <!-- Error Alert -->
    <div x-show="errorMessage" x-transition class="mb-6 bg-red-500/10 border border-red-500/30 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <p class="text-red-400 text-sm font-medium" x-text="errorMessage"></p>
            <ul x-show="validationErrors.length > 0" class="mt-1 text-red-400/80 text-xs list-disc list-inside">
                <template x-for="err in validationErrors" :key="err">
                    <li x-text="err"></li>
                </template>
            </ul>
        </div>
        <button @click="errorMessage = ''; validationErrors = []" class="ml-auto text-red-400 hover:text-red-300">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 1: Welcome
         ═══════════════════════════════════════════════════════════ --}}
    <div x-show="currentStep === 'welcome'" x-transition>
        <div class="text-center mb-10">
            <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center text-4xl">
                🎯
            </div>
            <h1 class="text-3xl font-bold text-white mb-3">Welcome, Fellow!</h1>
            <p class="text-dark-400 max-w-xl mx-auto">You're about to start an exciting journey building your Career Capital. Let's set up your profile so you can make the most of the program.</p>
        </div>

        <div class="card p-8 text-center">
            <h2 class="text-xl font-semibold text-white mb-4">What is Career Capital?</h2>
            <div class="grid md:grid-cols-4 gap-4 mb-8">
                <div class="p-4 bg-dark-700/50 rounded-xl">
                    <div class="text-2xl mb-2">🔨</div>
                    <h3 class="font-semibold text-white text-sm">Build</h3>
                    <p class="text-dark-500 text-xs mt-1">Create tangible projects and deliverables</p>
                </div>
                <div class="p-4 bg-dark-700/50 rounded-xl">
                    <div class="text-2xl mb-2">📢</div>
                    <h3 class="font-semibold text-white text-sm">Brand</h3>
                    <p class="text-dark-500 text-xs mt-1">Develop your professional identity</p>
                </div>
                <div class="p-4 bg-dark-700/50 rounded-xl">
                    <div class="text-2xl mb-2">🎤</div>
                    <h3 class="font-semibold text-white text-sm">Interview</h3>
                    <p class="text-dark-500 text-xs mt-1">Sharpen your interview skills</p>
                </div>
                <div class="p-4 bg-dark-700/50 rounded-xl">
                    <div class="text-2xl mb-2">🤝</div>
                    <h3 class="font-semibold text-white text-sm">Collaborate</h3>
                    <p class="text-dark-500 text-xs mt-1">Work with peers and mentors</p>
                </div>
            </div>
            <button @click="nextStep()" class="btn btn-primary px-8">
                Let's Get Started
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 2: Fellow Type Selection
         ═══════════════════════════════════════════════════════════ --}}
    <div x-show="currentStep === 'type'" x-transition x-cloak>
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white">How are you joining us?</h2>
            <p class="text-dark-400 mt-1">This helps us tailor your onboarding experience</p>
        </div>

        <div class="space-y-4 mb-8">
            {{-- Academic Intern --}}
            <label @click="fellowType = 'academic'" 
                   class="block cursor-pointer card p-6 transition-all border-2"
                   :class="fellowType === 'academic' ? 'border-primary-600 bg-primary-600/5' : 'border-transparent hover:border-dark-500'">
                <div class="flex items-start gap-5">
                    <div class="w-14 h-14 rounded-xl bg-blue-500/20 flex items-center justify-center text-3xl shrink-0">🎓</div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white">Academic Intern</h3>
                        <p class="text-dark-400 text-sm mt-1">I'm a student completing an internship required by my school or university program.</p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 rounded-full text-xs">School/University</span>
                            <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 rounded-full text-xs">Structured Duration</span>
                            <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 rounded-full text-xs">Academic Supervisor</span>
                        </div>
                    </div>
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 mt-1"
                         :class="fellowType === 'academic' ? 'border-primary-600 bg-primary-600' : 'border-dark-500'">
                        <svg x-show="fellowType === 'academic'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </label>

            {{-- Corporate Intern --}}
            <label @click="fellowType = 'corporate'" 
                   class="block cursor-pointer card p-6 transition-all border-2"
                   :class="fellowType === 'corporate' ? 'border-primary-600 bg-primary-600/5' : 'border-transparent hover:border-dark-500'">
                <div class="flex items-start gap-5">
                    <div class="w-14 h-14 rounded-xl bg-emerald-500/20 flex items-center justify-center text-3xl shrink-0">🏢</div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white">Corporate Intern</h3>
                        <p class="text-dark-400 text-sm mt-1">I'm sponsored by my company or organization for a professional development internship.</p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded-full text-xs">Company Sponsored</span>
                            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded-full text-xs">Professional Growth</span>
                            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-400 rounded-full text-xs">Workplace Supervisor</span>
                        </div>
                    </div>
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 mt-1"
                         :class="fellowType === 'corporate' ? 'border-primary-600 bg-primary-600' : 'border-dark-500'">
                        <svg x-show="fellowType === 'corporate'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </label>

            {{-- Independent Fellow --}}
            <label @click="fellowType = 'independent'" 
                   class="block cursor-pointer card p-6 transition-all border-2"
                   :class="fellowType === 'independent' ? 'border-primary-600 bg-primary-600/5' : 'border-transparent hover:border-dark-500'">
                <div class="flex items-start gap-5">
                    <div class="w-14 h-14 rounded-xl bg-amber-500/20 flex items-center justify-center text-3xl shrink-0">🚀</div>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-white">Independent Fellow</h3>
                        <p class="text-dark-400 text-sm mt-1">I'm joining independently to build my career capital and professional skills.</p>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 rounded-full text-xs">Self-Enrolled</span>
                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 rounded-full text-xs">Flexible Timeline</span>
                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-400 rounded-full text-xs">Career Capital Focus</span>
                        </div>
                    </div>
                    <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center shrink-0 mt-1"
                         :class="fellowType === 'independent' ? 'border-primary-600 bg-primary-600' : 'border-dark-500'">
                        <svg x-show="fellowType === 'independent'" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </label>
        </div>

        <div class="flex justify-between">
            <button type="button" @click="prevStep()" class="btn bg-dark-700 text-dark-300 hover:bg-dark-600">Back</button>
            <button type="button" @click="saveFellowType()" :disabled="!fellowType || saving" 
                    class="btn btn-primary" :class="{ 'opacity-50 cursor-not-allowed': !fellowType }">
                Continue
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 3: Internship Details (Academic / Corporate only)
         ═══════════════════════════════════════════════════════════ --}}
    <div x-show="currentStep === 'details'" x-transition x-cloak>
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white">
                <span x-show="fellowType === 'academic'">Academic Internship Details</span>
                <span x-show="fellowType === 'corporate'">Corporate Internship Details</span>
            </h2>
            <p class="text-dark-400 mt-1">
                <span x-show="fellowType === 'academic'">Tell us about your school and internship program</span>
                <span x-show="fellowType === 'corporate'">Tell us about your company and internship arrangement</span>
            </p>
        </div>

        <form @submit.prevent="saveInternshipDetails()" class="card p-8 space-y-6" enctype="multipart/form-data">
            
            {{-- Institution Section --}}
            <div class="border-b border-dark-700 pb-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <span x-show="fellowType === 'academic'">🏫</span>
                    <span x-show="fellowType === 'corporate'">🏢</span>
                    <span x-show="fellowType === 'academic'">School / University</span>
                    <span x-show="fellowType === 'corporate'">Company / Organization</span>
                </h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">
                            <span x-show="fellowType === 'academic'">School / University Name *</span>
                            <span x-show="fellowType === 'corporate'">Company / Organization Name *</span>
                        </label>
                        <input type="text" x-model="internship.institution_name" required
                               :placeholder="fellowType === 'academic' ? 'e.g. University of Yaoundé I' : 'e.g. Acme Corporation'"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">
                            <span x-show="fellowType === 'academic'">Faculty / Department</span>
                            <span x-show="fellowType === 'corporate'">Department / Division</span>
                        </label>
                        <input type="text" x-model="internship.department"
                               :placeholder="fellowType === 'academic' ? 'e.g. Faculty of Science, Computer Science Dept.' : 'e.g. Engineering Department'"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Academic-Specific Fields --}}
            <div x-show="fellowType === 'academic'" class="border-b border-dark-700 pb-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    📚 Academic Information
                </h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Academic Level *</label>
                        <select x-model="internship.academic_level"
                                class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                            <option value="">Select your level</option>
                            @foreach($academicLevels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Student ID / Matriculation Number</label>
                        <input type="text" x-model="internship.student_id" placeholder="e.g. 20UB1234"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Supervisor Section --}}
            <div class="border-b border-dark-700 pb-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    👤 Supervisor Information
                </h3>
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Supervisor Name *</label>
                        <input type="text" x-model="internship.supervisor_name" required
                               placeholder="e.g. Dr. Jean Nkeng"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Supervisor Email *</label>
                        <input type="email" x-model="internship.supervisor_email" required
                               placeholder="supervisor@university.edu"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Supervisor Phone</label>
                        <input type="tel" x-model="internship.supervisor_phone"
                               placeholder="+237 6XX XXX XXX"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                    </div>
                </div>
            </div>

            {{-- Duration Section --}}
            <div class="border-b border-dark-700 pb-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    📅 Internship Duration
                </h3>

                {{-- Duration Type Toggle --}}
                <div class="flex gap-3 mb-6">
                    <button type="button" @click="internship.duration_type = 'predefined'"
                            class="flex-1 py-3 px-4 rounded-lg border-2 text-sm font-medium transition-all"
                            :class="internship.duration_type === 'predefined' ? 'border-primary-600 bg-primary-600/10 text-primary-400' : 'border-dark-600 bg-dark-700 text-dark-400 hover:border-dark-500'">
                        Standard Duration
                    </button>
                    <button type="button" @click="internship.duration_type = 'custom'"
                            class="flex-1 py-3 px-4 rounded-lg border-2 text-sm font-medium transition-all"
                            :class="internship.duration_type === 'custom' ? 'border-primary-600 bg-primary-600/10 text-primary-400' : 'border-dark-600 bg-dark-700 text-dark-400 hover:border-dark-500'">
                        Custom Date Range
                    </button>
                </div>

                {{-- Predefined Duration --}}
                <div x-show="internship.duration_type === 'predefined'" class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                    @foreach([1, 2, 3, 6, 12] as $months)
                    <label class="cursor-pointer">
                        <input type="radio" x-model.number="internship.predefined_duration_months" value="{{ $months }}" class="sr-only">
                        <div class="p-4 rounded-xl border-2 text-center transition-all"
                             :class="internship.predefined_duration_months === {{ $months }} ? 'border-primary-600 bg-primary-600/10' : 'border-dark-600 bg-dark-700 hover:border-dark-500'">
                            <div class="text-2xl font-bold text-white">{{ $months }}</div>
                            <div class="text-xs text-dark-400">{{ $months === 1 ? 'Month' : 'Months' }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>

                {{-- Custom Date Range --}}
                <div x-show="internship.duration_type === 'custom'" class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">Start Date *</label>
                        <input type="date" x-model="internship.start_date"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-dark-300 mb-1">End Date *</label>
                        <input type="date" x-model="internship.end_date"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                    </div>
                    {{-- Calculated Duration Display --}}
                    <div x-show="internship.start_date && internship.end_date" class="md:col-span-2">
                        <div class="bg-dark-700/50 rounded-lg p-3 flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-sm text-dark-300">Duration: <span class="text-white font-medium" x-text="calculatedDuration"></span></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Internship Letter Upload --}}
            <div class="border-b border-dark-700 pb-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    📄 Internship Letter / Convention
                </h3>
                <p class="text-dark-500 text-sm mb-4">Upload your official internship letter, convention, or agreement document. (PDF, DOC, DOCX, JPG. Max 5MB)</p>
                
                <div class="relative">
                    <input type="file" @change="handleFileUpload($event)" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                           class="hidden" x-ref="fileInput">
                    
                    <div x-show="!internship.file_name" @click="$refs.fileInput.click()"
                         class="border-2 border-dashed border-dark-600 rounded-xl p-8 text-center cursor-pointer hover:border-dark-500 transition-colors">
                        <svg class="w-10 h-10 mx-auto text-dark-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <p class="text-dark-400 text-sm">Click to upload or drag and drop</p>
                        <p class="text-dark-600 text-xs mt-1">PDF, DOC, DOCX, JPG (Max 5MB)</p>
                    </div>

                    <div x-show="internship.file_name" class="bg-dark-700/50 rounded-xl p-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg bg-primary-600/20 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-sm font-medium truncate" x-text="internship.file_name"></p>
                            <p class="text-dark-500 text-xs" x-text="internship.file_size"></p>
                        </div>
                        <button type="button" @click="removeFile()" class="text-dark-500 hover:text-red-400 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-dark-300 mb-1">Additional Notes</label>
                <textarea x-model="internship.notes" rows="2" placeholder="Any additional information about your internship..."
                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent"></textarea>
            </div>

            <div class="flex justify-between pt-2">
                <button type="button" @click="prevStep()" class="btn bg-dark-700 text-dark-300 hover:bg-dark-600">Back</button>
                <button type="submit" :disabled="saving" class="btn btn-primary">
                    Continue
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 4: Basic Info
         ═══════════════════════════════════════════════════════════ --}}
    <div x-show="currentStep === 'profile'" x-transition x-cloak>
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-white">Basic Information</h2>
            <p class="text-dark-400 mt-1">Confirm your name and contact details</p>
        </div>

        <form @submit.prevent="saveProfile()" class="card p-8 space-y-6">
            
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Full Name *</label>
                    <input type="text" x-model="profile.name" required
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-dark-300 mb-1">Phone Number</label>
                    <input type="tel" x-model="profile.phone" placeholder="+237 6XX XXX XXX"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary-600 focus:border-transparent">
                </div>
            </div>

            <p class="text-dark-500 text-sm">You'll complete the rest of your profile (bio, headline, skills, etc.) in the next step after onboarding.</p>

            <div class="flex justify-between pt-2">
                <button type="button" @click="prevStep()" class="btn bg-dark-700 text-dark-300 hover:bg-dark-600">Back</button>
                <button type="submit" :disabled="saving" class="btn btn-primary">
                    Continue
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 5: Select Goals
         ═══════════════════════════════════════════════════════════ --}}
    <div x-show="currentStep === 'goals'" x-transition x-cloak>
        <div class="text-center mb-8">
            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-gradient-to-br from-primary-600 to-accent-600 flex items-center justify-center text-3xl">
                🎯
            </div>
            <h2 class="text-2xl font-bold text-white">Set Your Goals</h2>
            <p class="text-dark-400 mt-2 max-w-xl mx-auto">
                No pressure — these help us tailor your dashboard, nudges and mentor matches.
                You can update them anytime from your profile.
            </p>
        </div>

        <form @submit.prevent="saveGoals()" class="card p-8 space-y-8">

            {{-- ── Question 1: What outcome are you aiming for? ── --}}
            <div>
                <div class="flex items-baseline justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-white">1. What are you working toward?</h3>
                        <p class="text-dark-500 text-sm">Pick everything that fits. Be honest — this shapes what we show you.</p>
                    </div>
                    <span class="text-xs text-dark-500" x-text="`${goals.length} selected`"></span>
                </div>

                <div class="grid md:grid-cols-2 gap-3">
                    @php
                    $goalOptions = [
                        ['icon' => '🎓', 'value' => 'first_internship', 'label' => 'Land my first internship',      'desc' => 'You\'re a student or fresh grad looking for real work experience.'],
                        ['icon' => '💼', 'value' => 'first_job',        'label' => 'Land my first full-time job',   'desc' => 'You\'re ready for an entry-level role and need something to prove you\'re hire-ready.'],
                        ['icon' => '🔄', 'value' => 'career_switch',    'label' => 'Switch to tech (or a new track)','desc' => 'You\'re moving from another field or pivoting between engineering / product / design.'],
                        ['icon' => '🚀', 'value' => 'promotion',        'label' => 'Grow in my current job',         'desc' => 'You want a promotion, senior title, or bigger scope where you already work.'],
                        ['icon' => '💵', 'value' => 'freelance',        'label' => 'Freelance & earn on the side',   'desc' => 'You want paying clients and repeatable freelance gigs.'],
                        ['icon' => '🛠️', 'value' => 'startup',          'label' => 'Launch my own product',          'desc' => 'You\'re building (or want to build) your own app, agency or startup.'],
                        ['icon' => '🏗️', 'value' => 'portfolio',        'label' => 'Build a portfolio recruiters trust','desc' => 'You need shipped projects and case studies that speak for themselves.'],
                        ['icon' => '🌐', 'value' => 'network',          'label' => 'Grow my network in tech',        'desc' => 'You want mentors, peers and warm intros — not just followers.'],
                    ];
                    @endphp

                    @foreach($goalOptions as $goal)
                        <label class="relative flex items-start gap-3 p-4 bg-dark-700/40 rounded-xl cursor-pointer border-2 transition-all"
                               :class="goals.includes('{{ $goal['value'] }}') ? 'border-primary-500 bg-primary-600/10' : 'border-transparent hover:border-dark-500'">
                            <input type="checkbox" value="{{ $goal['value'] }}" x-model="goals" class="sr-only">
                            <span class="text-2xl leading-none pt-0.5">{{ $goal['icon'] }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-white font-medium">{{ $goal['label'] }}</p>
                                <p class="text-dark-400 text-xs mt-1 leading-snug">{{ $goal['desc'] }}</p>
                            </div>
                            <div class="mt-1 w-5 h-5 rounded-full border-2 flex-shrink-0 flex items-center justify-center"
                                 :class="goals.includes('{{ $goal['value'] }}') ? 'border-primary-500 bg-primary-500' : 'border-dark-500'">
                                <svg x-show="goals.includes('{{ $goal['value'] }}')" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                        </label>
                    @endforeach
                </div>

                <label class="mt-3 inline-flex items-center gap-2 text-sm text-dark-400 cursor-pointer">
                    <input type="checkbox" x-model="exploring"
                           @change="if (exploring) { goals = []; primary_goal = ''; }"
                           class="rounded border-dark-600 bg-dark-700 text-primary-600 focus:ring-primary-500">
                    <span>I'm still exploring — I'll figure it out with my mentor.</span>
                </label>
            </div>

            {{-- ── Question 2: Primary goal (only shows once >1 selected) ── --}}
            <div x-show="goals.length > 1" x-transition x-cloak class="border-t border-dark-700 pt-6">
                <h3 class="text-lg font-semibold text-white mb-1">2. Which one matters most right now?</h3>
                <p class="text-dark-500 text-sm mb-4">We'll optimize your dashboard nudges for this one.</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($goalOptions as $goal)
                        <label x-show="goals.includes('{{ $goal['value'] }}')" x-cloak
                               class="flex items-center gap-2 px-3 py-2 rounded-full border-2 cursor-pointer text-sm transition-all"
                               :class="primary_goal === '{{ $goal['value'] }}' ? 'border-primary-500 bg-primary-600/20 text-white' : 'border-dark-600 text-dark-300 hover:border-dark-400'">
                            <input type="radio" name="primary_goal" value="{{ $goal['value'] }}" x-model="primary_goal" class="sr-only">
                            <span>{{ $goal['icon'] }}</span>
                            <span>{{ $goal['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Question 3: Timeline ── --}}
            <div x-show="goals.length > 0 || exploring" x-transition x-cloak class="border-t border-dark-700 pt-6">
                <h3 class="text-lg font-semibold text-white mb-1">
                    <span x-show="goals.length > 1">3.</span><span x-show="goals.length <= 1">2.</span>
                    When do you want to be ready?
                </h3>
                <p class="text-dark-500 text-sm mb-4">Sets the pace we hold you to. No penalty for being flexible.</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @php
                    $timelineOptions = [
                        ['value' => '3_months',  'label' => 'In 3 months',   'hint' => 'Sprint mode'],
                        ['value' => '6_months',  'label' => 'In 6 months',   'hint' => 'Focused'],
                        ['value' => '12_months', 'label' => 'This year',     'hint' => 'Steady'],
                        ['value' => 'exploring', 'label' => 'Still exploring','hint' => 'Learn first'],
                    ];
                    @endphp
                    @foreach($timelineOptions as $opt)
                        <label class="cursor-pointer">
                            <input type="radio" name="timeline" value="{{ $opt['value'] }}" x-model="goal_timeline" class="sr-only peer">
                            <div class="p-3 rounded-xl border-2 border-dark-600 text-center transition-all peer-checked:border-primary-500 peer-checked:bg-primary-600/10 hover:border-dark-500">
                                <p class="text-white font-medium text-sm">{{ $opt['label'] }}</p>
                                <p class="text-dark-500 text-[11px] mt-0.5">{{ $opt['hint'] }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Question 4: Success vision ── --}}
            <div x-show="goals.length > 0 || exploring" x-transition x-cloak class="border-t border-dark-700 pt-6">
                <label>
                    <h3 class="text-lg font-semibold text-white mb-1">
                        <span x-show="goals.length > 1">4.</span><span x-show="goals.length <= 1">3.</span>
                        In one sentence, what does success look like?
                    </h3>
                    <p class="text-dark-500 text-sm mb-3">
                        Optional. Writing it down doubles your chances — this is what your dashboard,
                        mentor and AI interview prep will point you toward.
                    </p>
                    <textarea x-model="goal_success_vision" maxlength="250" rows="3"
                              class="form-input w-full"
                              placeholder="e.g. Land a junior full-stack internship at a Cameroonian startup by December with 3 shipped projects on my portfolio."></textarea>
                    <p class="text-dark-500 text-xs mt-1 text-right">
                        <span x-text="(goal_success_vision || '').length"></span> / 250
                    </p>
                </label>
            </div>

            <div class="flex justify-between border-t border-dark-700 pt-6">
                <button type="button" @click="prevStep()" class="btn bg-dark-700 text-dark-300 hover:bg-dark-600">Back</button>
                <button type="submit" :disabled="(goals.length === 0 && !exploring) || saving"
                        class="btn btn-primary"
                        :class="{ 'opacity-50 cursor-not-allowed': (goals.length === 0 && !exploring) }">
                    Continue
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </form>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 6: All Set
         ═══════════════════════════════════════════════════════════ --}}
    <div x-show="currentStep === 'complete'" x-transition x-cloak>
        <div class="text-center">
            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-green-500/20 flex items-center justify-center">
                <svg class="w-12 h-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h2 class="text-3xl font-bold text-white mb-3">You're All Set! 🎉</h2>
            <p class="text-dark-400 max-w-lg mx-auto mb-4">Your profile is ready. Now choose a career track to start earning Career Capital points.</p>

            {{-- Summary Card --}}
            <div class="card p-6 mb-8 text-left max-w-md mx-auto">
                <h3 class="text-sm font-semibold text-dark-400 uppercase tracking-wider mb-4">Your Onboarding Summary</h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-600/20 flex items-center justify-center text-sm">
                            <span x-text="fellowType === 'academic' ? '🎓' : (fellowType === 'corporate' ? '🏢' : '🚀')"></span>
                        </div>
                        <div>
                            <div class="text-xs text-dark-500">Fellow Type</div>
                            <div class="text-white text-sm font-medium" x-text="fellowType === 'academic' ? 'Academic Intern' : (fellowType === 'corporate' ? 'Corporate Intern' : 'Independent Fellow')"></div>
                        </div>
                    </div>
                    <div x-show="fellowType !== 'independent'" class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-600/20 flex items-center justify-center text-sm">🏫</div>
                        <div>
                            <div class="text-xs text-dark-500">Institution</div>
                            <div class="text-white text-sm font-medium" x-text="internship.institution_name"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-600/20 flex items-center justify-center text-sm">👤</div>
                        <div>
                            <div class="text-xs text-dark-500">Name</div>
                            <div class="text-white text-sm font-medium" x-text="profile.name"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary-600/20 flex items-center justify-center text-sm">🎯</div>
                        <div>
                            <div class="text-xs text-dark-500">Goals</div>
                            <div class="text-white text-sm font-medium" x-text="goals.length + ' selected'"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button @click="completeOnboarding()" :disabled="saving" class="btn btn-primary px-8">
                    Complete Your Profile
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function onboardingWizard() {
    return {
        // Step management
        allSteps: ['welcome', 'type', 'details', 'profile', 'goals', 'complete'],
        currentStep: 'welcome',
        stepLabels: {
            welcome: 'Welcome',
            type: 'Fellow Type',
            details: 'Internship',
            profile: 'Basic Info',
            goals: 'Goals',
            complete: 'Done'
        },

        // Fellow type
        fellowType: @json($user->fellow_type?->value ?? ''),

        // Internship data
        internship: {
            institution_name: @json($internshipProfile->institution_name ?? ''),
            department: @json($internshipProfile->department ?? ''),
            academic_level: @json($internshipProfile->academic_level ?? ''),
            student_id: @json($internshipProfile->student_id ?? ''),
            supervisor_name: @json($internshipProfile->supervisor_name ?? ''),
            supervisor_email: @json($internshipProfile->supervisor_email ?? ''),
            supervisor_phone: @json($internshipProfile->supervisor_phone ?? ''),
            duration_type: @json($internshipProfile->duration_type ?? 'predefined'),
            predefined_duration_months: @json($internshipProfile->predefined_duration_months ?? null),
            start_date: @json(optional($internshipProfile)->start_date ? $internshipProfile->start_date->format('Y-m-d') : ''),
            end_date: @json(optional($internshipProfile)->end_date ? $internshipProfile->end_date->format('Y-m-d') : ''),
            notes: @json($internshipProfile->notes ?? ''),
            file: null,
            file_name: @json($internshipProfile && $internshipProfile->internship_letter_path ? basename($internshipProfile->internship_letter_path) : ''),
            file_size: '',
        },

        // Profile data (only name & phone — other fields collected in complete-profile)
        profile: {
            name: @json($user->name ?? ''),
            phone: @json($user->phone ?? ''),
        },

        // Goals
        goals: @json(isset($user->skills['goals']) ? $user->skills['goals'] : []),
        primary_goal: @json($user->skills['primary_goal'] ?? ''),
        goal_timeline: @json($user->skills['goal_timeline'] ?? ''),
        goal_success_vision: @json($user->skills['goal_success_vision'] ?? ''),
        exploring: @json(($user->skills['goal_timeline'] ?? '') === 'exploring' && empty($user->skills['goals'] ?? [])),

        // UI state
        saving: false,
        errorMessage: '',
        validationErrors: [],

        // Computed: active steps based on fellow type
        get activeSteps() {
            if (this.fellowType === 'independent' || this.fellowType === '') {
                return this.allSteps.filter(s => s !== 'details');
            }
            return this.allSteps;
        },

        get currentStepNumber() {
            return this.activeSteps.indexOf(this.currentStep) + 1;
        },

        get totalSteps() {
            return this.activeSteps.length;
        },

        get calculatedDuration() {
            if (!this.internship.start_date || !this.internship.end_date) return '';
            const start = new Date(this.internship.start_date);
            const end = new Date(this.internship.end_date);
            const diffTime = end - start;
            if (diffTime <= 0) return 'Invalid range';
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const months = Math.floor(diffDays / 30);
            const days = diffDays % 30;
            if (months === 0) return diffDays + ' days';
            if (days === 0) return months === 1 ? '1 month' : months + ' months';
            return months + ' month' + (months > 1 ? 's' : '') + ' and ' + days + ' day' + (days > 1 ? 's' : '');
        },

        // Navigation
        nextStep() {
            const idx = this.activeSteps.indexOf(this.currentStep);
            if (idx < this.activeSteps.length - 1) {
                this.currentStep = this.activeSteps[idx + 1];
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        prevStep() {
            const idx = this.activeSteps.indexOf(this.currentStep);
            if (idx > 0) {
                this.currentStep = this.activeSteps[idx - 1];
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        // File upload handling
        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (file.size > 5 * 1024 * 1024) {
                this.showError('File size must be less than 5MB');
                return;
            }

            const allowed = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
            if (!allowed.includes(file.type)) {
                this.showError('Please upload a PDF, DOC, DOCX, JPG, or PNG file');
                return;
            }

            this.internship.file = file;
            this.internship.file_name = file.name;
            this.internship.file_size = this.formatFileSize(file.size);
        },

        removeFile() {
            this.internship.file = null;
            this.internship.file_name = '';
            this.internship.file_size = '';
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },

        formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        },

        // API calls
        async saveFellowType() {
            if (!this.fellowType) return;
            this.saving = true;
            this.clearErrors();

            try {
                const response = await fetch(@json(route('fellow.onboarding.save-type')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ fellow_type: this.fellowType }),
                });

                const data = await response.json();
                if (!response.ok) throw data;

                this.nextStep();
            } catch (err) {
                this.handleApiError(err);
            } finally {
                this.saving = false;
            }
        },

        async saveInternshipDetails() {
            this.saving = true;
            this.clearErrors();

            try {
                const formData = new FormData();
                formData.append('institution_name', this.internship.institution_name);
                formData.append('department', this.internship.department || '');
                formData.append('supervisor_name', this.internship.supervisor_name);
                formData.append('supervisor_email', this.internship.supervisor_email);
                formData.append('supervisor_phone', this.internship.supervisor_phone || '');
                formData.append('duration_type', this.internship.duration_type);
                formData.append('notes', this.internship.notes || '');

                if (this.fellowType === 'academic') {
                    formData.append('academic_level', this.internship.academic_level);
                    formData.append('student_id', this.internship.student_id || '');
                }

                if (this.internship.duration_type === 'predefined') {
                    formData.append('predefined_duration_months', this.internship.predefined_duration_months || '');
                } else {
                    formData.append('start_date', this.internship.start_date || '');
                    formData.append('end_date', this.internship.end_date || '');
                }

                if (this.internship.file) {
                    formData.append('internship_letter', this.internship.file);
                }

                const response = await fetch(@json(route('fellow.onboarding.save-internship')), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const data = await response.json();
                if (!response.ok) throw data;

                this.nextStep();
            } catch (err) {
                this.handleApiError(err);
            } finally {
                this.saving = false;
            }
        },

        async saveProfile() {
            this.saving = true;
            this.clearErrors();

            try {
                const response = await fetch(@json(route('fellow.onboarding.save-profile')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.profile),
                });

                const data = await response.json();
                if (!response.ok) throw data;

                this.nextStep();
            } catch (err) {
                this.handleApiError(err);
            } finally {
                this.saving = false;
            }
        },

        async saveGoals() {
            if (this.goals.length === 0 && !this.exploring) return;
            // Auto-set primary_goal when only one selected
            if (this.goals.length === 1) this.primary_goal = this.goals[0];
            if (this.exploring && !this.goal_timeline) this.goal_timeline = 'exploring';

            this.saving = true;
            this.clearErrors();

            try {
                const response = await fetch(@json(route('fellow.onboarding.save-goals')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        goals: this.goals,
                        primary_goal: this.primary_goal || null,
                        goal_timeline: this.goal_timeline || null,
                        goal_success_vision: this.goal_success_vision || null,
                        exploring: this.exploring,
                    }),
                });

                const data = await response.json();
                if (!response.ok) throw data;

                this.nextStep();
            } catch (err) {
                this.handleApiError(err);
            } finally {
                this.saving = false;
            }
        },

        async completeOnboarding(redirectUrl) {
            this.saving = true;
            this.clearErrors();

            try {
                const response = await fetch(@json(route('fellow.onboarding.complete')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({}),
                });

                const data = await response.json();
                if (!response.ok) throw data;

                window.location.href = redirectUrl || data.redirect || @json(route('profile.complete'));
            } catch (err) {
                this.handleApiError(err);
            } finally {
                this.saving = false;
            }
        },

        // Error handling
        showError(message) {
            this.errorMessage = message;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        clearErrors() {
            this.errorMessage = '';
            this.validationErrors = [];
        },

        handleApiError(err) {
            if (err.errors) {
                this.validationErrors = Object.values(err.errors).flat();
                this.errorMessage = err.message || 'Please fix the errors below.';
            } else if (err.message) {
                this.errorMessage = err.message;
            } else {
                this.errorMessage = 'Something went wrong. Please try again.';
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        // Resume onboarding from where the user left off
        init() {
            const hasType = !!this.fellowType;
            const hasInternship = @json($internshipProfile ? true : false);
            const onboardingDone = @json($user->onboarding_completed_at ? true : false);

            if (onboardingDone) {
                this.currentStep = 'complete';
            } else if (hasType && hasInternship) {
                this.currentStep = 'profile';
            } else if (hasType && this.fellowType !== 'independent' && !hasInternship) {
                this.currentStep = 'details';
            } else if (hasType) {
                this.currentStep = 'profile';
            }
        }
    };
}
</script>
@endpush
@endsection
