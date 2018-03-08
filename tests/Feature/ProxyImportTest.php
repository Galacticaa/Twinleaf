<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProxyImportTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function theImportModalHasAllFields()
    {
        $response = $this->get('proxies');

        $this->assertAllImportFieldsAreVisible($response);

        $this->assertAllProxyProvidersAreVisible($response);
    }

    /** @test */
    public function canImportProxies()
    {
        $response = $this->from('proxies')->post('proxies/import', [
            'provider' => 'lime',
            'mode' => 'a',
            'proxies' => implode("\n", [
                "127.0.0.1",
                "192.168.1.100",
            ]),
        ]);

        $response->assertRedirect('proxies');

        $this->assertDatabaseHas('proxies', [
            'url' => '192.168.1.100',
            'provider' => 'lime',
        ]);

        $this->assertDatabaseHas('proxies', [
            'url' => '127.0.0.1',
            'provider' => 'lime',
        ]);
    }

    /** @test */
    public function proxyListShowsNewProxiesAfterImport()
    {
        $response = $this->from('proxies')->post('proxies/import', [
            'provider' => 'lime',
            'mode' => 'a',
            'proxies' => implode("\n", [
                "127.0.0.1",
                "192.168.1.100",
            ]),
        ]);

        $response = $this->get('proxies');

        $this->seesTableHeaders($response, [
            'Proxy',
            'Provider',
            'Map Area',
            'PTC Status',
            'PoGo Status',
        ]);

        $response->assertSee('Lime Proxies');
        $response->assertSee('127.0.0.1');
        $response->assertSee('192.168.1.100');
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

    protected function seesTableHeaders($response, array $values)
    {
        foreach ($values as $k => $v) {
            $values[$k] = '<th>'.$v.'</th>';
        }

        $this->seesValues($response, $values);
    }

    protected function seesValues($response, array $values)
    {
        foreach ($values as $value) {
            $response->assertSee($value);
        }
    }
}
