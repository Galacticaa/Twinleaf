<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProxyImportTest extends TestCase
{
    /** @test */
    public function theImportModalHasAllFields()
    {
        $response = $this->get('proxies');

        $this->assertAllImportFieldsAreVisible($response);

        $this->assertAllProxyProvidersAreVisible($response);
    }

    protected function assertAllImportFieldsAreVisible($response)
    {
        $this->seesValues($response, [
            'Provider',
            'Import mode',
            'Append',
            'Replace',
            'Purge',
            'Proxy list',
            'name="proxies"',
        ]);
    }

    protected function assertAllProxyProvidersAreVisible($response)
    {
        $this->seesValues($response, [
            'BuyProxies.org',
            'Instant Proxies',
            'Lime Proxies',
            'My Private Proxy',
            'ProxyPrivate',
        ]);
    }

    protected function seesValues($response, array $values)
    {
        foreach ($values as $value) {
            $response->assertSee($value);
        }
    }
}
