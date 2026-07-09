<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CurriculumController as AdminCurriculumController;
use App\Http\Controllers\Admin\FeeController as AdminFeeController;
use App\Http\Controllers\Fellow\FeeController as FellowFeeController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Fellow\OnboardingController;
use App\Http\Controllers\Fellow\CurriculumController as FellowCurriculumController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InterviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecruiterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Landing page - loads dynamic content from CMS
Route::get('/', [HomeController::class, 'index'])->name('home');

// Public Talent Directory
Route::get('/talent', [ProfileController::class, 'directory'])->name('public.talent.directory');

// Public profile viewing (by username)
Route::get('/talent/{user:username}', [ProfileController::class, 'public'])->name('talent.profile');

// Public Profile Show (by ID - for directory links)
Route::get('/profile/{user}', [ProfileController::class, 'publicById'])->name('public.profile.show');

// Public Receipt Verification (no auth needed)
Route::get('/receipt/verify/{uuid}', [AdminFeeController::class, 'publicVerify'])->name('receipt.verify');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:5,1');
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [\App\Http\Controllers\Auth\VerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerificationController::class, 'verify'])
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('/email/verification-notification', [\App\Http\Controllers\Auth\VerificationController::class, 'send'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile Completion
    |--------------------------------------------------------------------------
    */
    Route::get('/complete-profile', [ProfileController::class, 'complete'])->name('profile.complete');
    Route::post('/complete-profile', [ProfileController::class, 'storeComplete'])->name('profile.complete.store');

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/toggle-visibility', [ProfileController::class, 'toggleVisibility'])->name('profile.toggle-visibility');

    /*
    |--------------------------------------------------------------------------
    | Fellow Onboarding Routes (outside profile.complete middleware)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:fellow')->prefix('onboarding')->name('fellow.onboarding')->group(function () {
        Route::get('/', [OnboardingController::class, 'index']);
        Route::post('/fellow-type', [OnboardingController::class, 'saveFellowType'])->name('.save-type');
        Route::post('/internship-details', [OnboardingController::class, 'saveInternshipDetails'])->name('.save-internship');
        Route::post('/profile', [OnboardingController::class, 'saveProfile'])->name('.save-profile');
        Route::post('/goals', [OnboardingController::class, 'saveGoals'])->name('.save-goals');
        Route::post('/complete', [OnboardingController::class, 'complete'])->name('.complete');
    });

    /*
    |--------------------------------------------------------------------------
    | Fellow Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:fellow', 'profile.complete', 'internship.approved', 'active.track'])->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
        Route::get('/dashboard/score-breakdown', [DashboardController::class, 'scoreBreakdown'])->name('dashboard.score-breakdown');
        Route::get('/dashboard/track-comparison', [DashboardController::class, 'trackComparison'])->name('dashboard.track-comparison');

        // Activities
        Route::resource('activities', ActivityController::class);
        Route::post('/activities/{activity}/complete', [ActivityController::class, 'complete'])->name('activities.complete');

        // Interviews - Static routes MUST come before resource/parameterized routes
        // Practice Mode - Unlimited practice without affecting Career Capital
        Route::get('/interviews/practice/start', [InterviewController::class, 'practiceRoom'])->name('interviews.practice');

        // Live AI Interview - Conversational voice-enabled interviews
        Route::get('/interviews/live', [\App\Http\Controllers\LiveInterviewController::class, 'lobby'])->name('interviews.live.lobby');
        Route::post('/interviews/live/start', [\App\Http\Controllers\LiveInterviewController::class, 'start'])->name('interviews.live.start');

        // Interview resource and parameterized routes
        Route::resource('interviews', InterviewController::class)->except(['edit', 'update', 'destroy']);
        Route::post('/interviews/{interview}/start', [InterviewController::class, 'start'])->name('interviews.start');
        Route::post('/interviews/{interview}/cancel', [InterviewController::class, 'cancel'])->name('interviews.cancel');
        Route::post('/interviews/{interview}/complete', [InterviewController::class, 'complete'])->name('interviews.complete');
        Route::get('/interviews/{interview}/ai-room', [InterviewController::class, 'aiRoom'])->name('interviews.ai-room');
        Route::get('/interviews/{interview}/questions', [InterviewController::class, 'getQuestions'])->name('interviews.questions');
        Route::post('/interviews/{interview}/evaluate', [InterviewController::class, 'evaluateResponse'])->name('interviews.evaluate');
        
        // Live interview parameterized routes (require interview ID)
        Route::get('/interviews/{interview}/live', [\App\Http\Controllers\LiveInterviewController::class, 'room'])->name('interviews.live.room');
        Route::post('/interviews/{interview}/live/message', [\App\Http\Controllers\LiveInterviewController::class, 'sendMessage'])->name('interviews.live.message');
        Route::post('/interviews/{interview}/live/hint', [\App\Http\Controllers\LiveInterviewController::class, 'getHint'])->name('interviews.live.hint');
        Route::post('/interviews/{interview}/live/skip', [\App\Http\Controllers\LiveInterviewController::class, 'skipQuestion'])->name('interviews.live.skip');
        Route::post('/interviews/{interview}/live/end', [\App\Http\Controllers\LiveInterviewController::class, 'end'])->name('interviews.live.end');
        Route::get('/interviews/{interview}/live/progress', [\App\Http\Controllers\LiveInterviewController::class, 'getProgress'])->name('interviews.live.progress');

        // Weekly Progress
        Route::get('/weekly-progress', [\App\Http\Controllers\WeeklyProgressController::class, 'index'])->name('weekly-progress.index');
        Route::get('/weekly-progress/submit', [\App\Http\Controllers\WeeklyProgressController::class, 'create'])->name('weekly-progress.submit');
        Route::post('/weekly-progress', [\App\Http\Controllers\WeeklyProgressController::class, 'store'])->name('weekly-progress.store');

        // Track selection
        // Track selection & switching
        Route::get('/tracks', [\App\Http\Controllers\TrackController::class, 'index'])->name('tracks.index');
        Route::get('/tracks/select', [\App\Http\Controllers\TrackController::class, 'select'])->name('tracks.select');
        Route::post('/tracks/enroll', [\App\Http\Controllers\TrackController::class, 'enroll'])->name('tracks.enroll');
        Route::post('/tracks/switch-primary', [\App\Http\Controllers\TrackController::class, 'switchPrimary'])->name('tracks.switch-primary');
        Route::post('/tracks/switch-active', [\App\Http\Controllers\TrackController::class, 'switchActive'])->name('tracks.switch-active');

        // ==============================================
        // Curriculum System (Structured Track Activities)
        // ==============================================
        Route::prefix('curriculum')->name('curriculum.')->group(function () {
            Route::get('/', [FellowCurriculumController::class, 'index'])->name('index');
            Route::get('/{track}', [FellowCurriculumController::class, 'track'])->name('track')->where('track', '[0-9a-f\-]{36}');

            // Activity interactions
            Route::get('/activities/{activity}', [FellowCurriculumController::class, 'showActivity'])->name('activity.show');
            Route::post('/activities/{activity}/start', [FellowCurriculumController::class, 'startActivity'])->name('activity.start');
            Route::post('/activities/{activity}/interview', [FellowCurriculumController::class, 'launchInterview'])->name('activity.interview');

            // Submission
            Route::get('/progress/{progress}/submit', [FellowCurriculumController::class, 'submitForm'])->name('submit.form');
            Route::post('/progress/{progress}/submit', [FellowCurriculumController::class, 'submit'])->name('submit');

            // Peer review
            Route::get('/peer-review/{progress}', [FellowCurriculumController::class, 'peerReviewForm'])->name('peer-review.form');
            Route::post('/peer-review/{progress}', [FellowCurriculumController::class, 'peerReviewSubmit'])->name('peer-review.submit');

            // Badges
            Route::get('/badges', [FellowCurriculumController::class, 'badges'])->name('badges');
            Route::post('/badges/{badge}/share', [FellowCurriculumController::class, 'shareBadge'])->name('badges.share');
        });

        // Fellow Fees
        Route::get('/fees', [FellowFeeController::class, 'index'])->name('fees.index');
        Route::get('/fees/{fee}', [FellowFeeController::class, 'show'])->name('fees.show');
        Route::get('/fees/{fee}/upload', [FellowFeeController::class, 'uploadForm'])->name('fees.upload');
        Route::post('/fees/{fee}/upload', [FellowFeeController::class, 'uploadStore'])->name('fees.upload.store');
        Route::get('/fees/payments/{payment}/receipt', [FellowFeeController::class, 'downloadReceipt'])->name('fees.receipt');

        // Attendance Tracking
        Route::get('/attendance', [\App\Http\Controllers\Fellow\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/clock-in', [\App\Http\Controllers\Fellow\AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [\App\Http\Controllers\Fellow\AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    });

    /*
    |--------------------------------------------------------------------------
    | Recruiter Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:recruiter')->prefix('recruiter')->name('recruiter.')->group(function () {
        
        Route::get('/dashboard', [RecruiterController::class, 'dashboard'])->name('dashboard');
        Route::get('/onboarding', [RecruiterController::class, 'onboarding'])->name('onboarding');
        
        // Routes requiring active subscription
        Route::middleware('subscription.active')->group(function () {
            // Marketplace
            Route::get('/marketplace', [RecruiterController::class, 'marketplace'])->name('marketplace.index');
            Route::get('/talent/{user}', [RecruiterController::class, 'viewProfile'])->name('talent.show');
            Route::post('/talent/{user}/shortlist', [RecruiterController::class, 'addToShortlist'])->name('talent.shortlist');
            Route::delete('/talent/{user}/shortlist', [RecruiterController::class, 'removeFromShortlist'])->name('talent.unshortlist');
            Route::post('/talent/{user}/contact', [RecruiterController::class, 'contact'])->name('talent.contact');
            
            Route::get('/shortlist', [RecruiterController::class, 'shortlist'])->name('shortlist.index');
        });
        
        // Subscription (accessible without active subscription so recruiters can subscribe/upgrade)
        Route::get('/subscription', [RecruiterController::class, 'subscriptionIndex'])->name('subscription.index');
        Route::post('/subscription/trial', [RecruiterController::class, 'startTrial'])->name('subscription.trial');
        Route::post('/subscription/subscribe', [RecruiterController::class, 'subscribe'])->name('subscription.subscribe');
        Route::post('/subscription/upgrade', [RecruiterController::class, 'upgrade'])->name('subscription.upgrade');
        Route::post('/subscription/cancel', [RecruiterController::class, 'cancelSubscription'])->name('subscription.cancel');
    });

    /*
    |--------------------------------------------------------------------------
    | Mentor Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:mentor')->prefix('mentor')->name('mentor.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\MentorController::class, 'dashboard'])->name('dashboard');
        
        Route::get('/interviews', [\App\Http\Controllers\MentorController::class, 'interviews'])->name('interviews');
        Route::get('/interviews/{interview}', [\App\Http\Controllers\MentorController::class, 'reviewInterview'])->name('interviews.review');
        Route::post('/interviews/{interview}/complete', [\App\Http\Controllers\MentorController::class, 'completeInterview'])->name('interviews.complete');
        
        Route::get('/availability', [\App\Http\Controllers\MentorController::class, 'availability'])->name('availability');
        Route::post('/availability', [\App\Http\Controllers\MentorController::class, 'updateAvailability'])->name('availability.update');
        
        Route::get('/profile', [\App\Http\Controllers\MentorController::class, 'profile'])->name('profile');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        
        // Redirect /admin to /admin/dashboard
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        
        // User & Role Management
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class)->only(['index', 'edit', 'update']);
        
        // Activity Queue
        Route::get('/activities', [AdminController::class, 'activityQueue'])->name('activities.queue');
        Route::get('/activities/{activity}/review', [AdminController::class, 'reviewActivity'])->name('activities.review');
        Route::patch('/activities/{activity}', [AdminController::class, 'updateActivityReview'])->name('activities.update');
        Route::post('/activities/{activity}/approve', [AdminController::class, 'approveActivity'])->name('activities.approve');
        Route::post('/activities/{activity}/reject', [AdminController::class, 'rejectActivity'])->name('activities.reject');
        Route::post('/activities/{activity}/revision', [AdminController::class, 'needsRevision'])->name('activities.revision');
        
        // Fellow Management
        Route::get('/fellows', [AdminController::class, 'fellows'])->name('fellows.index');
        Route::get('/fellows/{user}', [AdminController::class, 'showFellow'])->name('fellows.show');
        Route::post('/fellows/{user}/toggle-status', [AdminController::class, 'toggleFellowStatus'])->name('fellows.toggle-status');

        // Internship Profile Review
        Route::get('/internships', [\App\Http\Controllers\Admin\InternshipController::class, 'index'])->name('internships.index');
        Route::get('/internships/{internship}', [\App\Http\Controllers\Admin\InternshipController::class, 'show'])->name('internships.show');
        Route::get('/internships/{internship}/letter/preview', [\App\Http\Controllers\Admin\InternshipController::class, 'previewLetter'])->name('internships.letter.preview');
        Route::get('/internships/{internship}/letter', [\App\Http\Controllers\Admin\InternshipController::class, 'downloadLetter'])->name('internships.letter');
        Route::post('/internships/{internship}/approve', [\App\Http\Controllers\Admin\InternshipController::class, 'approve'])->name('internships.approve');
        Route::post('/internships/{internship}/request-changes', [\App\Http\Controllers\Admin\InternshipController::class, 'requestChanges'])->name('internships.request-changes');
        Route::post('/internships/{internship}/reject', [\App\Http\Controllers\Admin\InternshipController::class, 'reject'])->name('internships.reject');

        // Fee Reports
        Route::get('/fees', [AdminFeeController::class, 'index'])->name('fees.index');
        Route::get('/fees/create', [AdminFeeController::class, 'create'])->name('fees.create');
        Route::post('/fees', [AdminFeeController::class, 'store'])->name('fees.store');
        Route::get('/fees/{fee}', [AdminFeeController::class, 'show'])->name('fees.show');
        Route::post('/fees/{fee}/payment', [AdminFeeController::class, 'recordPayment'])->name('fees.record-payment');
        Route::post('/fees/{fee}/waive', [AdminFeeController::class, 'waive'])->name('fees.waive');
        Route::delete('/fees/{fee}', [AdminFeeController::class, 'destroy'])->name('fees.destroy');
        Route::get('/fees/payments/{payment}/receipt', [AdminFeeController::class, 'printReceipt'])->name('fees.receipt');
        Route::get('/fees/fellow/{fellow}/billables', [AdminFeeController::class, 'getFellowBillables'])->name('fees.fellow-billables');

        // Payment Verifications
        Route::get('/payment-verifications', [AdminFeeController::class, 'verifications'])->name('payment-verifications.index');
        Route::get('/payment-verifications/{payment}', [AdminFeeController::class, 'showVerification'])->name('payment-verifications.show');
        Route::post('/payment-verifications/{payment}/approve', [AdminFeeController::class, 'approveVerification'])->name('payment-verifications.approve');
        Route::post('/payment-verifications/{payment}/reject', [AdminFeeController::class, 'rejectVerification'])->name('payment-verifications.reject');
        Route::delete('/payment-verifications/{payment}', [AdminFeeController::class, 'destroyPayment'])->name('payment-verifications.destroy');

        // Attendance Management
        Route::get('/attendance', [\App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/start', [\App\Http\Controllers\Admin\AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/{session}', [\App\Http\Controllers\Admin\AttendanceController::class, 'show'])->name('attendance.show');
        Route::post('/attendance/{session}/close', [\App\Http\Controllers\Admin\AttendanceController::class, 'close'])->name('attendance.close');
        Route::post('/attendance/{session}/records/{record}', [\App\Http\Controllers\Admin\AttendanceController::class, 'updateRecord'])->name('attendance.update-record');
        Route::get('/attendance/{session}/live-data', [\App\Http\Controllers\Admin\AttendanceController::class, 'liveData'])->name('attendance.live-data');

        // Track Enrollment Review
        Route::get('/track-enrollments', [\App\Http\Controllers\Admin\TrackEnrollmentController::class, 'index'])->name('track-enrollments.index');
        Route::get('/track-enrollments/{enrollment}', [\App\Http\Controllers\Admin\TrackEnrollmentController::class, 'show'])->name('track-enrollments.show');
        Route::post('/track-enrollments/{enrollment}/approve', [\App\Http\Controllers\Admin\TrackEnrollmentController::class, 'approve'])->name('track-enrollments.approve');
        Route::post('/track-enrollments/{enrollment}/request-changes', [\App\Http\Controllers\Admin\TrackEnrollmentController::class, 'requestChanges'])->name('track-enrollments.request-changes');
        Route::post('/track-enrollments/{enrollment}/reject', [\App\Http\Controllers\Admin\TrackEnrollmentController::class, 'reject'])->name('track-enrollments.reject');
        
        // Track Management
        Route::get('/tracks', [AdminController::class, 'tracks'])->name('tracks.index');
        Route::get('/tracks/create', [AdminController::class, 'createTrack'])->name('tracks.create');
        Route::post('/tracks', [AdminController::class, 'storeTrack'])->name('tracks.store');
        Route::get('/tracks/{track}/edit', [AdminController::class, 'editTrack'])->name('tracks.edit');
        Route::patch('/tracks/{track}', [AdminController::class, 'updateTrack'])->name('tracks.update');
        Route::delete('/tracks/{track}', [AdminController::class, 'destroyTrack'])->name('tracks.destroy');
        Route::post('/tracks/{track}/toggle', [AdminController::class, 'toggleTrack'])->name('tracks.toggle');
        
        // Recruiter Management
        Route::get('/recruiters', [AdminController::class, 'recruiters'])->name('recruiters.index');
        Route::get('/recruiters/{user}', [AdminController::class, 'showRecruiter'])->name('recruiters.show');
        Route::post('/recruiters/{user}/approve', [AdminController::class, 'approveRecruiter'])->name('recruiters.approve');
        Route::post('/recruiters/{user}/suspend', [AdminController::class, 'suspendRecruiter'])->name('recruiters.suspend');
        Route::post('/recruiters/{user}/activate', [AdminController::class, 'activateRecruiter'])->name('recruiters.activate');
        
        // Mentor Management
        Route::get('/mentors', [AdminController::class, 'mentors'])->name('mentors.index');
        Route::get('/mentors/{user}', [AdminController::class, 'showMentor'])->name('mentors.show');
        Route::post('/mentors/{user}/approve', [AdminController::class, 'approveMentor'])->name('mentors.approve');
        Route::post('/mentors/{user}/suspend', [AdminController::class, 'suspendMentor'])->name('mentors.suspend');
        Route::post('/mentors/{user}/activate', [AdminController::class, 'activateMentor'])->name('mentors.activate');
        
        // Interview Management
        Route::get('/interviews', [AdminController::class, 'interviews'])->name('interviews.index');
        Route::get('/interviews/analytics', [AdminController::class, 'interviewAnalytics'])->name('interviews.analytics');
        Route::get('/interviews/export', [AdminController::class, 'exportInterviews'])->name('interviews.export');
        Route::get('/interviews/{interview}', [AdminController::class, 'showInterview'])->name('interviews.show');
        Route::post('/interviews/{interview}/assign-mentor', [AdminController::class, 'assignMentor'])->name('interviews.assign-mentor');
        Route::post('/interviews/{interview}/cancel', [AdminController::class, 'cancelInterview'])->name('interviews.cancel');
        Route::post('/interviews/{interview}/reschedule', [AdminController::class, 'rescheduleInterview'])->name('interviews.reschedule');
        
        // Cohort Management
        Route::get('/cohorts', [AdminController::class, 'cohorts'])->name('cohorts.index');
        Route::get('/cohorts/create', [AdminController::class, 'createCohort'])->name('cohorts.create');
        Route::post('/cohorts', [AdminController::class, 'storeCohort'])->name('cohorts.store');
        Route::get('/cohorts/{cohort}', [AdminController::class, 'showCohort'])->name('cohorts.show');
        Route::get('/cohorts/{cohort}/edit', [AdminController::class, 'editCohort'])->name('cohorts.edit');
        Route::patch('/cohorts/{cohort}', [AdminController::class, 'updateCohort'])->name('cohorts.update');
        Route::delete('/cohorts/{cohort}', [AdminController::class, 'destroyCohort'])->name('cohorts.destroy');
        Route::post('/cohorts/{cohort}/transition', [AdminController::class, 'transitionCohort'])->name('cohorts.transition');
        Route::post('/cohorts/{cohort}/enroll', [AdminController::class, 'enrollFellow'])->name('cohorts.enroll');
        Route::post('/cohorts/{cohort}/enroll-bulk', [AdminController::class, 'bulkEnrollFellows'])->name('cohorts.enroll-bulk');
        Route::post('/cohorts/{cohort}/fellows/{fellow}/remove', [AdminController::class, 'removeFellow'])->name('cohorts.remove-fellow');
        Route::post('/cohorts/{cohort}/fellows/{fellow}/complete', [AdminController::class, 'markFellowCompleted'])->name('cohorts.complete-fellow');
        
        // Program Management (Administrative groupings across all tracks)
        Route::get('/programs', [AdminController::class, 'programs'])->name('programs.index');
        Route::get('/programs/create', [AdminController::class, 'createProgram'])->name('programs.create');
        Route::post('/programs', [AdminController::class, 'storeProgram'])->name('programs.store');
        Route::get('/programs/{program}', [AdminController::class, 'showProgram'])->name('programs.show');
        Route::get('/programs/{program}/edit', [AdminController::class, 'editProgram'])->name('programs.edit');
        Route::patch('/programs/{program}', [AdminController::class, 'updateProgram'])->name('programs.update');
        Route::delete('/programs/{program}', [AdminController::class, 'destroyProgram'])->name('programs.destroy');
        Route::post('/programs/{program}/transition', [AdminController::class, 'transitionProgram'])->name('programs.transition');
        Route::post('/programs/{program}/enroll', [AdminController::class, 'enrollFellowInProgram'])->name('programs.enroll');
        Route::post('/programs/{program}/enroll-bulk', [AdminController::class, 'bulkEnrollFellowsInProgram'])->name('programs.enroll-bulk');
        Route::delete('/programs/{program}/fellows/{fellow}', [AdminController::class, 'removeFellowFromProgram'])->name('programs.remove-fellow');
        Route::post('/programs/{program}/fellows/{fellow}/graduate', [AdminController::class, 'graduateFellowFromProgram'])->name('programs.graduate-fellow');
        Route::post('/programs/{program}/fellows/{fellow}/certificate', [AdminController::class, 'issueCertificateForProgram'])->name('programs.issue-certificate');
        Route::put('/programs/{program}/fellows/{fellow}/outcome', [AdminController::class, 'updateAlumniOutcome'])->name('programs.update-outcome');
        Route::post('/programs/{program}/announce', [AdminController::class, 'sendProgramAnnouncement'])->name('programs.announce');
        Route::get('/programs/{program}/export', [AdminController::class, 'exportProgramFellows'])->name('programs.export');
        
        // ==============================================
        // Curriculum Management
        // ==============================================
        Route::prefix('tracks/{track}/curriculum')->name('curriculum.')->group(function () {
            // Curriculum overview for a track
            Route::get('/', [AdminCurriculumController::class, 'index'])->name('index');
            Route::get('/analytics', [AdminCurriculumController::class, 'analytics'])->name('analytics');

            // Milestone CRUD
            Route::get('/milestones/create', [AdminCurriculumController::class, 'createMilestone'])->name('milestones.create');
            Route::post('/milestones', [AdminCurriculumController::class, 'storeMilestone'])->name('milestones.store');
            Route::get('/milestones/{milestone}/edit', [AdminCurriculumController::class, 'editMilestone'])->name('milestones.edit');
            Route::put('/milestones/{milestone}', [AdminCurriculumController::class, 'updateMilestone'])->name('milestones.update');
            Route::delete('/milestones/{milestone}', [AdminCurriculumController::class, 'destroyMilestone'])->name('milestones.destroy');
            Route::post('/milestones/reorder', [AdminCurriculumController::class, 'reorderMilestones'])->name('milestones.reorder');

            // Activity CRUD within milestones
            Route::get('/milestones/{milestone}/activities/create', [AdminCurriculumController::class, 'createActivity'])->name('activities.create');
            Route::post('/milestones/{milestone}/activities', [AdminCurriculumController::class, 'storeActivity'])->name('activities.store');
            Route::get('/activities/{activity}/edit', [AdminCurriculumController::class, 'editActivity'])->name('activities.edit');
            Route::put('/activities/{activity}', [AdminCurriculumController::class, 'updateActivity'])->name('activities.update');
            Route::delete('/activities/{activity}', [AdminCurriculumController::class, 'destroyActivity'])->name('activities.destroy');
            Route::post('/milestones/{milestone}/activities/reorder', [AdminCurriculumController::class, 'reorderActivities'])->name('activities.reorder');

            // Accountability pairs
            Route::get('/pairs', [AdminCurriculumController::class, 'pairs'])->name('pairs');
            Route::post('/pairs/auto', [AdminCurriculumController::class, 'autoPair'])->name('pairs.auto');
            Route::post('/pairs/rotate', [AdminCurriculumController::class, 'rotatePairs'])->name('pairs.rotate');
        });

        // Curriculum review queue (not track-specific)
        Route::get('/curriculum/reviews', [AdminCurriculumController::class, 'reviewQueue'])->name('curriculum.reviews');
        Route::get('/curriculum/reviews/{progress}', [AdminCurriculumController::class, 'reviewShow'])->name('curriculum.reviews.show');
        Route::post('/curriculum/reviews/{progress}', [AdminCurriculumController::class, 'reviewProcess'])->name('curriculum.reviews.process');

        // Settings
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::patch('/settings/{group}', [AdminController::class, 'updateSettings'])->name('settings.update');
        Route::post('/settings/initialize', [AdminController::class, 'initializeSettings'])->name('settings.initialize');
        
        // Content Management (CMS)
        Route::get('/content', [ContentController::class, 'index'])->name('content.index');
        Route::patch('/content/section/{section}', [ContentController::class, 'updateSection'])->name('content.section.update');
        Route::post('/content/seed', [ContentController::class, 'seed'])->name('content.seed');
        
        // Testimonials CRUD
        Route::post('/content/testimonials', [ContentController::class, 'storeTestimonial'])->name('content.testimonials.store');
        Route::post('/content/testimonials/{testimonial}/toggle', [ContentController::class, 'toggleTestimonial'])->name('content.testimonials.toggle');
        Route::delete('/content/testimonials/{testimonial}', [ContentController::class, 'destroyTestimonial'])->name('content.testimonials.destroy');
        
        // FAQs CRUD
        Route::post('/content/faqs', [ContentController::class, 'storeFaq'])->name('content.faqs.store');
        Route::post('/content/faqs/{faq}/toggle', [ContentController::class, 'toggleFaq'])->name('content.faqs.toggle');
        Route::delete('/content/faqs/{faq}', [ContentController::class, 'destroyFaq'])->name('content.faqs.destroy');
        
        // Footer Links CRUD
        Route::post('/content/footer-links', [ContentController::class, 'storeFooterLink'])->name('content.footer-links.store');
        Route::delete('/content/footer-links/{footerLink}', [ContentController::class, 'destroyFooterLink'])->name('content.footer-links.destroy');
        
        // Audit Logs
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('audit-logs');
    });
});

