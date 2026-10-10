<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\InternshipAttestation;
use App\Models\InternshipProfile;
use App\Models\User;
use App\Services\AttestationService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InternshipAttestationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $fellow;
    protected InternshipProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(config('filesystems.default'));

        Role::findOrCreate('admin');
        Role::findOrCreate('fellow');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->admin->assignRole('admin');

        $this->fellow = User::factory()->create([
            'name' => 'Jane Intern',
            'email' => 'jane.intern@example.com',
            'role' => 'fellow',
            'fellow_type' => 'academic',
        ]);
        $this->fellow->assignRole('fellow');

        $this->profile = InternshipProfile::create([
            'user_id' => $this->fellow->id,
            'type' => 'academic',
            'institution_name' => 'University of Bamenda',
            'academic_level' => 'hnd',
            'student_id' => 'UBA-SECRET-001',
            'supervisor_name' => 'Dr. Supervisor',
            'supervisor_email' => 'supervisor@example.com',
            'duration_type' => 'custom',
            'status' => InternshipProfile::STATUS_COMPLETED,
            'approved_start_date' => now()->subDays(60)->toDateString(),
            'approved_end_date' => now()->subDay()->toDateString(),
            'completed_at' => now(),
        ]);
    }

    protected function service(): AttestationService
    {
        return app(AttestationService::class);
    }

    protected function unpaidFee(): Fee
    {
        return Fee::create([
            'reference' => 'FEE-TEST-000001',
            'fellow_id' => $this->fellow->id,
            'title' => 'Internship fee',
            'amount_total' => 50000,
            'amount_paid' => 0,
            'first_due_date' => now()->addWeek()->toDateString(),
            'final_due_date' => now()->addWeek()->toDateString(),
            'status' => Fee::STATUS_ACTIVE,
        ]);
    }

    protected function issue(array $data = ['mention' => 'very_good']): InternshipAttestation
    {
        return $this->service()->issue($this->service()->ensureDraft($this->profile), $data, $this->admin);
    }

    public function test_draft_is_created_once_for_a_completed_internship(): void
    {
        $first = $this->service()->ensureDraft($this->profile);
        $second = $this->service()->ensureDraft($this->profile);

        $this->assertTrue($first->isDraft());
        $this->assertSame($first->id, $second->id);
        $this->assertSame('Jane Intern', $first->snapshot['holder']['name']);
        $this->assertDatabaseCount('internship_attestations', 1);
    }

    public function test_expired_internships_are_completed_and_given_a_draft(): void
    {
        $this->profile->update(['status' => InternshipProfile::STATUS_ACTIVE, 'completed_at' => null]);

        $this->assertSame(1, $this->service()->syncCompleted());

        $this->assertSame(InternshipProfile::STATUS_COMPLETED, $this->profile->fresh()->status);
        $this->assertDatabaseCount('internship_attestations', 1);
    }

    public function test_internship_of_a_deleted_fellow_is_skipped(): void
    {
        $this->fellow->delete();

        $this->service()->syncCompleted();
        $this->assertNull($this->service()->ensureDraft($this->profile->fresh()));
        $this->assertDatabaseCount('internship_attestations', 0);

        $this->actingAs($this->admin)->get(route('admin.attestations.index'))->assertOk();
    }

    public function test_it_cannot_be_issued_while_a_fee_is_outstanding(): void
    {
        $this->unpaidFee();

        $this->assertFalse($this->service()->eligibility($this->profile)['eligible']);

        $this->expectException(DomainException::class);
        $this->issue();
    }

    public function test_it_cannot_be_issued_before_the_internship_has_ended(): void
    {
        $this->profile->update(['status' => InternshipProfile::STATUS_ACTIVE]);

        $this->expectException(DomainException::class);
        $this->issue();
    }

    public function test_admin_issues_through_the_review_screen(): void
    {
        $draft = $this->service()->ensureDraft($this->profile);

        $this->actingAs($this->admin)
            ->get(route('admin.attestations.index'))
            ->assertOk()
            ->assertSee('Jane Intern');

        $this->actingAs($this->admin)
            ->get(route('admin.attestations.show', $draft))
            ->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.attestations.issue', $draft), [
                'mention' => 'excellent',
                'remarks_en' => 'Dependable and thorough.',
                'remarks_fr' => 'Fiable et rigoureuse.',
            ])
            ->assertRedirect(route('admin.attestations.show', $draft))
            ->assertSessionHas('success');

        $issued = $draft->fresh();
        $this->assertTrue($issued->isIssued());
        $this->assertMatchesRegularExpression('/^IKS-ATT-\d{4}-0001$/', $issued->reference);
        $this->assertMatchesRegularExpression('/^[A-Z2-9]{4}-[A-Z2-9]{4}$/', $issued->verification_code);
        $this->assertSame('excellent', $issued->snapshot['award']['mention']);
        Storage::assertExists($issued->pdf_path);
        $this->assertStringStartsWith('%PDF', Storage::get($issued->pdf_path));
        $this->assertDatabaseHas('audit_logs', ['action' => 'attestation_issued', 'auditable_id' => $issued->uuid]);
        $this->assertDatabaseHas('notifications', ['user_id' => $this->fellow->id, 'type' => 'attestation_issued']);
    }

    public function test_issued_record_is_frozen_and_public_page_hides_private_details(): void
    {
        $issued = $this->issue();

        $this->fellow->update(['name' => 'Changed Later']);

        $this->get(route('attestation.verify', $issued->uuid))
            ->assertOk()
            ->assertSee('Verified Attestation')
            ->assertSee('Jane Intern')
            ->assertSee($issued->reference)
            ->assertDontSee('Changed Later')
            ->assertDontSee('jane.intern@example.com')
            ->assertDontSee('UBA-SECRET-001')
            ->assertDontSee('supervisor@example.com');
    }

    public function test_draft_is_not_publicly_verifiable(): void
    {
        $draft = $this->service()->ensureDraft($this->profile);

        $this->get(route('attestation.verify', $draft->uuid))->assertNotFound();
    }

    public function test_manual_lookup_needs_the_matching_verification_code(): void
    {
        $issued = $this->issue();

        $this->post(route('attestation.find'), ['reference' => $issued->reference, 'code' => 'AAAA-AAAA'])
            ->assertSessionHasErrors('reference');

        $this->post(route('attestation.find'), ['reference' => strtolower($issued->reference), 'code' => $issued->verification_code])
            ->assertRedirect(route('attestation.verify', $issued->uuid));
    }

    public function test_revoked_attestation_shows_as_revoked_and_cannot_be_downloaded(): void
    {
        $issued = $this->issue();

        $this->actingAs($this->fellow)->get(route('fellow.attestation.download'))->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.attestations.revoke', $issued), ['revocation_reason' => 'Issued with the wrong dates.'])
            ->assertSessionHas('success');

        $this->get(route('attestation.verify', $issued->uuid))
            ->assertOk()
            ->assertSee('Revoked Attestation')
            ->assertDontSee('Issued with the wrong dates.');

        $this->actingAs($this->fellow)->get(route('fellow.attestation.download'))->assertNotFound();
    }

    public function test_reissue_supersedes_the_earlier_version_only_once_issued(): void
    {
        $first = $this->issue();

        $draft = $this->service()->reissue($first, $this->admin);
        $this->assertSame(2, $draft->version);
        $this->assertTrue($first->fresh()->isIssued());

        $second = $this->service()->issue($draft, ['mention' => 'good'], $this->admin);

        $this->assertTrue($first->fresh()->isSuperseded());
        $this->assertTrue($second->isIssued());
        $this->assertNotSame($first->reference, $second->reference);

        $this->get(route('attestation.verify', $first->uuid))
            ->assertOk()
            ->assertSee('Superseded Attestation')
            ->assertSee($second->reference);
    }

    public function test_admin_can_preview_a_draft_and_save_settings(): void
    {
        $draft = $this->service()->ensureDraft($this->profile);

        $this->actingAs($this->admin)
            ->get(route('admin.attestations.preview', ['attestation' => $draft, 'mention' => 'good']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertNull($draft->fresh()->pdf_path);

        $this->actingAs($this->admin)->get(route('admin.attestations.settings'))->assertOk();

        $this->actingAs($this->admin)
            ->post(route('admin.attestations.settings.update'), [
                'attestation_signatory_name' => 'Test Signatory',
                'attestation_signatory_title' => 'Director',
                'attestation_signatory_title_fr' => 'Directeur',
                'attestation_issue_place' => 'Douala',
                'attestation_org_address' => 'Douala, Cameroon',
                'attestation_mention_excellent_min' => 90,
                'attestation_mention_very_good_min' => 75,
                'attestation_mention_good_min' => 60,
            ])
            ->assertSessionHasNoErrors();

        $issued = $this->service()->issue($draft, ['mention' => 'good'], $this->admin);

        $this->assertSame('Test Signatory', $issued->signatory_name);
        $this->assertSame('Douala', $issued->snapshot['award']['place']);
    }

    public function test_independent_fellow_application_reaches_the_admin_internship_list(): void
    {
        $independent = User::factory()->create([
            'name' => 'Indie Fellow',
            'role' => 'fellow',
            'fellow_type' => 'independent',
            'onboarding_completed_at' => now(),
        ]);
        $independent->assignRole('fellow');

        $this->actingAs($independent)
            ->get(route('fellow.onboarding'))
            ->assertOk()
            ->assertSee('Independent Fellowship Details');

        $this->actingAs($independent)
            ->postJson(route('fellow.onboarding.save-internship'), [
                'institution_name' => 'Building my own product',
                'supervisor_name' => 'Mentor Person',
                'duration_type' => 'predefined',
                'predefined_duration_months' => 3,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('internship_profiles', [
            'user_id' => $independent->id,
            'type' => 'independent',
            'status' => InternshipProfile::STATUS_PENDING,
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.internships.index', ['type' => 'independent']))
            ->assertOk()
            ->assertSee('Indie Fellow');
    }

    public function test_fellow_sees_own_status_and_cannot_reach_admin_screens(): void
    {
        $this->unpaidFee();
        $draft = $this->service()->ensureDraft($this->profile);

        $this->actingAs($this->fellow)
            ->get(route('fellow.attestation.show'))
            ->assertOk()
            ->assertSee('on hold until your outstanding fees are settled');

        $this->actingAs($this->fellow)->get(route('fellow.attestation.download'))->assertNotFound();
        $this->actingAs($this->fellow)->get(route('admin.attestations.show', $draft))->assertForbidden();
        $this->actingAs($this->fellow)->post(route('admin.attestations.issue', $draft), ['mention' => 'excellent'])->assertForbidden();
    }
}
