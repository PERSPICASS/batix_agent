<?php

namespace Tests\Feature;

use App\Jobs\GenerateCampaignContents;
use App\Jobs\GenerateMarketingImage;
use App\Jobs\ScoreMarketingLead;
use App\Models\AdminUser;
use App\Models\MarketingCampaign;
use App\Models\MarketingContent;
use App\Models\MarketingLead;
use App\Services\BatixGrowthAiService;
use App\Services\MarketingImageService;
use App\Support\GrowthOptions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class GrowthControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedRequest(): static
    {
        $admin = AdminUser::factory()->create();

        return $this->withSession(['growth_admin_id' => $admin->id]);
    }

    public function test_growth_pages_redirect_unauthenticated_visitors_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_an_administrator_can_sign_in_from_the_login_page(): void
    {
        $admin = AdminUser::factory()->create([
            'username' => 'test-admin',
        ]);

        $this->get('/login')->assertOk();
        $this->post('/login', ['username' => 'test-admin', 'password' => 'test-password'])->assertRedirect('/');
        $this->get('/')->assertOk();
        $this->assertNotNull($admin->fresh()->last_login_at);
    }

    public function test_invalid_login_credentials_are_rejected(): void
    {
        $this->post('/login', ['username' => 'test-admin', 'password' => 'wrong-password'])
            ->assertRedirect()
            ->assertSessionHasErrors('username');
    }

    public function test_an_administrator_can_create_another_administrator(): void
    {
        $this->authenticatedRequest()->post('/admins', [
            'name' => 'Aminata Koné',
            'username' => 'aminata-kone',
            'password' => 'mot-de-passe-solide',
        ])->assertRedirect();

        $admin = AdminUser::query()->where('username', 'aminata-kone')->firstOrFail();
        $this->assertSame('Aminata Koné', $admin->name);
        $this->assertTrue(Hash::check('mot-de-passe-solide', $admin->password));
    }

    public function test_an_administrator_cannot_deactivate_their_own_account(): void
    {
        $admin = AdminUser::factory()->create();

        $this->withSession(['growth_admin_id' => $admin->id])
            ->patch("/admins/{$admin->id}", ['is_active' => false])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_a_campaign_can_be_created_with_a_supported_channel(): void
    {
        $this->authenticatedRequest()->post('/campaigns', [
            'name' => 'Acquisition Abidjan',
            'channel' => 'facebook',
            'objective' => 'Obtenir des demandes de démonstration.',
            'audience' => 'Gérants de commerces.',
            'offer' => 'Démonstration gratuite.',
            'daily_budget' => 5000,
        ])->assertRedirect();

        $this->assertDatabaseHas('marketing_campaigns', [
            'name' => 'Acquisition Abidjan',
            'channel' => 'facebook',
            'status' => 'draft',
        ]);
    }

    public function test_an_unsupported_campaign_channel_is_rejected(): void
    {
        $this->authenticatedRequest()->post('/campaigns', [
            'name' => 'Campagne invalide',
            'channel' => 'email',
            'objective' => 'Objectif.',
            'audience' => 'Audience.',
        ])->assertSessionHasErrors('channel');
    }

    public function test_an_administrator_can_update_campaign_tracking_metrics(): void
    {
        $campaign = MarketingCampaign::factory()->create(['status' => 'draft']);

        $this->authenticatedRequest()->patch("/campaigns/{$campaign->id}", [
            'status' => 'active',
            'metrics' => ['reach' => 1200, 'clicks' => 80, 'conversations' => 14, 'demos' => 3, 'spend' => 12500],
        ])->assertRedirect();

        $campaign->refresh();
        $this->assertSame('active', $campaign->status);
        $this->assertSame(1200, $campaign->metrics['reach']);
        $this->assertSame(12500, $campaign->metrics['spend']);
    }

    public function test_lost_leads_are_not_considered_qualified(): void
    {
        $this->assertContains('qualified', GrowthOptions::QUALIFIED_LEAD_STATUSES);
        $this->assertNotContains('lost', GrowthOptions::QUALIFIED_LEAD_STATUSES);
    }

    public function test_content_generation_is_queued(): void
    {
        Queue::fake();
        $campaign = MarketingCampaign::factory()->create();

        $this->authenticatedRequest()->post("/campaigns/{$campaign->id}/generate")->assertRedirect();

        Queue::assertPushed(GenerateCampaignContents::class, fn (GenerateCampaignContents $job) => $job->campaignId === $campaign->id);
        $this->assertDatabaseHas('marketing_campaigns', ['id' => $campaign->id, 'content_generation_status' => 'queued']);
    }

    public function test_scoring_is_queued(): void
    {
        Queue::fake();
        $lead = MarketingLead::factory()->create();

        $this->authenticatedRequest()->post("/leads/{$lead->id}/score")->assertRedirect();

        Queue::assertPushed(ScoreMarketingLead::class, fn (ScoreMarketingLead $job) => $job->leadId === $lead->id);
        $this->assertDatabaseHas('marketing_leads', ['id' => $lead->id, 'scoring_status' => 'queued']);
    }

    public function test_an_administrator_can_move_a_lead_through_the_sales_pipeline(): void
    {
        $lead = MarketingLead::factory()->create(['status' => 'new']);

        $this->authenticatedRequest()->patch("/leads/{$lead->id}/status", ['status' => 'demo'])->assertRedirect();

        $this->assertDatabaseHas('marketing_leads', ['id' => $lead->id, 'status' => 'demo']);
    }

    public function test_an_administrator_can_record_a_lead_interaction(): void
    {
        $admin = AdminUser::factory()->create();
        $lead = MarketingLead::factory()->create();

        $this->withSession(['growth_admin_id' => $admin->id])
            ->post("/leads/{$lead->id}/interactions", [
                'type' => 'whatsapp',
                'body' => 'Le prospect souhaite une démonstration mardi.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('marketing_lead_interactions', [
            'marketing_lead_id' => $lead->id,
            'admin_user_id' => $admin->id,
            'type' => 'whatsapp',
            'body' => 'Le prospect souhaite une démonstration mardi.',
        ]);
    }

    public function test_an_administrator_can_send_a_whatsapp_message(): void
    {
        config([
            'services.whatsapp.graph_version' => 'v21.0',
            'services.whatsapp.phone_number_id' => '123456',
            'services.whatsapp.access_token' => 'test-token',
        ]);
        Http::fake([
            'https://graph.facebook.com/v21.0/123456/messages' => Http::response([
                'messages' => [['id' => 'wamid.outbound']],
                'contacts' => [['wa_id' => '2250102030405']],
            ], 200),
        ]);
        $lead = MarketingLead::factory()->create(['phone' => '+225 01 02 03 04 05']);

        $this->authenticatedRequest()->post("/leads/{$lead->id}/whatsapp", [
            'body' => 'Bonjour, pouvons-nous organiser une démonstration ?',
        ])->assertRedirect();

        Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v21.0/123456/messages'
            && $request['to'] === '2250102030405'
            && $request['text']['body'] === 'Bonjour, pouvons-nous organiser une démonstration ?');
        $this->assertDatabaseHas('marketing_lead_interactions', [
            'marketing_lead_id' => $lead->id,
            'type' => 'whatsapp',
            'direction' => 'outbound',
            'external_id' => 'wamid.outbound',
        ]);
    }

    public function test_whatsapp_webhook_verification_returns_the_challenge(): void
    {
        config(['services.meta.verify_token' => 'verification-token']);

        $this->get('/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=verification-token&hub.challenge=challenge-value')
            ->assertOk()
            ->assertSeeText('challenge-value');
    }

    public function test_an_inbound_whatsapp_webhook_creates_a_lead_and_interaction(): void
    {
        config(['services.meta.app_secret' => 'meta-app-secret']);
        $payload = [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'contacts' => [['wa_id' => '2250102030405', 'profile' => ['name' => 'Awa Koné']]],
                        'messages' => [[
                            'id' => 'wamid.inbound',
                            'from' => '2250102030405',
                            'timestamp' => (string) now()->timestamp,
                            'type' => 'text',
                            'text' => ['body' => 'Bonjour, je souhaite une démonstration.'],
                        ]],
                    ],
                ]],
            ]],
        ];
        $content = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = 'sha256='.hash_hmac('sha256', $content, 'meta-app-secret');

        $this->call('POST', '/webhooks/whatsapp', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => $signature,
        ], $content)->assertOk();

        $this->assertDatabaseHas('marketing_leads', [
            'name' => 'Awa Koné',
            'phone' => '+2250102030405',
            'source' => 'whatsapp',
        ]);
        $this->assertDatabaseHas('marketing_lead_interactions', [
            'external_id' => 'wamid.inbound',
            'type' => 'whatsapp',
            'direction' => 'inbound',
            'body' => 'Bonjour, je souhaite une démonstration.',
        ]);
    }

    public function test_a_facebook_post_image_is_generated_and_stored(): void
    {
        Storage::fake('public');
        config([
            'services.openai.api_key' => 'test-openai-key',
            'services.openai.image_model' => 'gpt-image-2',
            'services.openai.image_quality' => 'low',
        ]);
        Http::fake([
            'https://api.openai.com/v1/images/generations' => Http::response([
                'data' => [['b64_json' => base64_encode('generated-image')]],
            ]),
        ]);
        $content = MarketingContent::create([
            'channel' => 'facebook',
            'format' => 'post',
            'status' => 'draft',
            'title' => 'Pilotez votre commerce',
            'body' => 'Simplifiez les ventes et le stock avec BatixPro.',
        ]);

        (new GenerateMarketingImage($content->id))->handle(app(MarketingImageService::class));

        $content->refresh();
        $this->assertSame('completed', $content->image_generation_status);
        $this->assertNotNull($content->image_path);
        Storage::disk('public')->assertExists($content->image_path);
    }

    public function test_an_approved_facebook_post_with_an_image_can_be_published(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('marketing/facebook/post.png', 'image-bytes');
        config([
            'services.meta.graph_version' => 'v21.0',
            'services.meta.page_id' => 'batixpro-page',
            'services.meta.page_access_token' => 'page-token',
        ]);
        Http::fake([
            'https://graph.facebook.com/v21.0/batixpro-page/photos' => Http::response([
                'id' => 'facebook-photo-id',
                'post_id' => 'batixpro-page_facebook-post-id',
            ]),
        ]);
        $content = MarketingContent::create([
            'channel' => 'facebook',
            'format' => 'post',
            'status' => 'approved',
            'title' => 'Pilotez votre commerce',
            'body' => 'Simplifiez les ventes et le stock avec BatixPro.',
            'image_path' => 'marketing/facebook/post.png',
            'image_generation_status' => 'completed',
        ]);

        $this->authenticatedRequest()->post("/contents/{$content->id}/facebook-publish")->assertRedirect();

        Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v21.0/batixpro-page/photos');
        $this->assertDatabaseHas('marketing_contents', [
            'id' => $content->id,
            'status' => 'published',
            'facebook_post_id' => 'batixpro-page_facebook-post-id',
        ]);
    }

    public function test_an_administrator_can_create_a_facebook_post_from_the_content_interface(): void
    {
        Queue::fake();
        $ai = Mockery::mock(BatixGrowthAiService::class);
        $ai->shouldReceive('generateFacebookPost')->once()->with(
            'Éviter les ruptures de stock',
            'Gérants de commerces',
            'Demander une démo',
        )->andReturn([
            'title' => 'Anticipez vos ruptures de stock',
            'hook' => 'Votre stock ne devrait jamais vous surprendre.',
            'body' => 'Pilotez les ventes et le stock avec BatixPro.',
            'cta' => 'Demander une démo',
        ]);
        $this->app->instance(BatixGrowthAiService::class, $ai);

        $this->authenticatedRequest()->post('/facebook-posts', [
            'subject' => 'Éviter les ruptures de stock',
            'audience' => 'Gérants de commerces',
            'offer' => 'Demander une démo',
        ])->assertRedirect();

        $this->assertDatabaseHas('marketing_contents', [
            'channel' => 'facebook',
            'format' => 'post',
            'status' => 'draft',
            'title' => 'Anticipez vos ruptures de stock',
        ]);
        Queue::assertPushed(GenerateMarketingImage::class);
    }

    public function test_content_generation_job_saves_drafts_and_tracks_completion(): void
    {
        Queue::fake();
        $campaign = MarketingCampaign::factory()->create();
        $ai = Mockery::mock(BatixGrowthAiService::class);
        $ai->shouldReceive('generateCampaignContents')->once()->andReturn([
            ['format' => 'post', 'title' => 'Post', 'hook' => 'Hook', 'body' => 'Texte', 'cta' => 'Écrire'],
            ['format' => 'reel_script', 'title' => 'Reel', 'hook' => 'Hook', 'body' => 'Texte', 'cta' => 'Voir'],
            ['format' => 'ad', 'title' => 'Publicité', 'hook' => 'Hook', 'body' => 'Texte', 'cta' => 'Tester'],
        ]);

        (new GenerateCampaignContents($campaign->id))->handle($ai);

        $this->assertSame(3, MarketingContent::where('marketing_campaign_id', $campaign->id)->where('status', 'draft')->count());
        Queue::assertPushed(GenerateMarketingImage::class, fn (GenerateMarketingImage $job) => $job->contentId > 0);
        $this->assertDatabaseHas('marketing_campaigns', [
            'id' => $campaign->id,
            'content_generation_status' => 'completed',
            'content_generation_attempts' => 1,
        ]);
    }

    public function test_scoring_job_preserves_a_won_status(): void
    {
        $lead = MarketingLead::factory()->create(['status' => 'won']);
        $ai = Mockery::mock(BatixGrowthAiService::class);
        $ai->shouldReceive('scoreLead')->once()->andReturn([
            'score' => 88,
            'qualification' => 'warm',
            'summary' => 'Besoin identifié.',
            'next_action' => 'Proposer un créneau.',
            'whatsapp_message' => 'Bonjour, parlons de votre besoin.',
        ]);
        (new ScoreMarketingLead($lead->id))->handle($ai);

        $this->assertDatabaseHas('marketing_leads', [
            'id' => $lead->id,
            'score' => 88,
            'status' => 'won',
            'scoring_status' => 'completed',
            'scoring_attempts' => 1,
        ]);
    }

    public function test_a_failed_generation_is_recorded_for_retry(): void
    {
        $campaign = MarketingCampaign::factory()->create();

        (new GenerateCampaignContents($campaign->id))->failed(new RuntimeException('OpenAI indisponible'));

        $this->assertDatabaseHas('marketing_campaigns', [
            'id' => $campaign->id,
            'content_generation_status' => 'failed',
            'content_generation_error' => 'OpenAI indisponible',
        ]);
    }
}
