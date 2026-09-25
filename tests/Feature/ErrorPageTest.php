<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_missing_page_renders_the_branded_404(): void
    {
        $this->get('/creators/nobody-here')
            ->assertNotFound()
            ->assertSee('This page isn’t here.', false)
            ->assertSee('Error 404')
            ->assertSee('noindex, nofollow', false)
            ->assertSee(route('creators.index'), false);
    }

    public function test_the_500_page_renders(): void
    {
        $this->assertStringContainsString('Something broke on our side.', view('errors.500')->render());
    }
}
