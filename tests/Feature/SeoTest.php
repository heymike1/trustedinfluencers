<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Models\Creator;
use App\Models\CreatorCategory;
use App\Models\CreatorSocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_carry_meta_open_graph_and_structured_data(): void
    {
        $category = CreatorCategory::factory()->create(['name' => 'Finance', 'slug' => 'finance']);
        $creator = Creator::factory()->create(['name' => 'John Smith', 'slug' => 'john-smith', 'creator_category_id' => $category->id, 'bio' => 'Money, simply.']);
        CreatorSocialAccount::factory()->for($creator)->platform(Platform::YouTube, 'johnsmith')->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large">', false)
            ->assertSee('<meta property="og:site_name" content="', false)
            ->assertSee('<meta name="twitter:card"', false)
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('SearchAction', false);

        $this->get(route('creators.show', $creator))
            ->assertOk()
            ->assertSee('<title>John Smith YouTube Stats &amp; Verified Creator Metrics', false)
            ->assertSee('<link rel="canonical" href="'.route('creators.show', $creator).'">', false)
            ->assertSee('<meta property="og:type" content="profile">', false)
            ->assertSee('"@type":"ProfilePage"', false)
            ->assertSee('https://www.youtube.com/@johnsmith', false)
            ->assertSee('"name":"Finance"', false);

        $this->get(route('creators.index', ['platform' => 'youtube', 'page' => 2]))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('creators.index', ['platform' => 'youtube']).'">', false);
    }

    public function test_legal_pages_are_served_and_linked(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('YouTube API Services')->assertSee('Limited Use')->assertSee('Run More Brands')->assertSee('Autoriteit Persoonsgegevens');
        $this->get('/terms')->assertOk()->assertSee('Dutch law')->assertSee('EUR 100');
        $this->get('/')->assertSee(route('privacy'))->assertSee(route('terms'));
    }

    public function test_private_pages_are_not_indexed(): void
    {
        $user = User::factory()->create();

        $this->get(route('login'))->assertSee('noindex, nofollow', false);
        $this->actingAs($user)->get(route('account'))->assertSee('noindex, nofollow', false);
    }

    public function test_the_sitemap_lists_active_creators_categories_and_platforms(): void
    {
        CreatorCategory::factory()->create(['slug' => 'tech']);
        $active = Creator::factory()->create(['slug' => 'listed']);
        Creator::factory()->create(['slug' => 'hidden-one', 'status' => 'hidden']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=utf-8')
            ->assertSee(route('creators.show', $active), false)
            ->assertSee(route('creators.index', ['category' => 'tech']), false)
            ->assertSee(route('creators.index', ['platform' => 'x']), false)
            ->assertDontSee('hidden-one');
    }

    public function test_robots_llms_and_manifest_are_in_place(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Disallow: /admin', $robots);
        $this->assertStringContainsString('Sitemap:', $robots);
        $this->assertStringContainsString('# Trusted Influencers', file_get_contents(public_path('llms.txt')));
        $this->assertSame('Trusted Influencers', json_decode(file_get_contents(public_path('site.webmanifest')), true)['name']);
    }
}
