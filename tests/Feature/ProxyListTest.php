<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProxyListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function proxyListShowsNoProxiesMessage()
    {
        $response = $this->get('proxies');

        $response->assertStatus(200);
        $response->assertSee("There aren't any proxies!");
    }
}
