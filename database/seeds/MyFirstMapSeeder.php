<?php

use Illuminate\Database\Seeder;
use Twinleaf\Map;
use Twinleaf\MapArea;

class MyFirstMapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Map::create([
            'name' => 'My First Map',
            'code' => 'my-first-map',
            'url' => 'http://localhost:8001',
            'location' => '',
            'db_name' => 'myfirstmap',
            'db_user' => 'root',
            'db_pass' => 'root',
        ]);

        MapArea::create([
            'name' => 'Santa Monica',
            'slug' => 'santa-monica',
            'map_id' => 1,
            'location' => '35.31233, 138.5892',
        ]);
    }
}
