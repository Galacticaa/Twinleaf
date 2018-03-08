<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @test
     * @return void
     */
    public function indexRedirectsToDashboard()
    {
        $response = $this->get('/');

        $response->assertRedirect('/dashboard');
    }
}
