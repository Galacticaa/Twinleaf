<?php

namespace Tests\Feature;

use Twinleaf\Map;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddMapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @test
     * @return void
     */
    public function addMapPageLoadsWithoutError()
    {
        $response = $this->get('/maps/create');

        $response->assertStatus(200);

        $this->assertAllInputsAreVisible($response);
    }

    protected function assertAllInputsAreVisible($response)
    {
        $response->assertSee('Name');
        $response->assertSee('Code');
        $response->assertSee('Description');
        $response->assertSee('Cover Image URL');
        $response->assertSee('Web Address');
        $response->assertSee('Google Analytics Key');
        $response->assertSee('Map Location');
        $response->assertSee('Database Name');
        $response->assertSee('MySQL Username');
        $response->assertSee('MySQL Password');
    }

    /**
     * Test user can save a new map
     *
     * @test
     * @return void
     */
    public function canSaveNewMap()
    {
        $map = $this->getDummyMap();

        $response = $this->post('/maps', $map);

        $response->assertRedirect('maps/test-map');

        $this->assertDatabaseHas((new Map)->getTable(), $map);
    }

    /** @test */
    public function requiredFields()
    {
        $this->validateInput('name');
        $this->validateInput('description');
        $this->validateInput('code');
        $this->validateInput('url');
        $this->validateInput('image_url');
        $this->validateInput('location');
        $this->validateInput('db_name');
        $this->validateInput('db_user');
        $this->validateInput('db_pass');
    }

    public function descriptionMustBeString()
    {
        $this->validateInput('description', 1);
    }

    protected function validateInput($key, $value = '')
    {
        $map = $this->getDummyMap([$key => $value]);

        $response = $this->from('/maps')->post('/maps', $map);

        $response->assertRedirect('/maps');
        $response->assertSessionHasErrors($key);
    }

    protected function getDummyMap($params = [])
    {
        return array_merge([
            'name' => 'Test Map',
            'code' => 'test-map',
            'description' => 'A description.',
            'analytics_key' => 'GA-12345-A',
            'url' => 'http://google.com',
            'image_url' => 'http://example.com/image.png',
            'location' => '34.1234,1.532',
            'db_name' => 'root',
            'db_user' => 'root',
            'db_pass' => 'root',
        ], $params);
    }
}
