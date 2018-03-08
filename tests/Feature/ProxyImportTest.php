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
            'for_scanning' => '1',
            'for_creation' => '1',
            'for_activation' => '1',
            'proxies' => "127.0.0.1",
        ]);

        $response->assertRedirect('proxies');

        $this->assertDatabaseHas('proxies', [
            'url' => '127.0.0.1',
            'provider' => 'lime',
            'for_scanning' => true,
            'for_creation' => true,
            'for_activation' => true,
        ]);
    }

    /** @test */
    public function proxyListShowsNewProxiesAfterImport()
    {
        $response = $this->from('proxies')->post('proxies/import', [
            'provider' => 'lime',
            'mode' => 'a',
            'for_scanning' => '1',
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
            'Usage',
            'Scanning',
            'Creation',
            'Activation',
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
