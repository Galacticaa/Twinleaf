<?php

namespace Twinleaf\Http\Controllers;

use Twinleaf\Map;
use Twinleaf\MapArea;
use Twinleaf\Http\Requests\StoreMapArea;

class MapAreaController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Map $map)
    {
        return view('maps.areas.create')->with('map', $map);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreMapArea $request)
    {
        $area = MapArea::create($request->all());
        $map = Map::find($area->map_id);

        return redirect()->route('mapareas.show', [
            'map' => $map,
            'area' => $area,
        ]);
    }
}
