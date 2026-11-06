<?php

namespace Tests\Feature;

use App\Enums\SubscriptionStatus;
use App\Enums\SubscriptionTier;
use App\Models\AdminSetting;
use App\Models\Notification;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecruiterSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $recruiter;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('recruiter');
        Role::findOrCreate('fellow');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        $this->recruiter = User::factory()->create([
            'name' => 'Rita Recruiter',
            'role' => 'recruiter',
            'company_name' => 'Douala Tech',
            'is_active' => true,
        ]);
        $this->recruiter->assignRole('recruiter');
    }

    protected function requestPartner(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->recruiter)->post(route('recruiter.subscription.subscribe'), array_merge([
            'tier' => 'partner',
            'months' => 12,
            'payment_method' => 'mtn_momo',
            'payment_reference' => 'MP241105.1200.A12345',
        ], $overrides));
    }

    public function test_a_paid_plan_needs_a_payment_reference(): void
    {
        $this->requestPartner(['payment_reference' => ''])->assertSessionHasErrors('payment_reference');

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_a_paid_plan_request_gives_no_access_until_payment_is_confirmed(): void
    {
        AdminSetting::set('partner_price', 300000);

        $this->requestPartner()->assertRedirect(route('recruiter.subscription.index'));

        $request = Subscription::sole();
        $this->assertSame(SubscriptionStatus::PENDING, $request->status);
        $this->assertSame(300000, $request->amount);
        $this->assertSame(12, $request->requested_months);
        $this->assertSame('MP241105.1200.A12345', $request->payment_reference);
        $this->assertNull($this->recruiter->activeSubscription());

        $this->get(route('recruiter.marketplace.index'))->assertRedirect(route('recruiter.subscription.index'));

        $this->assertTrue(Notification::where('user_id', $this->admin->id)->where('type', 'subscription_payment_review')->exists());
    }

    public function test_admin_confirmation_starts_the_plan_and_closes_the_trial(): void
    {
        $this->actingAs($this->recruiter)->post(route('recruiter.subscription.trial'))->assertRedirect(route('recruiter.dashboard'));
        $trial = $this->recruiter->activeSubscription();
        $this->assertSame(SubscriptionStatus::TRIAL, $trial->status);

        $this->requestPartner();
        $request = $this->recruiter->pendingSubscription();

        $this->actingAs($this->admin)->post(route('admin.subscriptions.approve', $request))->assertSessionHas('success');

        $active = $this->recruiter->fresh()->activeSubscription();
        $this->assertSame($request->id, $active->id);
        $this->assertSame(SubscriptionTier::PARTNER, $active->tier);
        $this->assertSame($this->admin->id, $active->verified_by);
        $this->assertTrue($active->expires_at->between(now()->addMonths(12)->subDay(), now()->addMonths(12)->addDay()));
        $this->assertSame(SubscriptionStatus::CANCELLED, $trial->fresh()->status);

        // A second confirmation is refused.
        $this->post(route('admin.subscriptions.approve', $request))->assertSessionHas('error');

        // The recruiter now reaches the marketplace and dashboard.
        $this->actingAs($this->recruiter->fresh());
        $this->get(route('recruiter.marketplace.index'))->assertOk();
        $this->get(route('recruiter.dashboard'))->assertOk();
        $this->get(route('recruiter.subscription.index'))->assertOk()->assertSee('Partner Plan');
    }

    public function test_admin_can_reject_a_request_with_a_reason(): void
    {
        $this->requestPartner();
        $request = $this->recruiter->pendingSubscription();

        $this->actingAs($this->admin)
            ->post(route('admin.subscriptions.reject', $request), ['reason' => 'No payment found with this reference'])
            ->assertSessionHas('success');

        $request->refresh();
        $this->assertSame(SubscriptionStatus::CANCELLED, $request->status);
        $this->assertSame('No payment found with this reference', $request->rejection_reason);
        $this->assertNull($this->recruiter->activeSubscription());
        $this->assertTrue(Notification::where('user_id', $this->recruiter->id)->where('type', 'subscription_rejected')->exists());

        $this->actingAs($this->recruiter)->get(route('recruiter.subscription.index'))
            ->assertOk()
            ->assertSee('No payment found with this reference');
    }

    public function test_the_trial_can_be_used_only_once(): void
    {
        $this->actingAs($this->recruiter)->post(route('recruiter.subscription.trial'));
        $this->recruiter->activeSubscription()->update(['status' => SubscriptionStatus::CANCELLED]);

        $this->post(route('recruiter.subscription.trial'))->assertSessionHas('error', 'Trial already used for this account.');
        $this->assertSame(1, $this->recruiter->subscriptions()->count());
    }

    public function test_the_free_plan_starts_at_once(): void
    {
        $this->actingAs($this->recruiter)
            ->post(route('recruiter.subscription.subscribe'), ['tier' => 'free'])
            ->assertRedirect(route('recruiter.dashboard'));

        $active = $this->recruiter->activeSubscription();
        $this->assertSame(SubscriptionTier::FREE, $active->tier);
        $this->assertSame(SubscriptionStatus::ACTIVE, $active->status);
    }

    public function test_an_expired_plan_gives_no_access(): void
    {
        Subscription::create([
            'recruiter_id' => $this->recruiter->id,
            'tier' => SubscriptionTier::PARTNER,
            'status' => SubscriptionStatus::ACTIVE,
            'started_at' => now()->subYear(),
            'expires_at' => now()->subDay(),
        ]);

        $this->assertNull($this->recruiter->activeSubscription());
        $this->actingAs($this->recruiter)->get(route('recruiter.marketplace.index'))->assertRedirect(route('recruiter.subscription.index'));
    }

    public function test_an_upgrade_is_a_request_that_keeps_the_period(): void
    {
        $expires = now()->addMonths(3)->startOfSecond();
        $partner = Subscription::create([
            'recruiter_id' => $this->recruiter->id,
            'tier' => SubscriptionTier::PARTNER,
            'status' => SubscriptionStatus::ACTIVE,
            'started_at' => now()->subMonths(9),
            'expires_at' => $expires,
        ]);

        $this->actingAs($this->recruiter)->post(route('recruiter.subscription.upgrade'), [
            'tier' => 'premium',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'BANK-998877',
        ])->assertSessionHas('success');

        $this->assertSame($partner->id, $this->recruiter->activeSubscription()->id, 'Partner stays active until the upgrade is confirmed.');

        $request = $this->recruiter->pendingSubscription();
        $this->assertSame(SubscriptionTier::PREMIUM, $request->tier);
        $this->assertGreaterThan(0, $request->amount);

        $this->actingAs($this->admin)->post(route('admin.subscriptions.approve', $request));

        $active = $this->recruiter->fresh()->activeSubscription();
        $this->assertSame(SubscriptionTier::PREMIUM, $active->tier);
        $this->assertEquals($expires, $active->expires_at);
    }

    public function test_an_unlimited_plan_can_browse_fellows_whose_skills_hold_goal_data(): void
    {
        // Onboarding stores goals inside the skills column; the marketplace must only list skill names.
        $fellow = User::factory()->create([
            'name' => 'Fiona Fellow',
            'role' => 'fellow',
            'is_active' => true,
            'is_public' => true,
            'skills' => ['Laravel', 'React', 'goals' => ['first_job'], 'primary_goal' => 'first_job', 'goal_timeline' => '3_months'],
        ]);
        $fellow->assignRole('fellow');

        Subscription::create([
            'recruiter_id' => $this->recruiter->id,
            'tier' => SubscriptionTier::PARTNER,
            'status' => SubscriptionStatus::ACTIVE,
            'started_at' => now(),
            'expires_at' => now()->addYear(),
            'profile_view_limit' => -1, // unlimited
            'intro_request_limit' => 5,
        ]);

        $this->assertSame(['Laravel', 'React'], $fellow->skill_list);

        $this->actingAs($this->recruiter);
        $this->get(route('recruiter.marketplace.index'))->assertOk();
        $this->get(route('recruiter.dashboard'))->assertOk();
        $this->get(route('recruiter.talent.show', $fellow))->assertOk()->assertSee('Fiona Fellow');
    }

    public function test_subscription_pages_render(): void
    {
        $this->requestPartner();
        Subscription::create([
            'recruiter_id' => $this->recruiter->id,
            'tier' => SubscriptionTier::FREE,
            'status' => SubscriptionStatus::ACTIVE,
            'started_at' => now(),
        ]);

        $this->actingAs($this->recruiter)->get(route('recruiter.subscription.index'))
            ->assertOk()
            ->assertSee('Awaiting payment confirmation')
            ->assertSee('MP241105.1200.A12345');

        $this->actingAs($this->admin);
        $this->get(route('admin.subscriptions.index'))->assertOk()->assertSee('Rita Recruiter')->assertSee('MP241105.1200.A12345');
        $this->get(route('admin.recruiters.index'))->assertOk()->assertSee('Free')->assertSee('Request pending');
        $this->get(route('admin.recruiters.show', $this->recruiter))->assertOk()->assertSee('Partner plan requested');
    }

    public function test_admin_can_save_subscription_and_score_formula_settings(): void
    {
        $this->actingAs($this->admin)->patch(route('admin.settings.update', 'subscriptions'), [
            'free_profile_views' => 20,
            'free_downloads' => 5,
            'partner_price' => 250000,
            'partner_profile_views' => -1,
            'partner_intros' => 5,
            'premium_price' => 1000000,
            'premium_profile_views' => -1,
            'premium_intros' => -1,
            'recruiter_trial_days' => 7,
            'subscription_payment_instructions' => 'MTN MoMo 650 000 000 (I-NNOVA CMR)',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.settings'));

        $this->assertSame(250000, (int) AdminSetting::get('partner_price'));
        $this->assertSame('MTN MoMo 650 000 000 (I-NNOVA CMR)', AdminSetting::get('subscription_payment_instructions'));

        $this->patch(route('admin.settings.update', 'scoring_curve'), [
            'score_curve_steepness' => 1.5,
            'score_interview_full_volume' => 15,
            'score_interview_session_share' => 60,
        ])->assertSessionHasNoErrors();

        $this->assertEquals(1.5, AdminSetting::get('score_curve_steepness'));
        $this->assertSame(15, (int) AdminSetting::get('score_interview_full_volume'));

        $this->get(route('admin.settings'))->assertOk()->assertSee('Score Formula');
    }
}
